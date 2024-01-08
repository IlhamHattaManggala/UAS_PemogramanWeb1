<?php
require_once 'koneksi.php';

// Ambil data dari database (contoh: semua data dari tabel 'warga')
$query = $db->prepare("SELECT kegiatan.*, perangkat.nama AS nama_perangkat
                        FROM kegiatan
                        INNER JOIN perangkat ON kegiatan.perangkat_id_perangkat = perangkat.id_perangkat
                        ORDER BY kegiatan.id_kegiatan");
$query->execute();
$kegiatan = $query->fetchAll(PDO::FETCH_ASSOC);

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
        $nama_kegiatan = htmlspecialchars($_POST['nama_kegiatan']);
        $tempat_pelaksanaan = htmlspecialchars($_POST['tempat_pelaksanaan']);
        $awal_pelaksanaan = htmlspecialchars($_POST['awal_pelaksanaan']);
        $akhir_pelaksanaan = htmlspecialchars($_POST['akhir_pelaksanaan']);
        $id_perangkat = htmlspecialchars($_POST['id_perangkat']);
        $waktu = htmlspecialchars($_POST['waktu']);
        $deskripsi = htmlspecialchars($_POST['deskripsi']);

        // Pemeriksaan isset dan empty
        if (!empty($nama_kegiatan) && !empty($tempat_pelaksanaan) && !empty($awal_pelaksanaan)  && !empty($id_perangkat) && !empty($waktu) && isset($deskripsi)) {

            // Dapatkan ID terakhir dari tabel
            $getLastIdQuery = $db->query("SELECT MAX(id_kegiatan) AS last_id FROM kegiatan");
            $lastIdResult = $getLastIdQuery->fetch(PDO::FETCH_ASSOC);
            $lastId = $lastIdResult['last_id'];

            // Hitung ID baru
            $newId = ($lastId === null) ? 1 : $lastId + 1;

            // Query untuk menambah data baru ke database
            $insertQuery = $db->prepare("INSERT INTO kegiatan (id_kegiatan,nama_kegiatan,tempat_pelaksanaan,awal_pelaksanaan,akhir_pelaksanaan, id_perangkat,waktu,deskripsi) VALUES (?, ?, ?, ?, ?, ?)");

            try {
                $insertQuery->execute([$newId_kegiatan, $nama_kegiatan, $tempat_pelaksanaan, $awal_pelaksanaan, $akhir_pelaksanaan, $id_perangkat, $waktu, $deskripsi]);

                // Debugging: Tampilkan nilai variabel setelah eksekusi query
                echo "<script>console.log(" . json_encode([$newId_kegiatan, $nama_kegiatan, $tempat_pelaksanaan, $awal_pelaksanaan, $akhir_pelaksanaan, $id_perangkat, $waktu, $deskripsi]) . ");</script>";

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
        $id_kegiatan = $_POST['edit_id_kegiatan'];
        $nama_kegiatan = htmlspecialchars($_POST['edit_nama_kegiatan']);
        $tempat_pelaksanaan = htmlspecialchars($_POST['edit_tempat_pelaksanaan']);
        $awal_pelaksanaan = htmlspecialchars($_POST['edit_awal_pelaksanaan']);
        $akhir_pelaksanaan = htmlspecialchars($_POST['edit_akhir_pelaksanaan']);
        $id_perangkat = htmlspecialchars($_POST['edit_id_perangkat']);
        $waktu = htmlspecialchars($_POST['edit_waktu']);
        $deskripsi = htmlspecialchars($_POST['edit_deskripsi']);

        // Query untuk mengupdate data ke database
        $updateQuery = $db->prepare("UPDATE kegiatan SET nama_kegiatan=?, tempat_pelaksanaan=?, awal_pelaksanaan=?, akhir_pelaksanaan=?, id_perangkat=?, waktu=?, deskripsi=? WHERE id_kegiatan=?");
        $updateQuery->execute([$nama_kegiatan, $tempat_pelaksanaan, $awal_pelaksanaan, $akhir_pelaksanaan, $id_perangkat, $waktu, $waktu, $deskripsi]);

        // Redirect agar halaman tidak di-refresh
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }

    // Proses form hapus data
    if (isset($_POST['hapus'])) {
        // Ambil ID data yang akan dihapus
        $deleteId = $_POST['delete_id_kegiatan'];

        // Query untuk menghapus data dari database
        $deleteQuery = $db->prepare("DELETE FROM kegiatan WHERE id_kegiatan=?");
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
        }

        .table thead,
        .table tbody {
            font-size: 15px;
        }

        .checkbox-column {
            width: 30px;
        }

        .id_kegiatan {
            width: 60px;
        }

        .tempat_pelaksanaan {
            width: 250px;
        }

        .nama_kegiatan {
            width: 250px;
        }

        .awal_pelaksanaan,
        .akhir_pelaksanaan {
            width: 150px;
        }

        .waktu {
            width: 100px;
        }

        .deskripsi {
            width: 280px;
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
                <a href="warga.php" <?php echo ($currentPage == 'warga.php') ? 'class="active"' : ''; ?>>Data Warga</a>
                <a href="kegiatan.php" class="active" <?php echo ($currentPage == 'kegiatan.php') ? 'class="active"' : ''; ?>>Data Kegiatan</a>
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
                                        <label for="nama_kegiatan" class="form-label">Nama Kegiatan</label>
                                        <input type="text" class="form-control" id="nama_kegiatan" name="nama_kegiatan" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="tempat_pelaksanaan" class="form-label">Tempat Pelaksanaan</label>
                                        <input type="text" class="form-control" id="tempat_pelaksanaan" name="tempat_pelaksanaan" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="awal_pelaksanaan" class="form-label">Awal Pelaksanaan</label>
                                        <input type="date" class="form-control" id="awal_pelaksanaan" name="awal_pelaksanaan" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="akhir_pelaksanaan" class="form-label">Akhir Pelaksanaan</label>
                                        <input type="date" class="form-control" id="akhir_pelaksanaan" name="akhir_pelaksanaan" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="id_perangkat" class="form-label">Id Perangkat</label>
                                        <input type="text" class="form-control" id="id_perangkat" name="id_perangkat" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="waktu" class="form-label">Waktu</label>
                                        <input type="text" class="form-control" id="waktu" name="waktu" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="deskripsi" class="form-label">Deskripsi</label>
                                        <input type="text" class="form-control" id="deskripsi" name="deskripsi" required>
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
                                    <input type="hidden" name="edit_id_kegiatan" id="edit_id_kegiatan" value="">

                                    <div class="mb-3">
                                        <label for="edit_nama_kegiatan" class="form-label">Nama Kegiatan</label>
                                        <input type="text" class="form-control" id="edit_nama_kegiatan" name="edit_nama_kegiatan" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_tempat_pelaksanaan" class="form-label">Tempat Pelaksanaan</label>
                                        <input type="text" class="form-control" id="edit_tempat_pelaksanaan" name="edit_tempat_pelaksanaan" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_awal_pelaksanaan" class="form-label">Awal Pelaksanaan</label>
                                        <input type="date" class="form-control" id="edit_awal_pelaksanaan" name="edit_awal_pelaksanaan" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_akhir_pelaksanaan" class="form-label">Akhir Pelaksanaan</label>
                                        <input type="date" class="form-control" id="edit_akhir_pelaksanaan" name="edit_akhir_pelaksanaan" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_id_perangkat" class="form-label">Id Perangkat</label>
                                        <input type="text" class="form-control" id="edit_id_perangkat" name="edit_id_perangkat" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_waktu" class="form-label">Waktu</label>
                                        <input type="text" class="form-control" id="edit_waktu" name="edit_waktu" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_deskripsi" class="form-label">Deskripsi</label>
                                        <input type="text" class="form-control" id="edit_deskripsi" name="edit_deskripsi" required>
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
                                    <input type="hidden" name="delete_id_kegiatan" id="delete_id_kegiatan" value="">
                                    <button type="submit" id="hapusButton" name="hapus" class="btn btn-danger" data-bs-dismiss="modal">Ya, Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <h2 class="heading1 text-center">DATA KEGIATAN KANTOR KELURAHAN</h2>
                <h4 class="heading2 text-center">Tahun 2023</h5>
                    <!-- Tampilkan data dari database -->
                    <table class="table table-dark table-bordered">
                        <thead>
                            <tr>
                                <th class="checkbox-column"></th>
                                <th class="id_kegiatan">ID</th>
                                <th class="nama_kegiatan">Nama Kegiatan</th>
                                <th class="tempat_pelaksanaan">Tempat Pelaksanaan</th>
                                <th class="awal_pelaksanaan">Awal Pelaksanaan</th>
                                <th class="akhir_pelaksanaan">Akhir Pelaksanaan</th>
                                <th class="id_perangkat">Perangkat</th>
                                <th class="waktu">Waktu</th>
                                <th class="deskripsi">Deskripsi</th>
                            </tr>
                        </thead>
                    </table>
                    <div class="content-table scrollable-table">
                        <table class="table table-1 table-bordered">
                            <tbody>
                                <?php foreach ($kegiatan as $data) : ?>
                                    <tr>
                                        <td class="checkbox-column">
                                            <input type="checkbox" name="selected_ids[]" value="<?= $data['id_kegiatan'] ?>" data-id="<?= $data['id_kegiatan'] ?>">
                                        </td>
                                        <td class="id_kegiatan"><?= $data['id_kegiatan'] ?></td>
                                        <td class="nama_kegiatan"><?= $data['nama_kegiatan'] ?></td>
                                        <td class="tempat_pelaksanaan"><?= $data['tempat_pelaksanaan'] ?></td>
                                        <td class="awal_pelaksanaan"><?= $data['awal_pelaksanaan'] ?></td>
                                        <td class="akhir_pelaksanaan"><?= $data['akhir_pelaksanaan'] ?></td>
                                        <td class="id_perangkat"><?= $data['nama_perangkat'] ?></td>
                                        <td class="waktu"><?= $data['waktu'] ?></td>
                                        <td class="deskripsi"><?= $data['deskripsi'] ?></td>
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
                hapusForm.querySelector('#delete_id_kegiatan').value = deleteId;

                // Tampilkan modal hapus
                hapusModal.show();
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
                const id_kegiatan = firstCheckbox.val();
                const nama_kegiatan = firstCheckbox.closest('tr').find('.nama_kegiatan').text();
                const tempat_pelaksanaan = firstCheckbox.closest('tr').find('.tempat_pelaksanaan').text();
                const awal_pelaksanaan = firstCheckbox.closest('tr').find('.awal_pelaksanaan').text();
                const akhir_pelaksanaan = firstCheckbox.closest('tr').find('.akhir_pelaksanaan').text();
                const id_perangkat = firstCheckbox.closest('tr').find('.id_perangkat').text();
                const waktu = firstCheckbox.closest('tr').find('.waktu').text();
                const deskripsi = firstCheckbox.closest('tr').find('.deskripsi').text();

                // Mengisi nilai input pada form edit dengan data dari baris yang dicentang
                editForm.find('#edit_id_kegiatan').val(id_kegiatan);
                editForm.find('#edit_nama_kegiatan').val(nama_kegiatan);
                editForm.find('#edit_tempat_pelaksanaan').val(tempat_pelaksanaan);
                editForm.find('#edit_awal_pelaksanaan').val(awal_pelaksanaan);
                editForm.find('#edit_akhir_pelaksanaan').val(akhir_pelaksanaan);
                editForm.find('#edit_id_perangkat').val(id_perangkat);
                editForm.find('#edit_waktu').val(waktu);
                editForm.find('#edit_deskripsi').val(deskripsi);

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