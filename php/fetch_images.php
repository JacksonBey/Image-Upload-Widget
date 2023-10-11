<?php
  header('Content-Type: application/json');

  $file_path = $_GET['path'];
  $files = [];

  if (is_dir($file_path)) {
    $dir = new DirectoryIterator($file_path);
    foreach ($dir as $fileinfo) {
      if (!$fileinfo->isDot()) {
        $fileData = [
          'path' => $file_path . '/' . $fileinfo->getFilename(),
          'name' => $fileinfo->getFilename()
        ];
        array_push($files, $fileData);
      }
    }
  }

  echo json_encode($files);
?>
