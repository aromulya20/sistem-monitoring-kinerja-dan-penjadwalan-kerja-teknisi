<?php
session_start();
include '../database/config.php';

/* CEK LOGIN */
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'teknisi') {
    header("Location: /auth/login.php");
    exit;
}

$id_pengguna = $_SESSION['id_pengguna'];

/* AMBIL ID TEKNISI */
$qTeknisi = $conn->prepare("SELECT id_teknisi FROM teknisi WHERE id_pengguna = ?");
$qTeknisi->bind_param("i", $id_pengguna);
$qTeknisi->execute();
$dataTeknisi = $qTeknisi->get_result()->fetch_assoc();

if (!$dataTeknisi) {
    die("Data teknisi tidak ditemukan");
}

$id_teknisi = $dataTeknisi['id_teknisi'];

/* PROSES TOMBOL MULAI */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mulai'])) {
    $id_jadwal = $_POST['id_jadwal'];
    $update = $conn->prepare("UPDATE jadwal SET status='proses' WHERE id_jadwal=?");
    $update->bind_param("i", $id_jadwal);
    $update->execute();
    header("Location: dashboard.teknisi.php");
    exit;
}

/* AMBIL DATA TUGAS / JADWAL */
$qJadwal = $conn->prepare("
    SELECT j.id_jadwal, j.deskripsi_pekerjaan, j.status, j.tanggal_jadwal,
           p.nama_pelanggan, p.alamat
    FROM jadwal j
    JOIN pelanggan p ON j.id_pelanggan = p.id_pelanggan
    WHERE j.id_teknisi = ?
    ORDER BY j.tanggal_jadwal DESC
");
$qJadwal->bind_param("i", $id_teknisi);
$qJadwal->execute();
$jadwal = $qJadwal->get_result();

/* STATISTIK KERJA */
$qStat = $conn->prepare("
    SELECT 
        SUM(status='dijadwalkan') AS dijadwalkan,
        SUM(status='proses') AS proses,
        SUM(status='selesai') AS selesai
    FROM jadwal
    WHERE id_teknisi = ?
");
$qStat->bind_param("i", $id_teknisi);
$qStat->execute();
$stat = $qStat->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tugas Teknisi</title>

<style>
*{box-sizing:border-box}
body{
    margin:0;
    font-family:'Poppins',sans-serif;
    background:#f2f4f8;
}

/* APP BAR */
.appbar{
    background:#3f72af;
    color:#fff;
    padding:14px 18px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    position:sticky;
    top:0;
    z-index:10;
}

/* CONTENT */
.content{
    padding:16px;
}

/* TASK CARD */
.task{
    background:#fff;
    border-radius:18px;
    padding:16px;
    margin-bottom:16px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
}
.task h4{
    margin:0;
    color:#3f72af;
}
.task p{
    margin:6px 0;
    color:#555;
    font-size:14px;
}

/* BADGE STATUS */
.badge{
    display:inline-block;
    padding:6px 12px;
    border-radius:999px;
    font-size:12px;
    font-weight:600;
    color:#fff;
    margin-top:6px;
}
.dijadwalkan{background:#f9a825}
.proses{background:#29b6f6}
.selesai{background:#66bb6a}

/* STATISTIK */
.stats{
    display:flex;
    gap:12px;
    margin-bottom:18px;
}
.stat-card{
    flex:1;
    background:#fff;
    padding:14px;
    border-radius:18px;
    text-align:center;
    box-shadow:0 6px 16px rgba(0,0,0,.08);
}
.stat-card h3{
    margin:0;
    font-size:13px;
    color:#777;
}
.stat-card h2{
    margin-top:6px;
    font-size:22px;
    font-weight:700;
}
.stat-dij{color:#f9a825}
.stat-pro{color:#29b6f6}
.stat-sel{color:#66bb6a}

/* BUTTON */
.btn{
    display:block;
    width:100%;
    margin-top:12px;
    padding:12px;
    border:none;
    border-radius:14px;
    font-weight:600;
    color:#fff;
    text-align:center;
    text-decoration:none;
    cursor:pointer;
}
.btn-proses{background:#29b6f6}
.btn-selesai{background:#66bb6a}
</style>
</head>

<body>

<!-- APP BAR -->
<div class="appbar">
    <span>📋 Tugas Teknisi</span>
    <a href="/auth/logout.php" style="color:#fff;text-decoration:none">Logout</a>
</div>

<div class="content">
<div class="stats">
    <div class="stat-card">
        <h3>Dijadwalkan</h3>
        <h2 class="stat-dij"><?= $stat['dijadwalkan'] ?></h2>
    </div>
    <div class="stat-card">
        <h3>Proses</h3>
        <h2 class="stat-pro"><?= $stat['proses'] ?></h2>
    </div>
    <div class="stat-card">
        <h3>Selesai</h3>
        <h2 class="stat-sel"><?= $stat['selesai'] ?></h2>
    </div>
</div>

<?php if($jadwal->num_rows > 0): ?>
    <?php while($r = $jadwal->fetch_assoc()): ?>

    <div class="task">
        <h4><?= $r['nama_pelanggan'] ?></h4>
        <p>📍 <?= $r['alamat'] ?></p>
        <p>🛠 <?= $r['deskripsi_pekerjaan'] ?></p>
        <p>📅 <?= $r['tanggal_jadwal'] ?></p>

        <span class="badge <?= $r['status'] ?>"><?= $r['status'] ?></span>

        <?php if($r['status'] == 'dijadwalkan'): ?>
            <form method="POST">
                <input type="hidden" name="id_jadwal" value="<?= $r['id_jadwal'] ?>">
                <button name="mulai" class="btn btn-proses">Mulai Tugas</button>
            </form>

        <?php elseif($r['status'] == 'proses'): ?>
            <a href="form_laporan.php?id_jadwal=<?= $r['id_jadwal'] ?>" class="btn btn-selesai">
                Selesaikan & Buat Laporan
            </a>

        <?php else: ?>
            <a href="edit_laporan.php?id_jadwal=<?= $r['id_jadwal'] ?>" class="btn btn-proses">
                Lihat / Edit Laporan
            </a>
        <?php endif; ?>
    </div>

    <?php endwhile; ?>
<?php else: ?>
    <p>Tidak ada tugas saat ini.</p>
<?php endif; ?>

</div>

</body>
</html>
