<?php
// Shared sidebar for admin pages
$current = basename($_SERVER['PHP_SELF']);
?>

<style>
/* Shared admin sidebar styles */
.sidebar{
 width:260px;
 height:100vh;
 position:fixed;
 background:linear-gradient(180deg,#1e3a8a,#2563eb);
 color:#fff;
 z-index:1000;
}
.brand{
 padding:26px;
 font-size:20px;
 font-weight:700;
 letter-spacing:.5px;
}
.nav a{
 display:block;
 padding:14px 26px;
 color:#e0e7ff;
 text-decoration:none;
 font-size:14px;
}
.nav a.active,
.nav a:hover{
 background:rgba(255,255,255,.18);
}

/* Keep layout consistent for pages */
.main{margin-left:260px;padding:30px}

/* Mobile */
.menu{display:none}
@media(max-width:768px){
 .sidebar{left:-100%}
 .sidebar.show{left:0}
 .main{margin:0;padding:22px}
 .menu{display:block;font-size:22px;background:none;border:none;color:var(--text)}
}
</style>

<div class="sidebar" id="sidebar">
 <div class="brand">🔧 SISMONTEK</div>
 <div class="nav">
  <a href="dashboard.php" class="<?= $current=='dashboard.php' ? 'active' : '' ?>">Dashboard</a>
  <a href="jadwal.php" class="<?= $current=='jadwal.php' ? 'active' : '' ?>">Jadwal</a>
  <a href="pelanggan.php" class="<?= $current=='pelanggan.php' ? 'active' : '' ?>">Pelanggan</a>
  <a href="teknisi.php" class="<?= $current=='teknisi.php' ? 'active' : '' ?>">Teknisi</a>
  <a href="laporan.php" class="<?= $current=='laporan.php' ? 'active' : '' ?>">Laporan</a>
  <a href="tambah_pengguna.php" class="<?= $current=='tambah_pengguna.php' ? 'active' : '' ?>">Tambah Pengguna</a>
  <a href="../auth/logout.php">Logout</a>
 </div>
</div>

<script>
// Allow menu button to toggle sidebar on mobile
document.querySelector('.menu')?.addEventListener('click', function(){
  document.getElementById('sidebar')?.classList.toggle('show');
});
</script>