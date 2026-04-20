<?php
session_start();
include '../koneksi.php';

$kategoriList = [];
$kategoriQuery = mysqli_query($conn, "SELECT id_kategori, nama_kategori FROM kategori ORDER BY id_kategori ASC");
if ($kategoriQuery) {
    while ($kategoriRow = mysqli_fetch_assoc($kategoriQuery)) {
        $kategoriList[] = $kategoriRow;
    }
}

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit;
}

$id_user = (int) $_SESSION['id_user'];

$query = mysqli_query(
    $conn,
    "SELECT p.*, 
            GROUP_CONCAT(CONCAT(b.judul, ' (x', dp.qty, ')') SEPARATOR ', ') as books
     FROM pesanan p
     LEFT JOIN detail_pesanan dp ON p.id_pesanan = dp.id_pesanan
     LEFT JOIN buku b ON dp.id_buku = b.id_buku
     WHERE p.id_user='$id_user'
     GROUP BY p.id_pesanan
     ORDER BY p.tanggal DESC"
);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Riwayat Pesanan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    <style>
        :root {
            --green-900: #1f4d3b;
            --cream: #f6f7f3;
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

        .kategori-dropdown .dropdown-toggle {
            color: #f0f8f3;
            text-decoration: none;
            font-weight: 700;
            padding: 6px 12px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
        }

        .kategori-dropdown .dropdown-toggle:hover,
        .kategori-dropdown .dropdown-toggle:focus {
            color: #fff;
            background: rgba(255, 255, 255, 0.22);
        }

        .kategori-dropdown .dropdown-menu {
            border-radius: 12px;
            border: 1px solid #d8e4dc;
            min-width: 210px;
        }

        .search-wrap {
            max-width: 260px;
            width: 100%;
        }

        .search-box {
            display: flex;
            align-items: center;
            width: 100%;
            background: #ffffff;
            border: 1.5px solid #232323;
            border-radius: 999px;
            min-height: 38px;
            overflow: hidden;
        }

        .search-input {
            border: none;
            background: transparent;
            box-shadow: none;
            padding: 0 12px;
            height: 38px;
            font-size: 0.88rem;
            font-weight: 600;
            color: #6f6f6f;
        }

        .search-input::placeholder {
            color: #777777;
            opacity: 1;
            font-weight: 700;
        }

        .search-input:focus {
            border: none;
            box-shadow: none;
            background: transparent;
            color: #5f5f5f;
        }

        .search-btn {
            border: none;
            background: transparent;
            height: 38px;
            width: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #000;
            flex-shrink: 0;
            padding: 0;
        }

        .search-btn:hover {
            background: transparent;
            color: #000;
        }

        .cart-icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            padding: 0;
            border: none;
            background: transparent;
        }

        .cart-icon-btn img {
            width: 28px;
            height: 28px;
            display: block;
        }

        .order-card {
            border: 1px solid #d9e6dd;
            border-radius: 14px;
            background: #fff;
        }

        .status-badge {
            font-size: 0.8rem;
            padding: 5px 10px;
            border-radius: 999px;
        }

        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.35rem;
            }

            .nav-links {
                display: none !important;
            }

            .search-wrap {
                display: none !important;
            }

            .search-box {
                min-height: 36px;
            }

            .search-input {
                height: 36px;
                font-size: 0.82rem;
            }

            .search-btn {
                height: 36px;
                width: 38px;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="../index.php">RaBu</a>

            <div class="d-none d-md-flex align-items-center gap-3 text-white fw-semibold nav-links">
                <a href="../index.php" class="text-white text-decoration-none">Beranda</a>
                <div class="dropdown kategori-dropdown">
                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Kategori
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../index.php">Semua</a></li>
                        <?php foreach ($kategoriList as $kategoriItem) { ?>
                            <li><a class="dropdown-item" href="../index.php?kategori=<?php echo (int) $kategoriItem['id_kategori']; ?>"><?php echo htmlspecialchars($kategoriItem['nama_kategori']); ?></a></li>
                        <?php } ?>
                    </ul>
                </div>
                <a href="../about.php" class="text-white text-decoration-none">Tentang Kami</a>
                <a href="../kontak.php" class="text-white text-decoration-none">Kontak</a>
            </div>

            <form method="get" action="../index.php" class="search-wrap d-none d-md-flex ms-auto me-3">
                <div class="search-box">
                    <input type="text" name="q" class="form-control search-input" placeholder="Cari Buku">
                    <button type="submit" class="search-btn" aria-label="Cari Buku" title="Cari Buku">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2.6"/>
                            <path d="M16.5 16.5L22 22" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
            </form>

            <div class="d-flex align-items-center gap-2">
                <a href="cart.php" class="cart-icon-btn" aria-label="Keranjang" title="Keranjang">
                    <img src="../asset/keranjang.png" alt="Keranjang">
                </a>
                <a href="pesanan.php" class="btn btn-light btn-sm">Riwayat Pesanan</a>
                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle d-inline-flex align-items-center gap-2 text-white border-0 bg-transparent" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="../asset/login.png" alt="Profil" width="18" height="18">
                        <span><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Profil'); ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profil.php">Edit Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../logout.php" onclick="return confirm('Yakin ingin logout?')">Keluar</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="order-card shadow-sm p-4">
            <h2 class="fw-bold mb-3">Riwayat Pesanan</h2>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Buku yang Dibeli</th>
                            <th>Total</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($query) === 0) { ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada pesanan.</td>
                            </tr>
                        <?php } else { ?>
                            <?php while ($row = mysqli_fetch_assoc($query)) { ?>
                                <tr>
                                    <td><strong>#<?php echo (int) $row['id_pesanan']; ?></strong></td>
                                    <td>
                                        <?php 
                                        $books = !empty($row['books']) ? $row['books'] : 'Tidak ada data';
                                        echo htmlspecialchars($books);
                                        ?>
                                    </td>
                                    <td>Rp <?php echo number_format($row['total']); ?></td>
                                    <td>
                                        <?php
                                        $metode = strtoupper((string) ($row['metode_pembayaran'] ?? 'COD'));
                                        if ($metode === 'TF') {
                                            echo '<span class="badge bg-primary status-badge">TF</span>';
                                        } else {
                                            echo '<span class="badge bg-success status-badge">COD</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status = strtolower((string) $row['status']);
                                        if ($status === 'pending') {
                                            echo '<span class="badge bg-warning text-dark status-badge">Pending</span>';
                                        } elseif ($status === 'selesai') {
                                            echo '<span class="badge bg-success status-badge">Selesai</span>';
                                        } else {
                                            echo '<span class="badge bg-secondary status-badge">' . htmlspecialchars($row['status']) . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo date('d-m-Y H:i', strtotime($row['tanggal'])); ?></td>
                                </tr>
                            <?php } ?>
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
