/**
 * Theme Store - Manages light/dark mode preference for the admin panel.
 * Uses Svelte 5 runes with localStorage persistence.
 */

const STORAGE_KEY = 'cernecms-theme';

class ThemeStore {
    current = $state('light');

    constructor() {
        // Initialize from localStorage or system preference
        if (typeof window !== 'undefined') {
            const stored = localStorage.getItem(STORAGE_KEY);
            if (stored === 'dark' || stored === 'light') {
                this.current = stored;
            } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                this.current = 'dark';
            }
            this.applyTheme();
        }
    }

    get isDark() {
        return this.current === 'dark';
    }

    toggle() {
        this.current = this.current === 'dark' ? 'light' : 'dark';
        this.applyTheme();
        this.persist();
    }

    setTheme(theme) {
        if (theme === 'dark' || theme === 'light') {
            this.current = theme;
            this.applyTheme();
            this.persist();
        }
    }

    applyTheme() {
        if (typeof document !== 'undefined') {
            const root = document.documentElement;
            if (this.current === 'dark') {
                root.classList.add('dark');
            } else {
                root.classList.remove('dark');
            }
        }
    }

    persist() {
        if (typeof localStorage !== 'undefined') {
            localStorage.setItem(STORAGE_KEY, this.current);
        }
    }
}

export const themeStore = new ThemeStore();
