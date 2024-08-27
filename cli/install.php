#!/usr/bin/env php
<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

$target = '/path/to/target';
$link = '/path/to/link';

if (is_link($link)) {
    $exist_target = readlink($link);
    if ($exist_target === false) {
        throw new RuntimeException('Cannot read ' . $link);
    }

    if ($exist_target == $target) {
        // Nothing to do
        return;
    }

    if (is_dir($exist_target)) {
        if (!rmdir($link)) {
            throw new RuntimeException('Cannot remove ' . $link);
        }
    } else {
        if (!unlink($link)) {
            throw new RuntimeException('Cannot remove ' . $link);
        }
    }
} elseif (file_exists($link)) {
    throw new RuntimeException('Already exist ' . $link);
}

if (!symlink($target, $link)) {
    throw new RuntimeException('Cannot create ' . $link);
}
