<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center mb-6">
        <a href="/admin/campaigns" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Danh sach dang ky</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Chien dich: <?= htmlspecialchars($campaign['name']) ?></p>
        </div>
    </div>

    <!-- Campaign Info -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">URL</p>
                <code class="text-sm bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">/c/<?= htmlspecialchars($campaign['slug']) ?></code>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Bonus</p>
                <p class="text-lg font-semibold text-yellow-600"><?= number_format($campaign['bonus_tokens'] + $campaign['bonus_credits'], 0) ?> tokens</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Tong dang ky</p>
                <p class="text-lg font-semibold text-gray-800 dark:text-white"><?= $campaign['current_registrations'] ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Trang thai</p>
                <?php if ($campaign['is_active']): ?>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Hoat dong</span>
                <?php else: ?>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Tam dung</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Registrations Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <?php if (empty($registrations)): ?>
        <div class="p-8 text-center">
            <i class="fas fa-users text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
            <p class="text-gray-500 dark:text-gray-400">Chua co ai dang ky qua chien dich nay</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nguoi dung</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Bonus nhan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">IP</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Thoi gian</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($registrations as $i => $reg): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $i + 1 ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center">
                                    <span class="text-purple-600 font-medium"><?= strtoupper(substr($reg['user_name'] ?? 'U', 0, 1)) ?></span>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($reg['user_name'] ?? 'Unknown') ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <?= htmlspecialchars($reg['user_email'] ?? '') ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-yellow-600"><?= number_format($reg['bonus_received'], 0) ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= htmlspecialchars($reg['ip_address'] ?? 'N/A') ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= date('d/m/Y H:i', strtotime($reg['registered_at'])) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
