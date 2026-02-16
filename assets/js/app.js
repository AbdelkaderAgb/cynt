/**
 * CYN Tourism - App JavaScript (Alpine.js Components + Utilities)
 * 
 * Replaces ui-utils.js with Alpine.js-powered components.
 * Loaded by the main layout template.
 * 
 * @version 3.0.0
 */

// ============================================
// Alpine.js Global Components
// ============================================

/**
 * App Shell — manages sidebar state across the layout
 */
function appShell() {
    return {
        sidebarOpen: false,
        sidebarCollapsed: localStorage.getItem('sidebar-collapsed') === 'true',

        init() {
            // Close sidebar on desktop resize
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024) {
                    this.sidebarOpen = false;
                }
            });

            // Listen for escape key
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.sidebarOpen = false;
                }
            });
        }
    }
}

/**
 * Toast Manager — notification system
 */
function toastManager() {
    return {
        toasts: [],
        nextId: 0,

        addToast(message, type = 'info', title = '', duration = 5000) {
            const icons = {
                success: 'fas fa-check-circle',
                error: 'fas fa-exclamation-circle',
                warning: 'fas fa-exclamation-triangle',
                info: 'fas fa-info-circle'
            };
            const iconClasses = {
                success: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400',
                error: 'bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400',
                warning: 'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400',
                info: 'bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400'
            };

            const id = this.nextId++;
            const toast = {
                id,
                message,
                title,
                type,
                icon: icons[type] || icons.info,
                iconClass: iconClasses[type] || iconClasses.info,
                visible: true,
            };

            this.toasts.push(toast);

            if (duration > 0) {
                setTimeout(() => this.removeToast(id), duration);
            }
        },

        removeToast(id) {
            const index = this.toasts.findIndex(t => t.id === id);
            if (index > -1) {
                this.toasts[index].visible = false;
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 300);
            }
        }
    }
}

// ============================================
// Global Toast API (for non-Alpine contexts)
// ============================================

const Toast = {
    _getManager() {
        const container = document.getElementById('toast-container');
        return container ? Alpine.$data(container) : null;
    },

    show(message, type = 'info', title = '') {
        const manager = this._getManager();
        if (manager && manager.addToast) {
            manager.addToast(message, type, title);
        }
    },

    success(message, title = 'Success') { this.show(message, 'success', title); },
    error(message, title = 'Error') { this.show(message, 'error', title); },
    warning(message, title = 'Warning') { this.show(message, 'warning', title); },
    info(message, title = 'Info') { this.show(message, 'info', title); },
};

// ============================================
// Utility Functions
// ============================================

const Utils = {
    debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func(...args), wait);
        };
    },

    throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func(...args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },

    formatDate(date, format = 'YYYY-MM-DD') {
        const d = new Date(date);
        return format
            .replace('YYYY', d.getFullYear())
            .replace('MM', String(d.getMonth() + 1).padStart(2, '0'))
            .replace('DD', String(d.getDate()).padStart(2, '0'));
    },

    formatNumber(num, decimals = 0) {
        return Number(num).toLocaleString('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    },

    async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            Toast.success('Copied to clipboard');
        } catch {
            Toast.error('Failed to copy');
        }
    }
};

// ============================================
// Confirm Delete Helper
// ============================================

function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this item?');
}

// ============================================
// Print Helper
// ============================================

function printPage() {
    window.print();
}
