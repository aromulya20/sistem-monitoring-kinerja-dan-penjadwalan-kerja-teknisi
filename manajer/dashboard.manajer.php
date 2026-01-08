<?php
session_start();
include '../database/config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'manajer') {
    header("Location: ../auth/login.php");
    exit;
}

$status = $conn->query("SELECT status, COUNT(*) j FROM jadwal GROUP BY status");
$status_labels=[]; $status_values=[];
while($r=$status->fetch_assoc()){
  $status_labels[]=ucfirst($r['status']);
  $status_values[]=$r['j'];
}

$lap = $conn->query("
SELECT tanggal_jadwal, COUNT(*) t 
FROM jadwal GROUP BY tanggal_jadwal ORDER BY tanggal_jadwal
");
$lap_t=[]; $lap_v=[];
while($r=$lap->fetch_assoc()){
  $lap_t[]=$r['tanggal_jadwal'];
  $lap_v[]=$r['t'];
}

$laporan = $conn->query("
SELECT l.*, p.nama_pelanggan, j.deskripsi_pekerjaan
FROM laporan l
JOIN jadwal j ON l.id_jadwal=j.id_jadwal
JOIN pelanggan p ON j.id_pelanggan=p.id_pelanggan
ORDER BY l.tanggal_laporan DESC LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard Manajer</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style>
*{box-sizing:border-box}
body{
  margin:0;
  font-family:Poppins,sans-serif;
  background:#f4f6fb;
}

/* HEADER */
.header{
  background:#3f72af;
  color:#fff;
  padding:16px;
  display:flex;
  justify-content:space-between;
  align-items:center;
}
.header h1{font-size:18px}
.header a{
  color:#fff;
  text-decoration:none;
  background:rgba(255,255,255,.2);
  padding:8px 12px;
  border-radius:8px;
}

/* CONTENT */
.container{padding:16px}

/* CARD */
.card{
  background:#fff;
  border-radius:16px;
  padding:16px;
  margin-bottom:16px;
  box-shadow:0 6px 15px rgba(0,0,0,.08);
}
.card h3{font-size:16px;margin-bottom:8px;color:#3f72af}

/* CHART */
.chart{
  height:200px;
}

/* LAPORAN LIST (MOBILE FRIENDLY) */
.list-item{
  border-left:4px solid #3f72af;
  padding:12px;
  margin-bottom:12px;
  background:#f9fbff;
  border-radius:10px;
}
.list-item strong{display:block}

/* BUTTON */
.btn{
  width:100%;
  padding:14px;
  border:none;
  border-radius:12px;
  background:#3f72af;
  color:#fff;
  font-size:15px;
  margin-bottom:10px;
}

/* DESKTOP */
@media(min-width:768px){
  .grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
  }
  .chart{height:260px}
  .btn{width:auto}
}
</style>
</head>
<body>

<div class="header">
  <h1>📊 Manajer</h1>
  <a href="../auth/logout.php">Logout</a>
</div>

<div class="container">

<div class="card">
  <h3>Halo, <?= htmlspecialchars($_SESSION['nama']) ?> 👋</h3>
  <small>Ringkasan performa teknisi</small>
</div>

<div class="grid">
  <div class="card">
    <h3>Status Jadwal</h3>
    <canvas id="c1" class="chart"></canvas>
  </div>
  <div class="card">
    <h3>Kerusakan Harian</h3>
    <canvas id="c2" class="chart"></canvas>
  </div>
</div>

<div class="card">
  <h3>Laporan Terbaru</h3>

  <?php if($laporan->num_rows): while($r=$laporan->fetch_assoc()): ?>
    <div class="list-item">
      <strong><?= $r['nama_pelanggan'] ?></strong>
      <?= $r['deskripsi_pekerjaan'] ?><br>
      <small><?= $r['kendala'] ?> • <?= $r['tanggal_laporan'] ?></small>
    </div>
  <?php endwhile; else: ?>
    <p>Tidak ada laporan</p>
  <?php endif; ?>
</div>

<button class="btn" onclick="cetak('minggu')">📅 Cetak Mingguan</button>
<button class="btn" onclick="cetak('bulan')">🗓 Cetak Bulanan</button>

<iframe id="f" style="display:none"></iframe>

</div>

<script>
new Chart(c1,{
  type:'bar',
  data:{labels:<?=json_encode($status_labels)?>,
  datasets:[{data:<?=json_encode($status_values)?>,backgroundColor:'#3f72af'}]},
  options:{responsive:true,plugins:{legend:{display:false}}}
});

new Chart(c2,{
  type:'line',
  data:{labels:<?=json_encode($lap_t)?>,
  datasets:[{data:<?=json_encode($lap_v)?>,borderColor:'#3f72af',fill:true}]},
  options:{responsive:true}
});

function cetak(p){
  const f=document.getElementById('f');
  f.src='cetak_laporan.php?periode='+p;
  f.onload=()=>f.contentWindow.print();
}
</script>

</body>
</html>
