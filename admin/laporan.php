<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../auth/login.php");
    exit;
}
include '../database/config.php';

/* ===== KPI ===== */
$totalLaporan = $conn->query("SELECT COUNT(*) t FROM laporan")->fetch_assoc()['t'];
$selesai = $conn->query("SELECT COUNT(*) t FROM jadwal WHERE status='selesai'")->fetch_assoc()['t'];
$proses  = $conn->query("SELECT COUNT(*) t FROM jadwal WHERE status='proses'")->fetch_assoc()['t'];
$teknisi = $conn->query("SELECT COUNT(*) t FROM teknisi")->fetch_assoc()['t'];

/* ===== BAR CHART JADWAL PER STATUS ===== */
$statusLabel = ['Dijadwalkan','Proses','Selesai'];
$jadwalValue = [
    (int)$conn->query("SELECT COUNT(*) FROM jadwal WHERE status='dijadwalkan'")->fetch_assoc()['COUNT(*)'],
    (int)$conn->query("SELECT COUNT(*) FROM jadwal WHERE status='proses'")->fetch_assoc()['COUNT(*)'],
    (int)$conn->query("SELECT COUNT(*) FROM jadwal WHERE status='selesai'")->fetch_assoc()['COUNT(*)']
];

/* ===== LINE CHART LAPORAN PER TANGGAL ===== */
$dateLabel=[]; $dateValue=[];
$q2 = $conn->query("
    SELECT DATE(tanggal_laporan) tgl, COUNT(*) total
    FROM laporan
    GROUP BY DATE(tanggal_laporan)
    ORDER BY tgl ASC
");
while($r=$q2->fetch_assoc()){
    $dateLabel[] = date('d M',strtotime($r['tgl']));
    $dateValue[] = $r['total'];
}

/* ===== DATA TABLE ===== */
$data = $conn->query("
    SELECT 
        l.id_laporan,
        p.nama_pelanggan,
        u.nama teknisi,
        j.status,
        l.tanggal_laporan
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
<title>Dashboard | SISMONTEK</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet"
 href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
body{
 margin:0;
 font-family:Inter,system-ui,sans-serif;
 background:#f5f7fb;
 color:#0f172a;
}
.main{
 margin-left:260px;
 padding:34px 38px;
}

/* HEADER */
.header{
 display:flex;
 justify-content:space-between;
 align-items:center;
 margin-bottom:30px;
}
.header h1{
 font-size:26px;
 font-weight:600;
 color:#1e40af;
}

/* KPI */
.kpi{
 display:grid;
 grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
 gap:20px;
 margin-bottom:32px;
}
.kpi-card{
 padding:22px;
 border-radius:18px;
 color:#fff;
 box-shadow:0 12px 30px rgba(0,0,0,.08);
}
.kpi-card span{font-size:13px;opacity:.9}
.kpi-card h2{margin:6px 0 0;font-size:30px}
.total{background:linear-gradient(135deg,#2563eb,#60a5fa)}
.selesai{background:linear-gradient(135deg,#16a34a,#4ade80)}
.proses{background:linear-gradient(135deg,#0891b2,#22d3ee)}
.teknisi{background:linear-gradient(135deg,#7c3aed,#a78bfa)}

/* CARD */
.card{
 background:#fff;
 border-radius:18px;
 padding:26px;
 margin-bottom:30px;
 box-shadow:0 14px 36px rgba(0,0,0,.06);
}
.card h3{
 margin-top:0;
 margin-bottom:18px;
 font-size:18px;
 font-weight:600;
}

/* TABLE */
table{
 width:100%;
 border-collapse:collapse;
}
th{
 text-align:left;
 font-size:13px;
 color:#64748b;
 padding-bottom:12px;
}
td{
 padding:14px 0;
 border-top:1px solid #e5e7eb;
}
.status{
 padding:6px 14px;
 border-radius:999px;
 font-size:12px;
 font-weight:500;
 color:#fff;
}
.status.selesai{background:#22c55e}
.status.proses{background:#06b6d4}
.status.dijadwalkan{background:#f59e0b}

.detail{
 text-decoration:none;
 color:#2563eb;
 font-weight:500;
}
.detail:hover{text-decoration:underline}

@media(max-width:1024px){
 .main{margin-left:0;padding:24px}
 .chart-flex{flex-direction:column;}
}
.chart-flex{display:flex; gap:20px; flex-wrap:wrap;}
.chart-flex .chart-box{flex:1; min-width:300px;}
</style>
</head>

<body>

<?php include __DIR__ . '/../layout/sidebar.php'; ?>

<div class="main">

<div class="header">
 <h1>Dashboard Sistem Monitoring Teknisi</h1>
</div>

<!-- KPI -->
<div class="kpi">
 <div class="kpi-card total"><span>Total Laporan</span><h2><?= $totalLaporan ?></h2></div>
 <div class="kpi-card selesai"><span>Laporan Selesai</span><h2><?= $selesai ?></h2></div>
 <div class="kpi-card proses"><span>Dalam Proses</span><h2><?= $proses ?></h2></div>
 <div class="kpi-card teknisi"><span>Teknisi Aktif</span><h2><?= $teknisi ?></h2></div>
</div>

<!-- CHARTS BERSEBELAHAN -->
<div class="card chart-flex">
  <div class="chart-box">
    <h3>Tren Laporan Berdasarkan Tanggal</h3>
    <canvas id="lineChart" height="150"></canvas>
  </div>
  <div class="chart-box">
    <h3>Statistik Jadwal Berdasarkan Status</h3>
    <canvas id="barChartJadwal" height="150"></canvas>
  </div>
</div>

<!-- TABLE -->
<div class="card">
 <h3>Laporan Terbaru</h3>
 <table>
  <tr>
   <th>ID</th>
   <th>Pelanggan</th>
   <th>Teknisi</th>
   <th>Status</th>
   <th>Tanggal</th>
   <th>Aksi</th>
  </tr>
  <?php while($r=$data->fetch_assoc()): ?>
  <tr>
   <td>#<?= $r['id_laporan'] ?></td>
   <td><?= $r['nama_pelanggan'] ?></td>
   <td><?= $r['teknisi'] ?></td>
   <td><span class="status <?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
   <td><?= date('d M Y',strtotime($r['tanggal_laporan'])) ?></td>
   <td><a class="detail" href="detail_laporan.php?id=<?= $r['id_laporan'] ?>">Detail</a>
</td>
  </tr>
  <?php endwhile ?>
 </table>
</div>

</div>

<script>
// DATA PHP
let lineLabels = <?= json_encode($dateLabel) ?>;
let lineData = <?= json_encode($dateValue) ?>;
let statusLabels = <?= json_encode($statusLabel) ?>;
let jadwalData = <?= json_encode($jadwalValue) ?>;

// LINE CHART
new Chart(document.getElementById('lineChart'),{
    type:'line',
    data:{
        labels: lineLabels,
        datasets:[{
            data: lineData,
            borderColor:'#22c55e',
            backgroundColor:'rgba(34,197,94,.25)',
            tension:.4,
            fill:true
        }]
    },
    options:{scales:{y:{beginAtZero:true}}}
});

// BAR CHART JADWAL
new Chart(document.getElementById('barChartJadwal'),{
    type:'bar',
    data:{
        labels: statusLabels,
        datasets:[{
            label:'Jumlah Jadwal',
            data: jadwalData,
            backgroundColor:['#f59e0b','#06b6d4','#22c55e'],
            borderRadius:10
        }]
    },
    options:{plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}}}
});
</script>

</body>
</html>
