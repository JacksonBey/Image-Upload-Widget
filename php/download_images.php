<?php
function countFilesInDirectory($dir) {
    $files = glob($dir . "*");
    return count($files);
}

function generateUniqueFilename($filePath, $fileName) {
    $uniqueName = $fileName;
    $count = 1;

    while (file_exists($filePath . $uniqueName)) {
        $pathInfo = pathinfo($fileName);
        $uniqueName = $pathInfo['filename'] . '-' . $count . '.' . $pathInfo['extension'];
        $count++;
    }

    return $uniqueName;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate JSON payload
    if (!isset($data['images'], $data['path'], $data['max_photos'], $data['fileNames'])) {
        echo json_encode(["status" => "invalid_payload"]);
        exit;
    }

    $images = $data['images'];
    $fileNames = $data['fileNames'];
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

    foreach ($images as $index => $imageData) {
        $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
        $imageData = str_replace(' ', '+', $imageData);
        $data = base64_decode($imageData);
        $uniqueFile = generateUniqueFilename($filePath, $fileNames[$index] ?? "image{$index}.jpg");
        $file = $filePath . $uniqueFile;
        file_put_contents($file, $data);
    }

    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "invalid_request"]);
}
?>
