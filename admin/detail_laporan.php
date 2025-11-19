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
        background: #eef2f7;
        padding: 40px;
    }

    .container {
        background: white;
        padding: 35px;
        border-radius: 14px;
        max-width: 900px;
        margin: auto;
        box-shadow: 0px 6px 18px rgba(0,0,0,0.08);
    }

    h2 {
        text-align: center;
        margin-bottom: 35px;
        color: #1f3c88;
        letter-spacing: 1px;
        font-size: 26px;
        font-weight: 600;
    }

    .section-title {
        font-size: 18px;
        margin-bottom: 10px;
        font-weight: 600;
        color: #1f3c88;
        border-left: 4px solid #3f72af;
        padding-left: 10px;
    }

    .info-box {
        background: #f8faff;
        border: 1px solid #d7e3fc;
        padding: 18px;
        border-radius: 10px;
        margin-bottom: 25px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .info-item strong {
        color: #112d4e;
    }

    .kendala-box {
        background: #fff6e6;
        padding: 15px;
        border-left: 4px solid #ffa502;
        border-radius: 10px;
        margin-top: 10px;
    }

    img {
        width: 100%;
        max-height: 380px;
        border-radius: 12px;
        object-fit: cover;
        margin-top: 10px;
        box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
    }

    .back-btn {
        display: inline-block;
        padding: 10px 18px;
        margin-top: 25px;
        background: #1f3c88;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-size: 14px;
        transition: 0.3s;
    }

    .back-btn:hover {
        background: #163276;
    }
</style>

</head>
<body>

<div class="container">

    <h2>📋 Detail Laporan Teknisi</h2>

    <!-- ======================== DATA PELANGGAN =========================== -->
    <div class="section-title">Informasi Pelanggan</div>
    <div class="info-box info-grid">
        <div class="info-item"><strong>Nama Pelanggan:</strong><br><?= htmlspecialchars($data['nama_pelanggan']); ?></div>
        <div class="info-item"><strong>Email:</strong><br><?= htmlspecialchars($data['email']); ?></div>
        <div class="info-item"><strong>No. Telepon:</strong><br><?= htmlspecialchars($data['nomer_telepon']); ?></div>
        <div class="info-item"><strong>Alamat:</strong><br><?= htmlspecialchars($data['alamat']); ?></div>
    </div>

    <!-- ======================== DATA TEKNISI =========================== -->
    <div class="section-title">Informasi Teknisi</div>
    <div class="info-box info-grid">
        <div class="info-item"><strong>Nama Teknisi:</strong><br><?= htmlspecialchars($data['nama_teknisi']); ?></div>
        <div class="info-item"><strong>Status Pekerjaan:</strong><br><?= ucfirst($data['status']); ?></div>
    </div>

    <!-- ======================== DATA PEKERJAAN =========================== -->
    <div class="section-title">Detail Pekerjaan</div>
    <div class="info-box">
        <strong>Deskripsi Pekerjaan:</strong><br>
        <?= nl2br(htmlspecialchars($data['deskripsi_pekerjaan'])); ?>

        <br><br>
        <strong>Tanggal Laporan:</strong><br><?= htmlspecialchars($data['tanggal_laporan']); ?>

        <br><br>
        <strong>Kendala:</strong>
        <div class="kendala-box">
            <?= nl2br(htmlspecialchars($data['kendala'])); ?>
        </div>
    </div>

    <!-- ======================== FOTO BUKTI =========================== -->
    <?php if (!empty($data['foto_bukti'])): ?>
        <div class="section-title">Foto Bukti</div>
        <img src="../uploads/<?= htmlspecialchars($data['foto_bukti']); ?>" alt="Foto Bukti">
    <?php endif; ?>

    <a href="laporan.php" class="back-btn">⬅ Kembali</a>
</div>

</body>
</html>