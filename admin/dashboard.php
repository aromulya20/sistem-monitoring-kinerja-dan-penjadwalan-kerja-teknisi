<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}

include '../database/config.php';

/*
 Dashboard: ambil data untuk dua chart (status jadwal & jumlah laporan per hari)
 serta ringkasan tugas terbaru.
*/

// Data tugas terbaru (5)
$jadwalQuery = $conn->query("
    SELECT deskripsi_pekerjaan, status, tanggal_jadwal
    FROM jadwal
    ORDER BY tanggal_jadwal DESC
    LIMIT 5
");

// Data untuk chart status jadwal
$statusQuery = $conn->query("SELECT status, COUNT(*) AS jumlah FROM jadwal GROUP BY status");
$status_labels = array();
$status_values = array();
if ($statusQuery) {
    while ($row = $statusQuery->fetch_assoc()) {
        $status_labels[] = ucfirst($row['status']);
        $status_values[] = (int)$row['jumlah'];
    }
}

// Data untuk chart jumlah laporan per hari
$laporanQuery = $conn->query("
    SELECT tanggal_laporan, COUNT(*) AS total
    FROM laporan
    GROUP BY tanggal_laporan
    ORDER BY tanggal_laporan ASC
");
$laporan_tanggal = array();
$laporan_total = array();
if ($laporanQuery) {
    while ($row = $laporanQuery->fetch_assoc()) {
        $laporan_tanggal[] = $row['tanggal_laporan'];
        $laporan_total[] = (int)$row['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Dashboard Admin | Sismontek</title>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style>
:root{
  --primary: #3f72af;
  --light-bg: #f4f6fb;
  --card: #ffffff;
}
*{box-sizing:border-box}
body{
  margin:0;
  font-family: "Poppins", sans-serif;
  background:var(--light-bg);
  color:#243240;
  height:100vh;
  display:flex;
}

/* Sidebar */
.sidebar{
  width:240px;
  background:var(--primary);
  color:#fff;
  height:100vh;
  position:fixed;
  left:0;
  top:0;
  padding-top:20px;
  display:flex;
  flex-direction:column;
}
.sidebar h2{
  text-align:center;
  margin:0 0 20px 0;
  font-weight:600;
}
.sidebar a{
  color:#fff;
  text-decoration:none;
  padding:12px 20px;
  display:block;
  transition:background .15s;
}
.sidebar a:hover, .sidebar a.active{ background:#2e5c8a; }

/* Main area */
.main {
  margin-left:240px;
  width:calc(100% - 240px);
  display:flex;
  flex-direction:column;
  min-height:100vh;
}

/* Header */
header{
  background:#fff;
  padding:16px 28px;
  box-shadow:0 2px 8px rgba(0,0,0,0.06);
  display:flex;
  justify-content:space-between;
  align-items:center;
  position:sticky;
  top:0;
  z-index:5;
}
header h2{ color:var(--primary); margin:0; font-size:18px; }
.logout{ background:#e74c3c; color:#fff; padding:8px 12px; border-radius:6px; text-decoration:none; }

/* Content */
.content{
  padding:26px;
  overflow:auto;
  flex:1;
}

/* Chart row */
.chart-row{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:20px;
  margin-bottom:20px;
}
.chart-card{
  background:var(--card);
  padding:18px;
  border-radius:12px;
  box-shadow:0 6px 18px rgba(63,114,175,0.08);
}
.chart-card h3{ margin:0 0 12px 0; color:var(--primary); }

/* Welcome & table */
.card{
  background:var(--card);
  border-radius:12px;
  padding:18px;
  box-shadow:0 6px 18px rgba(63,114,175,0.06);
  margin-bottom:18px;
}
.welcome{ display:flex; justify-content:space-between; gap:10px; align-items:center; flex-wrap:wrap; }
.welcome h3{ margin:0; color:var(--primary); }

/* Table */
.table{
  width:100%;
  border-collapse:collapse;
  margin-top:12px;
}
.table th{
  background:var(--primary);
  color:#fff;
  padding:10px;
  text-align:left;
}
.table td{
  padding:10px;
  border-bottom:1px solid #eee;
}

/* Responsive */
@media(max-width:900px){
  .chart-row{ grid-template-columns:1fr; }
  .sidebar{ position:relative; width:100%; height:auto; flex-direction:row; padding:10px; }
  .main{ margin-left:0; width:100%; }
  header{ padding:12px; }
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2>🔧 Sismontek</h2>
  <a href="dashboard.php" class="active">🏠 Home</a>
  <a href="jadwal.php">🗓 Jadwal</a>
  <a href="tambah_pengguna.php">➕ Tambah Pengguna</a>
  <a href="pelanggan.php">👥 Pelanggan</a>
  <a href="teknisi.php">🧑‍🔧 Teknisi</a>
  <a href="laporan.php">📊 Laporan Kinerja</a>
  <a href="../auth/logout.php">🚪 Logout</a>
</div>

<!-- Main -->
<div class="main">
  <header>
    <h2>Dashboard Admin</h2>
    <a href="../auth/logout.php" class="logout">Logout</a>
  </header>

  <div class="content">

     <!-- Welcome -->
    <div class="card">
      <div class="welcome">
        <div>
          <h3>Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama']); ?> 👋</h3>
          <div style="color:#6b7280;font-size:14px;">Anda login sebagai <strong><?php echo ucfirst(htmlspecialchars($_SESSION['role'])); ?></strong></div>
        </div>
        <div style="display:flex; gap:10px; align-items:center;"></div>
      </div>
    </div>

    <!-- Charts on top (2 columns) -->
    <div class="chart-row">
      <div class="chart-card">
        <h3>📊 Status Jadwal Teknisi</h3>
        <canvas id="statusChart" aria-label="Status Jadwal Chart" role="img"></canvas>
      </div>

      <div class="chart-card">
        <h3>📈 Jumlah Laporan per Hari</h3>
        <canvas id="laporanChart" aria-label="Jumlah Laporan Chart" role="img"></canvas>
      </div>
    </div>

    <!-- Recent tasks table -->
    <div class="card">
      <h3 style="color:var(--primary); margin:0 0 10px 0;">Tugas Teknisi Terbaru</h3>
      <table class="table" role="table" aria-label="Tugas Teknisi Terbaru">
        <thead>
          <tr>
            <th>Deskripsi</th>
            <th>Status</th>
            <th>Tanggal</th>
          </tr>
        </thead>
        <tbody>
        <?php
        if ($jadwalQuery && $jadwalQuery->num_rows > 0) {
            while ($r = $jadwalQuery->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($r['deskripsi_pekerjaan']) . '</td>';
                echo '<td>' . ucfirst(htmlspecialchars($r['status'])) . '</td>';
                echo '<td>' . htmlspecialchars($r['tanggal_jadwal']) . '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="3">Belum ada tugas terbaru.</td></tr>';
        }
        ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<!-- Charts script -->
<script>
const statusLabels = <?php echo json_encode($status_labels, JSON_UNESCAPED_UNICODE); ?>;
const statusData = <?php echo json_encode($status_values, JSON_UNESCAPED_UNICODE); ?>;

const laporanLabels = <?php echo json_encode($laporan_tanggal, JSON_UNESCAPED_UNICODE); ?>;
const laporanData = <?php echo json_encode($laporan_total, JSON_UNESCAPED_UNICODE); ?>;

// Status Bar Chart
const ctxStatus = document.getElementById('statusChart').getContext('2d');
new Chart(ctxStatus, {
    type: 'bar',
    data: {
        labels: statusLabels,
        datasets: [{
            label: 'Jumlah Jadwal',
            data: statusData,
            backgroundColor: ['#f9a825', '#29b6f6', '#66bb6a'],
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Jumlah' } }
        }
    }
});

// Laporan Line Chart
const ctxLaporan = document.getElementById('laporanChart').getContext('2d');
new Chart(ctxLaporan, {
    type: 'line',
    data: {
        labels: laporanLabels,
        datasets: [{
            label: 'Jumlah Laporan',
            data: laporanData,
            borderColor: '#3f72af',
            backgroundColor: 'rgba(63,114,175,0.15)',
            fill: true,
            tension: 0.3,
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { position: 'bottom' } },
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Jumlah Laporan' } },
            x: { title: { display: true, text: 'Tanggal' } }
        }
    }
});
</script>

</body>
</html>