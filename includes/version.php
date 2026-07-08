<?php
if (!defined('APP_VERSION')) {
    define('APP_VERSION', '1.1.3');
}

if (!function_exists('asset_versioned_path')) {
    function asset_versioned_path(string $webPath): string
    {
        $pathOnly = strtok($webPath, '?');
        $projectRoot = realpath(__DIR__ . '/..');
        $assetPath = $projectRoot . '/' . ltrim((string) $pathOnly, '/');
        $version = @filemtime($assetPath);

        if ($version === false) {
            $version = APP_VERSION;
        }

        $separator = str_contains($webPath, '?') ? '&' : '?';
        return $webPath . $separator . 'v=' . rawurlencode((string) $version);
    }
}
