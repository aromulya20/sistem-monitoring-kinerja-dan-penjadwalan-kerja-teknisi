<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}
include '../database/config.php';

/* DATA */
$jadwalQuery = $conn->query("
    SELECT deskripsi_pekerjaan, status, tanggal_jadwal
    FROM jadwal
    ORDER BY tanggal_jadwal DESC
    LIMIT 5
");

$status_labels = [];
$status_values = [];
$q = $conn->query("SELECT status, COUNT(*) total FROM jadwal GROUP BY status");
while($r = $q->fetch_assoc()){
  $status_labels[] = ucfirst($r['status']);
  $status_values[] = (int)$r['total'];
}

$laporan_tanggal = [];
$laporan_total = [];
$q2 = $conn->query("
  SELECT tanggal_laporan, COUNT(*) total
  FROM laporan
  GROUP BY tanggal_laporan
  ORDER BY tanggal_laporan
");
while($r = $q2->fetch_assoc()){
  $laporan_tanggal[] = $r['tanggal_laporan'];
  $laporan_total[] = (int)$r['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard Admin</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style>
:root{
  --primary:#3f72af;
  --bg:#f4f6fb;
  --card:#fff;
}
*{box-sizing:border-box}
body{
  margin:0;
  font-family:Poppins,sans-serif;
  background:var(--bg);
}

/* BACKDROP */
.backdrop{
  position:fixed;
  inset:0;
  background:rgba(0,0,0,.4);
  display:none;
  z-index:90;
}
.backdrop.show{display:block}

/* SIDEBAR */
.sidebar{
  position:fixed;
  top:0;
  left:-260px;
  width:240px;
  height:100%;
  background:var(--primary);
  color:#fff;
  padding-top:20px;
  transition:.3s;
  z-index:100;
}
.sidebar.show{left:0}
.sidebar h2{text-align:center;margin-bottom:20px}
.sidebar a{
  color:#fff;
  text-decoration:none;
  padding:14px 20px;
  display:block;
}
.sidebar a:hover,.active{background:#2e5c8a}

/* HEADER */
header{
  background:#fff;
  padding:14px 16px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  position:sticky;
  top:0;
  z-index:10;
  box-shadow:0 2px 8px rgba(0,0,0,.08);
}
.menu-btn{
  font-size:22px;
  border:none;
  background:none;
}
.logout{
  background:#e74c3c;
  color:#fff;
  padding:8px 12px;
  border-radius:8px;
  text-decoration:none;
}

/* CONTENT */
.content{
  padding:16px;
}

/* CARD */
.card{
  background:var(--card);
  border-radius:14px;
  padding:16px;
  margin-bottom:16px;
  box-shadow:0 6px 18px rgba(0,0,0,.06);
}

/* CHART */
.chart{
  height:240px;
}

/* TABLE */
.table{
  width:100%;
  border-collapse:collapse;
}
.table th{
  background:var(--primary);
  color:#fff;
  padding:10px;
}
.table td{
  padding:10px;
  border-bottom:1px solid #eee;
}

/* MOBILE OPTIMIZATION */
@media(max-width:600px){

  h3,h4{font-size:16px}

  .chart{height:200px}

  /* sembunyikan kolom tanggal */
  .table th:nth-child(3),
  .table td:nth-child(3){
    display:none;
  }
}
</style>
</head>

<body>

<div class="backdrop" id="backdrop"></div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
  <h2>🔧 Sismontek</h2>
  <a class="active" href="#">🏠 Dashboard</a>
  <a href="jadwal.php">🗓 Jadwal</a>
  <a href="pelanggan.php">👥 Pelanggan</a>
  <a href="teknisi.php">🧑‍🔧 Teknisi</a>
  <a href="laporan.php">📊 Laporan</a>
  <a href="../auth/logout.php">🚪 Logout</a>
</div>

<!-- HEADER -->
<header>
  <button class="menu-btn" id="menuBtn">☰</button>
  <strong>Dashboard</strong>
  <a href="../auth/logout.php" class="logout">Logout</a>
</header>

<!-- CONTENT -->
<div class="content">

<div class="card">
  <h3>Halo, <?= htmlspecialchars($_SESSION['nama']) ?> 👋</h3>
  <small>Role: <?= ucfirst($_SESSION['role']) ?></small>
</div>

<div class="card">
  <h4>Status Jadwal</h4>
  <canvas id="statusChart" class="chart"></canvas>
</div>

<div class="card">
  <h4>Laporan Harian</h4>
  <canvas id="laporanChart" class="chart"></canvas>
</div>

<div class="card">
<h4>Tugas Terbaru</h4>
<table class="table">
<thead>
<tr>
  <th>Deskripsi</th>
  <th>Status</th>
  <th>Tanggal</th>
</tr>
</thead>
<tbody>
<?php
if ($jadwalQuery->num_rows){
  while($r=$jadwalQuery->fetch_assoc()){
    echo "<tr>
      <td>".htmlspecialchars($r['deskripsi_pekerjaan'])."</td>
      <td>".ucfirst($r['status'])."</td>
      <td>".$r['tanggal_jadwal']."</td>
    </tr>";
  }
}else{
  echo "<tr><td colspan='3'>Belum ada data</td></tr>";
}
?>
</tbody>
</table>
</div>

</div>

<script>
const sidebar = document.getElementById("sidebar");
const backdrop = document.getElementById("backdrop");

menuBtn.onclick = ()=>{
  sidebar.classList.add("show");
  backdrop.classList.add("show");
}
backdrop.onclick = ()=>{
  sidebar.classList.remove("show");
  backdrop.classList.remove("show");
}

/* Charts */
new Chart(statusChart,{
  type:'bar',
  data:{
    labels:<?= json_encode($status_labels) ?>,
    datasets:[{data:<?= json_encode($status_values) ?>,backgroundColor:'#3f72af'}]
  },
  options:{responsive:true,plugins:{legend:{display:false}}}
});

new Chart(laporanChart,{
  type:'line',
  data:{
    labels:<?= json_encode($laporan_tanggal) ?>,
    datasets:[{data:<?= json_encode($laporan_total) ?>,borderColor:'#3f72af',fill:true}]
  },
  options:{responsive:true}
});
</script>

</body>
</html>
