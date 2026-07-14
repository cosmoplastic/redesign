<?php
$pageTitle = 'Free Design Tools for Color, Type & CSS — ONE design';
$pageDescription = 'Use fast, free tools for palettes, gradients, typography, shadows, and UI snippets. Build, preview, and copy production-ready CSS in seconds.';
$activePage = 'index';
require 'includes/header.php';
?>

<main class="scrollable home-scrollable">

    <?php /*
    <div class="topbar">
        <div class="topbar-greeting">
            <h1>Tools for designers who <br><em>care about the details</em></h1>
            <p>A growing collection of tools for designers — made by <a
                    href="https://rydesignstudios.com/?utm_source=onedesign" target="_blank" rel="noopener"
                    style="color: inherit;">a designer</a>.</p>
        </div>
    </div>
    */ ?>

    <?php require 'includes/convergence-hero.php'; ?>

    <?php require 'includes/tools-section.php'; ?>
</main>
</div>

<style>
    .home-scrollable {
        max-width: 1700px;
        width: 100%;
        margin: 0 auto;
    }
</style>
<?php require 'includes/footer.php'; ?>