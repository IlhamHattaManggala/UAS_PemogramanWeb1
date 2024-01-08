<?php
session_start();
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve values from the form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query to check if the combination of username and password exists in the database
    $query = $db->prepare("SELECT * FROM perangkat WHERE no_kontak = :username AND nama LIKE :nama");
    $query->bindParam(':username', $username);
    $query->bindValue(':nama', $password . '%', PDO::PARAM_STR); // Use bindValue for non-variable values
    $query->execute();
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Simpan informasi pengguna dalam sesi
        $_SESSION['id_perangkat'] = $user['id_perangkat']; // Sesuaikan dengan kolom ID pengguna di database Anda
        $_SESSION['username'] = $user['no_kontak']; // Sesuaikan dengan kolom username di database Anda
        $_SESSION['nama'] = $user['nama']; // Sesuaikan dengan kolom nama di database Anda
        header('Location: beranda.php');
        exit();
    } else {
        // Invalid login, you may display an error message or redirect to the login page
        $error_message = 'Invalid login credentials';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url(img/slide2.jpg);
            background-size: cover;
            background-position: center;
            margin: 0;
            /* Menghilangkan margin default pada body */
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            /* Menetapkan tinggi body menjadi 100% dari viewport height */
        }

        .login-container {
            max-width: 700px;
            margin-top: 50px;
            /* Menyesuaikan margin-top untuk perangkat dengan lebar layar lebih kecil */
        }

        .login-form {
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: rgba(255, 255, 255, 0.8);
            width: 400px;
            /* Atur nilai max-width sesuai kebutuhan Anda */
            height: auto;
        }


        .login-form h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .form-control {
            height: 40px;
            transition: border-color 0.3s ease-in-out;
        }

        .form-control:focus {
            border-color: #007bff;
        }

        .btn-login-container {
            text-align: right;
            /* Menempatkan tombol di sebelah kanan form */
        }

        .btn-login {
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
            margin-top: 20px;
            /* Mengurangi margin-top agar tidak terlalu jauh di perangkat mobile */
            width: 100%;
        }

        .btn-login:hover {
            background-color: #0056b3;
        }

        /* Media query untuk responsivitas */
        @media (max-width: 768px) {
            .login-container {
                margin-top: 20px;
                max-width: 500px;
                /* Menyesuaikan margin-top untuk perangkat dengan lebar layar lebih kecil */
            }

            .login-form {
                height: auto;
                max-width: 600px;
                width: 100%;
                /* Mengubah tinggi menjadi otomatis */
            }
        }

        @media (max-width: 250px) {
            .login-container {
                margin-left: 10px;
                margin-right: 10px;
                /* max-width: 200px; */
                /* Menyesuaikan margin-top untuk perangkat dengan lebar layar lebih kecil */
            }

            .login-form {
                height: auto;
                width: 100%;
                /* Mengubah tinggi menjadi otomatis */
            }

            .login-form label {
                font-size: 14px;
            }

            .form-control {
                height: 35px;
            }
        }

        @media (max-width: 200px) {
            .login-container {
                margin-left: 10px;
                margin-right: 10px;
                /* max-width: 200px; */
                /* Menyesuaikan margin-top untuk perangkat dengan lebar layar lebih kecil */
            }

            .login-form {
                height: auto;
                width: 100%;
                /* Mengubah tinggi menjadi otomatis */
            }

            .login-form label {
                font-size: 14px;
            }

            .form-control {
                height: 35px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <form class="login-form" method="post" action="login.php">
            <h2>Login</h2>
            <div class="form-group">
                <label for="username">Username (No Kontak):</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password (Nama Kata Pertama):</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="btn-login-container">
                <button type="submit" class="btn btn-login">Login</button>
            </div>
        </form>
    </div>

    <?php if (isset($error_message)) : ?>
        <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="errorModalLabel">Error</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger" role="alert">
                            <?php echo $error_message; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bootstrap JS and Popper.js (optional) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            // Aktifkan modal dengan ID errorModal
            var myModal = new bootstrap.Modal(document.getElementById('errorModal'));
            myModal.show();
        </script>
    <?php endif; ?>
</body>

</html>