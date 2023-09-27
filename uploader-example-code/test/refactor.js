// REfactor:
// Constants for Constraints
const CONSTRAINTS = {
  max_width: 800,
  max_height: 600,
  min_width: 100,
  min_height: 100,
  max_photos: 10
};

// State Variables
let savedImages = [];
let unsavedThumbnails = [];
let currentUnsavedIndex = null;
let cropper = null;

// DOM Elements
const cropBtn = document.getElementById('crop');
const cancelBtn = document.getElementById('cancel');
const canvas = document.getElementById('canvas');
const buttonContainer = document.querySelector('.button-container');
const canvasContainer = document.querySelector('.img-container');

function initializeEventListeners() {
  cropBtn.addEventListener('click', cropAndSave);
  cancelBtn.addEventListener('click', cancelImage);

  canvasContainer.addEventListener('dragover', e => e.preventDefault());
  canvasContainer.addEventListener('drop', onDrop);

  const imageInput = document.getElementById('image');
  imageInput.addEventListener('change', onImageUpload);
}

const handleFileReading = (file) => {
  const reader = new FileReader();
  reader.onload = (e) => handleImageLoading(e.target.result);
  reader.readAsDataURL(file);
};

const handleImageLoading = (imageSrc) => {
  const img = new Image();
  img.src = imageSrc;
  img.onload = () => {
    const validation = validateImage(img);
    if (!validation.isValid) {
      alert(validation.error);
      document.getElementById('image').value = '';
      return;
    }
    unsavedThumbnails.push(img);
    renderUnsavedThumbnails();
    initCanvas(img, unsavedThumbnails.length - 1);
  };
};

const handleFileUpload = (files) => {
  const filesArray = Array.from(files);
  if (savedImages.length + filesArray.length >= max_photos) {
    alert('Maximum number of photos reached.');
    return;
  }
  filesArray.forEach(handleFileReading);
};

const initCanvas = (imageElement, index) => {
  currentUnsavedIndex = index;
  const ctx = canvas.getContext('2d');
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  ctx.drawImage(imageElement, 0, 0, canvas.width, canvas.height);

  if (cropper) {
    cropper.destroy();
  }
  cropper = new Cropper(canvas, { autoCropArea: 1 });
  buttonContainer.style.display = 'block';
  initializeCropAndRotateButtons();
};

const initializeCropAndRotateButtons = () => {
  document.getElementById('rotateClockwise').addEventListener('click', () => cropper.rotate(90));
  document.getElementById('rotateCounterClockwise').addEventListener('click', () => cropper.rotate(-90));
};

const cancelImage = () => {
  if (cropper) {
    cropper.destroy();
  }
  buttonContainer.style.display = 'none';
  canvas.style.display = 'none';
};

const cropAndSave = () => {
  if (!cropper) {
    console.log("Error: Cropper not initialized.");
    return;
  }

  const croppedCanvas = cropper.getCroppedCanvas({ width: 160, height: 160 });
  if (!croppedCanvas) {
    console.log("Error: Cropped canvas is null. Make sure crop box is set.");
    return;
  }

  croppedCanvas.toBlob((blob) => {
    const thumbnail = document.createElement('img');
    thumbnail.src = croppedCanvas.toDataURL();
    thumbnail.width = 100;

    const index = savedImages.length;
    thumbnail.addEventListener('click', () => {
      const img = new Image();
      img.src = savedFullImages[index];
      img.onload = () => initCanvas(img);
    });

    savedImages.push(blob);
    savedFullImages.push(croppedCanvas.toDataURL());
    document.getElementById('thumbnails').appendChild(thumbnail);
  }, 'image/jpeg', 1);

  if (currentUnsavedIndex !== null) {
    unsavedThumbnails.splice(currentUnsavedIndex, 1);
    renderUnsavedThumbnails();
  }
  currentUnsavedIndex = null;
  cancelImage();
};

canvasContainer.addEventListener('drop', (e) => {
  e.preventDefault();
  handleFileUpload(e.dataTransfer.files);
});

const onImageUpload = (event) => handleFileUpload(event.target.files);


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

// Main Execution
function main() {
  // Main function to execute the script
  initializeEventListeners();
}

// Execute the main function when the document is ready
document.addEventListener("DOMContentLoaded", main);



// OLD:

// constraints
// let max_width = 800;  // Set to 0 to ignore
// let max_height = 600; // Set to 0 to ignore
// let min_width = 100;  // Set to 0 to ignore
// let min_height = 100; // Set to 0 to ignore
// let max_photos = 10;  // Maximum number of photos allowed

// function validateImage(imageElement) {
//   const width = imageElement.width;
//   const height = imageElement.height;
//   // Check for max photos
//   if (savedImages.length >= max_photos) {
//     alert('Maximum number of photos reached.');
//     return { isValid: false, error: "Maximum number of photos reached." };
//   }

//   // Max Width and Height
//   if ((max_width > 0 && width > max_width) || (max_height > 0 && height > max_height)) {
//     return { isValid: false, error: "Image dimensions exceed maximum limits" };
//   }

//   // Min Width and Height
//   if ((min_width > 0 && width < min_width) || (min_height > 0 && height < min_height)) {
//     return { isValid: false, error: "Image dimensions are below minimum limits" };
//   }

//   return { isValid: true };
// }

// let thumbnailToReplace = null;
// let cropper = null;
// let unsavedThumbnails = [];
// let savedImages = [];
// let savedFullImages = [];
// let currentUnsavedIndex = null;
// let hasSavedImages = false;
// let hasUnsavedThumbnails = false;

// const cropBtn = document.getElementById('crop');
// // const saveBtn = document.getElementById('save');
// const cancelBtn = document.getElementById('cancel');
// const canvas = document.getElementById('canvas');
// const buttonContainer = document.querySelector('.button-container');

// cropBtn.addEventListener('click', cropAndSave);
// // saveBtn.addEventListener('click', saveImage);
// cancelBtn.addEventListener('click', cancelImage);
// document.getElementById('download').addEventListener('click', function () {
//   // Implement your download logic here
// });
// document.getElementById('save').addEventListener('click', function () {
//   saveImage(); // Assuming saveImage is refactored accordingly
// });
// function start(imageElement, index) {
//   // console.log("Starting to process image", imageElement, index); // Debug line
//   console.log("Start Function: Called with index ", index);
//   currentUnsavedIndex = index; // store the index
//   const ctx = canvas.getContext('2d');
//   canvas.style.display = 'block';
//   ctx.clearRect(0, 0, canvas.width, canvas.height);
//   ctx.drawImage(imageElement, 0, 0, canvas.width, canvas.height);

//   if (cropper) {
//     cropper.destroy();
//   }
//   cropper = new Cropper(canvas, {
//     autoCropArea: 1 // Sets crop box to 100% of the image area
//   });
//   buttonContainer.style.display = 'block';

//   function createThumbnail(src) {
//     const thumbnail = document.createElement('img');
//     thumbnail.src = src;
//     thumbnail.width = 100;
//     return thumbnail;
//   }


//   document.getElementById('rotateClockwise').addEventListener('click', function () {
//     cropper.rotate(90);
//   });

//   document.getElementById('rotateCounterClockwise').addEventListener('click', function () {
//     cropper.rotate(-90);
//   });
// }

// // const cancelBtn = document.getElementById('cancel');
// function cancelImage() {
//   if (cropper) {
//     cropper.destroy();
//   }
//   buttonContainer.style.display = 'none';
//   canvas.style.display = 'none';
// }

// function cropAndSave() {
//   console.log("cropAndSave: Called");  // Debug Log

//   // Check for cropper initialization
//   if (!cropper) {
//     console.log("Error: Cropper not initialized.");
//     return;
//   }
//   // Log and check croppedCanvas
//   // console.log("Cropped Canvas: ", croppedCanvas);

//   // Remove any existing event listener before adding a new one
//   // cropBtn.removeEventListener('click', cropAndSave);
//   // cropBtn.addEventListener('click', cropAndSave);
//   if (cropper) {
//     const croppedCanvas = cropper.getCroppedCanvas({
//       width: 160,
//       height: 160,
//     });
//     if (!croppedCanvas) {
//       console.log("Error: Cropped canvas is null. Make sure crop box is set.");
//       return;
//     }
//     // if (thumbnailToReplace) {
//     //   thumbnailToReplace.src = croppedCanvas.toDataURL();
//     //   thumbnailToReplace = null;
//     // } else {
//     //   const thumbnail = document.createElement('img');
//     //   thumbnail.src = croppedCanvas.toDataURL();
//     //   thumbnail.width = 100;
//     //   thumbnail.addEventListener('click', function () {
//     croppedCanvas.toBlob(function (blob) {
//       const thumbnail = document.createElement('img');
//       thumbnail.src = croppedCanvas.toDataURL();
//       thumbnail.width = 100;

//       // Change 1: Assign index as data attribute
//       const index = savedImages.length;

//       thumbnail.addEventListener('click', function () {
//         // Change 2: Retrieve index from data attribute

//         console.log("Thumbnail: Click event fired");  // Debug Log
//         // console.log("Index of clicked thumbnail: ", index); // Debug Log
//         console.log("Corresponding Full Image Source: ", savedFullImages[index]); // Debug Log
//         const fullQualityImageSrc = savedFullImages[index];
//         const img = new Image();
//         img.src = fullQualityImageSrc;
//         img.onload = function () {
//           start(img);
//         };
//       });
//       savedImages.push(blob);
//       toggleSaveImageText();
//       toggleVisibility();
//       savedFullImages.push(croppedCanvas.toDataURL());
//       document.getElementById('thumbnails').appendChild(thumbnail);
//       // }
//     }, 'image/jpeg', 1);

//     // Remove from unsaved if exists
//     if (currentUnsavedIndex !== null) {
//       unsavedThumbnails.splice(currentUnsavedIndex, 1);
//       renderUnsavedThumbnails();
//     }
//     currentUnsavedIndex = null;

//     cropper.destroy();
//     buttonContainer.style.display = 'none';
//     canvas.style.display = 'none';
//   }
// }


// function saveImage() {
//   if (!cropper) {
//     console.error("Error: Cropper not initialized.");
//     return;
//   }

//   const croppedCanvas = cropper.getCroppedCanvas();

//   if (!croppedCanvas) {
//     console.error("Error: croppedCanvas is null");
//     return;
//   }

//   croppedCanvas.toBlob(function (blob) {
//     const thumbnail = document.createElement('img');
//     thumbnail.src = croppedCanvas.toDataURL();
//     thumbnail.width = 100;

//     // Attach index as a data attribute to the thumbnail
//     thumbnail.dataset.index = savedImages.length;

//     thumbnail.addEventListener('click', function () {
//       const index = parseInt(this.dataset.index);  // Retrieve index from data attribute
//       const fullQualityImageSrc = savedFullImages[index];
//       const img = new Image();
//       img.src = fullQualityImageSrc;
//       img.onload = function () {
//         start(img);
//       };
//     });

//     savedImages.push(blob);
//     toggleSaveImageText();
//     toggleVisibility();
//     savedFullImages.push(croppedCanvas.toDataURL());
//     document.getElementById('thumbnails').appendChild(thumbnail);

//     if (currentUnsavedIndex !== null) {
//       unsavedThumbnails.splice(currentUnsavedIndex, 1);
//       renderUnsavedThumbnails();
//     }
//     currentUnsavedIndex = null;
//   }, 'image/jpeg', 1);

//   cropper.destroy();
//   buttonContainer.style.display = 'none';
//   canvas.style.display = 'none';
// }


// const canvasContainer = document.querySelector('.img-container');
// canvasContainer.addEventListener('dragover', function (e) {
//   e.preventDefault();
// });

// // Updated to handle multiple files
// canvasContainer.addEventListener('drop', function (e) {
//   e.preventDefault();
//   const files = e.dataTransfer.files;
//   const filesArray = Array.from(files);
//   if (savedImages.length + filesArray.length > max_photos) {
//     alert('Maximum number of photos reached.');
//     return;
//   }
//   Array.from(files).forEach(file => {
//     const reader = new FileReader();
//     reader.onload = function (e) {
//       const img = new Image();
//       img.src = e.target.result;
//       img.onload = function () {
//         // img.uniqueSrc = `${img.src}?timestamp=${new Date().getTime()}`; // Add uniqueSrc as a property to the image object
//         // unsavedThumbnails.push(img); // Store the actual image object
//         // renderUnsavedThumbnails();
//         const validation = validateImage(img);
//         if (!validation.isValid) {
//           alert(validation.error);
//           document.getElementById('image').value = '';
//           return;
//         }
//         unsavedThumbnails.push(img);
//         renderUnsavedThumbnails();
//         console.log('Image Loaded: Trying to auto-populate canvas'); // Debug Log
//         start(img, unsavedThumbnails.length - 1);  // Auto-populate canvas
//       };
//     };
//     reader.readAsDataURL(file);
//   });
// });

// function renderUnsavedThumbnails() {
//   const unsavedArea = document.getElementById('unsaved-thumbnails');
//   unsavedArea.innerHTML = '';
//   unsavedThumbnails.forEach((img, index) => { // 'img' is an image object
//     const thumbnail = new Image();
//     thumbnail.src = img.src;
//     thumbnail.width = 100;
//     thumbnail.style.border = '2px solid red'; // Red border for unsaved
//     thumbnail.addEventListener('click', function () {
//       const originalImage = unsavedThumbnails[index]; // Access the original full-quality image from unsavedThumbnails
//       // console.log('originalImage', originalImage)
//       start(originalImage, index); // Pass the actual original image object
//     });
//     unsavedArea.appendChild(thumbnail);
//   });
//   toggleVisibility();
// }



// window.addEventListener('DOMContentLoaded', function () {
//   const imageInput = document.getElementById('image');
//   imageInput.addEventListener('change', function (event) {
//     const file = event.target.files[0];
//     if (file && file.type.startsWith('image/')) {
//       // const reader = new FileReader();
//       // reader.onload = function (e) {
//       //   const img = new Image();
//       //   img.src = e.target.result;
//       //   img.onload = function () {
//       //     unsavedThumbnails.push(img.src);
//       //     renderUnsavedThumbnails(); // Moved here
//       //   };
//       // };
//       // reader.readAsDataURL(file);
//       console.log('events', event)
//       onImageUpload(event); // Call the function directly
//     } else {
//       console.log('No image file selected or wrong file type.');
//     }
//   });
// });

// function onImageUpload(event) {
//   const files = event.target.files;
//   const filesArray = Array.from(files);
//   if (savedImages.length + filesArray.length >= max_photos) {
//     alert('Maximum number of photos reached.');
//     return;
//   }
//   Array.from(files).forEach(file => {
//     const reader = new FileReader();

//     reader.onload = function (e) {
//       const img = new Image();
//       img.src = e.target.result;
//       img.onload = function () {
//         const validation = validateImage(img);
//         if (!validation.isValid) {
//           alert(validation.error);
//           document.getElementById('image').value = '';
//           return;
//         }
//         unsavedThumbnails.push(img);
//         renderUnsavedThumbnails();
//         console.log('Image Loaded: Trying to auto-populate canvas'); // Debug Log
//         start(img, unsavedThumbnails.length - 1);  // Auto-populate canvas
//       };
//     };
//   });
//   reader.readAsDataURL(file);
// }



// // Hook up the validation to your input change event
// const imageInput = document.getElementById('image');
// imageInput.addEventListener('change', onImageUpload);

// function toggleSaveImageText() {
//   // id="images-text"
//   // id="download-btn"
//   const imagesText = document.getElementById('images-text');
//   const downloadBtn = document.getElementById('download');
//   const thumbnailsArray = document.getElementById('thumbnails').children;
//   if (thumbnailsArray.length > 0) {
//     imagesText.style.display = 'block';
//     // downloadBtn.style.display = 'block';
//   } else {
//     imagesText.style.display = 'none';
//     downloadBtn.style.display = 'none';
//   }
// }

// function toggleVisibility() {
//   const newHasSavedImages = savedImages.length > 0;
//   const newHasUnsavedThumbnails = unsavedThumbnails.length > 0;

//   if (hasSavedImages !== newHasSavedImages) {
//     document.getElementById('images-text').style.display = newHasSavedImages ? 'block' : 'none';
//     document.getElementById('saved-thumbnails-text').style.display = newHasSavedImages ? 'block' : 'none';
//     hasSavedImages = newHasSavedImages;
//   }

//   if (hasUnsavedThumbnails !== newHasUnsavedThumbnails) {
//     document.getElementById('unsaved-thumbnails-text').style.display = newHasUnsavedThumbnails ? 'block' : 'none';
//     hasUnsavedThumbnails = newHasUnsavedThumbnails;
//   }
// }