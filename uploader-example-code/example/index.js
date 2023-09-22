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
      img.addEventListener('click', function() {
        openModal();
      });

      // // Add rotation button for each image
      // let rotateBtn = document.createElement('button');
      // rotateBtn.innerHTML = 'Rotate';
      // rotateBtn.onclick = function() {
      //   rotateImage(img);
      // };
      // document.getElementById('thumbnails').appendChild(rotateBtn);

      // // Add cropping button for each image (For demonstration)
      // let cropBtn = document.createElement('button');
      // cropBtn.innerHTML = 'Crop';
      // cropBtn.onclick = function() {
      //   cropImage(img);
      // };
      // document.getElementById('thumbnails').appendChild(cropBtn);

      // // Open editor modal and display image
      // const canvas = document.getElementById('editorCanvas');
      // const ctx = canvas.getContext('2d');
      // canvas.width = img.width;
      // canvas.height = img.height;
      // img.onload = function() {
      //   ctx.drawImage(img, 0, 0, img.width, img.height);
      // };
      // openModal();
    };
  });

}

// import Cropper from 'cropperjs';

// const image = document.getElementById('image');
// const cropper = new Cropper(image, {
//   aspectRatio: 16 / 9,
//   crop(event) {
//     console.log(event.detail.x);
//     console.log(event.detail.y);
//     console.log(event.detail.width);
//     console.log(event.detail.height);
//     console.log(event.detail.rotate);
//     console.log(event.detail.scaleX);
//     console.log(event.detail.scaleY);
//   },
// });