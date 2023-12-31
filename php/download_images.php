<?php
function countFilesInDirectory($dir) {
    $files = glob($dir . "*");
    return count($files);
}

function generateUniqueFilename($filePath, $fileName, $maxPhotos, $id) {
    $pathInfo = pathinfo($fileName);
    $extension = $pathInfo['extension'];
    
    if ($maxPhotos == 1) {
        return $id . '.' . $extension;
    } else {
        $count = 1;        
        while (file_exists($filePath . $count . '.' . $extension)) {
            $count++;
        }
        return $count . '.' . $extension;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate JSON payload
    if (!isset($data['images'], $data['path'], $data['max_photos'])) {
        echo json_encode(["status" => "invalid_payload"]);
        exit;
    }
    $images = $data['images'];
    $filePath = $data['path'];

    // Check if directory exists, create if not
    if (!is_dir($filePath)) {
        mkdir($filePath, 0777, true);
    }

    $maxPhotos = $data['max_photos'];

    $existingFilesCount = countFilesInDirectory($filePath);

    if ($existingFilesCount >= $maxPhotos) {
        echo json_encode(["status" => "limit_reached"]);
        exit;
    }
    
    $id = 12345;

    foreach ($images as $index => $imageData) {
        // $extension = 'jpg';  // Default to JPEG
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
            $extension = $matches[1];
        }
    
        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
        $imageData = str_replace(' ', '+', $imageData);
        $data = base64_decode($imageData);
    
        // Check if the image is a PNG; otherwise, convert it to JPEG
        if ($extension === 'png') {
            $uniqueFile = generateUniqueFilename($filePath, $fileNames[$index] ?? "image{$index}.png", $maxPhotos, $id);
        } else {
            $uniqueFile = generateUniqueFilename($filePath, $fileNames[$index] ?? "image{$index}.jpg", $maxPhotos, $id);
            $extension = 'jpg';
        }
    
        $file = $filePath . $uniqueFile;
        file_put_contents($file, $data);
    }
    
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "invalid_request"]);
}
?>

