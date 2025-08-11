<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV Laris Jaya Gas - Solusi Gas Industri Terpercaya</title>
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        .hero-section {
            background-color: #002D55 !important;
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        .hero-section h1 {
            font-size: 3rem;
            font-weight: 700;
        }
        .hero-section p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
        .btn-custom {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 30px;
            font-size: 1.1rem;
            border-radius: 25px;
            transition: background-color 0.3s ease;
        }
        .btn-custom:hover {
            background-color: #0056b3;
            color: white;
        }
        .service-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .download-section {
            background-color: #007bff;
            color: white;
            padding: 60px 0;
        }
        .download-section img {
            max-width: 150px;
            margin: 20px;
        }
        .footer {
            background-color: #343a40;
            color: white;
            padding: 40px 0;
        }
        .footer a {
            color: #007bff;
            text-decoration: none;
        }
        .footer a:hover {
            color: #0056b3;
        }
        .navbar-brand {
            display: flex;
            align-items: center;
        }
        .navbar-brand-text {
            margin-left: 10px;
            font-size: 1.2rem;
            font-weight: 600;
            color: #002D55 !important;
        }
        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2rem;
            }
            .hero-section p {
                font-size: 1rem;
            }
            .navbar-brand-text {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="{{ asset('img/logolarisjaya.jpg') }}" alt="Laris Jaya Gas Logo" style="width: 40px; height: 40px;">
                <span class="navbar-brand-text">Laris Jaya Gas</span>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Layanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Tentang Kami</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Kontak</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <h1>CV Laris Jaya Gas</h1>
            <p>Solusi Terpercaya untuk Peminjaman dan Isi Ulang Tabung Gas Industri</p>
            <a href="#download" class="btn btn-custom">Unduh Aplikasi Sekarang</a>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Layanan Kami</h2>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card service-card">
                        <img src="{{ asset('img/background.jpg') }}" class="card-img-top" alt="Peminjaman Tabung">
                        <div class="card-body text-center">
                            <h5 class="card-title">Peminjaman Tabung</h5>
                            <p class="card-text">Solusi peminjaman tabung gas yang fleksibel untuk kebutuhan industri Anda.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card service-card">
                        <img src="{{ asset('img/background.jpg') }}" class="card-img-top" alt="Isi Ulang Tabung">
                        <div class="card-body text-center">
                            <h5 class="card-title">Isi Ulang Tabung</h5>
                            <p class="card-text">Layanan isi ulang tabung gas yang cepat, aman, dan terjangkau.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-4">Tentang Kami</h2>
            <p class="text-center">CV Laris Jaya Gas adalah penyedia layanan gas industri terpercaya yang berlokasi di Jl. Karangampel Gang 2 Utara. Kami menyediakan berbagai jenis gas seperti Oksigen, Nitrogen, Acetylene, Argon, Dinitrogen, dan sebagainya, dengan layanan peminjaman dan isi ulang tabung gas berkualitas tinggi untuk mendukung kebutuhan industri Anda. Dengan pengalaman dan dedikasi, kami siap menjadi mitra terbaik Anda.</p>
        </div>
    </section>

    <!-- Download App Section -->
    <section id="download" class="download-section">
        <div class="container text-center">
            <h2>Unduh Aplikasi Kami</h2>
            <p>Pesan tabung gas, cek status peminjaman, dan kelola transaksi Anda dengan mudah melalui aplikasi mobile kami.</p>
            <div class="d-flex justify-content-center">
                <a href="#download-app" class="btn btn-custom">Download di Sini</a>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Kontak Kami</h2>
            <div class="row">
                <div class="col-md-6">
                    <h5>Hubungi Kami</h5>
                    <p><i class="fas fa-map-marker-alt mr-2"></i>Jl. Karangampel Gang 2 Utara</p>
                    <p><i class="fas fa-phone-alt mr-2"></i>+62 123 456 7890</p>
                    <p><i class="fas fa-envelope mr-2"></i>info@larisjayagas.com</p>
                </div>
                <div class="col-md-6">
                    <h5>Jam Operasional</h5>
                    <p>Senin - Jumat: 08:00 - 17:00</p>
                    <p>Sabtu: 08:00 - 14:00</p>
                    <p>Minggu: Tutup</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container text-center">
            <p>© {{ date('Y') }} CV Laris Jaya Gas. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap 4 JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
    <script>
        // Smooth scroll untuk navigasi
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>