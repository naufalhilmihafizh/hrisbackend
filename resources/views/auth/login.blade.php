<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — HRIS</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- App CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js']) 
</head>
<body class="login-page">
    <div class="login-card">
        <div class="login-logo">
            <div class="login-logo-icon">
                <i data-lucide="building-2"></i>
            </div>
            <div class="login-logo-text">HRIS</div>
        </div>
        
        <h1 class="login-title">Selamat Datang Kembali</h1>
        <p class="login-subtitle">Silakan login untuk mengakses sistem HRIS</p>

        @if($errors->any())
            <div class="alert alert-danger mb-6">
                <i data-lucide="alert-circle"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('web.login.post') }}">
            @csrf
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus placeholder="nama@perusahaan.com">
            </div>

            <div class="form-group mb-6">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-primary login-btn">
                <span>Login ke Sistem</span>
                <i data-lucide="arrow-right"></i>
            </button>
        </form>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
