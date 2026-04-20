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
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tentang Kami - BookStore</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <style>
        :root {
            --green-900: #1f4d3b;
            --green-800: #2b654d;
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

        .about-card {
            border: 1px solid #d9e6dd;
            border-radius: 14px;
            background: #fff;
        }

        .about-title {
            color: #1f4d3b;
            font-weight: 800;
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

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="about-card shadow-sm p-4">
                    <h2 class="about-title mb-3">Tentang Kami</h2>
                    <p>Rak Buku (RaBu) adalah toko buku yang menyediakan berbagai jenis buku, mulai dari fiksi, non-fiksi, hingga buku anak-anak. Kami percaya bahwa buku bukan hanya sumber informasi, tetapi juga jendela untuk melihat dunia dari sudut pandang yang berbeda dan memperluas wawasan pembaca.</p>

                    <p>Melalui website ini, kami ingin membuat proses mencari dan membeli buku jadi lebih mudah, cepat, dan nyaman. Pengunjung dapat menelusuri berbagai kategori, melihat detail buku lengkap, dan melakukan pemesanan dengan langkah yang sederhana. Kami berkomitmen untuk terus memperbarui koleksi agar selalu relevan dengan kebutuhan dan minat pembaca.</p>

                    <p>Terima kasih sudah berkunjung dan mempercayakan kebutuhan bacaan Anda kepada RaBu. Dukungan dan kepercayaan Anda adalah semangat kami untuk terus tumbuh dan memberikan layanan yang lebih baik setiap hari.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
<?php include 'footer.php'; ?>
</body>

</html>

</html>
