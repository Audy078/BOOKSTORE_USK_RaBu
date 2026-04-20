<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$keyword = trim((string) ($_GET['q'] ?? ''));
$keywordSql = mysqli_real_escape_string($conn, $keyword);

// Handle AJAX request untuk detail pesanan
if (isset($_GET['action']) && $_GET['action'] === 'getDetail' && isset($_GET['id'])) {
    $id_pesanan = (int) $_GET['id'];
    
    $queryPesanan = mysqli_query(
        $conn,
        "SELECT pesanan.*, users.nama, users.email, users.alamat, users.no_telp
         FROM pesanan
         JOIN users ON pesanan.id_user = users.id_user
         WHERE pesanan.id_pesanan = '$id_pesanan'
         LIMIT 1"
    );
    
    if ($queryPesanan && mysqli_num_rows($queryPesanan) > 0) {
        $pesanan = mysqli_fetch_assoc($queryPesanan);
        
        $queryDetail = mysqli_query(
            $conn,
            "SELECT detail_pesanan.*, buku.judul
             FROM detail_pesanan
             JOIN buku ON detail_pesanan.id_buku = buku.id_buku
             WHERE detail_pesanan.id_pesanan = '$id_pesanan'
             ORDER BY detail_pesanan.id_detail ASC"
        );
        
        $details = [];
        while ($row = mysqli_fetch_assoc($queryDetail)) {
            $details[] = $row;
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'pesanan' => $pesanan,
            'details' => $details
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
    }
    exit;
}

if (isset($_GET['aksi'], $_GET['id'])) {
    $idPesanan = (int) $_GET['id'];
    $aksi = strtolower(trim((string) $_GET['aksi']));
    $allowedActions = ['diproses', 'dikirim', 'selesai', 'dibatalkan'];

    if ($idPesanan > 0 && in_array($aksi, $allowedActions, true)) {
        mysqli_query($conn, "UPDATE pesanan SET status='$aksi' WHERE id_pesanan='$idPesanan'");
    }

    header("Location: pesanan.php");
    exit;
}

if (isset($_GET['hapus'], $_GET['id'])) {
    $idPesanan = (int) $_GET['id'];

    if ($idPesanan > 0) {
        $buktiQuery = mysqli_query($conn, "SELECT bukti_pembayaran FROM pesanan WHERE id_pesanan='$idPesanan' LIMIT 1");
        $buktiRow = $buktiQuery ? mysqli_fetch_assoc($buktiQuery) : null;
        $buktiNama = trim((string) ($buktiRow['bukti_pembayaran'] ?? ''));

        if ($buktiNama !== '') {
            $buktiPath = '../uploads/bukti-transfer/' . $buktiNama;
            if (file_exists($buktiPath)) {
                unlink($buktiPath);
            }
        }

        mysqli_query($conn, "DELETE FROM pesanan WHERE id_pesanan='$idPesanan'");
    }

    header("Location: pesanan.php");
    exit;
}

$query = mysqli_query(
    $conn,
    "SELECT pesanan.*, users.nama, users.email 
     FROM pesanan 
     JOIN users ON pesanan.id_user = users.id_user
     ORDER BY pesanan.tanggal DESC"
);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Pesanan</title>
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

        .badge-status {
            font-size: 0.9rem;
            padding: 6px 12px;
            border-radius: 20px;
        }

        .table thead th {
            background: #e8f4ee;
            color: #1f4d3b;
        }

        .card-header {
            background: #e8f4ee !important;
            color: #1f4d3b !important;
            font-weight: 700;
        }

        .modal-header {
            background: #1f3a3a;
            color: white;
            border-bottom: 2px solid var(--green-800);
        }

        .modal-title {
            font-weight: 700;
            font-size: 1.25rem;
        }

        .info-label {
            color: #666;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 600;
            color: #1f2723;
            margin-bottom: 16px;
        }

        .section-divider {
            border-top: 2px solid var(--green-800);
            margin: 20px 0;
            padding-top: 20px;
            font-weight: 700;
        }

        .bukti-img-thumb {
            max-width: 150px;
            max-height: 200px;
            border: 1px solid var(--line);
            border-radius: 6px;
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

    <div class="container mt-4">

        <div class="card shadow-sm mb-4">
            <div class="card-header">
                Data Pesanan User
            </div>
            <div class="card-body border-top">
                <form method="get" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <input type="text" name="q" class="form-control" placeholder="Cari nomor order, nama, email, atau status..." value="<?php echo htmlspecialchars($keyword); ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-success">Cari</button>
                    </div>
                    <?php if ($keyword !== '') { ?>
                        <div class="col-auto">
                            <a href="pesanan.php" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    <?php } ?>
                </form>
            </div>
        </div>

        <!-- ===== TABEL PESANAN ===== -->
        <div class="card shadow-sm">
            <div class="card-body table-responsive">

                <table class="table table-bordered table-hover align-middle text-center">
                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Nama</th>
                            <th>Total Pembayaran</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th>Tanggal Pesanan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $where = '';
                        if ($keyword !== '') {
                            $where = "WHERE pesanan.id_pesanan LIKE '%$keywordSql%' OR users.nama LIKE '%$keywordSql%' OR users.email LIKE '%$keywordSql%' OR pesanan.metode_pembayaran LIKE '%$keywordSql%' OR pesanan.status LIKE '%$keywordSql%'";
                        }

                        $query = mysqli_query(
                            $conn,
                            "SELECT pesanan.*, users.nama, users.email 
                             FROM pesanan 
                             JOIN users ON pesanan.id_user = users.id_user
                             $where
                             ORDER BY pesanan.tanggal DESC"
                        );

                        while ($row = mysqli_fetch_assoc($query)) {
                            ?>
                            <tr>
                                <td>#<?php echo (int) $row['id_pesanan']; ?></td>
                                <td class="text-start"><?php echo $row['nama']; ?></td>
                                <td class="text-end">
                                    Rp <?php echo number_format($row['total']); ?>
                                </td>
                                <td>
                                    <?php
                                    $metode = strtoupper((string) ($row['metode_pembayaran'] ?? 'COD'));
                                    if ($metode === 'TF') {
                                        echo '<span class="badge bg-primary badge-status">TF</span>';
                                    } else {
                                        echo '<span class="badge bg-success badge-status">COD</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($row['status'] == 'pending') {
                                        echo '<span class="badge bg-warning badge-status">Pending</span>';
                                    } elseif ($row['status'] == 'diproses') {
                                        echo '<span class="badge bg-info text-dark badge-status">Diproses</span>';
                                    } elseif ($row['status'] == 'dikirim') {
                                        echo '<span class="badge bg-primary badge-status">Dikirim</span>';
                                    } elseif ($row['status'] == 'selesai') {
                                        echo '<span class="badge bg-success badge-status">Selesai</span>';
                                    } elseif ($row['status'] == 'dibatalkan') {
                                        echo '<span class="badge bg-danger badge-status">Dibatalkan</span>';
                                    } else {
                                        echo '<span class="badge bg-secondary badge-status">' . $row['status'] . '</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php echo date('d-m-Y H:i', strtotime($row['tanggal'])); ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#detailModal" onclick="showDetail(<?php echo (int) $row['id_pesanan']; ?>)">
                                        Detail
                                    </button>
                                    <div class="dropdown d-inline">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            Status
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="?aksi=diproses&id=<?php echo (int) $row['id_pesanan']; ?>">Diproses</a></li>
                                            <li><a class="dropdown-item" href="?aksi=dikirim&id=<?php echo (int) $row['id_pesanan']; ?>">Dikirim</a></li>
                                            <li><a class="dropdown-item" href="?aksi=selesai&id=<?php echo (int) $row['id_pesanan']; ?>">Selesai</a></li>
                                            <li><a class="dropdown-item text-danger" href="?aksi=dibatalkan&id=<?php echo (int) $row['id_pesanan']; ?>" onclick="return confirm('Batalkan pesanan ini?');">Dibatalkan</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="?hapus=1&id=<?php echo (int) $row['id_pesanan']; ?>" onclick="return confirm('Hapus pesanan ini permanen?');">Hapus</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            </div>
        </div>

    </div>

    <!-- MODAL DETAIL PESANAN -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Detail Pesanan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detailContent">
                    <p class="text-muted">Loading...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        function showDetail(idPesanan) {
            const detailContent = document.getElementById('detailContent');
            detailContent.innerHTML = '<p class="text-muted">Loading...</p>';
            
            fetch(`pesanan.php?action=getDetail&id=${idPesanan}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const p = data.pesanan;
                        const details = data.details;
                        
                        let metodeLabel = p.metode_pembayaran === 'TF' ? 'Transfer Bank' : 'Cash on Delivery';
                        let metodeBadge = p.metode_pembayaran === 'TF' 
                            ? '<span class="badge bg-primary">Transfer Bank</span>' 
                            : '<span class="badge bg-success">Cash on Delivery</span>';
                        
                        let statusBadge = '';
                        if (p.status === 'pending') statusBadge = '<span class="badge bg-warning">Pending</span>';
                        else if (p.status === 'diproses') statusBadge = '<span class="badge bg-info text-dark">Diproses</span>';
                        else if (p.status === 'dikirim') statusBadge = '<span class="badge bg-primary">Dikirim</span>';
                        else if (p.status === 'selesai') statusBadge = '<span class="badge bg-success">Selesai</span>';
                        else if (p.status === 'dibatalkan') statusBadge = '<span class="badge bg-danger">Dibatalkan</span>';
                        
                        let html = `
                            <div class="info-row">
                                <div class="info-label">ID Order</div>
                                <div class="info-value">#${p.id_pesanan}</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Nama Pembeli</div>
                                <div class="info-value">${p.nama}</div>
                            </div>

                            <div class="info-row">
                                <div class="info-label">Email</div>
                                <div class="info-value">${p.email ? p.email : '-'}</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Alamat Pengiriman</div>
                                <div class="info-value">${p.alamat}</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">No. Telepon</div>
                                <div class="info-value">${p.no_telp}</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Tanggal Pesanan</div>
                                <div class="info-value">${new Date(p.tanggal).toLocaleDateString('id-ID', {year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit'})}</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Metode Pembayaran</div>
                                <div class="info-value">${metodeBadge}</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Status Pesanan</div>
                                <div class="info-value">${statusBadge}</div>
                            </div>
                        `;
                        
                        if (details.length > 0) {
                            html += '<div class="section-divider">Buku yang Dibeli</div>';
                            html += `
                                <table class="table table-sm" style="margin-top: 12px;">
                                    <thead>
                                        <tr style="background: #e8f4ee;">
                                            <th style="color: #1f4d3b;">Judul Buku</th>
                                            <th style="color: #1f4d3b; text-align: center;">Qty</th>
                                            <th style="color: #1f4d3b; text-align: right;">Harga</th>
                                            <th style="color: #1f4d3b; text-align: right;">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            `;
                            
                            let totalAll = 0;
                            details.forEach((item) => {
                                totalAll += item.subtotal;
                                html += `
                                    <tr>
                                        <td>${item.judul}</td>
                                        <td style="text-align: center;">${item.qty}</td>
                                        <td style="text-align: right;">Rp ${parseInt(item.harga).toLocaleString('id-ID')}</td>
                                        <td style="text-align: right; font-weight: bold;">Rp ${parseInt(item.subtotal).toLocaleString('id-ID')}</td>
                                    </tr>
                                `;
                            });
                            
                            html += `
                                        <tr style="background: #e8f4ee; font-weight: bold;">
                                            <td colspan="3" style="text-align: right;">Total Pembayaran:</td>
                                            <td style="text-align: right; color: #1f4d3b; font-size: 1.05rem;">Rp ${parseInt(p.total).toLocaleString('id-ID')}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            `;
                        }
                        
                        if (p.metode_pembayaran === 'TF' && p.bukti_pembayaran) {
                            html += '<div class="section-divider">Bukti Pembayaran</div>';
                            html += `
                                <div style="margin-top: 12px;">
                                    <img src="../uploads/bukti-transfer/${p.bukti_pembayaran}" alt="Bukti" class="bukti-img-thumb">
                                </div>
                            `;
                        }
                        
                        detailContent.innerHTML = html;
                    } else {
                        detailContent.innerHTML = '<p class="text-danger">Data tidak ditemukan</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    detailContent.innerHTML = '<p class="text-danger">Gagal memuat data</p>';
                });
        }
    </script>
<?php include '../footer.php'; ?>
</body>

</html>