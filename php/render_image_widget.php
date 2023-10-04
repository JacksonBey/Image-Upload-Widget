<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function render_image_widget($config)
{
    $max_width = isset($config['max_width']) ? $config['max_width'] : 800;
    $max_height = isset($config['max_height']) ? $config['max_height'] : 600;
    $min_width = isset($config['min_width']) ? $config['min_width'] : 100;
    $min_height = isset($config['min_height']) ? $config['min_height'] : 100;
    $max_photos = isset($config['max_photos']) ? $config['max_photos'] : 10;
    $file_path = $config['file_path'] ?? 'downloads/';
    $theme = isset($config['theme']) ? $config['theme'] : 'light'; // light or dark
    $unique_id =isset($config['id']) ? $config['id'] : uniqid('widget'); // light or dark
?>
 <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Cropper.js</title>
        <link rel="stylesheet" href="cropper.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js" integrity="sha512-9KkIqdfN7ipEW6B6k+Aq20PV31bjODg4AA52W+tYtAE0jE0kMx49bjJ3FgvS56wzmyfMUHbQ4Km2b7l9+Y/+Eg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://unpkg.com/jquery@3/dist/jquery.min.js" crossorigin="anonymous"></script>
    </head>
    <body>
        <!-- <div class="container <?php echo $theme; ?>"> -->
        <div class="container <?php echo $theme; ?>" data-widget-id="<?php echo $unique_id; ?>">
            <!-- <h1>Image Cropper</h1> -->
            <div style='margin: 20px;'>
            <h3 id="images-text" style="display: none;">Images</h3>
                <h3 id="saved-thumbnails-text" style="display: none;">Saved</h3>
                <div id="thumbnails"></div>
                <button id="download" style="display: none;" class="standard-button">Upload</button>
            </div>
            <div class="img-upload-container">
                <label for="image" class="custom-file-upload">
                    <input type="file" name="" id="image" required accept="image/*" multiple>
                    <span id="file-upload-text">Drag Your File(s) Here or Click to Choose</span>
                </label>
            </div>
            <div class="img-edit-container">
                <canvas id="canvas"></canvas>
            </div>
            <div class="button-container" style="display:none;">
                <button type="button" class="standard-button" id="cancel">Cancel</button>
                <button type="button" class="standard-button" id="delete">Delete</button>
                <button type="button" class="standard-button" id="crop">Crop</button>
                <button id="rotateClockwise" class="standard-button">Rotate Clockwise</button>
                <button id="rotateCounterClockwise" class="standard-button">Rotate Counter Clockwise</button>
                <button id="save" class="standard-button">Save without crop</button>

            </div>
            <div>
                <h3 id="unsaved-thumbnails-text" style="display: none;">Unsaved</h3>
                <div id="unsaved-thumbnails"></div>
            </div>
            <div class="crop-tooltip">Drag to move, pinch to zoom, and adjust the box to crop.</div>

        </div>

        <!-- <script src="https://unpkg.com/bootstrap@4/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script> -->
        <script>
            (function() {
            const unique_id = <?php echo json_encode($unique_id); ?>;
            const container = document.querySelector(`[data-widget-id="${unique_id}"]`);

            // constraints
            let max_width = <?php echo $max_width; ?>;
            let max_height = <?php echo $max_height; ?>;
            let min_width = <?php echo $min_width; ?>;
            let min_height = <?php echo $min_height; ?>;
            let max_photos = <?php echo $max_photos; ?>;
            let file_path = '<?php echo $file_path; ?>';

            function validateImage(imageElement) {
                const width = imageElement.width;
                const height = imageElement.height;
                if (savedImages.length >= max_photos) {
                    alert('Maximum number of photos reached.');
                    return {
                        isValid: false,
                        error: "Maximum number of photos reached."
                    };
                }

                if ((max_width > 0 && width > max_width) || (max_height > 0 && height > max_height)) {
                    return {
                        isValid: false,
                        error: "Image dimensions exceed maximum limits"
                    };
                }

                if ((min_width > 0 && width < min_width) || (min_height > 0 && height < min_height)) {
                    return {
                        isValid: false,
                        error: "Image dimensions are below minimum limits"
                    };
                }

                return {
                    isValid: true
                };
            }

            let thumbnailToReplace = null;
            let cropper = null;
            let unsavedThumbnails = [];
            let savedImages = [];
            let savedFullImages = [];
            let currentUnsavedIndex = null;
            let hasSavedImages = false;
            let hasUnsavedThumbnails = false;
            let existingFilesCount = 0;
            let currentSavedIndex = null;

            // DOM Elements
            const cropBtn = container.querySelector('#crop');
            const cancelBtn = container.querySelector('#cancel');
            const canvas = container.querySelector('#canvas');
            const buttonContainer = container.querySelector('.button-container');
            const canvasContainer = container.querySelector('.img-edit-container');
            const imageUploadContainer = container.querySelector('.img-upload-container');
            const saveBtn = container.querySelector('#save');
            const downloadBtn = container.querySelector('#download');
            const deleteBtn = container.querySelector('#delete');
            const imageInput = container.querySelector('#image');
            const tooltip = container.querySelector('.crop-tooltip');

            function initializeEventListeners() {
                cropBtn.addEventListener('click', cropAndSave);
                cancelBtn.addEventListener('click', cancelImage);
                saveBtn.addEventListener('click', function() {
                    saveImage();
                });
                downloadBtn.addEventListener('click', function() {
                    downloadImages();
                });


                imageUploadContainer.addEventListener('dragover', e => e.preventDefault());
                imageUploadContainer.addEventListener('drop', onDrop);

                imageInput.addEventListener('change', onImageUpload);

                deleteBtn.addEventListener('click', deleteImage);

                canvasContainer.addEventListener('mouseenter', function() {
                    tooltip.style.display = 'block';
                });

                canvasContainer.addEventListener('mouseleave', function() {
                    tooltip.style.display = 'none';
                });
            }




            function start(imageElement, index) {
                currentUnsavedIndex = index; // store the index
                // const ctx = canvas.getContext('2d');
                // canvas.style.display = 'block';
                // ctx.clearRect(0, 0, canvas.width, canvas.height);
                // ctx.drawImage(imageElement, 0, 0, canvas.width, canvas.height);

                const imageAspectRatio = imageElement.width / imageElement.height;
                const canvasAspectRatio = canvas.width / canvas.height;
                let drawWidth = canvas.width;
                let drawHeight = canvas.height;

                // Maintain the original aspect ratio of the image.
                if (imageAspectRatio > canvasAspectRatio) {
                    drawHeight = canvas.width / imageAspectRatio;
                } else {
                    drawWidth = canvas.height * imageAspectRatio;
                }

                const ctx = canvas.getContext('2d');
                canvas.style.display = 'block';
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                // Draw the image at the center of the canvas, maintaining its aspect ratio.
                // const dx = (canvas.width - drawWidth) / 2;
                // const dy = (canvas.height - drawHeight) / 2;
                const dx = 0
                const dy = 0
                ctx.drawImage(imageElement, dx, dy, drawWidth, drawHeight);

                if (cropper) {
                    cropper.destroy();
                }
                cropper = new Cropper(canvas, {
                    // aspectRatio: 1,  // Add aspect ratio (change the value as per your requirement)
                    autoCropArea: 1, // Sets crop box to 100% of the image area
                    dragMode: 'move',
                    cropBoxResizable: true,
                    cropBoxMovable: true,
                    ready: function () {
                        cropper.setCropBoxData({
                            // left: dx,
                            // top: dy,
                            width: drawWidth,
                            height: drawHeight
                        });
                    }
                });
                buttonContainer.style.display = 'block';

                container.querySelector('#rotateClockwise').addEventListener('click', function() {
                    cropper.rotate(90);
                });

                container.querySelector('#rotateCounterClockwise').addEventListener('click', function() {
                    cropper.rotate(-90);
                });
            }

            // const cancelBtn = container.querySelector('#cancel');
            function cancelImage() {
                if (cropper) {
                    cropper.destroy();
                }
                buttonContainer.style.display = 'none';
                canvas.style.display = 'none';
                imageInput.value = '';
            }

            function cropAndSave() {
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
                    croppedCanvas.toBlob(function(blob) {
                        const thumbnail = document.createElement('img');
                        thumbnail.src = croppedCanvas.toDataURL();
                        thumbnail.width = 100;

                        // Change 1: Assign index as data attribute
                        const index = savedImages.length;
                        thumbnail.dataset.index = index;

                        thumbnail.addEventListener('click', function() {
                            const fullQualityImageSrc = savedFullImages[index];
                            const img = new Image();
                            currentSavedIndex = index; // Update the currentSavedIndex
                            img.src = fullQualityImageSrc;
                            img.onload = function() {
                                start(img);
                            };
                        });
                        savedImages.push(blob);
                        toggleVisibility();
                        savedFullImages.push(croppedCanvas.toDataURL());
                        container.querySelector('#thumbnails').appendChild(thumbnail);
                    }, 'image/jpeg', 1);

                    if (currentUnsavedIndex !== null) {
                        unsavedThumbnails.splice(currentUnsavedIndex, 1);
                        renderUnsavedThumbnails();
                    }
                    currentUnsavedIndex = null;

                    cropper.destroy();
                    buttonContainer.style.display = 'none';
                    canvas.style.display = 'none';
                    downloadBtn.style.display = 'block';
                    imageInput.value = '';
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

                croppedCanvas.toBlob(function(blob) {
                    const thumbnail = document.createElement('img');
                    thumbnail.src = croppedCanvas.toDataURL();
                    thumbnail.width = 100;
                    thumbnail.dataset.index = savedImages.length;

                    thumbnail.addEventListener('click', function() {
                        const index = parseInt(this.dataset.index); // Retrieve index from data attribute
                        const fullQualityImageSrc = savedFullImages[index];
                        currentSavedIndex = index; // Update the currentSavedIndex
                        const img = new Image();
                        img.src = fullQualityImageSrc;
                        img.onload = function() {
                            start(img);
                        };
                    });

                    savedImages.push(blob);
                    toggleVisibility();
                    savedFullImages.push(croppedCanvas.toDataURL());
                    container.querySelector('#thumbnails').appendChild(thumbnail);

                    console.log('CURRENT UNSAVED INDEX: ', currentUnsavedIndex)

                    if (currentUnsavedIndex !== null) {
                        unsavedThumbnails.splice(currentUnsavedIndex, 1);
                        renderUnsavedThumbnails();
                    }
                    currentUnsavedIndex = null;
                }, 'image/jpeg', 1);

                cropper.destroy();
                buttonContainer.style.display = 'none';
                canvas.style.display = 'none';
                downloadBtn.style.display = 'block';
                imageInput.value = '';
            }


            // const canvasContainer = container.querySelector('.img-container');
            // canvasContainer.addEventListener('dragover', function (e) {
            //   e.preventDefault();
            // });

            const onDrop = (e) => {
                e.preventDefault();
                const files = e.dataTransfer.files;
                const filesArray = Array.from(files);
                if (savedImages.length + filesArray.length > max_photos) {
                    alert('Maximum number of photos reached.');
                    return;
                }
                if (existingFilesCount >= max_photos) {
                    alert('Maximum number of photos reached.');
                    return;
                }
                Array.from(files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = new Image();
                        img.src = e.target.result;
                        img.onload = function() {
                            const validation = validateImage(img);
                            if (!validation.isValid) {
                                alert(validation.error);
                                container.querySelector('#image').value = '';
                                return;
                            }
                            unsavedThumbnails.push(img);
                            renderUnsavedThumbnails();
                            start(img, unsavedThumbnails.length - 1); // Auto-populate canvas
                        };
                    };
                    reader.readAsDataURL(file);
                });
            };

            function renderUnsavedThumbnails() {
                const unsavedArea = container.querySelector('#unsaved-thumbnails');
                unsavedArea.innerHTML = '';
                unsavedThumbnails.forEach((img, index) => { // 'img' is an image object
                    const thumbnail = new Image();
                    thumbnail.src = img.src;
                    thumbnail.width = 100;
                    thumbnail.dataset.index = index;
                    thumbnail.style.border = '2px solid red'; // Red border for unsaved
                    thumbnail.addEventListener('click', function() {
                        const originalImage = unsavedThumbnails[index]; // Access the original full-quality image from unsavedThumbnails
                        start(originalImage, index); // Pass the actual original image object
                    });
                    unsavedArea.appendChild(thumbnail);
                });
                toggleVisibility();
            }

            function onImageUpload(event) {
                const files = event.target.files;
                const filesArray = Array.from(files);
                if (savedImages.length + filesArray.length > max_photos) {
                    alert('Maximum number of photos reached.');
                    return;
                }
                if (existingFilesCount >= max_photos) {
                    alert('Maximum number of photos reached.');
                    return;
                }
                Array.from(files).forEach(file => {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        const img = new Image();
                        img.src = e.target.result;
                        img.onload = function() {
                            const validation = validateImage(img);
                            if (!validation.isValid) {
                                alert(validation.error);
                                container.querySelector('#image').value = '';
                                return;
                            }
                            unsavedThumbnails.push(img);
                            renderUnsavedThumbnails();
                            start(img, unsavedThumbnails.length - 1); // Auto-populate canvas
                        };
                    };
                    reader.readAsDataURL(file);
                });
            }

            function toggleSaveImageText() {
                const imagesText = container.querySelector('#images-text');
                const downloadBtn = container.querySelector('#download');
                const thumbnailsArray = container.querySelector('#thumbnails').children;
                if (thumbnailsArray.length > 0) {
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
                    container.querySelector('#images-text').style.display = newHasSavedImages ? 'block' : 'none';
                    // container.querySelector('#saved-thumbnails-text').style.display = newHasSavedImages ? 'block' : 'none';
                    hasSavedImages = newHasSavedImages;
                }

                if (hasUnsavedThumbnails !== newHasUnsavedThumbnails) {
                    container.querySelector('#unsaved-thumbnails-text').style.display = newHasUnsavedThumbnails ? 'block' : 'none';
                    hasUnsavedThumbnails = newHasUnsavedThumbnails;
                }
            }

            // // Function to fetch existing file count from the backend
            // function fetchExistingFilesCount() {
            //     const xhr = new XMLHttpRequest();
            //     xhr.open('POST', file_path, true);
            //     xhr.setRequestHeader('Content-Type', 'application/json');
            //     xhr.onreadystatechange = function() {
            //         if (xhr.readyState === 4 && xhr.status === 200) {
            //             const response = JSON.parse(xhr.responseText);
            //             existingFilesCount = response.count;
            //         }
            //     };
            //     xhr.send(JSON.stringify({
            //         path: file_path
            //     }));
            // }

            function downloadImages() {
                Promise.all(savedImages.map(blob => new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onloadend = function() {
                            resolve(reader.result);
                        };
                        reader.onerror = reject;
                        reader.readAsDataURL(blob);
                    })))
                    .then(dataUrls => {
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', 'download_images.php', true);
                        xhr.setRequestHeader('Content-Type', 'application/json');
                        xhr.send(JSON.stringify({
                            images: dataUrls,
                            path: file_path,
                            max_photos: max_photos,
                            existing_files_count: existingFilesCount
                        }));
                    })
                    .catch(error => {
                        console.error("Failed to convert blobs to data URLs: ", error);
                    });
                container.querySelector('#thumbnails').html = '';
            }
            // function downloadImages() {
            // const maxPhotos = <?php echo json_encode($max_photos); ?>; // Fetch max_photos from PHP
            //     const id = <?php echo json_encode($id ?? null); ?>; // Fetch $id from PHP if it exists
            //     savedImages.forEach((blob, index) => {
            //         const url = URL.createObjectURL(blob);
            //         const a = document.createElement('a');
            //         a.style.display = 'none';
            //         a.href = url;
            //         // File renaming logic
            //         if (maxPhotos > 1) {
            //             a.download = `${index + 1}.jpg`; // If max number of files is greater than 1
            //         } else if (maxPhotos === 1 && id) {
            //             a.download = `${id}.jpg`; // If max number of files is 1 and $id is available
            //         }
            //         a.download = `image${index + 1}.jpg`;
            //         document.body.appendChild(a);
            //         a.click();
            //         URL.revokeObjectURL(url);
            //         document.body.removeChild(a);
            //     });
            // }

            function deleteImage() {
                if (currentSavedIndex !== null && currentSavedIndex !== undefined) {
                    // Remove from saved thumbnails and images
                    const thumbnailElement = container.querySelector(`[data-index='${currentSavedIndex}']`);
                    if (thumbnailElement) {
                        thumbnailElement.remove();
                        savedImages.splice(currentSavedIndex, 1);
                        savedFullImages.splice(currentSavedIndex, 1);

                        // Re-index remaining thumbnails
                        const remainingThumbnails = container.querySelectorAll('#thumbnails > img');
                        remainingThumbnails.forEach((thumbnail, index) => {
                            thumbnail.dataset.index = index;
                        });

                        setTimeout(() => {
                            toggleSaveImageText();
                        }, 100); // waits 100 milliseconds
                        toggleVisibility();
                    }
                } else  if (currentUnsavedIndex !== null && currentUnsavedIndex !== undefined) {
                    // Remove from unsaved thumbnails
                    unsavedThumbnails.splice(currentUnsavedIndex, 1);
                    renderUnsavedThumbnails();
                }
                if (cropper) {
                    cropper.destroy();
                }
                buttonContainer.style.display = 'none';
                canvas.style.display = 'none';
                currentUnsavedIndex = null;
                currentSavedIndex = null;
                imageInput.value = '';
            }



            // Main Execution
            function main() {
                // Main function to execute the script
                initializeEventListeners();
                // fetchExistingFilesCount();
            }

            // Execute the main function when the document is ready
            document.addEventListener("DOMContentLoaded", main);
        })();
        </script>
</body>

    </html>
<?php
}
?>