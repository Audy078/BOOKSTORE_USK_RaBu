<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit;
}

if (isset($_POST['kirim'])) {
    $pesan = $_POST['pesan'];
    $id_user = $_SESSION['id_user'];

    mysqli_query(
        $conn,
        "INSERT INTO pesan (id_user, pesan)
         VALUES ('$id_user', '$pesan')"
    );
tuh
    $sukses = true;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Hubungi Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">

    <style>
        body {
            background-color: #f4f7fb;
        }

        .navbar {
            background-color: #2f5d9f;
        }

        .card {
            border-radius: 14px;
        }

        .btn-primary {
            background-color: #3b6fb6;
            border: none;
        }

        .btn-primary:hover {
            background-color: #345f9e;
        }
    </style>
</head>

<body>

    <!-- ===== NAVBAR ===== -->
    <nav class="navbar navbar-dark">
        <div class="container">
            <span class="navbar-brand fw-bold">📩 Hubungi Admin</span>
            <a href="dashboard.php" class="btn btn-light btn-sm">
                Kembali
            </a>
        </div>
    </nav>

    <!-- ===== CONTENT ===== -->
    <div class="container mt-4">

        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <h5 class="mb-0">Kirim Pesan ke Admin</h5>
                    </div>

                    <div class="card-body">

                        <?php if (isset($sukses)) { ?>
                            <div class="alert alert-success text-center">
                                Pesan berhasil dikirim ke admin
                            </div>
                        <?php } ?>

                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Pesan</label>
                                <textarea name="pesan" class="form-control" rows="5"
                                    placeholder="Tulis pesan atau pertanyaan Anda..." required></textarea>
                            </div>

                            <button type="submit" name="kirim" class="btn btn-primary w-100">
                                Kirim Pesan
                            </button>
                        </form>

                    </div>
                </div>

            </div>
        </div>

    </div>

<?php include '../footer.php'; ?>
</body>

</html>