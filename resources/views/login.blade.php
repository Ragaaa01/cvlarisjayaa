<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: url('{{ asset('img/background.jpg') }}') no-repeat center center;
            background-size: cover;
        }
        .login-card {
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            padding: 2rem;
            border-radius: 12px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }
        .login-card img {
            width: 80px;
            display: block;
            margin: 0 auto 20px;
        }
        @media (max-width: 576px) {
            .login-card {
                padding: 1.5rem;
                margin: 0 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-card">
        <img src="{{ asset('img/logolarisjaya.jpg') }}" alt="Logo Perusahaan">
        <h4 class="text-center mb-4">Silakan Masuk</h4>

        <!-- Tampilkan pesan error atau sukses dari session -->
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Form Login -->
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                       name="email" placeholder="Email" value="{{ old('email') }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                       name="password" placeholder="Password" required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3 text-end">
                <a href="#" class="text-decoration-none">Lupa Password?</a>
                <!-- Pastikan rute 'forgot-password' sudah didefinisikan jika fitur ini diaktifkan -->
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>