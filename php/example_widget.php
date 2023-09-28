<?php
include 'render_image_widget.php';
$config = [
    'max_width' => 800,
    'max_height' => 600,
    'min_width' => 100,
    'min_height' => 100,
    'max_photos' => 10,
    'file_path' => 'downloads/',
    'theme' => 'dark'
];
$config2 = [
    'max_width' => 2000,
    'max_height' => 2000,
    'min_width' => 100,
    'min_height' => 100,
    'max_photos' => 1,
    'file_path' => 'downloads/',
    'theme' => 'light'
];
render_image_widget($config);
render_image_widget($config2);
?>
