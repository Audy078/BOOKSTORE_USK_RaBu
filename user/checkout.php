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

// proteksi login
if (!isset($_SESSION['id_user'])) {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

$userQuery = mysqli_query($conn, "SELECT * FROM users WHERE id_user='$id_user' LIMIT 1");
$userData = $userQuery ? mysqli_fetch_assoc($userQuery) : null;

$formNama = trim((string) ($userData['nama'] ?? ($_SESSION['nama'] ?? '')));
$formEmail = trim((string) ($userData['email'] ?? ''));
$formNoTelp = trim((string) ($userData['no_telp'] ?? ''));
$formAlamat = trim((string) ($userData['alamat'] ?? ''));

// ambil data cart
$query = mysqli_query(
    $conn,
    "SELECT cart.*, buku.judul, buku.harga 
     FROM cart 
     JOIN buku ON cart.id_buku = buku.id_buku
     WHERE cart.id_user='$id_user'"
);

$cartRows = [];
$total = 0;
while ($row = mysqli_fetch_assoc($query)) {
    $cartRows[] = $row;
    $total += $row['harga'] * $row['qty'];
}
$isCartEmpty = count($cartRows) === 0;

$errorMsg = '';

// proses checkout
if (isset($_POST['checkout'])) {
    $metodePembayaran = strtoupper(trim((string) ($_POST['metode_pembayaran'] ?? 'COD')));

    if (!in_array($metodePembayaran, ['COD', 'TF'], true)) {
        $errorMsg = 'Metode pembayaran tidak valid.';
    }

    if ($errorMsg === '' && ($formNama === '' || $formNoTelp === '' || $formAlamat === '')) {
        $errorMsg = 'Data akun belum lengkap. Silakan lengkapi profil terlebih dahulu.';
    } elseif ($errorMsg === '' && $metodePembayaran === 'TF' && (!isset($_FILES['bukti']) || $_FILES['bukti']['error'] !== UPLOAD_ERR_OK)) {
        $errorMsg = 'Upload bukti transfer wajib diisi untuk pembayaran TF.';
    } elseif ($errorMsg === '' && $metodePembayaran === 'TF') {
        $allowedExt = ['jpg', 'jpeg', 'png'];
        $fileName = $_FILES['bukti']['name'];
        $fileSize = (int) $_FILES['bukti']['size'];
        $tmpFile = $_FILES['bukti']['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowedExt, true)) {
            $errorMsg = 'Format bukti transfer harus JPG, JPEG, atau PNG.';
        } elseif ($fileSize > 2 * 1024 * 1024) {
            $errorMsg = 'Ukuran bukti transfer maksimal 2MB.';
        } else {
            $uploadDir = '../uploads/bukti-transfer';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $savedName = 'bukti_' . $id_user . '_' . time() . '.' . $fileExt;
            $uploadPath = $uploadDir . '/' . $savedName;

            if (!move_uploaded_file($tmpFile, $uploadPath)) {
                $errorMsg = 'Gagal mengupload bukti transfer.';
            }
        }
    }

    if ($errorMsg === '') {
        $buktiPembayaran = null;

        if ($metodePembayaran === 'TF' && isset($savedName)) {
            $buktiPembayaran = $savedName;
        }

        // simpan pesanan
        $sqlInsert = "INSERT INTO pesanan (id_user, total, status, metode_pembayaran, bukti_pembayaran)
                     VALUES ('$id_user', '$total', 'pending', '$metodePembayaran', " . ($buktiPembayaran ? "'$buktiPembayaran'" : "NULL") . ")";
        mysqli_query($conn, $sqlInsert);
        $id_pesanan = mysqli_insert_id($conn);

        // ambil isi cart untuk simpan detail dan update stok
        $cart = mysqli_query(
            $conn,
            "SELECT cart.*, buku.harga FROM cart JOIN buku ON cart.id_buku = buku.id_buku WHERE cart.id_user='$id_user'"
        );

        while ($c = mysqli_fetch_assoc($cart)) {
            $id_buku = (int) $c['id_buku'];
            $qty = (int) $c['qty'];
            $harga = (int) $c['harga'];
            $subtotal = $qty * $harga;

            // simpan detail pesanan
            mysqli_query(
                $conn,
                "INSERT INTO detail_pesanan (id_pesanan, id_buku, qty, harga, subtotal)
                 VALUES ('$id_pesanan', '$id_buku', '$qty', '$harga', '$subtotal')"
            );

            // kurangi stok buku
            mysqli_query(
                $conn,
                "UPDATE buku 
                 SET stok = stok - $qty 
                 WHERE id_buku='$id_buku'"
            );
        }

        // hapus cart
        mysqli_query(
            $conn,
            "DELETE FROM cart WHERE id_user='$id_user'"
        );

        echo "<script>
            alert('Pesanan berhasil dibuat');
            window.location='pesanan.php';
        </script>";
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
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

        .checkout-page {
            padding: 24px 0 34px;
        }

        .page-label {
            color: #74867d;
            letter-spacing: 1.4px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.76rem;
            margin-bottom: 4px;
        }

        .page-title {
            font-size: clamp(1.28rem, 2.3vw, 1.85rem);
            font-weight: 800;
            color: #1f4d3b;
            margin-bottom: 0;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 16px;
        }

        .checkout-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 8px 20px rgba(28, 55, 42, 0.06);
        }

        .card-title {
            font-size: 1rem;
            font-weight: 800;
            color: #214d3c;
            margin-bottom: 12px;
        }

        .summary-list {
            display: grid;
            gap: 10px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #d9e5dd;
            font-size: 0.9rem;
        }

        .summary-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .summary-name {
            color: #33423c;
            font-weight: 600;
        }

        .summary-price {
            color: #1f4d3b;
            font-weight: 700;
            white-space: nowrap;
        }

        .total-row {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #d9e5dd;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 800;
            color: #1f4d3b;
        }

        .total-row span:last-child {
            color: #d35400;
        }

        .transfer-total {
            color: #d35400;
        }

        .recipient-grid {
            display: grid;
            gap: 10px;
        }

        .field-label {
            display: block;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.2px;
            color: #70827a;
            margin-bottom: 4px;
        }

        .info-field {
            width: 100%;
            border: 1px solid #d6e3db;
            border-radius: 10px;
            background: #f7fbf9;
            color: #1f2723;
            font-size: 0.92rem;
            padding: 10px 11px;
        }

        .payment-option {
            display: block;
            border: 1px solid #d5dbe5;
            border-radius: 12px;
            padding: 12px;
            background: #ffffff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
            cursor: pointer;
        }

        .payment-option:hover {
            border-color: #a8b8cc;
            transform: translateY(-1px);
        }

        .payment-option.active {
            border-color: #3f7d63;
            box-shadow: 0 0 0 3px rgba(63, 125, 99, 0.14);
        }

        .transfer-box {
            background: #f5faf7;
            border: 1px dashed #bad4c6;
            border-radius: 12px;
            padding: 12px;
            font-size: 0.88rem;
        }

        .upload-box {
            background: #f9fcfa;
            border: 1px solid #d6e3db;
            border-radius: 12px;
            padding: 12px;
        }

        .action-stack {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        .btn-confirm {
            width: fit-content;
            background: var(--green-800);
            color: #fff;
            border: none;
            padding: 7px 14px;
            font-weight: 700;
            font-size: 0.88rem;
            line-height: 1.2;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-confirm:hover {
            background: var(--green-900);
            color: #fff;
        }

        .btn-confirm:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-back {
            width: fit-content;
            background: #c74444;
            color: #fff;
            border: none;
            padding: 7px 14px;
            font-weight: 700;
            font-size: 0.88rem;
            line-height: 1.2;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            justify-content: center;
            align-items: center;
        }

        .btn-back:hover {
            background: #b43a3a;
            color: #fff;
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

            .checkout-grid {
                grid-template-columns: 1fr;
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

    <div class="container checkout-page">
        <div class="mb-3">
            <div class="page-label">Checkout</div>
            <h4 class="page-title">Konfirmasi Pesanan Kamu</h4>
        </div>

        <?php if ($errorMsg !== '') { ?>
            <div class="alert alert-danger py-2 px-3"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php } ?>

        <?php if ($isCartEmpty) { ?>
            <div class="alert alert-warning py-2 px-3">Keranjang kamu kosong. Tambahkan buku dulu sebelum checkout.</div>
        <?php } ?>

        <form method="post" enctype="multipart/form-data">
            <div class="checkout-grid">
                <div class="d-grid gap-3">
                    <div class="checkout-card">
                        <div class="card-title">Ringkasan Pesanan</div>
                        <div class="summary-list">
                            <?php if ($isCartEmpty) { ?>
                                <div class="text-muted small">Belum ada item di keranjang.</div>
                            <?php } ?>
                            <?php foreach ($cartRows as $item) { ?>
                                <div class="summary-item">
                                    <div class="summary-name">
                                        <?php echo htmlspecialchars($item['judul']); ?>
                                        <span class="text-muted">x<?php echo (int) $item['qty']; ?></span>
                                    </div>
                                    <div class="summary-price">Rp <?php echo number_format((int) $item['harga'] * (int) $item['qty']); ?></div>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="total-row">
                            <span>Total</span>
                            <span>Rp <?php echo number_format($total); ?></span>
                        </div>
                    </div>

                    <div class="checkout-card">
                        <div class="card-title">Data Penerima</div>
                        <div class="recipient-grid">
                            <div>
                                <span class="field-label">Nama</span>
                                <input type="text" name="nama" class="info-field" value="<?php echo htmlspecialchars($formNama); ?>" readonly disabled>
                            </div>
                            <div>
                                <span class="field-label">Email</span>
                                <input type="email" name="email" class="info-field" value="<?php echo htmlspecialchars($formEmail); ?>" readonly disabled>
                            </div>
                            <div>
                                <span class="field-label">Nomor Telepon</span>
                                <input type="text" name="no_telp" class="info-field" value="<?php echo htmlspecialchars($formNoTelp); ?>" readonly disabled>
                            </div>
                            <div>
                                <span class="field-label">Alamat Lengkap</span>
                                <textarea name="alamat" class="info-field" rows="3" readonly disabled><?php echo htmlspecialchars($formAlamat); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-3">
                    <div class="checkout-card">
                        <div class="card-title">Metode Pembayaran</div>
                        <div class="d-grid gap-2 mb-3">
                            <label class="payment-option">
                                <input type="radio" name="metode_pembayaran" value="COD" class="form-check-input me-2" checked>
                                <span class="fw-semibold">COD</span>
                                <div class="small text-muted mt-1">Bayar saat pesanan diterima.</div>
                            </label>
                            <label class="payment-option">
                                <input type="radio" name="metode_pembayaran" value="TF" class="form-check-input me-2">
                                <span class="fw-semibold">TF</span>
                                <div class="small text-muted mt-1">Transfer bank dan unggah bukti pembayaran.</div>
                            </label>
                        </div>

                        <div id="tfInfo" class="transfer-box d-none">
                            <p class="mb-2">Silakan transfer ke rekening berikut: <strong>Bank Bangkrut</strong></p>
                            <div class="small mb-1">Nomor Rekening: <strong>1234 5678 910</strong></div>
                            <div class="small mb-1">Atas Nama: <strong>RaBu</strong></div>
                            <div class="small mb-1">Jumlah: <strong class="transfer-total">Rp <?php echo number_format($total); ?></strong></div>
                        </div>

                        <div class="upload-box d-none mt-3" id="tfUploadWrap">
                            <label class="form-label fw-semibold mb-1 small">Upload Bukti Transfer (Format: JPG/PNG, max 2MB)</label>
                            <input type="file" name="bukti" class="form-control form-control-sm" accept=".jpg,.jpeg,.png" required>
                        </div>

                        <div class="action-stack">
                            <button type="submit" name="checkout" class="btn-confirm" <?php echo $isCartEmpty ? 'disabled' : ''; ?>>Konfirmasi Pesanan</button>
                            <a href="cart.php" class="btn-back">Kembali ke Keranjang</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
   
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const paymentRadios = document.querySelectorAll('input[name="metode_pembayaran"]');
            const tfInfo = document.getElementById('tfInfo');
            const tfUploadWrap = document.getElementById('tfUploadWrap');
            const tfUploadInput = tfUploadWrap ? tfUploadWrap.querySelector('input[type="file"]') : null;

            function togglePayment() {
                const selected = document.querySelector('input[name="metode_pembayaran"]:checked');
                const isTf = selected && selected.value === 'TF';

                document.querySelectorAll('.payment-option').forEach((option) => {
                    const radio = option.querySelector('input[name="metode_pembayaran"]');
                    option.classList.toggle('active', !!radio && radio.checked);
                });

                if (tfInfo) {
                    tfInfo.classList.toggle('d-none', !isTf);
                }

                if (tfUploadWrap) {
                    tfUploadWrap.classList.toggle('d-none', !isTf);
                }

                if (tfUploadInput) {
                    tfUploadInput.required = isTf;
                    if (!isTf) {
                        tfUploadInput.value = '';
                    }
                }
            }

            paymentRadios.forEach((radio) => radio.addEventListener('change', togglePayment));
            togglePayment();
        })();
    </script>

<?php include '../footer.php'; ?>
</body>

</html>