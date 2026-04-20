<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

function getCount($conn, $sql)
{
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_row($result);
    return (int) ($row[0] ?? 0);
}

$totalKategori = getCount($conn, "SELECT COUNT(*) FROM kategori");
$totalBuku = getCount($conn, "SELECT COUNT(*) FROM buku WHERE stok > 0");
$pesanBaru = getCount($conn, "SELECT COUNT(*) FROM pesan WHERE status='baru'");
$pesananPending = getCount($conn, "SELECT COUNT(*) FROM pesanan WHERE status='pending'");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">

    <style>
        :root {
            --green-900: #1f4d3b;
            --green-800: #2b654d;
            --green-700: #347a5c;
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
            font-size: 28px;
        }

        .nav-links {
            margin-left: 32px;
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

        .menu-card h5 {
            font-size: 16px;
            font-weight: 700;
            color: #18382c;
        }

        .menu-card .card-body {
            padding: 16px 14px;
        }

        .menu-card p {
            font-size: 14px;
            margin-bottom: 14px;
        }

        .btn-main {
            background: var(--green-800);
            border: none;
            color: #fff;
            border-radius: 999px;
            padding: 7px 18px;
            font-weight: 700;
        }

        .btn-main:hover {
            background: var(--green-900);
            color: #fff;
        }

        .stat-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 14px 16px;
            height: 100%;
            text-align: center;
        }

        .stat-label {
            font-size: 12px;
            color: #6c7b74;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 800;
            color: #1f4d3b;
            line-height: 1.2;
            margin-bottom: 2px;
        }

        .stat-note {
            font-size: 13px;
            color: #73817b;
            margin: 0;
        }

        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 22px;
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
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="kategori.php">Kategori</a>
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

    <!-- ===== CONTENT ===== -->
    <div class="container mt-4">

        <!-- ===== GREETING ===== -->
        <div class="row mb-4">
            <div class="col">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-1">Halo,</h6>
                        <h4 class="fw-bold text-dark">
                            <?php echo $_SESSION['nama']; ?>
                        </h4>
                        <p class="text-muted mb-0">
                            Selamat datang di dashboard admin
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== STATISTIK ===== -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="stat-card shadow-sm">
                    <div class="stat-label">Total Kategori</div>
                    <div class="stat-value"><?php echo $totalKategori; ?></div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="stat-card shadow-sm">
                    <div class="stat-label">Pesan Masuk</div>
                    <div class="stat-value"><?php echo $pesanBaru; ?></div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="stat-card shadow-sm">
                    <div class="stat-label">Total Buku</div>
                    <div class="stat-value"><?php echo $totalBuku; ?></div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="stat-card shadow-sm">
                    <div class="stat-label">Pesanan Pending</div>
                    <div class="stat-value"><?php echo $pesananPending; ?></div>
                </div>
            </div>
        </div>

        <!-- ===== MENU DASHBOARD ===== -->
        <div class="row g-3">

            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm h-100 text-center menu-card">
                    <div class="card-body">
                        <h5> Kelola Kategori</h5>
                        <p class="text-muted">
                            Tambah & atur kategori buku
                        </p>
                        <a href="kategori.php" class="btn btn-main">
                            Buka
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm h-100 text-center menu-card">
                    <div class="card-body">
                        <h5>Pesan Masuk</h5>
                        <p class="text-muted">
                            Lihat pesan masuk dari user
                        </p>
                        <a href="pesan.php" class="btn btn-main">
                            Buka
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm h-100 text-center menu-card">
                    <div class="card-body">
                        <h5>Kelola Buku</h5>
                        <p class="text-muted">
                            Tambah & kelola data buku
                        </p>
                        <a href="buku.php" class="btn btn-main">
                            Buka
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm h-100 text-center menu-card">
                    <div class="card-body">
                        <h5>Pesanan</h5>
                        <p class="text-muted">
                            Lihat data pesanan user
                        </p>
                        <a href="pesanan.php" class="btn btn-main">
                            Buka
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
<?php include '../footer.php'; ?>
</body>

</html>