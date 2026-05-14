/**
 * Landing Page JavaScript
 * Handles FAQ accordion, mobile menu, and smooth scrolling
 */

document.addEventListener('DOMContentLoaded', function() {
    // FAQ Accordion
    initFaqAccordion();
    
    // Mobile Menu Toggle
    initMobileMenu();
    
    // Smooth Scroll for anchor links
    initSmoothScroll();
    
    // Navbar scroll effect
    initNavbarScroll();
});

/**
 * Initialize FAQ Accordion functionality
 */
function initFaqAccordion() {
    const faqQuestions = document.querySelectorAll('.faq-question');
    
    faqQuestions.forEach(function(question) {
        question.addEventListener('click', function() {
            const faqItem = this.parentElement;
            const answer = faqItem.querySelector('.faq-answer');
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            // Close all other FAQ items
            document.querySelectorAll('.faq-item').forEach(function(item) {
                if (item !== faqItem) {
                    item.querySelector('.faq-question').setAttribute('aria-expanded', 'false');
                    item.querySelector('.faq-answer').classList.remove('open');
                }
            });
            
            // Toggle current item
            this.setAttribute('aria-expanded', !isExpanded);
            if (!isExpanded) {
                answer.classList.add('open');
            } else {
                answer.classList.remove('open');
            }
        });
    });
}

/**
 * Initialize Mobile Menu toggle
 */
function initMobileMenu() {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (menuToggle && mobileMenu) {
        menuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('open');
            
            // Update aria-expanded
            const isExpanded = mobileMenu.classList.contains('open');
            menuToggle.setAttribute('aria-expanded', isExpanded);
        });
        
        // Close menu when clicking a link
        mobileMenu.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                mobileMenu.classList.remove('open');
                menuToggle.setAttribute('aria-expanded', 'false');
            });
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!menuToggle.contains(e.target) && !mobileMenu.contains(e.target)) {
                mobileMenu.classList.remove('open');
                menuToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }
}

/**
 * Initialize Smooth Scroll for anchor links
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                e.preventDefault();
                
                const navHeight = document.querySelector('.landing-nav').offsetHeight;
                const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - navHeight - 20;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

/**
 * Initialize Navbar scroll effect
 */
function initNavbarScroll() {
    const nav = document.querySelector('.landing-nav');
    
    if (nav) {
        let lastScroll = 0;
        
        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 100) {
                nav.style.boxShadow = 'var(--shadow-md)';
            } else {
                nav.style.boxShadow = 'none';
            }
            
            lastScroll = currentScroll;
        });
    }
}
