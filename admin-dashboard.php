<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard - Platform Manager</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <style>
    :root {
      --bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      --panel: #ffffff;
      --text: #1f2937;
      --muted: #6b7280;
      --primary: #4f46e5;
      --secondary: #7c3aed;
      --border: #e5e7eb;
      --green: #059669;
      --red: #dc2626;
      --yellow: #d97706;
      --blue: #2563eb;
      --orange: #ea580c;
      --purple: #9333ea;
      --pink: #db2777;
      --success: #10b981;
      --warning: #f59e0b;
      --error: #ef4444;
      --info: #3b82f6;
      --shadow: 0 10px 25px rgba(0,0,0,0.1);
      --shadow-hover: 0 20px 40px rgba(0,0,0,0.15);
    }

    * {
      box-sizing: border-box;
      transition: all 0.3s ease;
    }

    body {
      margin: 0;
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      overflow-x: hidden;
    }

    .container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 20px;
      animation: fadeInUp 0.6s ease-out;
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .card {
      background: var(--panel);
      border: 1px solid var(--border);
      border-radius: 20px;
      box-shadow: var(--shadow);
      margin-bottom: 24px;
      overflow: hidden;
      transition: all 0.3s ease;
    }

    .card:hover {
      box-shadow: var(--shadow-hover);
      transform: translateY(-2px);
    }

    .card-header {
      padding: 20px 24px;
      border-bottom: 1px solid var(--border);
      font-weight: 700;
      font-size: 18px;
      background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
      color: var(--text);
    }

    .card-body {
      padding: 24px;
    }

    .row {
      display: flex;
      gap: 16px;
      flex-wrap: wrap;
    }

    .col {
      flex: 1;
      min-width: 220px;
    }

    label {
      display: block;
      font-size: 14px;
      color: var(--muted);
      margin-bottom: 8px;
      font-weight: 500;
    }

    input[type="text"], input[type="number"], input[type="email"], select, textarea {
      width: 100%;
      padding: 12px 16px;
      border: 2px solid var(--border);
      border-radius: 12px;
      outline: none;
      font: inherit;
      background: #fff;
      transition: all 0.3s ease;
      font-size: 14px;
    }

    input:focus, select:focus, textarea:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    textarea {
      min-height: 100px;
      resize: vertical;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 12px 20px;
      border-radius: 12px;
      border: none;
      cursor: pointer;
      font-weight: 600;
      font-size: 14px;
      transition: all 0.3s ease;
      text-decoration: none;
      position: relative;
      overflow: hidden;
    }

    .btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s;
    }

    .btn:hover::before {
      left: 100%;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      color: #fff;
      box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
    }

    .btn-secondary {
      background: linear-gradient(135deg, var(--blue) 0%, #1d4ed8 100%);
      color: #fff;
      box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
    }

    .btn-success {
      background: linear-gradient(135deg, var(--green) 0%, #047857 100%);
      color: #fff;
      box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);
    }

    .btn-muted {
      background: #f3f4f6;
      color: #374151;
      border: 1px solid var(--border);
    }

    .btn-muted:hover {
      background: #e5e7eb;
    }

    .btn-danger {
      background: linear-gradient(135deg, var(--red) 0%, #b91c1c 100%);
      color: #fff;
      box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
    }

    .btn-warning {
      background: linear-gradient(135deg, var(--orange) 0%, #c2410c 100%);
      color: #fff;
      box-shadow: 0 4px 15px rgba(234, 88, 12, 0.3);
    }

    .btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none !important;
    }

    .toolbar {
      display: flex;
      gap: 12px;
      align-items: center;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }

    th, td {
      padding: 16px;
      text-align: left;
      border-bottom: 1px solid var(--border);
      font-size: 14px;
    }

    thead th {
      background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
      color: #374151;
      font-weight: 600;
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    tbody tr {
      transition: all 0.3s ease;
    }

    tbody tr:hover {
      background: #f8fafc;
      transform: scale(1.01);
    }

    .badge {
      font-size: 12px;
      padding: 6px 12px;
      border-radius: 20px;
      font-weight: 600;
      display: inline-block;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .badge-green { background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); color: #065f46; }
    .badge-yellow { background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); color: #92400e; }
    .badge-grey { background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); color: #374151; }
    .badge-red { background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); color: #991b1b; }
    .badge-blue { background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); color: #1e40af; }
    .badge-purple { background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%); color: #6b21a8; }

    .status-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      display: inline-block;
      margin-right: 8px;
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
      70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
      100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    .dot-green { background: var(--success); }
    .dot-grey { background: var(--muted); }
    .dot-yellow { background: var(--warning); }
    .dot-red { background: var(--error); }
    .dot-blue { background: var(--info); }

    .pagination {
      display: flex;
      align-items: center;
      gap: 12px;
      justify-content: center;
      margin-top: 20px;
    }

    .message {
      display: none;
      margin: 16px 0;
      padding: 16px 20px;
      border-radius: 12px;
      border: 1px solid;
      font-weight: 500;
      animation: slideIn 0.4s ease-out;
    }

    @keyframes slideIn {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .message.success {
      background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
      color: #065f46;
      border-color: #a7f3d0;
    }

    .message.error {
      background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
      color: #991b1b;
      border-color: #fecaca;
    }

    .overlay {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.5);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 1000;
      backdrop-filter: blur(4px);
    }

    .loader {
      background: var(--panel);
      padding: 24px 32px;
      border-radius: 16px;
      border: 1px solid var(--border);
      box-shadow: var(--shadow);
      animation: bounce 1s infinite;
    }

    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
      40% { transform: translateY(-10px); }
      60% { transform: translateY(-5px); }
    }

    .header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
      background: var(--panel);
      padding: 24px;
      border-radius: 16px;
      box-shadow: var(--shadow);
    }

    .brand {
      font-weight: 800;
      font-size: 24px;
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .muted {
      color: var(--muted);
      font-size: 14px;
    }

    .switch {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
    }

    .tabs {
      display: flex;
      gap: 8px;
      margin-bottom: 24px;
      border-bottom: 2px solid var(--border);
      background: var(--panel);
      padding: 8px;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .tab {
      padding: 16px 24px;
      cursor: pointer;
      border-bottom: 3px solid transparent;
      font-weight: 600;
      border-radius: 8px;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .tab::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(79, 70, 229, 0.1), transparent);
      transition: left 0.5s;
    }

    .tab:hover::before {
      left: 100%;
    }

    .tab.active {
      border-bottom-color: var(--primary);
      color: var(--primary);
      background: linear-gradient(135deg, rgba(79, 70, 229, 0.1) 0%, rgba(124, 58, 237, 0.1) 100%);
      box-shadow: 0 4px 15px rgba(79, 70, 229, 0.2);
    }

    .tab-content {
      display: none;
      animation: fadeIn 0.5s ease-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .tab-content.active {
      display: block;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 24px;
    }

    .stat-card {
      background: var(--panel);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 24px;
      text-align: center;
      box-shadow: var(--shadow);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: linear-gradient(90deg, var(--primary), var(--secondary), var(--blue));
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-hover);
    }

    .stat-value {
      font-size: 32px;
      font-weight: 800;
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 8px;
      display: block;
    }

    .stat-label {
      font-size: 16px;
      color: var(--muted);
      font-weight: 500;
    }

    .modal {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.6);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 1000;
      backdrop-filter: blur(8px);
    }

    .modal-content {
      background: var(--panel);
      border-radius: 20px;
      padding: 32px;
      max-width: 600px;
      width: 90%;
      max-height: 80vh;
      overflow-y: auto;
      box-shadow: 0 25px 50px rgba(0,0,0,0.25);
      animation: modalSlideIn 0.4s ease-out;
    }

    @keyframes modalSlideIn {
      from { opacity: 0; transform: scale(0.9) translateY(-20px); }
      to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .modal-header {
      margin-bottom: 20px;
      font-weight: 700;
      font-size: 20px;
      color: var(--text);
    }

    .modal-actions {
      display: flex;
      gap: 12px;
      justify-content: flex-end;
      margin-top: 24px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      margin-bottom: 8px;
    }

    .search-box {
      position: relative;
      margin-bottom: 20px;
    }

    .search-box input {
      padding-left: 40px;
    }

    .search-box::before {
      content: '🔍';
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--muted);
      font-size: 16px;
    }

    .filter-row {
      display: flex;
      gap: 16px;
      align-items: end;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }

    .filter-row .col {
      min-width: 150px;
    }

    /* Enhanced Feedback System */
    .feedback-container {
      position: fixed;
      top: 80px;
      right: 20px;
      z-index: 9999;
      display: flex;
      flex-direction: column;
      gap: 10px;
      max-width: 350px;
    }
    
    .feedback-toast {
      padding: 16px 20px;
      border-radius: 12px;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
      display: flex;
      align-items: center;
      animation: toastIn 0.5s ease forwards, toastOut 0.5s ease forwards 2.5s;
      transform: translateX(100%);
      opacity: 0;
      position: relative;
      overflow: hidden;
    }
    
    .feedback-toast.success {
      background: #f0fdf4;
      color: #166534;
      border-left: 4px solid #22c55e;
    }
    
    .feedback-toast.error {
      background: #fef2f2;
      color: #991b1b;
      border-left: 4px solid #ef4444;
    }
    
    .feedback-toast.warning {
      background: #fffbeb;
      color: #92400e;
      border-left: 4px solid #f59e0b;
    }
    
    .feedback-toast.info {
      background: #eff6ff;
      color: #1e40af;
      border-left: 4px solid #3b82f6;
    }
    
    .feedback-toast-icon {
      margin-right: 12px;
      font-size: 20px;
      flex-shrink: 0;
    }
    
    .feedback-toast-content {
      flex: 1;
    }
    
    .feedback-toast-title {
      font-weight: 600;
      margin-bottom: 4px;
    }
    
    .feedback-toast-message {
      font-size: 14px;
      opacity: 0.9;
    }
    
    .feedback-toast-close {
      background: none;
      border: none;
      font-size: 18px;
      cursor: pointer;
      opacity: 0.7;
      margin-left: 10px;
      color: inherit;
      flex-shrink: 0;
    }
    
    .feedback-toast-close:hover {
      opacity: 1;
    }
    
    .feedback-toast-progress {
      position: absolute;
      bottom: 0;
      left: 0;
      height: 3px;
      width: 100%;
      background: rgba(0, 0, 0, 0.1);
    }
    
    .feedback-toast-progress-bar {
      height: 100%;
      animation: progressBar 3s linear forwards;
    }
    
    .success .feedback-toast-progress-bar {
      background: #22c55e;
    }
    
    .error .feedback-toast-progress-bar {
      background: #ef4444;
    }
    
    .warning .feedback-toast-progress-bar {
      background: #f59e0b;
    }
    
    .info .feedback-toast-progress-bar {
      background: #3b82f6;
    }
    
    @keyframes toastIn {
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
    
    @keyframes toastOut {
      to {
        transform: translateX(100%);
        opacity: 0;
      }
    }
    
    @keyframes progressBar {
      from {
        width: 100%;
      }
      to {
        width: 0%;
      }
    }

    @media (max-width: 768px) {
      .container { padding: 16px; }
      .tabs { flex-direction: column; }
      .tab { padding: 12px 16px; }
      .stats-grid { grid-template-columns: 1fr; }
      .row { flex-direction: column; }
      .filter-row { flex-direction: column; align-items: stretch; }
      .feedback-container {
        top: 70px;
        right: 10px;
        left: 10px;
        max-width: none;
      }
    }
  </style>
</head>
<body>
  <!-- Feedback Toast Container -->
  <div class="feedback-container" id="feedbackContainer"></div>

  <div class="container">
    <div class="header">
      <div>
        <div class="brand"><i class="fa-solid fa-rocket"></i> Admin Dashboard</div>
        <div class="muted">Complete platform management system</div>
      </div>
      <div class="toolbar">
        <a class="btn btn-muted" href="/frontend/user/dashboard.php"><i class="fa-solid fa-user"></i> User Dashboard</a>
        <button id="logoutBtn" class="btn btn-danger"><i class="fa-solid fa-right-from-bracket"></i> Log Out</button>
      </div>
    </div>

    <div id="msg" class="message"></div>

    <div class="tabs">
      <div class="tab active" data-tab="tasks"><i class="fa-solid fa-list-check"></i> Tasks</div>
      <div class="tab" data-tab="users"><i class="fa-solid fa-users"></i> Users</div>
      <div class="tab" data-tab="withdrawals"><i class="fa-solid fa-money-bill-transfer"></i> Withdrawals</div>
      <div class="tab" data-tab="statistics"><i class="fa-solid fa-chart-simple"></i> Statistics</div>
      <div class="tab" data-tab="broadcast"><i class="fa-solid fa-bullhorn"></i> Broadcast</div>
    </div>

    <!-- Tasks Tab -->
    <div id="tasks" class="tab-content active">
      <div class="card">
        <div class="card-header"><i class="fa-solid fa-plus"></i> Create New Task</div>
        <div class="card-body">
          <form id="createForm">
            <div class="row">
              <div class="col">
                <label><i class="fa-solid fa-heading"></i> Title</label>
                <input type="text" name="title" required placeholder="e.g., Watch TikTok video (30 sec)">
              </div>
              <div class="col">
                <label><i class="fa-solid fa-tag"></i> Category</label>
                <select name="category" required>
                  <option value="tiktok"><i class="fa-brands fa-tiktok"></i> TikTok</option>
                  <option value="youtube"><i class="fa-brands fa-youtube"></i> YouTube</option>
                  <option value="whatsapp"><i class="fa-brands fa-whatsapp"></i> WhatsApp</option>
                  <option value="facebook"><i class="fa-brands fa-facebook"></i> Facebook Ads</option>
                  <option value="instagram"><i class="fa-brands fa-instagram"></i> Instagram Ads</option>
                  <option value="ads"><i class="fa-solid fa-ad"></i> Ads</option>
                  <option value="blogs"><i class="fa-solid fa-blog"></i> Blogs</option>
                  <option value="trivia"><i class="fa-solid fa-question"></i> Trivia</option>
                </select>
              </div>
              <div class="col">
                <label><i class="fa-solid fa-coins"></i> Reward Price</label>
                <input type="number" step="0.01" min="0" name="price" required placeholder="e.g., 0.50">
              </div>
              <div class="col">
                <label><i class="fa-solid fa-wallet"></i> Reward Wallet</label>
                <select name="reward_wallet">
                  <option value="main"><i class="fa-solid fa-house"></i> Main</option>
                  <option value="tiktok"><i class="fa-brands fa-tiktok"></i> TikTok</option>
                  <option value="youtube"><i class="fa-brands fa-youtube"></i> YouTube</option>
                  <option value="whatsapp"><i class="fa-brands fa-whatsapp"></i> WhatsApp</option>
                  <option value="facebook"><i class="fa-brands fa-facebook"></i> Facebook</option>
                  <option value="instagram"><i class="fa-brands fa-instagram"></i> Instagram</option>
                </select>
              </div>
            </div>
            <div class="row">
              <div class="col" style="flex: 1 1 100%;">
                <label><i class="fa-solid fa-book"></i> Instructions</label>
                <textarea name="instructions" placeholder="Describe what the user must do to complete the task."></textarea>
              </div>
            </div>
            <div class="row">
              <div class="col">
                <label><i class="fa-solid fa-link"></i> Target URL (optional)</label>
                <input type="text" name="target_url" placeholder="https://...">
              </div>
              <div class="col">
                <label><i class="fa-solid fa-image"></i> Image URL (optional)</label>
                <input type="text" name="image_url" placeholder="https://.../image.jpg">
              </div>
            </div>
            <div class="row" style="align-items:center; justify-content: space-between;">
              <label class="switch">
                <input id="activeInput" type="checkbox" checked>
                <i class="fa-solid fa-toggle-on"></i> Active
              </label>
              <button class="btn btn-primary" type="submit">
                <i class="fa-solid fa-plus"></i> Create Task
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><i class="fa-solid fa-gears"></i> Task Management</div>
        <div class="card-body">
          <div class="filter-row">
            <div class="col">
              <label><i class="fa-solid fa-filter"></i> Filter: Category</label>
              <select id="fCategory">
                <option value="">All Categories</option>
                <option value="tiktok"><i class="fa-brands fa-tiktok"></i> TikTok</option>
                <option value="youtube"><i class="fa-brands fa-youtube"></i> YouTube</option>
                <option value="whatsapp"><i class="fa-brands fa-whatsapp"></i> WhatsApp</option>
                <option value="facebook"><i class="fa-brands fa-facebook"></i> Facebook Ads</option>
                <option value="instagram"><i class="fa-brands fa-instagram"></i> Instagram Ads</option>
                <option value="ads"><i class="fa-solid fa-ad"></i> Ads</option>
                <option value="blogs"><i class="fa-solid fa-blog"></i> Blogs</option>
                <option value="trivia"><i class="fa-solid fa-question"></i> Trivia</option>
              </select>
            </div>
            <div class="col">
              <label><i class="fa-solid fa-filter"></i> Filter: Status</label>
              <select id="fActive">
                <option value="">All Status</option>
                <option value="true"><i class="fa-solid fa-toggle-on"></i> Active</option>
                <option value="false"><i class="fa-solid fa-toggle-off"></i> Inactive</option>
              </select>
            </div>
            <div class="col" style="min-width:auto;">
              <button id="applyFilters" class="btn btn-secondary"><i class="fa-solid fa-filter"></i> Apply Filters</button>
            </div>
            <div class="col" style="flex:1 1 auto; text-align:right; min-width:auto;">
              <div class="pagination">
                <button id="prevBtn" class="btn btn-muted"><i class="fa-solid fa-chevron-left"></i> Prev</button>
                <span class="muted">Page <span id="page">1</span></span>
                <button id="nextBtn" class="btn btn-muted">Next <i class="fa-solid fa-chevron-right"></i></button>
              </div>
            </div>
          </div>

          <div style="overflow-x:auto;">
            <table>
              <thead>
                <tr>
                  <th><i class="fa-solid fa-heading"></i> Title</th>
                  <th><i class="fa-solid fa-tag"></i> Category</th>
                  <th><i class="fa-solid fa-coins"></i> Price</th>
                  <th><i class="fa-solid fa-wallet"></i> Wallet</th>
                  <th><i class="fa-solid fa-signal"></i> Status</th>
                  <th><i class="fa-solid fa-calendar"></i> Created</th>
                  <th style="width: 250px;"><i class="fa-solid fa-gear"></i> Actions</th>
                </tr>
              </thead>
              <tbody id="taskRows"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Users Tab -->
    <div id="users" class="tab-content">
      <div class="card">
        <div class="card-header">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span><i class="fa-solid fa-users-gear"></i> User Management</span>
            <button id="registerUserBtn" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> Register New User</button>
          </div>
        </div>
        <div class="card-body">
          <div class="search-box">
            <input type="text" id="userSearch" placeholder="Search users by name or email...">
          </div>
          <div class="filter-row">
            <div class="col">
              <label><i class="fa-solid fa-filter"></i> Filter: Status</label>
              <select id="userStatusFilter">
                <option value="">All Users</option>
                <option value="active"><i class="fa-solid fa-toggle-on"></i> Active</option>
                <option value="inactive"><i class="fa-solid fa-toggle-off"></i> Inactive</option>
              </select>
            </div>
            <div class="col" style="flex:1 1 auto; text-align:right; min-width:auto;">
              <div class="pagination">
                <button id="userPrevBtn" class="btn btn-muted"><i class="fa-solid fa-chevron-left"></i> Prev</button>
                <span class="muted">Page <span id="userPage">1</span></span>
                <button id="userNextBtn" class="btn btn-muted">Next <i class="fa-solid fa-chevron-right"></i></button>
              </div>
            </div>
          </div>

          <div style="overflow-x:auto;">
            <table>
              <thead>
                <tr>
                  <th><i class="fa-solid fa-user"></i> Name</th>
                  <th><i class="fa-solid fa-envelope"></i> Email</th>
                  <th><i class="fa-solid fa-phone"></i> Phone</th>
                  <th><i class="fa-solid fa-coins"></i> Balance</th>
                  <th><i class="fa-solid fa-signal"></i> Status</th>
                  <th><i class="fa-solid fa-earth-africa"></i> Country</th>
                  <th><i class="fa-solid fa-calendar"></i> Joined</th>
                  <th style="width: 200px;"><i class="fa-solid fa-gear"></i> Actions</th>
                </tr>
              </thead>
              <tbody id="userRows"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Withdrawals Tab -->
    <div id="withdrawals" class="tab-content">
      <div class="card">
        <div class="card-header"><i class="fa-solid fa-money-bill-transfer"></i> Withdrawal Management</div>
        <div class="card-body">
          <div class="filter-row">
            <div class="col">
              <label><i class="fa-solid fa-filter"></i> Filter: Status</label>
              <select id="withdrawalStatusFilter">
                <option value="">All Status</option>
                <option value="pending"><i class="fa-solid fa-clock"></i> Pending</option>
                <option value="approved"><i class="fa-solid fa-check"></i> Approved</option>
                <option value="rejected"><i class="fa-solid fa-xmark"></i> Rejected</option>
              </select>
            </div>
            <div class="col" style="flex:1 1 auto; text-align:right; min-width:auto;">
              <div class="pagination">
                <button id="withdrawalPrevBtn" class="btn btn-muted"><i class="fa-solid fa-chevron-left"></i> Prev</button>
                <span class="muted">Page <span id="withdrawalPage">1</span></span>
                <button id="withdrawalNextBtn" class="btn btn-muted">Next <i class="fa-solid fa-chevron-right"></i></button>
              </div>
            </div>
          </div>

          <div style="overflow-x:auto;">
            <table>
              <thead>
                <tr>
                  <th><i class="fa-solid fa-user"></i> User</th>
                  <th><i class="fa-solid fa-money-bill"></i> Amount</th>
                  <th><i class="fa-solid fa-wallet"></i> Wallet</th>
                  <th><i class="fa-solid fa-hashtag"></i> Address</th>
                  <th><i class="fa-solid fa-signal"></i> Status</th>
                  <th><i class="fa-solid fa-calendar"></i> Requested</th>
                  <th style="width: 200px;"><i class="fa-solid fa-gear"></i> Actions</th>
                </tr>
              </thead>
              <tbody id="withdrawalRows"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics Tab -->
    <div id="statistics" class="tab-content">
      <div class="stats-grid">
        <div class="stat-card">
          <span class="stat-value" id="totalUsers">0</span>
          <span class="stat-label"><i class="fa-solid fa-users"></i> Total Users</span>
        </div>
        <div class="stat-card">
          <span class="stat-value" id="activeUsers">0</span>
          <span class="stat-label"><i class="fa-solid fa-user-check"></i> Active Users</span>
        </div>
        <div class="stat-card">
          <span class="stat-value" id="totalTasks">0</span>
          <span class="stat-label"><i class="fa-solid fa-list-check"></i> Total Tasks</span>
        </div>
        <div class="stat-card">
          <span class="stat-value" id="activeTasks">0</span>
          <span class="stat-label"><i class="fa-solid fa-toggle-on"></i> Active Tasks</span>
        </div>
        <div class="stat-card">
          <span class="stat-value" id="totalWithdrawals">0</span>
          <span class="stat-label"><i class="fa-solid fa-money-bill-transfer"></i> Total Withdrawals</span>
        </div>
        <div class="stat-card">
          <span class="stat-value" id="pendingWithdrawals">0</span>
          <span class="stat-label"><i class="fa-solid fa-clock"></i> Pending Withdrawals</span>
        </div>
        <div class="stat-card">
          <span class="stat-value" id="totalPayout">0</span>
          <span class="stat-label"><i class="fa-solid fa-money-bill-wave"></i> Total Payout</span>
        </div>
        <div class="stat-card">
          <span class="stat-value" id="platformProfit">0</span>
          <span class="stat-label"><i class="fa-solid fa-chart-line"></i> Platform Profit</span>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><i class="fa-solid fa-chart-line"></i> Daily Registrations (Last 30 Days)</div>
        <div class="card-body">
          <div id="registrationChart" style="height: 300px; width: 100%;"></div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><i class="fa-solid fa-chart-pie"></i> Task Categories Distribution</div>
        <div class="card-body">
          <div id="categoryChart" style="height: 300px; width: 100%;"></div>
        </div>
      </div>
    </div>

    <!-- Broadcast Tab -->
    <div id="broadcast" class="tab-content">
      <div class="card">
        <div class="card-header"><i class="fa-solid fa-bullhorn"></i> Send Broadcast Message</div>
        <div class="card-body">
          <form id="broadcastForm">
            <div class="form-group">
              <label><i class="fa-solid fa-heading"></i> Message Title</label>
              <input type="text" name="title" placeholder="Important announcement..." required>
            </div>
            <div class="form-group">
              <label><i class="fa-solid fa-message"></i> Message Content</label>
              <textarea name="content" placeholder="Write your message here..." rows="5" required></textarea>
            </div>
            <div class="form-group">
              <label><i class="fa-solid fa-users"></i> Target Audience</label>
              <select name="target">
                <option value="all">All Users</option>
                <option value="active">Active Users Only</option>
                <option value="inactive">Inactive Users Only</option>
                <option value="withdrawals">Users with Withdrawal Requests</option>
              </select>
            </div>
            <div class="form-group">
              <label><i class="fa-solid fa-paper-plane"></i> Message Type</label>
              <select name="type">
                <option value="info">Information</option>
                <option value="warning">Warning</option>
                <option value="important">Important Announcement</option>
                <option value="update">System Update</option>
              </select>
            </div>
            <div class="form-group">
              <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Send Broadcast</button>
            </div>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><i class="fa-solid fa-clock-rotate-left"></i> Broadcast History</div>
        <div class="card-body">
          <div class="filter-row">
            <div class="col" style="flex:1 1 auto; text-align:right; min-width:auto;">
              <div class="pagination">
                <button id="broadcastPrevBtn" class="btn btn-muted"><i class="fa-solid fa-chevron-left"></i> Prev</button>
                <span class="muted">Page <span id="broadcastPage">1</span></span>
                <button id="broadcastNextBtn" class="btn btn-muted">Next <i class="fa-solid fa-chevron-right"></i></button>
              </div>
            </div>
          </div>

          <div style="overflow-x:auto;">
            <table>
              <thead>
                <tr>
                  <th><i class="fa-solid fa-heading"></i> Title</th>
                  <th><i class="fa-solid fa-users"></i> Target</th>
                  <th><i class="fa-solid fa-message"></i> Type</th>
                  <th><i class="fa-solid fa-user"></i> Sent By</th>
                  <th><i class="fa-solid fa-calendar"></i> Date</th>
                  <th><i class="fa-solid fa-eye"></i> Views</th>
                </tr>
              </thead>
              <tbody id="broadcastRows"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Task Modal -->
  <div id="editTaskModal" class="modal">
    <div class="modal-content">
      <div class="modal-header"><i class="fa-solid fa-pen-to-square"></i> Edit Task</div>
      <form id="editTaskForm">
        <input type="hidden" name="id" id="editTaskId">
        <div class="form-group">
          <label><i class="fa-solid fa-heading"></i> Title</label>
          <input type="text" name="title" id="editTaskTitle" required>
        </div>
        <div class="form-group">
          <label><i class="fa-solid fa-tag"></i> Category</label>
          <select name="category" id="editTaskCategory" required>
            <option value="tiktok"><i class="fa-brands fa-tiktok"></i> TikTok</option>
            <option value="youtube"><i class="fa-brands fa-youtube"></i> YouTube</option>
            <option value="whatsapp"><i class="fa-brands fa-whatsapp"></i> WhatsApp</option>
            <option value="facebook"><i class="fa-brands fa-facebook"></i> Facebook Ads</option>
            <option value="instagram"><i class="fa-brands fa-instagram"></i> Instagram Ads</option>
            <option value="ads"><i class="fa-solid fa-ad"></i> Ads</option>
            <option value="blogs"><i class="fa-solid fa-blog"></i> Blogs</option>
            <option value="trivia"><i class="fa-solid fa-question"></i> Trivia</option>
          </select>
        </div>
        <div class="form-group">
          <label><i class="fa-solid fa-coins"></i> Reward Price</label>
          <input type="number" step="0.01" min="0" name="price" id="editTaskPrice" required>
        </div>
        <div class="form-group">
          <label><i class="fa-solid fa-wallet"></i> Reward Wallet</label>
          <select name="reward_wallet" id="editTaskWallet">
            <option value="main"><i class="fa-solid fa-house"></i> Main</option>
            <option value="tiktok"><i class="fa-brands fa-tiktok"></i> TikTok</option>
            <option value="youtube"><i class="fa-brands fa-youtube"></i> YouTube</option>
            <option value="whatsapp"><i class="fa-brands fa-whatsapp"></i> WhatsApp</option>
            <option value="facebook"><i class="fa-brands fa-facebook"></i> Facebook</option>
            <option value="instagram"><i class="fa-brands fa-instagram"></i> Instagram</option>
          </select>
        </div>
        <div class="form-group">
          <label><i class="fa-solid fa-book"></i> Instructions</label>
          <textarea name="instructions" id="editTaskInstructions"></textarea>
        </div>
        <div class="form-group">
          <label><i class="fa-solid fa-link"></i> Target URL (optional)</label>
          <input type="text" name="target_url" id="editTaskTargetUrl">
        </div>
        <div class="form-group">
          <label><i class="fa-solid fa-image"></i> Image URL (optional)</label>
          <input type="text" name="image_url" id="editTaskImageUrl">
        </div>
        <div class="form-group">
          <label class="switch">
            <input type="checkbox" name="active" id="editTaskActive">
            <i class="fa-solid fa-toggle-on"></i> Active
          </label>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn btn-muted" onclick="closeModal('editTaskModal')">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Register User Modal -->
  <div id="registerUserModal" class="modal">
    <div class="modal-content">
      <div class="modal-header"><i class="fa-solid fa-user-plus"></i> Register New User</div>
      <form id="registerUserForm">
        <div class="form-group">
          <label><i class="fa-solid fa-user"></i> Full Name</label>
          <input type="text" name="name" placeholder="Enter full name" required>
        </div>
        <div class="form-group">
          <label><i class="fa-solid fa-envelope"></i> Email</label>
          <input type="email" name="email" placeholder="Enter email address" required>
        </div>
        <div class="form-group">
          <label><i class="fa-solid fa-phone"></i> Phone</label>
          <input type="tel" name="phone" placeholder="Enter phone number" required>
        </div>
        <div class="form-group">
          <label><i class="fa-solid fa-earth-africa"></i> Country</label>
          <input type="text" id="countrySearch" placeholder="Search countries..." style="margin-bottom: 8px;">
          <select name="country" id="countrySelect" required>
            <option value="">Select a country</option>
            <option value="us">🇺🇸 United States</option>
            <option value="ca">🇨🇦 Canada</option>
            <option value="gb">🇬🇧 United Kingdom</option>
            <option value="de">🇩🇪 Germany</option>
            <option value="fr">🇫🇷 France</option>
            <option value="es">🇪🇸 Spain</option>
            <option value="it">🇮🇹 Italy</option>
            <option value="au">🇦🇺 Australia</option>
            <option value="jp">🇯🇵 Japan</option>
            <option value="in">🇮🇳 India</option>
            <option value="br">🇧🇷 Brazil</option>
            <option value="mx">🇲🇽 Mexico</option>
            <option value="nl">🇳🇱 Netherlands</option>
            <option value="se">🇸🇪 Sweden</option>
            <option value="no">🇳🇴 Norway</option>
            <option value="dk">🇩🇰 Denmark</option>
            <option value="fi">🇫🇮 Finland</option>
            <option value="pl">🇵🇱 Poland</option>
            <option value="cz">🇨🇿 Czech Republic</option>
            <option value="at">🇦🇹 Austria</option>
            <option value="ch">🇨🇭 Switzerland</option>
            <option value="be">🇧🇪 Belgium</option>
            <option value="pt">🇵🇹 Portugal</option>
            <option value="gr">🇬🇷 Greece</option>
            <option value="hu">🇭🇺 Hungary</option>
            <option value="ro">🇷🇴 Romania</option>
            <option value="sk">🇸🇰 Slovakia</option>
            <option value="si">🇸🇮 Slovenia</option>
            <option value="hr">🇭🇷 Croatia</option>
            <option value="ba">🇧🇦 Bosnia and Herzegovina</option>
            <option value="rs">🇷🇸 Serbia</option>
            <option value="me">🇲🇪 Montenegro</option>
            <option value="mk">🇲🇰 North Macedonia</option>
            <option value="al">🇦🇱 Albania</option>
            <option value="bg">🇧🇬 Bulgaria</option>
            <option value="tr">🇹🇷 Turkey</option>
            <option value="cy">🇨🇾 Cyprus</option>
            <option value="mt">🇲🇹 Malta</option>
            <option value="ee">🇪🇪 Estonia</option>
            <option value="lv">🇱🇻 Latvia</option>
            <option value="lt">🇱🇹 Lithuania</option>
            <option value="ru">🇷🇺 Russia</option>
            <option value="ua">🇺🇦 Ukraine</option>
            <option value="by">🇧🇾 Belarus</option>
            <option value="md">🇲🇩 Moldova</option>
            <option value="ge">🇬🇪 Georgia</option>
            <option value="am">🇦🇲 Armenia</option>
            <option value="az">🇦🇿 Azerbaijan</option>
            <option value="kz">🇰🇿 Kazakhstan</option>
            <option value="uz">🇺🇿 Uzbekistan</option>
            <option value="tj">🇹🇯 Tajikistan</option>
            <option value="kg">🇰🇬 Kyrgyzstan</option>
            <option value="tm">🇹🇲 Turkmenistan</option>
            <option value="cn">🇨🇳 China</option>
            <option value="kr">🇰🇷 South Korea</option>
            <option value="tw">🇹🇼 Taiwan</option>
            <option value="hk">🇭🇰 Hong Kong</option>
            <option value="mo">🇲🇴 Macau</option>
            <option value="sg">🇸🇬 Singapore</option>
            <option value="my">🇲🇾 Malaysia</option>
            <option value="th">🇹🇭 Thailand</option>
            <option value="vn">🇻🇳 Vietnam</option>
            <option value="ph">🇵🇭 Philippines</option>
            <option value="id">🇮🇩 Indonesia</option>
            <option value="bn">🇧🇳 Brunei</option>
            <option value="kh">🇰🇭 Cambodia</option>
            <option value="la">🇱🇦 Laos</option>
            <option value="mm">🇲🇲 Myanmar</option>
            <option value="np">🇳🇵 Nepal</option>
            <option value="bt">🇧🇹 Bhutan</option>
            <option value="bd">🇧🇩 Bangladesh</option>
            <option value="pk">🇵🇰 Pakistan</option>
            <option value="lk">🇱🇰 Sri Lanka</option>
            <option value="mv">🇲🇻 Maldives</option>
            <option value="af">🇦🇫 Afghanistan</option>
            <option value="ir">🇮🇷 Iran</option>
            <option value="iq">🇮🇶 Iraq</option>
            <option value="sy">🇸🇾 Syria</option>
            <option value="lb">🇱🇧 Lebanon</option>
            <option value="jo">🇯🇴 Jordan</option>
            <option value="ps">🇵🇸 Palestine</option>
            <option value="il">🇮🇱 Israel</option>
            <option value="sa">🇸🇦 Saudi Arabia</option>
            <option value="ye">🇾🇪 Yemen</option>
            <option value="om">🇴🇲 Oman</option>
            <option value="ae">🇦🇪 United Arab Emirates</option>
            <option value="kw">🇰🇼 Kuwait</option>
            <option value="bh">🇧🇭 Bahrain</option>
            <option value="qa">🇶🇦 Qatar</option>
            <option value="eg">🇪🇬 Egypt</option>
            <option value="ly">🇱🇾 Libya</option>
            <option value="tn">🇹🇳 Tunisia</option>
            <option value="dz">🇩🇿 Algeria</option>
            <option value="ma">🇲🇦 Morocco</option>
            <option value="eh">🇪🇭 Western Sahara</option>
            <option value="mr">🇲🇷 Mauritania</option>
            <option value="sn">🇸🇳 Senegal</option>
            <option value="gm">🇬🇲 Gambia</option>
            <option value="gn">🇬🇳 Guinea</option>
            <option value="sl">🇸🇱 Sierra Leone</option>
            <option value="lr">🇱🇷 Liberia</option>
            <option value="ci">🇨🇮 Ivory Coast</option>
            <option value="gh">🇬🇭 Ghana</option>
            <option value="tg">🇹🇬 Togo</option>
            <option value="bj">🇧🇯 Benin</option>
            <option value="ne">🇳🇪 Niger</option>
            <option value="bf">🇧🇫 Burkina Faso</option>
            <option value="ml">🇲🇱 Mali</option>
            <option value="td">🇹🇩 Chad</option>
            <option value="sd">🇸🇩 Sudan</option>
            <option value="ss">🇸🇸 South Sudan</option>
            <option value="er">🇪🇷 Eritrea</option>
            <option value="dj">🇩🇯 Djibouti</option>
            <option value="so">🇸🇴 Somalia</option>
            <option value="et">🇪🇹 Ethiopia</option>
            <option value="ke">🇰🇪 Kenya</option>
            <option value="tz">🇹🇿 Tanzania</option>
            <option value="ug">🇺🇬 Uganda</option>
            <option value="rw">🇷🇼 Rwanda</option>
            <option value="bi">🇧🇮 Burundi</option>
            <option value="mz">🇲🇿 Mozambique</option>
            <option value="zm">🇿🇲 Zambia</option>
            <option value="zw">🇿🇼 Zimbabwe</option>
            <option value="bw">🇧🇼 Botswana</option>
            <option value="na">🇳🇦 Namibia</option>
            <option value="za">🇿🇦 South Africa</option>
            <option value="sz">🇸🇿 Eswatini</option>
            <option value="ls">🇱🇸 Lesotho</option>
            <option value="mw">🇲🇼 Malawi</option>
            <option value="ao">🇦🇴 Angola</option>
            <option value="cd">🇨🇩 Democratic Republic of the Congo</option>
            <option value="cg">🇨🇬 Republic of the Congo</option>
            <option value="ga">🇬🇦 Gabon</option>
            <option value="cm">🇨🇲 Cameroon</option>
            <option value="gq">🇬🇶 Equatorial Guinea</option>
            <option value="td">🇹🇩 Chad</option>
            <option value="cf">🇨🇫 Central African Republic</option>
            <option value="st">🇸🇹 São Tomé and Príncipe</option>
            <option value="cv">🇨🇻 Cape Verde</option>
            <option value="gw">🇬🇼 Guinea-Bissau</option>
            <option value="ar">🇦🇷 Argentina</option>
            <option value="bo">🇧🇴 Bolivia</option>
            <option value="cl">🇨🇱 Chile</option>
            <option value="co">🇨🇴 Colombia</option>
            <option value="ec">🇪🇨 Ecuador</option>
            <option value="gy">🇬🇾 Guyana</option>
            <option value="pe">🇵🇪 Peru</option>
            <option value="py">🇵🇾 Paraguay</option>
            <option value="sr">🇸🇷 Suriname</option>
            <option value="uy">🇺🇾 Uruguay</option>
            <option value="ve">🇻🇪 Venezuela</option>
            <option value="nz">🇳🇿 New Zealand</option>
            <option value="fj">🇫🇯 Fiji</option>
            <option value="pg">🇵🇬 Papua New Guinea</option>
            <option value="sb">🇸🇧 Solomon Islands</option>
            <option value="vu">🇻🇺 Vanuatu</option>
            <option value="nc">🇳🇨 New Caledonia</option>
            <option value="pf">🇵🇫 French Polynesia</option>
            <option value="ws">🇼🇸 Samoa</option>
            <option value="to">🇹🇴 Tonga</option>
            <option value="tv">🇹🇻 Tuvalu</option>
            <option value="ki">🇰🇮 Kiribati</option>
            <option value="mh">🇲🇭 Marshall Islands</option>
            <option value="fm">🇫🇲 Federated States of Micronesia</option>
            <option value="pw">🇵🇼 Palau</option>
            <option value="nr">🇳🇷 Nauru</option>
            <option value="gl">🇬🇱 Greenland</option>
            <option value="is">🇮🇸 Iceland</option>
            <option value="fo">🇫🇴 Faroe Islands</option>
            <option value="ax">🇦🇽 Åland Islands</option>
            <option value="sj">🇸🇯 Svalbard and Jan Mayen</option>
            <option value="bq">🇧🇶 Caribbean Netherlands</option>
            <option value="cw">🇨🇼 Curaçao</option>
            <option value="sx">🇸🇽 Sint Maarten</option>
            <option value="aw">🇦🇼 Aruba</option>
            <option value="tt">🇹🇹 Trinidad and Tobago</option>
            <option value="bb">🇧🇧 Barbados</option>
            <option value="jm">🇯🇲 Jamaica</option>
            <option value="ht">🇭🇹 Haiti</option>
            <option value="do">🇩🇴 Dominican Republic</option>
            <option value="pr">🇵🇷 Puerto Rico</option>
            <option value="vi">🇻🇮 U.S. Virgin Islands</option>
            <option value="ky">🇰🇾 Cayman Islands</option>
            <option value="tc">🇹🇨 Turks and Caicos Islands</option>
            <option value="ms">🇲🇸 Montserrat</option>
            <option value="vg">🇻🇬 British Virgin Islands</option>
            <option value="ag">🇦🇬 Antigua and Barbuda</option>
            <option value="dm">🇩🇲 Dominica</option>
            <option value="lc">🇱🇨 Saint Lucia</option>
            <option value="vc">🇻🇨 Saint Vincent and the Grenadines</option>
            <option value="gd">🇬🇩 Grenada</option>
            <option value="kn">🇰🇳 Saint Kitts and Nevis</option>
            <option value="bs">🇧🇸 Bahamas</option>
            <option value="cu">🇨🇺 Cuba</option>
            <option value="jm">🇯🇲 Jamaica</option>
            <option value="pa">🇵🇦 Panama</option>
            <option value="cr">🇨🇷 Costa Rica</option>
            <option value="ni">🇳🇮 Nicaragua</option>
            <option value="hn">🇭🇳 Honduras</option>
            <option value="sv">🇸🇻 El Salvador</option>
            <option value="gt">🇬🇹 Guatemala</option>
            <option value="bz">🇧🇿 Belize</option>
            <option value="hn">🇭🇳 Honduras</option>
          </select>
        </div>
        <div class="form-group">
          <label><i class="fa-solid fa-lock"></i> Password</label>
          <input type="password" name="password" placeholder="Enter password" required>
        </div>
        <div class="form-group">
          <label><i class="fa-solid fa-lock"></i> Confirm Password</label>
          <input type="password" name="confirmPassword" placeholder="Confirm password" required>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn btn-muted" onclick="closeModal('registerUserModal')">Cancel</button>
          <button type="submit" class="btn btn-primary">Register User</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Loading Overlay -->
  <div id="loadingOverlay" class="overlay">
    <div class="loader">
      <i class="fa-solid fa-spinner fa-spin"></i> Processing...
    </div>
  </div>

  <script>
    // Global variables
    let currentPage = 1;
    let currentTab = 'tasks';
    let tasks = [];
    let users = [];
    let withdrawals = [];
    let broadcasts = [];

    // API base URL
    const API_BASE_URL = 'http://localhost:8001/api';

    // Get auth token
    function getAuthToken() {
        return localStorage.getItem('authToken');
    }

    // Make authenticated API call
    async function apiCall(endpoint, options = {}) {
        const token = getAuthToken();
        if (!token) {
            throw new Error('No authentication token found');
        }

        const defaultOptions = {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        };

        const response = await fetch(`${API_BASE_URL}${endpoint}`, {
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                ...options.headers
            }
        });

        if (!response.ok) {
            const error = await response.json().catch(() => ({ message: 'API call failed' }));
            throw new Error(error.detail || error.message || `HTTP ${response.status}`);
        }

        return response.json();
    }

    // DOM Ready
    document.addEventListener('DOMContentLoaded', function() {
      // Check authentication
      const token = getAuthToken();
      if (!token) {
        window.location.href = 'login.php';
        return;
      }

      // Initialize tabs
      const tabs = document.querySelectorAll('.tab');
      tabs.forEach(tab => {
        tab.addEventListener('click', () => {
          const tabName = tab.getAttribute('data-tab');
          switchTab(tabName);
        });
      });

      // Initialize buttons
      document.getElementById('logoutBtn').addEventListener('click', logout);
      document.getElementById('createForm').addEventListener('submit', createTask);
      document.getElementById('editTaskForm').addEventListener('submit', updateTask);
      document.getElementById('broadcastForm').addEventListener('submit', sendBroadcast);
      document.getElementById('applyFilters').addEventListener('click', loadTasks);

      // Pagination
      document.getElementById('prevBtn').addEventListener('click', () => changePage(-1));
      document.getElementById('nextBtn').addEventListener('click', () => changePage(1));
      document.getElementById('userPrevBtn').addEventListener('click', () => changeUserPage(-1));
      document.getElementById('userNextBtn').addEventListener('click', () => changeUserPage(1));
      document.getElementById('withdrawalPrevBtn').addEventListener('click', () => changeWithdrawalPage(-1));
      document.getElementById('withdrawalNextBtn').addEventListener('click', () => changeWithdrawalPage(1));
      document.getElementById('broadcastPrevBtn').addEventListener('click', () => changeBroadcastPage(-1));
      document.getElementById('broadcastNextBtn').addEventListener('click', () => changeBroadcastPage(1));

      // Search and filters
      document.getElementById('userSearch').addEventListener('input', debounce(loadUsers, 300));
      document.getElementById('userStatusFilter').addEventListener('change', loadUsers);
      document.getElementById('withdrawalStatusFilter').addEventListener('change', loadWithdrawals);

      // Load initial data
      loadTasks();
      loadUsers();
      loadWithdrawals();
      loadStatistics();
      loadBroadcasts();
    });

    // Tab switching
    function switchTab(tabName) {
      // Update active tab
      document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
        if (tab.getAttribute('data-tab') === tabName) {
          tab.classList.add('active');
        }
      });
      
      // Show active content
      document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
        if (content.id === tabName) {
          content.classList.add('active');
        }
      });
      
      currentTab = tabName;
      
      // Load data if needed
      if (tabName === 'statistics') {
        loadStatistics();
      } else if (tabName === 'broadcast') {
        loadBroadcasts();
      }
    }

    // Load tasks from server
    async function loadTasks() {
      try {
        showLoading();
        const category = document.getElementById('fCategory').value;
        const active = document.getElementById('fActive').value;

        const tasksData = await apiCall('/admin/tasks/');

        if (tasksData.success) {
          tasks = tasksData.tasks.map(task => ({
            id: task.task_id,
            title: task.title,
            category: task.type,
            price: parseFloat(task.reward),
            wallet: 'main', // API doesn't specify wallet, default to main
            active: task.is_active,
            created_at: new Date(task.created_at).toLocaleDateString(),
            instructions: task.description,
            target_url: task.requirements?.target_url || '',
            image_url: task.requirements?.image_url || ''
          }));

          // Apply filters
          let filteredTasks = tasks;
          if (category) {
            filteredTasks = filteredTasks.filter(task => task.category === category);
          }
          if (active !== '') {
            const isActive = active === 'true';
            filteredTasks = filteredTasks.filter(task => task.active === isActive);
          }

          renderTasks(filteredTasks);
          hideLoading();
          showFeedback('Tasks loaded successfully', 'success');
        } else {
          throw new Error(tasksData.message || 'Failed to load tasks');
        }

      } catch (error) {
        console.error('Failed to load tasks:', error);
        hideLoading();
        showFeedback('Failed to load tasks: ' + error.message, 'error');
      }
    }

    // Render tasks to the table
    function renderTasks(tasks) {
      const tbody = document.getElementById('taskRows');
      tbody.innerHTML = '';
      
      if (tasks.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;">No tasks found</td></tr>`;
        return;
      }
      
      tasks.forEach(task => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${task.title}</td>
          <td><span class="badge badge-${getCategoryBadge(task.category)}">${getCategoryIcon(task.category)} ${task.category}</span></td>
          <td>$${task.price.toFixed(2)}</td>
          <td><span class="badge badge-blue">${task.wallet}</span></td>
          <td><span class="status-dot ${task.active ? 'dot-green' : 'dot-grey'}"></span> ${task.active ? 'Active' : 'Inactive'}</td>
          <td>${task.created_at}</td>
          <td>
            <button class="btn btn-secondary" onclick="editTask(${task.id})"><i class="fa-solid fa-pen"></i> Edit</button>
            <button class="btn ${task.active ? 'btn-warning' : 'btn-success'}" onclick="toggleTask(${task.id}, ${!task.active})">
              <i class="fa-solid fa-toggle-${task.active ? 'on' : 'off'}"></i> ${task.active ? 'Deactivate' : 'Activate'}
            </button>
            <button class="btn btn-danger" onclick="deleteTask(${task.id})"><i class="fa-solid fa-trash"></i> Delete</button>
          </td>
        `;
        tbody.appendChild(row);
      });
    }

    // Create a new task
    async function createTask(e) {
      e.preventDefault();
      showLoading();

      const formData = new FormData(e.target);

      try {
        const taskData = {
          title: formData.get('title'),
          description: formData.get('instructions'),
          reward: parseFloat(formData.get('price')),
          type: formData.get('category'),
          requirements: {
            target_url: formData.get('target_url'),
            image_url: formData.get('image_url')
          },
          is_active: document.getElementById('activeInput').checked
        };

        const response = await apiCall('/admin/tasks', {
          method: 'POST',
          body: JSON.stringify(taskData)
        });

        if (response.success) {
          // Reload tasks to get updated list
          await loadTasks();
          e.target.reset();
          hideLoading();
          showFeedback('Task created successfully', 'success');
        } else {
          throw new Error(response.message || 'Failed to create task');
        }

      } catch (error) {
        console.error('Failed to create task:', error);
        hideLoading();
        showFeedback('Failed to create task: ' + error.message, 'error');
      }
    }

    // Edit task - open modal
    function editTask(id) {
      const task = tasks.find(t => t.id === id);
      if (!task) return;
      
      document.getElementById('editTaskId').value = task.id;
      document.getElementById('editTaskTitle').value = task.title;
      document.getElementById('editTaskCategory').value = task.category;
      document.getElementById('editTaskPrice').value = task.price;
      document.getElementById('editTaskWallet').value = task.wallet;
      document.getElementById('editTaskInstructions').value = task.instructions;
      document.getElementById('editTaskTargetUrl').value = task.target_url;
      document.getElementById('editTaskImageUrl').value = task.image_url;
      document.getElementById('editTaskActive').checked = task.active;
      
      openModal('editTaskModal');
    }

    // Update task
    async function updateTask(e) {
      e.preventDefault();
      showLoading();

      const formData = new FormData(e.target);
      const id = formData.get('id');

      try {
        const taskData = {
          title: formData.get('title'),
          description: formData.get('instructions'),
          reward: parseFloat(formData.get('price')),
          type: formData.get('category'),
          requirements: {
            target_url: formData.get('target_url'),
            image_url: formData.get('image_url')
          },
          is_active: document.getElementById('editTaskActive').checked
        };

        const response = await apiCall(`/admin/tasks/${id}`, {
          method: 'PUT',
          body: JSON.stringify(taskData)
        });

        if (response.success) {
          // Reload tasks to get updated list
          await loadTasks();
          closeModal('editTaskModal');
          hideLoading();
          showFeedback('Task updated successfully', 'success');
        } else {
          throw new Error(response.message || 'Failed to update task');
        }

      } catch (error) {
        console.error('Failed to update task:', error);
        hideLoading();
        showFeedback('Failed to update task: ' + error.message, 'error');
      }
    }

    // Toggle task status
    async function toggleTask(id, active) {
      showLoading();

      try {
        const response = await apiCall(`/admin/tasks/${id}/status`, {
          method: 'PUT',
          body: JSON.stringify({ is_active: active })
        });

        if (response.success) {
          // Reload tasks to get updated list
          await loadTasks();
          hideLoading();
          showFeedback(`Task ${active ? 'activated' : 'deactivated'} successfully`, 'success');
        } else {
          throw new Error(response.message || 'Failed to update task status');
        }

      } catch (error) {
        console.error('Failed to toggle task status:', error);
        hideLoading();
        showFeedback('Failed to update task status: ' + error.message, 'error');
      }
    }

    // Delete task
    async function deleteTask(id) {
      if (!confirm('Are you sure you want to delete this task?')) return;

      showLoading();

      try {
        const response = await apiCall(`/admin/tasks/${id}`, {
          method: 'DELETE'
        });

        if (response.success) {
          // Reload tasks to get updated list
          await loadTasks();
          hideLoading();
          showFeedback('Task deleted successfully', 'success');
        } else {
          throw new Error(response.message || 'Failed to delete task');
        }

      } catch (error) {
        console.error('Failed to delete task:', error);
        hideLoading();
        showFeedback('Failed to delete task: ' + error.message, 'error');
      }
    }

    // Load users from server
    async function loadUsers() {
      try {
        showLoading();
        const search = document.getElementById('userSearch').value;
        const status = document.getElementById('userStatusFilter').value;

        const usersData = await apiCall('/admin/users');

        if (usersData.success) {
          users = usersData.users.map(user => ({
            id: user.user_id,
            name: user.full_name,
            email: user.email,
            phone: user.phone,
            balance: parseFloat(user.wallet_balance),
            status: user.status,
            country: 'Unknown', // API doesn't provide country
            joined: new Date(user.created_at).toLocaleDateString()
          }));

          // Apply filters
          let filteredUsers = users;
          if (search) {
            const searchLower = search.toLowerCase();
            filteredUsers = filteredUsers.filter(user =>
              user.name.toLowerCase().includes(searchLower) ||
              user.email.toLowerCase().includes(searchLower)
            );
          }
          if (status) {
            filteredUsers = filteredUsers.filter(user => user.status === status);
          }

          renderUsers(filteredUsers);
          hideLoading();
        } else {
          throw new Error(usersData.message || 'Failed to load users');
        }

      } catch (error) {
        console.error('Failed to load users:', error);
        hideLoading();
        showFeedback('Failed to load users: ' + error.message, 'error');
      }
    }

    // Render users to the table
    function renderUsers(users) {
      const tbody = document.getElementById('userRows');
      tbody.innerHTML = '';
      
      if (users.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;">No users found</td></tr>`;
        return;
      }
      
      users.forEach(user => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${user.name}</td>
          <td>${user.email}</td>
          <td>${user.phone}</td>
          <td>$${user.balance.toFixed(2)}</td>
          <td><span class="badge ${user.status === 'active' ? 'badge-green' : 'badge-red'}">${user.status}</span></td>
          <td>${user.country}</td>
          <td>${user.joined}</td>
          <td>
            <button class="btn btn-secondary" onclick="viewUser(${user.id})"><i class="fa-solid fa-eye"></i> View</button>
            <button class="btn ${user.status === 'active' ? 'btn-warning' : 'btn-success'}" onclick="toggleUserStatus(${user.id}, '${user.status === 'active' ? 'inactive' : 'active'}')">
              <i class="fa-solid fa-user-${user.status === 'active' ? 'slash' : 'check'}"></i> ${user.status === 'active' ? 'Deactivate' : 'Activate'}
            </button>
          </td>
        `;
        tbody.appendChild(row);
      });
    }

    // View user details
    function viewUser(id) {
      const user = users.find(u => u.id === id);
      if (!user) return;
      
      alert(`User Details:\nName: ${user.name}\nEmail: ${user.email}\nPhone: ${user.phone}\nBalance: $${user.balance.toFixed(2)}\nStatus: ${user.status}\nCountry: ${user.country}\nJoined: ${user.joined}`);
    }

    // Toggle user status
    async function toggleUserStatus(id, status) {
      showLoading();

      try {
        const response = await apiCall(`/admin/users/${id}/status`, {
          method: 'PUT',
          body: JSON.stringify({ status: status })
        });

        if (response.success) {
          // Reload users to get updated list
          await loadUsers();
          hideLoading();
          showFeedback(`User ${status === 'active' ? 'activated' : 'deactivated'} successfully`, 'success');
        } else {
          throw new Error(response.message || 'Failed to update user status');
        }

      } catch (error) {
        console.error('Failed to toggle user status:', error);
        hideLoading();
        showFeedback('Failed to update user status: ' + error.message, 'error');
      }
    }

    // Load withdrawals from server
    async function loadWithdrawals() {
      try {
        showLoading();
        const status = document.getElementById('withdrawalStatusFilter').value;

        const withdrawalsData = await apiCall(`/admin/transactions/withdrawals${status ? `?status=${status}` : ''}`);

        if (withdrawalsData.success) {
          withdrawals = withdrawalsData.withdrawals.map(withdrawal => ({
            id: withdrawal.transaction_id,
            user: withdrawal.user_id, // We might need to get user name separately
            amount: parseFloat(withdrawal.amount),
            wallet: withdrawal.method,
            address: withdrawal.phone || withdrawal.recipient_email || 'N/A',
            status: withdrawal.status,
            requested: new Date(withdrawal.created_at).toLocaleDateString()
          }));

          renderWithdrawals(withdrawals);
          hideLoading();
        } else {
          throw new Error(withdrawalsData.message || 'Failed to load withdrawals');
        }

      } catch (error) {
        console.error('Failed to load withdrawals:', error);
        hideLoading();
        showFeedback('Failed to load withdrawals: ' + error.message, 'error');
      }
    }

    // Render withdrawals to the table
    function renderWithdrawals(withdrawals) {
      const tbody = document.getElementById('withdrawalRows');
      tbody.innerHTML = '';
      
      if (withdrawals.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;">No withdrawals found</td></tr>`;
        return;
      }
      
      withdrawals.forEach(withdrawal => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${withdrawal.user}</td>
          <td>$${withdrawal.amount.toFixed(2)}</td>
          <td>${withdrawal.wallet}</td>
          <td>${withdrawal.address}</td>
          <td><span class="badge ${getWithdrawalStatusBadge(withdrawal.status)}">${withdrawal.status}</span></td>
          <td>${withdrawal.requested}</td>
          <td>
            ${withdrawal.status === 'pending' ? `
              <button class="btn btn-success" onclick="updateWithdrawal(${withdrawal.id}, 'approved')"><i class="fa-solid fa-check"></i> Approve</button>
              <button class="btn btn-danger" onclick="updateWithdrawal(${withdrawal.id}, 'rejected')"><i class="fa-solid fa-xmark"></i> Reject</button>
            ` : `
              <button class="btn btn-secondary" onclick="viewWithdrawal(${withdrawal.id})"><i class="fa-solid fa-eye"></i> View</button>
            `}
          </td>
        `;
        tbody.appendChild(row);
      });
    }

    // Update withdrawal status
    async function updateWithdrawal(id, status) {
      try {
        showLoading();

        const approvalData = await apiCall('/admin/approve-withdrawal', {
          method: 'POST',
          body: JSON.stringify({
            transaction_id: id
          })
        });

        if (approvalData.success) {
          // Update local data
          const withdrawalIndex = withdrawals.findIndex(w => w.id === id);
          if (withdrawalIndex !== -1) {
            withdrawals[withdrawalIndex].status = 'approved'; // Assuming approval
          }
          renderWithdrawals(withdrawals);
          hideLoading();
          showFeedback(approvalData.message, 'success');
        } else {
          throw new Error(approvalData.message || 'Failed to update withdrawal');
        }

      } catch (error) {
        console.error('Failed to update withdrawal:', error);
        hideLoading();
        showFeedback('Failed to update withdrawal: ' + error.message, 'error');
      }
    }

    // View withdrawal details
    function viewWithdrawal(id) {
      const withdrawal = withdrawals.find(w => w.id === id);
      if (!withdrawal) return;
      
      alert(`Withdrawal Details:\nUser: ${withdrawal.user}\nAmount: $${withdrawal.amount.toFixed(2)}\nWallet: ${withdrawal.wallet}\nAddress: ${withdrawal.address}\nStatus: ${withdrawal.status}\nRequested: ${withdrawal.requested}`);
    }

    // Load statistics
    async function loadStatistics() {
      try {
        showLoading();

        const statsData = await apiCall('/admin/dashboard/stats');

        if (statsData.success) {
          const stats = statsData.stats;
          document.getElementById('totalUsers').textContent = stats.total_users.toLocaleString();
          document.getElementById('activeUsers').textContent = stats.activated_users.toLocaleString();
          document.getElementById('totalTasks').textContent = stats.total_tasks.toLocaleString();
          document.getElementById('activeTasks').textContent = stats.active_tasks.toLocaleString();
          document.getElementById('totalWithdrawals').textContent = stats.total_withdrawals.toLocaleString();
          document.getElementById('pendingWithdrawals').textContent = stats.pending_withdrawals.toLocaleString();
          document.getElementById('totalPayout').textContent = `$${stats.total_withdrawals.toFixed(2)}`;
          document.getElementById('platformProfit').textContent = `$${stats.total_deposits.toFixed(2)}`;

          // In a real app, we would render charts here
          hideLoading();
          showFeedback('Statistics loaded successfully', 'success');
        } else {
          throw new Error(statsData.message || 'Failed to load statistics');
        }

      } catch (error) {
        console.error('Failed to load statistics:', error);
        hideLoading();
        showFeedback('Failed to load statistics: ' + error.message, 'error');
      }
    }

    // Load broadcasts
    async function loadBroadcasts() {
      try {
        showLoading();
        const broadcastsData = await apiCall('/admin/notifications');

        if (broadcastsData.success) {
          broadcasts = broadcastsData.notifications.map(notification => ({
            id: notification.notification_id,
            title: notification.title,
            target: notification.user_id ? 'specific' : 'all',
            type: notification.type,
            sent_by: notification.user_name || 'Admin',
            date: new Date(notification.created_at).toLocaleDateString(),
            views: notification.is_read ? 'Read' : 'Unread',
            message: notification.message
          }));

          renderBroadcasts(broadcasts);
          hideLoading();
        } else {
          throw new Error(broadcastsData.message || 'Failed to load broadcasts');
        }

      } catch (error) {
        console.error('Failed to load broadcasts:', error);
        hideLoading();
        showFeedback('Failed to load broadcasts: ' + error.message, 'error');
      }
    }

    // Render broadcasts to the table
    function renderBroadcasts(broadcasts) {
      const tbody = document.getElementById('broadcastRows');
      tbody.innerHTML = '';
      
      if (broadcasts.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;">No broadcasts found</td></tr>`;
        return;
      }
      
      broadcasts.forEach(broadcast => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${broadcast.title}</td>
          <td><span class="badge badge-blue">${broadcast.target}</span></td>
          <td><span class="badge ${getBroadcastTypeBadge(broadcast.type)}">${broadcast.type}</span></td>
          <td>${broadcast.sent_by}</td>
          <td>${broadcast.date}</td>
          <td>${broadcast.views}</td>
        `;
        tbody.appendChild(row);
      });
    }

    // Send broadcast message
    async function sendBroadcast(e) {
      e.preventDefault();
      showLoading();

      const formData = new FormData(e.target);

      try {
        const broadcastData = {
          title: formData.get('title'),
          message: formData.get('content'),
          type: formData.get('type'),
          user_id: formData.get('target') === 'all' ? null : undefined // null for all users, undefined for specific logic
        };

        const response = await apiCall('/notifications/create', {
          method: 'POST',
          body: JSON.stringify(broadcastData)
        });

        if (response.success) {
          // Reload broadcasts to get updated list
          await loadBroadcasts();
          e.target.reset();
          hideLoading();
          showFeedback('Broadcast sent successfully', 'success');
        } else {
          throw new Error(response.message || 'Failed to send broadcast');
        }

      } catch (error) {
        console.error('Failed to send broadcast:', error);
        hideLoading();
        showFeedback('Failed to send broadcast: ' + error.message, 'error');
      }
    }

    // Helper functions
    function getCategoryIcon(category) {
      const icons = {
        'tiktok': 'fa-brands fa-tiktok',
        'youtube': 'fa-brands fa-youtube',
        'whatsapp': 'fa-brands fa-whatsapp',
        'facebook': 'fa-brands fa-facebook',
        'instagram': 'fa-brands fa-instagram',
        'ads': 'fa-solid fa-ad',
        'blogs': 'fa-solid fa-blog',
        'trivia': 'fa-solid fa-question'
      };
      return `<i class="${icons[category] || 'fa-solid fa-tasks'}"></i>`;
    }

    function getCategoryBadge(category) {
      const badges = {
        'tiktok': 'purple',
        'youtube': 'red',
        'whatsapp': 'green',
        'facebook': 'blue',
        'instagram': 'pink',
        'ads': 'yellow',
        'blogs': 'blue',
        'trivia': 'grey'
      };
      return badges[category] || 'grey';
    }

    function getWithdrawalStatusBadge(status) {
      const badges = {
        'pending': 'yellow',
        'approved': 'green',
        'rejected': 'red'
      };
      return badges[status] || 'grey';
    }

    function getBroadcastTypeBadge(type) {
      const badges = {
        'info': 'blue',
        'warning': 'yellow',
        'important': 'red',
        'update': 'green'
      };
      return badges[type] || 'grey';
    }

    function changePage(delta) {
      currentPage += delta;
      if (currentPage < 1) currentPage = 1;
      document.getElementById('page').textContent = currentPage;
      loadTasks();
    }

    function changeUserPage(delta) {
      currentPage += delta;
      if (currentPage < 1) currentPage = 1;
      document.getElementById('userPage').textContent = currentPage;
      loadUsers();
    }

    function changeWithdrawalPage(delta) {
      currentPage += delta;
      if (currentPage < 1) currentPage = 1;
      document.getElementById('withdrawalPage').textContent = currentPage;
      loadWithdrawals();
    }

    function changeBroadcastPage(delta) {
      currentPage += delta;
      if (currentPage < 1) currentPage = 1;
      document.getElementById('broadcastPage').textContent = currentPage;
      loadBroadcasts();
    }

    function openModal(id) {
      document.getElementById(id).style.display = 'flex';
    }

    function closeModal(id) {
      document.getElementById(id).style.display = 'none';
    }

    function showLoading() {
      document.getElementById('loadingOverlay').style.display = 'flex';
    }

    function hideLoading() {
      document.getElementById('loadingOverlay').style.display = 'none';
    }

    function logout() {
      if (confirm('Are you sure you want to log out?')) {
        showLoading();
        // In a real app, this would call the logout API
        setTimeout(() => {
          window.location.href = '/frontend/auth/login.php';
        }, 800);
      }
    }

    function debounce(func, wait) {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    }

    // Enhanced Feedback System
    function showFeedback(message, type = 'info', title = null) {
      const container = document.getElementById('feedbackContainer');
      
      // Set title based on type if not provided
      if (!title) {
        switch (type) {
          case 'success': title = 'Success'; break;
          case 'error': title = 'Error'; break;
          case 'warning': title = 'Warning'; break;
          default: title = 'Information';
        }
      }
      
      // Set icon based on type
      let icon;
      switch (type) {
        case 'success': icon = 'fa-circle-check'; break;
        case 'error': icon = 'fa-circle-exclamation'; break;
        case 'warning': icon = 'fa-triangle-exclamation'; break;
        default: icon = 'fa-circle-info';
      }
      
      const toast = document.createElement('div');
      toast.className = `feedback-toast ${type}`;
      toast.innerHTML = `
        <div class="feedback-toast-icon"><i class="fa-solid ${icon}"></i></div>
        <div class="feedback-toast-content">
          <div class="feedback-toast-title">${title}</div>
          <div class="feedback-toast-message">${message}</div>
        </div>
        <button class="feedback-toast-close" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
        <div class="feedback-toast-progress"><div class="feedback-toast-progress-bar"></div></div>
      `;
      
      container.appendChild(toast);
      
      // Remove toast after animation completes
      setTimeout(() => {
        if (toast.parentElement) {
          toast.remove();
        }
      }, 3000);
    }

    // Admin API wiring for FastAPI backend
    (function(){
      const API_BASE_KEY = 'admin_api_base';
      const TOKEN_KEY = 'admin_api_token';

      function getApiBase(){ return localStorage.getItem(API_BASE_KEY) || ''; }
      function getToken(){ return localStorage.getItem(TOKEN_KEY) || ''; }
      function setApiBase(v){ localStorage.setItem(API_BASE_KEY, v); }
      function setToken(v){ localStorage.setItem(TOKEN_KEY, v); }

      async function api(path, options={}){
        const base = getApiBase();
        if(!base) throw new Error('Configure API base URL (press Ctrl+K)');
        const headers = Object.assign({'Content-Type':'application/json'}, options.headers||{});
        const token = getToken();
        if(token) headers['Authorization'] = `Bearer ${token}`;
        const res = await fetch(`${base}${path}`, { ...options, headers });
        const json = await res.json().catch(()=>({success:false,message:'Invalid JSON'}));
        if(!json.success){ throw new Error(json.message || 'Request failed'); }
        return json.data;
      }

      // Quick config dialog
      function quickConfig(){
        const base = prompt('API Base URL (e.g., https://api.yourdomain.com)', getApiBase());
        if(base!==null) setApiBase(base.replace(/\/$/, ''));
        const token = prompt('Admin Access Token (Bearer)', getToken());
        if(token!==null) setToken(token);
        showFeedback('API configured', 'success');
        // initial loads
        try { loadAdminAnalytics(); } catch(e){}
        try { loadAdminPayouts(); } catch(e){}
        try { wireBroadcastForm(); } catch(e){}
      }

      // Expose shortcut Ctrl+K
      window.addEventListener('keydown', (e)=>{ if(e.ctrlKey && e.key.toLowerCase()==='k'){ e.preventDefault(); quickConfig(); }});

      // Analytics → #statistics cards
      async function loadAdminAnalytics(){
        try {
          const a = await api('/admin/analytics');
          const setText = (id, val)=>{ const el=document.getElementById(id); if(el) el.textContent = (val!=null? val : '-'); };
          setText('totalUsers', a.total_users);
          setText('activeUsers', a.active_users);
          setText('totalPayout', (a.total_payouts_usd||0).toFixed ? a.total_payouts_usd.toFixed(2) : a.total_payouts_usd);
          if(document.getElementById('platformProfit')){
            // simple proxy metric: commissions - payouts
            const profit = (a.total_commissions_usd||0) - (a.total_payouts_usd||0);
            setText('platformProfit', profit.toFixed(2));
          }
        } catch(err){ showFeedback('Analytics: '+err.message,'error'); }
      }

      // Payouts table → #withdrawalRows
      async function loadAdminPayouts(){
        const tbody = document.getElementById('withdrawalRows');
        if(!tbody) return;
        try {
          const list = await api('/payouts');
          tbody.innerHTML = '';
          list.forEach(p=>{
            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td>${p.user_id}</td>
              <td>$${Number(p.amount_usd).toFixed(2)}</td>
              <td>${p.gateway}</td>
              <td>${p.destination||''}</td>
              <td><span class="badge ${p.status}">${p.status}</span></td>
              <td>${new Date(p.created_at).toLocaleString()}</td>
              <td>
                <button class="btn btn-success" ${p.status==='sent'?'disabled':''} data-id="${p.id}" data-action="approve">Approve</button>
                <button class="btn btn-danger" ${p.status==='rejected'?'disabled':''} data-id="${p.id}" data-action="reject">Reject</button>
              </td>`;
            tbody.appendChild(tr);
          });
        } catch(err){ showFeedback('Payouts: '+err.message,'error'); }
      }

      // Delegate payout approve/reject clicks
      document.addEventListener('click', async (e)=>{
        const btn = e.target.closest('button');
        if(!btn) return;
        const action = btn.getAttribute('data-action');
        const id = btn.getAttribute('data-id');
        if(!action || !id) return;
        try {
          if(action==='approve'){
            await api(`/payouts/${id}/approve`, { method:'POST' });
            showFeedback('Payout approved','success');
          } else if(action==='reject'){
            const reason = prompt('Reason for rejection?') || '';
            await api(`/payouts/${id}/reject?reason=${encodeURIComponent(reason)}`, { method:'POST' });
            showFeedback('Payout rejected','success');
          }
          loadAdminPayouts();
        } catch(err){ showFeedback(err.message,'error'); }
      });

      // Users: suspend/unsuspend (uses existing inputs if present)
      async function suspendUserAdmin(userId, suspend){
        await api(`/admin/users/${userId}/suspend?suspend=${suspend?'true':'false'}`, { method:'PATCH' });
        showFeedback(suspend?'User suspended':'User unsuspended','success');
      }
      // Optional: wire buttons if they exist
      const suspendBtn = document.getElementById('suspendUserBtn');
      const unsuspendBtn = document.getElementById('unsuspendUserBtn');
      const userIdInput = document.getElementById('userIdInput');
      if(suspendBtn && unsuspendBtn && userIdInput){
        suspendBtn.addEventListener('click', ()=>{ if(userIdInput.value) suspendUserAdmin(userIdInput.value,true); });
        unsuspendBtn.addEventListener('click', ()=>{ if(userIdInput.value) suspendUserAdmin(userIdInput.value,false); });
      }

      // Broadcast email form
      function wireBroadcastForm(){
        const form = document.getElementById('broadcastForm');
        if(!form) return;
        form.addEventListener('submit', async (e)=>{
          e.preventDefault();
          const fd = new FormData(form);
          const payload = { subject: fd.get('title'), body: fd.get('content'), to_all: (fd.get('target')==='all') };
          try { await api('/admin/emails/send', { method:'POST', body: JSON.stringify(payload) }); showFeedback('Broadcast queued','success'); form.reset(); }
          catch(err){ showFeedback('Broadcast: '+err.message,'error'); }
        });
      }

      // Initial auto-load after DOM ready
      document.addEventListener('DOMContentLoaded', ()=>{
        // if no base configured, prompt once
        if(!getApiBase() || !getToken()) setTimeout(()=>quickConfig(), 200);
        else { loadAdminAnalytics(); loadAdminPayouts(); wireBroadcastForm(); }
      });

      // Expose to window for manual trigger if needed
      window.loadAdminAnalytics = loadAdminAnalytics;
      window.loadAdminPayouts = loadAdminPayouts;
      window.quickConfig = quickConfig;
    })();
  </script>
</body>
</html>