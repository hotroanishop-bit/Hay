<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center mb-6">
        <a href="/admin/campaigns" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 mr-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Tao Chien dich moi</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Tao URL dang ky voi phan thuong cho nguoi dung moi</p>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <form action="/admin/campaigns" method="POST" class="space-y-6">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Ten chien dich <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       required
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                       placeholder="VD: Khuyen mai Tet 2025">
            </div>

            <!-- Slug -->
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    URL Slug
                    <span class="text-gray-400 font-normal">(de trong de tu dong tao)</span>
                </label>
                <div class="flex items-center">
                    <span class="bg-gray-100 dark:bg-gray-600 px-4 py-2 border border-r-0 border-gray-300 dark:border-gray-600 rounded-l-lg text-gray-500 dark:text-gray-400">
                        /c/
                    </span>
                    <input type="text" 
                           id="slug" 
                           name="slug" 
                           class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-r-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                           placeholder="tet-2025">
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Chi cho phep chu thuong, so va dau gach ngang</p>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mo ta
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="3"
                          class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                          placeholder="Mo ta chien dich hien thi tren trang dang ky..."></textarea>
            </div>

            <!-- Bonus Section -->
            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-purple-800 dark:text-purple-300 mb-4">
                    <i class="fas fa-gift mr-2"></i>Phan thuong dang ky
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="bonus_tokens" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Bonus Tokens
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   id="bonus_tokens" 
                                   name="bonus_tokens" 
                                   value="0"
                                   min="0"
                                   step="0.01"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                            <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">tokens</span>
                        </div>
                    </div>
                    <div>
                        <label for="bonus_credits" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Bonus Credits
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   id="bonus_credits" 
                                   name="bonus_credits" 
                                   value="0"
                                   min="0"
                                   step="0.01"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                            <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">credits</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Limits Section -->
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-300 mb-4">
                    <i class="fas fa-cog mr-2"></i>Gioi han
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="max_registrations" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            So luot dang ky toi da
                        </label>
                        <input type="number" 
                               id="max_registrations" 
                               name="max_registrations" 
                               value="0"
                               min="0"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">0 = khong gioi han</p>
                    </div>
                    <div>
                        <label for="starts_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Ngay bat dau
                        </label>
                        <input type="datetime-local" 
                               id="starts_at" 
                               name="starts_at" 
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">De trong = bat dau ngay</p>
                    </div>
                    <div>
                        <label for="expires_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Ngay ket thuc
                        </label>
                        <input type="datetime-local" 
                               id="expires_at" 
                               name="expires_at" 
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">De trong = khong het han</p>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end space-x-4">
                <a href="/admin/campaigns" 
                   class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Huy
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-medium rounded-lg hover:from-purple-600 hover:to-indigo-700 transition-all duration-300">
                    <i class="fas fa-plus mr-2"></i>Tao chien dich
                </button>
            </div>
        </form>
    </div>
</div>
