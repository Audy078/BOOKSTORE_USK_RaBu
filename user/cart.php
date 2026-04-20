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

if (isset($_POST['tambah'])) {
    $id_buku = (int) $_POST['id_buku'];
    $qty = isset($_POST['qty']) ? (int) $_POST['qty'] : 1;
    if ($qty < 1) {
        $qty = 1;
    }

    $stokQ = mysqli_query($conn, "SELECT stok FROM buku WHERE id_buku='$id_buku' LIMIT 1");
    $stokRow = $stokQ ? mysqli_fetch_assoc($stokQ) : null;
    $stokTersedia = (int) ($stokRow['stok'] ?? 0);

    if ($stokTersedia <= 0) {
        echo "<script>alert('Stok buku habis.'); window.location='../detail_buku.php?id=$id_buku';</script>";
        exit;
    }

    if ($qty > $stokTersedia) {
        $qty = $stokTersedia;
    }

    $cek = mysqli_query(
        $conn,
        "SELECT * FROM cart WHERE id_user='$id_user' AND id_buku='$id_buku'"
    );

    if ($cek && mysqli_num_rows($cek) > 0) {
        $currentRow = mysqli_fetch_assoc($cek);
        $currentQty = (int) ($currentRow['qty'] ?? 0);
        $newQty = $currentQty + $qty;

        if ($newQty > $stokTersedia) {
            $newQty = $stokTersedia;
        }

        mysqli_query(
            $conn,
            "UPDATE cart SET qty = $newQty WHERE id_user='$id_user' AND id_buku='$id_buku'"
        );
    } else {
        mysqli_query(
            $conn,
            "INSERT INTO cart (id_user, id_buku, qty) VALUES ('$id_user', '$id_buku', '$qty')"
        );
    }

    header("Location: cart.php");
    exit;
}

if (isset($_POST['hapus'])) {
    $id_cart = isset($_POST['id_cart']) ? (int) $_POST['id_cart'] : 0;
    if ($id_cart > 0) {
        mysqli_query($conn, "DELETE FROM cart WHERE id_cart='$id_cart' AND id_user='$id_user'");
    }

    header("Location: cart.php");
    exit;
}

$rows = [];
$total = 0;
$totalItems = 0;

$query = mysqli_query(
    $conn,
    "SELECT cart.*, buku.judul, buku.harga, buku.penulis, buku.foto
     FROM cart
     JOIN buku ON cart.id_buku = buku.id_buku
     WHERE cart.id_user='$id_user'"
);

if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
        $foto = trim((string) ($row['foto'] ?? ''));
        $fotoPath = '../uploads/buku/' . $foto;
        $row['has_foto'] = $foto !== '' && file_exists($fotoPath);
        $row['foto_path'] = $fotoPath;
        $row['subtotal'] = (int) $row['harga'] * (int) $row['qty'];
        $rows[] = $row;
        $total += $row['subtotal'];
        $totalItems += (int) $row['qty'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Keranjang</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">

    <style>
        :root {
            --green-900: #1f4d3b;
            --green-800: #2b654d;
            --green-700: #347a5c;
            --cream: #f6f7f3;
            --line: #dde7e0;
            --accent: #2f7a5b;
            --text-soft: #76827c;
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

        .page-wrap {
            padding: 22px 0 30px;
        }

        .page-label {
            color: #7a8b83;
            letter-spacing: 1.6px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.76rem;
            margin-bottom: 4px;
        }

        .page-title {
            font-size: clamp(1.3rem, 2.2vw, 1.75rem);
            font-weight: 800;
            margin-bottom: 14px;
        }

        .page-title span {
            color: var(--accent);
        }

        .title-box {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 10px 14px;
            display: inline-block;
            margin-bottom: 14px;
        }

        .title-box .page-title {
            margin-bottom: 0;
        }

        .cart-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 6px 12px 0;
            overflow: hidden;
        }

        .cart-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .cart-table thead th {
            color: #9b8b75;
            font-size: 0.62rem;
            letter-spacing: 1.3px;
            text-transform: uppercase;
            padding: 14px 12px;
            border-bottom: 1px solid var(--line);
        }

        .cart-table td {
            padding: 14px 12px;
            border-bottom: 1px solid #eef3ef;
            vertical-align: middle;
        }

        .cart-table tbody tr:last-child td {
            border-bottom: none;
        }

        .book-info {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 220px;
        }

        .book-thumb {
            width: 54px;
            height: 76px;
            border-radius: 8px;
            background: linear-gradient(145deg, #2b654d, #85c2a2);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.95rem;
            flex-shrink: 0;
            overflow: hidden;
        }

        .book-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .book-name {
            font-size: 0.88rem;
            font-weight: 700;
            line-height: 1.2;
            margin: 0 0 2px;
        }

        .book-author {
            margin: 0;
            color: var(--text-soft);
            font-size: 0.74rem;
        }

        .price,
        .subtotal {
            font-size: 1.08rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .price {
            color: #2a3f35;
        }

        .subtotal {
            color: var(--accent);
        }

        .qty {
            font-size: 0.95rem;
            font-weight: 700;
            color: #2a3f35;
            text-align: center;
            min-width: 40px;
            display: inline-block;
        }

        .summary-card {
            background: linear-gradient(165deg, #2b654d, #1f4d3b);
            color: #fff;
            border-radius: 22px;
            padding: 20px 18px;
        }

        .summary-title {
            font-size: 1.18rem;
            font-weight: 800;
            margin-bottom: 14px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            font-size: 0.83rem;
            margin-bottom: 8px;
        }

        .summary-divider {
            border: none;
            height: 1px;
            background: rgba(255, 255, 255, 0.25);
            margin: 12px 0 14px;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 10px;
            margin-bottom: 14px;
        }

        .summary-total-label {
            color: #e8ddd1;
            font-size: 0.82rem;
        }

        .summary-total-price {
            font-size: 1.4rem;
            font-weight: 800;
            line-height: 1;
        }

        .btn-pay {
            width: 100%;
            border: none;
            border-radius: 999px;
            background: #ffffff;
            color: rgb(15, 78, 48);
            font-size: 0.95rem;
            font-weight: 700;
            padding: 11px 16px;
            text-decoration: none;
            display: inline-flex;
            justify-content: center;
            align-items: center;
        }

        .back-link {
            margin-top: 12px;
            display: inline-flex;
            width: 100%;
            justify-content: center;
            color: #d6e5dd;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.8rem;
        }

        .back-link:hover {
            color: #fff;
        }

        .btn-remove {
            border: 1px solid #e5b9b9;
            background: #fff5f5;
            color: #a64a4a;
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 0.7rem;
            font-weight: 700;
            line-height: 1;
        }

        .btn-remove:hover {
            background: #ffe8e8;
            color: #923f3f;
        }

        .empty-box {
            background: #fff;
            border: 1px dashed #cad8cf;
            border-radius: 18px;
            padding: 22px;
            text-align: center;
            color: #6f7d76;
            font-weight: 600;
        }

        @media (max-width: 991px) {
            .price,
            .subtotal {
                font-size: 1.15rem;
            }

            .qty {
                font-size: 1.05rem;
            }

            .summary-title {
                font-size: 1.25rem;
            }

            .summary-total-price {
                font-size: 1.55rem;
            }
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

            .page-wrap {
                padding: 22px 0 26px;
            }

            .page-title {
                margin-bottom: 14px;
            }

            .cart-card {
                border-radius: 18px;
                padding: 6px 8px;
            }

            .book-info {
                min-width: 180px;
            }

            .book-name {
                font-size: 0.95rem;
            }

            .book-author {
                font-size: 0.8rem;
            }

            .book-thumb {
                width: 52px;
                height: 72px;
                font-size: 0.9rem;
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

    <main class="page-wrap">
        <div class="container">
            <div class="title-box">
                <h1 class="page-title">Keranjang</h1>
            </div>

            <?php if (count($rows) === 0) { ?>
                <div class="empty-box">Keranjang masih kosong. Yuk pilih buku favoritmu dulu.</div>
            <?php } else { ?>
                <div class="row g-4 align-items-start">
                    <div class="col-lg-8">
                        <div class="cart-card">
                            <div class="table-responsive">
                                <table class="cart-table">
                                    <thead>
                                        <tr>
                                            <th style="min-width:320px;">Buku</th>
                                            <th>Harga</th>
                                            <th>Jumlah</th>
                                            <th>Subtotal</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rows as $row) { ?>
                                            <tr>
                                                <td>
                                                    <div class="book-info">
                                                        <div class="book-thumb">
                                                            <?php if (!empty($row['has_foto'])) { ?>
                                                                <img src="<?php echo htmlspecialchars($row['foto_path']); ?>" alt="<?php echo htmlspecialchars($row['judul']); ?>">
                                                            <?php } else { ?>
                                                                <?php echo strtoupper(substr($row['judul'], 0, 1)); ?>
                                                            <?php } ?>
                                                        </div>
                                                        <div>
                                                            <p class="book-name"><?php echo htmlspecialchars($row['judul']); ?></p>
                                                            <p class="book-author"><?php echo htmlspecialchars($row['penulis']); ?></p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="price">Rp <?php echo number_format((int) $row['harga']); ?></td>
                                                <td><span class="qty"><?php echo (int) $row['qty']; ?></span></td>
                                                <td class="subtotal">Rp <?php echo number_format((int) $row['subtotal']); ?></td>
                                                <td>
                                                    <form method="post" class="m-0" onsubmit="return confirm('Hapus buku ini dari keranjang?');">
                                                        <input type="hidden" name="id_cart" value="<?php echo (int) $row['id_cart']; ?>">
                                                        <button type="submit" name="hapus" class="btn-remove">Hapus</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="summary-card">
                            <h2 class="summary-title">Ringkasan</h2>

                            <div class="summary-row">
                                <span>Total Buku</span>
                                <span><?php echo $totalItems; ?> Buku</span>
                            </div>
                            <div class="summary-row">
                                <span>Layanan Pengiriman</span>
                                <span>Standar (Gratis)</span>
                            </div>

                            <hr class="summary-divider">

                            <div class="summary-total">
                                <span class="summary-total-label">Total Bayar</span>
                                <span class="summary-total-price">Rp <?php echo number_format($total); ?></span>
                            </div>

                            <a href="checkout.php" class="btn-pay">Bayar Sekarang</a>
                            <a href="../index.php" class="back-link">Lanjutkan Belanja</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </main>

    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
<?php include '../footer.php'; ?>
</body>

</html>
