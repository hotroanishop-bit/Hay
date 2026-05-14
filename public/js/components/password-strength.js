/**
 * Password Strength Meter Component
 * Provides real-time password strength feedback
 */

class PasswordStrengthMeter {
    constructor(inputElement, options = {}) {
        this.input = inputElement;
        this.options = {
            meterSelector: options.meterSelector || '.password-strength-meter',
            barsSelector: options.barsSelector || '.strength-bar',
            textSelector: options.textSelector || '.strength-text',
            minLength: options.minLength || 8,
            showRequirements: options.showRequirements !== false,
            ...options
        };
        
        this.meter = null;
        this.bars = [];
        this.textElement = null;
        
        this.init();
    }
    
    init() {
        // Find or create meter element
        this.meter = this.input.parentElement.querySelector(this.options.meterSelector);
        if (!this.meter) {
            this.meter = this.createMeter();
            this.input.parentElement.appendChild(this.meter);
        }
        
        this.bars = this.meter.querySelectorAll(this.options.barsSelector);
        this.textElement = this.meter.querySelector(this.options.textSelector);
        
        // Bind events
        this.input.addEventListener('input', () => this.checkStrength());
        this.input.addEventListener('focus', () => this.meter.classList.add('visible'));
        this.input.addEventListener('blur', () => {
            if (!this.input.value) {
                this.meter.classList.remove('visible');
            }
        });
    }
    
    createMeter() {
        const meter = document.createElement('div');
        meter.className = 'password-strength-meter';
        meter.innerHTML = `
            <div class="strength-bars">
                <span class="strength-bar"></span>
                <span class="strength-bar"></span>
                <span class="strength-bar"></span>
                <span class="strength-bar"></span>
            </div>
            <span class="strength-text">Password strength</span>
            ${this.options.showRequirements ? `
            <div class="strength-requirements">
                <div class="requirement" data-req="length">
                    <span class="req-icon"></span>
                    <span>At least ${this.options.minLength} characters</span>
                </div>
                <div class="requirement" data-req="lowercase">
                    <span class="req-icon"></span>
                    <span>Lowercase letter</span>
                </div>
                <div class="requirement" data-req="uppercase">
                    <span class="req-icon"></span>
                    <span>Uppercase letter</span>
                </div>
                <div class="requirement" data-req="number">
                    <span class="req-icon"></span>
                    <span>Number</span>
                </div>
                <div class="requirement" data-req="special">
                    <span class="req-icon"></span>
                    <span>Special character</span>
                </div>
            </div>
            ` : ''}
        `;
        return meter;
    }
    
    checkStrength() {
        const password = this.input.value;
        const checks = this.analyzePassword(password);
        const strength = this.calculateStrength(checks);
        
        this.updateMeter(strength);
        this.updateRequirements(checks);
        
        return strength;
    }
    
    analyzePassword(password) {
        return {
            length: password.length >= this.options.minLength,
            lowercase: /[a-z]/.test(password),
            uppercase: /[A-Z]/.test(password),
            number: /\d/.test(password),
            special: /[^a-zA-Z0-9]/.test(password)
        };
    }
    
    calculateStrength(checks) {
        let score = 0;
        
        if (checks.length) score++;
        if (checks.lowercase && checks.uppercase) score++;
        if (checks.number) score++;
        if (checks.special) score++;
        
        return {
            score,
            level: this.getLevel(score),
            label: this.getLabel(score)
        };
    }
    
    getLevel(score) {
        if (score === 0) return '';
        if (score === 1) return 'weak';
        if (score === 2) return 'fair';
        if (score === 3) return 'good';
        return 'strong';
    }
    
    getLabel(score) {
        if (score === 0) return 'Password strength';
        if (score === 1) return 'Weak';
        if (score === 2) return 'Fair';
        if (score === 3) return 'Good';
        return 'Strong';
    }
    
    updateMeter(strength) {
        // Update bars
        this.bars.forEach((bar, index) => {
            bar.className = 'strength-bar';
            if (index < strength.score) {
                bar.classList.add(strength.level);
            }
        });
        
        // Update text
        if (this.textElement) {
            this.textElement.textContent = strength.label;
            this.textElement.className = 'strength-text';
            if (strength.level) {
                this.textElement.classList.add('strength-' + strength.level);
            }
        }
    }
    
    updateRequirements(checks) {
        const requirements = this.meter.querySelectorAll('.requirement');
        requirements.forEach(req => {
            const type = req.dataset.req;
            if (checks[type]) {
                req.classList.add('met');
            } else {
                req.classList.remove('met');
            }
        });
    }
    
    isStrong() {
        return this.checkStrength().score >= 3;
    }
    
    getScore() {
        return this.checkStrength().score;
    }
}

// Auto-initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Find all password inputs with data-strength attribute
    document.querySelectorAll('input[type="password"][data-strength]').forEach(input => {
        new PasswordStrengthMeter(input);
    });
});

// CSS for the password strength meter (inline styles fallback)
const strengthStyles = `
.password-strength-meter {
    margin-top: 8px;
    opacity: 0;
    max-height: 0;
    overflow: hidden;
    transition: all 0.2s ease;
}

.password-strength-meter.visible {
    opacity: 1;
    max-height: 200px;
}

.strength-bars {
    display: flex;
    gap: 4px;
    margin-bottom: 4px;
}

.strength-bar {
    flex: 1;
    height: 4px;
    background: var(--bg-tertiary, #e9ecef);
    border-radius: 2px;
    transition: background-color 0.2s ease;
}

.strength-bar.weak { background-color: var(--color-error, #dc3545); }
.strength-bar.fair { background-color: var(--color-warning, #ffc107); }
.strength-bar.good { background-color: var(--color-info, #17a2b8); }
.strength-bar.strong { background-color: var(--color-success, #28a745); }

.strength-text {
    font-size: 12px;
    color: var(--text-muted, #6c757d);
    transition: color 0.2s ease;
}

.strength-text.strength-weak { color: var(--color-error, #dc3545); }
.strength-text.strength-fair { color: var(--color-warning, #ffc107); }
.strength-text.strength-good { color: var(--color-info, #17a2b8); }
.strength-text.strength-strong { color: var(--color-success, #28a745); }

.strength-requirements {
    margin-top: 8px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.requirement {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--text-muted, #6c757d);
}

.requirement .req-icon::before {
    content: '\\2715';
    color: var(--color-error, #dc3545);
}

.requirement.met {
    color: var(--color-success, #28a745);
}

.requirement.met .req-icon::before {
    content: '\\2713';
    color: var(--color-success, #28a745);
}
`;

// Inject styles if not already present
if (!document.getElementById('password-strength-styles')) {
    const styleSheet = document.createElement('style');
    styleSheet.id = 'password-strength-styles';
    styleSheet.textContent = strengthStyles;
    document.head.appendChild(styleSheet);
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PasswordStrengthMeter;
}
