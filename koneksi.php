<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "marketplace";

// Buat koneksi
$conn = new mysqli($host, $username, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Cek apakah sudah ada user default
$check = "SELECT * FROM users WHERE email = 'admin@admin.com'";
$result = $conn->query($check);

if ($result->num_rows == 0) {
    // Membuat user default
    $default_nama = "admin";
    $default_email = "admin@admin.com";
    $default_password = "admin123"; // Dalam praktik nyata, password harus di-hash
    $default_nomor_telepon = "081234567890";
    $default_alamat = "Admin Address";

    $sql = "INSERT INTO users (nama, email, password, nomor_telepon, alamat) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssss",
        $default_nama,
        $default_email,
        $default_password,
        $default_nomor_telepon,
        $default_alamat
    );

    if (!$stmt->execute()) {
        die("Error inserting default user: " . $stmt->error);
    }
}
