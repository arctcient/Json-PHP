<?php
function connect()
{
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "Kelas";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Koneksi database gagal: ' . $conn->connect_error
        ]);
        exit;
    }

    return $conn;
}
