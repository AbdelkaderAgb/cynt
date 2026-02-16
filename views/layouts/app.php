<?php
/**
 * CYN Tourism - Main Application Layout (Tailwind CSS + Alpine.js)
 * 
 * This layout wraps all authenticated pages with the sidebar, topbar,
 * and common scripts. Uses Tailwind CSS v3 via CDN (no build step).
 * 
 * Variables available:
 *   $content   - The rendered view content (injected by Controller::view)
 *   $pageTitle - Page title string
 *   $activePage - Current active page key for sidebar highlighting
 *   $user      - Current authenticated user array
 */

$user = $user ?? Auth::user();
$pageTitle = $pageTitle ?? 'Dashboard';
$currentLang = function_exists('getCurrentLang') ? getCurrentLang() : 'en';
$notificationCount = function_exists('get_notification_count') ? get_notification_count() : 0;
?>
<!DOCTYPE html>
<html lang="<?php echo e($currentLang); ?>" x-data="{ darkMode: localStorage.getItem('theme') === 'dark' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?> - <?php echo e(COMPANY_NAME); ?></title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS v3 via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.3s ease-out',
                        'fade-in-up': 'fadeInUp 0.4s ease-out',
                        'slide-in': 'slideIn 0.3s ease-out',
                        'pulse-slow': 'pulse 3s infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        slideIn: {
                            '0%': { opacity: '0', transform: 'translateX(-20px)' },
                            '100%': { opacity: '1', transform: 'translateX(0)' },
                        },
                    },
                },
            },
        }
    </script>

    <!-- Alpine.js v3 -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Minimal custom styles for elements Tailwind can't easily handle -->
    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Selection */
        ::selection { background: #3b82f6; color: white; }

        /* Print */
        @media print {
            .no-print { display: none !important; }
            .main-area { margin-left: 0 !important; }
        }
    </style>
</head>
<body class="font-sans bg-slate-50 text-slate-900 dark:bg-slate-900 dark:text-slate-100 antialiased min-h-screen">

    <!-- App Shell -->
    <div x-data="appShell()" class="flex min-h-screen">

        <!-- Sidebar -->
        <?php include App::getBasePath() . '/views/partials/sidebar.php'; ?>

        <!-- Sidebar Overlay (mobile) -->
        <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
             class="fixed inset-0 bg-black/50 backdrop-blur-sm z-30 lg:hidden no-print" x-cloak></div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col transition-all duration-300"
             :class="sidebarCollapsed ? 'lg:ml-[72px]' : 'lg:ml-64'">

            <!-- Top Bar -->
            <?php include App::getBasePath() . '/views/partials/topbar.php'; ?>

            <!-- Page Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <?php echo $content; ?>
            </main>

            <!-- Footer -->
            <footer class="border-t border-slate-200 dark:border-slate-700 px-6 py-4 text-center text-sm text-slate-500 dark:text-slate-400 no-print">
                &copy; <?php echo date('Y'); ?> <?php echo e(COMPANY_NAME); ?>. All rights reserved.
            </footer>
        </div>
    </div>

    <!-- Toast Container -->
    <div x-data="toastManager()" id="toast-container"
         class="fixed top-6 right-6 z-[999] flex flex-col gap-3 pointer-events-none">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible" x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-12"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-12"
                 class="pointer-events-auto bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 p-4 flex items-start gap-3 min-w-[320px] max-w-[400px]">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg flex-shrink-0"
                     :class="toast.iconClass">
                    <i :class="toast.icon"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div x-show="toast.title" class="font-semibold text-slate-900 dark:text-white text-sm" x-text="toast.title"></div>
                    <div class="text-sm text-slate-600 dark:text-slate-300" x-text="toast.message"></div>
                </div>
                <button @click="removeToast(toast.id)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        </template>
    </div>

    <!-- App JavaScript -->
    <script src="<?php echo url('assets/js/app.js'); ?>"></script>
</body>
</html>
