/**
 * Glassify Confirmation Dialog System
 * A modern, async confirmation modal to replace browser confirm() dialogs
 * 
 * Usage:
 *   const confirmed = await showConfirmationAsync('Are you sure?');
 *   if (!confirmed) return;
 * 
 * With custom options:
 *   const confirmed = await showConfirmationAsync(
 *       'Are you sure you want to delete this?',
 *       'Confirm Delete',
 *       'Yes, Delete',
 *       'Cancel'
 *   );
 */

(function() {
    'use strict';

    // Create and inject styles
    function injectStyles() {
        if (document.getElementById('confirmation-dialog-styles')) return;
        
        const style = document.createElement('style');
        style.id = 'confirmation-dialog-styles';
        style.textContent = `
            .confirmation-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 100000;
                animation: confirmFadeIn 0.2s ease;
                font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }
            
            .confirmation-dialog {
                background: white;
                border-radius: 12px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                max-width: 420px;
                width: 90%;
                animation: confirmSlideIn 0.3s ease;
                overflow: hidden;
            }
            
            .confirmation-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 18px 24px;
                font-size: 18px;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .confirmation-header-icon {
                width: 24px;
                height: 24px;
                background: rgba(255,255,255,0.2);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 14px;
            }
            
            .confirmation-body {
                padding: 24px;
                font-size: 15px;
                line-height: 1.6;
                color: #333;
                white-space: pre-line;
            }
            
            .confirmation-footer {
                padding: 16px 24px;
                background: #f8f9fa;
                display: flex;
                justify-content: flex-end;
                gap: 12px;
                border-top: 1px solid #e9ecef;
            }
            
            .confirmation-btn {
                padding: 10px 20px;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s ease;
                border: none;
                font-family: inherit;
            }
            
            .confirmation-btn-cancel {
                background: #e9ecef;
                color: #495057;
            }
            
            .confirmation-btn-cancel:hover {
                background: #dee2e6;
            }
            
            .confirmation-btn-confirm {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            
            .confirmation-btn-confirm:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            }
            
            .confirmation-btn:focus {
                outline: none;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3);
            }
            
            @keyframes confirmFadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            @keyframes confirmSlideIn {
                from { 
                    opacity: 0;
                    transform: scale(0.9) translateY(-20px);
                }
                to { 
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
            }
            
            @keyframes confirmFadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
            
            .confirmation-overlay.closing {
                animation: confirmFadeOut 0.2s ease forwards;
            }
            
            .confirmation-overlay.closing .confirmation-dialog {
                animation: confirmSlideOut 0.2s ease forwards;
            }
            
            @keyframes confirmSlideOut {
                from { 
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
                to { 
                    opacity: 0;
                    transform: scale(0.9) translateY(-20px);
                }
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Show a confirmation dialog and return a promise
     * @param {string} message - The message to display
     * @param {string} title - The dialog title (default: 'Confirm')
     * @param {string} confirmText - Text for confirm button (default: 'Confirm')
     * @param {string} cancelText - Text for cancel button (default: 'Cancel')
     * @returns {Promise<boolean>} - Resolves to true if confirmed, false if cancelled
     */
    function showConfirmationAsync(message, title = 'Confirm', confirmText = 'Confirm', cancelText = 'Cancel') {
        injectStyles();
        
        return new Promise((resolve) => {
            // Create overlay
            const overlay = document.createElement('div');
            overlay.className = 'confirmation-overlay';
            
            // Create dialog
            overlay.innerHTML = `
                <div class="confirmation-dialog" role="dialog" aria-modal="true" aria-labelledby="confirm-title">
                    <div class="confirmation-header">
                        <span class="confirmation-header-icon">?</span>
                        <span id="confirm-title">${escapeHtml(title)}</span>
                    </div>
                    <div class="confirmation-body">${escapeHtml(message)}</div>
                    <div class="confirmation-footer">
                        <button class="confirmation-btn confirmation-btn-cancel" data-action="cancel">
                            ${escapeHtml(cancelText)}
                        </button>
                        <button class="confirmation-btn confirmation-btn-confirm" data-action="confirm">
                            ${escapeHtml(confirmText)}
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(overlay);
            
            // Focus the confirm button
            const confirmBtn = overlay.querySelector('[data-action="confirm"]');
            const cancelBtn = overlay.querySelector('[data-action="cancel"]');
            confirmBtn.focus();
            
            // Close function
            function closeDialog(result) {
                overlay.classList.add('closing');
                setTimeout(() => {
                    overlay.remove();
                    resolve(result);
                }, 200);
            }
            
            // Button click handlers
            confirmBtn.addEventListener('click', () => closeDialog(true));
            cancelBtn.addEventListener('click', () => closeDialog(false));
            
            // Click outside to cancel
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    closeDialog(false);
                }
            });
            
            // Keyboard handlers
            overlay.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeDialog(false);
                } else if (e.key === 'Enter' && document.activeElement === confirmBtn) {
                    closeDialog(true);
                }
            });
        });
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Export to global scope
    window.showConfirmationAsync = showConfirmationAsync;

    console.log('Glassify Confirmation Dialog System loaded');
})();
