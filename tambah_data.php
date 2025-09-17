<?php
require_once 'connection/koneksi.php';

header('Content-Type: application/json');

function send_response($status, $message, $data = null, $http_code = 200)
{
    http_response_code($http_code);
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response('error', 'Metode tidak diizinkan.', null, 405);
}

$json_data = file_get_contents('php://input');
$data = json_decode($json_data);

if (is_null($data) || !isset($data->nama_kelas) || !isset($data->status) || empty(trim($data->nama_kelas))) {
    send_response('error', 'Input tidak valid. Pastikan nama_kelas dan status diisi.', null, 400);
}

if (!in_array($data->status, ['open', 'close'])) {
    send_response('error', 'Status hanya boleh "open" atau "close".', null, 400);
}

try {
    $conn = connect();
    if ($conn->connect_error) {
        throw new Exception("Koneksi database gagal: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("INSERT INTO informasi_kelas (nama_kelas, status) VALUES (?, ?)");

    $stmt->bind_param("ss", $data->nama_kelas, $data->status);

    if ($stmt->execute()) {
        $new_id = $stmt->insert_id; 
        send_response('success', 'Data berhasil ditambahkan.', ['id' => $new_id]);
    } else {
        throw new Exception("Gagal menyimpan data ke database.");
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    send_response('error', $e->getMessage(), null, 500);
}
