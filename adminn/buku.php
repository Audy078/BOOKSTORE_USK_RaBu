<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$keyword = trim((string) ($_GET['q'] ?? ''));
$keywordSql = mysqli_real_escape_string($conn, $keyword);

/* ================= TAMBAH BUKU ================= */
if (isset($_POST['tambah'])) {
    $judul = $_POST['judul'];
    $penulis = $_POST['penulis'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $id_kategori = $_POST['id_kategori'];
    $fotoName = null;

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $fileName = $_FILES['foto']['name'];
        $tmpFile = $_FILES['foto']['tmp_name'];
        $fileSize = (int) $_FILES['foto']['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExt, $allowedExt, true) && $fileSize <= 2 * 1024 * 1024) {
            $uploadDir = '../uploads/buku';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fotoName = 'buku_' . time() . '_' . mt_rand(1000, 9999) . '.' . $fileExt;
            $uploadPath = $uploadDir . '/' . $fotoName;

            if (!move_uploaded_file($tmpFile, $uploadPath)) {
                $fotoName = null;
            }
        }
    }

    mysqli_query(
        $conn,
        "INSERT INTO buku (judul, penulis, harga, stok, id_kategori, foto)
         VALUES ('$judul', '$penulis', '$harga', '$stok', '$id_kategori', " . ($fotoName ? "'$fotoName'" : "NULL") . ")"
    );

    header("Location: buku.php");
}

/* ================= HAPUS BUKU ================= */
if (isset($_GET['hapus'])) {
    $id_hapus = (int) $_GET['hapus'];

    $cekDipakai = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM detail_pesanan WHERE id_buku='$id_hapus'"
    );
    $dipakaiData = $cekDipakai ? mysqli_fetch_assoc($cekDipakai) : null;
    $totalDipakai = (int) ($dipakaiData['total'] ?? 0);

    if ($totalDipakai > 0) {
        header("Location: buku.php?gagal_hapus=terpakai");
        exit;
    }

    $fotoQ = mysqli_query($conn, "SELECT foto FROM buku WHERE id_buku='$id_hapus' LIMIT 1");
    $fotoRow = $fotoQ ? mysqli_fetch_assoc($fotoQ) : null;
    $fotoLama = (string) ($fotoRow['foto'] ?? '');

    if ($fotoLama !== '') {
        $fotoPath = '../uploads/buku/' . $fotoLama;
        if (file_exists($fotoPath)) {
            unlink($fotoPath);
        }
    }

    mysqli_query($conn, "DELETE FROM buku WHERE id_buku='$id_hapus'");
    header("Location: buku.php");
    exit;
}

/* ================= AMBIL DATA EDIT ================= */
$data_edit = null;
if (isset($_GET['edit'])) {
    $id_edit = (int) $_GET['edit'];
    $editQ = mysqli_query($conn, "SELECT * FROM buku WHERE id_buku='$id_edit' LIMIT 1");
    $data_edit = $editQ ? mysqli_fetch_assoc($editQ) : null;
}

/* ================= UPDATE BUKU ================= */
if (isset($_POST['update'])) {
    $id_buku = (int) $_POST['id_buku'];
    $judul = $_POST['judul'];
    $penulis = $_POST['penulis'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $id_kategori = $_POST['id_kategori'];
    $fotoName = trim((string) ($_POST['old_foto'] ?? ''));

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $fileName = $_FILES['foto']['name'];
        $tmpFile = $_FILES['foto']['tmp_name'];
        $fileSize = (int) $_FILES['foto']['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExt, $allowedExt, true) && $fileSize <= 2 * 1024 * 1024) {
            $uploadDir = '../uploads/buku';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $newFotoName = 'buku_' . time() . '_' . mt_rand(1000, 9999) . '.' . $fileExt;
            $uploadPath = $uploadDir . '/' . $newFotoName;

            if (move_uploaded_file($tmpFile, $uploadPath)) {
                if ($fotoName !== '') {
                    $oldPath = $uploadDir . '/' . $fotoName;
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
                $fotoName = $newFotoName;
            }
        }
    }

    $fotoSql = $fotoName !== '' ? "'$fotoName'" : "NULL";
    mysqli_query(
        $conn,
        "UPDATE buku
         SET judul='$judul', penulis='$penulis', harga='$harga', stok='$stok', id_kategori='$id_kategori', foto=$fotoSql
         WHERE id_buku='$id_buku'"
    );

    header("Location: buku.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kelola Buku</title>
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

        .book-thumb {
            width: 56px;
            height: 76px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #d7e3db;
            background: #eef3ef;
        }

        .book-thumb-fallback {
            width: 56px;
            height: 76px;
            border-radius: 8px;
            border: 1px dashed #c7d4cc;
            background: #f2f6f3;
            color: #7a8b83;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
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

    <!-- ================= NAVBAR ================= -->
    <nav class="navbar navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="dashboard.php">RaBu</a>

            <div class="d-none d-md-flex align-items-center gap-2 fw-semibold nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="kategori.php">Kategori</a>
                <a href="pesan.php">Pesan</a>
                <a href="buku.php" class="active">Buku</a>
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

    <!-- ================= CONTENT ================= -->
    <div class="container mt-4">

        <?php if (isset($_GET['gagal_hapus']) && $_GET['gagal_hapus'] === 'terpakai') { ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Buku tidak bisa dihapus karena sudah digunakan pada transaksi pesanan.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>

        <!-- ===== FORM TAMBAH BUKU ===== -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                Tambah Buku
            </div>

            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="row g-3">

                        <?php if ($data_edit) { ?>
                            <input type="hidden" name="id_buku" value="<?php echo (int) $data_edit['id_buku']; ?>">
                            <input type="hidden" name="old_foto" value="<?php echo htmlspecialchars((string) ($data_edit['foto'] ?? '')); ?>">
                        <?php } ?>

                        <div class="col-md-6">
                            <label class="form-label">Judul Buku</label>
                            <input type="text" name="judul" class="form-control" value="<?php echo htmlspecialchars((string) ($data_edit['judul'] ?? '')); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Penulis</label>
                            <input type="text" name="penulis" class="form-control" value="<?php echo htmlspecialchars((string) ($data_edit['penulis'] ?? '')); ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Harga</label>
                            <input type="number" name="harga" class="form-control" value="<?php echo htmlspecialchars((string) ($data_edit['harga'] ?? '')); ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Stok</label>
                            <input type="number" name="stok" class="form-control" value="<?php echo htmlspecialchars((string) ($data_edit['stok'] ?? '')); ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Kategori</label>
                            <select name="id_kategori" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php
                                $kategori = mysqli_query($conn, "SELECT * FROM kategori");
                                while ($k = mysqli_fetch_assoc($kategori)) {
                                    $selected = '';
                                    if ($data_edit && (int) $data_edit['id_kategori'] === (int) $k['id_kategori']) {
                                        $selected = 'selected';
                                    }
                                    ?>
                                    <option value="<?php echo $k['id_kategori']; ?>" <?php echo $selected; ?>>
                                        <?php echo $k['nama_kategori']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Foto Buku</label>
                            <input type="file" name="foto" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                            <small class="text-muted">Format JPG/JPEG/PNG/WEBP, maksimal 2MB.</small>
                            <?php if ($data_edit && !empty($data_edit['foto']) && file_exists('../uploads/buku/' . $data_edit['foto'])) { ?>
                                <div class="mt-2">
                                    <img src="../uploads/buku/<?php echo htmlspecialchars($data_edit['foto']); ?>" alt="Foto Saat Ini" class="book-thumb">
                                </div>
                            <?php } ?>
                        </div>

                    </div>

                    <?php if ($data_edit) { ?>
                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" name="update" class="btn btn-warning">
                                Update Buku
                            </button>
                            <a href="buku.php" class="btn btn-secondary">
                                Batal
                            </a>
                        </div>
                    <?php } else { ?>
                        <button type="submit" name="tambah" class="btn btn-main mt-3">
                            Tambah Buku
                        </button>
                    <?php } ?>
                </form>
            </div>
        </div>

        <!-- ===== LIST BUKU ===== -->
        <div class="card shadow-sm">
            <div class="card-header">
                Data Buku
            </div>

            <div class="card-body table-responsive">
                <form method="get" class="row g-2 mb-3">
                    <div class="col-md-4">
                        <input type="text" name="q" class="form-control" placeholder="Cari judul, penulis, atau kategori..." value="<?php echo htmlspecialchars($keyword); ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-success">Cari</button>
                    </div>
                    <?php if ($keyword !== '') { ?>
                        <div class="col-auto">
                            <a href="buku.php" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    <?php } ?>
                </form>

                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Foto</th>
                            <th>Judul</th>
                            <th>Penulis</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Kategori</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $query = mysqli_query(
                            $conn,
                            "SELECT buku.*, kategori.nama_kategori
                         FROM buku
                         JOIN kategori ON buku.id_kategori = kategori.id_kategori"
                        );

                        if ($keyword !== '') {
                            $query = mysqli_query(
                                $conn,
                                "SELECT buku.*, kategori.nama_kategori
                                 FROM buku
                                 JOIN kategori ON buku.id_kategori = kategori.id_kategori
                                 WHERE buku.judul LIKE '%$keywordSql%' OR buku.penulis LIKE '%$keywordSql%' OR kategori.nama_kategori LIKE '%$keywordSql%'"
                            );
                        }

                        while ($row = mysqli_fetch_assoc($query)) {
                            ?>
                            <tr>
                                <td>
                                    <?php if (!empty($row['foto']) && file_exists('../uploads/buku/' . $row['foto'])) { ?>
                                        <img src="../uploads/buku/<?php echo htmlspecialchars($row['foto']); ?>" alt="Foto Buku" class="book-thumb">
                                    <?php } else { ?>
                                        <span class="book-thumb-fallback">No Img</span>
                                    <?php } ?>
                                </td>
                                <td><?php echo $row['judul']; ?></td>
                                <td><?php echo $row['penulis']; ?></td>
                                <td>Rp <?php echo number_format($row['harga']); ?></td>
                                <td><?php echo $row['stok']; ?></td>
                                <td><?php echo $row['nama_kategori']; ?></td>
                                <td>
                                    <a href="?edit=<?php echo (int) $row['id_buku']; ?>" class="btn btn-sm btn-warning">
                                        Edit
                                    </a>
                                    <a href="?hapus=<?php echo (int) $row['id_buku']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus buku ini?')">
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

    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
<?php include '../footer.php'; ?>
</body>

</html>