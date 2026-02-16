<?php
/**
 * Top Bar Partial — Tailwind CSS + Alpine.js
 * 
 * Variables: $pageTitle, $user, $notificationCount
 */

$user = $user ?? Auth::user();
$notificationCount = $notificationCount ?? 0;
$currentLang = function_exists('getCurrentLang') ? getCurrentLang() : 'en';
?>

<header class="sticky top-0 z-20 bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg border-b border-slate-200 dark:border-slate-700 h-16 flex items-center px-4 sm:px-6 no-print">
    <div class="flex items-center justify-between w-full gap-4">

        <!-- Left: Mobile Menu Toggle + Search -->
        <div class="flex items-center gap-3">
            <!-- Mobile hamburger -->
            <button @click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden flex items-center justify-center w-10 h-10 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <i class="fas fa-bars text-lg"></i>
            </button>

            <!-- Search -->
            <div class="relative hidden sm:block w-72">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" placeholder="<?php echo __('search') ?: 'Search...'; ?>"
                       class="w-full pl-10 pr-4 py-2 text-sm bg-slate-100 dark:bg-slate-700 border-0 rounded-full focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:bg-white dark:focus:bg-slate-600 text-slate-700 dark:text-slate-200 placeholder-slate-400 transition-all">
            </div>
        </div>

        <!-- Right: Actions -->
        <div class="flex items-center gap-2">

            <!-- Language Switcher -->
            <div class="flex bg-slate-100 dark:bg-slate-700 rounded-lg p-0.5 gap-0.5">
                <a href="?lang=en" class="px-2 py-1 rounded-md text-xs font-semibold uppercase transition-all
                    <?php echo $currentLang === 'en' ? 'bg-white dark:bg-slate-600 text-brand-600 dark:text-brand-400 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'; ?>">
                    EN
                </a>
                <a href="?lang=tr" class="px-2 py-1 rounded-md text-xs font-semibold uppercase transition-all
                    <?php echo $currentLang === 'tr' ? 'bg-white dark:bg-slate-600 text-brand-600 dark:text-brand-400 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'; ?>">
                    TR
                </a>
            </div>

            <!-- Dark Mode Toggle -->
            <button @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light')"
                    class="w-10 h-10 flex items-center justify-center rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <i class="fas" :class="darkMode ? 'fa-sun text-amber-400' : 'fa-moon'"></i>
            </button>

            <!-- Notifications -->
            <a href="<?php echo url('/notifications'); ?>" class="relative w-10 h-10 flex items-center justify-center rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <i class="fas fa-bell"></i>
                <?php if ($notificationCount > 0): ?>
                    <span class="absolute top-1 right-1 min-w-[18px] h-[18px] px-1 flex items-center justify-center text-[10px] font-bold bg-red-500 text-white rounded-full animate-pulse-slow">
                        <?php echo (int)$notificationCount; ?>
                    </span>
                <?php endif; ?>
            </a>

            <!-- User Dropdown -->
            <div x-data="{ open: false }" class="relative ml-1">
                <button @click="open = !open" @click.away="open = false"
                        class="flex items-center gap-2 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-500 to-cyan-500 flex items-center justify-center text-white font-semibold text-sm">
                        <?php echo strtoupper(substr($user['full_name'] ?? $user['email'] ?? 'U', 0, 1)); ?>
                    </div>
                    <span class="hidden sm:block text-sm font-medium text-slate-700 dark:text-slate-200">
                        <?php echo e($user['full_name'] ?? $user['email'] ?? 'User'); ?>
                    </span>
                    <i class="fas fa-chevron-down text-xs text-slate-400 hidden sm:inline"></i>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open" x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 top-full mt-2 w-56 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xl py-2 z-50"
                     x-cloak>
                    <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-700 mb-1">
                        <div class="text-sm font-medium text-slate-900 dark:text-white"><?php echo e($user['full_name'] ?? 'User'); ?></div>
                        <div class="text-xs text-slate-500"><?php echo e($user['email'] ?? ''); ?></div>
                    </div>
                    <a href="<?php echo url('/profile'); ?>" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <i class="fas fa-user w-4 text-center text-slate-400"></i>
                        <?php echo __('profile') ?: 'Profile'; ?>
                    </a>
                    <a href="<?php echo url('/settings'); ?>" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <i class="fas fa-cog w-4 text-center text-slate-400"></i>
                        <?php echo __('settings') ?: 'Settings'; ?>
                    </a>
                    <div class="h-px bg-slate-100 dark:bg-slate-700 my-1"></div>
                    <a href="<?php echo url('/logout'); ?>" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                        <i class="fas fa-sign-out-alt w-4 text-center"></i>
                        <?php echo __('logout') ?: 'Logout'; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
