<?php
/**
 * CYN Tourism — Tax Rates Settings
 * Manage tax rates by country and service type.
 */
$rates = $rates ?? [];
?>

<?php if (!empty($_GET['saved'])): ?>
<script>document.addEventListener('DOMContentLoaded', () => window.showToast?.('<?= __('saved_successfully') ?>', 'success'));</script>
<?php elseif (!empty($_GET['deleted'])): ?>
<script>document.addEventListener('DOMContentLoaded', () => window.showToast?.('<?= __('deleted_successfully') ?>', 'success'));</script>
<?php endif; ?>

<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white"><?= __('tax') ?> Rates</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage tax rates applied to invoices</p>
    </div>
    <button onclick="openTaxModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-amber-600 to-orange-600 text-white rounded-xl hover:shadow-lg transition font-medium">
        <i class="fas fa-plus"></i> Add Tax Rate
    </button>
</div>

<!-- Tax Rates Table -->
<?php if (empty($rates)): ?>
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center">
    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
        <i class="fas fa-percentage text-2xl text-amber-500"></i>
    </div>
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Tax Rates</h3>
    <p class="text-gray-500 dark:text-gray-400 mb-6">Configure tax rates to auto-apply on invoices.</p>
</div>
<?php else: ?>
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-700/50">
                    <th class="px-5 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Name</th>
                    <th class="px-5 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Rate %</th>
                    <th class="px-5 py-3 text-left font-medium text-gray-600 dark:text-gray-300"><?= __('country') ?></th>
                    <th class="px-5 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Applies To</th>
                    <th class="px-5 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Default</th>
                    <th class="px-5 py-3 text-center font-medium text-gray-600 dark:text-gray-300"><?= __('status') ?></th>
                    <th class="px-5 py-3 text-center font-medium text-gray-600 dark:text-gray-300"><?= __('actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rates as $r): ?>
                <tr class="border-t border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                    <td class="px-5 py-3 font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($r['name']) ?></td>
                    <td class="px-5 py-3 text-right font-semibold text-gray-900 dark:text-white"><?= number_format($r['rate'], 2) ?>%</td>
                    <td class="px-5 py-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($r['country'] ?: '—') ?></td>
                    <td class="px-5 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400"><?= ucfirst($r['applies_to']) ?></span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <?php if ($r['is_default']): ?>
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">✓ Default</span>
                        <?php else: ?>
                        <span class="text-gray-400">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $r['status'] === 'active' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400' ?>"><?= ucfirst($r['status']) ?></span>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick='openTaxModal(<?= json_encode($r) ?>)' class="p-1.5 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition"><i class="fas fa-edit"></i></button>
                            <a href="<?= url('settings/tax-rates/delete') ?>?id=<?= $r['id'] ?>" onclick="return confirm('<?= __('confirm_delete') ?>')" class="p-1.5 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Tax Modal -->
<div id="taxModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg mx-4">
        <form action="<?= url('settings/tax-rates/store') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="tax_id" value="">

            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <h2 id="tax_title" class="text-xl font-bold text-gray-900 dark:text-white">Add Tax Rate</h2>
            </div>

            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label>
                    <input type="text" name="name" id="tax_name" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500" placeholder="e.g. KDV, VAT">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rate (%) *</label>
                        <input type="number" name="rate" id="tax_rate" step="0.01" min="0" max="100" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= __('country') ?></label>
                        <input type="text" name="country" id="tax_country" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500" placeholder="Turkey">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Applies To</label>
                        <select name="applies_to" id="tax_applies" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                            <option value="all"><?= __('all') ?></option>
                            <option value="hotel"><?= __('hotel') ?></option>
                            <option value="tour">Tour</option>
                            <option value="transfer">Transfer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?= __('status') ?></label>
                        <select name="status" id="tax_status" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                            <option value="active"><?= __('active') ?></option>
                            <option value="inactive"><?= __('inactive') ?></option>
                        </select>
                    </div>
                </div>
                <label class="inline-flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_default" id="tax_default" class="w-5 h-5 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Set as Default</span>
                </label>
            </div>

            <div class="p-6 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                <button type="button" onclick="closeTaxModal()" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition font-medium"><?= __('cancel') ?></button>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-amber-600 to-orange-600 text-white rounded-xl hover:shadow-lg transition font-medium">
                    <i class="fas fa-save mr-1"></i> <?= __('save') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openTaxModal(data = null) {
    const m = document.getElementById('taxModal');
    m.classList.remove('hidden');
    m.classList.add('flex');
    if (data) {
        document.getElementById('tax_title').textContent = '<?= __('edit') ?> Tax Rate';
        document.getElementById('tax_id').value = data.id;
        document.getElementById('tax_name').value = data.name;
        document.getElementById('tax_rate').value = data.rate;
        document.getElementById('tax_country').value = data.country || '';
        document.getElementById('tax_applies').value = data.applies_to;
        document.getElementById('tax_status').value = data.status;
        document.getElementById('tax_default').checked = !!parseInt(data.is_default);
    } else {
        document.getElementById('tax_title').textContent = 'Add Tax Rate';
        document.getElementById('tax_id').value = '';
        document.getElementById('tax_name').value = '';
        document.getElementById('tax_rate').value = '';
        document.getElementById('tax_country').value = '';
        document.getElementById('tax_applies').value = 'all';
        document.getElementById('tax_status').value = 'active';
        document.getElementById('tax_default').checked = false;
    }
}
function closeTaxModal() {
    const m = document.getElementById('taxModal');
    m.classList.add('hidden');
    m.classList.remove('flex');
}
document.getElementById('taxModal')?.addEventListener('click', function(e) { if (e.target === this) closeTaxModal(); });
</script>
