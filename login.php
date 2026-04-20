<?php
session_start();
include 'koneksi.php';

$error = '';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    $query = mysqli_query(
        $conn,
        "SELECT * FROM users 
         WHERE email='$email' AND password='$password'"
    );

    $data = $query ? mysqli_fetch_assoc($query) : null;
    if ($data) {
        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['role'] = $data['role'];

        // redirect berdasarkan role
        if ($data['role'] == 'admin') {
            header("Location: adminn/dashboard.php");
        } else {
            header("Location: user/dashboard.php");
        }
        exit;

    } else {
        $error = "Email atau password salah";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
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
            background: radial-gradient(circle at top, #edf6f1, var(--cream));
            min-height: 100vh;
        }

        .navbar {
            background: var(--green-900);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.14);
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

        .card-wrap {
            max-width: 460px;
        }

        .card {
            border-radius: 14px;
            border: 1px solid #d9e6dd;
        }

        .btn-primary {
            background: var(--green-800);
            border: none;
            border-radius: 999px;
            padding: 10px 16px;
            font-weight: 700;
        }

        .btn-primary:hover {
            background: var(--green-900);
        }

        .card-header {
            background: linear-gradient(135deg, var(--green-900), var(--green-800));
            border-top-left-radius: 14px;
            border-top-right-radius: 14px;
        }

        .text-brand {
            color: var(--green-800) !important;
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
                        <li><a class="dropdown-item" href="index.php?kategori=3">Fiksi</a></li>
                        <li><a class="dropdown-item" href="index.php?kategori=4">Non Fiksi</a></li>
                        <li><a class="dropdown-item" href="index.php?kategori=5">Anak Anak</a></li>
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
                        <a href="user/dashboard.php" class="btn btn-light btn-sm">Dashboard</a>
                    <?php } ?>
                    <div class="dropdown">
                        <button class="btn btn-sm dropdown-toggle d-inline-flex align-items-center gap-2 text-white border-0 bg-transparent" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="asset/login.png" alt="Profil" width="18" height="18">
                            <span><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Profil'); ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
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

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 card-wrap">

                <div class="card shadow-sm">
                    <div class="card-header text-white text-center py-3">
                        <h5 class="mb-0">Login Akun</h5>
                    </div>

                    <div class="card-body">

                        <?php if ($error !== '') { ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php } ?>

                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="Masukkan email"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control"
                                    placeholder="Masukkan password" required>
                            </div>

                            <button type="submit" name="login" class="btn btn-primary w-100">
                                Login
                            </button>
                        </form>

                        <!-- ===== REGISTER LINK ===== -->
                        <hr>
                        <p class="text-center mb-0">
                            Belum punya akun?
                            <a href="register.php" class="fw-semibold text-brand text-decoration-none">
                                Daftar di sini
                            </a>
                        </p>

                    </div>
                </div>

            </div>
        </div>
    </div>

<?php include 'footer.php'; ?>
</body>

<script src="bootstrap/js/bootstrap.bundle.min.js"></script>

</html>