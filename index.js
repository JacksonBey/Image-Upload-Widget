// constraints
let max_width = 800;  // Set to 0 to ignore
let max_height = 600; // Set to 0 to ignore
let min_width = 100;  // Set to 0 to ignore
let min_height = 100; // Set to 0 to ignore
let max_photos = 10;  // Maximum number of photos allowed
let filePath = null;

// constraints
// let max_width = <?php echo $max_width; ?>;
// let max_height = <?php echo $max_height; ?>;
// let min_width = <?php echo $min_width; ?>;
// let min_height = <?php echo $min_height; ?>;
// let max_photos = <?php echo $max_photos; ?>;
// let file_path = '<?php echo $file_path; ?>';

function validateImage(imageElement) {
  const width = imageElement.width;
  const height = imageElement.height;
  if (savedImages.length >= max_photos) {
    alert('Maximum number of photos reached.');
    return { isValid: false, error: "Maximum number of photos reached." };
  }

  if ((max_width > 0 && width > max_width) || (max_height > 0 && height > max_height)) {
    return { isValid: false, error: "Image dimensions exceed maximum limits" };
  }

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


// DOM Elements
const cropBtn = document.getElementById('crop');
const cancelBtn = document.getElementById('cancel');
const canvas = document.getElementById('canvas');
const buttonContainer = document.querySelector('.button-container');
const canvasContainer = document.querySelector('.img-container');
const saveBtn = document.getElementById('save');
const downloadBtn = document.getElementById('download');

function initializeEventListeners() {
  cropBtn.addEventListener('click', cropAndSave);
  cancelBtn.addEventListener('click', cancelImage);
  saveBtn.addEventListener('click', function () {
    saveImage(); // Assuming saveImage is refactored accordingly
  });
  downloadBtn.addEventListener('click', function () {
    downloadImages();
  });
  

  canvasContainer.addEventListener('dragover', e => e.preventDefault());
  canvasContainer.addEventListener('drop', onDrop);

  const imageInput = document.getElementById('image');
  imageInput.addEventListener('change', onImageUpload);
}




function start(imageElement, index) {
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

  document.getElementById('rotateClockwise').addEventListener('click', function () {
    cropper.rotate(90);
  });

  document.getElementById('rotateCounterClockwise').addEventListener('click', function () {
    cropper.rotate(-90);
  });
}

// const cancelBtn = document.getElementById('cancel');
function cancelImage() {
  if (cropper) {
    cropper.destroy();
  }
  buttonContainer.style.display = 'none';
  canvas.style.display = 'none';
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
    croppedCanvas.toBlob(function (blob) {
      const thumbnail = document.createElement('img');
      thumbnail.src = croppedCanvas.toDataURL();
      thumbnail.width = 100;

      // Change 1: Assign index as data attribute
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


// const canvasContainer = document.querySelector('.img-container');
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
    reader.readAsDataURL(file);
  });
};

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
      start(originalImage, index); // Pass the actual original image object
    });
    unsavedArea.appendChild(thumbnail);
  });
  toggleVisibility();
}

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
    reader.readAsDataURL(file);
  });
}

function toggleSaveImageText() {
  const imagesText = document.getElementById('images-text');
  const downloadBtn = document.getElementById('download');
  const thumbnailsArray = document.getElementById('thumbnails').children;
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
    document.getElementById('images-text').style.display = newHasSavedImages ? 'block' : 'none';
    document.getElementById('saved-thumbnails-text').style.display = newHasSavedImages ? 'block' : 'none';
    hasSavedImages = newHasSavedImages;
  }

  if (hasUnsavedThumbnails !== newHasUnsavedThumbnails) {
    document.getElementById('unsaved-thumbnails-text').style.display = newHasUnsavedThumbnails ? 'block' : 'none';
    hasUnsavedThumbnails = newHasUnsavedThumbnails;
  }
}

// function downloadImages() {
//   Promise.all(savedImages.map(blob => new Promise((resolve, reject) => {
//       const reader = new FileReader();
//       reader.onloadend = function() {
//           resolve(reader.result);
//       };
//       reader.onerror = reject;
//       reader.readAsDataURL(blob);
//   })))
//   .then(dataUrls => {
//       const xhr = new XMLHttpRequest();
//       xhr.open('POST', 'download_images.php', true);
//       xhr.setRequestHeader('Content-Type', 'application/json');
//       xhr.send(JSON.stringify({
//           images: dataUrls,
//           path: filePath
//       }));
//   })
//   .catch(error => {
//       console.error("Failed to convert blobs to data URLs: ", error);
//   });
// }
function downloadImages() {
  savedImages.forEach((blob, index) => {
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.style.display = 'none';
      a.href = url;
      a.download = `image${index + 1}.jpg`;
      document.body.appendChild(a);
      a.click();
      URL.revokeObjectURL(url);
      document.body.removeChild(a);
  });
}


// Main Execution
function main() {
  // Main function to execute the script
  initializeEventListeners();
}

// Execute the main function when the document is ready
document.addEventListener("DOMContentLoaded", main);
