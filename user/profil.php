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
$errorMsg = '';
$successMsg = '';

if (isset($_POST['simpan_profil'])) {
    $email = mysqli_real_escape_string($conn, trim((string) ($_POST['email'] ?? '')));
    $alamat = mysqli_real_escape_string($conn, trim((string) ($_POST['alamat'] ?? '')));

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = 'Email tidak valid.';
    } elseif ($alamat === '') {
        $errorMsg = 'Alamat tidak boleh kosong.';
    } else {
        $cekEmail = mysqli_query(
            $conn,
            "SELECT id_user FROM users WHERE email='$email' AND id_user <> '$id_user' LIMIT 1"
        );

        if ($cekEmail && mysqli_num_rows($cekEmail) > 0) {
            $errorMsg = 'Email sudah digunakan akun lain.';
        } else {
            $update = mysqli_query(
                $conn,
                "UPDATE users SET email='$email', alamat='$alamat' WHERE id_user='$id_user'"
            );

            if ($update) {
                $successMsg = 'Profil berhasil diperbarui.';
            } else {
                $errorMsg = 'Gagal memperbarui profil.';
            }
        }
    }
}

$userQuery = mysqli_query($conn, "SELECT nama, no_telp, email, alamat FROM users WHERE id_user='$id_user' LIMIT 1");
$userData = $userQuery ? mysqli_fetch_assoc($userQuery) : null;

if (!$userData) {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Profil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">

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

        .profile-card {
            max-width: 680px;
            border-radius: 16px;
            border: 1px solid #d9e6dd;
            background: #fff;
        }

        .profile-title {
            color: var(--green-900);
            font-weight: 800;
        }

        .btn-save {
            background: var(--green-800);
            border: none;
            border-radius: 999px;
            padding: 10px 18px;
            font-weight: 700;
            color: #fff;
        }

        .btn-save:hover {
            background: var(--green-900);
            color: #fff;
        }

        .btn-back {
            border-radius: 999px;
            padding: 10px 18px;
            font-weight: 700;
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
                        <li><a class="dropdown-item active" href="profil.php">Edit Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../logout.php" onclick="return confirm('Yakin ingin logout?')">Keluar</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="profile-card shadow-sm p-4 mx-auto">
            <h4 class="profile-title mb-3">Edit Profil</h4>

            <?php if ($errorMsg !== '') { ?>
                <div class="alert alert-danger py-2 px-3"><?php echo htmlspecialchars($errorMsg); ?></div>
            <?php } ?>
            <?php if ($successMsg !== '') { ?>
                <div class="alert alert-success py-2 px-3"><?php echo htmlspecialchars($successMsg); ?></div>
            <?php } ?>

            <form method="post">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($userData['nama'] ?? ''); ?>" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">No Telepon</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($userData['no_telp'] ?? ''); ?>" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="3" required><?php echo htmlspecialchars($userData['alamat'] ?? ''); ?></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="simpan_profil" class="btn-save">Simpan Perubahan</button>
                    <a href="../index.php" class="btn btn-outline-secondary btn-back">Kembali</a>
                </div>
            </form>
        </div>
    </div>

    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
<?php include '../footer.php'; ?>
</body>

</html>
