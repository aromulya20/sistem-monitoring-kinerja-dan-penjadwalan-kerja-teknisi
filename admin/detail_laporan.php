<?php
session_start();
include '../database/config.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: laporan.php");
    exit;
}

$id_laporan = $_GET['id'];

$sql = "
    SELECT 
        l.*, 
        p.nama_pelanggan, p.alamat, p.nomer_telepon, p.email,
        u.nama AS nama_teknisi, 
        j.deskripsi_pekerjaan, j.status
    FROM laporan l
    JOIN jadwal j ON l.id_jadwal = j.id_jadwal
    JOIN pelanggan p ON j.id_pelanggan = p.id_pelanggan
    JOIN teknisi t ON j.id_teknisi = t.id_teknisi
    JOIN pengguna u ON t.id_pengguna = u.id_pengguna
    WHERE l.id_laporan = '$id_laporan'
";
$result = $conn->query($sql);
if (!$result || $result->num_rows == 0) {
    echo "Data tidak ditemukan.";
    exit;
}
$data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Detail Laporan | Sismontek</title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background-color: #f4f6f9;
    padding: 40px;
}
.container {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    max-width: 700px;
    margin: auto;
}
h2 {
    color: #3f72af;
    text-align: center;
    margin-bottom: 25px;
}
.detail-box {
    margin-bottom: 15px;
}
.detail-box strong {
    color: #112d4e;
}
img {
    width: 100%;
    max-height: 350px;
    border-radius: 10px;
    object-fit: cover;
    margin-top: 10px;
}
a.button {
    display: inline-block;
    margin-top: 20px;
    text-decoration: none;
    padding: 10px 15px;
    background-color: #3f72af;
    color: white;
    border-radius: 8px;
}
a.button:hover { background-color: #2e5c8a; }
</style>
</head>
<body>
<div class="container">
    <h2>📋 Detail Laporan Teknisi</h2>

    <div class="detail-box"><strong>Nama Pelanggan:</strong> <?= htmlspecialchars($data['nama_pelanggan']); ?></div>
    <div class="detail-box"><strong>Alamat:</strong> <?= htmlspecialchars($data['alamat']); ?></div>
    <div class="detail-box"><strong>No. Telepon:</strong> <?= htmlspecialchars($data['nomer_telepon']); ?></div>
    <div class="detail-box"><strong>Email:</strong> <?= htmlspecialchars($data['email']); ?></div>
    <hr>
    <div class="detail-box"><strong>Nama Teknisi:</strong> <?= htmlspecialchars($data['nama_teknisi']); ?></div>
    <div class="detail-box"><strong>Deskripsi Pekerjaan:</strong> <?= htmlspecialchars($data['deskripsi_pekerjaan']); ?></div>
    <div class="detail-box"><strong>Status:</strong> <?= ucfirst($data['status']); ?></div>
    <div class="detail-box"><strong>Tanggal Laporan:</strong> <?= htmlspecialchars($data['tanggal_laporan']); ?></div>
    <div class="detail-box"><strong>Kendala:</strong><br><?= nl2br(htmlspecialchars($data['kendala'])); ?></div>

    <?php if (!empty($data['foto_bukti'])): ?>
        <div class="detail-box">
            <strong>Foto Bukti:</strong><br>
            <img src="../uploads/<?= htmlspecialchars($data['foto_bukti']); ?>" alt="Foto Bukti">
        </div>
    <?php endif; ?>

    <a href="laporan.php" class="button">⬅ Kembali</a>
</div>
</body>
</html>
