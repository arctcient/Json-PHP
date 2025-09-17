<?php
require_once 'connection/koneksi.php';

header('Content-Type: application/json');

function send_response($status, $message, $http_code = 200)
{
    http_response_code($http_code);
    echo json_encode([
        'status' => $status,
        'message' => $message
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response('error', 'Metode tidak diizinkan.', 405);
}

$json_data = file_get_contents('php://input');
$data = json_decode($json_data);

if (is_null($data) || !isset($data->id) || !is_numeric($data->id)) {
    send_response('error', 'Input tidak valid. Pastikan ID diisi dengan benar.', 400);
}

try {
    $conn = connect();
    if ($conn->connect_error) {
        throw new Exception("Koneksi database gagal: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("DELETE FROM informasi_kelas WHERE id = ?");

    $stmt->bind_param("i", $data->id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            send_response('success', 'Data berhasil dihapus.');
        } else {
            send_response('error', 'Data dengan ID tersebut tidak ditemukan.', 404);
        }
    } else {
        throw new Exception("Gagal menghapus data dari database.");
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    send_response('error', $e->getMessage(), 500);
}
