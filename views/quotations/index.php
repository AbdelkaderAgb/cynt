<?php
/**
 * Quotations List View
 */
$statusLabels = [
    'draft'     => 'Draft',
    'sent'      => 'Sent',
    'accepted'  => 'Accepted',
    'rejected'  => 'Rejected',
    'expired'   => 'Expired',
    'converted' => 'Converted',
];
$statusColors = [
    'draft'     => 'bg-gray-100 text-gray-600',
    'sent'      => 'bg-blue-100 text-blue-700',
    'accepted'  => 'bg-emerald-100 text-emerald-700',
    'rejected'  => 'bg-red-100 text-red-700',
    'expired'   => 'bg-amber-100 text-amber-700',
    'converted' => 'bg-purple-100 text-purple-700',
];
?>

<?php if (isset($_GET['saved'])): ?>
<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-2" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)">
    <i class="fas fa-check-circle"></i> <?= __('quotation_saved') ?: 'Quotation saved successfully' ?>
</div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
<div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-2" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)">
    <i class="fas fa-trash"></i> <?= __('quotation_deleted') ?: 'Quotation deleted' ?>
</div>
<?php endif; ?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-file-alt mr-2 text-orange-500"></i><?= __('quotations') ?: 'Quotations' ?></h1>
        <p class="text-sm text-gray-500 mt-1"><?= number_format($total) ?> <?= __('records_found') ?: 'records found' ?></p>
    </div>
    <a href="<?= url('quotations/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-orange-600 to-amber-600 text-white rounded-xl font-semibold shadow-lg shadow-orange-500/25 hover:shadow-orange-500/40 transition-all hover:-translate-y-0.5">
        <i class="fas fa-plus"></i> <?= __('new_quotation') ?: 'New Quotation' ?>
    </a>
</div>

<!-- Filters -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
    <form method="GET" action="<?= url('quotations') ?>" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('search') ?: 'Search' ?></label>
            <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="<?= __('quote_client') ?: 'Quote no, client...' ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-orange-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('status') ?: 'Status' ?></label>
            <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm">
                <option value=""><?= __('all') ?: 'All' ?></option>
                <?php foreach ($statusLabels as $k => $v): ?>
                <option value="<?= $k ?>" <?= ($filters['status'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg text-sm font-semibold hover:bg-orange-700 transition"><i class="fas fa-search mr-1"></i><?= __('filter') ?: 'Filter' ?></button>
            <a href="<?= url('quotations') ?>" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-200 transition"><i class="fas fa-undo"></i></a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('quote_no') ?: 'Quote No' ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('client') ?: 'Client' ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('partner') ?: 'Partner' ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('travel_dates') ?: 'Travel Dates' ?></th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase"><?= __('pax') ?: 'Pax' ?></th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase"><?= __('total') ?: 'Total' ?></th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase"><?= __('status') ?: 'Status' ?></th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase"><?= __('actions') ?: 'Actions' ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php if (empty($quotations)): ?>
                <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400"><i class="fas fa-file-alt text-4xl mb-3 block"></i><?= __('no_quotations') ?: 'No quotations found' ?></td></tr>
                <?php else: ?>
                <?php foreach ($quotations as $q): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                    <td class="px-4 py-3 font-mono font-semibold text-orange-600"><?= e($q['quote_number']) ?></td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800 dark:text-gray-200"><?= e($q['client_name']) ?></div>
                        <?php if ($q['client_email']): ?>
                        <div class="text-xs text-gray-400"><?= e($q['client_email']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600"><?= e($q['partner_name'] ?? '—') ?></td>
                    <td class="px-4 py-3 text-xs">
                        <?php if ($q['travel_dates_from']): ?>
                        <?= date('d M', strtotime($q['travel_dates_from'])) ?> → <?= $q['travel_dates_to'] ? date('d M Y', strtotime($q['travel_dates_to'])) : '—' ?>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-orange-50 text-orange-600 text-xs font-bold"><?= (int)$q['adults'] + (int)$q['children'] + (int)$q['infants'] ?></span>
                    </td>
                    <td class="px-4 py-3 text-right font-semibold"><?= format_currency($q['total'], $q['currency']) ?></td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold <?= $statusColors[$q['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                            <?= $statusLabels[$q['status']] ?? ucfirst($q['status']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="<?= url('quotations/show') ?>?id=<?= (int)$q['id'] ?>" class="p-1.5 text-gray-400 hover:text-orange-600 transition" title="View"><i class="fas fa-eye"></i></a>
                            <a href="<?= url('quotations/edit') ?>?id=<?= (int)$q['id'] ?>" class="p-1.5 text-gray-400 hover:text-blue-600 transition" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="<?= url('quotations/pdf') ?>?id=<?= (int)$q['id'] ?>" class="p-1.5 text-gray-400 hover:text-red-600 transition" title="PDF" target="_blank"><i class="fas fa-file-pdf"></i></a>
                            <a href="<?= url('quotations/delete') ?>?id=<?= (int)$q['id'] ?>" onclick="return confirm('Delete this quotation?')" class="p-1.5 text-gray-400 hover:text-red-600 transition" title="Delete"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <p class="text-xs text-gray-500"><?= __('page') ?: 'Page' ?> <?= $page ?> / <?= $pages ?></p>
        <div class="flex gap-1">
            <?php if ($page > 1): ?>
            <a href="<?= url('quotations') ?>?page=<?= $page - 1 ?>&<?= http_build_query(array_filter($filters)) ?>" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 rounded-lg text-xs hover:bg-gray-200 transition">&laquo; Prev</a>
            <?php endif; ?>
            <?php if ($page < $pages): ?>
            <a href="<?= url('quotations') ?>?page=<?= $page + 1 ?>&<?= http_build_query(array_filter($filters)) ?>" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 rounded-lg text-xs hover:bg-gray-200 transition">Next &raquo;</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
