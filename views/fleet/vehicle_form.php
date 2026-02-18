<?php $vh = $vehicle; ?>
<div class="mb-6"><h1 class="text-2xl font-bold text-gray-800 dark:text-white"><?= $pageTitle ?></h1></div>
<form method="POST" action="<?= url('vehicles/store') ?>" class="space-y-6">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $vh['id'] ?>"><?php endif; ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('plate_number') ?> *</label><input type="text" name="plate_number" value="<?= e($vh['plate_number'] ?? '') ?>" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('brand') ?> *</label><input type="text" name="make" value="<?= e($vh['make'] ?? '') ?>" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('model') ?></label><input type="text" name="model" value="<?= e($vh['model'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('year') ?></label><input type="number" name="year" value="<?= $vh['year'] ?? date('Y') ?>" min="2000" max="2030" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1">Color</label><input type="text" name="color" value="<?= e($vh['color'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('capacity') ?></label><input type="number" name="capacity" value="<?= $vh['capacity'] ?? 4 ?>" min="1" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('vehicle_type') ?></label><select name="vehicle_type" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"><?php foreach(['sedan'=>__('sedan'),'suv'=>'SUV','van'=>__('van'),'minibus'=>__('midibus'),'bus'=>__('bus')] as $k=>$v): ?><option value="<?= $k ?>" <?= ($vh['vehicle_type'] ?? 'sedan') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1">Fuel Type</label><select name="fuel_type" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"><?php foreach(['gasoline'=>'Gasoline','diesel'=>'Diesel','electric'=>'Electric','hybrid'=>'Hybrid'] as $k=>$v): ?><option value="<?= $k ?>" <?= ($vh['fuel_type'] ?? 'gasoline') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('driver') ?></label><select name="driver_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"><option value="">—</option><?php foreach($drivers ?? [] as $d): ?><option value="<?= $d['id'] ?>" <?= ($vh['driver_id'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option><?php endforeach; ?></select></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1">Insurance Expiry</label><input type="date" name="insurance_expiry" value="<?= $vh['insurance_expiry'] ?? '' ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1">Registration Expiry</label><input type="date" name="registration_expiry" value="<?= $vh['registration_expiry'] ?? '' ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"></div>
            <div><label class="block text-sm font-medium text-gray-600 mb-1"><?= __('status') ?></label><select name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl"><?php foreach(['available'=>__('available'),'in_use'=>__('on_duty'),'maintenance'=>__('maintenance'),'retired'=>__('inactive')] as $k=>$v): ?><option value="<?= $k ?>" <?= ($vh['status'] ?? 'available') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-orange-500 to-amber-600 text-white rounded-xl font-semibold shadow-lg transition-all hover:-translate-y-0.5"><i class="fas fa-save mr-2"></i><?= $isEdit ? __('update') : __('save') ?></button>
        <a href="<?= url('vehicles') ?>" class="px-6 py-2.5 bg-gray-100 text-gray-600 rounded-xl font-semibold hover:bg-gray-200 transition"><?= __('cancel') ?></a>
    </div>
</form>
