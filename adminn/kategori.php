<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

/* ======================
   PROSES TAMBAH
====================== */
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama_kategori'];
    mysqli_query($conn, "INSERT INTO kategori (nama_kategori) VALUES ('$nama')");
    header("Location: kategori.php");
}

/* ======================
   PROSES HAPUS
====================== */
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];

    $cekStok = mysqli_query($conn, "SELECT COUNT(*) AS total FROM buku WHERE id_kategori='$id' AND stok > 0");
    $stokData = $cekStok ? mysqli_fetch_assoc($cekStok) : null;
    $totalStokAktif = (int) ($stokData['total'] ?? 0);

    if ($totalStokAktif > 0) {
        header("Location: kategori.php?gagal_hapus=stok");
        exit;
    }

    $cekJumlahBuku = mysqli_query($conn, "SELECT COUNT(*) AS total FROM buku WHERE id_kategori='$id'");
    $jumlahBukuData = $cekJumlahBuku ? mysqli_fetch_assoc($cekJumlahBuku) : null;
    $jumlahBukuTerhapus = (int) ($jumlahBukuData['total'] ?? 0);

    mysqli_begin_transaction($conn);

    try {
        mysqli_query($conn, "DELETE FROM buku WHERE id_kategori='$id'");
        mysqli_query($conn, "DELETE FROM kategori WHERE id_kategori='$id'");
        mysqli_commit($conn);
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        throw $e;
    }

    header("Location: kategori.php?sukses_hapus=1&buku_terhapus=" . $jumlahBukuTerhapus);
    exit;
}

/* ======================
   AMBIL DATA EDIT
====================== */
$data_edit = null;
if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $edit = mysqli_query($conn, "SELECT * FROM kategori WHERE id_kategori='$id_edit'");
    $data_edit = mysqli_fetch_assoc($edit);
}

/* ======================
   PROSES UPDATE
====================== */
if (isset($_POST['update'])) {
    $nama = $_POST['nama_kategori'];
    $id = $_POST['id_kategori'];
    mysqli_query($conn, "UPDATE kategori SET nama_kategori='$nama' WHERE id_kategori='$id'");
    header("Location: kategori.php");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kelola Kategori</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">

    <style>
        :root {
            --green-900: #1f4d3b;
            --green-800: #2b654d;
            --cream: #f6f7f3;
            --line: #dde7e0;
        }

        body {
            background: var(--cream);
            color: #1f2723;
        }

        .navbar {
            background: var(--green-900);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.14);
        }

        .navbar-brand {
            letter-spacing: 0.4px;
            font-size: 1.75rem;
        }

        .nav-links {
            margin-left: 2rem;
        }

        .nav-links a {
            color: #f0f8f3;
            text-decoration: none;
            font-weight: 700;
            padding: 6px 10px;
            border-radius: 999px;
            transition: background 0.2s ease;
        }

        .nav-links a:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.14);
        }

        .nav-links a.active {
            background: rgba(255, 255, 255, 0.22);
            color: #fff;
        }

        .card {
            border-radius: 14px;
            border: 1px solid var(--line);
        }

        .btn-main {
            background: var(--green-800);
            border: none;
            color: #fff;
        }

        .btn-main:hover {
            background: var(--green-900);
            color: #fff;
        }

        .card-header {
            background: #e8f4ee !important;
            color: #1f4d3b !important;
            font-weight: 700;
        }

        .table thead th {
            background: #e8f4ee;
            color: #1f4d3b;
        }

        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.35rem;
            }

            .nav-links {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="dashboard.php">RaBu</a>

            <div class="d-none d-md-flex align-items-center gap-2 fw-semibold nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="kategori.php" class="active">Kategori</a>
                <a href="pesan.php">Pesan</a>
                <a href="buku.php">Buku</a>
                <a href="pesanan.php">Pesanan</a>
            </div>

            <div class="d-flex align-items-center gap-2 ms-auto">
                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle d-inline-flex align-items-center gap-2 text-white border-0 bg-transparent" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="../asset/login.png" alt="Profil" width="18" height="18">
                        <span><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item text-danger" href="../logout.php" onclick="return confirm('Yakin ingin logout?')">Keluar</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">

        <?php if (isset($_GET['sukses_hapus']) && $_GET['sukses_hapus'] === '1') { ?>
            <?php $bukuTerhapus = isset($_GET['buku_terhapus']) ? (int) $_GET['buku_terhapus'] : 0; ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Kategori berhasil dihapus.
                <?php if ($bukuTerhapus > 0) { ?>
                    <?php echo $bukuTerhapus; ?> buku terkait juga ikut terhapus.
                <?php } ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>

        <?php if (isset($_GET['gagal_hapus']) && $_GET['gagal_hapus'] === 'stok') { ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Kategori tidak bisa dihapus karena masih ada buku dengan stok tersedia.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>

        <!-- ===== FORM + TABEL ===== -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                Kelola Kategori Buku
            </div>
            <div class="card-body">
                <form method="post" class="row g-3">

                    <?php if ($data_edit) { ?>
                        <input type="hidden" name="id_kategori" value="<?php echo $data_edit['id_kategori']; ?>">
                    <?php } ?>

                    <div class="col-md-8">
                        <input type="text" name="nama_kategori" class="form-control" placeholder="Nama Kategori"
                            value="<?php echo $data_edit ? $data_edit['nama_kategori'] : ''; ?>" required>
                    </div>

                    <div class="col-md-4 d-flex align-items-center justify-content-md-end">
                        <?php if ($data_edit) { ?>
                            <button type="submit" name="update" class="btn btn-warning btn-sm px-3">
                                Update Kategori
                            </button>
                        <?php } else { ?>
                            <button type="submit" name="tambah" class="btn btn-main btn-sm px-3">
                                Tambah Kategori
                            </button>
                        <?php } ?>
                    </div>

                </form>

                <table class="table table-bordered table-hover text-center align-middle mt-4">
                    <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query = mysqli_query($conn, "
                            SELECT k.*, 
                                   (SELECT COUNT(*) 
                                    FROM buku b 
                                    WHERE b.id_kategori = k.id_kategori AND b.stok > 0) AS buku_stok_aktif
                            FROM kategori k
                        ");
                        while ($row = mysqli_fetch_assoc($query)) {
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $row['nama_kategori']; ?></td>
                                <td>
                                    <a href="?edit=<?php echo $row['id_kategori']; ?>" class="btn btn-sm btn-warning">
                                        Edit
                                    </a>
                                    <a href="?hapus=<?php echo $row['id_kategori']; ?>" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Hapus data kategori ini?')">
                                        Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

<?php include '../footer.php'; ?>
</body>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>

</html>