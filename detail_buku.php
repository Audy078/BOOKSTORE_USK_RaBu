<?php
session_start();
include 'koneksi.php';

$kategoriList = [];
$kategoriQuery = mysqli_query($conn, "SELECT id_kategori, nama_kategori FROM kategori ORDER BY id_kategori ASC");
if ($kategoriQuery) {
    while ($kategoriRow = mysqli_fetch_assoc($kategoriQuery)) {
        $kategoriList[] = $kategoriRow;
    }
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$query = mysqli_query($conn, "SELECT buku.*, kategori.nama_kategori FROM buku LEFT JOIN kategori ON buku.id_kategori = kategori.id_kategori WHERE buku.id_buku='$id' LIMIT 1");
$buku = mysqli_fetch_assoc($query);

if (!$buku) {
    header("Location: index.php");
    exit;
}

$foto = trim((string) ($buku['foto'] ?? ''));
$fotoPath = 'uploads/buku/' . $foto;
$hasFoto = $foto !== '' && file_exists($fotoPath);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Detail Buku</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">

    <style>
        :root {
            --green-900: #1f4d3b;
            --green-800: #2b654d;
            --green-100: #e8f4ee;
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

        .login-icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            padding: 0;
            border: none;
            background: transparent;
        }

        .login-icon-btn img {
            width: 28px;
            height: 28px;
            display: block;
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

        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.35rem;
            }

            .navbar .container {
                gap: 10px;
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

        .detail-card {
            border-radius: 16px;
            border: 1px solid #d9e6dd;
            background: #fff;
            overflow: hidden;
        }

        .cover-wrap {
            border-right: 1px solid #e3ebe6;
            background: #fbfdfb;
            min-height: 100%;
            padding: 20px;
        }

        .cover-box {
            width: 100%;
            max-width: 260px;
            height: 360px;
            margin: 0 auto;
            border-radius: 14px;
            border: 1px solid #d8e2db;
            overflow: hidden;
            background: linear-gradient(135deg, #2e6a52, #7ebc9f);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-weight: 700;
            padding: 14px;
        }

        .cover-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .info-wrap {
            padding: 20px 22px;
        }

        .book-title {
            color: #276636;
            font-weight: 800;
            font-size: 2.6rem;
            margin-bottom: 10px;
            line-height: 1.2;
        }

        .book-meta {
            color: #6f7b74;
            font-size: 1.05rem;
            margin-bottom: 6px;
        }

        .book-meta strong {
            color: #2f4139;
        }

        .book-price {
            color: #d35400;
            font-size: 1.7rem;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .qty-inline {
            max-width: 170px;
            margin-bottom: 14px;
        }

        .qty-inline .form-label {
            margin-bottom: 6px;
            font-size: 0.9rem;
            color: #5e6a63;
            font-weight: 700;
        }

        .qty-inline .form-control {
            border-radius: 12px;
            border: 1px solid #cfd9d3;
            background: #fbfdfb;
            color: #2f4139;
            box-shadow: none;
            font-weight: 700;
            padding: 10px 12px;
        }

        .qty-inline .form-control:focus {
            border-color: #7ebc9f;
            box-shadow: 0 0 0 0.2rem rgba(126, 188, 159, 0.16);
        }

        .stock-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .stock-label {
            color: #2f4139;
            font-weight: 700;
            font-size: 0.92rem;
        }

        .stock-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #35b36f;
            border-radius: 999px;
            padding: 6px 14px;
            color: #2e9b60;
            font-weight: 700;
            font-size: 0.88rem;
            background: #f4fbf7;
        }

        .stock-check {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #2e9b60;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 800;
            line-height: 1;
        }

        .btn-cart {
            background: var(--green-800);
            border: none;
            border-radius: 999px;
            font-weight: 700;
            padding: 11px 18px;
            color: #fff;
            width: 100%;
        }

        .btn-cart:hover {
            background: var(--green-900);
            color: #fff;
        }

        .qty-wrap {
            max-width: 180px;
            margin-bottom: 14px;
        }

        .btn-login {
            background: var(--green-800);
            border: none;
            border-radius: 999px;
            font-weight: 700;
            padding: 10px 16px;
            color: #fff;
        }

        .btn-login:hover {
            background: var(--green-900);
            color: #fff;
        }

        @media (max-width: 768px) {
            .cover-wrap {
                border-right: none;
                border-bottom: 1px solid #e3ebe6;
                padding: 16px;
            }

            .cover-box {
                max-width: 220px;
                height: 300px;
            }

            .info-wrap {
                padding: 16px;
            }

            .book-title {
                font-size: 1.7rem;
            }

            .book-price {
                font-size: 1.45rem;
            }

            .qty-inline {
                width: 100%;
                max-width: 170px;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="index.php">RaBu</a>

            <div class="d-none d-md-flex align-items-center gap-3 text-white fw-semibold nav-links">
                <a href="index.php" class="text-white text-decoration-none">Beranda</a>
                <div class="dropdown kategori-dropdown">
                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Kategori
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="index.php">Semua</a></li>
                        <?php foreach ($kategoriList as $kategoriItem) { ?>
                            <li><a class="dropdown-item" href="index.php?kategori=<?php echo (int) $kategoriItem['id_kategori']; ?>"><?php echo htmlspecialchars($kategoriItem['nama_kategori']); ?></a></li>
                        <?php } ?>
                    </ul>
                </div>
                <a href="about.php" class="text-white text-decoration-none">Tentang Kami</a>
                <a href="kontak.php" class="text-white text-decoration-none">Kontak</a>
            </div>

            <form method="get" action="index.php" class="search-wrap d-none d-md-flex ms-auto me-3">
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
                <?php if (isset($_SESSION['id_user'])) { ?>
                    <?php if ($_SESSION['role'] == 'admin') { ?>
                        <a href="adminn/dashboard.php" class="btn btn-light btn-sm">Admin</a>
                    <?php } else { ?>
                        <a href="user/cart.php" class="cart-icon-btn" aria-label="Keranjang" title="Keranjang">
                            <img src="asset/keranjang.png" alt="Keranjang">
                        </a>
                        <a href="user/pesanan.php" class="btn btn-light btn-sm">Riwayat Pesanan</a>
                    <?php } ?>
                    <div class="dropdown">
                        <button class="btn btn-sm dropdown-toggle d-inline-flex align-items-center gap-2 text-white border-0 bg-transparent" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="asset/login.png" alt="Profil" width="18" height="18">
                            <span><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Profil'); ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'user') { ?>
                                <li><a class="dropdown-item" href="user/profil.php">Edit Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php } ?>
                            <li><a class="dropdown-item text-danger" href="logout.php" onclick="return confirm('Yakin ingin logout?')">Keluar</a></li>
                        </ul>
                    </div>
                <?php } else { ?>
                    <a href="login.php" class="login-icon-btn" aria-label="Login" title="Login">
                        <img src="asset/login.png" alt="Login">
                    </a>
                    <a href="user/cart.php" class="cart-icon-btn" aria-label="Keranjang" title="Keranjang">
                        <img src="asset/keranjang.png" alt="Keranjang">
                    </a>
                <?php } ?>
            </div>
        </div>
    </nav>

    <!-- ================= CONTENT ================= -->
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="detail-card shadow-sm">
                    <div class="row g-0">
                        <div class="col-md-5 col-lg-4 cover-wrap">
                            <div class="cover-box">
                                <?php if ($hasFoto) { ?>
                                    <img src="<?php echo htmlspecialchars($fotoPath); ?>" alt="<?php echo htmlspecialchars($buku['judul']); ?>">
                                <?php } else { ?>
                                    <?php echo htmlspecialchars($buku['judul']); ?>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="col-md-7 col-lg-8 info-wrap">
                            <h1 class="book-title"><?php echo htmlspecialchars($buku['judul']); ?></h1>
                            <div class="book-meta"><strong>Penulis :</strong> <?php echo htmlspecialchars($buku['penulis']); ?></div>
                            <div class="book-meta mb-3"><strong>Kategori :</strong> <?php echo htmlspecialchars((string) ($buku['nama_kategori'] ?? 'Buku')); ?></div>
                            <div class="stock-row">
                                <span class="stock-label">Stok :</span>
                                <span class="stock-badge">
                                       <?php if ((int) $buku['stok'] > 0) { ?>
                                           <span class="stock-check">&#10003;</span>
                                           Tersedia
                                       <?php } else { ?>
                                           <span style="color: #d9534f;">Habis</span>
                                       <?php } ?>
                                </span>
                            </div>
                            <div class="book-price">Rp <?php echo number_format((int) $buku['harga']); ?></div>
                            
                            <?php if (!isset($_SESSION['id_user'])) { ?>
                                <div class="alert alert-warning mb-3">Silahkan login untuk melakukan pembelian.</div>
                                <a href="login.php" class="btn btn-login">Login</a>
                               <?php } elseif ((int) $buku['stok'] > 0) { ?>
                                <form method="post" action="user/cart.php" class="mb-2">
                                    <input type="hidden" name="id_buku" value="<?php echo (int) $buku['id_buku']; ?>">
                                    <div class="qty-inline">
                                        <label class="form-label">Jumlah</label>
                                        <input type="number" name="qty" value="1" min="1" max="<?php echo (int) $buku['stok']; ?>" class="form-control" required>
                                    </div>
                                    <button type="submit" name="tambah" class="btn btn-cart">Tambah ke Keranjang</button>
                                </form>
                                <a href="index.php" class="btn btn-outline-secondary btn-sm mt-2">Kembali</a>
                               <?php } else { ?>
                                   <div class="alert alert-danger mb-3">Stok buku habis. Silahkan kembali lagi nanti.</div>
                                   <a href="index.php" class="btn btn-outline-secondary btn-sm">Kembali</a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
<?php include 'footer.php'; ?>
</body>

</html>