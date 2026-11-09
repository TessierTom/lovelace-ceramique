<?php
// Test : renvoie exactement ce que php://input contient
header('Content-Type: application/json; charset=utf-8');
$raw = file_get_contents('php://input');
echo json_encode([
    'methode'       => $_SERVER['REQUEST_METHOD'],
    'content_type'  => $_SERVER['CONTENT_TYPE'] ?? 'absent',
    'content_length'=> $_SERVER['CONTENT_LENGTH'] ?? 'absent',
    'raw_type'      => gettype($raw),
    'raw_val'       => $raw === false ? 'FALSE' : $raw,
    'post_keys'     => array_keys($_POST),
]);