<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center mb-6">
        <a href="/admin/campaigns" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Chinh sua Chien dich</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1"><?= htmlspecialchars($campaign['name']) ?></p>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <form action="/admin/campaigns/<?= $campaign['id'] ?>" method="POST" class="space-y-6">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Ten chien dich <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" required
                       value="<?= htmlspecialchars($campaign['name']) ?>"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Slug -->
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">URL Slug</label>
                <div class="flex items-center">
                    <span class="bg-gray-100 dark:bg-gray-600 px-4 py-2 border border-r-0 border-gray-300 dark:border-gray-600 rounded-l-lg text-gray-500">/c/</span>
                    <input type="text" id="slug" name="slug"
                           value="<?= htmlspecialchars($campaign['slug']) ?>"
                           class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-r-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white">
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mo ta</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white"><?= htmlspecialchars($campaign['description'] ?? '') ?></textarea>
            </div>

            <!-- Bonus -->
            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-purple-800 dark:text-purple-300 mb-4"><i class="fas fa-gift mr-2"></i>Phan thuong</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="bonus_tokens" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Bonus Tokens</label>
                        <input type="number" id="bonus_tokens" name="bonus_tokens" value="<?= $campaign['bonus_tokens'] ?>" min="0" step="0.01"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label for="bonus_credits" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Bonus Credits</label>
                        <input type="number" id="bonus_credits" name="bonus_credits" value="<?= $campaign['bonus_credits'] ?>" min="0" step="0.01"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
            </div>

            <!-- Limits -->
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-300 mb-4"><i class="fas fa-cog mr-2"></i>Gioi han</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="max_registrations" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">So luot dang ky toi da</label>
                        <input type="number" id="max_registrations" name="max_registrations" value="<?= $campaign['max_registrations'] ?>" min="0"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label for="starts_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ngay bat dau</label>
                        <input type="datetime-local" id="starts_at" name="starts_at"
                               value="<?= !empty($campaign['starts_at']) ? date('Y-m-d\TH:i', strtotime($campaign['starts_at'])) : '' ?>"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label for="expires_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ngay ket thuc</label>
                        <input type="datetime-local" id="expires_at" name="expires_at"
                               value="<?= !empty($campaign['expires_at']) ? date('Y-m-d\TH:i', strtotime($campaign['expires_at'])) : '' ?>"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end space-x-4">
                <a href="/admin/campaigns" class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Huy</a>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-medium rounded-lg hover:from-purple-600 hover:to-indigo-700">
                    <i class="fas fa-save mr-2"></i>Luu thay doi
                </button>
            </div>
        </form>
    </div>
</div>
