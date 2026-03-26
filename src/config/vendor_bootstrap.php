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
        cleanupMalformedVendorFiles($projectRoot);
        extractVendorZipNormalized($zipPath, $projectRoot);
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

function extractVendorZipNormalized(string $zipPath, string $projectRoot): void
{
    $zip = new ZipArchive();
    if ($zip->open($zipPath) !== true) {
        return;
    }

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entryName = $zip->getNameIndex($i);
        if ($entryName === false || $entryName === '') {
            continue;
        }

        // Normalize Windows path separators to avoid creating literal backslash filenames on Linux.
        $normalized = str_replace('\\', '/', $entryName);
        $normalized = ltrim($normalized, '/');

        if ($normalized === '' || strpos($normalized, '../') !== false) {
            continue;
        }

        $targetPath = $projectRoot . '/' . $normalized;

        if (substr($normalized, -1) === '/') {
            if (!is_dir($targetPath)) {
                mkdir($targetPath, 0755, true);
            }
            continue;
        }

        $targetDir = dirname($targetPath);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $contents = $zip->getFromIndex($i);
        if ($contents !== false) {
            file_put_contents($targetPath, $contents);
        }
    }

    $zip->close();
}

function cleanupMalformedVendorFiles(string $projectRoot): void
{
    $entries = scandir($projectRoot);
    if ($entries === false) {
        return;
    }

    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        // Previous Windows-built archives may create literal backslash filenames at project root.
        if (strpos($entry, '\\') === false) {
            continue;
        }

        $path = $projectRoot . '/' . $entry;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
