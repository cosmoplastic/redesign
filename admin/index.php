<?php
session_start();
require_once __DIR__ . '/../includes/version.php';

define('ADMIN_PASS', '2230');
define('MAX_UPLOAD_MB', 512);

$dataDir = __DIR__ . '/data';
$uploadDir = __DIR__ . '/uploads';

if (!is_dir($dataDir))
  mkdir($dataDir, 0755, true);
if (!is_dir($uploadDir))
  mkdir($uploadDir, 0755, true);

$snipsFile = $dataDir . '/snippets.json';
$filesFile = $dataDir . '/files.json';

// ── Public file/image serving (no auth required) ─────────────────
if (isset($_GET['token']) || isset($_GET['img'])) {
  $token = $_GET['token'] ?? $_GET['img'];
  $inline = isset($_GET['img']);
  $files = json_decode(file_exists($filesFile) ? file_get_contents($filesFile) : '[]', true) ?: [];
  foreach ($files as $f) {
    if ($f['token'] === $token) {
      if ($f['expires'] < time()) {
        http_response_code(410);
        echo 'This link has expired.';
        exit;
      }
      $path = $uploadDir . '/' . $token . '_' . $f['name'];
      if (!file_exists($path)) {
        http_response_code(404);
        echo 'File not found.';
        exit;
      }
      header('Content-Type: ' . ($f['mime'] ?: 'application/octet-stream'));
      header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . addslashes($f['name']) . '"');
      header('Content-Length: ' . filesize($path));
      header('Cache-Control: no-store');
      readfile($path);
      exit;
    }
  }
  http_response_code(404);
  echo 'Not found.';
  exit;
}

// ── Auth ─────────────────────────────────────────────────────────
const SESSION_TTL = 1800; // 30 minutes

// Expire session if older than TTL
if (!empty($_SESSION['admin']) && isset($_SESSION['admin_time'])) {
  if (time() - $_SESSION['admin_time'] > SESSION_TTL) {
    $_SESSION = [];
    session_destroy();
    session_start();
  }
}

if (isset($_POST['logout'])) {
  $_SESSION = [];
  session_destroy();
  header('Location: /admin/');
  exit;
}
if (isset($_POST['password'])) {
  if ($_POST['password'] === ADMIN_PASS) {
    $_SESSION['admin'] = true;
    $_SESSION['admin_time'] = time();
    header('Location: /admin/');
    exit;
  }
  header('Location: /admin/?err=1');
  exit;
}
$authed = !empty($_SESSION['admin']);

// ── Ping (returns 401 when session gone) ─────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'ping') {
  header('Content-Type: application/json');
  if (!$authed) {
    http_response_code(401);
    echo json_encode(['authed' => false]);
    exit;
  }
  echo json_encode(['authed' => true]);
  exit;
}

// ── Re-auth (no session required) ────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'reauth') {
  header('Content-Type: application/json');
  if (($_POST['password'] ?? '') === ADMIN_PASS) {
    $_SESSION['admin'] = true;
    $_SESSION['admin_time'] = time();
    echo json_encode(['ok' => true]);
  } else {
    echo json_encode(['ok' => false]);
  }
  exit;
}

// ── API (authed only) ─────────────────────────────────────────────
if ($authed) {

  // File / image upload
  if (!empty($_FILES['upload']['name'])) {
    header('Content-Type: application/json');
    if ($_FILES['upload']['error'] === UPLOAD_ERR_INI_SIZE || $_FILES['upload']['error'] === UPLOAD_ERR_FORM_SIZE) {
      echo json_encode(['ok' => false, 'error' => 'File exceeds the ' . MAX_UPLOAD_MB . ' MB limit.']);
      exit;
    }
    if ($_FILES['upload']['error'] !== UPLOAD_ERR_OK) {
      echo json_encode(['ok' => false, 'error' => 'Upload failed (error ' . $_FILES['upload']['error'] . ').']);
      exit;
    }
    if ($_FILES['upload']['size'] > MAX_UPLOAD_MB * 1024 * 1024) {
      echo json_encode(['ok' => false, 'error' => 'File exceeds the ' . MAX_UPLOAD_MB . ' MB limit.']);
      exit;
    }
    $type = $_POST['upload_type'] ?? 'file';
    $hours = max(1, min(720, intval($_POST['expire_hours'] ?? 72)));
    $token = bin2hex(random_bytes(8));
    $orig = preg_replace('/[^a-zA-Z0-9._\-]/', '_', basename($_FILES['upload']['name']));
    $mime = $_FILES['upload']['type'] ?: 'application/octet-stream';
    $dest = $uploadDir . '/' . $token . '_' . $orig;
    move_uploaded_file($_FILES['upload']['tmp_name'], $dest);
    $files = json_decode(file_exists($filesFile) ? file_get_contents($filesFile) : '[]', true) ?: [];
    $entry = [
      'token' => $token,
      'name' => $orig,
      'mime' => $mime,
      'type' => $type,
      'size' => filesize($dest),
      'expires' => time() + $hours * 3600,
      'uploaded' => time(),
    ];
    $files[] = $entry;
    file_put_contents($filesFile, json_encode($files, JSON_PRETTY_PRINT));
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'entry' => $entry]);
    exit;
  }

  // JSON actions
  if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];

    if ($action === 'ping') {
      echo json_encode(['authed' => true]);
      exit;
    }
    $snips = json_decode(file_exists($snipsFile) ? file_get_contents($snipsFile) : '[]', true) ?: [];
    $files = json_decode(file_exists($filesFile) ? file_get_contents($filesFile) : '[]', true) ?: [];

    if ($action === 'save_snip') {
      $id = !empty($_POST['id']) ? $_POST['id'] : ('s' . uniqid());
      $label = trim($_POST['label'] ?? '');
      $text = $_POST['text'] ?? '';
      $found = false;
      foreach ($snips as &$s) {
        if ($s['id'] === $id) {
          $s['label'] = $label;
          $s['text'] = $text;
          $s['updated'] = time();
          $found = true;
          break;
        }
      }
      unset($s);
      if (!$found)
        $snips[] = ['id' => $id, 'label' => $label, 'text' => $text, 'created' => time(), 'updated' => time()];
      file_put_contents($snipsFile, json_encode(array_values($snips), JSON_PRETTY_PRINT));
      echo json_encode(['ok' => true, 'id' => $id, 'snips' => array_values($snips)]);
      exit;
    }

    if ($action === 'del_snip') {
      $id = $_POST['id'];
      $snips = array_values(array_filter($snips, fn($s) => $s['id'] !== $id));
      file_put_contents($snipsFile, json_encode($snips, JSON_PRETTY_PRINT));
      echo json_encode(['ok' => true]);
      exit;
    }

    if ($action === 'del_file') {
      $token = $_POST['token'];
      foreach ($files as $f) {
        if ($f['token'] === $token) {
          @unlink($uploadDir . '/' . $token . '_' . $f['name']);
          break;
        }
      }
      $files = array_values(array_filter($files, fn($f) => $f['token'] !== $token));
      file_put_contents($filesFile, json_encode($files, JSON_PRETTY_PRINT));
      echo json_encode(['ok' => true]);
      exit;
    }

    if ($action === 'get_data') {
      $now = time();
      $clean = array_values(array_filter($files, fn($f) => $f['expires'] > $now));
      if (count($clean) !== count($files)) {
        foreach ($files as $f) {
          if ($f['expires'] <= $now)
            @unlink($uploadDir . '/' . $f['token'] . '_' . $f['name']);
        }
        file_put_contents($filesFile, json_encode($clean, JSON_PRETTY_PRINT));
      }
      echo json_encode(['snips' => array_values($snips), 'files' => $clean]);
      exit;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link
    href="https://fonts.googleapis.com/css2?family=DM+Mono:ital,wght@0,300;0,400;0,500;1,400&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,700;1,9..144,300&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="/assets/style.css">
  <link rel="icon" type="image/svg+xml" href="/assets/favicon/favicon.svg">
  <link rel="icon" type="image/png" sizes="96x96" href="/assets/favicon/favicon-96x96.png">
  <link rel="shortcut icon" href="/assets/favicon/favicon.ico">
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/apple-touch-icon.png">
  <link rel="manifest" href="/assets/favicon/site.webmanifest">
  <style>
    body {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 24px;
    }

    /* Lock screen */
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
      color: var(--accent);
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
      color: var(--text4);
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
      color: var(--text);
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
      color: var(--text);
      cursor: pointer;
      transition: background .15s, border-color .15s;
    }

    .lock-submit:hover {
      background: rgba(255, 255, 255, 0.11);
      border-color: var(--border3);
    }

    /* Admin panel */
    .admin-wrap {
      width: 100%;
      max-width: 760px;
    }

    .admin-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 28px;
    }

    .admin-title {
      font-family: var(--serif);
      font-size: 22px;
      font-weight: 300;
      color: var(--accent);
      letter-spacing: -0.01em;
    }

    .admin-title em {
      font-style: italic;
    }

    .logout-btn {
      background: transparent;
      border: 1px solid var(--border);
      border-radius: var(--r-sm);
      padding: 5px 12px;
      font-family: var(--mono);
      font-size: 11px;
      color: var(--text3);
      cursor: pointer;
      transition: border-color .15s, color .15s;
    }

    .logout-btn:hover {
      border-color: var(--border2);
      color: var(--text2);
    }

    /* Tabs */
    .admin-tabs {
      display: flex;
      gap: 4px;
      margin-bottom: 24px;
      background: var(--bg2);
      border: 1px solid var(--border);
      border-radius: var(--r-md);
      padding: 4px;
    }

    .admin-tab {
      flex: 1;
      padding: 9px 12px;
      background: transparent;
      border: none;
      border-radius: var(--r-sm);
      font-family: var(--mono);
      font-size: 12px;
      color: var(--text3);
      cursor: pointer;
      transition: background .15s, color .15s;
      white-space: nowrap;
    }

    .admin-tab:hover {
      color: var(--text2);
    }

    .admin-tab.active {
      background: rgba(255, 255, 255, 0.08);
      color: var(--text);
    }

    /* Sections */
    .admin-section {
      display: none;
    }

    .admin-section.active {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    /* Upload zone */
    .upload-zone {
      border: 1px dashed var(--border2);
      border-radius: var(--r-lg);
      padding: 36px 24px;
      text-align: center;
      cursor: pointer;
      transition: border-color .15s, background .15s;
      position: relative;
    }

    .upload-zone:hover,
    .upload-zone.drag-over {
      border-color: var(--border3);
      background: rgba(255, 255, 255, 0.02);
    }

    .upload-zone input[type="file"] {
      position: absolute;
      inset: 0;
      opacity: 0;
      cursor: pointer;
      width: 100%;
      height: 100%;
    }

    .upload-zone-text {
      font-size: 13px;
      color: var(--text3);
      pointer-events: none;
    }

    .upload-zone-text strong {
      color: var(--text2);
      font-weight: 500;
    }

    /* Expire row */
    .expire-row {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
    }

    .expire-label {
      font-size: 11px;
      color: var(--text4);
      text-transform: uppercase;
      letter-spacing: 0.08em;
    }

    .expire-btn {
      padding: 4px 10px;
      background: transparent;
      border: 1px solid var(--border);
      border-radius: 20px;
      font-family: var(--mono);
      font-size: 11px;
      color: var(--text3);
      cursor: pointer;
      transition: all .15s;
    }

    .expire-btn:hover {
      border-color: var(--border2);
      color: var(--text2);
    }

    .expire-btn.active {
      background: rgba(255, 255, 255, 0.08);
      border-color: var(--border2);
      color: var(--text);
    }

    /* Item list */
    .item-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .item-row {
      background: var(--bg2);
      border: 1px solid var(--border);
      border-radius: var(--r-md);
      padding: 14px 16px;
      display: flex;
      align-items: flex-start;
      gap: 12px;
    }

    .item-row-icon {
      width: 36px;
      height: 36px;
      border-radius: var(--r-sm);
      background: rgba(255, 255, 255, 0.05);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .item-row-icon svg {
      width: 16px;
      height: 16px;
      stroke: var(--text3);
      fill: none;
      stroke-width: 1.5;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .item-thumb {
      width: 36px;
      height: 36px;
      border-radius: var(--r-sm);
      object-fit: cover;
      flex-shrink: 0;
      border: 1px solid var(--border);
    }

    .item-meta {
      flex: 1;
      min-width: 0;
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .item-name {
      font-size: 13px;
      color: var(--text);
      font-weight: 500;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .item-sub {
      font-size: 11px;
      color: var(--text4);
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .item-url {
      font-size: 11px;
      color: var(--text3);
      font-family: var(--mono);
      background: var(--bg3);
      border: 1px solid var(--border);
      border-radius: var(--r-sm);
      padding: 4px 8px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 100%;
      cursor: pointer;
      transition: border-color .15s;
      display: block;
      margin-top: 2px;
    }

    .item-url:hover {
      border-color: var(--border2);
      color: var(--text2);
    }

    .item-actions {
      display: flex;
      gap: 6px;
      flex-shrink: 0;
      align-items: flex-start;
      margin-top: 2px;
    }

    .icon-btn {
      width: 28px;
      height: 28px;
      background: transparent;
      border: 1px solid var(--border);
      border-radius: var(--r-sm);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all .15s;
      color: var(--text3);
      flex-shrink: 0;
    }

    .icon-btn:hover {
      border-color: var(--border2);
      color: var(--text2);
    }

    .icon-btn.danger:hover {
      border-color: rgba(248, 113, 113, 0.4);
      color: #f87171;
    }

    .icon-btn svg {
      width: 13px;
      height: 13px;
      stroke: currentColor;
      fill: none;
      stroke-width: 1.8;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    /* Grid + card layout */
    .item-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 12px;
    }

    .item-card {
      background: var(--bg2);
      border: 1px solid var(--border);
      border-radius: var(--r-md);
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    .item-card-preview {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 80px;
      background: var(--bg3);
      border-bottom: 1px solid var(--border);
    }

    .item-card-preview svg {
      width: 28px;
      height: 28px;
      stroke: var(--text3);
      fill: none;
      stroke-width: 1.5;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .item-card-img {
      width: 100%;
      height: 120px;
      object-fit: cover;
      border-bottom: 1px solid var(--border);
      display: block;
    }

    .item-card-body {
      padding: 10px 12px 8px;
      flex: 1;
      min-width: 0;
    }

    .item-card-name {
      font-size: 12px;
      font-weight: 500;
      color: var(--text);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .item-card-sub {
      font-size: 11px;
      color: var(--text4);
      margin-top: 3px;
      display: flex;
      gap: 8px;
    }

    .item-card-actions {
      display: flex;
      gap: 6px;
      padding: 8px 10px;
      border-top: 1px solid var(--border);
      align-items: center;
    }

    .card-dl-btn {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
      padding: 6px 10px;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid var(--border2);
      border-radius: var(--r-sm);
      font-family: var(--mono);
      font-size: 11px;
      color: var(--text2);
      text-decoration: none;
      transition: background .15s, border-color .15s, color .15s;
    }

    .card-dl-btn svg {
      width: 11px;
      height: 11px;
      stroke: currentColor;
      fill: none;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .card-dl-btn:hover {
      background: rgba(255, 255, 255, 0.09);
      border-color: var(--border3);
      color: var(--text);
    }

    /* Snippet editor */
    .snip-form {
      background: var(--bg2);
      border: 1px solid var(--border);
      border-radius: var(--r-lg);
      padding: 18px;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .snip-form-row {
      display: flex;
      gap: 10px;
      align-items: center;
    }

    .snip-label-input {
      flex: 1;
      background: var(--bg3);
      border: 1px solid var(--border);
      border-radius: var(--r);
      padding: 8px 12px;
      font-family: var(--mono);
      font-size: 12px;
      color: var(--text);
      outline: none;
      transition: border-color .15s;
    }

    .snip-label-input:focus {
      border-color: var(--border3);
    }

    .snip-label-input::placeholder {
      color: var(--text4);
    }

    .snip-textarea {
      width: 100%;
      background: var(--bg3);
      border: 1px solid var(--border);
      border-radius: var(--r);
      padding: 10px 12px;
      font-family: var(--mono);
      font-size: 12px;
      color: var(--text);
      outline: none;
      resize: vertical;
      min-height: 140px;
      line-height: 1.6;
      transition: border-color .15s;
    }

    .snip-textarea:focus {
      border-color: var(--border3);
    }

    .snip-textarea::placeholder {
      color: var(--text4);
    }

    .snip-save-btn {
      padding: 8px 18px;
      background: rgba(255, 255, 255, 0.07);
      border: 1px solid var(--border2);
      border-radius: var(--r);
      font-family: var(--mono);
      font-size: 12px;
      color: var(--text);
      cursor: pointer;
      transition: background .15s;
      white-space: nowrap;
    }

    .snip-save-btn:hover {
      background: rgba(255, 255, 255, 0.11);
    }

    .snip-status {
      font-family: var(--mono);
      font-size: 11px;
      color: var(--text4);
      letter-spacing: 0.02em;
      min-height: 14px;
      transition: color .15s;
    }

    .snip-status.saving { color: var(--text4); }
    .snip-status.saved { color: var(--text3); }
    .snip-status.error { color: #f87171; }

    .snip-text-preview {
      font-size: 12px;
      color: var(--text2);
      white-space: pre-wrap;
      word-break: break-all;
      max-height: 60px;
      overflow: hidden;
      line-height: 1.6;
    }

    .empty-state {
      text-align: center;
      padding: 32px;
      color: var(--text4);
      font-size: 12px;
    }

    .session-overlay {
      position: fixed;
      inset: 0;
      background: var(--bg);
      z-index: 999;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }

    .session-overlay.hidden {
      display: none;
    }

    .upload-progress {
      display: none;
      flex-direction: column;
      gap: 7px;
    }

    .upload-progress.show {
      display: flex;
    }

    .upload-progress-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 8px;
    }

    .upload-progress-name {
      font-size: 11px;
      color: var(--text3);
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      flex: 1;
    }

    .upload-progress-pct {
      font-size: 11px;
      color: var(--text3);
      flex-shrink: 0;
      font-variant-numeric: tabular-nums;
      min-width: 32px;
      text-align: right;
    }

    .upload-progress-track {
      height: 3px;
      background: rgba(255, 255, 255, 0.08);
      border-radius: 2px;
      overflow: hidden;
    }

    .upload-progress-fill {
      height: 100%;
      width: 0%;
      background: var(--green);
      border-radius: 2px;
      transition: width 0.1s linear;
    }
  </style>
</head>

<body>

  <?php if (!$authed): ?>

    <div class="lock-card">
      <div class="lock-title">Admin <em>access</em></div>
      <form method="POST" class="lock-field">
        <label for="pw">Passcode</label>
        <input class="lock-input" type="password" id="pw" name="password" autocomplete="off" autofocus>
        <div class="lock-error <?= isset($_GET['err']) ? 'show' : '' ?>">Incorrect passcode.</div>
        <button class="lock-submit" type="submit">Unlock</button>
      </form>
    </div>

  <?php else: ?>

    <div class="session-overlay hidden" id="session-overlay">
      <div class="lock-card">
        <div class="lock-title">Session <em>expired</em></div>
        <div class="lock-field">
          <label for="reauth-pw">Re-enter passcode</label>
          <input class="lock-input" type="password" id="reauth-pw" autocomplete="off">
          <div class="lock-error" id="reauth-error">Incorrect passcode.</div>
          <button class="lock-submit" id="reauth-submit">Unlock</button>
        </div>
      </div>
    </div>

    <div class="admin-wrap">
      <div class="admin-header">
        <div class="admin-title">Admin <em>tools</em></div>
        <div style="display:flex;align-items:center;gap:10px;">
          <a href="/" class="logout-btn" style="text-decoration:none;">← Home</a>
          <form method="POST" style="margin:0;">
            <button class="logout-btn" name="logout" value="1">Lock</button>
          </form>
        </div>
      </div>

      <div class="admin-tabs">
        <button class="admin-tab active" onclick="switchTab('files')">Share files</button>
        <button class="admin-tab" onclick="switchTab('snippets')">Text snippets</button>
        <button class="admin-tab" onclick="switchTab('images')">Image host</button>
      </div>

      <!-- ── FILE SHARE ── -->
      <div class="admin-section active" id="tab-files">
        <div class="upload-zone" id="file-zone">
          <input type="file" id="file-input" multiple>
          <div class="upload-zone-text">
            <strong>Click or drag</strong> to upload a file
          </div>
        </div>
        <div class="expire-row">
          <span class="expire-label">Expires after</span>
          <button class="expire-btn" data-hours="1" onclick="setExpiry('file',1,this)">1h</button>
          <button class="expire-btn" data-hours="24" onclick="setExpiry('file',24,this)">24h</button>
          <button class="expire-btn active" data-hours="72" onclick="setExpiry('file',72,this)">3 days</button>
          <button class="expire-btn" data-hours="168" onclick="setExpiry('file',168,this)">1 week</button>
        </div>
        <div class="upload-progress" id="file-progress">
          <div class="upload-progress-header">
            <span class="upload-progress-name"></span>
            <span class="upload-progress-pct">0%</span>
          </div>
          <div class="upload-progress-track">
            <div class="upload-progress-fill"></div>
          </div>
        </div>
        <div class="item-list" id="file-list"></div>
      </div>

      <!-- ── SNIPPETS ── -->
      <div class="admin-section" id="tab-snippets">
        <div class="snip-form" id="snip-form">
          <input class="snip-label-input" type="text" id="snip-label" placeholder="Label (optional)">
          <textarea class="snip-textarea" id="snip-text" placeholder="Paste anything — a URL, code, note…"></textarea>
          <div class="snip-form-row">
            <span class="snip-status" id="snip-status"></span>
            <div style="flex:1"></div>
            <button class="btn btn-primary snip-save-btn" onclick="clearSnipForm()">New snippet</button>
          </div>
        </div>
        <div class="item-list" id="snip-list"></div>
      </div>

      <!-- ── IMAGE HOST ── -->
      <div class="admin-section" id="tab-images">
        <div class="upload-zone" id="img-zone">
          <input type="file" id="img-input" multiple accept="image/*">
          <div class="upload-zone-text">
            <strong>Click or drag</strong> to upload an image
          </div>
        </div>
        <div class="expire-row">
          <span class="expire-label">Expires after</span>
          <button class="expire-btn" data-hours="1" onclick="setExpiry('img',1,this)">1h</button>
          <button class="expire-btn" data-hours="24" onclick="setExpiry('img',24,this)">24h</button>
          <button class="expire-btn active" data-hours="72" onclick="setExpiry('img',72,this)">3 days</button>
          <button class="expire-btn" data-hours="168" onclick="setExpiry('img',168,this)">1 week</button>
          <button class="expire-btn" data-hours="720" onclick="setExpiry('img',720,this)">1 month</button>
        </div>
        <div class="upload-progress" id="img-progress">
          <div class="upload-progress-header">
            <span class="upload-progress-name"></span>
            <span class="upload-progress-pct">0%</span>
          </div>
          <div class="upload-progress-track">
            <div class="upload-progress-fill"></div>
          </div>
        </div>
        <div class="item-list" id="img-list"></div>
      </div>
    </div>

  <?php endif; ?>

  <div class="toast" id="toast"></div>
  <script src="/assets/color-math.js?v=<?= APP_VERSION ?>"></script>
  <script>
    const BASE = window.location.origin;
    const MAX_UPLOAD_BYTES = <?= MAX_UPLOAD_MB * 1024 * 1024 ?>;
    const MAX_UPLOAD_LABEL = '<?= MAX_UPLOAD_MB ?> MB';
    let fileExpiry = 72;
    let imgExpiry = 72;
    let editingSnipId = null;

    // ── Expiry ───────────────────────────────────────────────────────
    function setExpiry(type, hours, btn) {
      if (type === 'file') fileExpiry = hours;
      else imgExpiry = hours;
      btn.closest('.expire-row').querySelectorAll('.expire-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    }

    // ── Tab switching ────────────────────────────────────────────────
    function switchTab(tab) {
      document.querySelectorAll('.admin-tab').forEach((b, i) => {
        b.classList.toggle('active', ['files', 'snippets', 'images'][i] === tab);
      });
      document.querySelectorAll('.admin-section').forEach((s, i) => {
        s.classList.toggle('active', ['tab-files', 'tab-snippets', 'tab-images'][i] === 'tab-' + tab);
      });
    }

    // ── Upload ───────────────────────────────────────────────────────
    function uploadFile(file, type) {
      return new Promise(resolve => {
        if (file.size > MAX_UPLOAD_BYTES) {
          showToast(file.name + ' exceeds the ' + MAX_UPLOAD_LABEL + ' limit');
          resolve();
          return;
        }

        const progressEl = document.getElementById(type === 'image' ? 'img-progress' : 'file-progress');
        const nameEl = progressEl.querySelector('.upload-progress-name');
        const pctEl = progressEl.querySelector('.upload-progress-pct');
        const fillEl = progressEl.querySelector('.upload-progress-fill');

        nameEl.textContent = file.name;
        pctEl.textContent = '0%';
        fillEl.style.width = '0%';
        progressEl.classList.add('show');

        const fd = new FormData();
        fd.append('upload', file);
        fd.append('upload_type', type);
        fd.append('expire_hours', type === 'image' ? imgExpiry : fileExpiry);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/admin/');

        xhr.upload.addEventListener('progress', e => {
          if (!e.lengthComputable) return;
          const pct = Math.round((e.loaded / e.total) * 100);
          fillEl.style.width = pct + '%';
          pctEl.textContent = pct + '%';
        });

        xhr.addEventListener('load', async () => {
          fillEl.style.width = '100%';
          pctEl.textContent = '100%';
          setTimeout(async () => {
            progressEl.classList.remove('show');
            try {
              const json = JSON.parse(xhr.responseText);
              if (json.ok) {
                await loadData();
                showToast('Uploaded · ' + file.name);
              } else {
                showToast(json.error || 'Upload failed');
              }
            } catch (_) {
              showToast('Upload failed — server returned an unexpected response');
            }
            resolve();
          }, 350);
        });

        xhr.addEventListener('error', () => {
          progressEl.classList.remove('show');
          showToast('Upload failed — server dropped the connection');
          resolve();
        });

        xhr.addEventListener('abort', () => {
          progressEl.classList.remove('show');
          showToast('Upload cancelled');
          resolve();
        });

        xhr.send(fd);
      });
    }

    function bindUploadZone(zoneId, inputId, type) {
      const zone = document.getElementById(zoneId);
      const input = document.getElementById(inputId);
      if (!zone || !input) return;

      async function runUploads(files) {
        for (const f of files) await uploadFile(f, type);
      }

      input.addEventListener('change', () => {
        const files = [...input.files];
        input.value = '';
        runUploads(files);
      });
      zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
      zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
      zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('drag-over');
        runUploads([...e.dataTransfer.files]);
      });
    }

    // ── Snippets (auto-save) ─────────────────────────────────────────
    let _snipSaveTimer;
    let _snipSavePromise = Promise.resolve();

    function setSnipStatus(text, cls) {
      const el = document.getElementById('snip-status');
      if (!el) return;
      el.textContent = text;
      el.className = 'snip-status' + (cls ? ' ' + cls : '');
    }

    function scheduleSnipAutoSave() {
      clearTimeout(_snipSaveTimer);
      _snipSaveTimer = setTimeout(triggerSnipAutoSave, 800);
    }

    function triggerSnipAutoSave() {
      _snipSaveTimer = null;
      const id = editingSnipId;
      const label = document.getElementById('snip-label').value.trim();
      const text = document.getElementById('snip-text').value;
      if (!text.trim()) return;
      _snipSavePromise = _snipSavePromise.then(() => doSnipSave(id, label, text));
    }

    function flushPendingSnipSave() {
      if (_snipSaveTimer) {
        clearTimeout(_snipSaveTimer);
        triggerSnipAutoSave();
      }
    }

    async function doSnipSave(id, label, text) {
      setSnipStatus('Saving…', 'saving');
      const fd = new FormData();
      fd.append('action', 'save_snip');
      fd.append('id', id || '');
      fd.append('label', label);
      fd.append('text', text);
      try {
        const res = await fetch('/admin/', { method: 'POST', body: fd });
        const json = await res.json();
        if (json.ok) {
          if (!id && json.id && !editingSnipId) editingSnipId = json.id;
          _lastDataString = '';
          renderSnips(json.snips);
          setSnipStatus('Saved', 'saved');
        } else {
          setSnipStatus('Save failed', 'error');
        }
      } catch (_) {
        setSnipStatus('Save failed', 'error');
      }
    }

    function editSnip(id, label, text) {
      flushPendingSnipSave();
      editingSnipId = id;
      document.getElementById('snip-label').value = label;
      document.getElementById('snip-text').value = text;
      document.getElementById('snip-text').focus();
      setSnipStatus('Saved', 'saved');
    }

    function clearSnipForm() {
      flushPendingSnipSave();
      editingSnipId = null;
      document.getElementById('snip-label').value = '';
      document.getElementById('snip-text').value = '';
      setSnipStatus('');
      document.getElementById('snip-text').focus();
    }

    async function delSnip(id) {
      const fd = new FormData();
      fd.append('action', 'del_snip');
      fd.append('id', id);
      await fetch('/admin/', { method: 'POST', body: fd });
      await loadData();
      showToast('Deleted');
    }

    async function delFile(token) {
      const fd = new FormData();
      fd.append('action', 'del_file');
      fd.append('token', token);
      await fetch('/admin/', { method: 'POST', body: fd });
      await loadData();
      showToast('Deleted');
    }

    // ── Render ───────────────────────────────────────────────────────
    function timeLeft(exp) {
      const s = exp - Math.floor(Date.now() / 1000);
      if (s <= 0) return 'expired';
      if (s < 3600) return Math.round(s / 60) + 'm left';
      if (s < 86400) return Math.round(s / 3600) + 'h left';
      return Math.round(s / 86400) + 'd left';
    }

    function fmtSize(bytes) {
      if (bytes < 1024) return bytes + ' B';
      if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
      return (bytes / 1024 / 1024).toFixed(1) + ' MB';
    }

    function copyAndToast(text) {
      navigator.clipboard.writeText(text);
      showToast('Copied!');
    }

    function renderFiles(files) {
      const el = document.getElementById('file-list');
      const list = files.filter(f => f.type === 'file');
      el.className = 'item-list';
      if (!list.length) { el.innerHTML = '<div class="empty-state">No active files</div>'; return; }
      el.innerHTML = '';
      list.slice().reverse().forEach(f => {
        const url = BASE + '/admin/?token=' + f.token;
        const row = document.createElement('div');
        row.className = 'item-row';
        row.innerHTML = `
      <div class="item-row-icon">
        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      </div>
      <div class="item-meta">
        <div class="item-name" title="${f.name}">${f.name}</div>
        <div class="item-sub"><span>${fmtSize(f.size)}</span><span>${timeLeft(f.expires)}</span></div>
      </div>
      <div class="item-actions">
        <a class="icon-btn" title="Download" href="${url}" download="${f.name}">
          <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        </a>
        <button class="icon-btn" title="Copy link" onclick="copyAndToast('${url}')">
          <svg viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
        </button>
        <button class="icon-btn danger" title="Delete" onclick="delFile('${f.token}')">
          <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
        </button>
      </div>`;
        el.appendChild(row);
      });
    }

    function renderImages(files) {
      const el = document.getElementById('img-list');
      const list = files.filter(f => f.type === 'image');
      if (!list.length) { el.className = 'item-list'; el.innerHTML = '<div class="empty-state">No hosted images</div>'; return; }
      el.className = 'item-grid';
      el.innerHTML = '';
      list.slice().reverse().forEach(f => {
        const imgUrl = BASE + '/admin/?img=' + f.token;
        const dlUrl = BASE + '/admin/?token=' + f.token;
        const card = document.createElement('div');
        card.className = 'item-card';
        card.innerHTML = `
      <img class="item-card-img" src="${imgUrl}" alt="${f.name}" loading="lazy">
      <div class="item-card-body">
        <div class="item-card-name" title="${f.name}">${f.name}</div>
        <div class="item-card-sub"><span>${fmtSize(f.size)}</span><span>${timeLeft(f.expires)}</span></div>
      </div>
      <div class="item-card-actions">
        <a class="card-dl-btn" href="${dlUrl}" download="${f.name}">
          <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Download
        </a>
        <button class="icon-btn" title="Copy link" onclick="copyAndToast('${imgUrl}')">
          <svg viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
        </button>
        <button class="icon-btn danger" title="Delete" onclick="delFile('${f.token}')">
          <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
        </button>
      </div>`;
        el.appendChild(card);
      });
    }

    function renderSnips(snips) {
      const el = document.getElementById('snip-list');
      if (!snips.length) { el.innerHTML = '<div class="empty-state">No snippets yet</div>'; return; }
      el.innerHTML = '';
      snips.slice().reverse().forEach(s => {
        const row = document.createElement('div');
        row.className = 'item-row';
        const label = s.label || 'Snippet';
        row.innerHTML = `
      <div class="item-row-icon"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></div>
      <div class="item-meta">
        <div class="item-name">${label}</div>
        <div class="snip-text-preview">${s.text.replace(/</g, '&lt;')}</div>
      </div>
      <div class="item-actions">
        <button class="icon-btn" title="Copy text" onclick="copyAndToast(${JSON.stringify(s.text)})">
          <svg viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
        </button>
        <button class="icon-btn" title="Edit" onclick='editSnip(${JSON.stringify(s.id)}, ${JSON.stringify(s.label || "")}, ${JSON.stringify(s.text)})'>
          <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        </button>
        <button class="icon-btn danger" title="Delete" onclick="delSnip('${s.id}')">
          <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
        </button>
      </div>`;
        el.appendChild(row);
      });
    }

    // ── Load data ────────────────────────────────────────────────────
    let _lastDataString = '';
    async function loadData() {
      const fd = new FormData();
      fd.append('action', 'get_data');
      const res = await fetch('/admin/', { method: 'POST', body: fd });
      if (!res.ok) return;
      const text = await res.text();
      if (text === _lastDataString) return;
      _lastDataString = text;
      let json;
      try { json = JSON.parse(text); } catch (_) { return; }
      renderFiles(json.files || []);
      renderImages(json.files || []);
      renderSnips(json.snips || []);
    }

    // ── Toast ────────────────────────────────────────────────────────
    function showToast(msg) {
      let t = document.getElementById('toast');
      t.textContent = msg;
      t.classList.add('show');
      clearTimeout(t._timer);
      t._timer = setTimeout(() => t.classList.remove('show'), 1800);
    }

    // ── Session watchdog ─────────────────────────────────────────────
    <?php if ($authed): ?>
      let _sessionInterval;
      let _dataPollInterval;

      async function pingSession() {
        const fd = new FormData();
        fd.append('action', 'ping');
        try {
          const res = await fetch('/admin/', { method: 'POST', body: fd });
          if (res.status === 401) showSessionExpired();
        } catch (_) { }
      }

      function showSessionExpired() {
        clearInterval(_sessionInterval);
        clearInterval(_dataPollInterval);
        const overlay = document.getElementById('session-overlay');
        overlay.classList.remove('hidden');
        setTimeout(() => document.getElementById('reauth-pw').focus(), 50);
      }

      document.getElementById('reauth-submit').addEventListener('click', reauth);
      document.getElementById('reauth-pw').addEventListener('keydown', e => {
        if (e.key === 'Enter') reauth();
      });

      async function reauth() {
        const pw = document.getElementById('reauth-pw').value;
        const fd = new FormData();
        fd.append('action', 'reauth');
        fd.append('password', pw);
        const res = await fetch('/admin/', { method: 'POST', body: fd });
        const json = await res.json();
        if (json.ok) {
          document.getElementById('session-overlay').classList.add('hidden');
          document.getElementById('reauth-pw').value = '';
          document.getElementById('reauth-error').classList.remove('show');
          _sessionInterval = setInterval(pingSession, 60000);
          _dataPollInterval = setInterval(() => loadData().catch(() => {}), 30000);
          loadData();
        } else {
          document.getElementById('reauth-error').classList.add('show');
        }
      }

      // ── Init ─────────────────────────────────────────────────────────
      bindUploadZone('file-zone', 'file-input', 'file');
      bindUploadZone('img-zone', 'img-input', 'image');
      document.getElementById('snip-label').addEventListener('input', scheduleSnipAutoSave);
      document.getElementById('snip-text').addEventListener('input', scheduleSnipAutoSave);
      loadData();
      _sessionInterval = setInterval(pingSession, 60000);
      _dataPollInterval = setInterval(() => loadData().catch(() => {}), 30000);
    <?php endif; ?>
  </script>

</body>

</html>