<?php
/**
 * Sidebar Partial — Tailwind CSS + Alpine.js
 * 
 * Uses the appShell Alpine component for collapse/mobile toggle state.
 * Variables: $activePage, $user
 */

$activePage = $activePage ?? '';
$user = $user ?? Auth::user();
?>

<aside class="fixed inset-y-0 left-0 z-40 flex flex-col bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 transition-all duration-300 no-print"
       :class="[
           sidebarCollapsed ? 'w-[72px]' : 'w-64',
           sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
       ]">

    <!-- Sidebar Header -->
    <div class="flex items-center justify-between h-16 px-4 border-b border-slate-200 dark:border-slate-700 flex-shrink-0">
        <a href="<?php echo url('/dashboard'); ?>" class="flex items-center gap-3 text-slate-900 dark:text-white font-bold text-lg overflow-hidden">
            <i class="fas fa-plane text-brand-500 text-xl flex-shrink-0"></i>
            <span class="whitespace-nowrap transition-all duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                <?php echo e(COMPANY_NAME); ?>
            </span>
        </a>
        <button @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('sidebar-collapsed', sidebarCollapsed)"
                class="hidden lg:flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-brand-500 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors flex-shrink-0">
            <i class="fas fa-chevron-left text-xs transition-transform duration-300" :class="sidebarCollapsed ? 'rotate-180' : ''"></i>
        </button>
        <button @click="sidebarOpen = false"
                class="lg:hidden flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto p-3 space-y-6">

        <!-- Main Menu -->
        <div>
            <div class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500 transition-all duration-300"
                 :class="sidebarCollapsed ? 'opacity-0 h-0 mb-0 overflow-hidden' : ''">
                <?php echo __('main_menu'); ?>
            </div>
            <a href="<?php echo url('/dashboard'); ?>"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 group
                      <?php echo $activePage === 'dashboard' 
                          ? 'bg-brand-500/10 text-brand-600 dark:text-brand-400' 
                          : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white'; ?>">
                <i class="fas fa-home w-5 text-center flex-shrink-0 <?php echo $activePage === 'dashboard' ? 'text-brand-500' : 'text-slate-400 group-hover:text-brand-500'; ?>"></i>
                <span class="whitespace-nowrap transition-all duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                    <?php echo __('dashboard'); ?>
                </span>
                <?php if ($activePage === 'dashboard'): ?>
                    <div class="absolute left-0 top-0 bottom-0 w-[3px] rounded-r-full bg-gradient-to-b from-brand-500 to-cyan-500"></div>
                <?php endif; ?>
            </a>
        </div>

        <!-- Transfers Section -->
        <div>
            <div class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500 transition-all duration-300"
                 :class="sidebarCollapsed ? 'opacity-0 h-0 mb-0 overflow-hidden' : ''">
                <?php echo __('transfers'); ?>
            </div>
            <?php
            $transferLinks = [
                ['url' => '/calendar', 'icon' => 'fa-calendar-alt', 'label' => __('transfer_calendar') ?: 'Transfer Calendar', 'page' => 'calendar'],
                ['url' => '/vouchers', 'icon' => 'fa-ticket-alt', 'label' => __('transfer_voucher'), 'page' => 'vouchers'],
                ['url' => '/transfer-invoice', 'icon' => 'fa-file-invoice', 'label' => __('transfer_invoice'), 'page' => 'transfer-invoice'],
            ];
            foreach ($transferLinks as $link): ?>
                <a href="<?php echo url($link['url']); ?>"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 relative group
                          <?php echo $activePage === $link['page']
                              ? 'bg-brand-500/10 text-brand-600 dark:text-brand-400'
                              : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white'; ?>">
                    <i class="fas <?php echo $link['icon']; ?> w-5 text-center flex-shrink-0 <?php echo $activePage === $link['page'] ? 'text-brand-500' : 'text-slate-400 group-hover:text-brand-500'; ?>"></i>
                    <span class="whitespace-nowrap transition-all duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        <?php echo e($link['label']); ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Hotels Section -->
        <div>
            <div class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500 transition-all duration-300"
                 :class="sidebarCollapsed ? 'opacity-0 h-0 mb-0 overflow-hidden' : ''">
                <?php echo __('hotels'); ?>
            </div>
            <?php
            $hotelLinks = [
                ['url' => '/hotel-voucher', 'icon' => 'fa-hotel', 'label' => __('hotel_voucher'), 'page' => 'hotel-voucher'],
                ['url' => '/hotel-invoice', 'icon' => 'fa-file-invoice-dollar', 'label' => __('hotel_invoice'), 'page' => 'hotel-invoice'],
                ['url' => '/hotel-calendar', 'icon' => 'fa-calendar-check', 'label' => __('hotel_calendar') ?: 'Hotel Calendar', 'page' => 'hotel-calendar'],
            ];
            foreach ($hotelLinks as $link): ?>
                <a href="<?php echo url($link['url']); ?>"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 relative group
                          <?php echo $activePage === $link['page']
                              ? 'bg-brand-500/10 text-brand-600 dark:text-brand-400'
                              : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white'; ?>">
                    <i class="fas <?php echo $link['icon']; ?> w-5 text-center flex-shrink-0 <?php echo $activePage === $link['page'] ? 'text-brand-500' : 'text-slate-400 group-hover:text-brand-500'; ?>"></i>
                    <span class="whitespace-nowrap transition-all duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        <?php echo e($link['label']); ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Tours Section -->
        <div>
            <div class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500 transition-all duration-300"
                 :class="sidebarCollapsed ? 'opacity-0 h-0 mb-0 overflow-hidden' : ''">
                <?php echo __('tours'); ?>
            </div>
            <?php
            $tourLinks = [
                ['url' => '/calendar', 'icon' => 'fa-calendar-alt', 'label' => __('tour_calendar'), 'page' => 'calendar'],
                ['url' => '/tour-voucher', 'icon' => 'fa-map-marked-alt', 'label' => __('tour_voucher'), 'page' => 'tour-voucher'],
                ['url' => '/tour-invoice', 'icon' => 'fa-file-invoice', 'label' => __('tour_invoice') ?: 'Tour Invoice', 'page' => 'tour-invoice'],
            ];
            foreach ($tourLinks as $link): ?>
                <a href="<?php echo url($link['url']); ?>"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 relative group
                          <?php echo $activePage === $link['page']
                              ? 'bg-brand-500/10 text-brand-600 dark:text-brand-400'
                              : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white'; ?>">
                    <i class="fas <?php echo $link['icon']; ?> w-5 text-center flex-shrink-0 <?php echo $activePage === $link['page'] ? 'text-brand-500' : 'text-slate-400 group-hover:text-brand-500'; ?>"></i>
                    <span class="whitespace-nowrap transition-all duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        <?php echo e($link['label']); ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Finance Section -->
        <div>
            <div class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500 transition-all duration-300"
                 :class="sidebarCollapsed ? 'opacity-0 h-0 mb-0 overflow-hidden' : ''">
                <?php echo __('finance'); ?>
            </div>
            <a href="<?php echo url('/receipts'); ?>"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 relative group text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white">
                <i class="fas fa-receipt w-5 text-center flex-shrink-0 text-slate-400 group-hover:text-brand-500"></i>
                <span class="whitespace-nowrap transition-all duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                    <?php echo __('receipts'); ?>
                </span>
            </a>
            <a href="<?php echo url('/invoices'); ?>"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 relative group text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white">
                <i class="fas fa-list-alt w-5 text-center flex-shrink-0 text-slate-400 group-hover:text-brand-500"></i>
                <span class="whitespace-nowrap transition-all duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                    <?php echo __('invoice_list'); ?>
                </span>
            </a>
        </div>

        <!-- Admin Section -->
        <?php if (Auth::isAdmin()): ?>
        <div>
            <div class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500 transition-all duration-300"
                 :class="sidebarCollapsed ? 'opacity-0 h-0 mb-0 overflow-hidden' : ''">
                <?php echo __('management'); ?>
            </div>
            <?php
            // Count unread partner messages and pending requests for badges
            $unreadMsgCount = 0;
            $pendingReqCount = 0;
            try {
                $unreadMsgCount = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM partner_messages WHERE sender_type = 'partner' AND is_read = 0")['c'] ?? 0);
                $pendingReqCount = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM partner_booking_requests WHERE status = 'pending'")['c'] ?? 0);
            } catch (\Exception $e) {}

            $adminLinks = [
                ['url' => '/partners', 'icon' => 'fa-handshake', 'label' => __('partners'), 'page' => 'partners'],
                ['url' => '/partner-requests', 'icon' => 'fa-calendar-check', 'label' => __('partner_requests'), 'page' => 'partner-requests', 'badge' => $pendingReqCount],
                ['url' => '/partner-messages', 'icon' => 'fa-comments', 'label' => __('partner_messages'), 'page' => 'partner-messages', 'badge' => $unreadMsgCount],
                ['url' => '/services', 'icon' => 'fa-tags', 'label' => __('services_pricing'), 'page' => 'services'],
                ['url' => '/hotels/profiles', 'icon' => 'fa-hotel', 'label' => __('hotel_profiles'), 'page' => 'hotel-profiles'],
                ['url' => '/vehicles', 'icon' => 'fa-car', 'label' => __('vehicles'), 'page' => 'vehicles'],
                ['url' => '/drivers', 'icon' => 'fa-id-card', 'label' => __('drivers'), 'page' => 'drivers'],
                ['url' => '/guides', 'icon' => 'fa-user-tie', 'label' => __('tour_guides'), 'page' => 'guides'],
                ['url' => '/users', 'icon' => 'fa-users', 'label' => __('users'), 'page' => 'users'],
                ['url' => '/reports', 'icon' => 'fa-chart-bar', 'label' => __('reports'), 'page' => 'reports'],
                ['url' => '/settings', 'icon' => 'fa-cog', 'label' => __('settings'), 'page' => 'settings'],
            ];
            foreach ($adminLinks as $link): ?>
                <a href="<?php echo url($link['url']); ?>"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 relative group
                          <?php echo $activePage === $link['page']
                              ? 'bg-brand-500/10 text-brand-600 dark:text-brand-400'
                              : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white'; ?>">
                    <i class="fas <?php echo $link['icon']; ?> w-5 text-center flex-shrink-0 <?php echo $activePage === $link['page'] ? 'text-brand-500' : 'text-slate-400 group-hover:text-brand-500'; ?>"></i>
                    <span class="whitespace-nowrap transition-all duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        <?php echo e($link['label']); ?>
                    </span>
                    <?php if (!empty($link['badge']) && $link['badge'] > 0): ?>
                    <span class="ml-auto px-1.5 py-0.5 text-[10px] font-bold bg-red-500 text-white rounded-full min-w-[18px] text-center" :class="sidebarCollapsed ? 'absolute -top-1 -right-1 scale-75' : ''">
                        <?= $link['badge'] ?>
                    </span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </nav>

    <!-- Sidebar Footer: User Mini Info -->
    <div class="border-t border-slate-200 dark:border-slate-700 p-3 flex-shrink-0">
        <div class="flex items-center gap-3 px-2 py-2 rounded-lg">
            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-500 to-cyan-500 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                <?php echo strtoupper(substr($user['full_name'] ?? $user['email'] ?? 'U', 0, 1)); ?>
            </div>
            <div class="flex-1 min-w-0 transition-all duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                <div class="text-sm font-medium text-slate-900 dark:text-white truncate">
                    <?php echo e($user['full_name'] ?? $user['email'] ?? 'User'); ?>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 truncate">
                    <?php echo e(ucfirst($user['role'] ?? 'user')); ?>
                </div>
            </div>
        </div>
    </div>
</aside>
