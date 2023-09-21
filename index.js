
// Initialize the Image Upload Widget
function initImageUploadWidget() {
  const dropZone = document.getElementById("dropZone");

  dropZone.addEventListener("dragenter", handleDragEnter, false);
  dropZone.addEventListener("dragleave", handleDragLeave, false);
  dropZone.addEventListener("dragover", handleDragOver, false);
  dropZone.addEventListener("drop", handleDrop, false);
}

function handleDragEnter(e) {
  console.log("handleDragEnter e:", e);
  console.log("e.currentTarget:", e.currentTarget);
  e.preventDefault();
  e.currentTarget.classList.add("dragging");
}

function handleDragLeave(e) {
  console.log("handleDragLeave e:", e);
  console.log("e.currentTarget:", e.currentTarget);
  e.preventDefault();
  e.currentTarget.classList.remove("dragging");
}

function handleDragOver(e) {
  console.log("handleDragOver e:", e);
  console.log("e.currentTarget:", e.currentTarget);
  e.preventDefault();
}

function handleDrop(e) {
  console.log("handleDragDrop e:", e);
  console.log("e.currentTarget:", e.currentTarget);
  e.preventDefault();
  e.currentTarget.classList.remove("dragging");
  
  // Further logic to process files
  // const files = e.dataTransfer.files;
}


exports.handleDragEnter = handleDragEnter;
exports.handleDragLeave = handleDragLeave;
exports.handleDragOver = handleDragOver;
exports.handleDrop = handleDrop;