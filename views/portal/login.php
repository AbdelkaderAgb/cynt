<?php
/**
 * Partner Portal — Login Page (Standalone, no layout)
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner Login - CYN Tourism</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-950 to-indigo-900 flex items-center justify-center p-4">

    <!-- Floating decorative shapes -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/3 left-1/4 w-64 h-64 bg-cyan-500/5 rounded-full blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-md">
        <!-- Logo / Branding -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-2xl shadow-lg shadow-blue-600/30 mb-4">
                <i class="fas fa-plane text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-white">CYN TURIZM</h1>
            <p class="text-blue-300/80 text-sm mt-1">Partner Portal</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white/10 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-2xl">
            <h2 class="text-xl font-bold text-white mb-1">Welcome Back</h2>
            <p class="text-sm text-blue-200/60 mb-6">Sign in to access your partner dashboard</p>

            <?php if (!empty($error)): ?>
                <div class="bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-xl text-sm mb-5 flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= url('portal/login') ?>" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-blue-200/80 mb-1.5">Email Address</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-blue-300/40"><i class="fas fa-envelope text-sm"></i></span>
                        <input type="email" name="email" required autocomplete="email"
                               class="w-full pl-10 pr-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-blue-200/30 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                               placeholder="partner@company.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-blue-200/80 mb-1.5">Password</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-blue-300/40"><i class="fas fa-lock text-sm"></i></span>
                        <input type="password" name="password" required autocomplete="current-password"
                               class="w-full pl-10 pr-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-blue-200/30 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                               placeholder="••••••••">
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-blue-600/30 hover:shadow-blue-600/50 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
            </form>
        </div>

        <!-- Footer -->
        <p class="text-center text-blue-300/40 text-xs mt-8">
            &copy; <?= date('Y') ?> CYN TURIZM. All rights reserved.<br>
            <a href="<?= url('login') ?>" class="text-blue-400/60 hover:text-blue-300 transition">Admin Login →</a>
        </p>
    </div>

</body>
</html>
