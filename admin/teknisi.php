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

<style>
body{
 margin:0;
 font-family:Poppins,Arial;
 background:#f5f7fb;
 display:flex;
}



/* ===== MAIN ===== */


h1{color:#1e40af;margin-bottom:20px;}

.card{
 background:#fff;
 border-radius:14px;
 padding:24px;
 margin-bottom:25px;
 box-shadow:0 8px 20px rgba(0,0,0,.06);
}

.card h3{margin-top:0;color:#1e40af;}

/* ===== FORM ===== */
input,select,textarea{
 width:100%;
 padding:10px;
 margin-top:8px;
 border-radius:8px;
 border:1px solid #d1d5db;
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

button:hover{background:#1e40af;}

/* ===== TABLE ===== */
table{width:100%;border-collapse:collapse;}
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
<h1>Manajemen Teknisi</h1>

<div class="card">
<h3><?= $editMode ? "Edit Teknisi" : "Tambah Teknisi" ?></h3>

<form method="post">
<?php if($editMode): ?>
<input type="hidden" name="update_id" value="<?= $editData['id_teknisi'] ?>">
<?php endif; ?>

<select name="id_pengguna" required>
<option value="">Pilih Teknisi</option>
<?php while($p=$pengguna->fetch_assoc()):
$sel=$editMode && $editData['id_pengguna']==$p['id_pengguna']?'selected':'';
?>
<option value="<?= $p['id_pengguna'] ?>" <?= $sel ?>><?= $p['nama'] ?></option>
<?php endwhile; ?>
</select>

<input type="text" name="no_hp" placeholder="No HP" required value="<?= $editMode?$editData['no_hp']:'' ?>">
<textarea name="alamat" placeholder="Alamat"><?= $editMode?$editData['alamat']:'' ?></textarea>

<button><?= $editMode?"Simpan Perubahan":"Tambah Teknisi" ?></button>
</form>
</div>

<div class="card">
<h3>Daftar Teknisi</h3>

<table>
<tr>
<th>ID</th><th>Nama</th><th>No HP</th><th>Alamat</th><th>Aksi</th>
</tr>
<?php if($teknisi->num_rows>0): while($t=$teknisi->fetch_assoc()): ?>
<tr>
<td><?= $t['id_teknisi'] ?></td>
<td><?= $t['nama'] ?></td>
<td><?= $t['no_hp'] ?></td>
<td><?= $t['alamat'] ?></td>
<td class="action">
<a class="edit" href="?edit=<?= $t['id_teknisi'] ?>">Edit</a>
<a class="delete" href="?delete=<?= $t['id_teknisi'] ?>" onclick="return confirm('Hapus data?')">Hapus</a>
</td>
</tr>
<?php endwhile; else: ?>
<tr><td colspan="5" align="center">Belum ada data teknisi</td></tr>
<?php endif; ?>
</table>
</div>
</div>

</body>
</html>
