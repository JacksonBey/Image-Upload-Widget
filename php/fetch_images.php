<?php
  header('Content-Type: application/json');

  $file_path = $_GET['path'];
  $files = [];

  if (is_dir($file_path)) {
    $dir = new DirectoryIterator($file_path);
    foreach ($dir as $fileinfo) {
      if (!$fileinfo->isDot()) {
        array_push($files, $file_path . '/' . $fileinfo->getFilename());
      }
    }
  }

  echo json_encode($files);
?>
