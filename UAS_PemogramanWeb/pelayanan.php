<?php
require_once 'koneksi.php';

// Ambil data dari database (contoh: semua data dari tabel 'pelayanan')
// $query = $db->prepare("SELECT * FROM pelayanan");
// $query->execute();
// $kegiatan = $query->fetchAll(PDO::FETCH_ASSOC);

$query = $db->prepare("SELECT pelayanan.*, perangkat.nama AS nama_perangkat, warga.nama AS nama_warga, bentuk_pelayanan.nama AS bentuk_pelayanan
                       FROM pelayanan
                       INNER JOIN perangkat ON pelayanan.perangkat_id_perangkat = perangkat.id_perangkat
                       INNER JOIN warga ON pelayanan.warga_id_warga = warga.id_warga
                       INNER JOIN bentuk_pelayanan ON pelayanan.bentuk_pelayanan_id = bentuk_pelayanan.id
                       ORDER BY pelayanan.id_pelayanan");
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
        $tanggal = htmlspecialchars($_POST['tanggal']);
        $deskripsi = htmlspecialchars($_POST['deskripsi']);
        $id_perangkat = htmlspecialchars($_POST['perangkat_id_perangkat']);
        $bentuk_pelayanan = htmlspecialchars($_POST['bentuk_pelayanan_id']);
        $warga_id_warga = htmlspecialchars($_POST['warga_id_warga']);

        // Pemeriksaan isset dan empty
        if (!empty($tanggal) && !empty($deskripsi) && !empty($id_perangkat) && !empty($bentuk_pelayanan) && !empty($warga_id_warga)) {

            // Dapatkan ID terakhir dari tabel
            $getLastIdQuery = $db->query("SELECT MAX(id_pelayanan) AS last_id FROM pelayanan");
            $lastIdResult = $getLastIdQuery->fetch(PDO::FETCH_ASSOC);
            $lastId = $lastIdResult['last_id'];

            // Hitung ID baru
            $newId = ($lastId === null) ? 1 : $lastId + 1;

            // Query untuk menambah data baru ke database
            $insertQuery = $db->prepare("INSERT INTO pelayanan (id_pelayanan,tanggal,deskripsi,perangkat_id_perangkat,bentuk_pelayanan_id,warga_id_warga) VALUES (?, ?, ?, ?, ?, ?)");

            try {
                $insertQuery->execute([$newId, $tanggal, $deskripsi, $id_perangkat, $bentuk_pelayanan, $warga_id_warga]);

                // Debugging: Tampilkan nilai variabel setelah eksekusi query
                echo "<script>console.log(" . json_encode([$newId_Pelayanan, $tanggal, $deskripsi, $id_perangkat, $bentuk_pelayanan, $warga_id_warga]) . ");</script>";

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
        $newId_Pelayanan = $_POST['edit_id_pelayanan'];
        $tanggal = htmlspecialchars($_POST['edit_tanggal']);
        $deskripsi = htmlspecialchars($_POST['edit_deskripsi']);
        $id_perangkat = htmlspecialchars($_POST['edit_perangkat_id_perangkat']);
        $bentuk_pelayanan_id = htmlspecialchars($_POST['edit_bentuk_pelayanan_id']);
        $warga_id_warga = htmlspecialchars($_POST['edit_warga_id_warga']);

        // Query untuk mengupdate data ke database
        $updateQuery = $db->prepare("UPDATE pelayanan SET tanggal=?, deskripsi=?, perangkat_id_perangkat=?, bentuk_pelayanan_id=?, warga_id_warga=? WHERE id_pelayanan=?");
        $updateQuery->execute([$tanggal, $deskripsi, $id_perangkat, $bentuk_pelayanan_id, $warga_id_warga, $newId_Pelayanan]);
        // Redirect agar halaman tidak di-refresh
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }

    // Proses form hapus data
    if (isset($_POST['hapus'])) {
        // Ambil ID data yang akan dihapus
        $deleteId = $_POST['delete_id_kegiatan'];

        // Query untuk menghapus data dari database
        $deleteQuery = $db->prepare("DELETE FROM pelayanan WHERE id_pelayanan=?");
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

        .checkbox-column {
            width: 30px;
        }

        .id_pelayanan {
            width: 50px;
        }

        .perangkat_id_perangkat {
            width: 150px;
        }

        .tanggal {
            width: 130px;
        }

        .bentuk_pelayanan_id {
            width: 150px;
        }

        .waktu {
            width: 100px;
        }

        .deskripsi {
            width: 420px;
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
                                        <label for="tanggal" class="form-label">Tanggal</label>
                                        <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="deskripsi" class="form-label">Deskripsi</label>
                                        <input type="text" class="form-control" id="deskripsi" name="deskripsi" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="perangkat_id_perangkat" class="form-label">ID Perangkat</label>
                                        <input type="text" class="form-control" id="perangkat_id_perangkat" name="perangkat_id_perangkat" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="bentuk_pelayanan_id" class="form-label">Bentuk Pelayanan</label>
                                        <input type="text" class="form-control" id="bentuk_pelayanan_id" name="bentuk_pelayanan_id" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="warga_id_warga" class="form-label">ID Warga</label>
                                        <input type="text" class="form-control" id="warga_id_warga" name="warga_id_warga" required>
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
                                    <input type="hidden" name="edit_id_pelayanan" id="edit_id_pelayanan" value="">

                                    <div class="mb-3">
                                        <label for="edit_tanggal" class="form-label">Tanggal</label>
                                        <input type="date" class="form-control" id="edit_tanggal" name="edit_tanggal" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_deskripsi" class="form-label">Deskripsi</label>
                                        <input type="text" class="form-control" id="edit_deskripsi" name="edit_deskripsi" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_perangkat_id_perangkat" class="form-label">ID Perangkat</label>
                                        <input type="text" class="form-control" id="edit_perangkat_id_perangkat" name="edit_perangkat_id_perangkat" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_bentuk_pelayanan_id" class="form-label">Bentuk Pelayanan</label>
                                        <input type="text" class="form-control" id="edit_bentuk_pelayanan_id" name="edit_bentuk_pelayanan_id" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_warga_id_warga" class="form-label">ID Warga</label>
                                        <input type="text" class="form-control" id="edit_warga_id_warga" name="edit_warga_id_warga" required>
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
                <h2 class="heading1 text-center">DATA PELAYANAN KANTOR KELURAHAN</h2>
                <h4 class="heading2 text-center">Tahun 2023</h5>
                    <!-- Tampilkan data dari database -->
                    <table class="table table-dark table-bordered">
                        <thead>
                            <tr>
                                <th class="checkbox-column"></th>
                                <th class="id_pelayanan">ID</th>
                                <th class="tanggal">Tanggal</th>
                                <th class="deskripsi">Deskripsi</th>
                                <th class="perangkat_id_perangkat">Perangkat</th>
                                <th class="bentuk_pelayanan_id">Bentuk Pelayanan</th>
                                <th class="warga_id_warga">ID Warga</th>
                            </tr>
                        </thead>
                    </table>
                    <div class="content-table scrollable-table">
                        <table class="table table-1 table-bordered">
                            <tbody>
                                <?php foreach ($kegiatan as $data) : ?>
                                    <tr>
                                        <td class="checkbox-column">
                                            <input type="checkbox" name="selected_ids[]" value="<?= $data['id_pelayanan'] ?>" data-id="<?= $data['id_pelayanan'] ?>">
                                        </td>
                                        <td class="id_pelayanan"><?= $data['id_pelayanan'] ?></td>
                                        <td class="tanggal"><?= $data['tanggal'] ?></td>
                                        <td class="deskripsi"><?= $data['deskripsi'] ?></td>
                                        <td class="perangkat_id_perangkat"><?= $data['nama_perangkat'] ?></td>
                                        <td class="bentuk_pelayanan_id"><?= $data['bentuk_pelayanan'] ?></td>
                                        <td class="warga_id_warga"><?= $data['nama_warga'] ?></td>
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
                hapusForm.querySelector('#delete_id_kegiatan').value = deleteId; // Fix the ID here

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
                const id_pelayanan = firstCheckbox.val();
                const tanggal = firstCheckbox.closest('tr').find('.tanggal').text();
                const deskripsi = firstCheckbox.closest('tr').find('.deskripsi').text();
                const perangkat_id_perangkat = firstCheckbox.closest('tr').find('.perangkat_id_perangkat').text();
                const bentuk_pelayanan_id = firstCheckbox.closest('tr').find('.bentuk_pelayanan_id').text();
                const warga_id_warga = firstCheckbox.closest('tr').find('.warga_id_warga').text();

                // Mengisi nilai input pada form edit dengan data dari baris yang dicentang
                editForm.find('#edit_id_pelayanan').val(id_pelayanan);
                editForm.find('#edit_tanggal').val(tanggal);
                editForm.find('#edit_deskripsi').val(deskripsi);
                editForm.find('#edit_perangkat_id_perangkat').val(perangkat_id_perangkat);
                editForm.find('#edit_bentuk_pelayanan_id').val(bentuk_pelayanan_id);
                editForm.find('#edit_warga_id_warga').val(warga_id_warga);

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