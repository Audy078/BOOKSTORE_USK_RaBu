<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: pesanan.php");
    exit;
}

$id_pesanan = (int) $_GET['id'];

// ambil data pesanan
$queryPesanan = mysqli_query(
    $conn,
    "SELECT pesanan.*, users.nama, users.alamat, users.no_telp
     FROM pesanan
     JOIN users ON pesanan.id_user = users.id_user
     WHERE pesanan.id_pesanan = '$id_pesanan'
     LIMIT 1"
);

if (!$queryPesanan || mysqli_num_rows($queryPesanan) === 0) {
    header("Location: pesanan.php");
    exit;
}

$pesanan = mysqli_fetch_assoc($queryPesanan);

// ambil detail pesanan (buku yang dibeli)
$queryDetail = mysqli_query(
    $conn,
    "SELECT detail_pesanan.*, buku.judul
     FROM detail_pesanan
     JOIN buku ON detail_pesanan.id_buku = buku.id_buku
     WHERE detail_pesanan.id_pesanan = '$id_pesanan'
     ORDER BY detail_pesanan.id_detail ASC"
);

$detailRows = [];
while ($row = mysqli_fetch_assoc($queryDetail)) {
    $detailRows[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Detail Pesanan</title>
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

        .label-field {
            color: #666;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .value-field {
            font-size: 1rem;
            font-weight: 600;
            color: #1f2723;
            margin-bottom: 16px;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1f4d3b;
            margin-top: 24px;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--green-800);
        }

        .badge-status {
            font-size: 0.9rem;
            padding: 6px 12px;
            border-radius: 20px;
        }

        .table thead th {
            background: #e8f4ee;
            color: #1f4d3b;
        }

        .bukti-image {
            max-width: 200px;
            max-height: 250px;
            border: 1px solid var(--line);
            border-radius: 8px;
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

    <nav class="navbar navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="dashboard.php">RaBu</a>

            <div class="d-none d-md-flex align-items-center gap-2 fw-semibold nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="kategori.php">Kategori</a>
                <a href="pesan.php">Pesan</a>
                <a href="buku.php">Buku</a>
                <a href="pesanan.php" class="active">Pesanan</a>
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

    <div class="container py-4">

        <!-- ===== JUDUL ===== -->
        <div class="row mb-4">
            <div class="col">
                <h3 class="fw-bold">Detail Pesanan #<?php echo $pesanan['id_pesanan']; ?></h3>
                <p class="text-muted">Informasi lengkap pesanan dan buku yang dibeli</p>
            </div>
        </div>

        <!-- ===== CARD DETAIL ===== -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">

                <!-- INFO PEMBELI -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="label-field">Nama Pembeli</div>
                        <div class="value-field"><?php echo htmlspecialchars($pesanan['nama']); ?></div>

                        <div class="label-field">Alamat Pengiriman</div>
                        <div class="value-field"><?php echo htmlspecialchars($pesanan['alamat']); ?></div>

                        <div class="label-field">No. Telepon</div>
                        <div class="value-field"><?php echo htmlspecialchars($pesanan['no_telp']); ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="label-field">No. Pesanan</div>
                        <div class="value-field">#<?php echo $pesanan['id_pesanan']; ?></div>

                        <div class="label-field">Tanggal Pesanan</div>
                        <div class="value-field"><?php echo date('d-m-Y H:i', strtotime($pesanan['tanggal'])); ?></div>

                        <div class="label-field">Metode Pembayaran</div>
                        <div class="value-field">
                            <?php
                            $metode = strtoupper((string) ($pesanan['metode_pembayaran'] ?? 'COD'));
                            if ($metode === 'TF') {
                                echo '<span class="badge bg-primary badge-status">Transfer Bank</span>';
                            } else {
                                echo '<span class="badge bg-success badge-status">Cash on Delivery</span>';
                            }
                            ?>
                        </div>

                        <div class="label-field">Status Pesanan</div>
                        <div class="value-field">
                            <?php
                            if ($pesanan['status'] == 'pending') {
                                echo '<span class="badge bg-warning badge-status">Pending</span>';
                            } elseif ($pesanan['status'] == 'diproses') {
                                echo '<span class="badge bg-info text-dark badge-status">Diproses</span>';
                            } elseif ($pesanan['status'] == 'dikirim') {
                                echo '<span class="badge bg-primary badge-status">Dikirim</span>';
                            } elseif ($pesanan['status'] == 'selesai') {
                                echo '<span class="badge bg-success badge-status">Selesai</span>';
                            } elseif ($pesanan['status'] == 'dibatalkan') {
                                echo '<span class="badge bg-danger badge-status">Dibatalkan</span>';
                            } else {
                                echo '<span class="badge bg-secondary badge-status">' . $pesanan['status'] . '</span>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ===== TABEL BUKU YANG DIBELI ===== -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="section-title">Buku yang Dibeli</h5>

                <?php if (count($detailRows) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Judul Buku</th>
                                    <th class="text-center">Jumlah</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $totalKeseluruhan = 0;
                                foreach ($detailRows as $detail) {
                                    $totalKeseluruhan += $detail['subtotal'];
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($detail['judul']); ?></td>
                                        <td class="text-center"><?php echo $detail['qty']; ?></td>
                                        <td class="text-end">Rp <?php echo number_format($detail['harga']); ?></td>
                                        <td class="text-end fw-bold">Rp <?php echo number_format($detail['subtotal']); ?></td>
                                    </tr>
                                <?php } ?>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Total Pembayaran:</td>
                                    <td class="text-end fw-bold" style="font-size: 1.1rem; color: #1f4d3b;">Rp <?php echo number_format($pesanan['total']); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Tidak ada detail produk ditemukan.</p>
                <?php endif; ?>

            </div>
        </div>

        <!-- ===== BUKTI PEMBAYARAN ===== -->
        <?php if ($pesanan['metode_pembayaran'] === 'TF' && !empty($pesanan['bukti_pembayaran'])): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="section-title">Bukti Pembayaran (Transfer Bank)</h5>

                    <div class="text-center">
                        <?php
                        $buktiPath = '../uploads/bukti-transfer/' . htmlspecialchars($pesanan['bukti_pembayaran']);
                        if (file_exists($buktiPath)):
                            ?>
                            <img src="<?php echo $buktiPath; ?>" alt="Bukti Pembayaran" class="bukti-image">
                            <div class="mt-3">
                                <a href="<?php echo $buktiPath; ?>" download class="btn btn-sm btn-primary">
                                    ⬇ Download Bukti
                                </a>
                            </div>
                        <?php else: ?>
                            <p class="text-danger">File bukti pembayaran tidak ditemukan.</p>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        <?php endif; ?>

        <!-- ===== TOMBOL KEMBALI ===== -->
        <div class="mt-4 mb-4">
            <a href="pesanan.php" class="btn btn-secondary btn-sm">
                Kembali ke Daftar Pesanan
            </a>
        </div>

    </div>

    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
<?php include '../footer.php'; ?>
</body>

</html>
