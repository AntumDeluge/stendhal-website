<?php

session_start();
$_SESSION['images_loaded'] = true;

header('Cache-Control: max-age=1, must-revalidate, no-cache, no-store');
header('Content-Type: image/png');
$file = dirname(__FILE__) . '/img.png';
header('Content-Length: ' . filesize($file));
readfile($file);
