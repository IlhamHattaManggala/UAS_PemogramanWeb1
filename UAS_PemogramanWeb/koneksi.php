<?php
// $host = 'localhost';
// $dbname = 'kantor_kelurahan';
// $username = 'root';
// $password = '';
$host = 'localhost';
$dbname = 'id21763311_kantor_kelurahan';
$username = 'id21763311_root';
$password = 'Root123.';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
