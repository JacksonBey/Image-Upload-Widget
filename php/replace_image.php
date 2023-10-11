<?php
// ReplaceImage.php
header('Content-Type: application/json');

$response = ["success" => false, "message" => "Unknown error"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file_path = $_POST['file_path'];
    $file_name = $_POST['file_name'];
    $blob = $_POST['image_data']; // Base64 encoded image data
    
    $decoded_image = base64_decode(str_replace('data:image/jpeg;base64,', '', $blob));
    $full_path = $file_path . '/' . $file_name;

    if (file_put_contents($full_path, $decoded_image)) {
        $response["success"] = true;
        $response["message"] = "Image replaced successfully.";
    } else {
        $response["message"] = "Failed to replace image.";
    }
}

echo json_encode($response);
?>
