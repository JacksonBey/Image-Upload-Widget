<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $file_path = $_POST['file_path'];
    $file_name = $_POST['file_name'];
    $image_data = $_FILES['image_data']; // Should now be captured as a file
    
    $full_path = $file_path . '/' . $file_name;

    // Validate all required fields
    if (isset($file_path, $file_name, $image_data) && file_exists($image_data['tmp_name'])) {
        
        if (!file_exists($full_path)) {
            $response["message"] = "File to replace does not exist.";
            echo json_encode($response);
            exit;
        }

        if (move_uploaded_file($image_data['tmp_name'], $full_path)) {
            $response["success"] = true;
            $response["message"] = "Image replaced successfully.";
        } else {
            $response["message"] = "Failed to replace image.";
        }
    } else {
        $response["message"] = "Incomplete data. Please send file_path, file_name, and image_data.";
    }
} else {
    $response["message"] = "Invalid request method.";
}

echo json_encode($response);

?>
