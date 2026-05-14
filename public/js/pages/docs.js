/**
 * API Documentation Page JavaScript
 * Handles tab switching, copy to clipboard, and smooth scroll navigation
 */

document.addEventListener('DOMContentLoaded', function() {
    initTabs();
    initCopyButtons();
    initSmoothScroll();
    initActiveNavHighlight();
});

/**
 * Initialize tab switching for code examples
 */
function initTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            const tabContainer = this.closest('.code-tabs');
            
            // Remove active class from all buttons in this container
            tabContainer.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Remove active class from all panes in this container
            tabContainer.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('active');
            });
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Show the corresponding tab pane
            const targetPane = tabContainer.querySelector('#tab-' + tabId);
            if (targetPane) {
                targetPane.classList.add('active');
            }
        });
    });
}

/**
 * Initialize copy to clipboard buttons
 */
function initCopyButtons() {
    const copyButtons = document.querySelectorAll('.copy-btn');
    
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetElement = document.getElementById(targetId);
            
            if (!targetElement) return;
            
            const textToCopy = targetElement.textContent;
            
            // Use modern clipboard API
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(textToCopy)
                    .then(() => showCopyFeedback(button, true))
                    .catch(() => fallbackCopy(textToCopy, button));
            } else {
                fallbackCopy(textToCopy, button);
            }
        });
    });
}

/**
 * Fallback copy method for older browsers
 */
function fallbackCopy(text, button) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();
    
    try {
        document.execCommand('copy');
        showCopyFeedback(button, true);
    } catch (err) {
        showCopyFeedback(button, false);
    }
    
    document.body.removeChild(textarea);
}

/**
 * Show feedback after copy attempt
 */
function showCopyFeedback(button, success) {
    const originalHTML = button.innerHTML;
    
    if (success) {
        button.classList.add('copied');
        button.innerHTML = '<i class="icon-check"></i> Copied!';
    } else {
        button.innerHTML = '<i class="icon-x"></i> Failed';
    }
    
    setTimeout(() => {
        button.classList.remove('copied');
        button.innerHTML = originalHTML;
    }, 2000);
}

/**
 * Initialize smooth scrolling for anchor links
 */
function initSmoothScroll() {
    const navLinks = document.querySelectorAll('.docs-nav a, .docs-content a[href^="#"]');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // Only handle internal anchor links
            if (!href.startsWith('#')) return;
            
            const targetId = href.substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                e.preventDefault();
                
                // Calculate offset for sticky header if present
                const headerOffset = 20;
                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
                
                // Update URL hash without jumping
                history.pushState(null, null, href);
                
                // Update active nav item
                updateActiveNavItem(targetId);
            }
        });
    });
}

/**
 * Update active navigation item based on current section
 */
function updateActiveNavItem(activeId) {
    const navLinks = document.querySelectorAll('.docs-nav a');
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === '#' + activeId) {
            link.classList.add('active');
        }
    });
}

/**
 * Initialize active nav highlighting on scroll
 */
function initActiveNavHighlight() {
    const sections = document.querySelectorAll('.docs-section[id]');
    const navLinks = document.querySelectorAll('.docs-nav a');
    
    if (sections.length === 0 || navLinks.length === 0) return;
    
    // Use Intersection Observer for better performance
    const observerOptions = {
        root: null,
        rootMargin: '-20% 0px -70% 0px',
        threshold: 0
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const id = entry.target.getAttribute('id');
                updateActiveNavItem(id);
            }
        });
    }, observerOptions);
    
    sections.forEach(section => {
        observer.observe(section);
    });
}

/**
 * Global copy function for inline use
 */
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const text = element.textContent;
    
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text)
            .then(() => alert('Copied to clipboard!'))
            .catch(() => alert('Failed to copy'));
    } else {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.select();
        
        try {
            document.execCommand('copy');
            alert('Copied to clipboard!');
        } catch (err) {
            alert('Failed to copy');
        }
        
        document.body.removeChild(textarea);
    }
}
