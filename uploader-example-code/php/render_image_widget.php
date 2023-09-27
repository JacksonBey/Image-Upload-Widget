
<?php
function render_image_widget($config) {
    $max_width = isset($config['max_width']) ? $config['max_width'] : 800;
    $max_height = isset($config['max_height']) ? $config['max_height'] : 600;
    $min_width = isset($config['min_width']) ? $config['min_width'] : 100;
    $min_height = isset($config['min_height']) ? $config['min_height'] : 100;
    $max_photos = isset($config['max_photos']) ? $config['max_photos'] : 10;
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Cropper.js</title>
        <link rel="stylesheet" href="cropper.css">
        <style>
            /* Your CSS here */
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Image Cropper</h1>
            <h3 id="images-text" style="display: none;">Images</h3>
            <div>
            <h3 id="saved-thumbnails-text" style="display: none;">Saved</h3>
            <div id="thumbnails"></div>
            <button id="download" style="display: none;">Download</button>
            </div>
            <div class="img-container">
            <input type="file" name="" id="image" required accept="image/*" multiple>
            </div>
            <div class="img-container">
            <canvas id="canvas"></canvas>
            </div>
            <div class="button-container" style="display:none;">
            <button type="button" class="btn btn-secondary" id="cancel">Cancel</button>
            <button type="button" class="btn btn-primary" id="crop">Crop</button>
            <button id="rotateClockwise">Rotate Clockwise</button>
            <button id="rotateCounterClockwise">Rotate Counter Clockwise</button>
            <button id="save">Save without crop</button>
            </div>
            <div>
            <h3 id="unsaved-thumbnails-text" style="display: none;">Unsaved</h3>
            <div id="unsaved-thumbnails"></div>
            </div>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"
            integrity="sha512-9KkIqdfN7ipEW6B6k+Aq20PV31bjODg4AA52W+tYtAE0jE0kMx49bjJ3FgvS56wzmyfMUHbQ4Km2b7l9+Y/+Eg=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://unpkg.com/jquery@3/dist/jquery.min.js" crossorigin="anonymous"></script>
        <script src="https://unpkg.com/bootstrap@4/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script>
            // constraints
            let max_width = <?php echo $max_width; ?>;
            let max_height = <?php echo $max_height; ?>;
            let min_width = <?php echo $min_width; ?>;
            let min_height = <?php echo $min_height; ?>;
            let max_photos = <?php echo $max_photos; ?>;

            function validateImage(imageElement) {
            const width = imageElement.width;
            const height = imageElement.height;
            // Check for max photos
            if (savedImages.length >= max_photos) {
                alert('Maximum number of photos reached.');
                return { isValid: false, error: "Maximum number of photos reached." };
            }

            // Max Width and Height
            if ((max_width > 0 && width > max_width) || (max_height > 0 && height > max_height)) {
                return { isValid: false, error: "Image dimensions exceed maximum limits" };
            }

            // Min Width and Height
            if ((min_width > 0 && width < min_width) || (min_height > 0 && height < min_height)) {
                return { isValid: false, error: "Image dimensions are below minimum limits" };
            }

            return { isValid: true };
            }

            let thumbnailToReplace = null;
            let cropper = null;
            let unsavedThumbnails = [];
            let savedImages = [];
            let savedFullImages = [];
            let currentUnsavedIndex = null;
            let hasSavedImages = false;
            let hasUnsavedThumbnails = false;

            const cropBtn = document.getElementById('crop');
            const cancelBtn = document.getElementById('cancel');
            const canvas = document.getElementById('canvas');
            const buttonContainer = document.querySelector('.button-container');

            cropBtn.addEventListener('click', cropAndSave);
            cancelBtn.addEventListener('click', cancelImage);
            document.getElementById('download').addEventListener('click', function () {
            // Implement your download logic here
            });
            document.getElementById('save').addEventListener('click', function () {
            saveImage(); // Assuming saveImage is refactored accordingly
            });
            function start(imageElement, index) {
            console.log("Start Function: Called with index ", index);
            currentUnsavedIndex = index; // store the index
            const ctx = canvas.getContext('2d');
            canvas.style.display = 'block';
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(imageElement, 0, 0, canvas.width, canvas.height);

            if (cropper) {
                cropper.destroy();
            }
            cropper = new Cropper(canvas, {
                autoCropArea: 1 // Sets crop box to 100% of the image area
            });
            buttonContainer.style.display = 'block';

            function createThumbnail(src) {
                const thumbnail = document.createElement('img');
                thumbnail.src = src;
                thumbnail.width = 100;
                return thumbnail;
            }


            document.getElementById('rotateClockwise').addEventListener('click', function () {
                cropper.rotate(90);
            });

            document.getElementById('rotateCounterClockwise').addEventListener('click', function () {
                cropper.rotate(-90);
            });
            }

            function cancelImage() {
            if (cropper) {
                cropper.destroy();
            }
            buttonContainer.style.display = 'none';
            canvas.style.display = 'none';
            }

            function cropAndSave() {
            console.log("cropAndSave: Called");  // Debug Log

            // Check for cropper initialization
            if (!cropper) {
                console.log("Error: Cropper not initialized.");
                return;
            }
            if (cropper) {
                const croppedCanvas = cropper.getCroppedCanvas({
                width: 160,
                height: 160,
                });
                if (!croppedCanvas) {
                console.log("Error: Cropped canvas is null. Make sure crop box is set.");
                return;
                }
                croppedCanvas.toBlob(function (blob) {
                const thumbnail = document.createElement('img');
                thumbnail.src = croppedCanvas.toDataURL();
                thumbnail.width = 100;

                const index = savedImages.length;

                thumbnail.addEventListener('click', function () {
                    const fullQualityImageSrc = savedFullImages[index];
                    const img = new Image();
                    img.src = fullQualityImageSrc;
                    img.onload = function () {
                    start(img);
                    };
                });
                savedImages.push(blob);
                toggleSaveImageText();
                toggleVisibility();
                savedFullImages.push(croppedCanvas.toDataURL());
                document.getElementById('thumbnails').appendChild(thumbnail);
                }, 'image/jpeg', 1);

                if (currentUnsavedIndex !== null) {
                unsavedThumbnails.splice(currentUnsavedIndex, 1);
                renderUnsavedThumbnails();
                }
                currentUnsavedIndex = null;

                cropper.destroy();
                buttonContainer.style.display = 'none';
                canvas.style.display = 'none';
            }
            }


            function saveImage() {
            if (!cropper) {
                console.error("Error: Cropper not initialized.");
                return;
            }

            const croppedCanvas = cropper.getCroppedCanvas();

            if (!croppedCanvas) {
                console.error("Error: croppedCanvas is null");
                return;
            }

            croppedCanvas.toBlob(function (blob) {
                const thumbnail = document.createElement('img');
                thumbnail.src = croppedCanvas.toDataURL();
                thumbnail.width = 100;

                thumbnail.dataset.index = savedImages.length;

                thumbnail.addEventListener('click', function () {
                const index = parseInt(this.dataset.index);  // Retrieve index from data attribute
                const fullQualityImageSrc = savedFullImages[index];
                const img = new Image();
                img.src = fullQualityImageSrc;
                img.onload = function () {
                    start(img);
                };
                });

                savedImages.push(blob);
                toggleSaveImageText();
                toggleVisibility();
                savedFullImages.push(croppedCanvas.toDataURL());
                document.getElementById('thumbnails').appendChild(thumbnail);

                if (currentUnsavedIndex !== null) {
                unsavedThumbnails.splice(currentUnsavedIndex, 1);
                renderUnsavedThumbnails();
                }
                currentUnsavedIndex = null;
            }, 'image/jpeg', 1);

            cropper.destroy();
            buttonContainer.style.display = 'none';
            canvas.style.display = 'none';
            }


            const canvasContainer = document.querySelector('.img-container');
            canvasContainer.addEventListener('dragover', function (e) {
            e.preventDefault();
            });

            // Updated to handle multiple files
            canvasContainer.addEventListener('drop', function (e) {
            e.preventDefault();
            const files = e.dataTransfer.files;
            const filesArray = Array.from(files);
            if (savedImages.length + filesArray.length > max_photos) {
                alert('Maximum number of photos reached.');
                return;
            }
            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function (e) {
                const img = new Image();
                img.src = e.target.result;
                img.onload = function () {
                    const validation = validateImage(img);
                    if (!validation.isValid) {
                    alert(validation.error);
                    document.getElementById('image').value = '';
                    return;
                    }
                    unsavedThumbnails.push(img);
                    renderUnsavedThumbnails();
                    console.log('Image Loaded: Trying to auto-populate canvas'); // Debug Log
                    start(img, unsavedThumbnails.length - 1);  // Auto-populate canvas
                };
                };
                reader.readAsDataURL(file);
            });
            });

            function renderUnsavedThumbnails() {
            const unsavedArea = document.getElementById('unsaved-thumbnails');
            unsavedArea.innerHTML = '';
            unsavedThumbnails.forEach((img, index) => { // 'img' is an image object
                const thumbnail = new Image();
                thumbnail.src = img.src;
                thumbnail.width = 100;
                thumbnail.style.border = '2px solid red'; // Red border for unsaved
                thumbnail.addEventListener('click', function () {
                const originalImage = unsavedThumbnails[index]; // Access the original full-quality image from unsavedThumbnails
                // console.log('originalImage', originalImage)
                start(originalImage, index); // Pass the actual original image object
                });
                unsavedArea.appendChild(thumbnail);
            });
            toggleVisibility();
            }



            window.addEventListener('DOMContentLoaded', function () {
            const imageInput = document.getElementById('image');
            imageInput.addEventListener('change', function (event) {
                const file = event.target.files[0];
                if (file && file.type.startsWith('image/')) {
                console.log('events', event)
                onImageUpload(event); // Call the function directly
                } else {
                console.log('No image file selected or wrong file type.');
                }
            });
            });

            function onImageUpload(event) {
            const files = event.target.files;
            const filesArray = Array.from(files);
            if (savedImages.length + filesArray.length >= max_photos) {
                alert('Maximum number of photos reached.');
                return;
            }
            Array.from(files).forEach(file => {
                const reader = new FileReader();

                reader.onload = function (e) {
                const img = new Image();
                img.src = e.target.result;
                img.onload = function () {
                    const validation = validateImage(img);
                    if (!validation.isValid) {
                    alert(validation.error);
                    document.getElementById('image').value = '';
                    return;
                    }
                    unsavedThumbnails.push(img);
                    renderUnsavedThumbnails();
                    start(img, unsavedThumbnails.length - 1);  // Auto-populate canvas
                };
                };
            });
            reader.readAsDataURL(file);
            }

            const imageInput = document.getElementById('image');
            imageInput.addEventListener('change', onImageUpload);

            function toggleSaveImageText() {
            const imagesText = document.getElementById('images-text');
            const downloadBtn = document.getElementById('download');
            const thumbnailsArray = document.getElementById('thumbnails').children;
            if(thumbnailsArray.length > 0) {
                imagesText.style.display = 'block';
                downloadBtn.style.display = 'block';
            } else {
                imagesText.style.display = 'none';
                downloadBtn.style.display = 'none';
            }
            }

            function toggleVisibility() {
            const newHasSavedImages = savedImages.length > 0;
            const newHasUnsavedThumbnails = unsavedThumbnails.length > 0;

            if (hasSavedImages !== newHasSavedImages) {
                document.getElementById('images-text').style.display = newHasSavedImages ? 'block' : 'none';
                document.getElementById('saved-thumbnails-text').style.display = newHasSavedImages ? 'block' : 'none';
                hasSavedImages = newHasSavedImages;
            }

            if (hasUnsavedThumbnails !== newHasUnsavedThumbnails) {
                document.getElementById('unsaved-thumbnails-text').style.display = newHasUnsavedThumbnails ? 'block' : 'none';
                hasUnsavedThumbnails = newHasUnsavedThumbnails;
            }
            }
        </script>
    </body>
    </html>
    <?php
}
?>
    