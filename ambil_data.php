<?php
require_once 'connection/koneksi.php';

// Mengatur header agar browser tahu bahwa ini adalah response JSON
header('Content-Type: application/json');

// Fungsi untuk mengirim response JSON dan menghentikan script
function send_json_response($data, $http_code = 200)
{
    http_response_code($http_code);
    // Kita kembalikan formatnya sesuai permintaan
    $response = [
        "table" => "informasi_kelas",
        "data" => $data
    ];
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

// --- KONEKSI KE DATABASE ---
$conn = connect();
if ($conn->connect_error) {
    send_json_response(['error' => "Koneksi gagal: " . $conn->connect_error], 500);
}

// --- LOGIKA UTAMA: Cek apakah ada parameter ID di URL ---
if (isset($_GET['id']) && !empty($_GET['id'])) {
    // --- JIKA ADA ID (GET BY ID) ---
    $id = $_GET['id'];

    // Gunakan PREPARED STATEMENT untuk keamanan
    $stmt = $conn->prepare("SELECT id, nama_kelas, status FROM informasi_kelas WHERE id = ?");
    $stmt->bind_param("i", $id); // 'i' untuk tipe data Integer
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        send_json_response($data); // Kirim satu objek data
    } else {
        // Jika data tidak ditemukan
        send_json_response(['error' => "Data dengan ID " . $id . " tidak ditemukan."], 404);
    }
    $stmt->close();
} else {
    // --- JIKA TIDAK ADA ID (GET ALL) ---
    $sql = "SELECT id, nama_kelas, status FROM informasi_kelas";
    $result = $conn->query($sql);

    $all_data = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $all_data[] = $row;
        }
    }
    send_json_response($all_data); // Kirim array data
}

$conn->close();
