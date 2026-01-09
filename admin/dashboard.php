<?php
session_start();
if(!isset($_SESSION['username'])){header("Location:../auth/login.php");exit;}
include '../database/config.php';

/* KPI */
$total=$conn->query("SELECT COUNT(*) t FROM laporan")->fetch_assoc()['t'];
$selesai=$conn->query("SELECT COUNT(*) t FROM jadwal WHERE status='selesai'")->fetch_assoc()['t'];
$proses=$conn->query("SELECT COUNT(*) t FROM jadwal WHERE status='proses'")->fetch_assoc()['t'];
$teknisi=$conn->query("SELECT COUNT(*) t FROM teknisi")->fetch_assoc()['t'];

/* Chart */
$labels=[];$values=[];
$q=$conn->query("SELECT status,COUNT(*) j FROM jadwal GROUP BY status");
while($r=$q->fetch_assoc()){ $labels[]=ucfirst($r['status']); $values[]=$r['j']; }

/* Table */
$data=$conn->query("
SELECT l.id_laporan,p.nama_pelanggan,u.nama teknisi,j.status,l.tanggal_laporan
FROM laporan l
JOIN jadwal j ON l.id_jadwal=j.id_jadwal
JOIN pelanggan p ON j.id_pelanggan=p.id_pelanggan
JOIN teknisi t ON j.id_teknisi=t.id_teknisi
JOIN pengguna u ON t.id_pengguna=u.id_pengguna
ORDER BY l.tanggal_laporan DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sismontek | Enterprise Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
:root{
 --bg:#f4f6fb;
 --card:#ffffff;
 --text:#0f172a;
 --muted:#64748b;
 --border:#e5e7eb;

 --primary:#2563eb;
 --success:#16a34a;
 --info:#0891b2;
 --warning:#f59e0b;
 --purple:#7c3aed;
}

*{box-sizing:border-box}
body{
 margin:0;
 font-family:Inter,sans-serif;
 background:var(--bg);
 color:var(--text);
}



/* Header */
.header{
 display:flex;
 justify-content:space-between;
 align-items:center;
 margin-bottom:30px;
}
.header h1{
 font-size:24px;
 font-weight:700;
}

/* KPI */
.kpi{
 display:grid;
 grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
 gap:22px;
 margin-bottom:32px;
}
.kpi-card{
 padding:24px;
 border-radius:18px;
 color:#fff;
 box-shadow:0 12px 30px rgba(0,0,0,.08);
}
.kpi-card span{
 font-size:13px;
 opacity:.9;
}
.kpi-card h2{
 margin-top:6px;
 font-size:30px;
}

.total{background:linear-gradient(135deg,#2563eb,#60a5fa)}
.selesai{background:linear-gradient(135deg,#16a34a,#4ade80)}
.proses{background:linear-gradient(135deg,#0891b2,#22d3ee)}
.teknisi{background:linear-gradient(135deg,#7c3aed,#a78bfa)}

/* Card */
.card{
 background:var(--card);
 border-radius:20px;
 padding:26px;
 margin-bottom:32px;
 box-shadow:0 14px 40px rgba(0,0,0,.06);
}
.card h3{
 margin:0 0 18px;
 font-size:18px;
 font-weight:600;
}

/* Table */
table{
 width:100%;
 border-collapse:collapse;
}
th{
 text-align:left;
 font-size:12px;
 color:var(--muted);
 padding-bottom:14px;
}
td{
 padding:16px 0;
 border-top:1px solid var(--border);
 font-size:14px;
}
.status{
 padding:6px 14px;
 border-radius:999px;
 font-size:12px;
 font-weight:500;
}
.status.selesai{
 background:rgba(22,163,74,.15);
 color:var(--success);
}
.status.proses{
 background:rgba(8,145,178,.15);
 color:var(--info);
}
.status.dijadwalkan{
 background:rgba(245,158,11,.18);
 color:var(--warning);
}


</style>
</head>

<body>

<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<div class="main">
 <div class="header">
  <button class="menu" onclick="sidebar.classList.toggle('show')">☰</button>
  <h1>Dashboard Monitoring</h1>
 </div>

 <div class="kpi">
  <div class="kpi-card total"><span>Total Laporan</span><h2><?= $total ?></h2></div>
  <div class="kpi-card selesai"><span>Laporan Selesai</span><h2><?= $selesai ?></h2></div>
  <div class="kpi-card proses"><span>Dalam Proses</span><h2><?= $proses ?></h2></div>
  <div class="kpi-card teknisi"><span>Teknisi Aktif</span><h2><?= $teknisi ?></h2></div>
 </div>

 <div class="card">
  <h3>Status Jadwal Teknisi</h3>
  <canvas id="chart" height="90"></canvas>
 </div>

 <div class="card">
  <h3>Laporan Terbaru</h3>
  <table>
   <tr>
    <th>ID</th><th>Pelanggan</th><th>Teknisi</th><th>Status</th><th>Tanggal</th>
   </tr>
   <?php while($r=$data->fetch_assoc()): ?>
   <tr>
    <td>#<?= $r['id_laporan'] ?></td>
    <td><?= $r['nama_pelanggan'] ?></td>
    <td><?= $r['teknisi'] ?></td>
    <td><span class="status <?= strtolower($r['status']) ?>"><?= ucfirst($r['status']) ?></span></td>
    <td><?= date('d M Y',strtotime($r['tanggal_laporan'])) ?></td>
   </tr>
   <?php endwhile ?>
  </table>
 </div>
</div>

<script>
new Chart(chart,{
 type:'bar',
 data:{
  labels:<?= json_encode($labels) ?>,
  datasets:[{
   data:<?= json_encode($values) ?>,
   backgroundColor:['#f59e0b','#22d3ee','#22c55e'],
   borderRadius:10
  }]
 },
 options:{
  plugins:{legend:{display:false}},
  scales:{
   y:{beginAtZero:true,grid:{color:'#e5e7eb'}},
   x:{grid:{display:false}}
  }
 }
});
</script>

</body>
</html>
