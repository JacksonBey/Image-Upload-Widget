<?php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  header('Content-Type: application/json');

  $payload = json_decode(file_get_contents("php://input"), true);
  $file_path = $payload['path'];

  if (file_exists($file_path)) {
    if (unlink($file_path)) {
      echo json_encode(['status' => 'success', 'message' => 'File deleted successfully']);
    } else {
      echo json_encode(['status' => 'error', 'message' => 'Could not delete file']);
    }
  } else {
    echo json_encode(['status' => 'error', 'message' => 'File does not exist']);
  }
?>
