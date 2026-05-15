<div class="min-h-screen bg-gradient-to-br from-gray-700 via-gray-800 to-gray-900 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full text-center">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden p-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-100 mb-6">
                <i class="fas fa-clock text-4xl text-gray-400"></i>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Chien dich da ket thuc</h1>
            
            <?php if (!empty($campaign)): ?>
            <p class="text-gray-600 mb-6">
                Chien dich "<?= htmlspecialchars($campaign['name']) ?>" da ket thuc hoac tam dung.
            </p>
            <?php else: ?>
            <p class="text-gray-600 mb-6">
                Chien dich nay da ket thuc hoac khong con hoat dong.
            </p>
            <?php endif; ?>

            <div class="space-y-3">
                <a href="/register" 
                   class="block w-full bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-medium py-3 px-6 rounded-lg hover:from-purple-600 hover:to-indigo-700 transition-all duration-300">
                    <i class="fas fa-user-plus mr-2"></i>
                    Dang ky tai khoan
                </a>
                
                <a href="/" 
                   class="block w-full bg-gray-100 text-gray-700 font-medium py-3 px-6 rounded-lg hover:bg-gray-200 transition-all duration-300">
                    <i class="fas fa-home mr-2"></i>
                    Trang chu
                </a>
            </div>
        </div>
    </div>
</div>
