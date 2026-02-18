<?php
/**
 * Group Files List View
 */
$statusLabels = [
    'planning'    => 'Planning',
    'confirmed'   => 'Confirmed',
    'in_progress' => 'In Progress',
    'completed'   => 'Completed',
    'cancelled'   => 'Cancelled',
];
$statusColors = [
    'planning'    => 'bg-gray-100 text-gray-600',
    'confirmed'   => 'bg-blue-100 text-blue-700',
    'in_progress' => 'bg-cyan-100 text-cyan-700',
    'completed'   => 'bg-emerald-100 text-emerald-700',
    'cancelled'   => 'bg-red-100 text-red-700',
];
?>

<?php if (isset($_GET['saved'])): ?>
<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-2" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)">
    <i class="fas fa-check-circle"></i> <?= __('group_file_saved') ?: 'Group file saved successfully' ?>
</div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
<div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-2" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)">
    <i class="fas fa-trash"></i> <?= __('group_file_deleted') ?: 'Group file deleted' ?>
</div>
<?php endif; ?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white"><i class="fas fa-folder-open mr-2 text-violet-500"></i><?= __('group_files') ?: 'Group Files' ?></h1>
        <p class="text-sm text-gray-500 mt-1"><?= number_format($total) ?> <?= __('records_found') ?: 'records found' ?></p>
    </div>
    <a href="<?= url('group-files/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-xl font-semibold shadow-lg shadow-violet-500/25 hover:shadow-violet-500/40 transition-all hover:-translate-y-0.5">
        <i class="fas fa-plus"></i> <?= __('new_group_file') ?: 'New Group File' ?>
    </a>
</div>

<!-- Filters -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
    <form method="GET" action="<?= url('group-files') ?>" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1"><?= __('search') ?: 'Search' ?></label>
            <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="<?= __('file_group_leader') ?: 'File no, group, leader...' ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-violet-500">
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
            <button type="submit" class="px-4 py-2 bg-violet-600 text-white rounded-lg text-sm font-semibold hover:bg-violet-700 transition"><i class="fas fa-search mr-1"></i><?= __('filter') ?: 'Filter' ?></button>
            <a href="<?= url('group-files') ?>" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-200 transition"><i class="fas fa-undo"></i></a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('file_no') ?: 'File No' ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('group_name') ?: 'Group Name' ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('partner') ?: 'Partner' ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('dates') ?: 'Dates' ?></th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase"><?= __('pax') ?: 'Pax' ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase"><?= __('leader') ?: 'Leader' ?></th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase"><?= __('status') ?: 'Status' ?></th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase"><?= __('actions') ?: 'Actions' ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php if (empty($groups)): ?>
                <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400"><i class="fas fa-folder-open text-4xl mb-3 block"></i><?= __('no_group_files') ?: 'No group files found' ?></td></tr>
                <?php else: ?>
                <?php foreach ($groups as $g): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                    <td class="px-4 py-3 font-mono font-semibold text-violet-600"><?= e($g['file_number']) ?></td>
                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200"><?= e($g['group_name']) ?></td>
                    <td class="px-4 py-3 text-xs text-gray-600"><?= e($g['partner_name'] ?? '—') ?></td>
                    <td class="px-4 py-3 text-xs">
                        <?php if ($g['arrival_date']): ?>
                        <?= date('d M', strtotime($g['arrival_date'])) ?> → <?= $g['departure_date'] ? date('d M Y', strtotime($g['departure_date'])) : '—' ?>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-violet-50 text-violet-600 text-xs font-bold"><?= (int)$g['total_pax'] ?></span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600"><?= e($g['leader_name'] ?: '—') ?></td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold <?= $statusColors[$g['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                            <?= $statusLabels[$g['status']] ?? ucfirst($g['status']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="<?= url('group-files/show') ?>?id=<?= (int)$g['id'] ?>" class="p-1.5 text-gray-400 hover:text-violet-600 transition" title="View"><i class="fas fa-eye"></i></a>
                            <a href="<?= url('group-files/edit') ?>?id=<?= (int)$g['id'] ?>" class="p-1.5 text-gray-400 hover:text-blue-600 transition" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="<?= url('group-files/pdf') ?>?id=<?= (int)$g['id'] ?>" class="p-1.5 text-gray-400 hover:text-red-600 transition" title="PDF" target="_blank"><i class="fas fa-file-pdf"></i></a>
                            <a href="<?= url('group-files/delete') ?>?id=<?= (int)$g['id'] ?>" onclick="return confirm('Delete this group file?')" class="p-1.5 text-gray-400 hover:text-red-600 transition" title="Delete"><i class="fas fa-trash"></i></a>
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
            <a href="<?= url('group-files') ?>?page=<?= $page - 1 ?>&<?= http_build_query(array_filter($filters)) ?>" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 rounded-lg text-xs hover:bg-gray-200 transition">&laquo; Prev</a>
            <?php endif; ?>
            <?php if ($page < $pages): ?>
            <a href="<?= url('group-files') ?>?page=<?= $page + 1 ?>&<?= http_build_query(array_filter($filters)) ?>" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 rounded-lg text-xs hover:bg-gray-200 transition">Next &raquo;</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
