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
if (isset($_POST['kirim'])) {
    if (!isset($_SESSION['id_user'])) {
        header("Location: login.php");
        exit;
    }

    $id_user = (int) $_SESSION['id_user'];

    $subjek = mysqli_real_escape_string($conn, trim($_POST['subjek']));
    $pesan = mysqli_real_escape_string($conn, trim($_POST['pesan']));
    $pesanLengkap = "Subjek: " . $subjek . "\nPesan: " . $pesan;

    mysqli_query(
        $conn,
        "INSERT INTO pesan (id_user, pesan)
         VALUES ('$id_user', '$pesanLengkap')"
    );

    $sukses = true;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kontak - BookStore</title>
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

        .contact-layout {
            margin-top: 26px;
            margin-bottom: 22px;
        }

        .info-card,
        .contact-card {
            border: 1px solid #dbe6df;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 8px 18px rgba(31, 77, 59, 0.06);
            height: 100%;
        }

        .info-card {
            padding: 22px;
        }

        .info-title {
            color: var(--green-900);
            font-weight: 800;
            margin-bottom: 10px;
        }

        .info-text {
            color: #4f5b55;
            font-size: 0.95rem;
            margin-bottom: 16px;
        }

        .wa-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #25d366;
            border: none;
            color: #fff;
            font-weight: 700;
            padding: 10px 14px;
            border-radius: 10px;
            text-decoration: none;
        }

        .wa-btn:hover {
            background: #21bf5d;
            color: #fff;
        }

        .contact-card {
            padding: 24px;
        }

        .form-group-label {
            font-weight: 800;
            color: #1f2723;
            font-size: 1.02rem;
            margin-bottom: 10px;
            display: block;
        }

        .contact-card .form-control {
            border: 1.5px solid #d8e4dc;
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 0.95rem;
            color: #4f5b55;
            background: #fff;
        }

        .contact-card .form-control::placeholder {
            color: #97a29c;
        }

        .contact-card .form-control:focus {
            border-color: var(--green-700);
            box-shadow: 0 0 0 0.2rem rgba(52, 122, 92, 0.12);
        }

        .message-textarea {
            min-height: 230px;
            resize: vertical;
        }

        .btn-send {
            background: var(--green-900);
            border: none;
            color: #fff;
            border-radius: 999px;
            min-height: 40px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.35px;
            padding: 8px 20px;
            font-size: 0.9rem;
        }

        .btn-send:hover {
            background: var(--green-800);
            color: #fff;
        }

        .btn-cancel {
            border: none;
            background: transparent;
            color: #7b8a83;
            font-weight: 700;
            text-decoration: none;
            padding: 8px 10px;
            display: inline-flex;
            align-items: center;
            min-height: 40px;
            font-size: 0.9rem;
        }

        .btn-cancel:hover {
            color: #586760;
            text-decoration: none;
        }

        .action-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .contact-alert {
            border: 1px solid #b9dec8;
            background: #e8f4ee;
            color: #1f4d3b;
            border-radius: 10px;
            padding: 10px 12px;
            font-weight: 700;
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

    <div class="container contact-layout">
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="info-card">
                    <h4 class="info-title">Hubungi Kami</h4>
                    <p class="info-text">Kalau butuh jawaban cepat, chat admin lewat WhatsApp. Untuk kendala pembayaran atau pertanyaan pengiriman, Anda juga bisa isi form di samping.</p>
                    <a href="https://wa.me/6285780738495?text=Halo%20Admin%20BookStore,%20saya%20ingin%20bertanya"
                       class="wa-btn" target="_blank" rel="noopener noreferrer">
                        WhatsApp
                    </a>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="contact-card">
                    <?php if (isset($sukses)) { ?>
                        <div class="contact-alert mb-3">Pesan Anda berhasil dikirim.</div>
                    <?php } ?>

                    <form method="post">
                        <div class="mb-3">
                            <label class="form-group-label">Subjek Pesan</label>
                            <input
                                type="text"
                                name="subjek"
                                class="form-control"
                                placeholder="Misalnya: Kendala Pembayaran"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-group-label">Detail Pesan Anda</label>
                            <textarea
                                name="pesan"
                                class="form-control message-textarea"
                                placeholder="Ceritakan detail kendala anda atau pertanyaan tulis di sini..."
                                required></textarea>
                        </div>

                        <div class="action-row">
                            <button type="submit" name="kirim" class="btn-send">Kirim Pesan</button>
                            <a href="index.php" class="btn-cancel">Batalkan</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
<?php include 'footer.php'; ?>
</body>

</html>
