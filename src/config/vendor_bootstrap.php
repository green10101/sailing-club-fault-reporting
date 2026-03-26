<?php

/**
 * Ensure Composer autoload is available.
 *
 * Shared hosting deployments sometimes fail to populate vendor/ via pull.
 * If vendor/autoload.php is missing but vendor.zip exists, extract it once.
 */
function loadVendorAutoload(): void
{
    $projectRoot = dirname(__DIR__, 2);
    $autoloadPath = $projectRoot . '/vendor/autoload.php';

    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
        return;
    }

    $zipPath = $projectRoot . '/vendor.zip';
    if (file_exists($zipPath) && class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) === true) {
            if (!is_dir($projectRoot . '/vendor')) {
                mkdir($projectRoot . '/vendor', 0755, true);
            }

            $zip->extractTo($projectRoot);
            $zip->close();
        }
    }

    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
        return;
    }

    throw new RuntimeException(
        'Composer autoload not found. Expected ' . $autoloadPath .
        '. Ensure vendor.zip is present and extractable, or upload vendor/.'
    );
}
