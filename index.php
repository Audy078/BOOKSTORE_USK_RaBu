<?php
session_start();
include 'koneksi.php';

$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
$id_kategori = isset($_GET['kategori']) ? (int) $_GET['kategori'] : 0;

$where = [];
if ($id_kategori > 0) {
    $where[] = "b.id_kategori='" . $id_kategori . "'";
}

if ($keyword !== '') {
    $safeKeyword = mysqli_real_escape_string($conn, $keyword);
    $where[] = "(b.judul LIKE '%$safeKeyword%' OR b.penulis LIKE '%$safeKeyword%')";
}

$sqlFiltered = "SELECT b.* FROM buku b INNER JOIN kategori k ON b.id_kategori = k.id_kategori";
if (!empty($where)) {
    $sqlFiltered .= " WHERE " . implode(" AND ", $where);
}
$sqlFiltered .= " ORDER BY b.id_buku DESC";

$booksFiltered = mysqli_query($conn, $sqlFiltered);

$kategoriList = [];
$kategoriQuery = mysqli_query($conn, "SELECT id_kategori, nama_kategori FROM kategori ORDER BY id_kategori ASC");
if ($kategoriQuery) {
    while ($kategoriRow = mysqli_fetch_assoc($kategoriQuery)) {
        $kategoriList[] = $kategoriRow;
    }
}

$selectedCategoryLabel = '';
foreach ($kategoriList as $kategoriItem) {
    if ((int) $kategoriItem['id_kategori'] === $id_kategori) {
        $selectedCategoryLabel = $kategoriItem['nama_kategori'];
        break;
    }
}
$homeSections = [];
foreach ($kategoriList as $kategoriItem) {
    $homeSections[] = [
        'title' => $kategoriItem['nama_kategori'],
        'ids' => [(int) $kategoriItem['id_kategori']],
        'limit' => 4,
    ];
}
$featuredBooks = mysqli_query($conn, "
    SELECT b.*
    FROM buku b
    INNER JOIN kategori k ON b.id_kategori = k.id_kategori
    ORDER BY b.id_buku DESC
    LIMIT 4
");

function renderBookCards($result, $showCart = false)
{
    if (mysqli_num_rows($result) === 0) {
        echo '<div class="col-12"><div class="empty-state">Buku tidak ditemukan.</div></div>';
        return;
    }

    while ($buku = mysqli_fetch_assoc($result)) {
        $judul = htmlspecialchars($buku['judul']);
        $penulis = htmlspecialchars($buku['penulis']);
        $idBuku = (int) $buku['id_buku'];
        $foto = trim((string) ($buku['foto'] ?? ''));
        $fotoPath = 'uploads/buku/' . $foto;
        $hasFoto = $foto !== '' && file_exists($fotoPath);
        ?>
        <div class="col-6 col-md-4 col-lg-3 col-xl">
            <div class="book-card p-2 h-100">
                <div class="book-cover <?php echo $hasFoto ? 'has-image' : ''; ?> mb-2">
                    <?php if ($hasFoto) { ?>
                        <img src="<?php echo htmlspecialchars($fotoPath); ?>" alt="<?php echo $judul; ?>">
                    <?php } else { ?>
                        <?php echo $judul; ?>
                    <?php } ?>
                </div>
                <div class="book-body px-1 pb-1">
                    <h6 class="mb-1 fw-bold"><?php echo $judul; ?></h6>
                    <div class="book-meta mb-1">By <?php echo $penulis; ?></div>
                    <div class="book-price mb-2">Rp <?php echo number_format($buku['harga']); ?></div>
                    <div class="book-actions">
                        <a href="detail_buku.php?id=<?php echo $idBuku; ?>" class="btn-card">Detail</a>
                        <?php if ($showCart && isset($_SESSION['id_user']) && $_SESSION['role'] === 'user') { ?>
                            <form method="post" action="user/cart.php" class="d-inline m-0">
                                <input type="hidden" name="id_buku" value="<?php echo $idBuku; ?>">
                                <input type="hidden" name="qty" value="1">
                                <button type="submit" name="tambah" class="btn-card btn-card-cart">Keranjang</button>
                            </form>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>BookStore</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
     <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <style>
        :root {
            --green-900: #1f4d3b;
            --green-800: #2b654d;
            --green-700: #347a5c;
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

        .hero {
            min-height: 320px;
            border-radius: 0 0 22px 22px;
            background: linear-gradient(90deg, rgba(20, 58, 42, 0.82), rgba(20, 58, 42, 0.5)),
                url('asset/bg.jpg') center/cover;
            display: flex;
            align-items: center;
            text-align: center;
            color: #ffffff;
            padding: 28px 18px;
        }

        .hero h1 {
            font-size: clamp(1.9rem, 4.6vw, 3rem);
            font-weight: 800;
        }

        .category-hero {
            min-height: 88px;
            border-radius: 0 0 22px 22px;
            display: flex;
            align-items: center;
            padding: 14px 22px;
            background: transparent;
        }

        .category-hero .container {
            background: #658C58;
            border-radius: 14px;
            padding: 10px 14px;
        }

        .category-hero h1 {
            font-size: clamp(1.3rem, 2.6vw, 2rem);
            font-weight: 800;
            margin: 0;
            color: #ffffff;
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

        .kategori-dropdown .dropdown-item.active,
        .kategori-dropdown .dropdown-item:active {
            background: var(--green-700);
            color: #fff;
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

        .btn-main {
            background: #b7c6ff;
            border: none;
            color: #fff;
            border-radius: 999px;
            padding: 8px 20px;
            font-weight: 700;
        }

        .btn-main:hover {
            background: #9fb1fb;
            color: #fff;
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

        .section-title {
            font-weight: 800;
            color: #18382c;
            margin-bottom: 14px;
        }

        .section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .section-head .section-title {
            margin-bottom: 0;
        }

        .category-panel {
            background: #ffffff;
            border: 1px solid #dbe6df;
            border-radius: 18px;
            box-shadow: 0 8px 18px rgba(31, 77, 59, 0.06);
            padding: 18px 18px 20px;
            margin-bottom: 24px;
        }

        .book-card {
            background: #fff;
            border: 1px solid #d8e2db;
            border-radius: 14px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
            max-width: 188px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            padding: 8px;
        }

        .book-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(42, 92, 71, 0.15);
        }

        .book-cover {
            min-height: 198px;
            border-radius: 10px;
            background: #fff;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-weight: 700;
            padding: 0;
            font-size: 1rem;
            overflow: hidden;
            border: 1px solid #e6ece8;
        }

        .book-cover.has-image {
            background: transparent;
            padding: 0;
            min-height: 0;
            border: none;
        }

        .book-cover img {
            width: 100%;
            height: auto;
            object-fit: contain;
            border-radius: 0;
            display: block;
        }

        .book-body {
            display: flex;
            flex-direction: column;
            gap: 3px;
            padding-top: 6px;
        }

        .book-meta {
            font-size: 0.72rem;
            color: #6f7b74;
        }

        .book-price {
            font-weight: 800;
            color: #d35400;
            font-size: 0.82rem;
        }

        .book-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .book-actions form {
            margin: 0;
            display: flex;
        }

        .book-card h6 {
            margin-bottom: 2px !important;
            color: #1f2723;
            font-size: 0.86rem;
            line-height: 1.2;
            text-transform: none;
            letter-spacing: 0;
        }

        .book-card .book-body {
            text-align: left;
        }

        .btn-card {
            border: none;
            border-radius: 999px;
            background: #bac8ff;
            color: #2f3c78;
            font-weight: 700;
            padding: 0 9px;
            font-size: 0.62rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 68px;
            height: 26px;
            line-height: 1;
            white-space: nowrap;
        }

        .btn-card:hover {
            background: #a8b8fb;
            color: #22316d;
        }

        .btn-card-cart {
            background: #d9efe3;
            color: #1f4d3b;
            appearance: none;
            -webkit-appearance: none;
        }

        .btn-card-cart:hover {
            background: #c9e8d7;
            color: #174131;
        }

        .btn-see-all {
            border: none;
            border-radius: 999px;
            background: var(--green-800);
            color: #fff;
            font-weight: 700;
            padding: 8px 22px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-see-all:hover {
            background: var(--green-900);
            color: #fff;
        }

        .empty-state {
            background: #fff;
            border: 1px dashed #c6d5cb;
            border-radius: 12px;
            padding: 26px;
            text-align: center;
            color: #6d7a74;
        }

        @media (max-width: 768px) {
            .hero {
                min-height: 280px;
                border-radius: 0 0 16px 16px;
            }

            .category-hero {
                min-height: 170px;
                border-radius: 0 0 16px 16px;
                padding: 16px 16px 30px;
                background-size: 34px 34px, 100% 100%;
            }

            .category-hero h1 {
                font-size: 1.95rem;
            }

            .navbar-brand {
                font-size: 1.35rem;
            }

            .menu-kategori {
                display: none;
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

            .category-hero {
                min-height: 72px;
                padding: 12px 16px;
            }

            .category-hero h1 {
                font-size: 1.2rem;
            }

            .book-card {
                max-width: 162px;
                padding: 7px;
            }

            .book-cover {
                min-height: 164px;
            }

            .book-card h6 {
                font-size: 0.8rem;
            }

            .book-meta {
                font-size: 0.68rem;
            }

            .book-price {
                font-size: 0.78rem;
            }

            .section-head {
                align-items: flex-start;
                flex-direction: column;
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

    <?php if ($keyword === '' && $id_kategori === 0) { ?>
        <section class="hero mb-4">
            <div class="container">
                <h1>Selamat Datang di RaBu</h1>
                <p class="lead mb-4">Temukan berbagai koleksi buku fiksi, non-fiksi, dan anak anak dengan mudah.</p>
            </div>
        </section>
    <?php } else { ?>
        <?php
        $bannerTitle = 'Kategori';
        if ($id_kategori > 0 && $selectedCategoryLabel !== '') {
            $bannerTitle = $selectedCategoryLabel;
        } elseif ($keyword !== '') {
            $bannerTitle = 'Hasil Pencarian';
        }
        ?>
        <section class="category-hero mb-4">
            <div class="container">
                <h1><?php echo htmlspecialchars($bannerTitle); ?></h1>
            </div>
        </section>
    <?php } ?>

    <div class="container" id="kategori">
        <?php if ($keyword !== '' || $id_kategori > 0) { ?>
            <div class="row g-3">
                <?php renderBookCards($booksFiltered, true); ?>
            </div>
        <?php } else { ?>
            <section class="category-panel">
                <h3 class="section-title">Buku Terlaris :</h3>
                <div class="row g-3">
                    <?php renderBookCards($featuredBooks, true); ?>
                </div>
            </section>

            <?php foreach ($homeSections as $section) { ?>
                <?php
                $idList = implode(',', array_map('intval', $section['ids']));
                $firstCategoryId = (int) $section['ids'][0];
                $booksBySection = mysqli_query(
                    $conn,
                    "SELECT * FROM buku WHERE id_kategori IN ($idList) ORDER BY id_buku DESC LIMIT " . (int) $section['limit']
                );
                ?>
                <section class="category-panel">
                    <div class="section-head">
                        <h3 class="section-title"><?php echo $section['title']; ?> :</h3>
                        <a href="index.php?kategori=<?php echo $firstCategoryId; ?>" class="btn-see-all">Lihat Semua</a>
                    </div>
                    <div class="row g-3">
                        <?php renderBookCards($booksBySection, true); ?>
                    </div>
                </section>
            <?php } ?>
        <?php } ?>
    </div>

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
<?php include 'footer.php'; ?>
</body>

</html>