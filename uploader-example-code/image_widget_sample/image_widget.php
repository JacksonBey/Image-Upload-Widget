<?php
function render_image_widget($config) {
    // Extract configuration values
    $max_width = $config['max_width'] ?? 800;
    $max_height = $config['max_height'] ?? 600;
    $min_width = $config['min_width'] ?? 100;
    $min_height = $config['min_height'] ?? 100;
    $max_photos = $config['max_photos'] ?? 10;
    $file_path = $config['file_path'] ?? 'downloads/';
    // HTML, CSS, and JS content can be included here
    // ...

    include 'index.html';
    include 'cropper.css';
    include 'image_widget.js';
}
?>
