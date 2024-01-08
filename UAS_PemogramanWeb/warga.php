<?php
require_once 'koneksi.php';

// Ambil data dari database (contoh: semua data dari tabel 'warga')
$query = $db->prepare("SELECT * FROM warga");
$query->execute();
$warga = $query->fetchAll(PDO::FETCH_ASSOC);
// Mulai sesi
session_start();

// Periksa apakah pengguna sudah login
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Query untuk mendapatkan nama berdasarkan no_kontak
    $queryNama = $db->prepare("SELECT nama FROM perangkat WHERE no_kontak = :username");
    $queryNama->bindParam(':username', $username);
    $queryNama->execute();
    $result = $queryNama->fetch(PDO::FETCH_ASSOC);

    // Pastikan query berhasil dan nama ditemukan
    if ($result && isset($result['nama'])) {
        $namaPengguna = $result['nama'];
    } else {
        // Handle jika nama tidak ditemukan
        $namaPengguna = 'Nama Tidak Ditemukan';
    }
} else {
    // Tangani kasus ketika pengguna belum login
    // Redirect atau lakukan tindakan lain sesuai kebutuhan
    // header('Location: login.php');
    // exit();
}

// Proses form tambah data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit'])) {
        // Validasi dan ambil data dari form
        $nama = htmlspecialchars($_POST['nama']);
        $tanggal_lahir = htmlspecialchars($_POST['tanggal_lahir']);
        $no_kontak = htmlspecialchars($_POST['no_kontak']);
        $alamat = htmlspecialchars($_POST['alamat']);
        $gender = htmlspecialchars($_POST['gender']);

        // Pemeriksaan isset dan empty
        if (!empty($nama) && !empty($tanggal_lahir) && !empty($no_kontak) && !empty($alamat) && isset($gender)) {

            // Dapatkan ID terakhir dari tabel
            $getLastIdQuery = $db->query("SELECT MAX(id_warga) AS last_id FROM warga");
            $lastIdResult = $getLastIdQuery->fetch(PDO::FETCH_ASSOC);
            $lastId = $lastIdResult['last_id'];

            // Hitung ID baru
            $newId = ($lastId === null) ? 1 : $lastId + 1;

            // Query untuk menambah data baru ke database
            $insertQuery = $db->prepare("INSERT INTO warga (id_warga, nama, tanggal_lahir, no_kontak, alamat, gender_id_gender) VALUES (?, ?, ?, ?, ?, ?)");

            try {
                $insertQuery->execute([$newId, $nama, $tanggal_lahir, $no_kontak, $alamat, $gender]);

                // Debugging: Tampilkan nilai variabel setelah eksekusi query
                echo "<script>console.log(" . json_encode([$newId, $nama, $tanggal_lahir, $no_kontak, $alamat, $gender]) . ");</script>";

                // Redirect jika berhasil
                header("Location: {$_SERVER['PHP_SELF']}");
                exit();
            } catch (PDOException $e) {
                // Tampilkan pesan kesalahan jika terjadi kesalahan dalam eksekusi query
                echo "Error: " . $e->getMessage();
            }
        } else {
            // Handle jika ada variabel $_POST yang tidak terdefinisi atau kosong
            echo "Error: Some POST variables are not set or empty.";
        }
    }

    // Proses form edit data
    if (isset($_POST['edit'])) {
        // Ambil data dari form
        $id = $_POST['edit_id'];
        $nama = htmlspecialchars($_POST['edit_nama']);
        $tanggal_lahir = htmlspecialchars($_POST['edit_tanggal_lahir']);
        $no_kontak = htmlspecialchars($_POST['edit_no_kontak']);
        $alamat = htmlspecialchars($_POST['edit_alamat']);
        $gender = htmlspecialchars($_POST['edit_gender']);

        // Query untuk mengupdate data ke database
        $updateQuery = $db->prepare("UPDATE warga SET nama=?, tanggal_lahir=?, no_kontak=?, alamat=?, gender_id_gender=? WHERE id_warga=?");
        $updateQuery->execute([$nama, $tanggal_lahir, $no_kontak, $alamat, $gender, $id]);

        // Redirect agar halaman tidak di-refresh
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }

    // Proses form hapus data
    if (isset($_POST['hapus'])) {
        // Ambil ID data yang akan dihapus
        $deleteId = $_POST['delete_id'];

        // Query untuk menghapus data dari database
        $deleteQuery = $db->prepare("DELETE FROM warga WHERE id_warga=?");
        $deleteQuery->execute([$deleteId]);

        // Redirect agar halaman tidak di-refresh
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <title>Kantor Kelurahan</title>

</head>

<body>
    <style>
        body {
            padding-top: 56px;
            /* Set this value to the height of your fixed navbar */
        }

        @media (min-width: 768px) {
            body {
                padding-top: 0;
            }
        }

        /* Add your custom styles for the sidebar here */
        #sidebar {
            height: 100vh;
            width: 200px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1;
            padding-top: 56px;
            padding-left: 20px;
            background-color: #000;
            /* Set this value to the height of your fixed navbar */
        }

        #content {
            margin-left: 200px;
            margin-top: 10px;
            /* Adjust this value based on your sidebar width */
            padding: 15px;
        }

        #sidebar a {
            padding: 15px;
            text-decoration: none;
            color: #ffffff;
            display: block;
            font-size: 20px;
            font-weight: 700;
        }

        #sidebar a:active,
        #sidebar a:hover {
            background-color: #ffffff;
            color: #000;
            border-radius: 20px 0 0 20px;
        }

        #sidebar .account-icon {
            text-decoration: none;
            color: #ffffff;
            display: block;
            width: 150px;
            text-align: center;
            margin-bottom: 20px;
        }

        .account-icon i {
            font-size: 100px;
            margin-bottom: 5px;
        }

        .account-icon h5 {
            font-size: 15px;
            font-weight: 700;
        }

        .heading1 {
            font-weight: 700;
            font-size: 30px;
        }

        .heading2 {
            margin-bottom: 20px;
        }

        .scrollable-table {
            overflow-y: auto;
            display: block;
        }

        .table {
            border: 1px solid var(--text-color);
            width: 99%;
            text-align: center;
        }

        .table-1 {
            width: 100%;
            height: 450px;
            border: 1px solid var(--bg-color);
        }

        .content-table {
            width: 100%;
            height: 450px;
            font-size: 14px;
        }

        .checkbox-column {
            width: 30px;
        }

        .id {
            width: 50px;
        }

        .nama {
            width: 280px;
        }

        .tanggal_lahir,
        .nomer {
            width: 150px;
        }

        .alamat {
            width: 350px;
        }

        .jenis_kelamin {
            width: 100px;
        }

        #sidebar .logout {
            text-align: end;
            justify-content: center;
            margin-top: 9rem;
        }

        #sidebar .logout #log:hover {
            padding: 15px;
            text-decoration: none;
            color: #ffffff;
            background-color: #000;
            display: block;
            font-size: 40px;
            font-weight: 700;
        }

        .logout #log {
            padding: 15px;
            text-decoration: none;
            color: #ffffff;
            background-color: #000;
            display: block;
            font-size: 40px;
            font-weight: 700;
        }

        .logout .log i {
            font-size: 40px;
        }
    </style>
    <header>
        <div class="container">
            <div id="sidebar">
                <?php if (isset($namaPengguna)) : ?>
                    <div class="account-icon">
                        <i class='bx bxs-user-circle'></i>
                        <h5><?php echo $namaPengguna; ?></h5>
                    </div>
                <?php endif; ?>
                <a href="beranda.php" <?php echo ($currentPage == 'index.php') ? 'class="active"' : ''; ?>>Beranda</a>
                <a href="warga.php" class="active" <?php echo ($currentPage == 'warga.php') ? 'class="active"' : ''; ?>>Data Warga</a>
                <a href="kegiatan.php" <?php echo ($currentPage == 'kegiatan.php') ? 'class="active"' : ''; ?>>Data Kegiatan</a>
                <a href="perangkat.php" <?php echo ($currentPage == 'perangkat.php') ? 'class="active"' : ''; ?>>Data Perangkat</a>
                <a href="pelayanan.php" <?php echo ($currentPage == 'pelayanan.php') ? 'class="active"' : ''; ?>>Data Pelayanan</a>
                <div class="logout">
                    <a href="#" id="log" data-bs-toggle="modal" data-bs-target="#konfirmasiLogoutModal"><i class='bx bx-exit'></i></a>
                </div>
            </div>
        </div>
    </header>

    <main id="content">
        <section id="section" class="section">
            <div class="container" id="container">
                <!-- Modal Konfirmasi Logout -->
                <div class="modal fade" id="konfirmasiLogoutModal" tabindex="-1" aria-labelledby="konfirmasiLogoutModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="konfirmasiLogoutModalLabel">Konfirmasi Keluar</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Apakah Anda yakin ingin keluar?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button>
                                <a href="login.php" class="btn btn-primary">Ya</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="tambahModal" tabindex="-1" aria-labelledby="tambahModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="tambahModalLabel">Tambah Data</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Form untuk menambah data -->
                                <form method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
                                    <!-- Tambahkan input hidden untuk id -->
                                    <div class="mb-3">
                                        <label for="nama" class="form-label">Nama</label>
                                        <input type="text" class="form-control" id="nama" name="nama" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                        <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="no_kontak" class="form-label">Nomer Kontak</label>
                                        <input type="text" class="form-control" id="no_kontak" name="no_kontak" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="alamat" class="form-label">Alamat</label>
                                        <input type="text" class="form-control" id="alamat" name="alamat" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="gender" class="form-label">Gender</label>
                                        <select class="form-select" id="gender" name="gender" required>
                                            <option value="1">Laki-laki</option>
                                            <option value="0">Perempuan</option>
                                        </select>
                                    </div>
                                    <button type="submit" name="submit" class="btn btn-primary">Tambah Data</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                    <!-- ... (isi modal edit data) ... -->
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModalLabel">Edit Data</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Form untuk mengedit data -->
                                <form method="post" action="<?= $_SERVER['PHP_SELF'] ?>" id="editForm">
                                    <!-- Tambahkan input hidden untuk id -->
                                    <input type="hidden" name="edit_id" id="edit_id" value="">

                                    <div class="mb-3">
                                        <label for="edit_nama" class="form-label">Nama</label>
                                        <input type="text" class="form-control" id="edit_nama" name="edit_nama" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                        <input type="date" class="form-control" id="edit_tanggal_lahir" name="edit_tanggal_lahir" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_no_kontak" class="form-label">Nomer Kontak</label>
                                        <input type="text" class="form-control" id="edit_no_kontak" name="edit_no_kontak" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_alamat" class="form-label">Alamat</label>
                                        <input type="text" class="form-control" id="edit_alamat" name="edit_alamat" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_gender" class="form-label">Gender</label>
                                        <select class="form-select" id="edit_gender" name="edit_gender" required>
                                            <option value="1">Laki-laki</option>
                                            <option value="0">Perempuan</option>
                                        </select>
                                    </div>
                                    <button type="submit" name="edit" class="btn btn-warning">Simpan Perubahan</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="hapusModal" tabindex="-1" aria-labelledby="hapusModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="hapusModalLabel">Hapus Data</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Apakah Anda yakin ingin menghapus data ini?</p>
                                <!-- Form untuk menghapus data -->
                                <form method="post" action="<?= $_SERVER['PHP_SELF'] ?>" id="hapusForm">
                                    <!-- Tambahkan input hidden untuk menyimpan ID yang akan dihapus -->
                                    <input type="hidden" name="delete_id" id="delete_id" value="">
                                    <button type="submit" id="hapusButton" name="hapus" class="btn btn-danger" data-bs-dismiss="modal">Ya, Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <h2 class="heading1 text-center">DATA WARGA KANTOR KELURAHAN</h2>
                <h4 class="heading2 text-center">Tahun 2023</h5>
                    <!-- Tampilkan data dari database -->
                    <table class="table table-dark table-bordered">
                        <thead>
                            <tr>
                                <th class="checkbox-column"></th>
                                <th class="id">ID</th>
                                <th class="nama">Nama</th>
                                <th class="tanggal_lahir">Tanggal Lahir</th>
                                <th class="nomer">Nomer Kontak</th>
                                <th class="alamat">Alamat</th>
                                <th class="jenis_kelamin">Jenis Kelamin</th>
                            </tr>
                        </thead>
                    </table>
                    <div class="content-table scrollable-table">
                        <table class="table table-1 table-bordered">
                            <tbody>
                                <?php foreach ($warga as $data) : ?>
                                    <tr>
                                        <td class="checkbox-column">
                                            <input type="checkbox" name="selected_ids[]" value="<?= $data['id_warga'] ?>" data-id="<?= $data['id_warga'] ?>">
                                        </td>
                                        <td class="id"><?= $data['id_warga'] ?></td>
                                        <td class="nama"><?= $data['nama'] ?></td>
                                        <td class="tanggal_lahir"><?= $data['tanggal_lahir'] ?></td>
                                        <td class="nomer"><?= $data['no_kontak'] ?></td>
                                        <td class="alamat"><?= $data['alamat'] ?></td>
                                        <td class="jenis_kelamin"><?= ($data['gender_id_gender'] == 1) ? 'Laki-laki' : 'Perempuan' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-primary mt-3 w-25" data-bs-toggle="modal" data-bs-target="#tambahModal">
                        Tambah Data
                    </button>
                    <button type="button" id="editButton" class="btn btn-warning mt-3 ms-2 w-25" data-bs-toggle="modal" data-bs-target="#editModal">
                        Edit
                    </button>
                    <button type="button" class="btn btn-danger mt-3 ms-2 w-25" data-bs-toggle="modal" data-bs-target="#hapusModal">
                        Hapus
                    </button>
            </div>
            </div>
        </section>
    </main>


    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Kemudian baru muat skrip JavaScript Anda -->
    <!-- Bootstrap JS and Popper.js (required for Bootstrap components) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Script Hapus Data -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hapusButton = document.querySelector('#hapusButton');
            const hapusForm = document.querySelector('#hapusForm');
            const hapusModal = new bootstrap.Modal(document.getElementById('hapusModal'));

            hapusButton.addEventListener('click', function() {
                // Mendapatkan semua checkbox yang dicentang
                const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]:checked');

                // Jika tidak ada yang dicentang, beri peringatan
                if (checkboxes.length === 0) {
                    alert('Pilih setidaknya satu baris untuk dihapus.');
                    return;
                }

                // Ambil data dari baris yang dicentang pertama
                const firstCheckbox = checkboxes[0];
                const deleteId = firstCheckbox.value;

                // Set nilai input pada form hapus
                hapusForm.querySelector('#delete_id').value = deleteId;

                // Tampilkan modal hapus
                hapusModal.show();

                // Panggil fungsi untuk mengeksekusi form hapus
                submitForm();
            });

            // Fungsi untuk mengeksekusi form hapus
            function submitForm() {
                hapusForm.submit();
            }
        });
    </script>
    <!-- Script Edit Data -->
    <script>
        $(document).ready(function() {
            const editButton = $('#editButton');
            const editForm = $('#editForm');
            const editModal = $('#editModal');

            editButton.on('click', function() {
                // Mendapatkan semua checkbox yang dicentang
                const checkboxes = $('input[name="selected_ids[]"]:checked');

                // Jika tidak ada yang dicentang, beri peringatan
                if (checkboxes.length === 0) {
                    alert('Pilih setidaknya satu baris untuk diedit.');
                    return;
                }

                // Ambil data dari baris yang dicentang pertama
                const firstCheckbox = checkboxes.eq(0);
                const id = firstCheckbox.val();
                const nama = firstCheckbox.closest('tr').find('.nama').text();
                const tanggal_lahir = firstCheckbox.closest('tr').find('.tanggal_lahir').text();
                const no_kontak = firstCheckbox.closest('tr').find('.nomer').text();
                const alamat = firstCheckbox.closest('tr').find('.alamat').text();
                const gender = firstCheckbox.closest('tr').find('.jenis_kelamin').text();

                // Mengisi nilai input pada form edit dengan data dari baris yang dicentang
                editForm.find('#edit_id').val(id);
                editForm.find('#edit_nama').val(nama);
                editForm.find('#edit_tanggal_lahir').val(tanggal_lahir);
                editForm.find('#edit_no_kontak').val(no_kontak);
                editForm.find('#edit_alamat').val(alamat);
                const genderValue = (gender.trim().toLowerCase() === 'laki-laki') ? '1' : '0';
                editForm.find('#edit_gender').val(genderValue);

                // Tampilkan modal edit
                editModal.modal('show');
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = '<?php echo $currentPage; ?>';

            // Menemukan link di sidebar berdasarkan nilai currentPage
            const currentLink = document.querySelector(`#sidebar a[href="${currentPage}"]`);

            // Menggunakan classList untuk menambah kelas "active" pada link yang sesuai
            if (currentLink) {
                currentLink.classList.add('active');
            }
        });
    </script>

</body>

</html>