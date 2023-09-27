# Image-Upload-Widget
Image Upload Widget is used via `render_image_widget($config)`. This function accepts a configuration array `$config` to set various constraints like `max_width`, `max_height`, `min_width`, `min_height`, and `max_photos`. You can include this PHP function in your project and use it to render the widget on any PHP page by calling the function and passing the configuration.

### File Structure Guidance
For a well-structured project, you could arrange your files as follows:

- `assets/`
  - `css/`
    - `cropper.css`
  - `js/`
    - `cropper.min.js`
    - `jquery.min.js`
    - `bootstrap.bundle.min.js`
- `includes/`
  - `render_image_widget.php`
- `index.php`

In your main PHP files, you can include the `render_image_widget.php` file and call the function like so:

```php
<?php
include 'includes/render_image_widget.php';
$config = [
    'max_width' => 800,
    'max_height' => 600,
    // ... other configurations
];
render_image_widget($config);
?>
```
