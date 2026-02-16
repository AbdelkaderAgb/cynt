<?php
/**
 * CYN Tourism - Partner Portal Layout
 * Separate from admin layout — clean partner-facing design
 */
$partner = $partner ?? Auth::partner();
$pageTitle = $pageTitle ?? 'Portal';
$activePage = $activePage ?? '';
$currentLang = function_exists('getCurrentLang') ? getCurrentLang() : 'en';

// Unread messages count for badge
$unreadCount = 0;
if ($partner) {
    try {
        $db = Database::getInstance()->getConnection();
        $unreadCount = (int)$db->query("SELECT COUNT(*) FROM partner_messages WHERE partner_id = {$partner['id']} AND sender_type = 'admin' AND is_read = 0")->fetchColumn();
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="<?= e($currentLang) ?>" x-data="{ darkMode: localStorage.getItem('theme') === 'dark', sidebarOpen: window.innerWidth >= 1024 }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - CYN Tourism Partner Portal</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
                }
            }
        };
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .portal-gradient { background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%); }
        .portal-card { backdrop-filter: blur(20px); }
        .sidebar-link { transition: all 0.2s ease; }
        .sidebar-link:hover, .sidebar-link.active { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
        .sidebar-link.active { border-left: 3px solid #3b82f6; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen">

<!-- Top Header Bar -->
<header class="portal-gradient text-white shadow-lg fixed top-0 left-0 right-0 z-50 h-16">
    <div class="flex items-center justify-between h-full px-6">
        <div class="flex items-center gap-4">
            <button @click="sidebarOpen = !sidebarOpen" class="text-white/80 hover:text-white lg:hidden">
                <i class="fas fa-bars text-lg"></i>
            </button>
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-blue-500 rounded-xl flex items-center justify-center font-bold text-sm">
                    <i class="fas fa-plane"></i>
                </div>
                <div>
                    <h1 class="text-base font-bold leading-none">CYN TURIZM</h1>
                    <span class="text-[10px] text-blue-300 uppercase tracking-widest">Partner Portal</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <!-- Dark mode toggle -->
            <button @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light')"
                    class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center transition">
                <i class="fas" :class="darkMode ? 'fa-sun text-yellow-300' : 'fa-moon text-blue-200'"></i>
            </button>

            <!-- Messages -->
            <a href="<?= url('portal/messages') ?>" class="relative w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center transition">
                <i class="fas fa-envelope text-sm"></i>
                <?php if ($unreadCount > 0): ?>
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full text-[10px] flex items-center justify-center font-bold"><?= $unreadCount ?></span>
                <?php endif; ?>
            </a>

            <!-- Partner info -->
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-xs font-bold">
                    <?= strtoupper(substr($partner['company_name'] ?? 'P', 0, 1)) ?>
                </div>
                <div class="hidden sm:block">
                    <div class="text-sm font-semibold leading-none"><?= e($partner['company_name'] ?? 'Partner') ?></div>
                    <div class="text-[10px] text-blue-300"><?= e($partner['email'] ?? '') ?></div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Sidebar -->
<aside class="fixed top-16 left-0 h-[calc(100vh-4rem)] w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 z-40 transform transition-transform duration-200"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <nav class="p-4 space-y-1">
        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-3 px-3">Navigation</p>

        <a href="<?= url('portal/dashboard') ?>" @click.stop="if(window.innerWidth < 1024) sidebarOpen = false"
           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 <?= $activePage === 'portal-dashboard' ? 'active' : '' ?>">
            <i class="fas fa-th-large w-5 text-center"></i> Dashboard
        </a>

        <a href="<?= url('portal/invoices') ?>" @click.stop="if(window.innerWidth < 1024) sidebarOpen = false"
           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 <?= $activePage === 'portal-invoices' ? 'active' : '' ?>">
            <i class="fas fa-file-invoice w-5 text-center"></i> Invoices
        </a>

        <a href="<?= url('portal/vouchers') ?>" @click.stop="if(window.innerWidth < 1024) sidebarOpen = false"
           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 <?= $activePage === 'portal-vouchers' ? 'active' : '' ?>">
            <i class="fas fa-receipt w-5 text-center"></i> Vouchers
        </a>

        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mt-6 mb-3 px-3">Services</p>

        <a href="<?= url('portal/bookings') ?>" @click.stop="if(window.innerWidth < 1024) sidebarOpen = false"
           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 <?= $activePage === 'portal-bookings' ? 'active' : '' ?>">
            <i class="fas fa-calendar-plus w-5 text-center"></i> Booking Requests
        </a>

        <a href="<?= url('portal/messages') ?>" @click.stop="if(window.innerWidth < 1024) sidebarOpen = false"
           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 <?= $activePage === 'portal-messages' ? 'active' : '' ?>">
            <i class="fas fa-comments w-5 text-center"></i> Messages
            <?php if ($unreadCount > 0): ?>
                <span class="ml-auto bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full"><?= $unreadCount ?></span>
            <?php endif; ?>
        </a>

        <a href="<?= url('portal/receipts') ?>" @click.stop="if(window.innerWidth < 1024) sidebarOpen = false"
           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 <?= $activePage === 'portal-receipts' ? 'active' : '' ?>">
            <i class="fas fa-file-invoice-dollar w-5 text-center"></i> Receipts
        </a>

        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mt-6 mb-3 px-3">Account</p>

        <a href="<?= url('portal/profile') ?>" @click.stop="if(window.innerWidth < 1024) sidebarOpen = false"
           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 <?= $activePage === 'portal-profile' ? 'active' : '' ?>">
            <i class="fas fa-user-circle w-5 text-center"></i> Profile
        </a>

        <a href="<?= url('portal/logout') ?>"
           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
            <i class="fas fa-sign-out-alt w-5 text-center"></i> Logout
        </a>
    </nav>
</aside>

<!-- Main Content -->
<main class="pt-16 lg:pl-64 min-h-screen">
    <div class="p-4 sm:p-6">
        <?= $content ?>
    </div>
</main>

<!-- Click outside to close sidebar on mobile -->
<div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/30 z-30 lg:hidden" x-transition.opacity></div>

</body>
</html>
