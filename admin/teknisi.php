<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}
include '../database/config.php';

/* ===== DELETE ===== */
if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM teknisi WHERE id_teknisi='$_GET[delete]'");
    header("Location: teknisi.php");
    exit;
}

/* ===== DATA ===== */
$pengguna = $conn->query("SELECT id_pengguna, nama FROM pengguna WHERE role='teknisi'");
$teknisi = $conn->query("
    SELECT t.id_teknisi, p.nama, t.no_hp, t.alamat
    FROM teknisi t
    JOIN pengguna p ON t.id_pengguna=p.id_pengguna
    ORDER BY t.id_teknisi DESC
");

/* ===== EDIT MODE ===== */
$editMode = false;
if (isset($_GET['edit'])) {
    $editMode = true;
    $editData = $conn->query("SELECT * FROM teknisi WHERE id_teknisi='$_GET[edit]'")->fetch_assoc();
}

/* ===== SAVE ===== */
if ($_SERVER['REQUEST_METHOD']=="POST") {
    if (isset($_POST['update_id'])) {
        $conn->query("
            UPDATE teknisi SET
            id_pengguna='$_POST[id_pengguna]',
            no_hp='$_POST[no_hp]',
            alamat='$_POST[alamat]'
            WHERE id_teknisi='$_POST[update_id]'
        ");
    } else {
        $conn->query("
            INSERT INTO teknisi (id_pengguna,no_hp,alamat)
            VALUES ('$_POST[id_pengguna]','$_POST[no_hp]','$_POST[alamat]')
        ");
    }
    header("Location: teknisi.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Manajemen Teknisi | SISMONTEK</title>

<!-- Font -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet"
 href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
:root{
 --bg:#f5f7fb;
 --card:#ffffff;
 --border:#e5e7eb;
 --text:#0f172a;
 --muted:#64748b;
 --primary:#2563eb;
 --danger:#dc2626;
}

*{box-sizing:border-box}
body{
 margin:0;
 font-family:Inter,sans-serif;
 background:var(--bg);
 color:var(--text);
}

/* ===== MAIN ===== */
main{
 margin-left:260px;
 padding:32px;
 min-height:100vh;
}

h1{
 font-size:24px;
 font-weight:700;
 margin-bottom:24px;
}

/* ===== CARD ===== */
.card{
 background:var(--card);
 border-radius:16px;
 padding:24px;
 margin-bottom:28px;
 box-shadow:0 10px 30px rgba(0,0,0,.06);
}
.card h3{
 margin:0 0 16px;
 font-size:18px;
 font-weight:600;
}

/* ===== FORM ===== */
label{
 font-size:13px;
 color:var(--muted);
}
input,select,textarea{
 width:100%;
 border:1px solid var(--border);
 border-radius:12px;
 padding:12px;
 margin:8px 0 16px;
 font-size:14px;
}

button{
 background:#2563eb;
 color:#fff;
 border:none;
 padding:12px 28px;
 border-radius:999px;
 font-weight:500;
 cursor:pointer;
}

/* ===== TABLE ===== */
table{
 width:100%;
 border-collapse:collapse;
}
th{
 background:#2563eb;
 color:#fff;
 padding:12px;
 text-align:left;
 font-size:13px;
}
td{
 padding:12px;
 border-bottom:1px solid var(--border);
 font-size:14px;
}

/* ===== ACTION ===== */
.action a{
 padding:8px 14px;
 border-radius:999px;
 font-size:12px;
 text-decoration:none;
 font-weight:500;
 margin-right:6px;
}
.edit{
 background:rgba(37,99,235,.15);
 color:var(--primary);
}
.delete{
 background:rgba(220,38,38,.15);
 color:var(--danger);
}
</style>
</head>

<body>

<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<main>

<h1>Manajemen Teknisi</h1>

<div class="card">
<h3><?= $editMode ? "Edit Teknisi" : "Tambah Teknisi" ?></h3>

<form method="post">

<?php if($editMode): ?>
<input type="hidden" name="update_id" value="<?= $editData['id_teknisi'] ?>">
<?php endif; ?>

<label>Nama Teknisi</label>
<select name="id_pengguna" required>
<option value="">Pilih Teknisi</option>
<?php while($p=$pengguna->fetch_assoc()):
$sel=$editMode && $editData['id_pengguna']==$p['id_pengguna']?'selected':'';
?>
<option value="<?= $p['id_pengguna'] ?>" <?= $sel ?>><?= $p['nama'] ?></option>
<?php endwhile; ?>
</select>

<label>No HP</label>
<input type="text" name="no_hp" required value="<?= $editMode?$editData['no_hp']:'' ?>">

<label>Alamat</label>
<textarea name="alamat"><?= $editMode?$editData['alamat']:'' ?></textarea>

<button>
<i class="fa-solid fa-floppy-disk"></i>
<?= $editMode?"Simpan Perubahan":"Tambah Teknisi" ?>
</button>

</form>
</div>

<div class="card">
<h3>Daftar Teknisi</h3>

<table>
<tr>
<th>ID</th>
<th>Nama</th>
<th>No HP</th>
<th>Alamat</th>
<th>Aksi</th>
</tr>

<?php if($teknisi->num_rows>0): while($t=$teknisi->fetch_assoc()): ?>
<tr>
<td><?= $t['id_teknisi'] ?></td>
<td><?= $t['nama'] ?></td>
<td><?= $t['no_hp'] ?></td>
<td><?= $t['alamat'] ?></td>
<td class="action">
<a class="edit" href="?edit=<?= $t['id_teknisi'] ?>">
<i class="fa-solid fa-pen"></i> Edit
</a>
<a class="delete" href="?delete=<?= $t['id_teknisi'] ?>" onclick="return confirm('Hapus data?')">
<i class="fa-solid fa-trash"></i> Hapus
</a>
</td>
</tr>
<?php endwhile; else: ?>
<tr>
<td colspan="5" align="center">Belum ada data teknisi</td>
</tr>
<?php endif; ?>
</table>

</div>

</main>

</body>
</html>
