<?php

$file = '/../vendor/autoload.php';

while (!file_exists(__DIR__.$file)) {
    $file = "/..$file";
}

require_once __DIR__.$file;
