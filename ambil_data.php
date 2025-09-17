<?php
require_once 'connection/koneksi.php';

header('Content-Type: application/json');

function send_json_response($data, $http_code = 200)
{
    http_response_code($http_code);
    $response = [
        "table" => "informasi_kelas",
        "data" => $data
    ];
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

$conn = connect();
if ($conn->connect_error) {
    send_json_response(['error' => "Koneksi gagal: " . $conn->connect_error], 500);
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("SELECT id, nama_kelas, status FROM informasi_kelas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        send_json_response($data);
    } else {
        send_json_response(['error' => "Data dengan ID " . $id . " tidak ditemukan."], 404);
    }
    $stmt->close();
} else {
    $sql = "SELECT id, nama_kelas, status FROM informasi_kelas";
    $result = $conn->query($sql);

    $all_data = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $all_data[] = $row;
        }
    }
    send_json_response($all_data);
}

$conn->close();
