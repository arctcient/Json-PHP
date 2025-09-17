<?php
// --- PENGATURAN KONEKSI DATABASE (Sama seperti sebelumnya) ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Kelas";

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

// Hanya izinkan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response('error', 'Metode tidak diizinkan.', null, 405);
}

// --- MENERIMA DATA JSON DARI REQUEST BODY ---
$json_data = file_get_contents('php://input');
$data = json_decode($json_data);

// --- VALIDASI INPUT ---
if (is_null($data) || !isset($data->nama_kelas) || !isset($data->status) || empty(trim($data->nama_kelas))) {
    send_response('error', 'Input tidak valid. Pastikan nama_kelas dan status diisi.', null, 400);
}

if (!in_array($data->status, ['open', 'close'])) {
    send_response('error', 'Status hanya boleh "open" atau "close".', null, 400);
}

// --- PROSES SIMPAN KE DATABASE ---
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Koneksi database gagal: " . $conn->connect_error);
    }

    // Gunakan PREPARED STATEMENT untuk keamanan (mencegah SQL Injection)
    $stmt = $conn->prepare("INSERT INTO informasi_kelas (nama_kelas, status) VALUES (?, ?)");

    // 'ss' berarti kita mengirim dua parameter bertipe String
    $stmt->bind_param("ss", $data->nama_kelas, $data->status);

    if ($stmt->execute()) {
        $new_id = $stmt->insert_id; // Ambil ID dari data yang baru dimasukkan
        send_response('success', 'Data berhasil ditambahkan.', ['id' => $new_id]);
    } else {
        throw new Exception("Gagal menyimpan data ke database.");
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    send_response('error', $e->getMessage(), null, 500);
}
