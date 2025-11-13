<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit;
}
include '../database/config.php';

// ================== PROSES TAMBAH PELANGGAN ==================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah'])) {
    $nama_pelanggan = $_POST['nama_pelanggan'];
    $alamat = $_POST['alamat'];
    $telepon = $_POST['nomer_telepon'];
    $email = $_POST['email'];
    $paket = $_POST['paket'];

    $sql = "INSERT INTO pelanggan (nama_pelanggan, alamat, nomer_telepon, email, paket)
            VALUES ('$nama_pelanggan', '$alamat', '$telepon', '$email', '$paket')";

    if ($conn->query($sql)) {
        $success = "✅ Data pelanggan berhasil ditambahkan!";
    } else {
        $error = "❌ Terjadi kesalahan: " . $conn->error;
    }
}

// ================== PROSES HAPUS PELANGGAN ==================
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $conn->query("DELETE FROM pelanggan WHERE id_pelanggan = '$id'");
    header("Location: pelanggan.php");
    exit;
}

// ================== PROSES EDIT PELANGGAN ==================
$editMode = false;
if (isset($_GET['edit'])) {
    $editMode = true;
    $id_edit = $_GET['edit'];
    $result = $conn->query("SELECT * FROM pelanggan WHERE id_pelanggan = '$id_edit'");
    $editData = $result->fetch_assoc();
}

if (isset($_POST['update'])) {
    $id_pelanggan = $_POST['id_pelanggan'];
    $nama_pelanggan = $_POST['nama_pelanggan'];
    $alamat = $_POST['alamat'];
    $telepon = $_POST['nomer_telepon'];
    $email = $_POST['email'];
    $paket = $_POST['paket'];

    $sql_update = "UPDATE pelanggan SET 
        nama_pelanggan='$nama_pelanggan',
        alamat='$alamat',
        nomer_telepon='$telepon',
        email='$email',
        paket='$paket'
        WHERE id_pelanggan='$id_pelanggan'";

    if ($conn->query($sql_update)) {
        header("Location: pelanggan.php");
        exit;
    } else {
        $error = "❌ Gagal memperbarui data: " . $conn->error;
    }
}

// ================== AMBIL DATA PELANGGAN ==================
$pelanggan = $conn->query("SELECT * FROM pelanggan ORDER BY id_pelanggan DESC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pelanggan | Sismontek</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            display: flex;
        }

        /* Sidebar */
        .sidebar {
            width: 240px;
            background-color: #3f72af;
            color: white;
            height: 100vh;
            padding-top: 20px;
            position: fixed;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 40px;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 14px 25px;
            transition: 0.3s;
            font-size: 15px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #2e5c8a;
        }

        /* Main content */
        .main-content {
            margin-left: 240px;
            padding: 30px;
            width: 100%;
        }

        h1 {
            color: #3f72af;
            margin-bottom: 20px;
        }

        .form-card,
        .table-card {
            background-color: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
        }

        .form-card h3,
        .table-card h3 {
            margin-top: 0;
            color: #3f72af;
        }

        form input,
        form select,
        form textarea {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }

        form button {
            background-color: #3f72af;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 15px;
            transition: 0.3s;
        }

        form button:hover {
            background-color: #2e5c8a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th {
            background-color: #3f72af;
            color: white;
            padding: 10px;
            text-align: left;
        }

        td {
            padding: 10px;
        }

        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .alert.success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .alert.error {
            background: #ffebee;
            color: #c62828;
        }

        .action-link {
            text-decoration: none;
            margin-right: 8px;
        }

        .edit-link {
            color: blue;
        }

        .delete-link {
            color: red;
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>🔧 Sismontek</h2>
        <a href="dashboard.php">🏠 Home</a>
        <a href="jadwal.php">🗓 Jadwal</a>
        <a href="tambah_pengguna.php">➕ Tambah Pengguna</a>
        <a href="pelanggan.php" class="active">👥 Pelanggan</a>
        <a href="teknisi.php">🧑‍🔧 Teknisi</a>
        <a href="laporan.php">📊 Laporan Kinerja</a>
        <a href="../auth/logout.php">🚪 Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1>Manajemen Data Pelanggan</h1>

        <?php if (isset($success)) echo "<div class='alert success'>$success</div>"; ?>
        <?php if (isset($error)) echo "<div class='alert error'>$error</div>"; ?>

        <!-- Form Input/Edit Pelanggan -->
        <div class="form-card">
            <h3><?= $editMode ? '✏️ Edit Data Pelanggan' : '➕ Tambah Pelanggan Baru'; ?></h3>
            <form method="POST" action="">
                <?php if ($editMode): ?>
                    <input type="hidden" name="id_pelanggan" value="<?= $editData['id_pelanggan']; ?>">
                <?php endif; ?>

                <label for="nama_pelanggan">Nama Pelanggan</label>
                <input type="text" name="nama_pelanggan" required value="<?= $editMode ? $editData['nama_pelanggan'] : ''; ?>">

                <label for="alamat">Alamat</label>
                <textarea name="alamat" rows="3" required><?= $editMode ? $editData['alamat'] : ''; ?></textarea>

                <label for="nomer_telepon">Nomor Telepon</label>
                <input type="text" name="nomer_telepon" required value="<?= $editMode ? $editData['nomer_telepon'] : ''; ?>">

                <label for="email">Email</label>
                <input type="email" name="email" required value="<?= $editMode ? $editData['email'] : ''; ?>">

                <label for="paket">Paket Layanan</label>
                <select name="paket" required>
                    <option value="">-- Pilih Paket --</option>
                    <option value="Basic" <?= $editMode && $editData['paket'] == 'Basic' ? 'selected' : ''; ?>>Basic</option>
                    <option value="Standard" <?= $editMode && $editData['paket'] == 'Standard' ? 'selected' : ''; ?>>Standard</option>
                    <option value="Premium" <?= $editMode && $editData['paket'] == 'Premium' ? 'selected' : ''; ?>>Premium</option>
                </select>

                <?php if ($editMode): ?>
                    <button type="submit" name="update">💾 Simpan Perubahan</button>
                    <a href="pelanggan.php" class="action-link" style="margin-left:10px;">❌ Batal</a>
                <?php else: ?>
                    <button type="submit" name="tambah">+ Tambah Pelanggan</button>
                <?php endif; ?>
            </form>
        </div>

        <!-- Tabel Pelanggan -->
        <div class="table-card">
            <h3>📋 Daftar Pelanggan</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Alamat</th>
                    <th>Telepon</th>
                    <th>Email</th>
                    <th>Paket</th>
                    <th>Aksi</th>
                </tr>
                <?php if ($pelanggan && $pelanggan->num_rows > 0): ?>
                    <?php while ($p = $pelanggan->fetch_assoc()): ?>
                        <tr>
                            <td><?= $p['id_pelanggan']; ?></td>
                            <td><?= $p['nama_pelanggan']; ?></td>
                            <td><?= $p['alamat']; ?></td>
                            <td><?= $p['nomer_telepon']; ?></td>
                            <td><?= $p['email']; ?></td>
                            <td><?= $p['paket']; ?></td>
                            <td>
                                <a href="?edit=<?= $p['id_pelanggan']; ?>" class="action-link edit-link">✏️ Edit</a>
                                <a href="?hapus=<?= $p['id_pelanggan']; ?>" class="action-link delete-link" onclick="return confirm('Yakin ingin menghapus data ini?');">🗑 Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center;">Belum ada data pelanggan.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

</body>

</html>
