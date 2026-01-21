/**
 * Integration wrapper for Comprehensive 2D Renderer
 * 
 * This file integrates the comprehensive 2D renderer with the existing
 * 2d_customization.js system, allowing automatic routing based on
 * product type and customization options from default-customization-fields.json
 */

// Ensure comprehensive renderer is loaded
// Note: This check happens at parse time, but the renderer may not be available until scripts finish loading
// The actual check happens when functions are called, so this is just a warning
if (typeof Comprehensive2DRenderer === 'undefined') {
    console.warn('[2D Integration] Comprehensive2DRenderer not found yet. It will be checked when needed.');
}

/**
 * Enhanced render function that uses comprehensive renderer when appropriate
 * This can be called instead of or alongside the existing renderWindow function
 * 
 * @param {Object} options - Rendering options
 * @param {Object} options.dimensions - { width, height, unit }
 * @param {Object} options.productData - Product information
 * @param {Object} options.customizationValues - Customization values from form
 * @param {Object} options.layer - Konva layer (optional, uses global layer if not provided)
 */
function renderWithComprehensiveRenderer(options) {
    const {
        dimensions,
        productData = {},
        customizationValues = {},
        layer = null
    } = options;

    // Get layer from global scope if not provided
    let targetLayer = layer;
    
    if (!targetLayer) {
        // Try to get from global scope
        if (typeof window !== 'undefined' && window.layer) {
            targetLayer = window.layer;
        } else {
            console.error('[2D Integration] No Konva layer available');
            return;
        }
    }

    // Prepare product data structure
    const productInfo = {
        productType: productData.productType || productData.type || productData.name || '',
        category: productData.category || '',
        customizationValues: customizationValues
    };

    // Use comprehensive renderer
    if (typeof Comprehensive2DRenderer !== 'undefined' && Comprehensive2DRenderer.renderProduct2D) {
        Comprehensive2DRenderer.renderProduct2D(productInfo, dimensions, targetLayer);
    } else {
        console.warn('[2D Integration] Comprehensive2DRenderer.renderProduct2D not available yet. Will retry when renderer loads.');
        // Retry after a short delay in case scripts are still loading
        setTimeout(() => {
            if (typeof Comprehensive2DRenderer !== 'undefined' && Comprehensive2DRenderer.renderProduct2D) {
                Comprehensive2DRenderer.renderProduct2D(productInfo, dimensions, targetLayer);
            }
        }, 100);
    }
}

/**
 * Auto-detect product type from customization fields and render
 * This function analyzes the customization values to determine the product type
 * 
 * @param {Object} customizationValues - Customization values from form
 * @param {Object} dimensions - { width, height, unit }
 * @param {Object} layer - Konva layer (optional)
 */
function autoRenderFromCustomization(customizationValues, dimensions, layer = null) {
    // Determine product type from customization values
    let category = '';
    let productType = '';

    // Check for Windows-specific fields
    if (customizationValues.numberOfPanels || customizationValues.trackSystem || 
        customizationValues.panelConfiguration || customizationValues.transomType ||
        customizationValues.panelRows || customizationValues.panelColumns ||
        (customizationValues.operation && customizationValues.operation.includes('Awning'))) {
        category = 'Windows';
        if (customizationValues.numberOfPanels) {
            productType = 'Sliding';
        } else if (customizationValues.operation && customizationValues.operation.includes('Awning')) {
            productType = 'Awning';
        } else if (customizationValues.panelRows || customizationValues.panelColumns) {
            productType = 'Awning';
        } else if (customizationValues.operation && customizationValues.operation.includes('Casement')) {
            productType = 'Casement';
        } else {
            productType = 'Fixed Glass';
        }
    }
    // Check for Doors-specific fields
    else if (customizationValues.panelCount || customizationValues.doorType || 
             customizationValues.doorSwing || customizationValues.series) {
        category = 'Doors';
        if (customizationValues.panelCount || (customizationValues.operation && customizationValues.operation.includes('Sliding'))) {
            productType = 'Sliding';
        } else if (customizationValues.doorType && customizationValues.doorType.includes('Frameless')) {
            productType = 'Frameless';
        } else if (customizationValues.series && (customizationValues.series.includes('45') || customizationValues.series.includes('75'))) {
            productType = 'Bi-fold Door';
        } else if (customizationValues.series && customizationValues.series.includes('Patch')) {
            productType = 'Patch Fitting';
        } else {
            productType = 'Swing Door';
        }
    }
    // Check for Partitions-specific fields
    else if (customizationValues.layout || customizationValues.mountingHardware || 
             customizationValues.series && customizationValues.series.includes('Shower')) {
        category = 'Partitions';
        if (customizationValues.series && customizationValues.series.includes('Shower')) {
            productType = 'Shower Enclosure';
        } else if (customizationValues.mountingHardware) {
            productType = 'Frameless Glass';
        } else {
            productType = 'Fixed Glass';
        }
    }
    // Check for Specialty-specific fields
    else if (customizationValues.shape || customizationValues.frameType || 
             customizationValues.lighting || customizationValues.mountingMethod) {
        category = 'Specialty';
        if (customizationValues.frameType || customizationValues.lighting) {
            productType = 'Mirrors';
        } else if (customizationValues.edgeFinish && !customizationValues.cornerRadius) {
            productType = 'Top Glass';
        } else {
            productType = 'Glass Board';
        }
    }
    // Check for Commercial-specific fields
    else if (customizationValues.handrailType || customizationValues.mountingSystem || 
             customizationValues.safetyGlassType) {
        category = 'Commercial';
        if (customizationValues.handrailType && customizationValues.mountingSystem) {
            if (customizationValues.series && customizationValues.series.includes('Balcony')) {
                productType = 'Glass Balcony';
            } else if (customizationValues.series && customizationValues.series.includes('Stair')) {
                productType = 'Stair Railings';
            } else {
                productType = 'Storefront';
            }
        } else {
            productType = 'Storefront';
        }
    }

    // Render with detected type
    const productData = {
        category: category,
        productType: productType,
        customizationValues: customizationValues
    };

    renderWithComprehensiveRenderer({
        dimensions: dimensions,
        productData: productData,
        customizationValues: customizationValues,
        layer: layer
    });
}

/**
 * Enhanced wrapper that integrates with existing renderWindow function
 * This can replace or enhance calls to renderWindow
 */
function renderWindowEnhanced(widthIn, heightIn, unit, shape, glassType, thickness, edgeWork, frameType, 
                              originalWidth, originalHeight, heightUnit, cornerRadiusIn = 0) {
    
    // Get customization values from global scope if available
    const customizationValues = window.selectedCustomizationValues || {};
    
    // Get product data from global scope if available
    const productData = window.selectedProduct || {};
    
    // Determine if we should use comprehensive renderer
    const shouldUseComprehensive = 
        customizationValues.numberOfPanels ||
        customizationValues.panelCount ||
        customizationValues.panelConfiguration ||
        customizationValues.trackSystem ||
        customizationValues.doorType ||
        customizationValues.layout ||
        customizationValues.handrailType ||
        (productData.category && (
            productData.category.includes('Windows') ||
            productData.category.includes('Doors') ||
            productData.category.includes('Partitions') ||
            productData.category.includes('Specialty') ||
            productData.category.includes('Commercial')
        ));

    if (shouldUseComprehensive && typeof Comprehensive2DRenderer !== 'undefined') {
        // Use comprehensive renderer
        const dimensions = {
            width: widthIn,
            height: heightIn,
            unit: unit
        };

        const productInfo = {
            productType: productData.type || productData.name || '',
            category: productData.category || '',
            customizationValues: {
                ...customizationValues,
                glassType: glassType,
                frameColor: frameType,
                thickness: thickness,
                edgeWork: edgeWork,
                shape: shape,
                cornerRadius: cornerRadiusIn
            }
        };

        const targetLayer = typeof layer !== 'undefined' ? layer : null;
        if (typeof Comprehensive2DRenderer !== 'undefined' && Comprehensive2DRenderer.renderProduct2D) {
            Comprehensive2DRenderer.renderProduct2D(productInfo, dimensions, targetLayer);
        } else {
            console.warn('[2D Integration] Comprehensive2DRenderer not available, falling back to renderWindow');
            // Fall back to existing renderWindow function
            if (typeof renderWindow === 'function') {
                renderWindow(widthIn, heightIn, unit, shape, glassType, thickness, edgeWork, frameType,
                            originalWidth, originalHeight, heightUnit, cornerRadiusIn);
            } else {
                console.warn('[2D Integration] renderWindow function not available');
            }
        }
    } else {
        // Fall back to existing renderWindow function
        if (typeof renderWindow === 'function') {
            renderWindow(widthIn, heightIn, unit, shape, glassType, thickness, edgeWork, frameType,
                        originalWidth, originalHeight, heightUnit, cornerRadiusIn);
        } else {
            console.warn('[2D Integration] renderWindow function not available');
        }
    }
}

/**
 * Initialize comprehensive renderer integration
 * Call this after both 2d_customization.js and comprehensive_2d_renderer.js are loaded
 */
function initComprehensiveRendererIntegration() {
    // Make functions available globally
    if (typeof window !== 'undefined') {
        window.renderWithComprehensiveRenderer = renderWithComprehensiveRenderer;
        window.autoRenderFromCustomization = autoRenderFromCustomization;
        window.renderWindowEnhanced = renderWindowEnhanced;
        
        // Optionally override existing renderWindow
        // Uncomment the line below to use comprehensive renderer by default
        // window.renderWindow = renderWindowEnhanced;
        
        console.log('[2D Integration] Comprehensive renderer integration initialized');
    }
}

// Auto-initialize if DOM is ready
if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initComprehensiveRendererIntegration);
    } else {
        initComprehensiveRendererIntegration();
    }
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        renderWithComprehensiveRenderer,
        autoRenderFromCustomization,
        renderWindowEnhanced,
        initComprehensiveRendererIntegration
    };
}
