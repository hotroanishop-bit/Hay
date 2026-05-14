/**
 * Bottom Sheet Component
 * Slide-up menu with swipe-to-close gesture support
 */
(function() {
    'use strict';

    /**
     * BottomSheet class constructor
     * @param {HTMLElement} element - The bottom sheet container element
     */
    function BottomSheet(element) {
        if (!element) {
            console.warn('BottomSheet: No element provided');
            return;
        }

        this.sheet = element;
        this.backdrop = this.sheet.querySelector('.bottom-sheet-backdrop');
        this.content = this.sheet.querySelector('.bottom-sheet-content');
        this.handle = this.sheet.querySelector('.bottom-sheet-handle');
        this.closeBtn = this.sheet.querySelector('.bottom-sheet-close');
        
        this.startY = 0;
        this.currentY = 0;
        this.isDragging = false;
        this.sheetHeight = 0;
        
        this.bindEvents();
    }

    /**
     * Bind all event listeners
     */
    BottomSheet.prototype.bindEvents = function() {
        var self = this;
        
        // Backdrop click to close
        if (this.backdrop) {
            this.backdrop.addEventListener('click', function() {
                self.close();
            });
        }

        // Close button click
        if (this.closeBtn) {
            this.closeBtn.addEventListener('click', function() {
                self.close();
            });
        }

        // Handle drag events - touch
        if (this.handle) {
            this.handle.addEventListener('touchstart', function(e) {
                self.onTouchStart(e);
            }, { passive: true });
            
            this.handle.addEventListener('touchmove', function(e) {
                self.onTouchMove(e);
            }, { passive: false });
            
            this.handle.addEventListener('touchend', function() {
                self.onTouchEnd();
            });

            // Mouse drag support for desktop
            this.handle.addEventListener('mousedown', function(e) {
                self.onMouseDown(e);
            });
        }

        // Escape key to close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && self.isOpen()) {
                self.close();
            }
        });

        // Trigger elements
        var triggerId = this.sheet.id;
        if (triggerId) {
            document.querySelectorAll('[data-bottom-sheet="' + triggerId + '"]').forEach(function(trigger) {
                trigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    self.open();
                });
            });
        }
    };

    /**
     * Touch start handler
     * @param {TouchEvent} e
     */
    BottomSheet.prototype.onTouchStart = function(e) {
        this.startY = e.touches[0].clientY;
        this.isDragging = true;
        this.sheetHeight = this.sheet.getBoundingClientRect().height;
        this.sheet.classList.add('dragging');
        this.sheet.style.transition = 'none';
    };

    /**
     * Touch move handler
     * @param {TouchEvent} e
     */
    BottomSheet.prototype.onTouchMove = function(e) {
        if (!this.isDragging) return;
        
        this.currentY = e.touches[0].clientY;
        var diff = this.currentY - this.startY;
        
        // Only allow dragging down
        if (diff > 0) {
            e.preventDefault();
            this.sheet.style.transform = 'translateY(' + diff + 'px)';
            
            // On desktop, account for centering transform
            if (window.innerWidth >= 768) {
                this.sheet.style.transform = 'translateX(-50%) translateY(' + diff + 'px)';
            }
        }
    };

    /**
     * Touch end handler
     */
    BottomSheet.prototype.onTouchEnd = function() {
        if (!this.isDragging) return;
        
        this.isDragging = false;
        this.sheet.classList.remove('dragging');
        this.sheet.style.transition = '';
        
        var diff = this.currentY - this.startY;
        var threshold = this.sheetHeight * 0.25; // 25% of sheet height
        
        if (diff > threshold || diff > 150) {
            this.close();
        } else {
            // Reset position
            this.sheet.style.transform = '';
        }
        
        this.startY = 0;
        this.currentY = 0;
    };

    /**
     * Mouse down handler for desktop drag
     * @param {MouseEvent} e
     */
    BottomSheet.prototype.onMouseDown = function(e) {
        var self = this;
        this.startY = e.clientY;
        this.isDragging = true;
        this.sheetHeight = this.sheet.getBoundingClientRect().height;
        this.sheet.classList.add('dragging');
        this.sheet.style.transition = 'none';
        
        var onMouseMove = function(e) {
            if (!self.isDragging) return;
            
            self.currentY = e.clientY;
            var diff = self.currentY - self.startY;
            
            if (diff > 0) {
                self.sheet.style.transform = 'translateY(' + diff + 'px)';
                if (window.innerWidth >= 768) {
                    self.sheet.style.transform = 'translateX(-50%) translateY(' + diff + 'px)';
                }
            }
        };
        
        var onMouseUp = function() {
            self.onTouchEnd();
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        };
        
        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    };

    /**
     * Open the bottom sheet
     */
    BottomSheet.prototype.open = function() {
        this.sheet.classList.add('open');
        if (this.backdrop) {
            this.backdrop.classList.add('open');
        }
        document.body.style.overflow = 'hidden';
        this.sheet.setAttribute('aria-hidden', 'false');
        
        // Focus first focusable element
        var focusable = this.sheet.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (focusable) {
            focusable.focus();
        }
        
        // Dispatch custom event
        this.sheet.dispatchEvent(new CustomEvent('bottomsheet:open', { bubbles: true }));
    };

    /**
     * Close the bottom sheet
     */
    BottomSheet.prototype.close = function() {
        this.sheet.classList.remove('open');
        if (this.backdrop) {
            this.backdrop.classList.remove('open');
        }
        document.body.style.overflow = '';
        this.sheet.setAttribute('aria-hidden', 'true');
        this.sheet.style.transform = '';
        
        // Dispatch custom event
        this.sheet.dispatchEvent(new CustomEvent('bottomsheet:close', { bubbles: true }));
    };

    /**
     * Toggle the bottom sheet
     */
    BottomSheet.prototype.toggle = function() {
        if (this.isOpen()) {
            this.close();
        } else {
            this.open();
        }
    };

    /**
     * Check if the bottom sheet is open
     * @returns {boolean}
     */
    BottomSheet.prototype.isOpen = function() {
        return this.sheet.classList.contains('open');
    };

    // Store instances on elements
    var instances = new WeakMap();

    /**
     * Initialize a bottom sheet or get existing instance
     * @param {HTMLElement} element
     * @returns {BottomSheet}
     */
    function getInstance(element) {
        if (instances.has(element)) {
            return instances.get(element);
        }
        var instance = new BottomSheet(element);
        instances.set(element, instance);
        return instance;
    }

    /**
     * Initialize all bottom sheets in the document
     */
    function initAll() {
        document.querySelectorAll('.bottom-sheet').forEach(function(element) {
            getInstance(element);
        });
    }

    // Export to global scope
    window.BottomSheet = BottomSheet;
    window.BottomSheet.getInstance = getInstance;
    window.BottomSheet.initAll = initAll;
})();
