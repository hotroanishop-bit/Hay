/**
 * Theme Switcher Module
 * Handles light/dark theme switching with persistence
 */
const ThemeSwitcher = (function() {
    'use strict';

    const STORAGE_KEY = 'preferred-theme';
    const DEFAULT_THEME = 'light';

    /**
     * Initialize the theme switcher
     * Load saved theme or use default, then setup toggle buttons
     */
    function init() {
        const savedTheme = localStorage.getItem(STORAGE_KEY) || DEFAULT_THEME;
        applyTheme(savedTheme);
        setupToggleButtons();
    }

    /**
     * Apply a theme to the document
     * @param {string} theme - 'light' or 'dark'
     */
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem(STORAGE_KEY, theme);
        updateToggleButtonStates(theme);
    }

    /**
     * Update aria-pressed states on toggle buttons
     * @param {string} activeTheme - Current active theme
     */
    function updateToggleButtonStates(activeTheme) {
        document.querySelectorAll('[data-theme-toggle]').forEach(function(btn) {
            var btnTheme = btn.dataset.themeToggle || btn.dataset.theme;
            btn.setAttribute('aria-pressed', btnTheme === activeTheme ? 'true' : 'false');
            
            // Update icons if present
            var lightIcon = btn.querySelector('.theme-icon-light');
            var darkIcon = btn.querySelector('.theme-icon-dark');
            
            if (lightIcon && darkIcon) {
                lightIcon.style.display = activeTheme === 'light' ? 'none' : '';
                darkIcon.style.display = activeTheme === 'dark' ? 'none' : '';
            }
        });
    }

    /**
     * Toggle between light and dark themes
     */
    function toggleTheme() {
        var current = document.documentElement.getAttribute('data-theme') || DEFAULT_THEME;
        var newTheme = current === 'light' ? 'dark' : 'light';
        applyTheme(newTheme);
        syncWithServer(newTheme);
    }

    /**
     * Set a specific theme
     * @param {string} theme - 'light' or 'dark'
     */
    function setTheme(theme) {
        if (theme !== 'light' && theme !== 'dark') {
            console.warn('Invalid theme:', theme);
            return;
        }
        applyTheme(theme);
        syncWithServer(theme);
    }

    /**
     * Get the current theme
     * @returns {string} Current theme name
     */
    function getTheme() {
        return document.documentElement.getAttribute('data-theme') || DEFAULT_THEME;
    }

    /**
     * Sync theme preference with server (fire and forget)
     * @param {string} theme - Theme to save
     */
    function syncWithServer(theme) {
        fetch('/theme/set', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ theme: theme }),
            credentials: 'same-origin'
        }).catch(function() {
            // Silently fail - localStorage is the primary storage
        });
    }

    /**
     * Setup click handlers for theme toggle buttons
     */
    function setupToggleButtons() {
        document.querySelectorAll('[data-theme-toggle]').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                toggleTheme();
            });
        });
    }

    // Public API
    return {
        init: init,
        applyTheme: applyTheme,
        toggleTheme: toggleTheme,
        setTheme: setTheme,
        getTheme: getTheme
    };
})();
