<?php
/**
 * Language Switcher Component
 * Dropdown for switching between Vietnamese and English
 */
$currentLang = TranslationService::getInstance()->getCurrentLanguage();
$languages = [
    'vi' => ['name' => 'Tieng Viet', 'flag' => 'VI'],
    'en' => ['name' => 'English', 'flag' => 'EN'],
];
?>

<div class="language-switcher">
    <button type="button" class="language-toggle" id="languageToggle" aria-label="<?php echo __('language.select', 'Select Language'); ?>" aria-expanded="false">
        <span class="lang-flag"><?php echo $languages[$currentLang]['flag']; ?></span>
        <span class="lang-name hide-mobile"><?php echo $languages[$currentLang]['name']; ?></span>
        <svg class="dropdown-arrow" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
    </button>
    <div class="language-dropdown" id="languageDropdown" aria-hidden="true">
        <?php foreach ($languages as $code => $lang): ?>
        <a href="?lang=<?php echo $code; ?>" class="language-option <?php echo $code === $currentLang ? 'active' : ''; ?>">
            <span class="lang-flag"><?php echo $lang['flag']; ?></span>
            <span class="lang-name"><?php echo $lang['name']; ?></span>
            <?php if ($code === $currentLang): ?>
            <svg class="check-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<style>
/* Language Switcher Styles */
.language-switcher {
    position: relative;
    display: inline-flex;
}

.language-toggle {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-3);
    background: var(--surface-secondary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    color: var(--text-primary);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.language-toggle:hover {
    background: var(--surface-tertiary);
    border-color: var(--border-hover);
}

.lang-flag {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 18px;
    background: var(--color-primary);
    color: var(--color-white);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-bold);
    border-radius: var(--radius-xs);
}

.lang-name {
    white-space: nowrap;
}

.dropdown-arrow {
    transition: transform var(--transition-fast);
}

.language-toggle[aria-expanded="true"] .dropdown-arrow {
    transform: rotate(180deg);
}

.language-dropdown {
    position: absolute;
    top: calc(100% + var(--space-1));
    right: 0;
    min-width: 160px;
    background: var(--surface-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-lg);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-8px);
    transition: all var(--transition-fast);
    z-index: 100;
}

.language-dropdown.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.language-option {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-3);
    color: var(--text-primary);
    text-decoration: none;
    transition: background-color var(--transition-fast);
}

.language-option:first-child {
    border-radius: var(--radius-md) var(--radius-md) 0 0;
}

.language-option:last-child {
    border-radius: 0 0 var(--radius-md) var(--radius-md);
}

.language-option:hover {
    background: var(--surface-secondary);
}

.language-option.active {
    background: var(--color-primary-bg);
    color: var(--color-primary);
}

.language-option .check-icon {
    margin-left: auto;
    color: var(--color-primary);
}

/* Mobile adjustments */
@media (max-width: 768px) {
    .hide-mobile {
        display: none;
    }
    
    .language-toggle {
        padding: var(--space-2);
    }
}
</style>

<script>
(function() {
    const toggle = document.getElementById('languageToggle');
    const dropdown = document.getElementById('languageDropdown');
    
    if (toggle && dropdown) {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const isExpanded = dropdown.classList.toggle('show');
            toggle.setAttribute('aria-expanded', isExpanded);
            dropdown.setAttribute('aria-hidden', !isExpanded);
        });
        
        // Close when clicking outside
        document.addEventListener('click', function(e) {
            if (!toggle.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('show');
                toggle.setAttribute('aria-expanded', 'false');
                dropdown.setAttribute('aria-hidden', 'true');
            }
        });
        
        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                dropdown.classList.remove('show');
                toggle.setAttribute('aria-expanded', 'false');
                dropdown.setAttribute('aria-hidden', 'true');
            }
        });
        
        // Store language preference in localStorage for persistence
        const langOptions = dropdown.querySelectorAll('.language-option');
        langOptions.forEach(function(option) {
            option.addEventListener('click', function() {
                const url = new URL(this.href);
                const lang = url.searchParams.get('lang');
                if (lang) {
                    localStorage.setItem('preferred_language', lang);
                }
            });
        });
    }
})();
</script>
