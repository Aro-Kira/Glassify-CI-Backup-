/**
 * ============================================================================
 * KONVA 2D PREVIEW INTEGRATION - Style Enhancement Module
 * ============================================================================
 * 
 * Enhances the 2D Konva preview with consolidated glass/frame styles,
 * visual indicators, and category information.
 * 
 * Integrates:
 * - Consolidated glass styles from 2d_customization.js
 * - Consolidated frame styles from 2d_customization.js
 * - Visual indicators and categories
 * - Real-time style information display
 * - Color swatches and metadata
 * 
 * USAGE:
 * 1. Include this file after 2d_customization.js and comprehensive_2d_renderer.js
 * 2. Call Konva2DPreviewEnhancer.init() on page load
 * 3. Call Konva2DPreviewEnhancer.updatePreview() when selections change
 * 
 * @author      Glassify Development Team
 * @version     1.0.0
 * @created     February 2026
 * ============================================================================
 */

window.Konva2DPreviewEnhancer = (function() {
    
    const MODULE_NAME = '[Konva 2D Preview Enhancer]';
    let initialized = false;
    let previewUpdateTimer = null;
    
    // ========================================================================
    // Core Methods
    // ========================================================================
    
    /**
     * Get enhanced style object for a glass type
     * Includes category, indicator, description, and visual properties
     * 
     * @param {string} glassType - Glass type name
     * @returns {Object} Enhanced style object with all metadata
     */
    function getEnhancedGlassStyle(glassType) {
        if (!glassType || !window.glassStyles) return null;
        
        const normalized = glassType.toLowerCase();
        const style = window.glassStyles[normalized];
        
        if (!style) return null;
        
        return {
            name: glassType,
            ...style,
            // Ensure all properties are present
            fill: style.fill || '#E0F2F1',
            opacity: style.opacity !== undefined ? style.opacity : 0.9,
            category: style.category || 'basic',
            indicator: style.indicator || '◇',
            description: style.description || 'Glass type'
        };
    }
    
    /**
     * Get enhanced style object for a frame type
     * Includes category, indicator, description, and visual properties
     * 
     * @param {string} frameType - Frame type name
     * @returns {Object} Enhanced style object with all metadata
     */
    function getEnhancedFrameStyle(frameType) {
        if (!frameType || !window.frameStyles) return null;
        
        const normalized = frameType.toLowerCase();
        const style = window.frameStyles[normalized];
        
        if (!style) return null;
        
        return {
            name: frameType,
            ...style,
            // Ensure all properties are present
            color: style.color || '#FFFFFF',
            width: style.width || 2,
            category: style.category || 'metal',
            indicator: style.indicator || '■',
            description: style.description || 'Frame type'
        };
    }
    
    /**
     * Create an enhanced label string with indicator and category
     * 
     * @param {string} typeName - Glass or frame type name
     * @param {Object} styleObj - Enhanced style object
     * @returns {string} Formatted label with indicator
     */
    function createEnhancedLabel(typeName, styleObj) {
        if (!styleObj) return typeName;
        
        const indicator = styleObj.indicator || '';
        const category = styleObj.category ? styleObj.category.toUpperCase() : 'UNKNOWN';
        
        return `${indicator} [${category}] ${typeName}`;
    }
    
    /**
     * Create a visual metadata overlay for the Konva preview
     * Displays selected glass/frame info with colors and indicators
     * 
     * @param {Konva.Layer} layer - Konva layer to add overlay to
     * @param {Object} customizationValues - Current customization selections
     * @param {number} x - X position for overlay
     * @param {number} y - Y position for overlay
     */
    function addMetadataOverlay(layer, customizationValues, x = 10, y = 10) {
        if (!layer || !customizationValues) return;
        
        const glassType = customizationValues.glassType || customizationValues.glass || 'Clear';
        const frameType = customizationValues.frameColor || customizationValues.frame || 'White';
        
        const glassStyle = getEnhancedGlassStyle(glassType);
        const frameStyle = getEnhancedFrameStyle(frameType);
        
        let overlayText = '';
        
        // Glass info
        if (glassStyle) {
            overlayText += `${glassStyle.indicator} Glass: ${glassStyle.category}\n`;
        }
        
        // Frame info
        if (frameStyle) {
            overlayText += `${frameStyle.indicator} Frame: ${frameStyle.category}`;
        }
        
        if (!overlayText) return;
        
        const metadata = new Konva.Text({
            x: x,
            y: y,
            text: overlayText,
            fontSize: 11,
            fontFamily: 'Courier New, monospace',
            fill: '#555',
            backgroundColor: 'rgba(255, 255, 255, 0.85)',
            padding: 6,
            cornerRadius: 4,
            align: 'left',
            lineHeight: 1.4
        });
        
        layer.add(metadata);
        return metadata;
    }
    
    /**
     * Add visual legend showing all glass and frame categories
     * Useful for reference during customization
     * 
     * @param {HTMLElement} container - Container to add legend to
     * @returns {HTMLElement} Legend element
     */
    function createCategoryLegend(container = null) {
        if (!window.glassStyles || !window.frameStyles) {
            console.warn(MODULE_NAME, 'Styles not available for legend');
            return null;
        }
        
        const legend = document.createElement('div');
        legend.id = 'konva-style-legend';
        legend.className = 'konva-style-legend';
        legend.style.cssText = `
            padding: 12px;
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: Montserrat, sans-serif;
            font-size: 12px;
            max-width: 320px;
            margin: 10px 0;
        `;
        
        // Glass categories
        const glassCategories = {};
        Object.entries(window.glassStyles).forEach(([name, style]) => {
            const cat = style.category || 'other';
            if (!glassCategories[cat]) {
                glassCategories[cat] = [];
            }
            glassCategories[cat].push({
                indicator: style.indicator || '◇',
                name: name.charAt(0).toUpperCase() + name.slice(1)
            });
        });
        
        let html = '<div style="margin-bottom: 10px;"><strong>Glass Types</strong>';
        Object.entries(glassCategories).forEach(([cat, items]) => {
            html += `<div style="margin-left: 10px; margin-top: 5px;">
                <small style="color: #666;"><strong>${cat}</strong></small><br>
                ${items.map(item => `<span style="margin-right: 8px;">${item.indicator} ${item.name}</span>`).join('')}
            </div>`;
        });
        html += '</div>';
        
        // Frame categories
        const frameCategories = {};
        Object.entries(window.frameStyles).forEach(([name, style]) => {
            const cat = style.category || 'other';
            if (!frameCategories[cat]) {
                frameCategories[cat] = [];
            }
            frameCategories[cat].push({
                indicator: style.indicator || '■',
                name: name.charAt(0).toUpperCase() + name.slice(1)
            });
        });
        
        html += '<div><strong>Frame Types</strong>';
        Object.entries(frameCategories).forEach(([cat, items]) => {
            html += `<div style="margin-left: 10px; margin-top: 5px;">
                <small style="color: #666;"><strong>${cat}</strong></small><br>
                ${items.map(item => `<span style="margin-right: 8px;">${item.indicator} ${item.name}</span>`).join('')}
            </div>`;
        });
        html += '</div>';
        
        legend.innerHTML = html;
        
        if (container) {
            container.appendChild(legend);
        }
        
        return legend;
    }
    
    /**
     * Update 2D preview with enhanced style information
     * Called when customization selections change
     */
    function updatePreview() {
        // Debounce updates
        if (previewUpdateTimer) {
            clearTimeout(previewUpdateTimer);
        }
        
        previewUpdateTimer = setTimeout(() => {
            const customizationValues = window.selectedCustomizationValues || {};
            
            // Update style info if available
            if (typeof updateStyleInfoDisplay === 'function') {
                updateStyleInfoDisplay();
            }
            
            // Trigger re-render of Konva if available
            if (typeof renderCustomState === 'function') {
                renderCustomState();
            }
            
            console.log(MODULE_NAME, 'Preview updated with enhanced styles', {
                glass: customizationValues.glassType,
                frame: customizationValues.frameColor
            });
        }, 300); // 300ms debounce
    }
    
    /**
     * Watch for changes to customization values and update preview
     * Sets up a Proxy or polling to detect changes
     */
    function setupChangeWatcher() {
        // If selectedCustomizationValues is already a Proxy, we can hook into it
        // Otherwise, we'll use polling
        
        // Try to setup a direct change listener on option cards
        document.addEventListener('click', function(e) {
            if (e.target.closest('.option-card')) {
                // A style option was clicked
                setTimeout(updatePreview, 100);
            }
        });
        
        // Also watch for select element changes
        document.addEventListener('change', function(e) {
            if (e.target.closest('[data-fieldId*="glass"]') || 
                e.target.closest('[data-fieldId*="frame"]')) {
                updatePreview();
            }
        });
        
        console.log(MODULE_NAME, 'Change watcher initialized');
    }
    
    /**
     * Initialize the enhancement module
     * Sets up observers, style sheets, and event handlers
     */
    function init() {
        if (initialized) {
            console.warn(MODULE_NAME, 'Already initialized');
            return;
        }
        
        // Check for required dependencies
        if (!window.glassStyles || !window.frameStyles) {
            console.warn(MODULE_NAME, 'Glass/frame styles not found. Waiting for dependencies...');
            setTimeout(init, 1000);
            return;
        }
        
        // Add stylesheet for enhancements
        addStylesheet();
        
        // Setup change watchers
        setupChangeWatcher();
        
        // Initialize option card enhancements if available
        if (typeof initOptionCardEnhancements === 'function') {
            initOptionCardEnhancements();
        }
        
        // Initialize Konva style renderer integration if available
        if (typeof initKonvaStyleRendererIntegration === 'function') {
            initKonvaStyleRendererIntegration();
        }
        
        initialized = true;
        console.log(MODULE_NAME, 'Initialization complete. Glass/Frame styles enhanced in 2D preview.');
        console.log(MODULE_NAME, 'Glass categories:', Object.values(window.glassStyles).reduce((acc, s) => {
            const cat = s.category || 'other';
            acc[cat] = (acc[cat] || 0) + 1;
            return acc;
        }, {}));
        console.log(MODULE_NAME, 'Frame categories:', Object.values(window.frameStyles).reduce((acc, s) => {
            const cat = s.category || 'other';
            acc[cat] = (acc[cat] || 0) + 1;
            return acc;
        }, {}));
    }
    
    /**
     * Add stylesheet for style enhancements
     */
    function addStylesheet() {
        if (document.getElementById('konva-2d-enhancer-styles')) {
            return; // Already added
        }
        
        const style = document.createElement('style');
        style.id = 'konva-2d-enhancer-styles';
        style.textContent = `
            /* Konva 2D Preview Enhancements */
            
            .konva-style-legend {
                line-height: 1.6;
            }
            
            .konva-style-legend strong {
                color: #333;
            }
            
            .konva-style-legend small {
                font-size: 11px;
            }
            
            /* Enhanced option cards */
            .option-card.enhanced {
                position: relative;
                padding-bottom: 8px;
            }
            
            .option-card .style-indicator-badge {
                position: absolute;
                top: 4px;
                right: 4px;
                font-size: 16px;
                width: 26px;
                height: 26px;
                line-height: 26px;
                text-align: center;
                background: white;
                border: 2px solid #999;
                border-radius: 50%;
                opacity: 0.9;
                z-index: 10;
                cursor: help;
            }
            
            .option-card:hover .style-indicator-badge {
                opacity: 1;
                border-color: #0066cc;
                transform: scale(1.15);
                box-shadow: 0 2px 6px rgba(0,0,0,0.2);
            }
            
            .option-card .style-category-badge {
                position: absolute;
                bottom: 2px;
                right: 4px;
                font-size: 9px;
                background: rgba(0,0,0,0.05);
                padding: 2px 4px;
                border-radius: 2px;
                color: #666;
                text-transform: capitalize;
                font-weight: 600;
            }
            
            .option-card:hover .style-category-badge {
                background: rgba(0,102,204,0.1);
                color: #0066cc;
            }
            
            .option-card .glass-color-swatch,
            .option-card .frame-color-swatch {
                position: absolute;
                bottom: 4px;
                left: 4px;
                width: 20px;
                height: 20px;
                border: 2px solid #333;
                border-radius: 2px;
                opacity: 0.85;
                cursor: help;
            }
            
            .option-card:hover .glass-color-swatch,
            .option-card:hover .frame-color-swatch {
                opacity: 1;
                box-shadow: 0 2px 4px rgba(0,0,0,0.3);
            }
            
            /* Style info panel */
            #style-info-panel-floating {
                font-family: Montserrat, sans-serif;
            }
            
            #style-info-panel-floating strong {
                color: #0066cc;
                font-weight: 600;
            }
            
            #style-info-panel-floating small {
                display: block;
                margin-top: 2px;
            }
            
            #style-info-panel-floating code {
                background: #f0f0f0;
                padding: 2px 4px;
                border-radius: 2px;
                font-family: 'Courier New', monospace;
                font-size: 10px;
            }
        `;
        
        document.head.appendChild(style);
        console.log(MODULE_NAME, 'Stylesheet added');
    }
    
    // Public API
    return {
        init: init,
        getEnhancedGlassStyle: getEnhancedGlassStyle,
        getEnhancedFrameStyle: getEnhancedFrameStyle,
        createLabel: createEnhancedLabel,
        addMetadataOverlay: addMetadataOverlay,
        createLegend: createCategoryLegend,
        updatePreview: updatePreview,
        setupWatcher: setupChangeWatcher
    };
})();

// Auto-initialize when page is ready
if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                Konva2DPreviewEnhancer.init();
            }, 500);
        });
    } else {
        setTimeout(() => {
            Konva2DPreviewEnhancer.init();
        }, 500);
    }
}

console.log('[Konva 2D Preview Enhancer] Module loaded and ready for initialization');
