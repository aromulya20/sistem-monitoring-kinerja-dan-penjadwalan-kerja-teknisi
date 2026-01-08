<?php
$current = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<aside class="fixed top-0 left-0 w-64 h-screen bg-blue-700 text-white z-40">

  <!-- LOGO -->
  <div class="px-6 py-6 border-b border-blue-600">
    <h2 class="text-xl font-bold flex items-center gap-2">
      <i class="fa-solid fa-screwdriver-wrench"></i>
      SISMONTEK
    </h2>
  </div>

  <!-- MENU -->
  <nav class="px-4 py-6 space-y-2 text-sm">

    <a href="dashboard.php"
       class="flex items-center gap-3 px-4 py-3 rounded-lg
       <?= $current=='dashboard.php' ? 'bg-blue-600' : 'hover:bg-blue-600/60' ?>">
      <i class="fa-solid fa-gauge"></i> Dashboard
    </a>

    <a href="jadwal.php"
       class="flex items-center gap-3 px-4 py-3 rounded-lg
       <?= $current=='jadwal.php' ? 'bg-blue-600' : 'hover:bg-blue-600/60' ?>">
      <i class="fa-solid fa-calendar-days"></i> Jadwal
    </a>

    <a href="pelanggan.php"
       class="flex items-center gap-3 px-4 py-3 rounded-lg
       <?= $current=='pelanggan.php' ? 'bg-blue-600' : 'hover:bg-blue-600/60' ?>">
      <i class="fa-solid fa-users"></i> Pelanggan
    </a>

    <a href="teknisi.php"
       class="flex items-center gap-3 px-4 py-3 rounded-lg
       <?= $current=='teknisi.php' ? 'bg-blue-600' : 'hover:bg-blue-600/60' ?>">
      <i class="fa-solid fa-user-gear"></i> Teknisi
    </a>

    <a href="laporan.php"
       class="flex items-center gap-3 px-4 py-3 rounded-lg
       <?= $current=='laporan.php' ? 'bg-blue-600' : 'hover:bg-blue-600/60' ?>">
      <i class="fa-solid fa-file-lines"></i> Laporan
    </a>

  </nav>

  <!-- LOGOUT -->
  <div class="absolute bottom-0 w-full px-6 py-4 border-t border-blue-600">
    <a href="../auth/logout.php"
       class="flex items-center gap-3 text-red-200 hover:text-white">
      <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
  </div>

</aside>
