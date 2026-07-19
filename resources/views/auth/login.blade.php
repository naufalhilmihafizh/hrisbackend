{!! $tailwind_cdn ?? '' !!}
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
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, .font-heading { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 flex items-center justify-center min-h-screen">

    <div class="bg-slate-900 border border-slate-800 p-8 rounded-2xl shadow-xl max-w-md w-full mx-4">
        <!-- Logo -->
        <div class="flex items-center gap-3 mb-8">
            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-600/20">
                <i data-lucide="building-2" class="w-5 h-5"></i>
            </div>
            <div class="text-xl font-bold tracking-wider font-heading text-white">HRIS</div>
        </div>
        
        <!-- Title -->
        <h1 class="text-2xl font-bold tracking-tight text-white mb-1">Selamat Datang Kembali</h1>
        <p class="text-slate-400 text-sm mb-6">Silakan login untuk mengakses sistem HRIS</p>

        <!-- Alert Error -->
        @if($errors->any())
            <div class="flex items-center gap-2 bg-red-950/50 border border-red-900/50 text-red-400 p-3 rounded-lg text-sm mb-6">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('web.login.post') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2" for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@perusahaan.com"
                    class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-3 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-200">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2" for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="••••••••"
                    class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-3 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-200">
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm py-3 rounded-lg transition duration-200 flex items-center justify-center gap-2 group shadow-lg shadow-blue-600/10">
                <span>Login ke Sistem</span>
                <i data-lucide="arrow-right" class="w-4 h-4 transform group-hover:translate-x-1 transition-transform"></i>
            </button>
        </form>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>