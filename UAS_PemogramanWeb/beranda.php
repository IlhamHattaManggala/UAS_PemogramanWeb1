<?php
require_once 'koneksi.php';

// Ambil data dari database (contoh: semua data dari tabel 'warga')
$query = $db->prepare("SELECT * FROM warga");
$query->execute();
$warga = $query->fetchAll(PDO::FETCH_ASSOC);

// Mulai sesi
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

$queryKegiatan = $db->prepare("SELECT * FROM kegiatan");
$queryKegiatan->execute();
$kegiatan = $queryKegiatan->fetchAll(PDO::FETCH_ASSOC);

$queryPerangkat = $db->prepare("SELECT * FROM perangkat");
$queryPerangkat->execute();
$perangkat = $queryPerangkat->fetchAll(PDO::FETCH_ASSOC);

$queryPelayanan = $db->prepare("SELECT * FROM pelayanan");
$queryPelayanan->execute();
$pelayanan = $queryPelayanan->fetchAll(PDO::FETCH_ASSOC);

$queryBentukPelayanan = $db->prepare("SELECT * FROM bentuk_pelayanan");
$queryBentukPelayanan->execute();
$bentukPelayananDatabase = $queryBentukPelayanan->fetchAll(PDO::FETCH_ASSOC);

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
            margin-top: 20px;
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

        #sidebar a:active {
            background-color: #ffffff;
            color: #000;
            border-radius: 20px 0 0 20px;
        }

        #sidebar a:hover {
            background-color: #ffffff;
            color: #000;
            border-radius: 20px 0 0 20px;
        }

        #container-content {
            width: 100%;
        }

        #container-content img {
            width: 100%;
            border-radius: 10px;
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
                <a href="beranda.php" class="active" <?php echo ($currentPage == 'beranda.php') ? 'class="active"' : ''; ?>>Beranda</a>
                <a href="warga.php" <?php echo ($currentPage == 'warga.php') ? 'class="active"' : ''; ?>>Data Warga</a>
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

        <section id="section" class="section">
            <div class="container" id="container">
                <div class="container-content" id="container-content">
                    <img src="img/SELAMAT DATANG DI.png" alt="">
                </div>
                <h3 id="heading" class="heading fw-bold fs-3 text-center">GRAFIK TABEL WARGA</h3>
                <canvas id="genderChart" width="600" height="150"></canvas>
                <h3 id="heading" class="heading fw-bold fs-3 text-center mt-5">GRAFIK TABEL KEGIATAN</h3>
                <canvas id="kegiatanChart" width="500" height="150"></canvas>
                <h3 id="heading" class="heading fw-bold fs-3 text-center mt-5">GRAFIK TABEL PERANGKAT</h3>
                <div class="row">
                    <div class="col-md-6">
                        <canvas id="perangkatChart" width="300" height="150"></canvas>
                    </div>
                    <div class="col-md-6">
                        <canvas id="birthYearChart" width="300" height="150"></canvas>
                    </div>
                </div>
                <h3 id="heading" class="heading fw-bold fs-3 text-center mt-5">GRAFIK TABEL PELAYANAN PERBULAN</h3>
                <canvas id="pelayananPerBulanChart" width="600" height="150"></canvas>
            </div>
        </section>
    </main>


    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap JS and Popper.js (required for Bootstrap components) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = '<?php echo $currentPage; ?>';

            // Menemukan link di sidebar berdasarkan nilai currentPage
            const currentLink = document.querySelector(`#sidebar a[href="${currentPage}"]`);

            // Menggunakan classList untuk menambah kelas "active" pada link yang sesuai
            if (currentLink) {
                currentLink.classList.add('active');
            }
            console.log('currentPage:', currentPage);
            console.log('currentLink:', currentLink);
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get data from PHP
            const wargaData = <?= json_encode($warga) ?>;

            // Count the total number of data, males, and females
            const totalData = wargaData.length;
            const males = wargaData.filter(data => data.gender_id_gender === 1).length;
            const females = wargaData.filter(data => data.gender_id_gender === 0).length;

            // Create a bar chart
            const ctx = document.getElementById('genderChart').getContext('2d');
            const genderChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Total Data', 'Laki-Laki', 'Perempuan'],
                    datasets: [{
                        label: 'Number of Data',
                        data: [totalData, males, females],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                        ],
                        borderWidth: 1,
                    }],
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                        },
                    },
                },
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get data from PHP
            const kegiatanData = <?= json_encode($kegiatan) ?>;

            // Count the total number of data
            const totalDataKegiatan = kegiatanData.length;

            // Define the order of months
            const monthOrder = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

            // Group kegiatan data by month
            const groupedByMonth = kegiatanData.reduce((acc, kegiatan) => {
                const month = new Date(kegiatan.awal_pelaksanaan).toLocaleString('en-us', {
                    month: 'long'
                }); // Full month name
                acc[month] = (acc[month] || 0) + 1;
                return acc;
            }, {});

            // Extract months and counts in the correct order
            const months = monthOrder.map(month => groupedByMonth[month] || 0);

            // Define custom colors
            const backgroundColors = [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)'
            ];
            const borderColors = [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ];

            // Create a bar chart for kegiatan with custom colors
            const ctxKegiatan = document.getElementById('kegiatanChart').getContext('2d');
            const kegiatanChart = new Chart(ctxKegiatan, {
                type: 'bar',
                data: {
                    labels: monthOrder,
                    datasets: [{
                        label: 'Jumlah Kegiatan',
                        data: months,
                        backgroundColor: backgroundColors,
                        borderColor: borderColors,
                        borderWidth: 1,
                    }],
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                        },
                    },
                },
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const perangkatData = <?= json_encode($perangkat) ?>;

            const groupedByGender = perangkatData.reduce((acc, perangkat) => {
                const gender = perangkat.gender_id_gender === '0' ? 'Perempuan' : 'Laki-laki';
                acc[gender] = (acc[gender] || 0) + 1;
                return acc;
            }, {});

            console.log('Grouped by Gender:', groupedByGender);

            const males = groupedByGender['Laki-laki'] || 0;
            const females = groupedByGender['Perempuan'] || 0;

            const ctxPerangkat = document.getElementById('perangkatChart').getContext('2d');
            const perangkatChart = new Chart(ctxPerangkat, {
                type: 'bar',
                data: {
                    labels: ['Laki-laki', 'Perempuan'],
                    datasets: [{
                        label: 'Jumlah Perangkat',
                        data: [males, females],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 99, 132, 0.2)',
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 99, 132, 1)',
                        ],
                        borderWidth: 1,
                    }],
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                        },
                    },
                },
            });


            const years = perangkatData.map(perangkat => new Date(perangkat.tanggal_lahir).getFullYear());
            const sortedYears = [...new Set(years)].sort((a, b) => a - b);

            const countsYears = sortedYears.map(year => {
                const count = perangkatData.filter(perangkat => new Date(perangkat.tanggal_lahir).getFullYear() === year).length;
                return count;
            });

            const ctxBirthYear = document.getElementById('birthYearChart').getContext('2d');
            const birthYearChart = new Chart(ctxBirthYear, {
                type: 'bar',
                data: {
                    labels: sortedYears.map(year => `Tahun ${year}`),
                    datasets: [{
                        label: 'Jumlah Perangkat',
                        data: countsYears,
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)',
                        ],
                        borderColor: [
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)',
                        ],
                        borderWidth: 1,
                    }],
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                        },
                    },
                },
            });
        });
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dapatkan data dari PHP untuk Pelayanan
            const pelayananData = <?= json_encode($pelayanan) ?>;

            // Filter data pelayanan yang memiliki tanggal yang valid
            const validPelayananData = pelayananData.filter(pelayanan => pelayanan.tanggal !== null && pelayanan.tanggal !== undefined);

            // Groupkan data pelayanan berdasarkan jenis layanan dan bulan
            // Ambil data dari PHP
            const bentukPelayananDatabase = <?= json_encode($bentukPelayananDatabase) ?>;

            // Buat objek pemetaan dari ID ke Nama Bentuk Pelayanan
            const bentukPelayananMap = {};
            bentukPelayananDatabase.forEach(item => {
                bentukPelayananMap[item.id] = item.nama;
            });

            // Proses data
            const groupedByMonth = validPelayananData.reduce((acc, pelayanan) => {
                const month = new Date(pelayanan.tanggal).toLocaleString('en-us', {
                    month: 'long'
                });
                const serviceName = bentukPelayananMap[pelayanan.bentuk_pelayanan_id] || 'Tidak Diketahui';
                const key = `${month} - ${serviceName}`;

                acc[key] = (acc[key] || 0) + 1;
                return acc;
            }, {});

            // Tambahkan log untuk memeriksa data yang digunakan untuk membuat grafik
            console.log('Dikelompokkan berdasarkan Bulan dan Layanan:', groupedByMonth);

            // Urutkan kunci berdasarkan jumlah secara menurun
            const sortedKeys = Object.keys(groupedByMonth).sort((a, b) => groupedByMonth[b] - groupedByMonth[a]);

            // Ambil kategori teratas untuk setiap bulan
            const uniqueMonths = [...new Set(sortedKeys.map(key => key.split(' - ')[0]))];
            const topCategories = uniqueMonths.map(month => {
                const category = sortedKeys.find(key => key.startsWith(`${month} -`));
                return category ? category.split(' - ')[1] : 'Tidak Diketahui';
            });

            // Ekstrak jumlah berdasarkan bulan dan kategori yang sudah diurutkan
            const countsService = uniqueMonths.map((month, index) => groupedByMonth[`${month} - ${topCategories[index]}`]);

            // Buat grafik batang untuk Pelayanan per bulan dengan warna kustom
            const ctxPelayananPerBulan = document.getElementById('pelayananPerBulanChart').getContext('2d');
            const pelayananPerBulanChart = new Chart(ctxPelayananPerBulan, {
                type: 'bar',
                data: {
                    labels: uniqueMonths,
                    datasets: [{
                        label: 'Jumlah pelayanan yang dilakukan',
                        data: countsService,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            // Tambahkan warna lain jika diperlukan
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            // Tambahkan warna lain jika diperlukan
                        ],
                        borderWidth: 1,
                    }],
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                        },
                    },
                },
            });
        });
    </script>


</body>

</html>