
// Configuration Constants
const MAX_WIDTH = 800;
const MAX_HEIGHT = 600;
const MIN_WIDTH = 100;
const MIN_HEIGHT = 100;
const MAX_PHOTOS = 10;

// State Variables
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
const thumbnailsArea = document.getElementById('thumbnails');

// Event Handlers
cropBtn.addEventListener('click', cropAndSave);
cancelBtn.addEventListener('click', cancelImage);
document.getElementById('download').addEventListener('click', downloadImages);
document.getElementById('save').addEventListener('click', saveImage);
document.getElementById('rotateClockwise').addEventListener('click', rotateClockwise);
document.getElementById('rotateCounterClockwise').addEventListener('click', rotateCounterClockwise);

function validateImage(imageElement) {
  const width = imageElement.width;
  const height = imageElement.height;

  if (width > MAX_WIDTH || height > MAX_HEIGHT) {
    return { isValid: false, error: "Image dimensions exceed maximum limits." };
  }

  if (width < MIN_WIDTH || height < MIN_HEIGHT) {
    return { isValid: false, error: "Image dimensions are below minimum limits." };
  }

  return { isValid: true };
}

function start(imageElement, index) {
  currentUnsavedIndex = index;
  const ctx = canvas.getContext('2d');
  ctx.drawImage(imageElement, 0, 0, canvas.width, canvas.height);

  if (cropper) {
    cropper.destroy();
  }
  cropper = new Cropper(canvas, {
    autoCropArea: 1
  });
  buttonContainer.classList.remove("hidden");
  canvas.classList.remove("hidden");
}

function cancelImage() {
  if (cropper) {
    cropper.destroy();
  }
  buttonContainer.classList.add("hidden");
  canvas.classList.add("hidden");
}

function cropAndSave() {
  if (!cropper) {
    console.error("Error: Cropper not initialized.");
    return;
  }

  const croppedCanvas = cropper.getCroppedCanvas({
    width: 160,
    height: 160,
  });

  croppedCanvas.toBlob(function (blob) {
    savedImages.push(blob);
    savedFullImages.push(croppedCanvas.toDataURL());
    
    // Additional logic to update UI
    const thumbnail = document.createElement('img');
    thumbnail.src = croppedCanvas.toDataURL();
    thumbnail.width = 100;
    thumbnailsArea.appendChild(thumbnail);

  }, 'image/jpeg', 1);

  cropper.destroy();
  buttonContainer.classList.add("hidden");
  canvas.classList.add("hidden");
}

function saveImage() {
  if (!cropper) {
    console.error("Error: Cropper not initialized.");
    return;
  }

  const canvasData = cropper.getCanvasData();
  const croppedCanvas = cropper.getCroppedCanvas(canvasData);

  croppedCanvas.toBlob(function (blob) {
    savedImages.push(blob);
    savedFullImages.push(croppedCanvas.toDataURL());
    
    // Additional logic to update UI
    const thumbnail = document.createElement('img');
    thumbnail.src = croppedCanvas.toDataURL();
    thumbnail.width = 100;
    thumbnailsArea.appendChild(thumbnail);

  }, 'image/jpeg', 1);

  cropper.destroy();
  buttonContainer.classList.add("hidden");
  canvas.classList.add("hidden");
}

function downloadImages() {
  savedImages.forEach((image, index) => {
    const link = document.createElement("a");
    link.href = URL.createObjectURL(image);
    link.download = `image_${index}.jpg`;
    link.click();
  });
}

function rotateClockwise() {
  cropper.rotate(90);
}

function rotateCounterClockwise() {
  cropper.rotate(-90);
}

function toggleVisibility() {
  const thumbnailsArray = thumbnailsArea.children;
  const imagesText = document.getElementById('images-text');
  const downloadBtn = document.getElementById('download');
  if (thumbnailsArray.length > 0) {
    imagesText.classList.remove('hidden');
    downloadBtn.classList.remove('hidden');
  } else {
    imagesText.classList.add('hidden');
    downloadBtn.classList.add('hidden');
  }
}

// Initialize
window.addEventListener('DOMContentLoaded', function() {
  const imageInput = document.getElementById('image');
  imageInput.addEventListener('change', function(event) {
    const file = event.target.files[0];
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = function(e) {
      const img = new Image();
      img.src = e.target.result;
      img.onload = function() {
        const validation = validateImage(img);
        if (validation.isValid) {
          unsavedThumbnails.push(img);
          start(img, unsavedThumbnails.length - 1);
        } else {
          alert(validation.error);
        }
      };
    };
  });
});
