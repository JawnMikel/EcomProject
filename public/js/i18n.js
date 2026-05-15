/**
 * GAINZ Localization System
 * JSON-based i18n for seamless language switching
 */

class LocalizationManager {
    constructor() {
        this.currentLang = localStorage.getItem('gainz-lang') || 'en';
        this.translations = {};
        this.isLoaded = false;
    }

    /**
     * Initialize the localization system
     */
    async init() {
        await this.loadTranslations(this.currentLang);
        this.applyTranslations();
        this.setupLanguageSwitcher();
        this.isLoaded = true;
    }

    /**
     * Load translations for a specific language
     */
    async loadTranslations(lang) {
        try {
            const response = await fetch(`/EcomProject/public/locales/${lang}.json`);
            if (!response.ok) {
                throw new Error(`Failed to load ${lang} translations`);
            }
            this.translations = await response.json();
            this.currentLang = lang;
            localStorage.setItem('gainz-lang', lang);
        } catch (error) {
            console.error('Error loading translations:', error);
            // Fallback to English if French fails to load
            if (lang !== 'en') {
                await this.loadTranslations('en');
            }
        }
    }

    /**
     * Get a translation value by key path (dot notation)
     */
    getTranslation(keyPath, variables = {}) {
        const keys = keyPath.split('.');
        let value = this.translations;

        for (const key of keys) {
            if (value && typeof value === 'object' && key in value) {
                value = value[key];
            } else {
                return keyPath; // Return key if translation not found
            }
        }

        // Handle string interpolation
        if (typeof value === 'string') {
            return value.replace(/\{\{(\w+)\}\}/g, (match, varName) => {
                return variables[varName] || match;
            });
        }

        return value;
    }

    /**
     * Apply translations to all elements with data-i18n attributes
     */
    applyTranslations() {
        // Handle data-i18n attributes
        document.querySelectorAll('[data-i18n]').forEach(element => {
            const key = element.getAttribute('data-i18n');
            const translation = this.getTranslation(key);

            if (translation) {
                // Handle different element types
                if (element.tagName === 'INPUT' && element.hasAttribute('placeholder')) {
                    element.placeholder = translation;
                } else if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                    // Don't translate input values, only placeholders
                    if (element.hasAttribute('placeholder')) {
                        element.placeholder = translation;
                    }
                } else {
                    element.textContent = translation;
                }
            }
        });

        // Handle data-i18n-html attributes (for HTML content)
        document.querySelectorAll('[data-i18n-html]').forEach(element => {
            const key = element.getAttribute('data-i18n-html');
            const translation = this.getTranslation(key);

            if (translation) {
                element.innerHTML = translation;
            }
        });

        // Handle data-i18n-title attributes
        document.querySelectorAll('[data-i18n-title]').forEach(element => {
            const key = element.getAttribute('data-i18n-title');
            const translation = this.getTranslation(key);

            if (translation) {
                element.title = translation;
            }
        });

        // Handle dynamic content with variables
        document.querySelectorAll('[data-i18n-vars]').forEach(element => {
            const key = element.getAttribute('data-i18n');
            const varsAttr = element.getAttribute('data-i18n-vars');

            if (key && varsAttr) {
                try {
                    const variables = JSON.parse(varsAttr);
                    const translation = this.getTranslation(key, variables);

                    if (element.tagName === 'INPUT' && element.hasAttribute('placeholder')) {
                        element.placeholder = translation;
                    } else {
                        element.textContent = translation;
                    }
                } catch (e) {
                    console.error('Error parsing i18n variables:', e);
                }
            }
        });

        // Handle pluralization and conditional text
        this.applyDynamicTranslations();
    }

    /**
     * Apply dynamic translations that require logic (pluralization, conditionals)
     */
    applyDynamicTranslations() {
        // Handle workout streak pluralization
        const streakElements = document.querySelectorAll('[data-dynamic="workout-streak"]');
        streakElements.forEach(element => {
            const count = parseInt(element.getAttribute('data-count') || '0');
            const unit = count === 1 ?
                this.getTranslation('dashboard.stats.days') :
                this.getTranslation('dashboard.stats.days_plural');
            element.textContent = unit;
        });

        // Handle workout count pluralization
        const workoutCountElements = document.querySelectorAll('[data-dynamic="workout-count"]');
        workoutCountElements.forEach(element => {
            const count = parseInt(element.getAttribute('data-count') || '0');
            const text = count === 1 ?
                this.getTranslation('dashboard.stats.workouts_logged') :
                this.getTranslation('dashboard.stats.workouts_logged_plural');
            element.textContent = text;
        });

        // Handle program count pluralization
        const programCountElements = document.querySelectorAll('[data-dynamic="program-count"]');
        programCountElements.forEach(element => {
            const count = parseInt(element.getAttribute('data-count') || '0');
            const text = count === 1 ?
                this.getTranslation('dashboard.stats.active_program') :
                this.getTranslation('dashboard.stats.active_programs');
            element.textContent = text;
        });

        // Handle conditional status text
        const statusElements = document.querySelectorAll('[data-dynamic="status"]');
        statusElements.forEach(element => {
            const type = element.getAttribute('data-type');
            const condition = element.getAttribute('data-condition') === 'true';

            let translationKey = '';
            switch (type) {
                case 'workout-consistency':
                    translationKey = condition ? 'dashboard.progress_checklist.on_track' : 'dashboard.progress_checklist.start_now';
                    break;
                case 'weight-log':
                    translationKey = condition ? 'dashboard.progress_checklist.up_to_date' : 'dashboard.progress_checklist.add_entry';
                    break;
                case 'complete-today':
                    translationKey = condition ? 'dashboard.todays_focus.keep_going' : 'dashboard.todays_focus.start_logging';
                    break;
                case 'weekly-goal':
                    translationKey = condition ? 'dashboard.todays_focus.stay_consistent' : 'dashboard.todays_focus.pick_a_program';
                    break;
            }

            if (translationKey) {
                element.textContent = this.getTranslation(translationKey);
            }
        });
    }

    /**
     * Switch to a different language
     */
    async switchLanguage(lang) {
        if (lang === this.currentLang) return;

        await this.loadTranslations(lang);
        this.applyTranslations();

        // Update language switcher UI
        this.updateLanguageSwitcher();

        // Dispatch custom event for other scripts to listen to
        window.dispatchEvent(new CustomEvent('languageChanged', {
            detail: { language: lang }
        }));
    }

    /**
     * Setup the language switcher in the navigation
     */
    setupLanguageSwitcher() {
        // Create language switcher element
        const switcher = document.createElement('li');
        switcher.className = 'nav-item dropdown';
        switcher.innerHTML = `
            <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-translate"></i> ${this.currentLang.toUpperCase()}
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                <li><a class="dropdown-item" href="#" data-lang="en">
                    <i class="bi bi-flag-fill" style="color: #012169;"></i> English
                </a></li>
                <li><a class="dropdown-item" href="#" data-lang="fr">
                    <i class="bi bi-flag-fill" style="color: #002654;"></i> Français
                </a></li>
            </ul>
        `;

        // Add event listeners
        switcher.querySelectorAll('[data-lang]').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const lang = e.currentTarget.getAttribute('data-lang');
                this.switchLanguage(lang);
            });
        });

        // Insert into navigation if navbar exists
        const navbarNav = document.querySelector('.navbar-nav');
        if (navbarNav) {
            navbarNav.appendChild(switcher);
        }
    }

    /**
     * Update the language switcher display
     */
    updateLanguageSwitcher() {
        const dropdown = document.getElementById('languageDropdown');
        if (dropdown) {
            dropdown.innerHTML = `<i class="bi bi-translate"></i> ${this.currentLang.toUpperCase()}`;
        }
    }
}

// Initialize localization when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.i18n = new LocalizationManager();
    window.i18n.init();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LocalizationManager;
}