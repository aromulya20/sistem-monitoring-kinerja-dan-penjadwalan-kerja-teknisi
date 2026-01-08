<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}
include '../database/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    if ($nama && $username && $password && $role) {

        // 🔍 CEK USERNAME SUDAH ADA ATAU BELUM
        $cek = $conn->prepare("SELECT id_pengguna FROM pengguna WHERE username = ?");
        $cek->bind_param("s", $username);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            $error = "Username sudah digunakan, silakan pilih username lain";
        } else {

            //HASH PASSWORD 
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO pengguna (nama, username, password, role) VALUES (?,?,?,?)"
            );
            $stmt->bind_param("ssss", $nama, $username, $hashedPassword, $role);

            if ($stmt->execute()) {
                header("Location: tambah_pengguna.php?success=1");
                exit;
            } else {
                $error = "Gagal menyimpan data pengguna";
            }
        }
    } else {
        $error = "Semua field wajib diisi";
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Pengguna | SISMONTEK</title>

<link rel="stylesheet"
 href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
body{
 margin:0;
 font-family:Inter,system-ui,sans-serif;
 background:#f5f7fb;
 color:#0f172a;
}

/* ===== MAIN CONTENT ===== */
main{
 margin-left:260px;
 padding:36px 40px;
 min-height:100vh;
}

/* ===== HEADER ===== */
.page-title{
 display:flex;
 align-items:center;
 gap:14px;
 font-size:26px;
 font-weight:600;
 color:#1e40af;
 margin-bottom:28px;
}

/* ===== CARD ===== */
.card{
 background:#fff;
 border-radius:18px;
 padding:34px;
 width:100%;
 max-width:1200px;
 box-shadow:0 16px 32px rgba(0,0,0,.07);
}

/* ===== FORM ===== */
.form-grid{
 display:grid;
 grid-template-columns:repeat(2,1fr);
 gap:26px;
}

.form-group{
 display:flex;
 flex-direction:column;
}

.form-group.full{
 grid-column:1/3;
}

label{
 font-size:15px;
 font-weight:600;
 margin-bottom:8px;
 color:#334155;
}

input,select{
 padding:14px 16px;
 border-radius:12px;
 border:1px solid #cbd5e1;
 font-size:15px;
}

input:focus,select:focus{
 outline:none;
 border-color:#2563eb;
 box-shadow:0 0 0 3px rgba(37,99,235,.18);
}

/* ===== ACTIONS ===== */
.actions{
 margin-top:34px;
 display:flex;
 justify-content:flex-end;
 gap:16px;
}

.btn{
 padding:14px 26px;
 border-radius:999px;
 border:none;
 font-size:15px;
 font-weight:500;
 cursor:pointer;
 display:flex;
 align-items:center;
 gap:10px;
}

.btn-primary{
 background:#2563eb;
 color:#fff;
}

.btn-primary:hover{background:#1e40af}

.btn-secondary{
 background:#e5e7eb;
 color:#0f172a;
 text-decoration:none;
}

.btn-secondary:hover{background:#d1d5db}

/* ===== ALERT ===== */
.alert{
 padding:14px 18px;
 border-radius:12px;
 font-size:15px;
 margin-bottom:22px;
 display:flex;
 align-items:center;
 gap:12px;
}

.alert.success{
 background:rgba(22,163,74,.14);
 color:#15803d;
}

.alert.error{
 background:rgba(220,38,38,.14);
 color:#b91c1c;
}

/* ===== RESPONSIVE ===== */
@media(max-width:1024px){
 main{margin-left:0;padding:28px}
 .form-grid{grid-template-columns:1fr}
 .form-group.full{grid-column:1}
}
</style>
</head>

<body>

<?php include __DIR__ . '/sidebar.php'; ?>

<main>

<div class="page-title">
<i class="fa-solid fa-user-plus"></i>
Tambah Pengguna
</div>

<div class="card">

<?php if(isset($_GET['success'])): ?>
<div class="alert success">
<i class="fa-solid fa-circle-check"></i>
Pengguna berhasil ditambahkan
</div>
<?php elseif(isset($error)): ?>
<div class="alert error">
<i class="fa-solid fa-triangle-exclamation"></i>
<?= $error ?>
</div>
<?php endif; ?>

<form method="post">

<div class="form-grid">

<div class="form-group full">
<label>Nama Lengkap</label>
<input type="text" name="nama" placeholder="Nama lengkap pengguna" required>
</div>

<div class="form-group">
<label>Username</label>
<input type="text" name="username" placeholder="Username login" required>
</div>

<div class="form-group">
<label>Password</label>
<input type="password" name="password" placeholder="Password login" required>
</div>

<div class="form-group full">
<label>Role Pengguna</label>
<select name="role" required>
<option value="">Pilih Role</option>
<option value="admin">Admin</option>
<option value="teknisi">Teknisi</option>
<option value="manajer">Manajer</option>
</select>
</div>

</div>

<div class="actions">
<a href="dashboard.php" class="btn btn-secondary">
<i class="fa-solid fa-arrow-left"></i>
Kembali
</a>

<button class="btn btn-primary">
<i class="fa-solid fa-floppy-disk"></i>
Simpan
</button>
</div>

</form>
</div>

</main>

</body>
</html>
