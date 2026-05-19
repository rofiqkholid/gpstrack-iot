<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - GPS MoSafe Tracker</title>

    <!-- Google Fonts - Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'bg-light': '#F8FAFC',
                        'bg-card': '#FFFFFF',
                        'bg-input': '#F1F5F9',
                        'accent': '#3B82F6',
                        'accent-dark': '#1D4ED8',
                        'success': '#10B981',
                        'danger': '#EF4444',
                        'text-primary': '#0F172A',
                        'text-secondary': '#64748B',
                        'border-color': '#E2E8F0',
                    },
                    fontFamily: {
                        sans: ['Outfit', '-apple-system', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>

<body class="font-sans bg-bg-light text-text-primary min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <!-- Abstract Background Gradients (Light theme) -->
    <div class="absolute w-[450px] h-[450px] rounded-full bg-blue-400/10 blur-[130px] top-[-10%] left-[-10%] pointer-events-none"></div>
    <div class="absolute w-[500px] h-[500px] rounded-full bg-indigo-300/10 blur-[140px] bottom-[-10%] right-[-10%] pointer-events-none"></div>

    <div class="w-full max-w-[460px] z-10 transition-all duration-300 transform">
        <!-- Logo and Heading -->
        <div class="text-center mb-8">
            <div class="inline-flex w-14 h-14 bg-accent/10 rounded-none items-center justify-center text-accent text-[24px] mb-3 shadow-md shadow-accent/5 border border-accent/20">
                <i class="fas fa-globe-asia"></i>
            </div>
            <h1 class="text-[26px] font-bold tracking-tight text-text-primary">GPS<span class="text-accent">Track</span></h1>
            <p class="text-text-secondary text-sm mt-1.5 font-medium">Sistem Monitoring IoT GPS & Servis MoSafe</p>
        </div>

        <!-- Premium White Login Card -->
        <div class="bg-bg-card border border-border-color rounded-none p-7 md:p-9 shadow-xl shadow-slate-200/50">
            <h2 class="text-xl font-semibold mb-6 text-text-primary">Masuk ke Dashboard</h2>

            @if($errors->any())
            <!-- Error Notification -->
            <div class="mb-5 bg-danger/5 border border-danger/20 rounded-none p-4 flex gap-3 text-sm text-danger animate-shake">
                <i class="fas fa-exclamation-circle text-base mt-0.5 shrink-0"></i>
                <div>
                    <span class="font-bold">Gagal Masuk:</span>
                    <p class="mt-0.5 text-danger/95">{{ $errors->first() }}</p>
                </div>
            </div>
            @endif

            @if(session('success'))
            <!-- Success Notification -->
            <div class="mb-5 bg-success/5 border border-success/20 rounded-none p-4 flex gap-3 text-sm text-success">
                <i class="fas fa-check-circle text-base mt-0.5 shrink-0"></i>
                <div>
                    <p class="text-success/95">{{ session('success') }}</p>
                </div>
            </div>
            @endif

            <form action="{{ url('/login') }}" method="POST" class="space-y-5">
                @csrf
                
                <!-- Username Input -->
                <div class="space-y-2">
                    <label for="username" class="text-xs font-semibold uppercase tracking-wider text-text-secondary">Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-text-secondary text-[15px]">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" name="username" id="username" 
                            class="w-full bg-bg-light border border-border-color hover:border-slate-300 focus:border-accent focus:bg-white focus:ring-1 focus:ring-accent rounded-none py-3.5 pl-11 pr-4 text-sm text-text-primary placeholder-text-secondary/40 outline-none transition-all duration-200"
                            placeholder="Masukkan Username" 
                            value="{{ old('username') }}" 
                            required autofocus>
                    </div>
                </div>

                <!-- Password Input -->
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <label for="password" class="text-xs font-semibold uppercase tracking-wider text-text-secondary">Kata Sandi</label>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-text-secondary text-[15px]">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" name="password" id="password" 
                            class="w-full bg-bg-light border border-border-color hover:border-slate-300 focus:border-accent focus:bg-white focus:ring-1 focus:ring-accent rounded-none py-3.5 pl-11 pr-4 text-sm text-text-primary placeholder-text-secondary/40 outline-none transition-all duration-200"
                            placeholder="••••••••" 
                            required>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2.5 cursor-pointer group">
                        <input type="checkbox" name="remember" class="accent-accent w-4 h-4 rounded-none border-border-color bg-white text-accent focus:ring-0 cursor-pointer">
                        <span class="text-xs text-text-secondary font-medium group-hover:text-text-primary transition-colors duration-150">Ingat Saya</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                    class="w-full bg-accent hover:bg-accent-dark text-white font-bold py-3.5 px-4 rounded-none shadow-lg shadow-accent/20 hover:shadow-accent-dark/25 transition-all duration-200 text-sm flex items-center justify-center gap-2 group cursor-pointer">
                    <span>Masuk Aplikasi</span>
                    <i class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition-transform duration-200"></i>
                </button>
            </form>
        </div>

        <!-- Footer copy -->
        <p class="text-center text-text-secondary/50 text-xs mt-6 font-medium">
            &copy; 2026 GPS MoSafe System. Hak Cipta Dilindungi.
        </p>
    </div>

    <style>
        /* Shake animation for errors */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        .animate-shake {
            animation: shake 0.5s ease-in-out;
        }
    </style>
</body>

</html>
