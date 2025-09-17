<?php
require_once 'connection/koneksi.php';

// Set header sebagai JSON
header('Content-Type: application/json');

// Fungsi untuk mengirim response JSON dan menghentikan script
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

// Hanya izinkan metode POST (atau PUT, tapi POST lebih sederhana untuk form HTML)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response('error', 'Metode tidak diizinkan.', null, 405);
}

// --- MENERIMA DATA JSON DARI REQUEST BODY ---
$json_data = file_get_contents('php://input');
$data = json_decode($json_data);

// --- VALIDASI INPUT ---
if (is_null($data) || !isset($data->id) || !isset($data->nama_kelas) || !isset($data->status) || empty(trim($data->nama_kelas))) {
    send_response('error', 'Input tidak valid. Pastikan id, nama_kelas, dan status diisi.', null, 400);
}

if (!in_array($data->status, ['open', 'close'])) {
    send_response('error', 'Status hanya boleh "open" atau "close".', null, 400);
}

// --- PROSES UPDATE KE DATABASE ---
try {
    $conn = connect();
    if ($conn->connect_error) {
        throw new Exception("Koneksi database gagal: " . $conn->connect_error);
    }

    // Gunakan PREPARED STATEMENT untuk keamanan
    $stmt = $conn->prepare("UPDATE informasi_kelas SET nama_kelas = ?, status = ? WHERE id = ?");

    // 'ssi' berarti String, String, Integer
    $stmt->bind_param("ssi", $data->nama_kelas, $data->status, $data->id);

    if ($stmt->execute()) {
        // Cek apakah ada baris yang benar-benar terpengaruh (berubah)
        if ($stmt->affected_rows > 0) {
            send_response('success', 'Data berhasil diperbarui.');
        } else {
            send_response('info', 'Tidak ada perubahan pada data atau data tidak ditemukan.');
        }
    } else {
        throw new Exception("Gagal memperbarui data di database.");
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    send_response('error', $e->getMessage(), null, 500);
}
