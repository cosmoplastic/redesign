<?php
session_start();
require_once __DIR__ . '/../includes/version.php';

header('X-Robots-Tag: noindex, nofollow', true);

require_once __DIR__ . '/../includes/admin-auth.php';
define('MAX_UPLOAD_MB', 512);

// ── Humanizer (Anthropic) config — key lives in the gitignored secret file ──
$hzSecret = __DIR__ . '/../includes/humanizer-secret.php';
if (file_exists($hzSecret))
  require_once $hzSecret;
require_once __DIR__ . '/../includes/humanizer.php';
$HZ_MODEL = 'claude-opus-4-8';
$hzKey = getenv('ANTHROPIC_API_KEY') ?: (defined('ANTHROPIC_API_KEY') ? ANTHROPIC_API_KEY : '');
$hzKeyConfigured = $hzKey && $hzKey !== 'sk-ant-REPLACE_ME';

$dataDir = __DIR__ . '/data';
$uploadDir = __DIR__ . '/uploads';

if (!is_dir($dataDir))
  mkdir($dataDir, 0755, true);
if (!is_dir($uploadDir))
  mkdir($uploadDir, 0755, true);

$snipsFile = $dataDir . '/snippets.json';
$filesFile = $dataDir . '/files.json';

// Generate a small JPEG thumbnail for an image upload (best-effort; needs GD).
function make_thumb(string $srcPath, string $mime, string $destPath, int $maxDim = 400): bool
{
  if (!function_exists('imagecreatetruecolor'))
    return false;
  switch ($mime) {
    case 'image/jpeg':
    case 'image/jpg':
      $img = @imagecreatefromjpeg($srcPath);
      break;
    case 'image/png':
      $img = @imagecreatefrompng($srcPath);
      break;
    case 'image/gif':
      $img = @imagecreatefromgif($srcPath);
      break;
    case 'image/webp':
      $img = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($srcPath) : false;
      break;
    default:
      return false;
  }
  if (!$img)
    return false;
  $w = imagesx($img);
  $h = imagesy($img);
  if ($w < 1 || $h < 1) {
    imagedestroy($img);
    return false;
  }
  $scale = min(1, $maxDim / max($w, $h));
  $tw = max(1, (int) round($w * $scale));
  $th = max(1, (int) round($h * $scale));
  $thumb = imagecreatetruecolor($tw, $th);
  // Flatten onto a dark background so transparent images blend with the UI.
  $bg = imagecolorallocate($thumb, 20, 20, 22);
  imagefilledrectangle($thumb, 0, 0, $tw, $th, $bg);
  imagecopyresampled($thumb, $img, 0, 0, 0, 0, $tw, $th, $w, $h);
  $ok = imagejpeg($thumb, $destPath, 82);
  imagedestroy($img);
  imagedestroy($thumb);
  return (bool) $ok;
}

// ── Public file/image serving (no auth required) ─────────────────
if (isset($_GET['token']) || isset($_GET['img']) || isset($_GET['thumb'])) {
  $token = $_GET['token'] ?? $_GET['img'] ?? $_GET['thumb'];
  $wantThumb = isset($_GET['thumb']);
  $inline = isset($_GET['img']) || $wantThumb;
  $files = json_decode(file_exists($filesFile) ? file_get_contents($filesFile) : '[]', true) ?: [];
  foreach ($files as $f) {
    if ($f['token'] === $token) {
      if ($f['expires'] < time()) {
        http_response_code(410);
        echo 'This link has expired.';
        exit;
      }
      // Serve the generated thumbnail when asked (falls back to the full image below).
      if ($wantThumb) {
        $thumbPath = $uploadDir . '/' . $token . '_thumb.jpg';
        if (file_exists($thumbPath)) {
          header('Content-Type: image/jpeg');
          header('Content-Disposition: inline');
          header('Content-Length: ' . filesize($thumbPath));
          header('Cache-Control: private, max-age=3600');
          readfile($thumbPath);
          exit;
        }
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
      header('Cache-Control: ' . ($wantThumb ? 'private, max-age=3600' : 'no-store'));
      readfile($path);
      exit;
    }
  }
  http_response_code(404);
  echo 'Not found.';
  exit;
}

// ── Auth ─────────────────────────────────────────────────────────
// ADMIN_PASS + SESSION_TTL come from includes/admin-auth.php.
// The login form lives at /password/ — this page only renders when authed.

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
  header('Location: /password/');
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
    $isImage = strpos($mime, 'image/') === 0;
    $hasThumb = $isImage ? make_thumb($dest, $mime, $uploadDir . '/' . $token . '_thumb.jpg') : false;
    $files = json_decode(file_exists($filesFile) ? file_get_contents($filesFile) : '[]', true) ?: [];
    $entry = [
      'token' => $token,
      'name' => $orig,
      'mime' => $mime,
      'type' => $type,
      'img' => $isImage,
      'thumb' => $hasThumb,
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

    if ($action === 'humanize') {
      header('Cache-Control: no-store');
      $text = (string) ($_POST['text'] ?? '');
      if (trim($text) === '') {
        echo json_encode(['ok' => false, 'error' => 'Enter some text first.']);
        exit;
      }
      if (mb_strlen($text) > 24000) {
        echo json_encode(['ok' => false, 'error' => 'Text is too long (24,000 character limit). Split it into chunks.']);
        exit;
      }
      if (!$hzKeyConfigured) {
        echo json_encode(['ok' => false, 'error' => 'Server not configured: add your Anthropic API key to includes/humanizer-secret.php.']);
        exit;
      }
      if (!function_exists('curl_init')) {
        echo json_encode(['ok' => false, 'error' => 'Server is missing the PHP curl extension.']);
        exit;
      }
      echo json_encode(humanizer_run($hzKey, $HZ_MODEL, $text), JSON_UNESCAPED_UNICODE);
      exit;
    }

    $snips = json_decode(file_exists($snipsFile) ? file_get_contents($snipsFile) : '[]', true) ?: [];
    $files = json_decode(file_exists($filesFile) ? file_get_contents($filesFile) : '[]', true) ?: [];

    if ($action === 'save_snip') {
      $id = !empty($_POST['id']) ? $_POST['id'] : ('s' . uniqid());
      $text = $_POST['text'] ?? '';
      $found = false;
      foreach ($snips as &$s) {
        if ($s['id'] === $id) {
          $s['text'] = $text;
          $s['updated'] = time();
          $found = true;
          break;
        }
      }
      unset($s);
      if (!$found)
        $snips[] = ['id' => $id, 'text' => $text, 'created' => time(), 'updated' => time()];
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
          @unlink($uploadDir . '/' . $token . '_thumb.jpg');
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
          if ($f['expires'] <= $now) {
            @unlink($uploadDir . '/' . $f['token'] . '_' . $f['name']);
            @unlink($uploadDir . '/' . $f['token'] . '_thumb.jpg');
          }
        }
        file_put_contents($filesFile, json_encode($clean, JSON_PRETTY_PRINT));
      }
      echo json_encode(['snips' => array_values($snips), 'files' => $clean]);
      exit;
    }
  }
}

// Not signed in → send to the gate. (Keeps the lock screen and the panel on
// separate URLs so a cached lock screen can never mask the panel after login.)
if (!$authed) {
  header('Location: /password/');
  exit;
}
header('Cache-Control: no-store, must-revalidate');
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
  <link rel="stylesheet" href="<?= asset_versioned_path('/assets/style.css') ?>">
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
      justify-content: flex-start;
      min-height: 100vh;
      padding: 56px 24px 64px;
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

    /* Admin panel */
    .admin-wrap {
      width: 100%;
      max-width: 1080px;
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
      color: var(--color-primary-100);
      letter-spacing: -0.01em;
    }

    .admin-title em {
      font-style: italic;
    }

    .logout-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      box-sizing: border-box;
      height: 30px;
      padding: 0 13px;
      background: transparent;
      border: 1px solid var(--border);
      border-radius: var(--r-sm);
      font-family: var(--mono);
      font-size: 12px;
      font-weight: 400;
      line-height: 1;
      letter-spacing: 0.02em;
      text-transform: none;
      color: var(--color-primary-300);
      cursor: pointer;
      transition: border-color .15s, color .15s;
    }

    .logout-btn:hover {
      border-color: var(--border2);
      color: var(--color-primary-200);
    }

    /* Icon glyphs (SVG masked so they inherit the button text color) */
    .btn-icon {
      display: inline-block;
      width: 13px;
      height: 13px;
      flex-shrink: 0;
      background-color: currentColor;
      -webkit-mask: var(--icon) center / contain no-repeat;
      mask: var(--icon) center / contain no-repeat;
    }

    /* Home link on the lock screen */
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

    .lock-home .btn-icon {
      width: 14px;
      height: 14px;
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
      color: var(--color-primary-300);
      cursor: pointer;
      transition: background .15s, color .15s;
      white-space: nowrap;
    }

    .admin-tab:hover {
      color: var(--color-primary-200);
    }

    .admin-tab.active {
      background: rgba(255, 255, 255, 0.08);
      color: var(--color-primary-100);
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
      color: var(--color-primary-300);
      pointer-events: none;
    }

    .upload-zone-text strong {
      color: var(--color-primary-200);
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
      color: var(--color-primary-400);
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
      color: var(--color-primary-300);
      cursor: pointer;
      transition: all .15s;
    }

    .expire-btn:hover {
      border-color: var(--border2);
      color: var(--color-primary-200);
    }

    .expire-btn.active {
      background: rgba(255, 255, 255, 0.08);
      border-color: var(--border2);
      color: var(--color-primary-100);
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
      stroke: var(--color-primary-300);
      fill: none;
      stroke-width: 1.5;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    /* Image files show a clickable thumbnail in the row's icon slot */
    .item-row-thumb {
      overflow: hidden;
      background: rgba(0, 0, 0, 0.3);
      cursor: zoom-in;
    }

    .item-row-thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
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
      color: var(--color-primary-100);
      font-weight: 500;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .item-sub {
      font-size: 11px;
      color: var(--color-primary-400);
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .item-url {
      font-size: 11px;
      color: var(--color-primary-300);
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
      color: var(--color-primary-200);
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
      color: var(--color-primary-300);
      flex-shrink: 0;
    }

    .icon-btn:hover {
      border-color: var(--border2);
      color: var(--color-primary-200);
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
      stroke: var(--color-primary-300);
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
      cursor: zoom-in;
    }

    .item-card-body {
      padding: 10px 12px 8px;
      flex: 1;
      min-width: 0;
    }

    .item-card-name {
      font-size: 12px;
      font-weight: 500;
      color: var(--color-primary-100);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .item-card-sub {
      font-size: 11px;
      color: var(--color-primary-400);
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
      color: var(--color-primary-200);
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
      color: var(--color-primary-100);
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

    .snip-textarea {
      width: 100%;
      background: var(--bg3);
      border: 1px solid var(--border);
      border-radius: var(--r);
      padding: 10px 12px;
      font-family: var(--mono);
      font-size: 12px;
      color: var(--color-primary-100);
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
      color: var(--color-primary-400);
    }

    @media (min-width: 1024px) {
      .snip-textarea {
        min-height: 280px;
      }
    }

    .snip-save-btn {
      padding: 8px 18px;
      background: rgba(255, 255, 255, 0.07);
      border: 1px solid var(--border2);
      border-radius: var(--r);
      font-family: var(--mono);
      font-size: 12px;
      color: var(--color-primary-100);
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
      color: var(--color-primary-400);
      letter-spacing: 0.02em;
      min-height: 14px;
      transition: color .15s;
    }

    .snip-status.saving {
      color: var(--color-primary-400);
    }

    .snip-status.saved {
      color: var(--color-primary-300);
    }

    .snip-status.error {
      color: #f87171;
    }

    /* Snippet rows — Apple Notes style: title, then date + preview inline */
    .snip-row .item-name {
      font-size: 14px;
      font-weight: 600;
    }

    .snip-line {
      display: flex;
      align-items: baseline;
      gap: 8px;
      min-width: 0;
      font-size: 12px;
      line-height: 1.45;
    }

    .snip-date {
      flex-shrink: 0;
      color: var(--color-primary-200);
      font-weight: 500;
    }

    .snip-preview {
      min-width: 0;
      color: var(--color-primary-400);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      text-transform: none;
    }

    .snip-preview-empty {
      font-style: italic;
      opacity: 0.8;
    }

    .empty-state {
      text-align: center;
      padding: 32px;
      color: var(--color-primary-400);
      font-size: 12px;
    }

    /* Page-wide drag & drop overlay */
    .page-drop-overlay {
      position: fixed;
      inset: 0;
      z-index: 900;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 24px;
      background: rgba(10, 12, 16, 0.82);
      -webkit-backdrop-filter: blur(3px);
      backdrop-filter: blur(3px);
      pointer-events: none;
    }

    .page-drop-overlay.show {
      display: flex;
    }

    .page-drop-box {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 14px;
      width: min(92%, 460px);
      padding: 48px 32px;
      border: 2px dashed var(--border3);
      border-radius: var(--r-lg);
      text-align: center;
    }

    .page-drop-box svg {
      width: 40px;
      height: 40px;
      fill: none;
      stroke: var(--color-primary-100);
      stroke-width: 1.5;
      stroke-linecap: round;
      stroke-linejoin: round;
      opacity: 0.9;
    }

    .page-drop-title {
      font-family: var(--mono);
      font-size: 14px;
      letter-spacing: 0.02em;
      color: var(--color-primary-100);
    }

    .page-drop-sub {
      font-family: var(--mono);
      font-size: 11px;
      color: var(--color-primary-300);
    }

    /* ── Image lightbox ── */
    .img-lightbox {
      position: fixed;
      inset: 0;
      z-index: 950;
      display: none;
    }

    .img-lightbox.open {
      display: block;
    }

    .lightbox-backdrop {
      position: absolute;
      inset: 0;
      background: rgba(0, 0, 0, 0.86);
      -webkit-backdrop-filter: blur(4px);
      backdrop-filter: blur(4px);
    }

    .lightbox-content {
      position: relative;
      z-index: 1;
      display: flex;
      flex-direction: column;
      height: 100%;
      padding: 16px 20px 18px;
      gap: 14px;
    }

    .lightbox-topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      flex-shrink: 0;
    }

    .lightbox-name {
      font-family: var(--mono);
      font-size: 12px;
      color: var(--color-primary-200);
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      min-width: 0;
    }

    .lightbox-actions {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-shrink: 0;
    }

    .lightbox-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      box-sizing: border-box;
      height: 32px;
      padding: 0 12px;
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid var(--border2);
      border-radius: var(--r-sm);
      font-family: var(--mono);
      font-size: 12px;
      color: var(--color-primary-100);
      text-decoration: none;
      cursor: pointer;
      transition: background .15s, border-color .15s;
    }

    .lightbox-btn:hover {
      background: rgba(255, 255, 255, 0.11);
      border-color: var(--border3);
    }

    .lightbox-btn svg {
      width: 14px;
      height: 14px;
      stroke: currentColor;
      fill: none;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .lightbox-icon-btn {
      width: 32px;
      padding: 0;
    }

    .lightbox-close {
      width: 32px;
      height: 32px;
      padding: 0;
      font-size: 20px;
      line-height: 1;
    }

    .lightbox-stage {
      position: relative;
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 0;
    }

    .lightbox-img {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
      border-radius: var(--r);
    }

    .lightbox-nav {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 44px;
      height: 44px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(0, 0, 0, 0.5);
      border: 1px solid var(--border2);
      color: #fff;
      font-size: 26px;
      line-height: 1;
      cursor: pointer;
      transition: background .15s, border-color .15s;
    }

    .lightbox-nav:hover {
      background: rgba(0, 0, 0, 0.8);
      border-color: var(--border3);
    }

    .lightbox-prev {
      left: 6px;
    }

    .lightbox-next {
      right: 6px;
    }

    .lightbox-strip {
      display: flex;
      gap: 8px;
      justify-content: center;
      overflow-x: auto;
      flex-shrink: 0;
      padding: 4px 2px;
    }

    .lightbox-thumb {
      width: 58px;
      height: 58px;
      flex-shrink: 0;
      object-fit: cover;
      border-radius: var(--r-sm);
      border: 2px solid transparent;
      opacity: 0.45;
      cursor: pointer;
      transition: opacity .15s, border-color .15s;
    }

    .lightbox-thumb:hover {
      opacity: 0.8;
    }

    .lightbox-thumb.active {
      opacity: 1;
      border-color: var(--color-primary-200);
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

    /* Upload queue — one row per selected file (active animates, pending sit at 0%) */
    .upload-queue {
      display: flex;
      flex-direction: column;
      gap: 16px;
      margin: 18px 0 4px;
    }

    .upload-queue:empty {
      display: none;
      margin: 0;
    }

    .upload-row {
      display: flex;
      flex-direction: column;
      gap: 7px;
    }

    .upload-row-head {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 8px;
    }

    .upload-row-name {
      font-size: 11px;
      color: var(--color-primary-300);
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      flex: 1;
    }

    .upload-row-pct {
      font-size: 11px;
      color: var(--color-primary-300);
      flex-shrink: 0;
      font-variant-numeric: tabular-nums;
      min-width: 32px;
      text-align: right;
    }

    .upload-row.pending .upload-row-name,
    .upload-row.pending .upload-row-pct {
      color: var(--color-primary-400);
    }

    .upload-row-track {
      height: 3px;
      background: rgba(255, 255, 255, 0.08);
      border-radius: 2px;
      overflow: hidden;
    }

    .upload-row-fill {
      height: 100%;
      width: 0%;
      background: var(--green);
      border-radius: 2px;
      transition: width 0.1s linear;
    }

    /* ── Humanizer tab ── */
    .hz-warn {
      font-family: var(--mono);
      font-size: 12px;
      line-height: 1.6;
      color: #fbbf77;
      background: rgba(251, 146, 60, 0.08);
      border: 1px solid rgba(251, 146, 60, 0.25);
      border-radius: var(--r);
      padding: 10px 14px;
    }

    .hz-warn code {
      color: #fdba74;
    }

    .hz-panes {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
      align-items: stretch;
    }

    .hz-pane {
      display: flex;
      flex-direction: column;
      background: var(--bg2);
      border: 1px solid var(--border);
      border-radius: var(--r-lg);
      overflow: hidden;
      min-height: 440px;
    }

    .hz-pane-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      padding: 11px 14px;
      border-bottom: 1px solid var(--border);
      flex-shrink: 0;
    }

    .hz-pane-label {
      font-family: var(--mono);
      font-size: 10px;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--color-primary-400);
    }

    .hz-textarea {
      flex: 1;
      width: 100%;
      resize: none;
      border: none;
      outline: none;
      background: transparent;
      padding: 14px;
      font-family: var(--mono);
      font-size: 13px;
      line-height: 1.7;
      letter-spacing: 0.01em;
      color: var(--color-primary-100);
      text-transform: none;
    }

    .hz-textarea::placeholder {
      color: var(--color-primary-400);
    }

    .hz-foot {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      padding: 11px 14px;
      border-top: 1px solid var(--border);
      flex-shrink: 0;
    }

    .hz-count {
      font-family: var(--mono);
      font-size: 11px;
      color: var(--color-primary-400);
      font-variant-numeric: tabular-nums;
    }

    .hz-output {
      flex: 1;
      overflow-y: auto;
      padding: 14px;
      font-family: var(--mono);
      font-size: 13px;
      line-height: 1.7;
      letter-spacing: 0.01em;
      color: var(--color-primary-100);
      white-space: pre-wrap;
      word-wrap: break-word;
      text-transform: none;
    }

    .hz-output-error {
      color: #f87171;
    }

    .hz-placeholder {
      color: var(--color-primary-400);
    }

    .hz-head-actions {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .hz-copy {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: transparent;
      border: 1px solid var(--border2);
      border-radius: var(--r-sm);
      padding: 4px 10px;
      font-family: var(--mono);
      font-size: 11px;
      color: var(--color-primary-300);
      cursor: pointer;
      transition: border-color .15s, color .15s, opacity .15s;
    }

    .hz-copy svg {
      width: 12px;
      height: 12px;
      stroke: currentColor;
      fill: none;
      stroke-width: 2;
    }

    .hz-copy:hover {
      border-color: var(--border3);
      color: var(--color-primary-100);
    }

    .hz-copy:disabled {
      opacity: 0.4;
      cursor: default;
    }

    .hz-copy.is-active {
      border-color: var(--green);
      color: var(--green);
    }

    .hz-del {
      background: rgba(248, 113, 113, 0.15);
      color: #fca5a5;
      text-decoration: line-through;
      text-decoration-color: rgba(248, 113, 113, 0.55);
      border-radius: 2px;
      box-decoration-break: clone;
      -webkit-box-decoration-break: clone;
    }

    .hz-ins {
      background: rgba(110, 231, 160, 0.16);
      color: #86efac;
      text-decoration: none;
      border-radius: 2px;
      box-decoration-break: clone;
      -webkit-box-decoration-break: clone;
    }

    /* ── Border-glow beam on the Humanize button ─────────────────────────
       Ported from the Border Glow tool (sm/button preset, dark, radius 6).
       Mono while idle; the .hz-running class swaps to the sunset palette
       while the command runs. A tiny rAF loop drives --beam-angle so the
       beam rotates in every browser (see the humanizer script). */
    #hz-run {
      position: relative;
      border-radius: 6px;
      background: #1d1d1f;
      border: 1px solid transparent;
      color: var(--color-text-100);
      overflow: hidden;
    }

    #hz-run:hover {
      background: #242426;
    }

    #hz-run:disabled {
      opacity: 1;
    }

    #hz-run .hz-run-label {
      position: relative;
      z-index: 0;
      display: inline-flex;
      align-items: center;
      gap: 7px;
    }

    /* Stroke — stationary colour field revealed by a rotating conic window */
    #hz-run::after {
      content: "";
      position: absolute;
      inset: 0;
      border-radius: 5px;
      padding: 1px;
      clip-path: inset(0 round 6px);
      background:
        conic-gradient(from var(--beam-angle, 0deg), transparent 0%, transparent 54%, rgba(255, 255, 255, 0.1) 57%, rgba(255, 255, 255, 0.3) 60%, rgba(255, 255, 255, 0.6) 63%, rgba(255, 255, 255, 0.75) 66%, rgba(255, 255, 255, 0.6) 69%, rgba(255, 255, 255, 0.3) 72%, rgba(255, 255, 255, 0.1) 75%, transparent 78%, transparent 100%),
        radial-gradient(ellipse 9px 18px at 2% 68%, rgb(160, 160, 160), transparent),
        radial-gradient(ellipse 4px 8px at 2% 68%, rgb(140, 140, 140), transparent),
        radial-gradient(ellipse 59px 9px at 72% -3%, rgb(180, 180, 180), transparent),
        radial-gradient(ellipse 42px 7px at 74% 100%, rgb(150, 150, 150), transparent),
        radial-gradient(ellipse 10px 17px at 100% 27%, rgb(170, 170, 170), transparent),
        radial-gradient(ellipse 10px 18px at 100% 27%, rgb(155, 155, 155), transparent),
        radial-gradient(ellipse 5px 10px at 100% 27%, rgb(145, 145, 145), transparent),
        radial-gradient(ellipse 11px 12px at 100% 27%, rgb(165, 165, 165), transparent);
      -webkit-mask:
        conic-gradient(from var(--beam-angle, 0deg), transparent 0%, transparent 30%, rgba(255, 255, 255, 0.1) 36%, rgba(255, 255, 255, 0.35) 44%, white 52%, white 80%, rgba(255, 255, 255, 0.35) 86%, rgba(255, 255, 255, 0.1) 92%, transparent 95%, transparent 100%),
        linear-gradient(#fff 0 0) content-box,
        linear-gradient(#fff 0 0);
      -webkit-mask-composite: source-in, xor;
      mask:
        conic-gradient(from var(--beam-angle, 0deg), transparent 0%, transparent 30%, rgba(255, 255, 255, 0.1) 36%, rgba(255, 255, 255, 0.35) 44%, white 52%, white 80%, rgba(255, 255, 255, 0.35) 86%, rgba(255, 255, 255, 0.1) 92%, transparent 95%, transparent 100%),
        linear-gradient(#fff 0 0) content-box,
        linear-gradient(#fff 0 0);
      mask-composite: intersect, exclude;
      pointer-events: none;
      z-index: 2;
      opacity: 0.230;
      filter: brightness(1.30) saturate(1.20);
    }

    /* Inner glow — soft colour bleed inside the element */
    #hz-run::before {
      content: "";
      position: absolute;
      inset: 0;
      border-radius: 6px;
      clip-path: inset(0 round 6px);
      background:
        radial-gradient(ellipse 9px 18px at 2% 68%, rgba(160, 160, 160, 0.25), transparent),
        radial-gradient(ellipse 4px 8px at 2% 68%, rgba(140, 140, 140, 0.22), transparent),
        radial-gradient(ellipse 59px 9px at 72% -3%, rgba(180, 180, 180, 0.17), transparent),
        radial-gradient(ellipse 42px 7px at 74% 100%, rgba(150, 150, 150, 0.17), transparent),
        radial-gradient(ellipse 10px 17px at 100% 27%, rgba(170, 170, 170, 0.15), transparent),
        radial-gradient(ellipse 10px 18px at 100% 27%, rgba(155, 155, 155, 0.2), transparent),
        radial-gradient(ellipse 5px 10px at 100% 27%, rgba(145, 145, 145, 0.15), transparent),
        radial-gradient(ellipse 11px 12px at 100% 27%, rgba(165, 165, 165, 0.15), transparent);
      box-shadow: inset 0 0 5px 1px rgba(255, 255, 255, 0.3);
      -webkit-mask-image: conic-gradient(from var(--beam-angle, 0deg), transparent 0%, transparent 22%, rgba(255, 255, 255, 0.12) 28%, rgba(255, 255, 255, 0.4) 36%, white 46%, white 82%, rgba(255, 255, 255, 0.4) 88%, rgba(255, 255, 255, 0.12) 94%, transparent 97%, transparent 100%);
      -webkit-mask-composite: source-over;
      mask-image: conic-gradient(from var(--beam-angle, 0deg), transparent 0%, transparent 22%, rgba(255, 255, 255, 0.12) 28%, rgba(255, 255, 255, 0.4) 36%, white 46%, white 82%, rgba(255, 255, 255, 0.4) 88%, rgba(255, 255, 255, 0.12) 94%, transparent 97%, transparent 100%);
      mask-composite: add;
      pointer-events: none;
      z-index: 1;
      opacity: 0.120;
      filter: brightness(1.30) saturate(1.20);
    }

    /* Bloom — blurred highlight riding the beam head */
    #hz-run .beam-bloom {
      position: absolute;
      inset: 0;
      border-radius: 5px;
      clip-path: inset(0 round 6px);
      background: conic-gradient(from var(--beam-angle, 0deg), transparent 0%, transparent 58%, rgba(255, 255, 255, 0.03) 62%, rgba(255, 255, 255, 0.08) 65%, rgba(255, 255, 255, 0.2) 67%, rgba(255, 255, 255, 0.45) 69%, rgba(255, 255, 255, 0.85) 70%, rgba(255, 255, 255, 0.85) 70.5%, rgba(255, 255, 255, 0.45) 71.5%, rgba(255, 255, 255, 0.2) 73%, rgba(255, 255, 255, 0.08) 75%, rgba(255, 255, 255, 0.03) 78%, transparent 82%);
      -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
      -webkit-mask-composite: xor;
      mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
      mask-composite: exclude;
      padding: 1px;
      filter: blur(8px) brightness(1.30) saturate(1.20);
      pointer-events: none;
      z-index: 3;
      opacity: 0.190;
    }

    /* ── Running: sunset variation ── */
    #hz-run.hz-running::after {
      background:
        conic-gradient(from var(--beam-angle, 0deg), transparent 0%, transparent 54%, rgba(255, 255, 255, 0.1) 57%, rgba(255, 255, 255, 0.3) 60%, rgba(255, 255, 255, 0.6) 63%, rgba(255, 255, 255, 0.75) 66%, rgba(255, 255, 255, 0.6) 69%, rgba(255, 255, 255, 0.3) 72%, rgba(255, 255, 255, 0.1) 75%, transparent 78%, transparent 100%),
        radial-gradient(ellipse 9px 18px at 2% 68%, rgb(255, 180, 50), transparent),
        radial-gradient(ellipse 4px 8px at 2% 68%, rgb(255, 150, 40), transparent),
        radial-gradient(ellipse 59px 9px at 72% -3%, rgb(255, 80, 60), transparent),
        radial-gradient(ellipse 42px 7px at 74% 100%, rgb(255, 100, 80), transparent),
        radial-gradient(ellipse 10px 17px at 100% 27%, rgb(255, 60, 80), transparent),
        radial-gradient(ellipse 10px 18px at 100% 27%, rgb(255, 120, 60), transparent),
        radial-gradient(ellipse 5px 10px at 100% 27%, rgb(255, 200, 50), transparent),
        radial-gradient(ellipse 11px 12px at 100% 27%, rgb(255, 90, 70), transparent);
      opacity: 0.460;
    }

    #hz-run.hz-running::before {
      background:
        radial-gradient(ellipse 9px 18px at 2% 68%, rgba(255, 180, 50, 0.5), transparent),
        radial-gradient(ellipse 4px 8px at 2% 68%, rgba(255, 150, 40, 0.45), transparent),
        radial-gradient(ellipse 59px 9px at 72% -3%, rgba(255, 80, 60, 0.35), transparent),
        radial-gradient(ellipse 42px 7px at 74% 100%, rgba(255, 100, 80, 0.35), transparent),
        radial-gradient(ellipse 10px 17px at 100% 27%, rgba(255, 60, 80, 0.3), transparent),
        radial-gradient(ellipse 10px 18px at 100% 27%, rgba(255, 120, 60, 0.4), transparent),
        radial-gradient(ellipse 5px 10px at 100% 27%, rgba(255, 200, 50, 0.3), transparent),
        radial-gradient(ellipse 11px 12px at 100% 27%, rgba(255, 90, 70, 0.3), transparent);
      opacity: 0.240;
    }

    #hz-run.hz-running .beam-bloom {
      opacity: 0.380;
    }

    @media (max-width: 640px) {
      .hz-panes {
        grid-template-columns: 1fr;
      }

      .hz-pane {
        min-height: 300px;
      }
    }
  </style>
</head>

<body>

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
        <a href="/" class="logout-btn" style="text-decoration:none;">
          <span class="btn-icon" style="--icon:url(/assets/icons/home.svg)"></span>Home</a>
        <form method="POST" style="margin:0;">
          <button class="logout-btn" name="logout" value="1">
            <span class="btn-icon" style="--icon:url(/assets/icons/lock.svg)"></span>Lock</button>
        </form>
      </div>
    </div>

    <div class="admin-tabs">
      <button class="admin-tab active" onclick="switchTab('files')">Share files</button>
      <button class="admin-tab" onclick="switchTab('snippets')">Text snippets</button>
      <button class="admin-tab" onclick="switchTab('humanizer')">Humanizer</button>
    </div>

    <!-- ── FILE SHARE ── -->
    <div class="admin-section active" id="tab-files">
      <div class="upload-zone" id="file-zone">
        <input type="file" id="file-input" multiple>
        <div class="upload-zone-text">
          <strong>Click or drag</strong> to upload a file or image
        </div>
      </div>
      <div class="expire-row">
        <span class="expire-label">Expires after</span>
        <button class="expire-btn" data-hours="1" onclick="setExpiry('file',1,this)">1h</button>
        <button class="expire-btn" data-hours="24" onclick="setExpiry('file',24,this)">24h</button>
        <button class="expire-btn" data-hours="72" onclick="setExpiry('file',72,this)">3 days</button>
        <button class="expire-btn active" data-hours="168" onclick="setExpiry('file',168,this)">1 week</button>
      </div>
      <div class="upload-queue" id="file-queue"></div>
      <div class="item-list" id="file-list"></div>
    </div>

    <!-- ── SNIPPETS ── -->
    <div class="admin-section" id="tab-snippets">
      <div class="snip-form" id="snip-form">
        <textarea class="snip-textarea" id="snip-text" placeholder="Paste anything — a URL, code, note…"></textarea>
        <div class="snip-form-row">
          <span class="snip-status" id="snip-status"></span>
          <div style="flex:1"></div>
          <button class="btn btn-primary snip-save-btn" onclick="clearSnipForm()">New snippet</button>
        </div>
      </div>
      <div class="item-list" id="snip-list"></div>
    </div>

    <!-- ── HUMANIZER ── -->
    <div class="admin-section" id="tab-humanizer">
      <?php if (!$hzKeyConfigured): ?>
        <div class="hz-warn">Server not configured &mdash; add your Anthropic API key to
          <code>includes/humanizer-secret.php</code> (or set the <code>ANTHROPIC_API_KEY</code> env var).
        </div>
      <?php endif; ?>
      <div class="hz-panes">
        <div class="hz-pane">
          <div class="hz-pane-head">
            <span class="hz-pane-label">Your text</span>
          </div>
          <textarea id="hz-input" class="hz-textarea" placeholder="Paste your text here&hellip;"
            spellcheck="false"></textarea>
          <div class="hz-output" id="hz-input-diff" style="display:none"></div>
          <div class="hz-foot">
            <span class="hz-count" id="hz-count">0 chars</span>
            <button class="btn" id="hz-run">
              <span class="beam-bloom" aria-hidden="true"></span>
              <span class="hz-run-label">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                  <path d="M12 3l1.9 4.6L18.5 9.5 13.9 11.4 12 16l-1.9-4.6L5.5 9.5l4.6-1.9z" />
                  <path d="M19 15l.8 2 2 .8-2 .8-.8 2-.8-2-2-.8 2-.8z" />
                </svg>
                Humanize
              </span>
            </button>
          </div>
        </div>

        <div class="hz-pane">
          <div class="hz-pane-head">
            <span class="hz-pane-label">Humanized</span>
            <div class="hz-head-actions">
              <button class="hz-copy" id="hz-compare" disabled title="Highlight what changed">
                <svg viewBox="0 0 24 24">
                  <rect x="3" y="4" width="18" height="16" rx="2" />
                  <line x1="12" y1="4" x2="12" y2="20" />
                </svg>
                Compare
              </button>
              <button class="hz-copy" id="hz-copy" disabled>
                <svg viewBox="0 0 24 24">
                  <rect x="9" y="9" width="13" height="13" rx="2" />
                  <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" />
                </svg>
                Copy
              </button>
            </div>
          </div>
          <div class="hz-output" id="hz-output"><span class="hz-placeholder">Your humanized text will appear
              here.</span></div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-drop-overlay" id="page-drop-overlay">
    <div class="page-drop-box">
      <svg viewBox="0 0 24 24">
        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
        <polyline points="7 9 12 4 17 9" />
        <line x1="12" y1="4" x2="12" y2="16" />
      </svg>
      <div class="page-drop-title">Drop to upload</div>
      <div class="page-drop-sub">Drop files anywhere on the page</div>
    </div>
  </div>

  <!-- ── Image lightbox ── -->
  <div class="img-lightbox" id="img-lightbox" aria-hidden="true">
    <div class="lightbox-backdrop" onclick="closeLightbox()"></div>
    <div class="lightbox-content">
      <div class="lightbox-topbar">
        <span class="lightbox-name" id="lightbox-name"></span>
        <div class="lightbox-actions">
          <a class="lightbox-btn" id="lightbox-dl" download>
            <svg viewBox="0 0 24 24">
              <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
              <polyline points="7 10 12 15 17 10" />
              <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            Download
          </a>
          <button class="lightbox-btn lightbox-icon-btn" id="lightbox-copy" title="Copy link">
            <svg viewBox="0 0 24 24">
              <rect x="9" y="9" width="13" height="13" rx="2" />
              <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" />
            </svg>
          </button>
          <button class="lightbox-btn lightbox-close" onclick="closeLightbox()" aria-label="Close">&times;</button>
        </div>
      </div>
      <div class="lightbox-stage">
        <button class="lightbox-nav lightbox-prev" onclick="lightboxGo(-1)" aria-label="Previous">&lsaquo;</button>
        <img class="lightbox-img" id="lightbox-img" src="" alt="">
        <button class="lightbox-nav lightbox-next" onclick="lightboxGo(1)" aria-label="Next">&rsaquo;</button>
      </div>
      <div class="lightbox-strip" id="lightbox-strip"></div>
    </div>
  </div>

  <div class="toast" id="toast"></div>
  <script src="/assets/color-math.js?v=<?= APP_VERSION ?>"></script>
  <script>
    const BASE = window.location.origin;
    const MAX_UPLOAD_BYTES = <?= MAX_UPLOAD_MB * 1024 * 1024 ?>;
    const MAX_UPLOAD_LABEL = '<?= MAX_UPLOAD_MB ?> MB';
    let fileExpiry = 168;
    let editingSnipId = null;

    // ── Expiry ───────────────────────────────────────────────────────
    function setExpiry(type, hours, btn) {
      fileExpiry = hours;
      btn.closest('.expire-row').querySelectorAll('.expire-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    }

    // ── Tab switching ────────────────────────────────────────────────
    function switchTab(tab) {
      document.querySelectorAll('.admin-tab').forEach((b, i) => {
        b.classList.toggle('active', ['files', 'snippets', 'humanizer'][i] === tab);
      });
      document.querySelectorAll('.admin-section').forEach((s, i) => {
        s.classList.toggle('active', ['tab-files', 'tab-snippets', 'tab-humanizer'][i] === 'tab-' + tab);
      });
    }

    // ── Upload ───────────────────────────────────────────────────────
    // Build a queue row for one file (starts pending at 0%).
    function makeUploadRow(type, name) {
      const container = document.getElementById('file-queue');
      const row = document.createElement('div');
      row.className = 'upload-row pending';
      row.innerHTML = `
        <div class="upload-row-head">
          <span class="upload-row-name"></span>
          <span class="upload-row-pct">0%</span>
        </div>
        <div class="upload-row-track"><div class="upload-row-fill"></div></div>`;
      row.querySelector('.upload-row-name').textContent = name;
      container.appendChild(row);
      return row;
    }

    function uploadOne(file, type, row) {
      return new Promise(resolve => {
        const pctEl = row.querySelector('.upload-row-pct');
        const fillEl = row.querySelector('.upload-row-fill');

        if (file.size > MAX_UPLOAD_BYTES) {
          showToast(file.name + ' exceeds the ' + MAX_UPLOAD_LABEL + ' limit');
          row.remove();
          resolve();
          return;
        }

        row.classList.remove('pending');

        const fd = new FormData();
        fd.append('upload', file);
        fd.append('upload_type', type);
        fd.append('expire_hours', fileExpiry);

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
            row.remove();
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
          }, 250);
        });

        xhr.addEventListener('error', () => {
          row.remove();
          showToast('Upload failed — server dropped the connection');
          resolve();
        });

        xhr.addEventListener('abort', () => {
          row.remove();
          showToast('Upload cancelled');
          resolve();
        });

        xhr.send(fd);
      });
    }

    // Queue a batch: show every file up front (pending), then upload one at a time.
    async function uploadBatch(files, type) {
      const list = [...files];
      if (!list.length) return;
      const rows = list.map(f => makeUploadRow(type, f.name));
      for (let i = 0; i < list.length; i++) {
        await uploadOne(list[i], type, rows[i]);
      }
    }

    function bindUploadZone(zoneId, inputId, type) {
      const zone = document.getElementById(zoneId);
      const input = document.getElementById(inputId);
      if (!zone || !input) return;

      input.addEventListener('change', () => {
        const files = [...input.files];
        input.value = '';
        uploadBatch(files, type);
      });
      // Dropping is handled page-wide (see initPageDrop) so files can be dropped anywhere.
    }

    // ── Page-wide drag & drop (everything uploads to Share files) ────
    function runDroppedUploads(files, type) {
      return uploadBatch(files, type);
    }

    function initPageDrop() {
      const overlay = document.getElementById('page-drop-overlay');
      if (!overlay) return;
      let depth = 0;
      const hasFiles = e => e.dataTransfer && Array.from(e.dataTransfer.types || []).includes('Files');

      window.addEventListener('dragenter', e => {
        if (!hasFiles(e)) return;
        e.preventDefault();
        depth++;
        overlay.classList.add('show');
      });
      window.addEventListener('dragover', e => {
        if (!hasFiles(e)) return;
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
      });
      window.addEventListener('dragleave', e => {
        if (!hasFiles(e)) return;
        depth = Math.max(0, depth - 1);
        if (depth === 0) overlay.classList.remove('show');
      });
      window.addEventListener('drop', e => {
        if (!hasFiles(e)) return;
        e.preventDefault();
        depth = 0;
        overlay.classList.remove('show');
        const files = [...e.dataTransfer.files];
        if (!files.length) return;
        // Everything goes to Share files; make sure that tab is visible.
        if (!document.getElementById('tab-files').classList.contains('active')) switchTab('files');
        runDroppedUploads(files, 'file');
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
      const text = document.getElementById('snip-text').value;
      if (!text.trim()) return;
      _snipSavePromise = _snipSavePromise.then(() => doSnipSave(id, text));
    }

    function flushPendingSnipSave() {
      if (_snipSaveTimer) {
        clearTimeout(_snipSaveTimer);
        triggerSnipAutoSave();
      }
    }

    async function doSnipSave(id, text) {
      setSnipStatus('Saving…', 'saving');
      const fd = new FormData();
      fd.append('action', 'save_snip');
      fd.append('id', id || '');
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

    function editSnip(id, text) {
      flushPendingSnipSave();
      editingSnipId = id;
      document.getElementById('snip-text').value = text;
      document.getElementById('snip-text').focus();
      setSnipStatus('Saved', 'saved');
    }

    function clearSnipForm() {
      flushPendingSnipSave();
      editingSnipId = null;
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

    function escapeHtml(text) {
      return String(text ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    function formatSnipDate(ts) {
      if (!ts) return '';
      const d = new Date(ts * 1000);
      if (Number.isNaN(d.getTime())) return '';
      return d.toLocaleDateString(undefined, { year: '2-digit', month: 'numeric', day: 'numeric' });
    }

    function isImageFile(f) { return f.img || (f.mime || '').indexOf('image/') === 0; }

    function renderFiles(files) {
      const el = document.getElementById('file-list');
      el.className = 'item-list';
      const openTok = lightboxOpen() && _gallery[_lightboxIdx] ? _gallery[_lightboxIdx].token : null;
      if (!files.length) {
        el.innerHTML = '<div class="empty-state">No active files</div>';
        _gallery = [];
        if (openTok) closeLightbox();
        return;
      }
      el.innerHTML = '';
      const ordered = files.slice().reverse();
      _gallery = ordered.filter(isImageFile);   // images only, in display order → lightbox set
      let imgIdx = 0;
      ordered.forEach(f => {
        const isImg = isImageFile(f);
        const dlUrl = BASE + '/admin/?token=' + f.token;
        const shareUrl = isImg ? (BASE + '/admin/?img=' + f.token) : dlUrl;
        const thumbUrl = BASE + '/admin/?thumb=' + f.token;
        const row = document.createElement('div');
        row.className = 'item-row';
        row.innerHTML = `
      <div class="item-row-icon${isImg ? ' item-row-thumb' : ''}">
        ${isImg
            ? `<img src="${thumbUrl}" alt="${f.name}" loading="lazy">`
            : `<svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>`}
      </div>
      <div class="item-meta">
        <div class="item-name" title="${f.name}">${f.name}</div>
        <div class="item-sub"><span>${fmtSize(f.size)}</span><span>${timeLeft(f.expires)}</span></div>
      </div>
      <div class="item-actions">
        <a class="icon-btn" title="Download" href="${dlUrl}" download="${f.name}">
          <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        </a>
        <button class="icon-btn" title="${isImg ? 'Copy image link' : 'Copy link'}" onclick="copyAndToast('${shareUrl}')">
          <svg viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
        </button>
        <button class="icon-btn danger" title="Delete" onclick="delFile('${f.token}')">
          <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
        </button>
      </div>`;
        if (isImg) {
          const myIdx = imgIdx++;
          const thumb = row.querySelector('.item-row-thumb');
          thumb.title = 'Click to preview';
          thumb.addEventListener('click', () => openLightbox(myIdx));
        }
        el.appendChild(row);
      });
      // Keep an open lightbox pointed at the same image (or nearest) after a re-render.
      if (openTok) {
        const ni = _gallery.findIndex(f => f.token === openTok);
        if (ni === -1) { if (_gallery.length) lightboxSet(Math.min(_lightboxIdx, _gallery.length - 1)); else closeLightbox(); }
        else { _lightboxIdx = ni; buildLightboxStrip(); showLightboxImage(); updateStripActive(); }
      }
    }

    // ── Image lightbox ───────────────────────────────────────────────
    let _gallery = [];
    let _lightboxIdx = -1;
    const lightboxOpen = () => document.getElementById('img-lightbox').classList.contains('open');

    function openLightbox(index) {
      if (!_gallery.length) return;
      _lightboxIdx = Math.max(0, Math.min(index, _gallery.length - 1));
      buildLightboxStrip();
      showLightboxImage();
      updateStripActive();
      const lb = document.getElementById('img-lightbox');
      lb.classList.add('open');
      lb.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
      const lb = document.getElementById('img-lightbox');
      lb.classList.remove('open');
      lb.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
    }

    function lightboxGo(delta) {
      if (_gallery.length < 2) return;
      _lightboxIdx = (_lightboxIdx + delta + _gallery.length) % _gallery.length;
      showLightboxImage();
      updateStripActive();
    }

    function lightboxSet(index) {
      if (!_gallery.length) { closeLightbox(); return; }
      _lightboxIdx = Math.max(0, Math.min(index, _gallery.length - 1));
      showLightboxImage();
      updateStripActive();
    }

    function showLightboxImage() {
      const f = _gallery[_lightboxIdx];
      if (!f) return;
      const imgUrl = BASE + '/admin/?img=' + f.token;
      const dlUrl = BASE + '/admin/?token=' + f.token;
      const img = document.getElementById('lightbox-img');
      img.src = imgUrl;
      img.alt = f.name;
      document.getElementById('lightbox-name').textContent = f.name;
      const dl = document.getElementById('lightbox-dl');
      dl.href = dlUrl;
      dl.setAttribute('download', f.name);
      document.getElementById('lightbox-copy').onclick = () => copyAndToast(imgUrl);
      const single = _gallery.length < 2;
      document.querySelectorAll('.lightbox-nav').forEach(n => n.style.display = single ? 'none' : '');
    }

    function buildLightboxStrip() {
      const strip = document.getElementById('lightbox-strip');
      strip.style.display = _gallery.length < 2 ? 'none' : 'flex';
      strip.innerHTML = '';
      _gallery.forEach((f, i) => {
        const t = document.createElement('img');
        t.className = 'lightbox-thumb' + (i === _lightboxIdx ? ' active' : '');
        t.src = BASE + '/admin/?thumb=' + f.token;
        t.alt = f.name;
        t.loading = 'lazy';
        t.addEventListener('click', () => lightboxSet(i));
        strip.appendChild(t);
      });
    }

    function updateStripActive() {
      const thumbs = document.querySelectorAll('#lightbox-strip .lightbox-thumb');
      thumbs.forEach((t, i) => t.classList.toggle('active', i === _lightboxIdx));
      const active = thumbs[_lightboxIdx];
      if (active) active.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' });
    }

    document.addEventListener('keydown', e => {
      if (!lightboxOpen()) return;
      if (e.key === 'Escape') closeLightbox();
      else if (e.key === 'ArrowLeft') lightboxGo(-1);
      else if (e.key === 'ArrowRight') lightboxGo(1);
    });

    function renderSnips(snips) {
      const el = document.getElementById('snip-list');
      if (!snips.length) { el.innerHTML = '<div class="empty-state">No snippets yet</div>'; return; }
      el.innerHTML = '';
      snips.slice().reverse().forEach(s => {
        const row = document.createElement('div');
        row.className = 'item-row snip-row';
        const text = String(s.text || '');
        const [firstLine, ...rest] = text.split(/\r?\n/);
        const title = firstLine.trim();
        const preview = rest.join('\n').trim();
        const dateText = formatSnipDate(s.created || s.updated);
        row.innerHTML = `
      <div class="item-meta">
        <div class="item-name">${escapeHtml(title) || 'Untitled'}</div>
        <div class="snip-line">
          ${dateText ? `<span class="snip-date">${dateText}</span>` : ''}
          <span class="snip-preview${preview ? '' : ' snip-preview-empty'}">${preview ? escapeHtml(preview) : 'No additional text'}</span>
        </div>
      </div>
      <div class="item-actions">
        <button class="icon-btn js-snip-copy" title="Copy text">
          <svg viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
        </button>
        <button class="icon-btn js-snip-edit" title="Edit">
          <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        </button>
        <button class="icon-btn danger js-snip-delete" title="Delete">
          <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
        </button>
      </div>`;
        row.querySelector('.js-snip-copy').addEventListener('click', () => copyAndToast(s.text));
        row.querySelector('.js-snip-edit').addEventListener('click', () => editSnip(s.id, s.text));
        row.querySelector('.js-snip-delete').addEventListener('click', () => delSnip(s.id));
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
          _dataPollInterval = setInterval(() => loadData().catch(() => { }), 30000);
          loadData();
        } else {
          document.getElementById('reauth-error').classList.add('show');
        }
      }

      // ── Init ─────────────────────────────────────────────────────────
      bindUploadZone('file-zone', 'file-input', 'file');
      initPageDrop();
      document.getElementById('snip-text').addEventListener('input', scheduleSnipAutoSave);
      loadData();
      _sessionInterval = setInterval(pingSession, 60000);
      _dataPollInterval = setInterval(() => loadData().catch(() => { }), 30000);

      // ── Humanizer tab ────────────────────────────────────────────────
      (function () {
        const input = document.getElementById('hz-input');
        const inputDiff = document.getElementById('hz-input-diff');
        const output = document.getElementById('hz-output');
        const runBtn = document.getElementById('hz-run');
        const copyBtn = document.getElementById('hz-copy');
        const compareBtn = document.getElementById('hz-compare');
        const countEl = document.getElementById('hz-count');
        if (!input) return;
        let busy = false;
        let lastRun = null;   // { input, output } from the most recent humanize
        let comparing = false;

        function updateCount() {
          const n = input.value.length;
          countEl.textContent = n.toLocaleString() + (n === 1 ? ' char' : ' chars');
        }
        input.addEventListener('input', updateCount);
        updateCount();

        // Border-glow beam: a tiny rAF loop rotates --beam-angle on the
        // button (CSS-composited layers can't self-rotate without @property).
        // The mono→sunset swap is handled by the .hz-running class in CSS.
        if (!matchMedia('(prefers-reduced-motion: reduce)').matches) {
          const beamDur = 4200;
          const spin = (t) => {
            runBtn.style.setProperty('--beam-angle', ((t / beamDur) % 1) * 360 + 'deg');
            requestAnimationFrame(spin);
          };
          requestAnimationFrame(spin);
        }

        async function humanize() {
          if (busy) return;
          const text = input.value;
          if (!text.trim()) { showToast('Enter some text first'); return; }
          busy = true;
          runBtn.disabled = true;
          runBtn.classList.add('hz-running');   // sunset border glow while running
          const label = runBtn.querySelector('.hz-run-label');
          const orig = label.innerHTML;
          label.innerHTML = 'Humanizing&hellip;';
          copyBtn.disabled = true;
          compareBtn.disabled = true;
          if (comparing) exitCompare();
          output.classList.remove('hz-output-error');
          output.innerHTML = '<span class="hz-placeholder">Working&hellip;</span>';
          try {
            const fd = new FormData();
            fd.append('action', 'humanize');
            fd.append('text', text);
            const res = await fetch('/admin/', { method: 'POST', body: fd });
            const raw = await res.text();
            let json;
            try { json = JSON.parse(raw); }
            catch (_) { throw new Error(raw.slice(0, 400).trim() || 'Empty response from server.'); }
            if (json.ok) {
              lastRun = { input: text, output: json.text };
              output.textContent = json.text;
              copyBtn.disabled = false;
              compareBtn.disabled = false;
            } else {
              output.textContent = json.error || 'Something went wrong.';
              output.classList.add('hz-output-error');
            }
          } catch (e) {
            output.textContent = (e && e.message) ? e.message : 'Request failed. Please try again.';
            output.classList.add('hz-output-error');
          } finally {
            busy = false;
            runBtn.disabled = false;
            runBtn.classList.remove('hz-running');   // back to mono
            label.innerHTML = orig;
          }
        }

        runBtn.addEventListener('click', humanize);
        input.addEventListener('keydown', e => {
          if ((e.metaKey || e.ctrlKey) && e.key === 'Enter') { e.preventDefault(); humanize(); }
        });
        copyBtn.addEventListener('click', () => {
          const txt = (lastRun && lastRun.output) || output.textContent;
          navigator.clipboard.writeText(txt).then(() => showToast('Copied!'));
        });

        // ── Compare (word-level diff, Myers) ──
        function escHtml(s) {
          return s.replace(/[&<>]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c]));
        }
        function tokenizeWords(s) { return s.match(/\S+\s*|\s+/g) || []; }
        function diffTokens(a, b) {
          const N = a.length, M = b.length, MAX = N + M, OFF = MAX;
          const v = new Int32Array(2 * MAX + 1);
          const trace = [];
          let D = 0, done = false;
          for (; D <= MAX && !done; D++) {
            trace.push(v.slice());
            for (let k = -D; k <= D; k += 2) {
              let x;
              if (k === -D || (k !== D && v[k - 1 + OFF] < v[k + 1 + OFF])) x = v[k + 1 + OFF];
              else x = v[k - 1 + OFF] + 1;
              let y = x - k;
              while (x < N && y < M && a[x] === b[y]) { x++; y++; }
              v[k + OFF] = x;
              if (x >= N && y >= M) { done = true; break; }
            }
          }
          D--;
          const ops = [];
          let x = N, y = M;
          for (let d = D; d > 0; d--) {
            const vv = trace[d];
            const k = x - y;
            let prevK;
            if (k === -d || (k !== d && vv[k - 1 + OFF] < vv[k + 1 + OFF])) prevK = k + 1;
            else prevK = k - 1;
            const prevX = vv[prevK + OFF], prevY = prevX - prevK;
            while (x > prevX && y > prevY) { ops.push(['eq', a[x - 1]]); x--; y--; }
            if (x === prevX) { ops.push(['ins', b[y - 1]]); y--; }
            else { ops.push(['del', a[x - 1]]); x--; }
          }
          while (x > 0 && y > 0) { ops.push(['eq', a[x - 1]]); x--; y--; }
          while (x > 0) { ops.push(['del', a[--x]]); }
          while (y > 0) { ops.push(['ins', b[--y]]); }
          ops.reverse();
          return ops;
        }
        function buildDiffHTML(ops, side) {
          let html = '', run = '', runType = null;
          const flush = () => {
            if (!run) return;
            if (runType === 'eq') html += escHtml(run);
            else if (runType === 'del') html += '<del class="hz-del">' + escHtml(run) + '</del>';
            else if (runType === 'ins') html += '<ins class="hz-ins">' + escHtml(run) + '</ins>';
            run = '';
          };
          for (const [t, val] of ops) {
            if ((side === 'del' && t === 'ins') || (side === 'ins' && t === 'del')) continue;
            if (t !== runType) { flush(); runType = t; }
            run += val;
          }
          flush();
          return html || '<span class="hz-placeholder">(no text)</span>';
        }
        function enterCompare() {
          if (!lastRun) return;
          const a = tokenizeWords(lastRun.input);
          const b = tokenizeWords(lastRun.output);
          if (a.length + b.length > 16000) { showToast('Text is too long to compare'); return; }
          const ops = diffTokens(a, b);
          inputDiff.innerHTML = buildDiffHTML(ops, 'del');
          output.innerHTML = buildDiffHTML(ops, 'ins');
          input.style.display = 'none';
          inputDiff.style.display = '';
          comparing = true;
          compareBtn.classList.add('is-active');
        }
        function exitCompare() {
          inputDiff.style.display = 'none';
          input.style.display = '';
          if (lastRun) output.textContent = lastRun.output;
          comparing = false;
          compareBtn.classList.remove('is-active');
        }
        compareBtn.addEventListener('click', () => {
          if (comparing) exitCompare(); else enterCompare();
        });
      })();
    <?php endif; ?>
  </script>

</body>

</html>