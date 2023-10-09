# JavaScript / jQuery Image Upload Widget

## Table of Contents

1. [Overview](#overview)
2. [Features](#features)
3. [Prerequisites](#prerequisites)
4. [Installation](#installation)
5. [Usage](#usage)
6. [Configuration](#configuration)
7. [Security](#security)
8. [Supported File Types](#supported-file-types)
9. [Contributing](#contributing)
10. [License](#license)
11. [Contact](#contact)

## Overview

This JavaScript / jQuery Image Upload Widget is a versatile, fully-featured image uploader designed for integration into custom PHP websites. It supports a range of functionalities from drag-and-drop uploads to image cropping and rotation. 

## Features

- Drag-and-drop upload
- Multiple image uploads
- Image thumbnails
- In-browser image rotation and cropping
- Customizable through JavaScript variables
- Light and Dark modes
- Responsive design

## Prerequisites

- PHP 7.x or higher
- A web server (e.g., Apache, Nginx)

## Installation

1. Download the `/php` directory from the project repository.
2. Place it in your web server's root directory or a subdirectory.
3. Ensure that the server has write permissions to the `downloads/` folder for image storage.

## Usage

Include the main widget rendering function in your PHP code:

\`\`\`php
include 'path/to/render_image_widget.php';
\`\`\`

Invoke the widget by calling the function `render_image_widget($config)`:

\`\`\`php
$config = [
    // Your configuration parameters here
];
render_image_widget($config);
\`\`\`

php -S localhost:8000

## Configuration

The widget accepts an associative array of configuration parameters:

- `max_width`: Maximum image width (default is 800)
- `max_height`: Maximum image height (default is 600)
- `min_width`: Minimum image width (default is 100)
- `min_height`: Minimum image height (default is 100)
- `max_photos`: Maximum number of photos (default is 10)
- `file_path`: File path to save the images (default is 'downloads/')
- `theme`: image color scheme. 'light' or 'dark' (default is 'light')

## Security

The widget only allows uploading of specified file types. For additional security, consider implementing server-side validation.

## Supported File Types

- jpeg
- jpg
- png
- gif
- heic

## Contributing

Please read `CONTRIBUTING.md` for details on our code of conduct, and the process for submitting pull requests to us.

## License

This project is licensed under the MIT License - see the `LICENSE.md` file for details.

## Contact

For any queries, feel free to contact Jackson Beytebiere.
