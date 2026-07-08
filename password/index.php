<?php
session_start();
require_once __DIR__ . '/../includes/version.php';
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
  <link rel="stylesheet" href="<?= asset_versioned_path('/assets/style.css') ?>">
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
      background: #111113;
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
      margin-top: 14px;
    }

    .lock-home:hover {
      color: var(--color-primary-200);
    }

    /* Border beam — card,
       ported from github.com/Jakubantalik/border-beam (MIT) */

    /* ===== Card  ·  .beam-card--md ===== */
    .beam-card--md {
      position: relative;
      border-radius: 16px;
      background: #0e0e10;
      overflow: hidden;
    }

    /* Stroke — stationary color field revealed by a rotating conic window */
    .beam-card--md::after {
      content: "";
      position: absolute;
      inset: 0;
      border-radius: 15px;
      padding: 1px;
      clip-path: inset(0 round 16px);
      background:
        conic-gradient(from var(--beam-angle, 0deg),
          transparent 0%, transparent 54%,
          rgba(255, 255, 255, 0.1) 57%, rgba(255, 255, 255, 0.3) 60%, rgba(255, 255, 255, 0.6) 63%, rgba(255, 255, 255, 0.75) 66%,
          rgba(255, 255, 255, 0.6) 69%, rgba(255, 255, 255, 0.3) 72%, rgba(255, 255, 255, 0.1) 75%,
          transparent 78%, transparent 100%),
        radial-gradient(ellipse 70px 40px at 33% -7.4%, rgb(180, 180, 180), transparent),
        radial-gradient(ellipse 60px 35px at 12% -5%, rgb(140, 140, 140), transparent),
        radial-gradient(ellipse 40px 70px at 2.1% 68.3%, rgb(160, 160, 160), transparent),
        radial-gradient(ellipse 20px 35px at 2.1% 68.3%, rgb(130, 130, 130), transparent),
        radial-gradient(ellipse 180px 32px at 74.4% 100%, rgb(170, 170, 170), transparent),
        radial-gradient(ellipse 85px 26px at 55% 100%, rgb(150, 150, 150), transparent),
        radial-gradient(ellipse 74px 32px at 93.9% 0%, rgb(190, 190, 190), transparent),
        radial-gradient(ellipse 26px 42px at 100% 27.1%, rgb(145, 145, 145), transparent),
        radial-gradient(ellipse 52px 48px at 100% 27.1%, rgb(165, 165, 165), transparent);
      -webkit-mask:
        conic-gradient(from var(--beam-angle, 0deg),
          transparent 0%, transparent 30%,
          rgba(255, 255, 255, 0.1) 36%, rgba(255, 255, 255, 0.35) 44%,
          white 52%, white 80%,
          rgba(255, 255, 255, 0.35) 86%, rgba(255, 255, 255, 0.1) 92%,
          transparent 95%, transparent 100%),
        linear-gradient(#fff 0 0) content-box,
        linear-gradient(#fff 0 0);
      -webkit-mask-composite: source-in, xor;
      mask:
        conic-gradient(from var(--beam-angle, 0deg),
          transparent 0%, transparent 30%,
          rgba(255, 255, 255, 0.1) 36%, rgba(255, 255, 255, 0.35) 44%,
          white 52%, white 80%,
          rgba(255, 255, 255, 0.35) 86%, rgba(255, 255, 255, 0.1) 92%,
          transparent 95%, transparent 100%),
        linear-gradient(#fff 0 0) content-box,
        linear-gradient(#fff 0 0);
      mask-composite: intersect, exclude;
      pointer-events: none;
      z-index: 2;
      opacity: 0.260;
      animation: beam-hue-shift 12s ease-in-out infinite;
    }

    /* Inner glow — soft color bleed inside the element */
    .beam-card--md::before {
      content: "";
      position: absolute;
      inset: 0;
      border-radius: 16px;
      clip-path: inset(0 round 16px);
      background:
        radial-gradient(ellipse 63px 36px at 33% -7.4%, rgba(180, 180, 180, 0.225), transparent),
        radial-gradient(ellipse 54px 32px at 12% -5%, rgba(140, 140, 140, 0.225), transparent),
        radial-gradient(ellipse 36px 63px at 2.1% 68.3%, rgba(160, 160, 160, 0.225), transparent),
        radial-gradient(ellipse 18px 32px at 2.1% 68.3%, rgba(130, 130, 130, 0.225), transparent),
        radial-gradient(ellipse 162px 29px at 74.4% 100%, rgba(170, 170, 170, 0.225), transparent),
        radial-gradient(ellipse 77px 23px at 55% 100%, rgba(150, 150, 150, 0.225), transparent),
        radial-gradient(ellipse 67px 29px at 93.9% 0%, rgba(190, 190, 190, 0.225), transparent),
        radial-gradient(ellipse 23px 38px at 100% 27.1%, rgba(145, 145, 145, 0.225), transparent),
        radial-gradient(ellipse 47px 43px at 100% 27.1%, rgba(165, 165, 165, 0.225), transparent);
      box-shadow: inset 0 0 9px 1px rgba(255, 255, 255, 0.27);
      -webkit-mask-image:
        conic-gradient(from var(--beam-angle, 0deg),
          transparent 0%, transparent 30%,
          rgba(255, 255, 255, 0.1) 36%, rgba(255, 255, 255, 0.35) 44%,
          white 52%, white 80%,
          rgba(255, 255, 255, 0.35) 86%, rgba(255, 255, 255, 0.1) 92%,
          transparent 95%, transparent 100%),
        linear-gradient(white, transparent 28px, transparent calc(100% - 28px), white),
        linear-gradient(to right, white, transparent 28px, transparent calc(100% - 28px), white);
      -webkit-mask-composite: source-in, source-over;
      mask-image:
        conic-gradient(from var(--beam-angle, 0deg),
          transparent 0%, transparent 30%,
          rgba(255, 255, 255, 0.1) 36%, rgba(255, 255, 255, 0.35) 44%,
          white 52%, white 80%,
          rgba(255, 255, 255, 0.35) 86%, rgba(255, 255, 255, 0.1) 92%,
          transparent 95%, transparent 100%),
        linear-gradient(white, transparent 28px, transparent calc(100% - 28px), white),
        linear-gradient(to right, white, transparent 28px, transparent calc(100% - 28px), white);
      mask-composite: intersect, add;
      pointer-events: none;
      z-index: 1;
      opacity: 0.420;
      animation: beam-hue-shift 12s ease-in-out infinite;
    }

    /* Bloom — blurred highlight ring riding the beam head */
    .beam-card--md .beam-bloom {
      position: absolute;
      inset: 0;
      border-radius: 15px;
      clip-path: inset(0 round 16px);
      background: conic-gradient(from var(--beam-angle, 0deg),
          transparent 0%, transparent 58%,
          rgba(255, 255, 255, 0.03) 62%, rgba(255, 255, 255, 0.08) 65%,
          rgba(255, 255, 255, 0.2) 67%, rgba(255, 255, 255, 0.45) 69%,
          rgba(255, 255, 255, 0.85) 70%, rgba(255, 255, 255, 0.85) 70.5%,
          rgba(255, 255, 255, 0.45) 71.5%, rgba(255, 255, 255, 0.2) 73%,
          rgba(255, 255, 255, 0.08) 75%, rgba(255, 255, 255, 0.03) 78%,
          transparent 82%);
      -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
      -webkit-mask-composite: xor;
      mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
      mask-composite: exclude;
      padding: 1px;
      filter: blur(8px) brightness(2.50) saturate(2.50);
      pointer-events: none;
      z-index: 3;
      opacity: 0.240;
    }

    .beam-card--md .beam-content {
      position: relative;
      z-index: 0;
    }

    @keyframes beam-hue-shift {
      0% {
        filter: hue-rotate(-30deg) brightness(2.50) saturate(2.50);
      }

      50% {
        filter: hue-rotate(30deg) brightness(2.50) saturate(2.50);
      }

      100% {
        filter: hue-rotate(-30deg) brightness(2.50) saturate(2.50);
      }
    }
  </style>
</head>

<body>

  <div class="lock-card beam-card--md">
    <div class="beam-bloom"></div>
    <div class="beam-content">
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
  </div>

  <script>
    // Drives --beam-angle with rAF for broad browser support.
    (function () {
      if (matchMedia("(prefers-reduced-motion: reduce)").matches) return;
      var dur = 4220;
      var rot = document.querySelectorAll(".beam-card--md");

      function tick(t) {
        var deg = ((t / dur) % 1) * 360 + "deg";
        for (var i = 0; i < rot.length; i++) rot[i].style.setProperty("--beam-angle", deg);
        requestAnimationFrame(tick);
      }

      requestAnimationFrame(tick);
    })();
  </script>

</body>

</html>