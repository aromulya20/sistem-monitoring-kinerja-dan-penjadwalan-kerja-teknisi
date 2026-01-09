<?php
session_start();
include '../database/config.php';

// Redirect jika belum login
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Redirect jika id laporan tidak ada
if (!isset($_GET['id'])) {
    header("Location: laporan.php");
    exit;
}

$id_laporan = (int) $_GET['id'];

// Prepared statement
$stmt = $conn->prepare("
    SELECT 
        l.id_laporan,
        l.id_jadwal,
        l.tanggal_laporan,
        l.kendala,
        l.foto_bukti,
        j.status,
        j.deskripsi_pekerjaan,
        p.nama_pelanggan,
        p.alamat,
        p.nomer_telepon,
        p.email,
        u.nama AS nama_teknisi
    FROM laporan l
    JOIN jadwal j ON l.id_jadwal = j.id_jadwal
    JOIN pelanggan p ON j.id_pelanggan = p.id_pelanggan
    JOIN teknisi t ON j.id_teknisi = t.id_teknisi
    JOIN pengguna u ON t.id_pengguna = u.id_pengguna
    WHERE l.id_laporan = ?
");
$stmt->bind_param("i", $id_laporan);
$stmt->execute();
$result = $stmt->get_result();

$data = $result->fetch_assoc();

if (!$data) {
    echo "<div style='text-align:center;margin-top:50px'>
            <h2>Data laporan tidak ditemukan</h2>
            <a href='laporan.php'>Kembali</a>
          </div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Laporan | SISMONTEK</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-100 via-sky-100 to-indigo-100 font-sans">

<div class="max-w-5xl mx-auto p-6">

    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-extrabold text-blue-900 mb-2">📋 Detail Laporan Teknisi</h1>
        <p class="text-gray-600">Informasi lengkap pekerjaan dan bukti teknisi</p>
    </div>

    <!-- Info -->
    <div class="grid gap-6 md:grid-cols-2">

        <!-- Pelanggan -->
        <div class="bg-white shadow-xl rounded-2xl p-6">
            <h2 class="text-lg font-bold text-blue-800 mb-4">👤 Informasi Pelanggan</h2>
            <div class="space-y-2 text-gray-700">
                <p><b>Nama:</b> <?= htmlspecialchars($data['nama_pelanggan'] ?? '-'); ?></p>
                <p><b>Email:</b> <?= htmlspecialchars($data['email'] ?? '-'); ?></p>
                <p><b>No. Telepon:</b> <?= htmlspecialchars($data['nomer_telepon'] ?? '-'); ?></p>
                <p><b>Alamat:</b> <?= htmlspecialchars($data['alamat'] ?? '-'); ?></p>
            </div>
        </div>

        <!-- Teknisi -->
        <div class="bg-white shadow-xl rounded-2xl p-6">
            <h2 class="text-lg font-bold text-blue-800 mb-4">🧑‍🔧 Informasi Teknisi</h2>
            <p><b>Nama Teknisi:</b> <?= htmlspecialchars($data['nama_teknisi'] ?? '-'); ?></p>

            <p class="mt-3">
                <b>Status:</b>
                <span class="px-3 py-1 rounded-full text-sm font-semibold
                    <?= ($data['status'] ?? '') == 'selesai'
                        ? 'bg-green-100 text-green-700'
                        : 'bg-yellow-100 text-yellow-700'; ?>">
                    <?= ucfirst($data['status'] ?? 'tidak diketahui'); ?>
                </span>
            </p>
        </div>
    </div>

    <!-- Detail -->
    <div class="bg-white shadow-xl rounded-2xl p-6 mt-6">
        <h2 class="text-lg font-bold text-blue-800 mb-4">📝 Detail Pekerjaan</h2>

        <p class="mb-3">
            <b>Deskripsi:</b><br>
            <?= nl2br(htmlspecialchars($data['deskripsi_pekerjaan'] ?? '-')); ?>
        </p>

        <p><b>Tanggal Laporan:</b> <?= htmlspecialchars($data['tanggal_laporan'] ?? '-'); ?></p>

        <?php if (!empty($data['kendala'])): ?>
        <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
            <b>Kendala:</b><br>
            <?= nl2br(htmlspecialchars($data['kendala'])); ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Foto -->
    <?php if (!empty($data['foto_bukti']) && file_exists('../uploads/'.$data['foto_bukti'])): ?>
    <div class="bg-white shadow-xl rounded-2xl p-6 mt-6">
        <h2 class="text-lg font-bold text-blue-800 mb-4">📷 Foto Bukti</h2>
        <img src="../uploads/<?= htmlspecialchars($data['foto_bukti']); ?>"
             class="w-full max-h-[400px] object-cover rounded-xl hover:scale-105 transition">
    </div>
    <?php endif; ?>

    <!-- Back -->
    <div class="text-center mt-8">
        <a href="laporan.php"
           class="inline-block bg-blue-800 text-white px-6 py-3 rounded-xl font-semibold hover:bg-blue-900 transition">
           ⬅ Kembali ke Laporan
        </a>
    </div>

</div>
</body>
</html>
