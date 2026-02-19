/**
 * ============================================================================
 * KONVA STYLE RENDERER - 2D Preview Integration
 * ============================================================================
 * 
 * Integrates consolidated glass and frame styles with visual indicators
 * into the 2D Preview Konva rendering system.
 * 
 * Features:
 * - Display category indicators in option cards
 * - Show color swatches for glass/frame selection
 * - Render visual labels with category information
 * - Enhanced preview with style metadata
 * - Color hex reference in previews
 * 
 * REQUIRES:
 * - 2d_customization.js (for glassStyles, frameStyles, helper functions)
 * - Konva.js (for rendering)
 * - dynamic_customization.js (for option card rendering)
 * 
 * @author      Glassify Development Team
 * @version     1.0.0
 * @created     February 2026
 * ============================================================================
 */

// ============================================================================
// ENHANCEMENT: Add Indicators to Option Cards
// ============================================================================

/**
 * Enhanced option card rendering with category indicators and color swatches
 * This function wraps the existing renderTagsField to add visual enhancements
 * 
 * @param {HTMLElement} optionCard - The option card element
 * @param {string} fieldId - Field ID (glassType, frameColor, etc.)
 * @param {string} optionValue - The option value (glass/frame type)
 */
function enhanceOptionCardWithIndicator(optionCard, fieldId, optionValue) {
    if (!optionCard) return;
    
    let styleObject = null;
    let glassOrFrame = '';
    
    // Determine which style object to use
    if (fieldId === 'glassType' && window.glassStyles) {
        styleObject = window.glassStyles[optionValue.toLowerCase()];
        glassOrFrame = 'glass';
    } else if ((fieldId === 'frameColor' || fieldId === 'frameColormaterial') && window.frameStyles) {
        styleObject = window.frameStyles[optionValue.toLowerCase()];
        glassOrFrame = 'frame';
    }
    
    if (!styleObject) return;
    
    // Add indicator badge
    if (styleObject.indicator) {
        const indicatorBadge = document.createElement('span');
        indicatorBadge.className = 'style-indicator-badge';
        indicatorBadge.textContent = styleObject.indicator;
        indicatorBadge.title = `${styleObject.category || 'other'} - ${styleObject.description || ''}`;
        indicatorBadge.style.cssText = `
            display: inline-block;
            position: absolute;
            top: 5px;
            right: 5px;
            font-size: 18px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid #666;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            cursor: help;
        `;
        optionCard.style.position = 'relative';
        optionCard.appendChild(indicatorBadge);
    }
    
    // Add category badge
    if (styleObject.category) {
        const categoryBadge = document.createElement('span');
        categoryBadge.className = 'style-category-badge';
        categoryBadge.textContent = styleObject.category;
        categoryBadge.style.cssText = `
            display: block;
            font-size: 11px;
            color: #666;
            margin-top: 4px;
            text-transform: capitalize;
            font-weight: 600;
            letter-spacing: 0.5px;
        `;
        optionCard.appendChild(categoryBadge);
    }
    
    // Add color swatch for glass types
    if (glassOrFrame === 'glass' && styleObject.fill) {
        const colorSwatch = document.createElement('div');
        colorSwatch.className = 'glass-color-swatch';
        colorSwatch.style.cssText = `
            position: absolute;
            bottom: 5px;
            left: 5px;
            width: 24px;
            height: 24px;
            background: ${styleObject.fill};
            opacity: ${styleObject.opacity || 1};
            border: 2px solid #333;
            border-radius: 3px;
            cursor: help;
        `;
        colorSwatch.title = `Color: ${styleObject.fill}`;
        optionCard.style.position = 'relative';
        optionCard.appendChild(colorSwatch);
    }
    
    // Add color swatch for frame types
    if (glassOrFrame === 'frame' && styleObject.color) {
        const colorSwatch = document.createElement('div');
        colorSwatch.className = 'frame-color-swatch';
        colorSwatch.style.cssText = `
            position: absolute;
            bottom: 5px;
            left: 5px;
            width: 24px;
            height: 24px;
            background: ${styleObject.color};
            border: 2px solid #333;
            border-radius: 3px;
            cursor: help;
        `;
        colorSwatch.title = `Color: ${styleObject.color}`;
        optionCard.style.position = 'relative';
        optionCard.appendChild(colorSwatch);
    }
}

/**
 * Initialize option card enhancements on page load
 * Scans all option cards and applies indicators/swatches
 */
function initOptionCardEnhancements() {
    const optionCards = document.querySelectorAll('.option-card[data-value][data-fieldId]');
    
    optionCards.forEach(card => {
        const fieldId = card.getAttribute('data-fieldId');
        const optionValue = card.getAttribute('data-value');
        
        if (fieldId && optionValue) {
            enhanceOptionCardWithIndicator(card, fieldId, optionValue);
        }
    });
    
    console.log('[Konva Style Renderer] Option card enhancements applied to', optionCards.length, 'cards');
}

// ============================================================================
// ENHANCEMENT: Konva Preview Label with Style Information
// ============================================================================

/**
 * Add enhanced label to Konva layer with style category and indicator
 * Displays glass type, frame type, and their categories with indicators
 * 
 * @param {Konva.Layer} layer - Konva layer to add label to
 * @param {number} x - X position
 * @param {number} y - Y position
 * @param {string} glassType - Selected glass type
 * @param {string} frameType - Selected frame type
 * @param {number} fontSize - Font size for label
 */
function addEnhancedStyleLabel(layer, x, y, glassType, frameType, fontSize = 12) {
    if (!layer) return;
    
    const glassStyle = window.glassStyles ? window.glassStyles[glassType?.toLowerCase()] : null;
    const frameStyle = window.frameStyles ? window.frameStyles[frameType?.toLowerCase()] : null;
    
    // Build label text with indicators
    let labelText = '';
    
    if (glassStyle) {
        const indicator = glassStyle.indicator || '';
        const category = glassStyle.category || 'unknown';
        labelText += `${indicator} ${category.toUpperCase()}: ${glassType}`;
    }
    
    if (frameStyle) {
        if (labelText) labelText += ' | ';
        const indicator = frameStyle.indicator || '';
        const category = frameStyle.category || 'unknown';
        labelText += `${indicator} ${category.toUpperCase()}: ${frameType}`;
    }
    
    if (!labelText) return; // No valid style info
    
    const label = new Konva.Text({
        x: x,
        y: y,
        text: labelText,
        fontSize: fontSize,
        fontFamily: 'Montserrat, sans-serif',
        fill: '#333',
        align: 'center',
        width: 300,
        height: 'auto'
    });
    
    layer.add(label);
    return label;
}

/**
 * Create a detailed style info panel (for future UI enhancement)
 * Returns HTML for a style information card
 * 
 * @param {string} glassType - Glass type name
 * @param {string} frameType - Frame type name
 * @returns {string} HTML for style info panel
 */
function generateStyleInfoPanel(glassType, frameType) {
    const glassStyle = window.glassStyles ? window.glassStyles[glassType?.toLowerCase()] : null;
    const frameStyle = window.frameStyles ? window.frameStyles[frameType?.toLowerCase()] : null;
    
    let html = '<div class="style-info-panel" style="padding: 12px; background: #f9f9f9; border-radius: 6px; font-family: Montserrat, sans-serif; font-size: 12px;">';
    
    // Glass style info
    if (glassStyle) {
        html += `
        <div style="margin-bottom: 10px;">
            <strong>Glass Type</strong><br>
            <span style="font-size: 18px; margin-right: 8px;">${glassStyle.indicator || ''}</span>
            <strong>${glassType}</strong><br>
            <small style="color: #666;">Category: ${glassStyle.category || 'unknown'}</small><br>
            <small style="color: #666;">Description: ${glassStyle.description || 'N/A'}</small><br>
            ${glassStyle.fill ? `<small style="color: #999;">Color: <code>${glassStyle.fill}</code> (Opacity: ${glassStyle.opacity || 1})</small>` : ''}
        </div>`;
    }
    
    // Frame style info
    if (frameStyle) {
        html += `
        <div>
            <strong>Frame Type</strong><br>
            <span style="font-size: 18px; margin-right: 8px;">${frameStyle.indicator || ''}</span>
            <strong>${frameType}</strong><br>
            <small style="color: #666;">Category: ${frameStyle.category || 'unknown'}</small><br>
            <small style="color: #666;">Description: ${frameStyle.description || 'N/A'}</small><br>
            ${frameStyle.color ? `<small style="color: #999;">Color: <code>${frameStyle.color}</code></small>` : ''}
        </div>`;
    }
    
    html += '</div>';
    return html;
}

// ============================================================================
// ENHANCEMENT: Style Information Tooltip/Sidebar
// ============================================================================

/**
 * Create a floating info panel showing selected glass/frame style details
 * Displays in the preview area with category, indicator, and color info
 * 
 * @param {string} glassType - Selected glass type
 * @param {string} frameType - Selected frame type
 * @param {HTMLElement} container - Container to append info panel
 */
function showStyleInfoPanel(glassType, frameType, container = null) {
    if (!container) {
        container = document.getElementById('konva-container') || 
                   document.querySelector('.preview-diagram') || 
                   document.body;
    }
    
    // Remove existing panel
    const existing = document.getElementById('style-info-panel-floating');
    if (existing) existing.remove();
    
    // Create new panel
    const panel = document.createElement('div');
    panel.id = 'style-info-panel-floating';
    panel.innerHTML = generateStyleInfoPanel(glassType, frameType);
    panel.style.cssText = `
        position: absolute;
        top: 10px;
        right: 10px;
        background: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        max-width: 280px;
        z-index: 100;
        animation: slideInPanel 0.3s ease;
    `;
    
    if (container.style.position === 'static') {
        container.style.position = 'relative';
    }
    
    container.appendChild(panel);
}

/**
 * Add CSS animations for style info panel
 */
function initStyleInfoPanelStyles() {
    if (document.getElementById('style-info-panel-styles')) return;
    
    const style = document.createElement('style');
    style.id = 'style-info-panel-styles';
    style.textContent = `
        @keyframes slideInPanel {
            from {
                opacity: 0;
                transform: translateX(10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .style-indicator-badge {
            transition: all 0.2s ease !important;
        }
        
        .option-card:hover .style-indicator-badge {
            transform: scale(1.2);
            background: #e8f4f8;
        }
        
        .style-category-badge {
            transition: all 0.2s ease;
        }
        
        .option-card:hover .style-category-badge {
            color: #0066cc;
            font-weight: 700;
        }
    `;
    document.head.appendChild(style);
}

// ============================================================================
// INTEGRATION: Hook into Dynamic Customization
// ============================================================================

/**
 * Override or wrap the renderTagsField function to add enhancements
 * This integrates with dynamic_customization.js
 */
function initKonvaStyleRendererIntegration() {
    // Add styles
    initStyleInfoPanelStyles();
    
    // Watch for changes to option cards and apply enhancements
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        // Check if this is an option card
                        if (node.classList && node.classList.contains('option-card')) {
                            const fieldId = node.getAttribute('data-fieldId');
                            const optionValue = node.getAttribute('data-value');
                            if (fieldId && optionValue) {
                                setTimeout(() => {
                                    enhanceOptionCardWithIndicator(node, fieldId, optionValue);
                                }, 50);
                            }
                        }
                        
                        // Check for newly added containers
                        const newCards = node.querySelectorAll?.('.option-card[data-value][data-fieldId]');
                        if (newCards && newCards.length > 0) {
                            newCards.forEach(card => {
                                const fieldId = card.getAttribute('data-fieldId');
                                const optionValue = card.getAttribute('data-value');
                                if (fieldId && optionValue) {
                                    enhanceOptionCardWithIndicator(card, fieldId, optionValue);
                                }
                            });
                        }
                    }
                });
            }
        });
    });
    
    // Start observing
    const targetNode = document.body;
    const config = {
        childList: true,
        subtree: true,
        attributes: false,
        characterData: false
    };
    
    observer.observe(targetNode, config);
    console.log('[Konva Style Renderer] Integration initialized and observing for changes');
    
    return observer;
}

/**
 * Update style info when selection changes
 * Call this from customization selection handlers
 */
function updateStyleInfoDisplay() {
    const customizationValues = window.selectedCustomizationValues || {};
    const glassType = customizationValues.glassType || 'Clear';
    const frameType = customizationValues.frameColor || 'White';
    
    // Update floating panel if it exists
    const container = document.getElementById('konva-container') || 
                     document.querySelector('.preview-diagram');
    if (container) {
        showStyleInfoPanel(glassType, frameType, container);
    }
}

// ============================================================================
// EXPORT FOR EXTERNAL USE
// ============================================================================

window.KonvaStyleRenderer = {
    enhanceOptionCard: enhanceOptionCardWithIndicator,
    initEnhancements: initOptionCardEnhancements,
    addStyleLabel: addEnhancedStyleLabel,
    generateInfoPanel: generateStyleInfoPanel,
    showInfoPanel: showStyleInfoPanel,
    updateDisplay: updateStyleInfoDisplay,
    init: initKonvaStyleRendererIntegration
};

// Auto-initialize when DOM is ready
if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                initOptionCardEnhancements();
                initKonvaStyleRendererIntegration();
                updateStyleInfoDisplay();
            }, 500);
        });
    } else {
        // DOM already loaded
        setTimeout(() => {
            initOptionCardEnhancements();
            initKonvaStyleRendererIntegration();
            updateStyleInfoDisplay();
        }, 500);
    }
}

console.log('[Konva Style Renderer] Module loaded and ready');
