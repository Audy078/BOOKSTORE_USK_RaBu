<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$keyword = trim((string) ($_GET['q'] ?? ''));
$keywordSql = mysqli_real_escape_string($conn, $keyword);

/* ===== TANDAI DIBACA ===== */
if (isset($_GET['baca'])) {
    $id = $_GET['baca'];
    mysqli_query(
        $conn,
        "UPDATE pesan SET status='dibaca' WHERE id_pesan='$id'"
    );
    header("Location: pesan.php");
}

/* ===== HAPUS PESAN ===== */
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query(
        $conn,
        "DELETE FROM pesan WHERE id_pesan='$id'"
    );
    header("Location: pesan.php");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pesan dari User</title>
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

        .card-header {
            background: #e8f4ee !important;
            color: #1f4d3b !important;
            font-weight: 700;
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

    <!-- ===== NAVBAR ===== -->
    <nav class="navbar navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="dashboard.php">RaBu</a>

            <div class="d-none d-md-flex align-items-center gap-2 fw-semibold nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="kategori.php">Kategori</a>
                <a href="pesan.php" class="active">Pesan</a>
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

    <!-- ===== CONTENT ===== -->
    <div class="container mt-4">

        <div class="card shadow-sm">
            <div class="card-header">
                Daftar Pesan Masuk
            </div>

            <div class="card-body table-responsive">

                <form method="get" class="row g-2 mb-3">
                    <div class="col-md-4">
                        <input type="text" name="q" class="form-control" placeholder="Cari email atau isi pesan..." value="<?php echo htmlspecialchars($keyword); ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-success">Cari</button>
                    </div>
                    <?php if ($keyword !== '') { ?>
                        <div class="col-auto">
                            <a href="pesan.php" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    <?php } ?>
                </form>

                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="50">No</th>
                            <th>Email User</th>
                            <th>Pesan</th>
                            <th width="160">Tanggal</th>
                            <th width="100">Status</th>
                            <th width="220">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $no = 1;
                        $where = '';
                        if ($keyword !== '') {
                            $where = "WHERE users.email LIKE '%$keywordSql%' OR pesan.pesan LIKE '%$keywordSql%' OR pesan.status LIKE '%$keywordSql%'";
                        }

                        $query = mysqli_query(
                            $conn,
                            "SELECT pesan.*, users.email 
                             FROM pesan 
                             JOIN users ON pesan.id_user = users.id_user
                             $where
                             ORDER BY tanggal DESC"
                        );

                        while ($p = mysqli_fetch_assoc($query)) {
                            ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($p['email'] ?? '-'); ?></td>
                                <td><?= nl2br(htmlspecialchars($p['pesan'])); ?></td>
                                <td><?= date('d-m-Y H:i', strtotime($p['tanggal'])); ?></td>
                                <td class="text-center">
                                    <?php if ($p['status'] == 'baru') { ?>
                                        <span class="badge bg-warning text-dark">Baru</span>
                                    <?php } else { ?>
                                        <span class="badge bg-success">Dibaca</span>
                                    <?php } ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <?php if ($p['status'] == 'baru') { ?>
                                            <a href="?baca=<?= $p['id_pesan']; ?>" class="btn btn-sm btn-success">
                                                Tandai Dibaca
                                            </a>
                                        <?php } ?>

                                        <a href="?hapus=<?= $p['id_pesan']; ?>" onclick="return confirm('Hapus pesan ini?')"
                                            class="btn btn-sm btn-danger">
                                            Hapus
                                        </a>
                                    </div>
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