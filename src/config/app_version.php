<?php

const APP_RELEASE_MARKER = '2026-04-20 v3.0';

function getAppVersionLabel()
{
    $version = getenv('APP_VERSION');
    if (is_string($version) && trim($version) !== '') {
        return trim($version);
    }

    $projectRoot = dirname(__DIR__, 2);
    $headPath = $projectRoot . '/.git/HEAD';
    if (is_file($headPath) && is_readable($headPath)) {
        $head = trim((string) file_get_contents($headPath));
        if (strpos($head, 'ref: ') === 0) {
            $refPath = $projectRoot . '/.git/' . substr($head, 5);
            if (is_file($refPath) && is_readable($refPath)) {
                $commit = trim((string) file_get_contents($refPath));
                if ($commit !== '') {
                    return APP_RELEASE_MARKER . ' (' . substr($commit, 0, 7) . ')';
                }
            }
        } elseif ($head !== '') {
            return APP_RELEASE_MARKER . ' (' . substr($head, 0, 7) . ')';
        }
    }

    return APP_RELEASE_MARKER;
}