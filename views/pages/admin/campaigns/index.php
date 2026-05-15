<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Quan ly Chien dich</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Tao va quan ly cac chien dich dang ky</p>
        </div>
        <a href="/admin/campaigns/create" 
           class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-medium rounded-lg hover:from-purple-600 hover:to-indigo-700 transition-all duration-300">
            <i class="fas fa-plus mr-2"></i>
            Tao chien dich moi
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                    <i class="fas fa-bullhorn text-purple-600 dark:text-purple-400"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Tong chien dich</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white"><?= $stats['total_campaigns'] ?? 0 ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Dang hoat dong</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white"><?= $stats['active_campaigns'] ?? 0 ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 dark:text-blue-400"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Tong dang ky</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white"><?= $stats['total_registrations'] ?? 0 ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-full bg-yellow-100 dark:bg-yellow-900 flex items-center justify-center">
                    <i class="fas fa-coins text-yellow-600 dark:text-yellow-400"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Tong tokens tang</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white"><?= number_format(($stats['total_tokens_given'] ?? 0) + ($stats['total_credits_given'] ?? 0), 0) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaigns Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <?php if (empty($campaigns)): ?>
        <div class="p-8 text-center">
            <i class="fas fa-bullhorn text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
            <p class="text-gray-500 dark:text-gray-400">Chua co chien dich nao</p>
            <a href="/admin/campaigns/create" class="mt-4 inline-block text-purple-600 hover:text-purple-700">
                <i class="fas fa-plus mr-1"></i> Tao chien dich dau tien
            </a>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Chien dich</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">URL</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Bonus</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Dang ky</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Trang thai</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Hanh dong</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($campaigns as $campaign): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                                    <i class="fas fa-bullhorn text-purple-600 dark:text-purple-400"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        <?= htmlspecialchars($campaign['name']) ?>
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        <?= date('d/m/Y H:i', strtotime($campaign['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <code class="text-sm bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                    /c/<?= htmlspecialchars($campaign['slug']) ?>
                                </code>
                                <button onclick="copyUrl('<?= htmlspecialchars($campaign['slug']) ?>')" 
                                        class="text-gray-400 hover:text-purple-600 transition-colors"
                                        title="Copy URL">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php $totalBonus = floatval($campaign['bonus_tokens']) + floatval($campaign['bonus_credits']); ?>
                            <span class="text-sm font-medium text-yellow-600 dark:text-yellow-400">
                                <?= number_format($totalBonus, 0) ?> tokens
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                <?= $campaign['current_registrations'] ?>
                                <?php if ($campaign['max_registrations'] > 0): ?>
                                    / <?= $campaign['max_registrations'] ?>
                                <?php endif; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($campaign['is_active']): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Hoat dong
                                </span>
                            <?php else: ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    Tam dung
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <a href="/admin/campaigns/<?= $campaign['id'] ?>/registrations" 
                               class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                               title="Xem dang ky">
                                <i class="fas fa-users"></i>
                            </a>
                            <a href="/admin/campaigns/<?= $campaign['id'] ?>/edit" 
                               class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                               title="Chinh sua">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="/admin/campaigns/<?= $campaign['id'] ?>/toggle" method="POST" class="inline">
                                <button type="submit" 
                                        class="<?= $campaign['is_active'] ? 'text-orange-600 hover:text-orange-900 dark:text-orange-400' : 'text-green-600 hover:text-green-900 dark:text-green-400' ?>"
                                        title="<?= $campaign['is_active'] ? 'Tat' : 'Bat' ?>">
                                    <i class="fas fa-<?= $campaign['is_active'] ? 'pause' : 'play' ?>"></i>
                                </button>
                            </form>
                            <?php if ($campaign['current_registrations'] == 0): ?>
                            <form action="/admin/campaigns/<?= $campaign['id'] ?>" method="POST" class="inline" onsubmit="return confirm('Ban co chac chan muon xoa chien dich nay?');">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" 
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                        title="Xoa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function copyUrl(slug) {
    const url = window.location.origin + '/c/' + slug;
    navigator.clipboard.writeText(url).then(function() {
        // Show toast or notification
        alert('Da copy URL: ' + url);
    }).catch(function(err) {
        console.error('Failed to copy: ', err);
    });
}
</script>
