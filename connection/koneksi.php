<?php
// Fungsi untuk membuat koneksi baru ke database
function connect()
{
    // --- PENGATURAN KONEKSI DATABASE ---
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "Kelas";

    // --- MEMBUAT KONEKSI ---
    $conn = new mysqli($servername, $username, $password, $dbname);

    // --- CEK KONEKSI ---
    if ($conn->connect_error) {
        // Jika koneksi gagal, hentikan script dan tampilkan error dalam format JSON
        http_response_code(500); // Internal Server Error
        echo json_encode([
            'status' => 'error',
            'message' => 'Koneksi database gagal: ' . $conn->connect_error
        ]);
        exit;
    }

    return $conn;
}
