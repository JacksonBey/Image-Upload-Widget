
// Constants for Constraints
const CONSTRAINTS = {
  max_width: 800,  // Set to 0 to ignore
  max_height: 600, // Set to 0 to ignore
  min_width: 100,  // Set to 0 to ignore
  min_height: 100, // Set to 0 to ignore
  max_photos: 10   // Maximum number of photos allowed
};


// DOM Elements
const cropBtn = document.getElementById('crop');
const cancelBtn = document.getElementById('cancel');
const canvas = document.getElementById('canvas');
const buttonContainer = document.querySelector('.button-container');

// Initialize Event Listeners
function initializeEventListeners() {
    cropBtn.addEventListener('click', cropAndSave);
    cancelBtn.addEventListener('click', cancelImage);
    document.getElementById('download').addEventListener('click', implementDownloadLogic);
    document.getElementById('save').addEventListener('click', saveImage);
}


function validateImage(imageElement) {
  const { width, height } = imageElement;
  
  // Validate maximum number of photos
  if (savedImages.length >= CONSTRAINTS.max_photos) {
      return { isValid: false, error: "Maximum number of photos reached." };
  }
  
  // Validate dimensions
  if (exceedsMaxDimensions(width, height) || belowMinDimensions(width, height)) {
      return { isValid: false, error: "Invalid image dimensions" };
  }

  return { isValid: true };
}

function exceedsMaxDimensions(width, height) {
  return (CONSTRAINTS.max_width > 0 && width > CONSTRAINTS.max_width) || 
         (CONSTRAINTS.max_height > 0 && height > CONSTRAINTS.max_height);
}

function belowMinDimensions(width, height) {
  return (CONSTRAINTS.min_width > 0 && width < CONSTRAINTS.min_width) || 
         (CONSTRAINTS.min_height > 0 && height < CONSTRAINTS.min_height);
}


function cropAndSave() {
  if (!validateCropperInitialization()) return;
  const croppedCanvas = getCroppedCanvas();
  if (!validateCroppedCanvas(croppedCanvas)) return;
  
  croppedCanvas.toBlob(blob => {
      saveCroppedImage(blob, croppedCanvas);
  }, 'image/jpeg', 1);
}

function cancelImage() {
  if (!validateCropperInitialization()) return;
  destroyCropper();
  hideUIElements();
}

function saveImage() {
  if (!validateCropperInitialization()) return;
  const croppedCanvas = getCroppedCanvas();
  if (!validateCroppedCanvas(croppedCanvas)) return;
  
  croppedCanvas.toBlob(blob => {
      saveCroppedImage(blob, croppedCanvas);
  }, 'image/jpeg', 1);
}

function saveCroppedImage(blob, croppedCanvas) {
  const thumbnail = createThumbnail(croppedCanvas.toDataURL());
  attachThumbnailEvent(thumbnail);
  savedImages.push(blob);
  updateUIAfterSave();
}

function createThumbnail(src) {
  const thumbnail = document.createElement('img');
  thumbnail.src = src;
  thumbnail.width = 100;
  return thumbnail;
}

function attachThumbnailEvent(thumbnail) {
  // Attach index as a data attribute to the thumbnail
  thumbnail.dataset.index = savedImages.length;
  thumbnail.addEventListener('click', function() {
      openFullImage(this.dataset.index);
  });
}

function openFullImage(index) {
  // Logic to open full-size image
}