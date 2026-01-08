<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}

include '../database/config.php';

// Proses form tambah pengguna
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    if (!empty($nama) && !empty($username) && !empty($password) && !empty($role)) {
        $sql = "INSERT INTO pengguna (nama, username, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $nama, $username, $password, $role);
        if ($stmt->execute()) {
            header("Location: tambah_pengguna.php?success=1");
            exit;
        } else {
            $error = "Gagal menambahkan pengguna.";
        }
    } else {
        $error = "Semua field wajib diisi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Pengguna | Sismontek</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
/* Consistent admin form styles */
body{font-family:'Poppins',sans-serif;background:#f4f6fb;margin:0;padding:0}
.card{background:#fff;border-radius:14px;padding:24px;box-shadow:0 8px 20px rgba(0,0,0,.06);max-width:720px;margin:60px auto}
h2{margin-top:0;color:#1e40af;text-align:center;margin-bottom:20px}
label{font-weight:600;display:block;margin-bottom:6px;color:#334155}
input,select,textarea{width:100%;padding:10px;margin-bottom:14px;border:1px solid #d1d5db;border-radius:8px}
button{width:100%;background:#2563eb;color:#fff;border:none;padding:10px;border-radius:8px;cursor:pointer}
button:hover{background:#1e3a8a}
.success{background:#16a34a;color:#fff;padding:10px;border-radius:6px;text-align:center;margin-bottom:15px}
.error{background:#ef4444;color:#fff;padding:10px;border-radius:6px;text-align:center;margin-bottom:15px}
.back{display:block;text-align:center;margin-top:10px;color:#2563eb;font-weight:600;text-decoration:none}
@media(max-width:768px){.main{margin:0;padding:22px}}
</style>
</head>
<body>
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="main">
  <button class="menu" onclick="sidebar.classList.toggle('show')">☰</button>
 <div class="card">
    <h2>Tambah Pengguna Baru</h2>

    <?php if (isset($_GET['success'])): ?>
        <div class="success">✅ Pengguna berhasil ditambahkan!</div>
    <?php elseif (isset($error)): ?>
        <div class="error">❌ <?= $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Nama Lengkap</label>
        <input type="text" name="nama" placeholder="Masukkan nama pengguna" required>

        <label>Username</label>
        <input type="text" name="username" placeholder="Masukkan username" required>

        <label>Password</label>
        <input type="password" name="password" placeholder="Masukkan password" required>

        <label>Role</label>
        <select name="role" required>
            <option value="">-- Pilih Role --</option>
            <option value="admin">Admin</option>
            <option value="teknisi">Teknisi</option>
            <option value="manajer">Manajer</option>
        </select>

        <button type="submit">+ Tambah Pengguna</button>
    </form>

    <a href="dashboard.php" class="back">← Kembali ke Dashboard</a>
 </div>
</div>
</body>
</html>
