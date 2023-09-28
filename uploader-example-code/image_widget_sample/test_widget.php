<?php
include 'render_image_widget.php';
$config = [
    'max_width' => 800,
    'max_height' => 600,
    'min_width' => 100,
    'min_height' => 100,
    'max_photos' => 10,
    'file_path' => 'downloads/'
];
render_image_widget($config);
?>
