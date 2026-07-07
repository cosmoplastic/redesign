<?php
session_start();
require_once __DIR__ . '/../includes/admin-auth.php';

header('X-Robots-Tag: noindex, nofollow', true);

// Expire a stale session so we always show a fresh gate.
if (!empty($_SESSION['admin']) && isset($_SESSION['admin_time'])) {
  if (time() - $_SESSION['admin_time'] > SESSION_TTL) {
    $_SESSION = [];
    session_destroy();
    session_start();
  }
}

// Already signed in → straight to the admin panel.
if (!empty($_SESSION['admin'])) {
  header('Location: /admin/');
  exit;
}

// Handle the passcode submission.
if (isset($_POST['password'])) {
  if ($_POST['password'] === ADMIN_PASS) {
    $_SESSION['admin'] = true;
    $_SESSION['admin_time'] = time();
    header('Location: /admin/');
    exit;
  }
  header('Location: /password/?err=1');
  exit;
}

header('Cache-Control: no-store, must-revalidate');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex">
  <title>Admin access</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link
    href="https://fonts.googleapis.com/css2?family=DM+Mono:ital,wght@0,300;0,400;0,500;1,400&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,700;1,9..144,300&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="/assets/style.css">
  <link rel="icon" type="image/svg+xml" href="/assets/favicon/favicon.svg">
  <link rel="shortcut icon" href="/assets/favicon/favicon.ico">
  <style>
    body {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 24px;
    }

    .lock-card {
      width: 100%;
      max-width: 340px;
      background: var(--bg2);
      border: 1px solid var(--border);
      border-radius: var(--r-xl);
      padding: 40px 36px 36px;
      display: flex;
      flex-direction: column;
      gap: 24px;
    }

    .lock-title {
      font-family: var(--serif);
      font-size: 22px;
      font-weight: 300;
      color: var(--color-primary-100);
      letter-spacing: -0.01em;
    }

    .lock-title em {
      font-style: italic;
    }

    .lock-field {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .lock-field label {
      font-size: 10px;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: var(--color-primary-400);
    }

    .lock-input {
      width: 100%;
      background: var(--bg3);
      border: 1px solid var(--border2);
      border-radius: var(--r);
      padding: 10px 14px;
      font-family: var(--mono);
      font-size: 20px;
      letter-spacing: 0.3em;
      color: var(--color-primary-100);
      outline: none;
      transition: border-color .15s;
    }

    .lock-input:focus {
      border-color: var(--border3);
    }

    .lock-error {
      font-size: 11px;
      color: #f87171;
      display: none;
    }

    .lock-error.show {
      display: block;
    }

    .lock-submit {
      width: 100%;
      padding: 11px;
      background: rgba(255, 255, 255, 0.07);
      border: 1px solid var(--border2);
      border-radius: var(--r);
      font-family: var(--mono);
      font-size: 13px;
      color: var(--color-primary-100);
      cursor: pointer;
      transition: background .15s, border-color .15s;
    }

    .lock-submit:hover {
      background: rgba(255, 255, 255, 0.11);
      border-color: var(--border3);
    }

    .btn-icon {
      display: inline-block;
      width: 14px;
      height: 14px;
      flex-shrink: 0;
      background-color: currentColor;
      -webkit-mask: var(--icon) center / contain no-repeat;
      mask: var(--icon) center / contain no-repeat;
    }

    .lock-home {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 7px;
      text-decoration: none;
      font-family: var(--mono);
      font-size: 12px;
      color: var(--color-primary-300);
      transition: color .15s;
    }

    .lock-home:hover {
      color: var(--color-primary-200);
    }
  </style>
</head>

<body>

  <div class="lock-card">
    <div class="lock-title">Admin <em>access</em></div>
    <form method="POST" class="lock-field">
      <label for="pw">Passcode</label>
      <input class="lock-input" type="password" id="pw" name="password" autocomplete="off" autofocus>
      <div class="lock-error <?= isset($_GET['err']) ? 'show' : '' ?>">Incorrect passcode.</div>
      <button class="lock-submit" type="submit">Unlock</button>
    </form>
    <a href="/" class="lock-home">
      <span class="btn-icon" style="--icon:url(/assets/icons/home.svg)"></span>Back to home
    </a>
  </div>

</body>

</html>
