<?php
session_start();
include '../database/config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'teknisi') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id_jadwal'])) {
    header("Location: dashboard.teknisi.php");
    exit;
}

$id_jadwal = $_GET['id_jadwal'];

// Ambil laporan
$stmt = $conn->prepare("SELECT * FROM laporan WHERE id_jadwal = ?");
$stmt->bind_param("i", $id_jadwal);
$stmt->execute();
$laporan = $stmt->get_result()->fetch_assoc();

if (!$laporan) {
    echo "<script>alert('Laporan tidak ditemukan'); window.location='dashboard.teknisi.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Laporan | Sismontek</title>

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #3f72af, #dbe2ef);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0;
}
.container {
    background: white;
    width: 100%;
    max-width: 420px;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
h2 {
    text-align: center;
    color: #3f72af;
    margin-bottom: 20px;
}
label {
    font-weight: 600;
    margin-top: 12px;
    display: block;
}
textarea, input[type="file"] {
    width: 100%;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
    margin-top: 6px;
}
img {
    width: 100%;
    margin-top: 10px;
    border-radius: 10px;
}
button {
    width: 100%;
    margin-top: 18px;
    background: #3f72af;
    color: white;
    border: none;
    padding: 12px;
    font-size: 15px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
}
button:hover {
    background: #2b5d9c;
}
</style>
</head>

<body>
<div class="container">
    <h2>✏️ Edit Laporan</h2>

    <form action="proses_laporan.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id_laporan" value="<?= $laporan['id_laporan']; ?>">
        <input type="hidden" name="id_jadwal" value="<?= $id_jadwal; ?>">

        <label>Kendala / Catatan</label>
        <textarea name="kendala" rows="4" required><?= htmlspecialchars($laporan['kendala']); ?></textarea>

        <label>Foto Bukti Saat Ini</label>
        <img src="<?= $laporan['foto_bukti']; ?>" alt="Foto Bukti">

        <label>Ganti Foto (opsional)</label>
        <input type="file" name="foto" accept="image/*">

        <button type="submit" name="update">Update Laporan</button>
    </form>
</div>
</body>
</html>
