/**
 * AJAX service for customization functionality
 * Handles saving/loading customization selections and real-time updates
 */

// Configuration
const AJAX_CONFIG = {
    baseUrl: typeof base_url !== 'undefined' ? base_url : '',
    endpoints: {
        saveCustomization: 'customization/save',
        loadCustomization: 'customization/load',
        getPriceUpdate: 'customization/price'
    },
    debounceDelay: 1000, // Delay for auto-save in milliseconds
    retryAttempts: 3,
    retryDelay: 1000
};

// Auto-save timer
let autoSaveTimer = null;

// Store customization state
let customizationState = {
    productId: null,
    selections: {},
    lastSaved: null,
    isDirty: false
};

/**
 * Initialize AJAX functionality for customizations
 */
function initCustomizationAjax() {
    console.log('Initializing customization AJAX functionality');

    // Get product ID from global scope
    if (window.selectedProduct && window.selectedProduct.id) {
        customizationState.productId = window.selectedProduct.id;
    }

    // Set up auto-save on customization changes
    setupAutoSave();

    // Load any previously saved customization
    loadSavedCustomization();

    console.log('Customization AJAX initialized successfully');
}

/**
 * Set up auto-save functionality
 */
function setupAutoSave() {
    // Get the existing selectedCustomizationValues object
    let targetObject = window.selectedCustomizationValues || {};
    
    // Wrap the object with a Proxy to intercept property changes
    const handler = {
        set: function(target, property, value) {
            const oldValue = target[property];
            target[property] = value;
            
            // Only trigger if value actually changed
            if (oldValue !== value) {
                customizationState.selections = { ...target };
                customizationState.isDirty = true;

                // Trigger 2D preview update immediately
                update2DPreview();

                // Trigger auto-save with debounce
                triggerAutoSave();

                // Trigger real-time price update
                triggerPriceUpdate();
            }

            return true;
        },
        deleteProperty: function(target, property) {
            delete target[property];
            customizationState.selections = { ...target };
            customizationState.isDirty = true;

            // Trigger 2D preview update immediately
            update2DPreview();

            // Trigger auto-save with debounce
            triggerAutoSave();

            // Trigger real-time price update
            triggerPriceUpdate();

            return true;
        }
    };

    // Create proxy and replace the global reference
    window.selectedCustomizationValues = new Proxy(targetObject, handler);
    
    // Also set up mutation observer as a fallback for direct property access
    setupMutationObserver();

    console.log('Auto-save functionality set up');
}

/**
 * Set up mutation observer as fallback for direct property assignments
 */
function setupMutationObserver() {
    // Monitor for changes to selectedCustomizationValues that bypass the proxy
    setInterval(() => {
        const current = window.selectedCustomizationValues || {};
        const currentKeys = Object.keys(current).sort().join(',');
        const savedKeys = Object.keys(customizationState.selections).sort().join(',');

        // Check if values have changed
        let hasChanges = false;
        if (currentKeys !== savedKeys) {
            hasChanges = true;
        } else {
            for (const key in current) {
                if (JSON.stringify(current[key]) !== JSON.stringify(customizationState.selections[key])) {
                    hasChanges = true;
                    break;
                }
            }
        }

        if (hasChanges) {
            customizationState.selections = { ...current };
            customizationState.isDirty = true;
            update2DPreview();
            triggerAutoSave();
            triggerPriceUpdate();
        }
    }, 500); // Check every 500ms
}

/**
 * Update 2D preview immediately when selections change
 */
function update2DPreview() {
    // Call renderCustomState if available
    if (typeof renderCustomState === 'function') {
        try {
            renderCustomState();
        } catch (e) {
            console.warn('Error updating 2D preview:', e);
        }
    } else if (typeof window.renderCustomState === 'function') {
        try {
            window.renderCustomState();
        } catch (e) {
            console.warn('Error updating 2D preview:', e);
        }
    }
}

/**
 * Trigger auto-save with debounce
 */
function triggerAutoSave() {
    if (autoSaveTimer) {
        clearTimeout(autoSaveTimer);
    }

    autoSaveTimer = setTimeout(() => {
        saveCustomizationSelections();
    }, AJAX_CONFIG.debounceDelay);
}

/**
 * Save customization selections to server (or localStorage if not logged in)
 */
async function saveCustomizationSelections() {
    if (!customizationState.productId || !customizationState.isDirty) {
        return;
    }

    const data = {
        product_id: customizationState.productId,
        selections: customizationState.selections,
        timestamp: Date.now()
    };

    // Always save to localStorage for immediate access
    saveToLocalStorage();

    try {
        const response = await makeAjaxRequest('POST', AJAX_CONFIG.endpoints.saveCustomization, data);

        if (response.success) {
            customizationState.lastSaved = new Date();
            customizationState.isDirty = false;

            // Show success indicator
            showSaveIndicator('saved');

            console.log('Customization selections saved successfully');
        } else {
            // Even if server save fails, localStorage save succeeded
            if (response.message && response.message.includes('not logged in')) {
                showSaveIndicator('saved'); // Show as saved since localStorage worked
            } else {
                throw new Error(response.message || 'Save failed');
            }
        }
    } catch (error) {
        console.warn('Server save failed (using localStorage):', error);
        // Don't show error if we have localStorage as backup
        customizationState.lastSaved = new Date();
        customizationState.isDirty = false;
        showSaveIndicator('saved');
    }
}

/**
 * Save to localStorage as backup
 */
function saveToLocalStorage() {
    try {
        const storageKey = `glassify_customization_${customizationState.productId}`;
        const data = {
            selections: customizationState.selections,
            timestamp: Date.now(),
            productId: customizationState.productId
        };
        localStorage.setItem(storageKey, JSON.stringify(data));
        console.log('Customization saved to localStorage');
    } catch (e) {
        console.error('Failed to save to localStorage:', e);
    }
}

/**
 * Load from localStorage as backup
 */
function loadFromLocalStorage() {
    try {
        const storageKey = `glassify_customization_${customizationState.productId}`;
        const saved = localStorage.getItem(storageKey);
        if (saved) {
            const data = JSON.parse(saved);
            if (data.selections) {
                return data.selections;
            }
        }
    } catch (e) {
        console.error('Failed to load from localStorage:', e);
    }
    return null;
}

/**
 * Load saved customization selections from server (or localStorage)
 */
async function loadSavedCustomization() {
    if (!customizationState.productId) {
        return;
    }

    let loadedSelections = null;

    // Try to load from server first
    try {
        const response = await makeAjaxRequest('GET', AJAX_CONFIG.endpoints.loadCustomization, {
            product_id: customizationState.productId
        });

        if (response.success && response.selections) {
            loadedSelections = response.selections;
            customizationState.lastSaved = new Date(response.timestamp);
            console.log('Saved customization selections loaded from server');
        }
    } catch (error) {
        console.warn('Failed to load from server, trying localStorage:', error);
    }

    // Fallback to localStorage if server load failed
    if (!loadedSelections) {
        loadedSelections = loadFromLocalStorage();
        if (loadedSelections) {
            console.log('Saved customization selections loaded from localStorage');
        }
    }

    // Apply loaded selections to the UI
    if (loadedSelections) {
        applySavedSelections(loadedSelections);
        customizationState.selections = loadedSelections;
    }
}

/**
 * Apply saved selections to the UI
 */
function applySavedSelections(selections) {
    Object.keys(selections).forEach(fieldId => {
        const value = selections[fieldId];

        // Find the corresponding form element
        const element = findFormElement(fieldId, value);
        if (element) {
            setElementValue(element, value);
        }

        // Update the global selections
        window.selectedCustomizationValues[fieldId] = value;
    });

    // Trigger a re-render of the 2D preview
    if (typeof renderCustomState === 'function') {
        setTimeout(() => renderCustomState(), 100);
    }
}

/**
 * Find form element for a field
 */
function findFormElement(fieldId, value) {
    // Try different selectors to find the element
    const selectors = [
        `[data-field-id="${fieldId}"][value="${value}"]`,
        `[data-field-id="${fieldId}"] [value="${value}"]`,
        `#${fieldId}_${value}`,
        `input[name="${fieldId}"][value="${value}"]`,
        `select[name="${fieldId}"] option[value="${value}"]`
    ];

    for (const selector of selectors) {
        const element = document.querySelector(selector);
        if (element) {
            return element;
        }
    }

    return null;
}

/**
 * Set value for a form element
 */
function setElementValue(element, value) {
    if (element.type === 'radio' || element.type === 'checkbox') {
        element.checked = true;
        // Trigger change event
        element.dispatchEvent(new Event('change', { bubbles: true }));
    } else if (element.tagName === 'OPTION') {
        element.selected = true;
        element.parentElement.dispatchEvent(new Event('change', { bubbles: true }));
    } else if (element.type === 'text' || element.type === 'number') {
        element.value = value;
        element.dispatchEvent(new Event('input', { bubbles: true }));
    }
}

/**
 * Trigger real-time price update
 */
function triggerPriceUpdate() {
    // Debounce price updates
    if (window.priceUpdateTimer) {
        clearTimeout(window.priceUpdateTimer);
    }

    window.priceUpdateTimer = setTimeout(() => {
        updatePriceRealtime();
    }, 300);
}

/**
 * Update price in real-time via AJAX
 */
async function updatePriceRealtime() {
    if (!customizationState.productId) {
        return;
    }

    const data = {
        product_id: customizationState.productId,
        selections: customizationState.selections,
        dimensions: getCurrentDimensions()
    };

    try {
        const response = await makeAjaxRequest('POST', AJAX_CONFIG.endpoints.getPriceUpdate, data);

        if (response.success && response.price !== undefined) {
            updatePriceDisplay(response.price, response.breakdown);
            console.log('Price updated successfully');
        }
    } catch (error) {
        console.error('Failed to update price:', error);
    }
}

/**
 * Get current dimensions from the form
 */
function getCurrentDimensions() {
    const widthInput = document.getElementById('input-width');
    const heightInput = document.getElementById('input-height');
    const widthUnit = document.querySelector('[data-current-unit]')?.dataset?.currentUnit || 'in';

    return {
        width: widthInput ? parseFloat(widthInput.value) || 0 : 0,
        height: heightInput ? parseFloat(heightInput.value) || 0 : 0,
        unit: widthUnit
    };
}

/**
 * Update price display in the UI
 */
function updatePriceDisplay(totalPrice, breakdown = null) {
    const priceElement = document.getElementById('total-price');
    if (priceElement) {
        priceElement.textContent = formatPrice(totalPrice);
    }

    if (breakdown) {
        updatePriceBreakdown(breakdown);
    }
}

/**
 * Update price breakdown display
 */
function updatePriceBreakdown(breakdown) {
    // Update individual breakdown items
    Object.keys(breakdown).forEach(key => {
        const element = document.getElementById(`cost-${key}`);
        if (element) {
            element.textContent = formatPrice(breakdown[key]);
        }
    });
}

/**
 * Format price for display
 */
function formatPrice(price) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(price);
}

/**
 * Show save status indicator
 */
function showSaveIndicator(status) {
    const indicator = document.getElementById('ajax-status-indicator');
    const textElement = document.getElementById('ajax-status-text');

    if (!indicator || !textElement) {
        return;
    }

    // Clear any existing timeout
    if (window.saveIndicatorTimeout) {
        clearTimeout(window.saveIndicatorTimeout);
    }

    switch (status) {
        case 'saving':
            textElement.textContent = 'Saving...';
            indicator.style.backgroundColor = '#fff3cd';
            indicator.style.color = '#856404';
            indicator.style.border = '1px solid #ffc107';
            indicator.style.display = 'block';
            break;
        case 'saved':
            textElement.textContent = '✓ Saved';
            indicator.style.backgroundColor = '#d4edda';
            indicator.style.color = '#155724';
            indicator.style.border = '1px solid #c3e6cb';
            indicator.style.display = 'block';
            break;
        case 'error':
            textElement.textContent = '✗ Save failed';
            indicator.style.backgroundColor = '#f8d7da';
            indicator.style.color = '#721c24';
            indicator.style.border = '1px solid #f5c6cb';
            indicator.style.display = 'block';
            break;
        default:
            indicator.style.display = 'none';
            return;
    }

    // Auto-hide after 3 seconds
    window.saveIndicatorTimeout = setTimeout(() => {
        indicator.style.display = 'none';
    }, 3000);
}

/**
 * Make AJAX request with retry logic
 */
async function makeAjaxRequest(method, endpoint, data = null) {
    const url = AJAX_CONFIG.baseUrl + endpoint;
    let attempts = 0;

    while (attempts < AJAX_CONFIG.retryAttempts) {
        try {
            const config = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };

            if (data && (method === 'POST' || method === 'PUT')) {
                config.body = JSON.stringify(data);
            } else if (data && method === 'GET') {
                const params = new URLSearchParams(data);
                config.url = `${url}?${params.toString()}`;
            }

            const response = await fetch(url, config);
            const result = await response.json();

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${result.message || 'Request failed'}`);
            }

            return result;

        } catch (error) {
            attempts++;
            if (attempts >= AJAX_CONFIG.retryAttempts) {
                throw error;
            }

            console.warn(`AJAX attempt ${attempts} failed, retrying in ${AJAX_CONFIG.retryDelay}ms:`, error);
            await new Promise(resolve => setTimeout(resolve, AJAX_CONFIG.retryDelay));
        }
    }
}

/**
 * Force save current customizations
 */
function forceSaveCustomizations() {
    showSaveIndicator('saving');
    saveCustomizationSelections();
}

/**
 * Export functions to window
 */
window.customizationAjax = {
    init: initCustomizationAjax,
    save: forceSaveCustomizations,
    load: loadSavedCustomization,
    updatePrice: updatePriceRealtime
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Wait for other scripts to load first
    setTimeout(initCustomizationAjax, 500);
});