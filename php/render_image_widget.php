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
        <title>Image Widget</title>
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
                <label for="image-<?php echo $unique_id; ?>" class="custom-file-upload">
                    <input type="file" name="" id="image-<?php echo $unique_id; ?>" required accept="image/*" multiple>
                    <span id="file-upload-text">Drag Your File(s) Here or Click to Choose</span>
                </label>
            </div>
            <div class="img-edit-container">
                <canvas id="canvas"></canvas>
            </div>
            <div class="button-container" style="display:none;">
                <button type="button" class="standard-button" id="cancel">Cancel</button>
                <button type="button" class="standard-button" id="delete">Delete</button>
                <!-- <button type="button" class="standard-button" id="crop">Crop</button> -->
                <button id="rotateClockwise" class="standard-button">Rotate Clockwise</button>
                <button id="rotateCounterClockwise" class="standard-button">Rotate Counter Clockwise</button>
                <button id="save" class="standard-button">Save</button>

            </div>
            <div>
                <h3 id="unsaved-thumbnails-text" style="display: none;">Unsaved</h3>
                <div id="unsaved-thumbnails"></div>
            </div>
            <div class="crop-tooltip">Drag to move, pinch to zoom, and adjust the box to crop.</div>

        </div>
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
            let originalWidth, originalHeight;
            let hasRotateEventAdded = false;

            let currentSelectedSavedIndex = null;
            let currentSelectedUnsavedIndex = null;

            let selectedImages = []; // An array to store selected images
            let selectedImageIndex = null
            let originalImage = null; 
            let globalDx = 0;
            let globalDy = 0;
            let savedFileNames = [];
            let imageType = null;


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
            // let imageInput = container.querySelector('#image');
            let imageInput = container.querySelector(`#image-${unique_id}`);
            const tooltip = container.querySelector('.crop-tooltip');

            function initializeEventListeners() {
                // cropBtn.addEventListener('click', cropAndSave);
                cancelBtn.addEventListener('click', cancelImage);
                saveBtn.addEventListener('click', function() {
                    saveImage();
                });
                downloadBtn.addEventListener('click', function() {
                    downloadImages();
                });


                imageUploadContainer.addEventListener('dragover', e => e.preventDefault());
                imageUploadContainer.addEventListener('drop', function(e) {
                    const files = e.dataTransfer.files; // This will give you the FileList
                    if (files.length > 0) {
                        const file = files[0]; // Assuming you're interested in the first file
                        const img = new Image();
                        const objectUrl = URL.createObjectURL(file);
                        
                        img.onload = function() {
                            originalWidth = this.naturalWidth;
                            originalHeight = this.naturalHeight;
                            aspectRatio = originalWidth / originalHeight;
                            // Call your onDrop function here if necessary.
                            URL.revokeObjectURL(objectUrl);
                        };
                        
                        img.src = objectUrl;
                    }
                    onDrop(e)
                });

                // imageInput = container.querySelector('#image');
                imageInput = container.querySelector(`#image-${unique_id}`);
                imageInput.addEventListener('change', function(e) {
                    const files = e.target.files; // This will give you the FileList
                    if (files.length > 0) {
                        const file = files[0]; // Assuming you're interested in the first file
                        const img = new Image();
                        const objectUrl = URL.createObjectURL(file);

                        img.onload = function() {
                            originalWidth = this.naturalWidth;
                            originalHeight = this.naturalHeight;
                            aspectRatio = originalWidth / originalHeight;
                            URL.revokeObjectURL(objectUrl);
                        };

                        img.src = objectUrl;
                    }
                    onImageUpload(e);
                });

                deleteBtn.addEventListener('click', deleteImage);

                canvasContainer.addEventListener('mouseenter', function() {
                    tooltip.style.display = 'block';
                });

                canvasContainer.addEventListener('mouseleave', function() {
                    tooltip.style.display = 'none';
                });
            }

            let totalRotation = 0;  // Declare this variable at a higher scope to persist its value.

            function resizeCanvasAndImage(imageElement, angle) {
                totalRotation += angle; // Increment total angle by the passed angle.
                const data = cropper.getData();
                const newWidth = data.height;
                const newHeight = data.width;

                canvas.width = newWidth;
                canvas.height = newHeight;

                cropper.destroy();
                const ctx = canvas.getContext('2d');
                
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                ctx.save();
                ctx.translate(canvas.width / 2, canvas.height / 2);
                ctx.rotate((totalRotation * Math.PI) / 180);
                ctx.drawImage(imageElement, -imageElement.naturalWidth / 2, -imageElement.naturalHeight / 2);
                ctx.restore();

                cropper = new Cropper(canvas, {
                    autoCropArea: 1,
                    dragMode: 'move',
                    cropBoxResizable: true,
                    cropBoxMovable: true,
                    ready: function () {
                        buttonContainer.style.display = 'block';
                    }
                });
            }

            function start(imageElement, index) {
                // currentUnsavedIndex = index;
                originalImage = imageElement;  // Set the original image
                selectedImageIndex = index;
                const ctx = canvas.getContext('2d');

                // Set canvas dimensions to match the original image dimensions
                canvas.width = imageElement.naturalWidth;
                canvas.height = imageElement.naturalHeight;

                // Clear canvas and draw the original image
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(imageElement, 0, 0, imageElement.naturalWidth, imageElement.naturalHeight);

                // Initialize Cropper.js
                if (cropper) {
                    cropper.destroy();
                }

                cropper = new Cropper(canvas, {
                    autoCropArea: 1,
                    dragMode: 'move',
                    cropBoxResizable: true,
                    cropBoxMovable: true,
                    ready: function () {
                        buttonContainer.style.display = 'block';
                    }

                });
                if (!hasRotateEventAdded) {
                    // Rotation events
                    container.querySelector('#rotateClockwise').addEventListener('click', function() {
                        cropper.rotate(90);
                        resizeCanvasAndImage(originalImage, 90);
                    });

                    container.querySelector('#rotateCounterClockwise').addEventListener('click', function() {
                        cropper.rotate(-90);
                        resizeCanvasAndImage(originalImage, -90);
                    });

                    hasRotateEventAdded = true;
                }
            }

            function cancelImage() {
                if (cropper) {
                    cropper.destroy();
                }
                buttonContainer.style.display = 'none';
                canvas.style.visibility = 'hidden';
                imageInput.value = '';
            } 

            function saveImage() {
                if (!cropper) {
                    console.error("Error: Cropper not initialized.");
                    return;
                }
                const croppedCanvas = cropper.getCroppedCanvas({
                    width: originalWidth,
                    height: originalHeight
                });

                if (!croppedCanvas) {
                    console.error("Error: croppedCanvas is null");
                    return;
                }
                const saveImageType = imageType === 'image/png' ? 'image/png' : 'image/jpeg';
                croppedCanvas.toBlob(function(blob) {
                    const thumbnail = document.createElement('img');

                    thumbnail.src = croppedCanvas.toDataURL();
                    thumbnail.width = 100;

                    const existingThumbnail = container.querySelector(`#thumbnails > img[data-index='${currentSelectedSavedIndex}']`);
                    if(existingThumbnail === null){
                        savedImages.push(blob);
                        savedFileNames.push(null);
                        savedFullImages.push(croppedCanvas.toDataURL());
                        thumbnail.dataset.index = savedImages.length -1;
                    } else {
                        thumbnail.dataset.index = currentSelectedSavedIndex;  // Use existing index
                        savedImages[currentSelectedSavedIndex] = blob;
                        savedFullImages[currentSelectedSavedIndex] = croppedCanvas.toDataURL();
                    }
                    thumbnail.addEventListener('click', function() {
                        totalRotation = 0
                        const index = parseInt(this.dataset.index); // Retrieve index from data attribute
                        const fullQualityImageSrc = savedFullImages[index];

                        currentSelectedSavedIndex = index;
                        currentSavedIndex = index; // Update the currentSavedIndex
                        const img = new Image();
                        if (typeof fullQualityImageSrc === 'string') {
                            // Handle URLs
                            img.src = fullQualityImageSrc;
                        } else if (fullQualityImageSrc instanceof Blob) {
                            // Handle Blobs
                            img.src = URL.createObjectURL(fullQualityImageSrc);
                        }


                        
                        img.onload = function() {

                            start(img, index);
                        };
                    });
                    if (currentSelectedSavedIndex !== null) {
                        // Only replace if a file with this index already exists
                        if(savedFileNames[currentSelectedSavedIndex]) {
                            // Update blob in savedImages
                            savedImages[currentSelectedSavedIndex] = blob;

                            // Retrieve existing file name
                            const existingFileName = savedFileNames[currentSelectedSavedIndex];

                            const formData = new FormData();
                            formData.append('file_path', file_path);
                            formData.append('file_name', existingFileName);
                            formData.append('image_data', blob); // blob should be your image Blob object

                            const xhr = new XMLHttpRequest();
                            xhr.open('POST', 'replace_image.php', true);
                            // Don't set Content-Type manually, FormData will take care of it

                            xhr.onreadystatechange = function() {
                                if (xhr.readyState === 4 && xhr.status === 200) {
                                    const response = JSON.parse(xhr.responseText);
                                    if (response.success) {
                                        console.log('Image replaced successfully.');
                                    } else {
                                        console.error('Failed to replace image:', response.message);
                                    }
                                }
                            };

                            xhr.send(formData);
                        } else {
                            thumbnail.style.border = '2px solid blue'; // Blue border for saved
                            downloadBtn.style.display = 'block';

                        }

                        // Find and replace the existing thumbnail element with the same data-index
                        if (existingThumbnail !== null) {
                            existingThumbnail.replaceWith(thumbnail);
                            savedFullImages[currentSelectedSavedIndex] = croppedCanvas.toDataURL();
                        }

                    } else {
                        thumbnail.style.border = '2px solid blue'; // Blue border for saved
                    downloadBtn.style.display = 'block';

                        container.querySelector('#thumbnails').appendChild(thumbnail);
                    }
                    toggleVisibility();
                    if (currentUnsavedIndex !== null) {
                        unsavedThumbnails.splice(currentUnsavedIndex, 1);
                        renderUnsavedThumbnails();
                        currentUnsavedIndex = null;
                    }
                }, saveImageType);


                cropper.destroy();
                buttonContainer.style.display = 'none';
                canvas.style.visibility = 'hidden';
                imageInput.value = '';
            }

            const onDrop = (e) => {
                totalRotation = 0
                e.preventDefault();

                currentSelectedSavedIndex = null;

                const files = e.dataTransfer.files;
                const filesArray = Array.from(files);
                if (selectedImages.length + filesArray.length > max_photos) {
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
                            imageType = img.src.substring("data:".length, img.src.indexOf(";base64"));
                            const validation = validateImage(img);
                            if (!validation.isValid) {
                                alert(validation.error);
                                return;
                            }
                            selectedImages.push(img);
                            unsavedThumbnails.push(img);
                            renderUnsavedThumbnails();
                            currentUnsavedIndex = unsavedThumbnails.length - 1;
                            start(img, unsavedThumbnails.length - 1); 
                        };
                    };
                    reader.readAsDataURL(file);
                });
            };

            function onImageUpload(event) {
                totalRotation = 0
                const files = event.target.files;
                const filesArray = Array.from(files);

                currentSelectedSavedIndex = null;

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
                            imageType = img.src.substring("data:".length, img.src.indexOf(";base64"));
                            const validation = validateImage(img);
                            if (!validation.isValid) {
                                alert(validation.error);
                                container.querySelector('#image').value = '';
                                return;
                            }
                            selectedImages.push(img);
                            currentUnsavedIndex = unsavedThumbnails.length - 1
                            unsavedThumbnails.push(img);
                            renderUnsavedThumbnails();
                            start(img, unsavedThumbnails.length - 1); // Auto-populate canvas
                        };
                    };
                    reader.readAsDataURL(file);
                });
            }

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
                        totalRotation = 0
                        const originalImage = unsavedThumbnails[index]; // Access the original full-quality image from unsavedThumbnails
                        currentUnsavedIndex = index;
                        currentSelectedUnsavedIndex = index;
                        currentSelectedSavedIndex = null;
                        start(originalImage, index); // Pass the actual original image object
                    });
                    unsavedArea.appendChild(thumbnail);
                });
                toggleVisibility();
            }

            function renderSavedThumbnails(){
                const savedArea = container.querySelector('#thumbnails');
                savedArea.innerHTML = '';
                savedImages.forEach((imgURL, index) => {
                    const thumbnail = new Image();
                    thumbnail.src = imgURL;
                    thumbnail.width = 100;
                    thumbnail.dataset.index = index;
                    thumbnail.addEventListener('click', function() {
                        totalRotation = 0
                        index = parseInt(this.dataset.index); // Retrieve index from data attribute
                        const fullQualityImageSrc = savedFullImages[index];
                        currentSelectedSavedIndex = index;
                        currentSavedIndex = index; // Update the currentSavedIndex
                        start(thumbnail, index);
                    });
                    savedArea.appendChild(thumbnail);
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
                    hasSavedImages = newHasSavedImages;
                }

                if (hasUnsavedThumbnails !== newHasUnsavedThumbnails) {
                    container.querySelector('#unsaved-thumbnails-text').style.display = newHasUnsavedThumbnails ? 'block' : 'none';
                    hasUnsavedThumbnails = newHasUnsavedThumbnails;
                }
            }
            
            function downloadImages() {
                const imagePromises = savedImages
                    .filter(blob => blob instanceof Blob) // Filter out anything that's not a Blob
                    .map(blob => {
                        return new Promise((resolve, reject) => {
                            const canvas = document.createElement('canvas');
                            const img = new Image();
                            img.onload = () => {
                                canvas.width = img.naturalWidth;
                                canvas.height = img.naturalHeight;
                                const ctx = canvas.getContext('2d');
                                ctx.drawImage(img, 0, 0);
                                canvas.toBlob(
                                    blob => {
                                        if (!blob) {
                                            reject(new Error("Failed to create a blob."));
                                            return;
                                        }
                                        const reader = new FileReader();
                                        reader.onload = () => {
                                            resolve(reader.result);
                                        };
                                        reader.readAsDataURL(blob);
                                    },
                                    imageType ? imageType : 'image/jpeg'
                                );
                            };
                            img.onerror = () => {
                                reject(new Error("Failed to load image."));
                            };
                            img.src = URL.createObjectURL(blob);
                        });
                    });

                Promise.all(imagePromises)
                    .then(dataUrls => {
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', 'download_images.php', true);
                        xhr.setRequestHeader('Content-Type', 'application/json');
                        xhr.onreadystatechange = function () {
                            if (xhr.readyState === 4) {
                                if (xhr.status === 200) {
                                    const response = JSON.parse(xhr.responseText);
                                    if (response.status === 'success') {
                                        alert('Images successfully uploaded.');
                                        container.querySelector('#thumbnails').innerHTML = '';
                                        downloadBtn.style.display = 'none';
                                        const imagesText = container.querySelector('#images-text');
                                        imagesText.style.display = 'none';
                                        savedImages = [];
                                        fetchExistingFiles();
                                    } else {
                                        alert('Upload failed: ' + response.status);
                                    }
                                } else {
                                    console.error('Failed to upload images. HTTP Status: ' + xhr.status);
                                }
                            }
                        };
                        xhr.send(JSON.stringify({
                            images: dataUrls,
                            fileNames: savedFileNames,  // new line to send file names
                            path: file_path,
                            max_photos: max_photos,
                            existing_files_count: existingFilesCount
                        }));
                    })
                    .catch(error => {
                        console.error("Failed to convert blobs to data URLs: ", error);
                    });
            }


            // save to local machine
            // function downloadImages() {
            //     const maxPhotos = <?php echo json_encode($max_photos); ?>; // Fetch max_photos from PHP
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
                        const imgURLToDelete = savedImages[currentSavedIndex];
                        if (typeof imgURLToDelete === 'string') {
                            deleteFromServer(imgURLToDelete);
                        }
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
                canvas.style.visibility = 'hidden';
                currentUnsavedIndex = null;
                currentSavedIndex = null;
                imageInput.value = '';
            }

            function deleteFromServer(imgURL) {
                fetch('delete_image.php', {
                    method: 'POST',
                    headers: {
                    'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ path: imgURL }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Remove the image thumbnail from the page and other clean-up actions
                        console.log('successfully deleted :', imgURL)
                    } else {
                    console.error('Failed to delete image:', data.message);
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                });
            }

            function fetchExistingFiles() {
                fetch(`fetch_images.php?path=${file_path}`)
                    .then(response => response.json())
                    .then(data => {
                        savedImages = data.map(item => item.path);
                        savedFileNames = data.map(item => item.name); // new line to store file names
                        savedFullImages = [...savedImages];
                        renderSavedThumbnails();
                        toggleVisibility();
                    })
                    .catch(error => console.error("Failed to fetch existing images:", error));
            }

            function main() {
                initializeEventListeners();
                fetchExistingFiles();
            }

            document.addEventListener("DOMContentLoaded", main);
        })();
        </script>
</body>

    </html>
<?php
}
?>