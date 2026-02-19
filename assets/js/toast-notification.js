/**
 * Global Toast Notification System
 * A modern, non-intrusive notification system to replace alert() boxes
 * 
 * Usage:
 *   showToast('Your message', 'success'); // success, error, warning, info
 *   showToast('Your message', 'error', 5000); // with custom duration (ms)
 */

// Initialize toast notification system
(function() {
    'use strict';

    // Only define if not already defined
    if (typeof window.showToast === 'function') {
        return;
    }

    /**
     * Display a toast notification
     * @param {string} message - The message to display
     * @param {string} type - Type of notification: 'success', 'error', 'warning', 'info'
     * @param {number} duration - How long to show the toast in milliseconds (default: 4000)
     * @returns {HTMLElement} The toast element
     */
    window.showToast = function(message, type = 'info', duration = 4000) {
        // Remove existing toasts
        const existingToasts = document.querySelectorAll('.glassify-toast');
        existingToasts.forEach(toast => {
            toast.classList.add('glassify-toast-fade-out');
            setTimeout(() => toast.remove(), 300);
        });

        // Create toast container if it doesn't exist
        let container = document.getElementById('glassify-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'glassify-toast-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 999999;
                display: flex;
                flex-direction: column;
                gap: 10px;
                pointer-events: none;
            `;
            document.body.appendChild(container);
        }

        // Create toast element
        const toast = document.createElement('div');
        toast.className = `glassify-toast glassify-toast-${type}`;
        
        // Configuration for different toast types
        const config = {
            success: { 
                icon: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>', 
                bg: 'linear-gradient(135deg, #28a745 0%, #20c997 100%)', 
                border: '#1e7e34',
                title: 'Success'
            },
            error: { 
                icon: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>', 
                bg: 'linear-gradient(135deg, #dc3545 0%, #c82333 100%)', 
                border: '#bd2130',
                title: 'Error'
            },
            warning: { 
                icon: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>', 
                bg: 'linear-gradient(135deg, #ffc107 0%, #ffb300 100%)', 
                border: '#d39e00',
                title: 'Warning'
            },
            info: { 
                icon: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>', 
                bg: 'linear-gradient(135deg, #17a2b8 0%, #138496 100%)', 
                border: '#117a8b',
                title: 'Info'
            }
        };
        
        const toastConfig = config[type] || config.info;
        const textColor = type === 'warning' ? '#212529' : '#ffffff';
        
        toast.innerHTML = `
            <div class="glassify-toast-icon">${toastConfig.icon}</div>
            <div class="glassify-toast-content">
                <div class="glassify-toast-message">${message}</div>
            </div>
            <button class="glassify-toast-close" onclick="this.parentElement.classList.add('glassify-toast-fade-out'); setTimeout(() => this.parentElement.remove(), 300);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
            <div class="glassify-toast-progress"></div>
        `;
        
        // Apply styles
        toast.style.cssText = `
            background: ${toastConfig.bg};
            color: ${textColor};
            padding: 14px 16px;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 320px;
            max-width: 450px;
            animation: glassifyToastSlideIn 0.35s cubic-bezier(0.21, 1.02, 0.73, 1);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Montserrat', sans-serif;
            border-left: 4px solid ${toastConfig.border};
            pointer-events: auto;
            position: relative;
            overflow: hidden;
        `;
        
        // Add animation styles if not already added
        if (!document.getElementById('glassify-toast-styles')) {
            const style = document.createElement('style');
            style.id = 'glassify-toast-styles';
            style.textContent = `
                @keyframes glassifyToastSlideIn {
                    from {
                        transform: translateX(120%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                @keyframes glassifyToastFadeOut {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(120%);
                        opacity: 0;
                    }
                }
                @keyframes glassifyToastProgress {
                    from {
                        width: 100%;
                    }
                    to {
                        width: 0%;
                    }
                }
                .glassify-toast {
                    transition: all 0.3s ease;
                }
                .glassify-toast-fade-out {
                    animation: glassifyToastFadeOut 0.3s ease forwards !important;
                }
                .glassify-toast-icon {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-shrink: 0;
                }
                .glassify-toast-icon svg {
                    width: 22px;
                    height: 22px;
                }
                .glassify-toast-content {
                    flex: 1;
                    min-width: 0;
                }
                .glassify-toast-message {
                    font-size: 14px;
                    line-height: 1.5;
                    word-wrap: break-word;
                    font-weight: 500;
                }
                .glassify-toast-close {
                    background: rgba(255,255,255,0.2);
                    border: none;
                    color: inherit;
                    cursor: pointer;
                    padding: 4px;
                    width: 28px;
                    height: 28px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    opacity: 0.8;
                    transition: all 0.2s;
                    flex-shrink: 0;
                    border-radius: 50%;
                }
                .glassify-toast-close:hover {
                    opacity: 1;
                    background: rgba(255,255,255,0.3);
                    transform: scale(1.1);
                }
                .glassify-toast-close svg {
                    width: 14px;
                    height: 14px;
                }
                .glassify-toast-progress {
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    height: 3px;
                    background: rgba(255,255,255,0.4);
                    border-radius: 0 0 0 10px;
                }
                .glassify-toast-warning .glassify-toast-progress {
                    background: rgba(0,0,0,0.2);
                }
                
                /* Mobile responsive */
                @media (max-width: 480px) {
                    #glassify-toast-container {
                        left: 10px !important;
                        right: 10px !important;
                    }
                    .glassify-toast {
                        min-width: auto !important;
                        max-width: none !important;
                    }
                }
            `;
            document.head.appendChild(style);
        }
        
        container.appendChild(toast);
        
        // Start progress bar animation
        const progressBar = toast.querySelector('.glassify-toast-progress');
        if (progressBar) {
            progressBar.style.animation = `glassifyToastProgress ${duration}ms linear forwards`;
        }
        
        // Auto remove after duration
        const removeTimeout = setTimeout(() => {
            toast.classList.add('glassify-toast-fade-out');
            setTimeout(() => toast.remove(), 300);
        }, duration);
        
        // Store timeout for potential early dismissal
        toast.removeTimeout = removeTimeout;
        
        return toast;
    };

    /**
     * Convenience methods for different toast types
     */
    window.toastSuccess = function(message, duration) {
        return window.showToast(message, 'success', duration);
    };

    window.toastError = function(message, duration) {
        return window.showToast(message, 'error', duration || 5000); // Errors stay longer by default
    };

    window.toastWarning = function(message, duration) {
        return window.showToast(message, 'warning', duration);
    };

    window.toastInfo = function(message, duration) {
        return window.showToast(message, 'info', duration);
    };

    // Expose a method to clear all toasts
    window.clearAllToasts = function() {
        const toasts = document.querySelectorAll('.glassify-toast');
        toasts.forEach(toast => {
            if (toast.removeTimeout) {
                clearTimeout(toast.removeTimeout);
            }
            toast.classList.add('glassify-toast-fade-out');
            setTimeout(() => toast.remove(), 300);
        });
    };

    console.log('Glassify Toast Notification System loaded');
})();
