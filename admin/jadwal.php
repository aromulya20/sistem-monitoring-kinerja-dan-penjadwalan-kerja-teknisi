<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}
include '../database/config.php';

/* ================= CRUD ================= */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah'])) {
    $conn->query("INSERT INTO jadwal (id_teknisi,id_pelanggan,deskripsi_pekerjaan,tanggal_jadwal,status)
        VALUES (
            '{$_POST['id_teknisi']}',
            '{$_POST['id_pelanggan']}',
            '{$_POST['deskripsi_pekerjaan']}',
            '{$_POST['tanggal_jadwal']}',
            '{$_POST['status']}'
        )");
    header("Location: jadwal.php?success=1");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit'])) {
    $conn->query("UPDATE jadwal SET
        id_teknisi='{$_POST['id_teknisi']}',
        id_pelanggan='{$_POST['id_pelanggan']}',
        deskripsi_pekerjaan='{$_POST['deskripsi_pekerjaan']}',
        tanggal_jadwal='{$_POST['tanggal_jadwal']}',
        status='{$_POST['status']}'
        WHERE id_jadwal='{$_POST['id_jadwal']}'
    ");
    header("Location: jadwal.php?updated=1");
    exit;
}

if (isset($_GET['hapus'])) {
    $conn->query("DELETE FROM jadwal WHERE id_jadwal='{$_GET['hapus']}'");
    header("Location: jadwal.php?deleted=1");
    exit;
}

/* ================= DATA ================= */
$teknisi = $conn->query("
    SELECT t.id_teknisi,u.nama 
    FROM teknisi t JOIN pengguna u ON t.id_pengguna=u.id_pengguna
");
$pelanggan = $conn->query("SELECT * FROM pelanggan");
$jadwal = $conn->query("
    SELECT j.*,u.nama teknisi,p.nama_pelanggan
    FROM jadwal j
    LEFT JOIN teknisi t ON j.id_teknisi=t.id_teknisi
    LEFT JOIN pengguna u ON t.id_pengguna=u.id_pengguna
    LEFT JOIN pelanggan p ON j.id_pelanggan=p.id_pelanggan
    ORDER BY j.tanggal_jadwal DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manajemen Jadwal | SISMONTEK</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet"
 href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
:root{
 --bg:#f4f6fb;
 --card:#ffffff;
 --border:#e5e7eb;
 --text:#0f172a;
 --muted:#64748b;

 --primary:#2563eb;
 --success:#16a34a;
 --info:#0891b2;
 --warning:#f59e0b;
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
 margin-bottom:26px;
}

/* ===== ALERT ===== */
.alert{
 padding:14px 20px;
 border-radius:14px;
 margin-bottom:18px;
 font-size:14px;
}
.success{
 background:rgba(22,163,74,.15);
 color:var(--success);
}

/* ===== CARD ===== */
.card{
 background:var(--card);
 border-radius:20px;
 padding:26px;
 margin-bottom:30px;
 box-shadow:0 14px 40px rgba(0,0,0,.06);
}
.card h3{
 margin:0 0 18px;
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
 border-radius:14px;
 padding:12px;
 margin:8px 0 16px;
 font-size:14px;
}

button{
 background:linear-gradient(135deg,#2563eb,#60a5fa);
 border:none;
 color:#fff;
 padding:12px 30px;
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
 font-size:12px;
 color:var(--muted);
 padding-bottom:14px;
 text-align:left;
}
td{
 padding:16px 0;
 border-top:1px solid var(--border);
 font-size:14px;
}

/* ===== STATUS ===== */
.status{
 padding:6px 14px;
 border-radius:999px;
 font-size:12px;
 font-weight:500;
}
.dijadwalkan{background:rgba(245,158,11,.18);color:var(--warning);}
.proses{background:rgba(8,145,178,.18);color:var(--info);}
.selesai{background:rgba(22,163,74,.18);color:var(--success);}

/* ===== ACTION ===== */
.action a{
 padding:8px 16px;
 border-radius:999px;
 font-size:12px;
 text-decoration:none;
 font-weight:500;
 margin-right:6px;
}
.edit{background:rgba(37,99,235,.15);color:var(--primary);}
.delete{background:rgba(220,38,38,.15);color:var(--danger);}
</style>
</head>

<body>

<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<main>

<h1>Manajemen Jadwal Teknisi</h1>

<?php if(isset($_GET['success'])): ?><div class="alert success">Jadwal berhasil ditambahkan</div><?php endif ?>
<?php if(isset($_GET['updated'])): ?><div class="alert success">Jadwal berhasil diperbarui</div><?php endif ?>
<?php if(isset($_GET['deleted'])): ?><div class="alert success">Jadwal berhasil dihapus</div><?php endif ?>

<div class="card">
<h3>Tambah / Edit Jadwal</h3>

<form method="POST">
<input type="hidden" name="id_jadwal" id="id_jadwal">

<label>Teknisi</label>
<select name="id_teknisi" id="id_teknisi">
<?php while($t=$teknisi->fetch_assoc()): ?>
<option value="<?= $t['id_teknisi'] ?>"><?= $t['nama'] ?></option>
<?php endwhile ?>
</select>

<label>Pelanggan</label>
<select name="id_pelanggan" id="id_pelanggan">
<?php while($p=$pelanggan->fetch_assoc()): ?>
<option value="<?= $p['id_pelanggan'] ?>"><?= $p['nama_pelanggan'] ?></option>
<?php endwhile ?>
</select>

<label>Deskripsi</label>
<textarea name="deskripsi_pekerjaan" id="deskripsi_pekerjaan"></textarea>

<label>Tanggal</label>
<input type="date" name="tanggal_jadwal" id="tanggal_jadwal">

<label>Status</label>
<select name="status" id="status">
<option value="dijadwalkan">Dijadwalkan</option>
<option value="proses">Proses</option>
<option value="selesai">Selesai</option>
</select>

<button id="btn" name="tambah">
<i class="fa-solid fa-calendar-plus"></i> Simpan Jadwal
</button>
</form>
</div>

<div class="card">
<h3>Daftar Jadwal Teknisi</h3>

<table>
<tr>
<th>Teknisi</th>
<th>Pelanggan</th>
<th>Tanggal</th>
<th>Status</th>
<th>Aksi</th>
</tr>

<?php while($j=$jadwal->fetch_assoc()): ?>
<tr>
<td><?= $j['teknisi'] ?></td>
<td><?= $j['nama_pelanggan'] ?></td>
<td><?= date('d M Y',strtotime($j['tanggal_jadwal'])) ?></td>
<td><span class="status <?= $j['status'] ?>"><?= ucfirst($j['status']) ?></span></td>
<td class="action">
<a class="edit" onclick='editData(<?= json_encode($j) ?>)'>
<i class="fa-solid fa-pen"></i> Edit
</a>
<a class="delete" href="?hapus=<?= $j['id_jadwal'] ?>" onclick="return confirm('Hapus jadwal?')">
<i class="fa-solid fa-trash"></i> Hapus
</a>
</td>
</tr>
<?php endwhile ?>
</table>
</div>

</main>

<script>
function editData(d){
 id_jadwal.value=d.id_jadwal;
 deskripsi_pekerjaan.value=d.deskripsi_pekerjaan;
 tanggal_jadwal.value=d.tanggal_jadwal;
 status.value=d.status;
 btn.name='edit';
 btn.innerHTML='<i class="fa-solid fa-pen"></i> Update Jadwal';
 window.scrollTo({top:0,behavior:'smooth'});
}
</script>

</body>
</html>
