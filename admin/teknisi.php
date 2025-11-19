<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}

include '../database/config.php';

/* ============================================================
   ================ DELETE DATA TEKNISI ========================
   ============================================================ */
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM teknisi WHERE id_teknisi = '$id'");
    header("Location: teknisi.php?msg=deleted");
    exit;
}

/* ============================================================
   ================ AMBIL DATA TEKNISI =========================
   ============================================================ */
$pengguna = $conn->query("SELECT id_pengguna, nama FROM pengguna WHERE role = 'teknisi'");

$teknisi = $conn->query("
    SELECT t.id_teknisi, p.nama, t.no_hp, t.alamat, t.id_pengguna
    FROM teknisi t
    JOIN pengguna p ON t.id_pengguna = p.id_pengguna
    ORDER BY t.id_teknisi DESC
");

/* ============================================================
   ================ MODE EDIT =========================
   ============================================================ */
$editMode = false;
$editData = null;

if (isset($_GET['edit'])) {
    $editMode = true;
    $id_edit = $_GET['edit'];

    $editData = $conn->query("
        SELECT * FROM teknisi WHERE id_teknisi = '$id_edit'
    ")->fetch_assoc();
}

/* ============================================================
   ================ PROSES SIMPAN / UPDATE ====================
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id_pengguna = $_POST['id_pengguna'];
    $no_hp = $_POST['no_hp'];
    $alamat = $_POST['alamat'];

    // MODE UPDATE
    if (isset($_POST['update_id'])) {
        $id_update = $_POST['update_id'];

        $conn->query("
            UPDATE teknisi 
            SET id_pengguna = '$id_pengguna', 
                no_hp = '$no_hp', 
                alamat = '$alamat'
            WHERE id_teknisi = '$id_update'
        ");

        header("Location: teknisi.php?msg=updated");
        exit;
    } 
    // MODE INSERT
    else {
        $conn->query("
            INSERT INTO teknisi (id_pengguna, no_hp, alamat)
            VALUES ('$id_pengguna', '$no_hp', '$alamat')
        ");

        header("Location: teknisi.php?msg=added");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manajemen Teknisi</title>

<style>
    body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; display: flex; }
    .sidebar { width: 240px; background: #3f72af; color: white; height: 100vh; padding-top: 20px; position: fixed; }
    .sidebar a { display: block; color: white; padding: 12px 20px; text-decoration: none; }
    .sidebar a:hover, .active { background: #2e5c8a; }

    .main-content { margin-left: 240px; padding: 25px; width: 100%; }
    .card { background: white; padding: 20px; border-radius: 10px; margin-bottom: 25px; box-shadow: 0 3px 7px rgba(0,0,0,0.1); }
    table { width: 100%; border-collapse: collapse; }
    th { background: #3f72af; color: white; padding: 10px; }
    td { padding: 10px; border: 1px solid #ddd; }
    input, select, textarea { width: 100%; padding: 10px; margin-top: 7px; border-radius: 7px; border: 1px solid #bbb; }

    .btn { padding: 6px 12px; border-radius: 5px; text-decoration: none; color: white; font-size: 14px; }
    .btn-edit { background: #17a2b8; }
    .btn-delete { background: #dc3545; }
    .btn-save { background: #3f72af; padding: 10px 20px; display: block; margin-top: 15px; }
</style>
</head>

<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2 style="text-align:center;">🔧 Sismontek</h2>
    <a href="dashboard.php">🏠 Home</a>
    <a href="jadwal.php">🗓 Jadwal</a>
    <a href="tambah_pengguna.php">➕ Tambah Pengguna</a>
    <a href="pelanggan.php">👥 Pelanggan</a>
    <a href="teknisi.php" class="active">🧑‍🔧 Teknisi</a>
    <a href="laporan.php">📊 Laporan Kinerja</a>
    <a href="../auth/logout.php">🚪 Logout</a>
</div>

<!-- Main -->
<div class="main-content">
    <h1>Manajemen Teknisi</h1>

    <!-- Form Tambah / Edit Teknisi -->
    <div class="card">
        <h3><?= $editMode ? "✏️ Edit Teknisi" : "➕ Tambah Teknisi Baru" ?></h3>

        <form method="POST">
            <?php if ($editMode): ?>
                <input type="hidden" name="update_id" value="<?= $editData['id_teknisi'] ?>">
            <?php endif; ?>

            <label>Pilih Pengguna (Teknisi)</label>
            <select name="id_pengguna" required>
                <option value="">-- Pilih Teknisi --</option>

                <?php
                // preselect saat edit
                while ($p = $pengguna->fetch_assoc()):
                    $selected = ($editMode && $editData['id_pengguna'] == $p['id_pengguna']) ? "selected" : "";
                ?>
                    <option value="<?= $p['id_pengguna'] ?>" <?= $selected ?>><?= $p['nama'] ?></option>
                <?php endwhile; ?>
            </select>

            <label>No HP</label>
            <input type="text" name="no_hp" required value="<?= $editMode ? $editData['no_hp'] : '' ?>">

            <label>Alamat</label>
            <textarea name="alamat"><?= $editMode ? $editData['alamat'] : '' ?></textarea>

            <button class="btn btn-save" type="submit">
                <?= $editMode ? "💾 Simpan Perubahan" : "+ Tambah Teknisi" ?>
            </button>
        </form>
    </div>

    <!-- Tabel -->
    <div class="card">
        <h3>Daftar Teknisi</h3>

        <table>
            <tr>
                <th>ID</th>
                <th>Nama Teknisi</th>
                <th>No HP</th>
                <th>Alamat</th>
                <th>Aksi</th>
            </tr>

            <?php if ($teknisi->num_rows > 0): ?>
                <?php while ($t = $teknisi->fetch_assoc()): ?>
                <tr>
                    <td><?= $t['id_teknisi'] ?></td>
                    <td><?= $t['nama'] ?></td>
                    <td><?= $t['no_hp'] ?></td>
                    <td><?= $t['alamat'] ?></td>

                    <td>
                        <a href="teknisi.php?edit=<?= $t['id_teknisi'] ?>" class="btn btn-edit">✏️ Edit</a>
                        <a href="teknisi.php?delete=<?= $t['id_teknisi'] ?>" 
                           class="btn btn-delete"
                           onclick="return confirm('Hapus teknisi ini?')">🗑 Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center;">Belum ada data teknisi.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

</body>
</html>
