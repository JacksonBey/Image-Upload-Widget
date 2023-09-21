// dragAndDrop.test.js
global.TextEncoder = require("util").TextEncoder;
global.TextDecoder = require("util").TextDecoder;
const { handleDragEnter, handleDragLeave, handleDragOver, handleDrop } = require('../index.js');

const fs = require('fs');
const { JSDOM } = require('jsdom');

describe("Drag-and-Drop Functionality", () => {

  let dropZone;
  let dom;
  let container;

  beforeAll((done) => {
    // Read the HTML file
    fs.readFile('./index.html', 'utf8', (err, data) => {
      if (err) {
        done(err);
        return;
      }

      // Create a JSDOM instance with the HTML
      dom = new JSDOM(data);
      
      // Set up the document and window objects
      global.document = dom.window.document;
      global.window = dom.window;

      container = document.getElementById("dropZone");

      done();
    });
  });



  // beforeEach(() => {
  //   // Create a dummy drop zone element
  //   dropZone = document.createElement('div');
  //   dropZone.id = 'dropZone';
  //   document.body.appendChild(dropZone);
  // });

  afterEach(() => {
    // Clean up after each test
    document.body.removeChild(dropZone);
  });

  test("handleDragEnter should add 'dragging' class", () => {
    const event = new Event('dragenter', { bubbles: true });
    event.currentTarget = dropZone;  // Manually set currentTarget
    handleDragEnter(event);
    expect(dropZone.classList.contains('dragging')).toBe(true);
  });
  
  test("handleDragLeave should remove 'dragging' class", () => {
    const event = new Event('dragleave', { bubbles: true });
    event.currentTarget = dropZone;  // Manually set currentTarget
    handleDragLeave(event);
    expect(dropZone.classList.contains('dragging')).toBe(false);
  });
  
  test("handleDrop should process files and remove 'dragging' class", () => {
    const event = new Event('drop', { bubbles: true });
    event.preventDefault = jest.fn();
    event.dataTransfer = { files: ['file1.jpg', 'file2.jpg'] };
    event.currentTarget = dropZone;  // Manually set currentTarget
    handleDrop(event);
    expect(event.preventDefault).toHaveBeenCalled();
    expect(dropZone.classList.contains('dragging')).toBe(false);
  });
  

});
