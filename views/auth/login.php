<?php
/**
 * Login View — Standalone Tailwind CSS + Alpine.js
 * 
 * Variables: $errors, $currentLang, $pageTitle
 */
?>
<!DOCTYPE html>
<html lang="<?php echo e($currentLang ?? 'en'); ?>" x-data="{ darkMode: localStorage.getItem('theme') === 'dark' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo e($pageTitle ?? 'Login'); ?> - <?php echo e(COMPANY_NAME); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS v3 CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                    animation: {
                        'float': 'float 20s ease-in-out infinite',
                        'float-reverse': 'float 15s ease-in-out infinite reverse',
                        'fade-in-up': 'fadeInUp 0.6s ease-out',
                        'shake': 'shake 0.5s ease-in-out',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px) rotate(0deg)' },
                            '33%': { transform: 'translateY(-30px) rotate(5deg)' },
                            '66%': { transform: 'translateY(15px) rotate(-3deg)' },
                        },
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(30px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        shake: {
                            '0%, 100%': { transform: 'translateX(0)' },
                            '10%, 30%, 50%, 70%, 90%': { transform: 'translateX(-5px)' },
                            '20%, 40%, 60%, 80%': { transform: 'translateX(5px)' },
                        },
                    },
                },
            },
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-600 via-blue-500 to-cyan-500 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 relative overflow-hidden antialiased">

    <!-- Animated Background Shapes -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-48 -right-48 w-[600px] h-[600px] rounded-full bg-white/10 animate-float"></div>
        <div class="absolute -bottom-24 -left-24 w-[400px] h-[400px] rounded-full bg-white/10 animate-float-reverse"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] rounded-full bg-white/5"></div>
    </div>

    <!-- Theme Toggle -->
    <button @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light')"
            class="absolute top-4 right-4 z-10 w-11 h-11 rounded-full bg-white/20 dark:bg-slate-700/50 backdrop-blur-sm text-white dark:text-slate-300 flex items-center justify-center hover:bg-white/30 dark:hover:bg-slate-600/50 transition-all shadow-lg">
        <i class="fas" :class="darkMode ? 'fa-sun text-amber-400' : 'fa-moon'"></i>
    </button>

    <!-- Login Card -->
    <div class="relative z-10 w-full max-w-md mx-4 animate-fade-in-up" x-data="loginForm()">
        <div class="bg-white/95 dark:bg-slate-800/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/20 dark:border-slate-700/50 p-8 sm:p-10">

            <!-- Logo & Header -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 mx-auto mb-5 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-500/30 transform hover:scale-105 transition-transform">
                    <i class="fas fa-plane text-white text-3xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white"><?php echo e(COMPANY_NAME); ?></h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-2"><?php echo __('login_subtitle') ?: 'Sign in to your account'; ?></p>

                <!-- Language Switcher -->
                <div class="flex justify-center gap-2 mt-4">
                    <a href="?lang=en" class="px-3 py-1.5 rounded-lg text-xs font-semibold uppercase transition-all
                        <?php echo ($currentLang ?? 'en') === 'en' 
                            ? 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400 shadow-sm' 
                            : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'; ?>">
                        EN
                    </a>
                    <a href="?lang=tr" class="px-3 py-1.5 rounded-lg text-xs font-semibold uppercase transition-all
                        <?php echo ($currentLang ?? 'en') === 'tr' 
                            ? 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400 shadow-sm' 
                            : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'; ?>">
                        TR
                    </a>
                </div>
            </div>

            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="mb-6 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-xl px-4 py-3 flex items-center gap-3 animate-shake">
                    <i class="fas fa-exclamation-circle text-red-500 text-lg flex-shrink-0"></i>
                    <div class="text-sm text-red-700 dark:text-red-400">
                        <?php foreach ($errors as $error): ?>
                            <div><?php echo e($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="<?php echo url('/login'); ?>" @submit="handleSubmit" class="space-y-5">
                <?php echo csrf_field(); ?>

                <!-- Email Field -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5"><?php echo __('email') ?: 'Email'; ?></label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="email" name="email" required autocomplete="email"
                               value="<?php echo e($_POST['email'] ?? ''); ?>"
                               placeholder="name@company.com"
                               class="w-full pl-11 pr-4 py-3 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 dark:focus:border-blue-400 text-slate-900 dark:text-white placeholder-slate-400 transition-all">
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5"><?php echo __('password') ?: 'Password'; ?></label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input :type="showPassword ? 'text' : 'password'" name="password" required
                               placeholder="••••••••"
                               class="w-full pl-11 pr-12 py-3 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 dark:focus:border-blue-400 text-slate-900 dark:text-white placeholder-slate-400 transition-all">
                        <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                            <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Forgot -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-blue-500 focus:ring-blue-500/30 bg-slate-50 dark:bg-slate-700">
                        <span class="text-sm text-slate-600 dark:text-slate-400"><?php echo __('remember_me') ?: 'Remember me'; ?></span>
                    </label>
                    <a href="<?php echo url('/reset-password'); ?>" class="text-sm text-blue-500 hover:text-blue-600 dark:hover:text-blue-400 font-medium hover:underline transition-colors">
                        <?php echo __('forgot_password') ?: 'Forgot password?'; ?>
                    </a>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full py-3 px-4 bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-xl hover:shadow-blue-500/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 flex items-center justify-center gap-2"
                        :class="loading ? 'opacity-80 pointer-events-none' : ''">
                    <span x-show="!loading"><?php echo __('login') ?: 'Sign In'; ?></span>
                    <span x-show="loading" class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <?php echo __('logging_in') ?: 'Signing in...'; ?>
                    </span>
                </button>
            </form>
        </div>

        <!-- Footer -->
        <p class="text-center text-white/60 text-sm mt-6">
            &copy; <?php echo date('Y'); ?> <?php echo e(COMPANY_NAME); ?>
        </p>
    </div>

    <script>
        function loginForm() {
            return {
                showPassword: false,
                loading: false,
                handleSubmit(e) {
                    this.loading = true;
                }
            }
        }
    </script>
</body>
</html>
