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

<!-- Tailwind CDN -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Font Awesome Icons -->
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body class="bg-slate-100">

<!-- SIDEBAR -->
<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<!-- KONTEN -->
<main class="ml-64 min-h-screen p-8">

<h1 class="text-2xl font-semibold text-slate-800 mb-6">
    Manajemen Data Pelanggan
</h1>

<!-- FORM -->
<div class="bg-white rounded-xl shadow p-6 mb-8">
<h3 class="text-lg font-semibold mb-4">
    <?= $editMode ? 'Edit Pelanggan' : 'Tambah Pelanggan'; ?>
</h3>

<form method="post" class="grid grid-cols-1 md:grid-cols-2 gap-6">

<?php if($editMode): ?>
<input type="hidden" name="id_pelanggan" value="<?= $editData['id_pelanggan']; ?>">
<?php endif; ?>

<div>
<label class="text-sm font-medium">Nama Pelanggan</label>
<input type="text" name="nama_pelanggan" required
       value="<?= $editMode?$editData['nama_pelanggan']:''; ?>"
       class="w-full border rounded-lg px-4 py-2 mt-1">
</div>

<div>
<label class="text-sm font-medium">Nomor Telepon</label>
<input type="text" name="nomer_telepon" required
       value="<?= $editMode?$editData['nomer_telepon']:''; ?>"
       class="w-full border rounded-lg px-4 py-2 mt-1">
</div>

<div>
<label class="text-sm font-medium">Email</label>
<input type="email" name="email" required
       value="<?= $editMode?$editData['email']:''; ?>"
       class="w-full border rounded-lg px-4 py-2 mt-1">
</div>

<div>
<label class="text-sm font-medium">Paket</label>
<select name="paket" required
        class="w-full border rounded-lg px-4 py-2 mt-1">
<option value="">Pilih Paket</option>
<option <?= $editMode && $editData['paket']=="Basic"?'selected':''; ?>>Basic</option>
<option <?= $editMode && $editData['paket']=="Standard"?'selected':''; ?>>Standard</option>
<option <?= $editMode && $editData['paket']=="Premium"?'selected':''; ?>>Premium</option>
</select>
</div>

<div class="md:col-span-2">
<label class="text-sm font-medium">Alamat</label>
<textarea name="alamat" rows="3" required
          class="w-full border rounded-lg px-4 py-2 mt-1"><?= $editMode?$editData['alamat']:''; ?></textarea>
</div>

<div class="md:col-span-2">
<button name="<?= $editMode?'update':'tambah'; ?>"
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
<i class="fa-solid fa-floppy-disk mr-1"></i> Simpan
</button>
</div>

</form>
</div>

<!-- TABEL -->
<div class="bg-white rounded-xl shadow p-6">
<h3 class="text-lg font-semibold mb-4">Daftar Pelanggan</h3>

<table class="w-full border">
<thead class="bg-blue-600 text-white">
<tr>
<th class="p-2">ID</th>
<th class="p-2">Nama</th>
<th class="p-2">Alamat</th>
<th class="p-2">Telepon</th>
<th class="p-2">Email</th>
<th class="p-2">Paket</th>
<th class="p-2">Aksi</th>
</tr>
</thead>

<tbody>
<?php while($p=$pelanggan->fetch_assoc()): ?>
<tr class="border-b">
<td class="p-2"><?= $p['id_pelanggan']; ?></td>
<td class="p-2"><?= $p['nama_pelanggan']; ?></td>
<td class="p-2"><?= $p['alamat']; ?></td>
<td class="p-2"><?= $p['nomer_telepon']; ?></td>
<td class="p-2"><?= $p['email']; ?></td>
<td class="p-2"><?= $p['paket']; ?></td>
<td class="p-2 space-x-2">
<a href="?edit=<?= $p['id_pelanggan']; ?>" class="text-blue-600">
<i class="fa-solid fa-pen-to-square"></i>
</a>
<a href="?hapus=<?= $p['id_pelanggan']; ?>"
   onclick="return confirm('Hapus data?')"
   class="text-red-600">
<i class="fa-solid fa-trash"></i>
</a>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>

</main>

</body>
</html>
