<?php
header('Content-Type: application/json');
$json = file_get_contents('php://input');
$data = json_decode($json, true);
echo json_encode(['test' => 'ok', 'input' => $json, 'parsed' => $data, 'method' => $_SERVER['REQUEST_METHOD']]);
