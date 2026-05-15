<div class="min-h-screen bg-gradient-to-br from-purple-600 via-blue-600 to-indigo-700 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-lg w-full space-y-8">
        <!-- Campaign Card -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden transform hover:scale-105 transition-transform duration-300">
            <!-- Header -->
            <div class="bg-gradient-to-r from-purple-500 to-indigo-600 px-6 py-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/20 backdrop-blur-sm mb-4">
                    <i class="fas fa-gift text-3xl text-white"></i>
                </div>
                <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">
                    <?= htmlspecialchars($campaign['name']) ?>
                </h1>
                <?php if (!empty($campaign['description'])): ?>
                <p class="text-purple-100 text-sm md:text-base">
                    <?= nl2br(htmlspecialchars($campaign['description'])) ?>
                </p>
                <?php endif; ?>
            </div>

            <!-- Bonus Section -->
            <div class="px-6 py-8 text-center">
                <?php 
                $totalBonus = floatval($campaign['bonus_tokens']) + floatval($campaign['bonus_credits']);
                ?>
                <?php if ($totalBonus > 0): ?>
                <div class="mb-6">
                    <p class="text-gray-500 text-sm uppercase tracking-wide mb-2">Dang ky ngay de nhan</p>
                    <div class="flex items-center justify-center space-x-2">
                        <span class="text-5xl md:text-6xl font-bold bg-gradient-to-r from-yellow-400 to-orange-500 bg-clip-text text-transparent">
                            <?= number_format($totalBonus, 0) ?>
                        </span>
                        <div class="text-left">
                            <span class="block text-2xl font-semibold text-gray-700">Tokens</span>
                            <span class="text-sm text-gray-500">Hoan toan mien phi!</span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Bonus Breakdown -->
                <?php if ($campaign['bonus_tokens'] > 0 && $campaign['bonus_credits'] > 0): ?>
                <div class="flex justify-center space-x-4 mb-6">
                    <div class="bg-purple-50 rounded-lg px-4 py-2">
                        <span class="text-purple-600 font-semibold"><?= number_format($campaign['bonus_tokens'], 0) ?></span>
                        <span class="text-purple-500 text-sm ml-1">Tokens</span>
                    </div>
                    <div class="bg-blue-50 rounded-lg px-4 py-2">
                        <span class="text-blue-600 font-semibold"><?= number_format($campaign['bonus_credits'], 0) ?></span>
                        <span class="text-blue-500 text-sm ml-1">Credits</span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Countdown Timer (if expires_at set) -->
                <?php if (!empty($campaign['expires_at'])): ?>
                <div class="mb-6" id="countdown-section">
                    <p class="text-gray-500 text-sm mb-2">Ket thuc sau:</p>
                    <div class="flex justify-center space-x-3" id="countdown">
                        <div class="bg-gray-100 rounded-lg px-3 py-2 min-w-[60px]">
                            <span id="days" class="block text-2xl font-bold text-gray-700">00</span>
                            <span class="text-xs text-gray-500">Ngay</span>
                        </div>
                        <div class="bg-gray-100 rounded-lg px-3 py-2 min-w-[60px]">
                            <span id="hours" class="block text-2xl font-bold text-gray-700">00</span>
                            <span class="text-xs text-gray-500">Gio</span>
                        </div>
                        <div class="bg-gray-100 rounded-lg px-3 py-2 min-w-[60px]">
                            <span id="minutes" class="block text-2xl font-bold text-gray-700">00</span>
                            <span class="text-xs text-gray-500">Phut</span>
                        </div>
                        <div class="bg-gray-100 rounded-lg px-3 py-2 min-w-[60px]">
                            <span id="seconds" class="block text-2xl font-bold text-gray-700">00</span>
                            <span class="text-xs text-gray-500">Giay</span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Registration Stats -->
                <?php if ($campaign['max_registrations'] > 0): ?>
                <div class="mb-6">
                    <div class="flex justify-between text-sm text-gray-500 mb-1">
                        <span>Da dang ky</span>
                        <span><?= $campaign['current_registrations'] ?>/<?= $campaign['max_registrations'] ?></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <?php $progress = min(100, ($campaign['current_registrations'] / $campaign['max_registrations']) * 100); ?>
                        <div class="bg-gradient-to-r from-purple-500 to-indigo-600 h-2 rounded-full transition-all duration-500" 
                             style="width: <?= $progress ?>%"></div>
                    </div>
                    <?php if ($progress >= 80): ?>
                    <p class="text-orange-500 text-sm mt-2">
                        <i class="fas fa-fire mr-1"></i> Chi con <?= $campaign['max_registrations'] - $campaign['current_registrations'] ?> suat!
                    </p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- CTA Button -->
                <a href="/register?campaign=<?= htmlspecialchars($campaign['slug']) ?>" 
                   class="block w-full bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-bold py-4 px-6 rounded-xl hover:from-purple-600 hover:to-indigo-700 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl">
                    <i class="fas fa-user-plus mr-2"></i>
                    Dang ky ngay
                </a>

                <!-- Already have account -->
                <p class="mt-4 text-gray-500 text-sm">
                    Da co tai khoan? 
                    <a href="/login" class="text-purple-600 hover:text-purple-700 font-medium">Dang nhap</a>
                </p>
            </div>

            <!-- Features -->
            <div class="bg-gray-50 px-6 py-4 border-t">
                <div class="flex justify-center space-x-6 text-sm text-gray-500">
                    <span><i class="fas fa-check text-green-500 mr-1"></i> Mien phi</span>
                    <span><i class="fas fa-check text-green-500 mr-1"></i> Khong spam</span>
                    <span><i class="fas fa-check text-green-500 mr-1"></i> An toan</span>
                </div>
            </div>
        </div>

        <!-- Trust badges -->
        <div class="text-center">
            <p class="text-white/70 text-sm">
                <i class="fas fa-shield-alt mr-1"></i>
                Bao mat thong tin cua ban
            </p>
        </div>
    </div>
</div>

<?php if (!empty($campaign['expires_at'])): ?>
<script>
// Countdown Timer
const expiresAt = new Date("<?= $campaign['expires_at'] ?>").getTime();

function updateCountdown() {
    const now = new Date().getTime();
    const distance = expiresAt - now;

    if (distance < 0) {
        document.getElementById('countdown-section').innerHTML = '<p class="text-red-500">Chien dich da ket thuc!</p>';
        return;
    }

    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

    document.getElementById('days').textContent = String(days).padStart(2, '0');
    document.getElementById('hours').textContent = String(hours).padStart(2, '0');
    document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
    document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
}

updateCountdown();
setInterval(updateCountdown, 1000);
</script>
<?php endif; ?>
