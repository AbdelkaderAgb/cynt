<?php $u = $user; ?>
<?php if (isset($_GET['saved'])): ?><div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl" x-data="{s:true}" x-show="s" x-init="setTimeout(()=>s=false,3000)"><i class="fas fa-check-circle mr-1"></i>Profil güncellendi</div><?php endif; ?>

<div class="max-w-2xl mx-auto">
    <div class="mb-6"><h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-user-circle text-blue-500 mr-2"></i>Profilim</h1></div>

    <form method="POST" action="<?= url('profile/update') ?>" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-4 mb-6 pb-6 border-b">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center text-white text-2xl font-bold"><?= strtoupper(mb_substr($u['first_name'],0,1) . mb_substr($u['last_name'],0,1)) ?></div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white"><?= e($u['first_name'] . ' ' . $u['last_name']) ?></h2>
                    <p class="text-sm text-gray-500"><?= e($u['email']) ?> · <span class="capitalize"><?= $u['role'] ?></span></p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-600 mb-1">Ad</label><input type="text" name="first_name" value="<?= e($u['first_name']) ?>" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium text-gray-600 mb-1">Soyad</label><input type="text" name="last_name" value="<?= e($u['last_name']) ?>" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
                <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-600 mb-1">Telefon</label><input type="text" name="phone" value="<?= e($u['phone'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4"><i class="fas fa-lock text-amber-500 mr-2"></i>Şifre Değiştir</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-600 mb-1">Yeni Şifre</label><input type="password" name="password" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
                <div><label class="block text-sm font-medium text-gray-600 mb-1">Şifre Tekrar</label><input type="password" name="password_confirm" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            </div>
        </div>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5"><i class="fas fa-save mr-2"></i>Güncelle</button>
    </form>
</div>
