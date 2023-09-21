// Initialize the Image Upload Widget
function initImageUploadWidget() {
  console.log("Image Upload Widget Initialized");

  // Register drag-and-drop event listeners
  const dropArea = document.getElementById('dropArea');
  dropArea.addEventListener('dragenter', handleDragEnter, false);
  dropArea.addEventListener('dragleave', handleDragLeave, false);
  dropArea.addEventListener('dragover', handleDragOver, false);
  dropArea.addEventListener('drop', handleDrop, false);
}

// Dummy function for API call to upload files, easily replaceable
function ajaxFileUpload(endpoint, formData) {
  console.log("File uploaded to", endpoint);
}

// Drag and Drop event handlers
function handleDragEnter(e) {
  e.preventDefault();
  console.log("Drag Enter");
}

function handleDragLeave(e) {
  e.preventDefault();
  console.log("Drag Leave");
}

function handleDragOver(e) {
  e.preventDefault();
  console.log("Drag Over");
}

let filesToUpload = [];

function handleDrop(e) {
  e.preventDefault();
  console.log("Drop");
  console.log('Data transfer:', e.dataTransfer.files);


  // File handling logic
  let files = e.dataTransfer.files;
  Array.from(files).forEach(file => {
    // Validation based on file type
    let fileType = file.type.split('/')[1];
    if (!['jpeg', 'jpg', 'png', 'gif', 'heic'].includes(fileType.toLowerCase())) {
      console.error('Unsupported file type');
      return;
    }

    // Add to global array for later upload
    filesToUpload.push(file);

    // File Preview as Thumbnail
    let reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onloadend = function() {
      let img = document.createElement('img');
      img.src = reader.result;
      img.width = 100;
      document.getElementById('thumbnails').appendChild(img);


      // Add rotation button for each image
      let rotateBtn = document.createElement('button');
      rotateBtn.innerHTML = 'Rotate';
      rotateBtn.onclick = function() {
        rotateImage(img);
      };
      document.getElementById('thumbnails').appendChild(rotateBtn);

      // Add cropping button for each image (For demonstration)
      let cropBtn = document.createElement('button');
      cropBtn.innerHTML = 'Crop';
      cropBtn.onclick = function() {
        cropImage(img);
      };
      document.getElementById('thumbnails').appendChild(cropBtn);

      // Open editor modal and display image
      const canvas = document.getElementById('editorCanvas');
      const ctx = canvas.getContext('2d');
      canvas.width = img.width;
      canvas.height = img.height;
      img.onload = function() {
        ctx.drawImage(img, 0, 0, img.width, img.height);
      };
      openModal();
    };
  });

}

// Rotate image function
function rotateImage(imgElement) {
  let canvas = document.createElement('canvas');
  let ctx = canvas.getContext('2d');
  canvas.width = imgElement.width;
  canvas.height = imgElement.height;
  ctx.rotate((90 * Math.PI) / 180);
  ctx.drawImage(imgElement, 0, -imgElement.height);
  imgElement.src = canvas.toDataURL();
}

// Crop image function (basic demonstration)
function cropImage(imgElement) {
  let canvas = document.createElement('canvas');
  let ctx = canvas.getContext('2d');
  canvas.width = imgElement.width / 2;
  canvas.height = imgElement.height / 2;
  ctx.drawImage(imgElement, 0, 0, canvas.width, canvas.height, 0, 0, canvas.width, canvas.height);
  imgElement.src = canvas.toDataURL();
}


// MODAL LOGIC
function openModal() {
  document.getElementById('editorModal').style.display = "block";
}

function closeModal() {
  document.getElementById('editorModal').style.display = "none";
}

// JavaScript function to rotate the image on the canvas
function rotateCanvas(canvas, angle) {
  const ctx = canvas.getContext('2d');
  const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  ctx.save();
  ctx.translate(canvas.width / 2, canvas.height / 2);
  ctx.rotate((angle * Math.PI) / 180);
  ctx.drawImage(imageData, -imageData.width / 2, -imageData.height / 2);
  ctx.restore();
}

// Event handlers for the rotation buttons
document.getElementById('rotateClockwise').addEventListener('click', function() {
  const canvas = document.getElementById('editorCanvas');
  rotateCanvas(canvas, 90);
});

document.getElementById('rotateCounterClockwise').addEventListener('click', function() {
  const canvas = document.getElementById('editorCanvas');
  rotateCanvas(canvas, -90);
});

function cropImage(canvas, cropX, cropY, cropWidth, cropHeight) {
  const ctx = canvas.getContext('2d');
  const imageData = ctx.getImageData(cropX, cropY, cropWidth, cropHeight);
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  canvas.width = cropWidth;
  canvas.height = cropHeight;
  ctx.putImageData(imageData, 0, 0);
}

// Event handler for the Crop button
document.getElementById('crop').addEventListener('click', function() {
  // Logic to display cropping UI goes here
  // ...
});

// Event handlers for OK and Cancel buttons
document.getElementById('cropOk').addEventListener('click', function() {
  // Logic to apply cropping goes here
  // ...
});

document.getElementById('cropCancel').addEventListener('click', function() {
  // Logic to cancel cropping goes here
  // ...
});





