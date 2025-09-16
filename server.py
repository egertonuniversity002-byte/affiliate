import os
from dotenv import load_dotenv
import uuid
import time
import asyncio
from datetime import datetime, timedelta, timezone
from typing import Any, Dict, List, Optional, Literal
import json
import urllib.request
import urllib.error

from fastapi import FastAPI, Depends, HTTPException, status, Body, Path, Query, WebSocket, WebSocketDisconnect, BackgroundTasks, Request
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from fastapi.websockets import WebSocketState

from pydantic import BaseModel, BaseSettings, Field, EmailStr
import smtplib
from email.mime.text import MIMEText

from beanie import Document, Indexed, init_beanie
from motor.motor_asyncio import AsyncIOMotorClient

from passlib.context import CryptContext
import jwt


# ----------------------------------------------------------------------------
# Settings
# ----------------------------------------------------------------------------


class Settings(BaseSettings):
    app_name: str = "Binary Affiliate Platform API"
    mongo_uri: str = os.getenv("MONGO_URI", "mongodb://localhost:27017/affiliate_db")
    jwt_secret: str = os.getenv("JWT_SECRET", "supersecretjwt")
    jwt_algorithm: str = "HS256"
    access_token_expires_minutes: int = 30
    refresh_token_expires_days: int = 30
    activation_fee_usd: float = 50.0
    commission_base_percent: float = 10.0  # level 1, then decays
    commission_decay_percent: float = 2.0  # per level
    frontend_base_url: str = os.getenv("FRONTEND_URL", "https://www.mywebsite.com")
    email_from: str = os.getenv("EMAIL_FROM", "no-reply@mywebsite.com")
    allowed_origins: List[str] = ["*"]
    min_withdraw_usd: float = float(os.getenv("MIN_WITHDRAW_USD", "4.5"))
    # SMTP (Gmail-compatible)
    smtp_host: Optional[str] = os.getenv("SMTP_HOST")
    smtp_port: int = int(os.getenv("SMTP_PORT", "587"))
    smtp_user: Optional[str] = os.getenv("SMTP_USER")
    smtp_password: Optional[str] = os.getenv("SMTP_PASSWORD")
    smtp_starttls: bool = os.getenv("SMTP_STARTTLS", "true").lower() in ("1", "true", "yes")
    # Currency rates
    currency_api_url: str = os.getenv("CURRENCY_API_URL", "https://api.exchangerate.host/latest?base=USD")
    currency_cache_ttl_seconds: int = int(os.getenv("CURRENCY_CACHE_TTL", "3600"))


load_dotenv()
settings = Settings()


# ----------------------------------------------------------------------------
# Utilities: Response Envelope
# ----------------------------------------------------------------------------


class Envelope(BaseModel):
    success: bool
    code: str
    message: str
    data: Any
    trace_id: str


def make_response(success: bool, code: str, message: str, data: Any = None, trace_id: Optional[str] = None, http_status: int = 200) -> JSONResponse:
    if trace_id is None:
        trace_id = str(uuid.uuid4())
    env = Envelope(success=success, code=code, message=message, data=data or {}, trace_id=trace_id)
    return JSONResponse(status_code=http_status, content=env.dict())


# ----------------------------------------------------------------------------
# Passwords & JWT
# ----------------------------------------------------------------------------


pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")


def hash_password(password: str) -> str:
    return pwd_context.hash(password)


def verify_password(password: str, password_hash: str) -> bool:
    return pwd_context.verify(password, password_hash)


def create_jwt_token(subject: str, token_type: Literal["access", "refresh"], expires_delta: Optional[timedelta] = None) -> str:
    now = datetime.now(timezone.utc)
    if expires_delta is None:
        if token_type == "access":
            expires_delta = timedelta(minutes=settings.access_token_expires_minutes)
        else:
            expires_delta = timedelta(days=settings.refresh_token_expires_days)
    payload = {
        "sub": subject,
        "type": token_type,
        "iat": int(now.timestamp()),
        "exp": int((now + expires_delta).timestamp()),
        "jti": str(uuid.uuid4()),
    }
    return jwt.encode(payload, settings.jwt_secret, algorithm=settings.jwt_algorithm)


def decode_jwt(token: str) -> Dict[str, Any]:
    try:
        return jwt.decode(token, settings.jwt_secret, algorithms=[settings.jwt_algorithm])
    except jwt.ExpiredSignatureError:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Token expired")
    except jwt.InvalidTokenError:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Invalid token")


# ----------------------------------------------------------------------------
# Models
# ----------------------------------------------------------------------------


class User(Document):
    name: str
    email: Indexed(EmailStr, unique=True)  # type: ignore
    phone: Optional[str] = None
    country: Optional[str] = None
    currency: Optional[str] = None
    password_hash: str
    referral_code: Indexed(str, unique=True)  # type: ignore
    parent_referrer: Optional[str] = None  # user id
    binary_parent: Optional[str] = None  # user id
    left_child: Optional[str] = None  # user id
    right_child: Optional[str] = None  # user id
    status: Literal["pending", "active", "suspended"] = "pending"
    activation_expires_at: Optional[datetime] = None
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    is_admin: bool = False

    class Settings:
        name = "users"


class Payment(Document):
    user_id: str
    gateway: Literal["pesapal", "paypal"]
    amount_usd: float
    amount_local: Optional[float] = None
    currency: Optional[str] = None
    status: Literal["initiated", "pending", "confirmed", "failed", "reversed"] = "initiated"
    reference: Optional[str] = None
    webhook_event_id: Optional[str] = None
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

    class Settings:
        name = "payments"


class Commission(Document):
    user_id: str
    source_user_id: str
    level: int
    amount_usd: float
    percent: float
    description: Optional[str] = None
    transaction_id: Optional[str] = None
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

    class Settings:
        name = "commissions"


class Notification(Document):
    user_id: Optional[str] = None
    type: Literal["payment", "referral", "task", "system"]
    title: str
    body: str
    data: Dict[str, Any] = Field(default_factory=dict)
    is_read: bool = False
    channels: List[Literal["email", "dashboard", "websocket"]] = Field(default_factory=lambda: ["email", "dashboard", "websocket"])
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

    class Settings:
        name = "notifications"


class Task(Document):
    title: str
    description: Optional[str] = None
    type: Literal["video", "survey", "form"]
    reward_usd: float
    broadcast_to: Optional[List[str]] = None  # segments
    attachments: Optional[List[str]] = None
    expires_at: Optional[datetime] = None
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))
    status: Literal["pending", "approved", "rejected"] = "pending"

    class Settings:
        name = "tasks"


class TaskSubmission(Document):
    task_id: str
    user_id: str
    payload: Dict[str, Any]
    status: Literal["submitted", "approved", "rejected"] = "submitted"
    reward_granted: bool = False
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

    class Settings:
        name = "task_submissions"


class PayoutRequest(Document):
    user_id: str
    amount_usd: float
    gateway: Literal["pesapal", "paypal"]
    destination: str
    status: Literal["pending", "approved", "rejected", "sent", "failed"] = "pending"
    admin_note: Optional[str] = None
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

    class Settings:
        name = "payout_requests"


class FraudLog(Document):
    user_id: Optional[str] = None
    action: str
    reason: str
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

    class Settings:
        name = "fraud_logs"


# ----------------------------------------------------------------------------
# App init & DB
# ----------------------------------------------------------------------------


app = FastAPI(title=settings.app_name)


app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.allowed_origins,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


@app.on_event("startup")
async def on_startup() -> None:
    client = AsyncIOMotorClient(settings.mongo_uri)
    await init_beanie(database=client.get_default_database(), document_models=[
        User,
        Payment,
        Commission,
        Notification,
        Task,
        TaskSubmission,
        PayoutRequest,
        FraudLog,
    ])


# ----------------------------------------------------------------------------
# Middleware: attach trace_id to request state
# ----------------------------------------------------------------------------


@app.middleware("http")
async def add_trace_id(request: Request, call_next):
    trace_id = request.headers.get("X-Trace-Id", str(uuid.uuid4()))
    request.state.trace_id = trace_id
    try:
        response = await call_next(request)
    except HTTPException as e:
        return make_response(False, "HTTP_ERROR", str(e.detail), http_status=e.status_code, trace_id=trace_id)
    except Exception as e:  # noqa: BLE001
        return make_response(False, "INTERNAL_ERROR", "Internal server error", http_status=500, trace_id=trace_id)
    response.headers["X-Trace-Id"] = trace_id
    return response


# ----------------------------------------------------------------------------
# Auth dependencies
# ----------------------------------------------------------------------------


class TokenPair(BaseModel):
    access_token: str
    refresh_token: str
    token_type: str = "bearer"


async def get_current_user(request: Request) -> User:
    auth = request.headers.get("Authorization", "")
    if not auth.startswith("Bearer "):
        raise HTTPException(status_code=401, detail="Missing bearer token")
    token = auth.split(" ", 1)[1]
    payload = decode_jwt(token)
    if payload.get("type") != "access":
        raise HTTPException(status_code=401, detail="Invalid token type")
    user_id = payload.get("sub")
    user = await User.get(user_id)
    if user is None:
        raise HTTPException(status_code=401, detail="User not found")
    if user.status == "suspended":
        raise HTTPException(status_code=403, detail="Account suspended")
    return user


async def get_admin_user(user: User = Depends(get_current_user)) -> User:
    if not user.is_admin:
        raise HTTPException(status_code=403, detail="Admin required")
    return user


async def get_active_user(user: User = Depends(get_current_user)) -> User:
    if user.status != "active":
        # 403 with machine-readable code and suggested next action
        raise HTTPException(status_code=403, detail="ACCOUNT_INACTIVE")
    return user


# ----------------------------------------------------------------------------
# Email + Notification + WebSocket (stubs)
# ----------------------------------------------------------------------------


class EmailMessage(BaseModel):
    to: EmailStr
    subject: str
    body: str


async def send_email_stub(message: EmailMessage) -> None:
    # If SMTP settings provided, send via SMTP; otherwise, simulate
    if settings.smtp_host and settings.smtp_user and settings.smtp_password:
        loop = asyncio.get_event_loop()
        def _send():
            msg = MIMEText(message.body, _subtype="plain", _charset="utf-8")
            msg["Subject"] = message.subject
            msg["From"] = settings.email_from
            msg["To"] = message.to
            with smtplib.SMTP(settings.smtp_host, settings.smtp_port) as server:
                if settings.smtp_starttls:
                    server.starttls()
                server.login(settings.smtp_user, settings.smtp_password)
                server.sendmail(settings.email_from, [message.to], msg.as_string())
        await loop.run_in_executor(None, _send)
    else:
        await asyncio.sleep(0.01)


class ConnectionManager:
    def __init__(self) -> None:
        self.user_connections: Dict[str, List[WebSocket]] = {}

    async def connect(self, user_id: str, websocket: WebSocket) -> None:
        await websocket.accept()
        self.user_connections.setdefault(user_id, []).append(websocket)

    def disconnect(self, user_id: str, websocket: WebSocket) -> None:
        conns = self.user_connections.get(user_id, [])
        if websocket in conns:
            conns.remove(websocket)
        if not conns and user_id in self.user_connections:
            del self.user_connections[user_id]

    async def send_to_user(self, user_id: str, message: Dict[str, Any]) -> None:
        for ws in self.user_connections.get(user_id, []):
            if ws.client_state == WebSocketState.CONNECTED:
                await ws.send_json(message)

    async def broadcast(self, message: Dict[str, Any]) -> None:
        for user_id in list(self.user_connections.keys()):
            await self.send_to_user(user_id, message)


ws_manager = ConnectionManager()


async def create_notification(user_id: Optional[str], ntype: Literal["payment", "referral", "task", "system"], title: str, body: str, data: Optional[Dict[str, Any]] = None, channels: Optional[List[str]] = None) -> Notification:
    notif = Notification(user_id=user_id, type=ntype, title=title, body=body, data=data or {}, channels=channels or ["email", "dashboard", "websocket"])
    await notif.insert()
    # WebSocket push
    if user_id:
        await ws_manager.send_to_user(user_id, {"type": ntype, "title": title, "body": body, "data": notif.data, "id": str(notif.id)})
    else:
        await ws_manager.broadcast({"type": ntype, "title": title, "body": body, "data": notif.data})
    # Email channel
    if user_id and ("email" in notif.channels):
        u = await User.get(user_id)
        if u:
            await send_email_stub(EmailMessage(to=u.email, subject=title, body=body))
    return notif


# ----------------------------------------------------------------------------
# Referral & Commission Logic (stubs with simple placement)
# ----------------------------------------------------------------------------


def generate_referral_code(name: str) -> str:
    base = (name.split(" ")[0] if name else "user").lower()
    return f"{base}-{uuid.uuid4().hex[:8]}"


async def place_in_binary_tree(new_user: User, parent: User) -> None:
    # Simple BFS-like placement under the referrer
    queue: List[User] = [parent]
    while queue:
        node = queue.pop(0)
        if node.left_child is None:
            node.left_child = str(new_user.id)
            await node.save()
            new_user.binary_parent = str(node.id)
            await new_user.save()
            return
        if node.right_child is None:
            node.right_child = str(new_user.id)
            await node.save()
            new_user.binary_parent = str(node.id)
            await new_user.save()
            return
        # fetch children if exist
        left = await User.get(node.left_child) if node.left_child else None
        right = await User.get(node.right_child) if node.right_child else None
        if left:
            queue.append(left)
        if right:
            queue.append(right)


async def record_commissions_for_referral(new_user: User, parent: User, trace_id: str) -> None:
    # Level-based decaying commission distribution upwards
    level = 1
    current = parent
    while current and level <= 10:  # cap at 10 for practicality
        percent = max(0.0, settings.commission_base_percent - settings.commission_decay_percent * (level - 1))
        if percent <= 0:
            break
        amount = round(settings.activation_fee_usd * (percent / 100.0), 2)
        comm = Commission(
            user_id=str(current.id),
            source_user_id=str(new_user.id),
            level=level,
            amount_usd=amount,
            percent=percent,
            description=f"Level {level} referral commission from {new_user.email}",
            transaction_id=trace_id,
        )
        await comm.insert()
        # notify earner
        await create_notification(str(current.id), "referral", "Referral commission earned", f"You earned ${amount} from a level {level} referral.")
        # move up
        current = await User.get(current.parent_referrer) if current.parent_referrer else None
        level += 1


# ----------------------------------------------------------------------------
# Currency & Conversion (auto-detect + live rates with cache)
# ----------------------------------------------------------------------------


_rates_cache: Dict[str, Any] = {"fetched_at": 0, "rates": {"USD": 1.0}}

# Basic country→currency mapping (ISO 3166-1 alpha-2 → ISO 4217)
COUNTRY_TO_CURRENCY: Dict[str, str] = {
    # Africa (sample/common)
    "KE": "KES", "UG": "UGX", "TZ": "TZS", "RW": "RWF", "BI": "BIF", "ET": "ETB",
    "NG": "NGN", "GH": "GHS", "ZA": "ZAR", "ZM": "ZMW", "MW": "MWK", "BW": "BWP",
    "CM": "XAF", "SN": "XOF", "CI": "XOF", "ML": "XOF", "NE": "XOF", "BF": "XOF",
    "TG": "XOF", "BJ": "XOF", "GN": "GNF", "LR": "LRD", "SL": "SLL",
    # Europe (common)
    "GB": "GBP", "IE": "EUR", "FR": "EUR", "DE": "EUR", "IT": "EUR", "ES": "EUR", "NL": "EUR",
    "BE": "EUR", "PT": "EUR", "FI": "EUR", "AT": "EUR", "GR": "EUR", "PL": "PLN", "RO": "RON",
    # Americas
    "US": "USD", "CA": "CAD", "MX": "MXN", "BR": "BRL", "AR": "ARS",
    # Asia
    "AE": "AED", "SA": "SAR", "IN": "INR", "PK": "PKR", "CN": "CNY", "JP": "JPY", "KR": "KRW",
}


def detect_currency_code_from_country(country: Optional[str]) -> Optional[str]:
    if not country:
        return None
    code = country.strip().upper()
    # accept 2-letter codes; if longer names given, try simple heuristics
    if len(code) == 2 and code.isalpha():
        return COUNTRY_TO_CURRENCY.get(code)
    # heuristic: map common country names to codes
    name = code.replace(" ", "")
    aliases = {
        "KENYA": "KE", "UGANDA": "UG", "TANZANIA": "TZ", "RWANDA": "RW", "BURUNDI": "BI",
        "ETHIOPIA": "ET", "NIGERIA": "NG", "GHANA": "GH", "SOUTHAFRICA": "ZA",
        "UNITEDSTATES": "US", "USA": "US", "UNITEDKINGDOM": "GB", "UK": "GB",
        "GERMANY": "DE", "FRANCE": "FR", "ITALY": "IT", "SPAIN": "ES",
        "UAE": "AE", "SAUDIARABIA": "SA", "INDIA": "IN", "PAKISTAN": "PK",
        "CHINA": "CN", "JAPAN": "JP", "KOREA": "KR",
    }
    c2 = aliases.get(name)
    return COUNTRY_TO_CURRENCY.get(c2) if c2 else None


def normalize_currency_code(currency: Optional[str], fallback_country: Optional[str] = None) -> str:
    if currency and isinstance(currency, str) and len(currency.strip()) >= 3:
        code = currency.strip().upper()
        if len(code) == 3 and code.isalpha():
            return code
    detected = detect_currency_code_from_country(fallback_country)
    return detected or "USD"


async def _fetch_rates_if_needed() -> None:
    now = time.time()
    if now - _rates_cache.get("fetched_at", 0) < settings.currency_cache_ttl_seconds:
        return
    try:
        with urllib.request.urlopen(settings.currency_api_url, timeout=10) as resp:
            data = json.loads(resp.read().decode("utf-8"))
            rates = data.get("rates") or {}
            if isinstance(rates, dict) and rates:
                rates["USD"] = 1.0
                _rates_cache["rates"] = {k.upper(): float(v) for k, v in rates.items()}
                _rates_cache["fetched_at"] = now
    except Exception:
        # keep old cache on failure
        pass


async def usd_to_local(amount_usd: float, currency: Optional[str]) -> Optional[float]:
    if not currency:
        return None
    currency_code = currency.upper()
    await _fetch_rates_if_needed()
    rate = _rates_cache["rates"].get(currency_code)
    if rate is None:
        return None
    return round(amount_usd * rate, 2)


# ----------------------------------------------------------------------------
# Schemas
# ----------------------------------------------------------------------------


class RegisterRequest(BaseModel):
    name: str
    email: EmailStr
    phone: Optional[str] = None
    country: Optional[str] = None
    currency: Optional[str] = None
    password: str
    referral_code: Optional[str] = None


class LoginRequest(BaseModel):
    email: EmailStr
    password: str


class RefreshRequest(BaseModel):
    refresh_token: str


class PaymentInitiateRequest(BaseModel):
    gateway: Literal["pesapal", "paypal"]


class PaymentWebhook(BaseModel):
    event_id: str
    reference: Optional[str] = None
    status: Literal["confirmed", "failed", "reversed"]
    amount_usd: Optional[float] = None
    user_id: Optional[str] = None


class TaskCreateRequest(BaseModel):
    title: str
    description: Optional[str] = None
    type: Literal["video", "survey", "form"]
    reward_usd: float
    broadcast_to: Optional[List[str]] = None
    attachments: Optional[List[str]] = None
    expires_at: Optional[datetime] = None


class TaskSubmitRequest(BaseModel):
    payload: Dict[str, Any]


class PayoutRequestCreate(BaseModel):
    amount_usd: float
    gateway: Literal["pesapal", "paypal"]
    destination: str


class MarkReadRequest(BaseModel):
    notification_id: str


class BroadcastEmailRequest(BaseModel):
    subject: str
    body: str
    to_all: bool = True
    user_ids: Optional[List[str]] = None


# ----------------------------------------------------------------------------
# Auth & Users
# ----------------------------------------------------------------------------


@app.post("/auth/register")
async def register(req: RegisterRequest, request: Request, background: BackgroundTasks):
    trace_id = request.state.trace_id
    # Fraud checks (stubs)
    existing = await User.find(User.email == req.email).first_or_none()
    if existing:
        await FraudLog(user_id=str(existing.id), action="register", reason="duplicate_email").insert()
        return make_response(False, "EMAIL_EXISTS", "Email already registered", http_status=400, trace_id=trace_id)
    if req.phone:
        existing_phone = await User.find(User.phone == req.phone).first_or_none()
        if existing_phone:
            await FraudLog(user_id=str(existing_phone.id), action="register", reason="duplicate_phone").insert()
            return make_response(False, "PHONE_EXISTS", "Phone already registered", http_status=400, trace_id=trace_id)

    referral_code = generate_referral_code(req.name)
    user = User(
        name=req.name,
        email=req.email,
        phone=req.phone,
        country=req.country,
        currency=normalize_currency_code(req.currency, req.country),
        password_hash=hash_password(req.password),
        referral_code=referral_code,
        status="pending",
    )
    # parent referrer by referral code
    parent = None
    if req.referral_code:
        parent = await User.find(User.referral_code == req.referral_code).first_or_none()
        if parent:
            user.parent_referrer = str(parent.id)
    await user.insert()

    # if parent exists, place in binary tree and notify
    if parent:
        await place_in_binary_tree(user, parent)
        background.add_task(create_notification, str(parent.id), "referral", "New referral joined", f"{user.name} joined using your link.")

    # Send welcome email (stub) and notify user
    background.add_task(send_email_stub, EmailMessage(to=user.email, subject="Welcome", body="Welcome to the platform!"))
    await create_notification(str(user.id), "system", "Welcome", "Your account was created. Activate to start earning.")

    data = {
        "user_id": str(user.id),
        "referral_link": f"{settings.frontend_base_url}/register?ref={user.referral_code}",
    }
    return make_response(True, "USER_REGISTERED", "Registration successful", data=data, trace_id=trace_id, http_status=201)


@app.post("/auth/login")
async def login(req: LoginRequest, request: Request):
    trace_id = request.state.trace_id
    user = await User.find(User.email == req.email).first_or_none()
    if not user or not verify_password(req.password, user.password_hash):
        await FraudLog(action="login", reason="invalid_credentials", user_id=str(user.id) if user else None).insert()
        return make_response(False, "INVALID_CREDENTIALS", "Invalid email or password", http_status=401, trace_id=trace_id)

    access = create_jwt_token(str(user.id), "access")
    refresh = create_jwt_token(str(user.id), "refresh")
    data = TokenPair(access_token=access, refresh_token=refresh).dict()
    await create_notification(str(user.id), "system", "Login", "You have logged in successfully.")
    return make_response(True, "LOGIN_SUCCESS", "Login successful", data=data, trace_id=trace_id)


@app.post("/auth/refresh")
async def refresh_token(req: RefreshRequest, request: Request):
    trace_id = request.state.trace_id
    payload = decode_jwt(req.refresh_token)
    if payload.get("type") != "refresh":
        return make_response(False, "INVALID_TOKEN", "Invalid refresh token", http_status=401, trace_id=trace_id)
    user_id = payload.get("sub")
    user = await User.get(user_id)
    if not user:
        return make_response(False, "USER_NOT_FOUND", "User not found", http_status=404, trace_id=trace_id)
    access = create_jwt_token(str(user.id), "access")
    refresh = create_jwt_token(str(user.id), "refresh")
    data = TokenPair(access_token=access, refresh_token=refresh).dict()
    return make_response(True, "TOKEN_REFRESHED", "Token refreshed", data=data, trace_id=trace_id)


@app.get("/users/me")
async def get_me(user: User = Depends(get_current_user), request: Request = None):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    data = {
        "id": str(user.id),
        "name": user.name,
        "email": user.email,
        "phone": user.phone,
        "country": user.country,
        "currency": user.currency,
        "status": user.status,
        "referral_code": user.referral_code,
        "referral_link": f"{settings.frontend_base_url}/register?ref={user.referral_code}",
        "activation_expires_at": user.activation_expires_at,
        "created_at": user.created_at,
    }
    return make_response(True, "PROFILE", "Profile fetched", data=data, trace_id=trace_id)


@app.get("/referrals/link")
async def referral_link(user: User = Depends(get_active_user), request: Request = None):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    link = f"{settings.frontend_base_url}/register?ref={user.referral_code}"
    return make_response(True, "REFERRAL_LINK", "Referral link", data={"link": link}, trace_id=trace_id)


@app.get("/referrals/tree")
async def referral_tree(user: User = Depends(get_active_user), request: Request = None):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())

    async def build(node_id: Optional[str], depth: int = 0, max_depth: int = 5) -> Optional[Dict[str, Any]]:
        if not node_id or depth > max_depth:
            return None
        node = await User.get(node_id)
        if not node:
            return None
        return {
            "id": node_id,
            "name": node.name,
            "left": await build(node.left_child, depth + 1, max_depth),
            "right": await build(node.right_child, depth + 1, max_depth),
        }

    tree = await build(str(user.id), 0, 5)
    return make_response(True, "REFERRAL_TREE", "Referral tree", data=tree, trace_id=trace_id)


# ----------------------------------------------------------------------------
# Payments
# ----------------------------------------------------------------------------


@app.post("/payments/activate/initiate")
async def initiate_activation(req: PaymentInitiateRequest, user: User = Depends(get_current_user), request: Request = None):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    # Create payment record
    pay = Payment(
        user_id=str(user.id),
        gateway=req.gateway,
        amount_usd=settings.activation_fee_usd,
        currency=user.currency or "USD",
        status="initiated",
        reference=str(uuid.uuid4()),
    )
    pay.amount_local = await usd_to_local(pay.amount_usd, pay.currency)
    await pay.insert()
    # Email user with checkout details
    await send_email_stub(EmailMessage(to=user.email, subject="Activation payment initiated", body=f"Use the checkout link to pay: https://payments.example/{req.gateway}/checkout/{pay.reference}"))
    data = {
        "payment_id": str(pay.id),
        "gateway": req.gateway,
        "amount_usd": pay.amount_usd,
        "amount_local": pay.amount_local,
        "currency": pay.currency,
        "reference": pay.reference,
        # In real life, return redirect/initiation URLs/tokens here
        "checkout_url": f"https://payments.example/{req.gateway}/checkout/{pay.reference}",
    }
    return make_response(True, "PAYMENT_INITIATED", "Activation payment initiated", data=data, trace_id=trace_id, http_status=201)


async def _process_payment_event(gateway: str, event: PaymentWebhook, request: Request) -> JSONResponse:
    trace_id = request.state.trace_id
    # idempotency: ensure event not processed
    existing = await Payment.find(Payment.webhook_event_id == event.event_id).first_or_none()
    if existing:
        return make_response(True, "WEBHOOK_ALREADY_PROCESSED", "Event already processed", data={"payment_id": str(existing.id)}, trace_id=trace_id)

    # Lookup payment by reference if provided
    pay = None
    if event.reference:
        pay = await Payment.find(Payment.reference == event.reference).first_or_none()
    if not pay and event.user_id:
        # Fallback: find most recent initiated for user
        pay = await Payment.find(Payment.user_id == event.user_id, Payment.status.in_(["initiated", "pending"]))\
            .sort("-created_at").first_or_none()
    if not pay:
        await FraudLog(action="payment_webhook", reason="payment_not_found", user_id=event.user_id).insert()
        return make_response(False, "PAYMENT_NOT_FOUND", "Payment not found", http_status=404, trace_id=trace_id)

    pay.webhook_event_id = event.event_id
    pay.status = event.status
    if event.amount_usd is not None:
        pay.amount_usd = event.amount_usd
    await pay.save()

    user = await User.get(pay.user_id)
    if not user:
        return make_response(False, "USER_NOT_FOUND", "User not found", http_status=404, trace_id=trace_id)

    if event.status == "confirmed":
        user.status = "active"
        user.activation_expires_at = datetime.now(timezone.utc) + timedelta(days=30 * 5)
        await user.save()
        await create_notification(str(user.id), "payment", "Payment confirmed", f"Your {gateway} payment is confirmed. Account activated.")
        # Email confirmation
        await send_email_stub(EmailMessage(to=user.email, subject="Payment confirmed", body="Your payment was confirmed and your account is now active."))
        # Commission distribution to uplines when activation happens
        if user.parent_referrer:
            parent = await User.get(user.parent_referrer)
            if parent:
                await record_commissions_for_referral(user, parent, trace_id)
        code = "PAYMENT_CONFIRMED"
        msg = "Payment confirmed"
    elif event.status == "failed":
        await create_notification(str(user.id), "payment", "Payment failed", f"Your {gateway} payment failed.")
        await send_email_stub(EmailMessage(to=user.email, subject="Payment failed", body="Your payment failed. Please try again."))
        code = "PAYMENT_FAILED"
        msg = "Payment failed"
    else:  # reversed
        await create_notification(str(user.id), "payment", "Payment reversed", f"Your {gateway} payment was reversed.")
        await send_email_stub(EmailMessage(to=user.email, subject="Payment reversed", body="Your payment was reversed. If this wasn't expected, contact support."))
        code = "PAYMENT_REVERSED"
        msg = "Payment reversed"

    return make_response(True, code, msg, data={"payment_id": str(pay.id)}, trace_id=trace_id)


@app.post("/payments/webhook/pesapal")
async def pesapal_webhook(event: PaymentWebhook, request: Request):
    return await _process_payment_event("pesapal", event, request)


@app.post("/payments/webhook/paypal")
async def paypal_webhook(event: PaymentWebhook, request: Request):
    return await _process_payment_event("paypal", event, request)


# ----------------------------------------------------------------------------
# Commissions & Balance
# ----------------------------------------------------------------------------


@app.get("/commissions")
async def list_commissions(user: User = Depends(get_active_user), request: Request = None, skip: int = 0, limit: int = 50):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    items = await Commission.find(Commission.user_id == str(user.id)).sort("-created_at").skip(skip).limit(limit).to_list()
    data = [{
        "id": str(c.id),
        "source_user_id": c.source_user_id,
        "level": c.level,
        "amount_usd": c.amount_usd,
        "percent": c.percent,
        "description": c.description,
        "created_at": c.created_at,
    } for c in items]
    return make_response(True, "COMMISSIONS", "Commissions list", data=data, trace_id=trace_id)


@app.get("/balance")
async def balance(user: User = Depends(get_active_user), request: Request = None):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    commissions = await Commission.find(Commission.user_id == str(user.id)).to_list()
    total_earned = round(sum(c.amount_usd for c in commissions), 2)
    # Sum approved payout amounts
    payouts = await PayoutRequest.find(PayoutRequest.user_id == str(user.id), PayoutRequest.status.in_(["sent"]))\
        .to_list()
    total_withdrawn = round(sum(p.amount_usd for p in payouts), 2)
    wallet_usd = round(total_earned - total_withdrawn, 2)
    wallet_local = await usd_to_local(wallet_usd, user.currency)
    data = {"wallet_usd": wallet_usd, "wallet_local": wallet_local, "currency": user.currency}
    return make_response(True, "BALANCE", "Wallet balance", data=data, trace_id=trace_id)


# ----------------------------------------------------------------------------
# Transactions (history)
# ----------------------------------------------------------------------------


@app.get("/transactions")
async def transactions(user: User = Depends(get_active_user), request: Request = None, skip: int = 0, limit: int = 50, include_payments: bool = False):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    user_id = str(user.id)

    # Gather commissions (credits)
    comms = await Commission.find(Commission.user_id == user_id).to_list()
    comm_items = [{
        "id": str(c.id),
        "type": "commission",
        "direction": "credit",
        "amount_usd": round(c.amount_usd, 2),
        "description": c.description or f"Level {c.level} commission",
        "created_at": c.created_at,
    } for c in comms]

    # Gather payouts (debits) — only sent affect balance
    payouts = await PayoutRequest.find(PayoutRequest.user_id == user_id, PayoutRequest.status == "sent").to_list()
    payout_items = [{
        "id": str(p.id),
        "type": "payout",
        "direction": "debit",
        "amount_usd": -round(p.amount_usd, 2),
        "description": f"Payout via {p.gateway} to {p.destination}",
        "created_at": p.created_at,
    } for p in payouts]

    entries = comm_items + payout_items

    # Optionally include payments (informational)
    if include_payments:
        pays = await Payment.find(Payment.user_id == user_id).to_list()
        pay_items = [{
            "id": str(pay.id),
            "type": "payment",
            "direction": "info",
            "amount_usd": round(pay.amount_usd or 0.0, 2),
            "status": pay.status,
            "gateway": pay.gateway,
            "description": f"{pay.gateway.capitalize()} payment {pay.status}",
            "created_at": pay.created_at,
        } for pay in pays]
        entries += pay_items

    # Sort by created_at desc
    entries.sort(key=lambda x: x.get("created_at") or datetime.now(timezone.utc), reverse=True)

    # Pagination (in-memory for simplicity)
    sliced = entries[skip: skip + limit]
    # Coerce datetime to isoformat for JSONResponse if needed (FastAPI will handle, but be explicit)
    for e in sliced:
        if isinstance(e.get("created_at"), datetime):
            e["created_at"] = e["created_at"].isoformat()

    data = {"items": sliced, "total": len(entries), "skip": skip, "limit": limit}
    return make_response(True, "TRANSACTIONS", "Transaction history", data=data, trace_id=trace_id)


# ----------------------------------------------------------------------------
# Tasks
# ----------------------------------------------------------------------------


@app.post("/tasks")
async def create_task(req: TaskCreateRequest, admin: User = Depends(get_admin_user), request: Request = None):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    t = Task(**req.dict())
    await t.insert()
    await create_notification(None, "task", "New task posted", t.title, data={"task_id": str(t.id)})
    # Optional: email all users about the task (lightweight stub - only notifies via notification email channel when users are targeted individually)
    return make_response(True, "TASK_CREATED", "Task created", data={"task_id": str(t.id)}, trace_id=trace_id, http_status=201)


@app.get("/tasks")
async def list_tasks(user: User = Depends(get_active_user), request: Request = None, skip: int = 0, limit: int = 50):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    now = datetime.now(timezone.utc)
    items = await Task.find((Task.expires_at == None) | (Task.expires_at > now)).sort("-created_at").skip(skip).limit(limit).to_list()  # noqa: E711
    data = [{
        "id": str(t.id),
        "title": t.title,
        "type": t.type,
        "reward_usd": t.reward_usd,
        "expires_at": t.expires_at,
        "status": t.status,
    } for t in items]
    return make_response(True, "TASKS", "Tasks list", data=data, trace_id=trace_id)


@app.post("/tasks/{task_id}/submit")
async def submit_task(task_id: str = Path(...), req: TaskSubmitRequest = Body(...), user: User = Depends(get_active_user), request: Request = None):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    task = await Task.get(task_id)
    if not task:
        return make_response(False, "TASK_NOT_FOUND", "Task not found", http_status=404, trace_id=trace_id)
    sub = TaskSubmission(task_id=task_id, user_id=str(user.id), payload=req.payload)
    await sub.insert()
    await create_notification(None, "task", "Task submitted", f"User {user.email} submitted a task.", data={"task_id": task_id, "submission_id": str(sub.id)})
    # Confirmation to submitting user
    await create_notification(str(user.id), "task", "We received your submission", "Thanks! We'll review and notify you soon.", data={"task_id": task_id, "submission_id": str(sub.id)})
    return make_response(True, "TASK_SUBMITTED", "Task submitted", data={"submission_id": str(sub.id)}, trace_id=trace_id, http_status=201)


@app.post("/tasks/{task_id}/approve")
async def approve_task_submission(task_id: str, submission_id: str = Query(...), admin: User = Depends(get_admin_user), request: Request = None):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    sub = await TaskSubmission.get(submission_id)
    if not sub or sub.task_id != task_id:
        return make_response(False, "SUBMISSION_NOT_FOUND", "Submission not found", http_status=404, trace_id=trace_id)
    if sub.status == "approved":
        return make_response(True, "ALREADY_APPROVED", "Already approved", data={"submission_id": submission_id}, trace_id=trace_id)
    sub.status = "approved"
    sub.reward_granted = True
    await sub.save()
    task = await Task.get(task_id)
    # Record commission-like reward as commission to user's own wallet
    comm = Commission(
        user_id=sub.user_id,
        source_user_id=sub.user_id,
        level=0,
        amount_usd=task.reward_usd if task else 0,
        percent=0,
        description=f"Reward for task {task.title if task else task_id}",
    )
    await comm.insert()
    await create_notification(sub.user_id, "task", "Task approved", f"Your task submission was approved. Reward ${comm.amount_usd} granted.")
    return make_response(True, "TASK_APPROVED", "Submission approved", data={"submission_id": submission_id}, trace_id=trace_id)


@app.post("/tasks/{task_id}/reject")
async def reject_task_submission(task_id: str, submission_id: str = Query(...), reason: Optional[str] = Query(None), admin: User = Depends(get_admin_user), request: Request = None):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    sub = await TaskSubmission.get(submission_id)
    if not sub or sub.task_id != task_id:
        return make_response(False, "SUBMISSION_NOT_FOUND", "Submission not found", http_status=404, trace_id=trace_id)
    sub.status = "rejected"
    await sub.save()
    await create_notification(sub.user_id, "task", "Task rejected", f"Your task submission was rejected. {reason or ''}")
    return make_response(True, "TASK_REJECTED", "Submission rejected", data={"submission_id": submission_id}, trace_id=trace_id)


# ----------------------------------------------------------------------------
# Payouts
# ----------------------------------------------------------------------------


@app.post("/payouts/request")
async def request_payout(req: PayoutRequestCreate, user: User = Depends(get_active_user), request: Request = None):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    # Check balance
    commissions = await Commission.find(Commission.user_id == str(user.id)).to_list()
    payouts = await PayoutRequest.find(PayoutRequest.user_id == str(user.id), PayoutRequest.status.in_(["sent"]))\
        .to_list()
    wallet_usd = round(sum(c.amount_usd for c in commissions) - sum(p.amount_usd for p in payouts), 2)
    if req.amount_usd < settings.min_withdraw_usd:
        return make_response(False, "WITHDRAW_MINIMUM", f"Minimum withdrawal is ${settings.min_withdraw_usd}", http_status=400, trace_id=trace_id)
    if req.amount_usd <= 0 or req.amount_usd > wallet_usd:
        return make_response(False, "INSUFFICIENT_BALANCE", "Insufficient balance", http_status=400, trace_id=trace_id)
    # Rate limit: only one payout request per 7 days
    seven_days_ago = datetime.now(timezone.utc) - timedelta(days=7)
    recent = await PayoutRequest.find(PayoutRequest.user_id == str(user.id), PayoutRequest.created_at > seven_days_ago).sort("-created_at").first_or_none()
    if recent:
        return make_response(False, "PAYOUT_RATE_LIMIT", "Only one payout request is allowed per 7 days", http_status=429, trace_id=trace_id)
    pr = PayoutRequest(user_id=str(user.id), amount_usd=req.amount_usd, gateway=req.gateway, destination=req.destination)
    await pr.insert()
    await create_notification(None, "system", "Payout requested", f"User {user.email} requested payout ${req.amount_usd}.")
    # Confirmation to requester
    await create_notification(str(user.id), "system", "We received your payout request", f"Your payout request of ${req.amount_usd} is under review.")
    return make_response(True, "PAYOUT_REQUESTED", "Payout requested", data={"payout_id": str(pr.id)}, trace_id=trace_id, http_status=201)


@app.get("/payouts")
async def list_payouts(user: User = Depends(get_active_user), request: Request = None, skip: int = 0, limit: int = 50):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    query = {}
    if user.is_admin:
        items = await PayoutRequest.find_all().sort("-created_at").skip(skip).limit(limit).to_list()
    else:
        items = await PayoutRequest.find(PayoutRequest.user_id == str(user.id)).sort("-created_at").skip(skip).limit(limit).to_list()
    data = [{
        "id": str(p.id),
        "user_id": p.user_id,
        "amount_usd": p.amount_usd,
        "gateway": p.gateway,
        "destination": p.destination,
        "status": p.status,
        "created_at": p.created_at,
    } for p in items]
    return make_response(True, "PAYOUTS", "Payouts list", data=data, trace_id=trace_id)


@app.post("/payouts/{payout_id}/approve")
async def approve_payout(payout_id: str, admin: User = Depends(get_admin_user), request: Request = None):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    pr = await PayoutRequest.get(payout_id)
    if not pr:
        return make_response(False, "PAYOUT_NOT_FOUND", "Payout not found", http_status=404, trace_id=trace_id)
    pr.status = "sent"
    await pr.save()
    await create_notification(pr.user_id, "system", "Payout approved", f"Your payout of ${pr.amount_usd} has been approved and sent.")
    # Email user
    if pr.user_id:
        u = await User.get(pr.user_id)
        if u:
            await send_email_stub(EmailMessage(to=u.email, subject="Payout approved", body=f"Your payout of ${pr.amount_usd} has been sent."))
    return make_response(True, "PAYOUT_APPROVED", "Payout approved", data={"payout_id": payout_id}, trace_id=trace_id)


@app.post("/payouts/{payout_id}/reject")
async def reject_payout(payout_id: str, reason: Optional[str] = Query(None), admin: User = Depends(get_admin_user), request: Request = None):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    pr = await PayoutRequest.get(payout_id)
    if not pr:
        return make_response(False, "PAYOUT_NOT_FOUND", "Payout not found", http_status=404, trace_id=trace_id)
    pr.status = "rejected"
    pr.admin_note = reason
    await pr.save()
    await create_notification(pr.user_id, "system", "Payout rejected", f"Your payout was rejected. {reason or ''}")
    # Email user
    if pr.user_id:
        u = await User.get(pr.user_id)
        if u:
            await send_email_stub(EmailMessage(to=u.email, subject="Payout rejected", body=f"Your payout request was rejected. {reason or ''}"))
    return make_response(True, "PAYOUT_REJECTED", "Payout rejected", data={"payout_id": payout_id}, trace_id=trace_id)


# ----------------------------------------------------------------------------
# Notifications
# ----------------------------------------------------------------------------


@app.get("/notifications")
async def list_notifications(user: User = Depends(get_active_user), request: Request = None, skip: int = 0, limit: int = 50):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    items = await Notification.find(Notification.user_id == str(user.id)).sort("-created_at").skip(skip).limit(limit).to_list()
    data = [{
        "id": str(n.id),
        "type": n.type,
        "title": n.title,
        "body": n.body,
        "data": n.data,
        "is_read": n.is_read,
        "created_at": n.created_at,
    } for n in items]
    return make_response(True, "NOTIFICATIONS", "Notifications list", data=data, trace_id=trace_id)


@app.get("/notifications/unread_count")
async def unread_count(user: User = Depends(get_active_user), request: Request = None):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    count = await Notification.find(Notification.user_id == str(user.id), Notification.is_read == False).count()  # noqa: E712
    return make_response(True, "UNREAD_COUNT", "Unread notifications count", data={"count": count}, trace_id=trace_id)


@app.post("/notifications/mark_read")
async def mark_read(req: MarkReadRequest, user: User = Depends(get_active_user), request: Request = None):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    n = await Notification.get(req.notification_id)
    if not n or n.user_id != str(user.id):
        return make_response(False, "NOTIF_NOT_FOUND", "Notification not found", http_status=404, trace_id=trace_id)
    n.is_read = True
    await n.save()
    return make_response(True, "NOTIF_READ", "Notification marked read", data={"id": req.notification_id}, trace_id=trace_id)


@app.post("/notifications/mark_all_read")
async def mark_all_read(user: User = Depends(get_active_user), request: Request = None):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    items = await Notification.find(Notification.user_id == str(user.id), Notification.is_read == False).to_list()  # noqa: E712
    for n in items:
        n.is_read = True
        await n.save()
    return make_response(True, "NOTIF_ALL_READ", "All notifications marked read", data={"updated": len(items)}, trace_id=trace_id)


@app.websocket("/ws/notifications")
async def websocket_notifications(websocket: WebSocket, token: str = Query(...)):
    # Authenticate via token query param
    payload = decode_jwt(token)
    if payload.get("type") != "access":
        await websocket.close(code=4401)
        return
    user_id = payload.get("sub")
    await ws_manager.connect(user_id, websocket)
    try:
        while True:
            # keep connection alive; ignore incoming messages
            await websocket.receive_text()
    except WebSocketDisconnect:
        ws_manager.disconnect(user_id, websocket)


# ----------------------------------------------------------------------------
# Admin
# ----------------------------------------------------------------------------


@app.post("/admin/emails/send")
async def broadcast_email(req: BroadcastEmailRequest, admin: User = Depends(get_admin_user), request: Request = None, background: BackgroundTasks = None):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    targets: List[User]
    if req.to_all:
        targets = await User.find_all().to_list()
    else:
        targets = []
        if req.user_ids:
            for uid in req.user_ids:
                u = await User.get(uid)
                if u:
                    targets.append(u)
    for u in targets:
        if background:
            background.add_task(send_email_stub, EmailMessage(to=u.email, subject=req.subject, body=req.body))
    await create_notification(None, "system", "Broadcast email", f"Broadcast email sent to {len(targets)} users.")
    return make_response(True, "EMAILS_SENT", "Broadcast email queued", data={"count": len(targets)}, trace_id=trace_id)


@app.get("/admin/fraud_logs")
async def get_fraud_logs(admin: User = Depends(get_admin_user), request: Request = None, skip: int = 0, limit: int = 50):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    items = await FraudLog.find_all().sort("-created_at").skip(skip).limit(limit).to_list()
    data = [{
        "id": str(f.id),
        "user_id": f.user_id,
        "action": f.action,
        "reason": f.reason,
        "created_at": f.created_at,
    } for f in items]
    return make_response(True, "FRAUD_LOGS", "Fraud logs", data=data, trace_id=trace_id)


@app.patch("/admin/users/{user_id}/suspend")
async def suspend_user(user_id: str, suspend: bool = Query(True), admin: User = Depends(get_admin_user), request: Request = None):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    u = await User.get(user_id)
    if not u:
        return make_response(False, "USER_NOT_FOUND", "User not found", http_status=404, trace_id=trace_id)
    u.status = "suspended" if suspend else "active"
    await u.save()
    await create_notification(user_id, "system", "Account status changed", f"Your account status is now {u.status}.")
    return make_response(True, "USER_STATUS_UPDATED", "User status updated", data={"user_id": user_id, "status": u.status}, trace_id=trace_id)


@app.get("/admin/analytics")
async def analytics(admin: User = Depends(get_admin_user), request: Request = None):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    total_users = await User.find_all().count()
    active_users = await User.find(User.status == "active").count()
    total_commissions = await Commission.find_all().to_list()
    total_commissions_usd = round(sum(c.amount_usd for c in total_commissions), 2)
    total_payouts = await PayoutRequest.find(PayoutRequest.status == "sent").to_list()
    total_payouts_usd = round(sum(p.amount_usd for p in total_payouts), 2)
    data = {
        "total_users": total_users,
        "active_users": active_users,
        "total_commissions_usd": total_commissions_usd,
        "total_payouts_usd": total_payouts_usd,
    }
    return make_response(True, "ANALYTICS", "Platform KPIs", data=data, trace_id=trace_id)


# ----------------------------------------------------------------------------
# Housekeeping: activation expiry background hint (cron external in real world)
# ----------------------------------------------------------------------------


@app.post("/admin/cron/expire_accounts")
async def cron_expire_accounts(admin: User = Depends(get_admin_user), request: Request = None):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    now = datetime.now(timezone.utc)
    users = await User.find(User.activation_expires_at != None, User.activation_expires_at < now, User.status == "active").to_list()  # noqa: E711
    updated = 0
    for u in users:
        u.status = "pending"
        await u.save()
        await create_notification(str(u.id), "system", "Activation expired", "Your account activation has expired. Please renew.")
        updated += 1
    return make_response(True, "ACTIVATIONS_EXPIRED", "Processed expirations", data={"updated": updated}, trace_id=trace_id)


# ----------------------------------------------------------------------------
# Dashboard Summary (user)
# ----------------------------------------------------------------------------


@app.get("/dashboard/summary")
async def dashboard_summary(user: User = Depends(get_active_user), request: Request = None):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    user_id = str(user.id)

    # Referrals (direct)
    total_referrals = await User.find(User.parent_referrer == user_id).count()
    active_referrals = await User.find(User.parent_referrer == user_id, User.status == "active").count()

    # Tasks available
    now = datetime.now(timezone.utc)
    tasks_available = await Task.find((Task.expires_at == None) | (Task.expires_at > now)).count()  # noqa: E711

    # Task submissions breakdown
    submissions_total = await TaskSubmission.find(TaskSubmission.user_id == user_id).count()
    submitted_count = await TaskSubmission.find(TaskSubmission.user_id == user_id, TaskSubmission.status == "submitted").count()
    approved_count = await TaskSubmission.find(TaskSubmission.user_id == user_id, TaskSubmission.status == "approved").count()
    rejected_count = await TaskSubmission.find(TaskSubmission.user_id == user_id, TaskSubmission.status == "rejected").count()

    # Payouts breakdown
    payouts_total = await PayoutRequest.find(PayoutRequest.user_id == user_id).count()
    payouts_pending = await PayoutRequest.find(PayoutRequest.user_id == user_id, PayoutRequest.status == "pending").count()
    payouts_sent = await PayoutRequest.find(PayoutRequest.user_id == user_id, PayoutRequest.status == "sent").count()
    payouts_rejected = await PayoutRequest.find(PayoutRequest.user_id == user_id, PayoutRequest.status == "rejected").count()

    # Totals
    total_commission_usd = 0.0
    for c in await Commission.find(Commission.user_id == user_id).to_list():
        total_commission_usd += c.amount_usd
    total_payouts_usd = 0.0
    for p in await PayoutRequest.find(PayoutRequest.user_id == user_id, PayoutRequest.status == "sent").to_list():
        total_payouts_usd += p.amount_usd
    wallet_usd = round(total_commission_usd - total_payouts_usd, 2)
    wallet_local = await usd_to_local(wallet_usd, user.currency)

    data = {
        "referrals": {
            "total": total_referrals,
            "active": active_referrals,
        },
        "tasks": {
            "available": tasks_available,
            "submissions": {
                "total": submissions_total,
                "submitted": submitted_count,
                "approved": approved_count,
                "rejected": rejected_count,
                "completed": approved_count,  # completed interpreted as approved
                "pending_review": submitted_count,
            },
        },
        "payouts": {
            "total": payouts_total,
            "pending": payouts_pending,
            "sent": payouts_sent,
            "rejected": payouts_rejected,
            "total_sent_usd": round(total_payouts_usd, 2),
        },
        "wallet": {
            "usd": wallet_usd,
            "local": wallet_local,
            "currency": user.currency,
        },
    }
    return make_response(True, "DASHBOARD_SUMMARY", "Dashboard summary", data=data, trace_id=trace_id)


# ----------------------------------------------------------------------------
# Dashboard Charts & Growth (user)
# ----------------------------------------------------------------------------


async def _collect_downline_by_level(root_user_id: str, max_levels: int = 10) -> Dict[int, int]:
    counts: Dict[int, int] = {}
    current_level_ids: List[str] = [root_user_id]
    visited: set[str] = set([root_user_id])
    for level in range(1, max_levels + 1):
        next_level_ids: List[str] = []
        for uid in current_level_ids:
            u = await User.get(uid)
            if not u:
                continue
            for child_id in [u.left_child, u.right_child]:
                if child_id and child_id not in visited:
                    visited.add(child_id)
                    next_level_ids.append(child_id)
        counts[level] = len(next_level_ids)
        current_level_ids = next_level_ids
        if not current_level_ids:
            break
    return counts


def _date_key(dt: datetime) -> str:
    d = dt.astimezone(timezone.utc).date()
    return d.isoformat()


@app.get("/dashboard/charts")
async def dashboard_charts(days: int = Query(30, ge=1, le=180), user: User = Depends(get_active_user), request: Request = None):
    trace_id = request.state.trace_id if request else str(uuid.uuid4())
    user_id = str(user.id)
    end = datetime.now(timezone.utc)
    start = end - timedelta(days=days - 1)

    # Prepare date buckets
    date_buckets = [(start + timedelta(days=i)).date().isoformat() for i in range(days)]
    zeros = {d: 0 for d in date_buckets}

    # Referrals over time (direct)
    referrals = await User.find(User.parent_referrer == user_id).to_list()
    ref_series = zeros.copy()
    for r in referrals:
        dk = _date_key(r.created_at)
        if dk in ref_series:
            ref_series[dk] += 1

    # Commissions over time (sum USD)
    comms = await Commission.find(Commission.user_id == user_id).to_list()
    comm_series = {d: 0.0 for d in date_buckets}
    for c in comms:
        dk = _date_key(c.created_at)
        if dk in comm_series:
            comm_series[dk] += float(c.amount_usd)

    # Tasks submissions over time broken down
    subs = await TaskSubmission.find(TaskSubmission.user_id == user_id).to_list()
    task_series = {
        "submitted": zeros.copy(),
        "approved": zeros.copy(),
        "rejected": zeros.copy(),
    }
    for s in subs:
        dk = _date_key(s.created_at)
        if dk in task_series.get(s.status, {}):
            task_series[s.status][dk] += 1

    # Downline by level (binary tree breadth)
    downline = await _collect_downline_by_level(user_id, max_levels=10)

    # Binary position metrics
    depth_from_root = 0
    cursor = await User.get(user.binary_parent) if user.binary_parent else None
    while cursor is not None and depth_from_root < 1000:
        depth_from_root += 1
        cursor = await User.get(cursor.binary_parent) if cursor.binary_parent else None

    data = {
        "dates": date_buckets,
        "referrals_over_time": [ref_series[d] for d in date_buckets],
        "commissions_over_time_usd": [round(comm_series[d], 2) for d in date_buckets],
        "tasks_over_time": {
            "submitted": [task_series["submitted"][d] for d in date_buckets],
            "approved": [task_series["approved"][d] for d in date_buckets],
            "rejected": [task_series["rejected"][d] for d in date_buckets],
        },
        "downline_by_level": downline,  # {level: count}
        "binary_position": {
            "depth_from_root": depth_from_root,
        },
    }
    return make_response(True, "DASHBOARD_CHARTS", "Dashboard chart data", data=data, trace_id=trace_id)


# ----------------------------------------------------------------------------
# Root
# ----------------------------------------------------------------------------


@app.get("/")
async def root(request: Request):
    trace_id = request.state.trace_id
    return make_response(True, "OK", "Service is up", data={"name": settings.app_name}, trace_id=trace_id)


