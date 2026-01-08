<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}
include '../database/config.php';

/* ================= CRUD ================= */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah'])) {
    $conn->query("INSERT INTO pelanggan VALUES (
        NULL,
        '$_POST[nama_pelanggan]',
        '$_POST[alamat]',
        '$_POST[nomer_telepon]',
        '$_POST[email]',
        '$_POST[paket]'
    )");
}

if (isset($_GET['hapus'])) {
    $conn->query("DELETE FROM pelanggan WHERE id_pelanggan='$_GET[hapus]'");
    header("Location: pelanggan.php");
    exit;
}

$editMode = false;
if (isset($_GET['edit'])) {
    $editMode = true;
    $editData = $conn->query("SELECT * FROM pelanggan WHERE id_pelanggan='$_GET[edit]'")->fetch_assoc();
}

if (isset($_POST['update'])) {
    $conn->query("UPDATE pelanggan SET
        nama_pelanggan='$_POST[nama_pelanggan]',
        alamat='$_POST[alamat]',
        nomer_telepon='$_POST[nomer_telepon]',
        email='$_POST[email]',
        paket='$_POST[paket]'
        WHERE id_pelanggan='$_POST[id_pelanggan]'");
    header("Location: pelanggan.php");
    exit;
}

$pelanggan = $conn->query("SELECT * FROM pelanggan ORDER BY id_pelanggan DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Pelanggan | SISMONTEK</title>

<style>
body{
 margin:0;
 font-family:Poppins,Arial;
 background:#f5f7fb;
 display:flex;
}



/* ===== MAIN ===== */


h1{
 color:#1e40af;
 margin-bottom:20px;
}

.card{
 background:#fff;
 border-radius:14px;
 padding:24px;
 box-shadow:0 8px 20px rgba(0,0,0,.06);
 margin-bottom:25px;
}

.card h3{
 margin-top:0;
 color:#1e40af;
}

/* ===== FORM ===== */
input,textarea,select{
 width:100%;
 padding:10px;
 margin-top:8px;
 border:1px solid #d1d5db;
 border-radius:8px;
}

button{
 background:#2563eb;
 color:#fff;
 border:none;
 padding:10px 22px;
 border-radius:8px;
 cursor:pointer;
 margin-top:15px;
}

button:hover{
 background:#1e40af;
}

/* ===== TABLE ===== */
table{
 width:100%;
 border-collapse:collapse;
}

th{
 background:#2563eb;
 color:#fff;
 padding:10px;
}

td{
 padding:10px;
 border-bottom:1px solid #e5e7eb;
}

.action a{
 text-decoration:none;
 margin-right:8px;
 font-weight:500;
}

.edit{color:#2563eb;}
.delete{color:#dc2626;}
</style>
</head>

<body>

<?php include __DIR__ . '/sidebar.php'; ?>

<!-- CONTENT -->
<div class="main">
  <button class="menu" onclick="sidebar.classList.toggle('show')">☰</button>
<h1>Manajemen Data Pelanggan</h1>

<div class="card">
<h3><?= $editMode ? 'Edit Pelanggan' : 'Tambah Pelanggan'; ?></h3>
<form method="post">
<?php if($editMode): ?>
<input type="hidden" name="id_pelanggan" value="<?= $editData['id_pelanggan']; ?>">
<?php endif; ?>

<input type="text" name="nama_pelanggan" placeholder="Nama" required value="<?= $editMode?$editData['nama_pelanggan']:''; ?>">
<textarea name="alamat" placeholder="Alamat" required><?= $editMode?$editData['alamat']:''; ?></textarea>
<input type="text" name="nomer_telepon" placeholder="Telepon" required value="<?= $editMode?$editData['nomer_telepon']:''; ?>">
<input type="email" name="email" placeholder="Email" required value="<?= $editMode?$editData['email']:''; ?>">

<select name="paket" required>
<option value="">Pilih Paket</option>
<option <?= $editMode && $editData['paket']=="Basic"?'selected':''; ?>>Basic</option>
<option <?= $editMode && $editData['paket']=="Standard"?'selected':''; ?>>Standard</option>
<option <?= $editMode && $editData['paket']=="Premium"?'selected':''; ?>>Premium</option>
</select>

<button name="<?= $editMode?'update':'tambah'; ?>">Simpan</button>
</form>
</div>

<div class="card">
<h3>Daftar Pelanggan</h3>
<table>
<tr>
<th>ID</th><th>Nama</th><th>Alamat</th><th>Telepon</th><th>Email</th><th>Paket</th><th>Aksi</th>
</tr>
<?php while($p=$pelanggan->fetch_assoc()): ?>
<tr>
<td><?= $p['id_pelanggan']; ?></td>
<td><?= $p['nama_pelanggan']; ?></td>
<td><?= $p['alamat']; ?></td>
<td><?= $p['nomer_telepon']; ?></td>
<td><?= $p['email']; ?></td>
<td><?= $p['paket']; ?></td>
<td class="action">
<a class="edit" href="?edit=<?= $p['id_pelanggan']; ?>">Edit</a>
<a class="delete" href="?hapus=<?= $p['id_pelanggan']; ?>" onclick="return confirm('Hapus data?')">Hapus</a>
</td>
</tr>
<?php endwhile; ?>
</table>
</div>
</div>

</body>
</html>
