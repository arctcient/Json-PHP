<?php
// --- PENGATURAN KONEKSI DATABASE ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Kelas";

// Set header sebagai JSON
header('Content-Type: application/json');

// Fungsi untuk mengirim response JSON dan menghentikan script
function send_response($status, $message, $http_code = 200)
{
    http_response_code($http_code);
    echo json_encode([
        'status' => $status,
        'message' => $message
    ]);
    exit;
}

// Hanya izinkan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response('error', 'Metode tidak diizinkan.', 405);
}

// --- MENERIMA DATA JSON DARI REQUEST BODY ---
$json_data = file_get_contents('php://input');
$data = json_decode($json_data);

// --- VALIDASI INPUT ---
if (is_null($data) || !isset($data->id) || !is_numeric($data->id)) {
    send_response('error', 'Input tidak valid. Pastikan ID diisi dengan benar.', 400);
}

// --- PROSES HAPUS DARI DATABASE ---
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Koneksi database gagal: " . $conn->connect_error);
    }

    // Gunakan PREPARED STATEMENT untuk keamanan
    $stmt = $conn->prepare("DELETE FROM informasi_kelas WHERE id = ?");

    // 'i' untuk tipe data Integer
    $stmt->bind_param("i", $data->id);

    if ($stmt->execute()) {
        // Cek apakah ada baris yang benar-benar terhapus
        if ($stmt->affected_rows > 0) {
            send_response('success', 'Data berhasil dihapus.');
        } else {
            // Ini terjadi jika ID yang dikirim tidak ada di database
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
