/**
 * Maintenance Page JavaScript
 * Handles countdown timer functionality
 */

(function() {
    'use strict';

    const countdownEl = document.getElementById('countdown');
    if (!countdownEl) return;

    const endTimeStr = countdownEl.dataset.endTime;
    if (!endTimeStr) return;

    const endTime = new Date(endTimeStr).getTime();
    
    const hoursEl = document.getElementById('hours');
    const minutesEl = document.getElementById('minutes');
    const secondsEl = document.getElementById('seconds');

    function padZero(num) {
        return num.toString().padStart(2, '0');
    }

    function updateCountdown() {
        const now = new Date().getTime();
        const distance = endTime - now;

        if (distance <= 0) {
            // Maintenance should be over, reload the page
            hoursEl.textContent = '00';
            minutesEl.textContent = '00';
            secondsEl.textContent = '00';
            
            // Reload after a short delay
            setTimeout(function() {
                window.location.reload();
            }, 2000);
            return;
        }

        // Calculate time units
        const hours = Math.floor(distance / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Update display
        hoursEl.textContent = padZero(hours);
        minutesEl.textContent = padZero(minutes);
        secondsEl.textContent = padZero(seconds);
    }

    // Initial update
    updateCountdown();

    // Update every second
    setInterval(updateCountdown, 1000);
})();
