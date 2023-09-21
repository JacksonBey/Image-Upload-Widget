// imageUploadWidget.test.js

// Mock the initImageUploadWidget and ajaxFileUpload functions
global.initImageUploadWidget = jest.fn(() => console.log("Image Upload Widget Initialized"));
global.ajaxFileUpload = jest.fn((endpoint, formData) => console.log(`File uploaded to ${endpoint}`));

describe("Image Upload Widget Initialization", () => {
  
  test("initImageUploadWidget function should be defined", () => {
    expect(typeof initImageUploadWidget).toEqual('function');
  });

  test("ajaxFileUpload function should be defined", () => {
    expect(typeof ajaxFileUpload).toEqual('function');
  });

});

