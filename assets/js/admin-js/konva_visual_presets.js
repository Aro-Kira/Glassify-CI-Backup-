// =====================================================
// KONVA VISUAL PRESETS
// Smart visual configuration presets based on field types
// Provides auto-suggestions for admin when adding tags
// =====================================================

/**
 * Field type categories - determines what Konva properties a field affects
 */
const FIELD_VISUAL_CATEGORIES = {
  // Glass appearance fields - affects fill color, opacity, patterns
  glass: {
    fieldIds: ['glassType', 'glassTreatment', 'finish', 'tintFinish', 'safetyGlassType', 'glassThickness', 'tint'],
    konvaProperties: ['fill', 'opacity', 'pattern', 'gradient'],
    description: 'Affects glass panel appearance (color, transparency, texture)'
  },
  
  // Frame/border fields - affects stroke color, width, style
  frame: {
    fieldIds: ['frameColor', 'frameType', 'hardwareColor', 'hardwareFinish', 'frameMaterialFinish', 'frameProfile'],
    konvaProperties: ['stroke', 'strokeWidth', 'edgeStyle'],
    description: 'Affects frame/border appearance (color, thickness, style)'
  },
  
  // Orientation fields - affects product orientation/layout
  orientation: {
    fieldIds: ['orientation'],
    konvaProperties: ['rotation', 'aspectRatio'],
    description: 'Affects product orientation (vertical, horizontal, full-body)'
  },
  
  // Shape fields - affects overall shape
  shape: {
    fieldIds: ['shape', 'layout'],
    konvaProperties: ['shape', 'cornerRadius'],
    description: 'Affects overall shape rendering'
  },
  
  // Pattern/grid fields - adds overlay patterns
  pattern: {
    fieldIds: ['gridPattern', 'gridPosition', 'style'],
    konvaProperties: ['pattern', 'patternDensity'],
    description: 'Adds grid or pattern overlays'
  },
  
  // Edge finish fields - affects edge rendering style
  edge: {
    fieldIds: ['edgeFinish', 'edgeStyle'],
    konvaProperties: ['edgeStyle', 'cornerRadius', 'stroke'],
    description: 'Affects edge finish appearance'
  },
  
  // Configuration fields - affects panel layout
  configuration: {
    fieldIds: ['numberOfPanels', 'panelCount', 'panelConfiguration', 'configuration', 'sizeConfiguration', 'layoutConfiguration', 'fixedPanels'],
    konvaProperties: ['panelCount', 'panelLayout'],
    description: 'Affects panel count and layout'
  },
  
  // Operation fields - affects operation indicators
  operation: {
    fieldIds: ['operation', 'doorType', 'doorSwing', 'hingeSide', 'openingDirection'],
    konvaProperties: ['operationIndicator', 'swingDirection'],
    description: 'Affects operation indicators (arrows, swing direction)'
  },
  
  // Hardware fields - affects hardware icons/indicators
  hardware: {
    fieldIds: ['handleType', 'handleStyle', 'handlesPulls', 'hinges', 'hardware', 'mounting', 'mountingMethod', 'mountingHardware', 'mountingSystem', 'installation'],
    konvaProperties: ['hardwareIndicator'],
    description: 'Affects hardware visual indicators'
  },
  
  // Lighting fields - affects glow/lighting effects
  lighting: {
    fieldIds: ['lighting', 'ledColorTemperature', 'ledColor', 'smartFeatures'],
    konvaProperties: ['shadow', 'shadowColor', 'glowEffect', 'shadowBlur', 'shadowOpacity'],
    description: 'Affects lighting and glow effects'
  },
  
  // Material fields - affects texture appearance
  material: {
    fieldIds: ['material', 'countertopMaterial', 'backsplashMaterial', 'organizerMaterials', 'cabinetColorFinish'],
    konvaProperties: ['fill', 'pattern', 'texture'],
    description: 'Affects material texture appearance'
  },
  
  // Features/accessories - badge indicators
  features: {
    fieldIds: ['screen', 'softClose', 'accessories', 'quantity', 'arrangement', 'transom', 'transomOptions', 'smartFeatures'],
    konvaProperties: ['badge', 'indicator'],
    description: 'Adds feature badges or indicators'
  }
};

/**
 * Get the visual category for a field ID
 * @param {string} fieldId - The field ID to categorize
 * @returns {Object|null} Category info or null
 */
function getFieldVisualCategory(fieldId) {
  const normalizedId = fieldId.toLowerCase();
  
  for (const [category, config] of Object.entries(FIELD_VISUAL_CATEGORIES)) {
    if (config.fieldIds.some(id => normalizedId.includes(id.toLowerCase()) || id.toLowerCase().includes(normalizedId))) {
      return { category, ...config };
    }
  }
  
  return null;
}

/**
 * Smart visual presets for common tag values
 * Provides auto-fill suggestions when admin types certain tag names
 */
const VISUAL_PRESETS = {
  // Glass Type Presets
  'clear': { fill: '#E0F2F1', opacity: 0.9, effectType: 'fill' },
  'tinted': { fill: '#546E7A', opacity: 0.7, effectType: 'fill' },
  'tinted black': { fill: '#263238', opacity: 0.6, effectType: 'shadow', shadowBlur: 10, shadowOpacity: 0.3 },
  'tinted bronze': { fill: '#795548', opacity: 0.65, effectType: 'fill' },
  'tinted brown': { fill: '#6D4C41', opacity: 0.65, effectType: 'fill' },
  'tinted grey': { fill: '#607D8B', opacity: 0.6, effectType: 'fill' },
  'tinted gray': { fill: '#607D8B', opacity: 0.6, effectType: 'fill' },
  'frosted': { fill: '#FFFFFF', opacity: 0.95, effectType: 'pattern', patternType: 'frosted', patternDensity: 8 },
  'laminated': { fill: '#CFD8DC', opacity: 0.95, effectType: 'fill' },
  'tempered': { fill: '#E0F2F1', opacity: 0.9, effectType: 'fill' },
  'low-e': { fill: '#DCEDC8', opacity: 0.85, effectType: 'fill' },
  'double-pane': { fill: '#B2DFDB', opacity: 0.9, effectType: 'fill' },
  'reflective': { fill: '#90CAF9', opacity: 0.7, effectType: 'gradient', gradientEnd: '#E3F2FD', gradientDirection: 'diagonal' },
  'patterned': { fill: '#E8E8E8', opacity: 0.9, effectType: 'pattern', patternType: 'lines', patternDensity: 5 },
  'smoke': { fill: '#455A64', opacity: 0.5, effectType: 'fill' },
  'smoke glass': { fill: '#37474F', opacity: 0.45, effectType: 'shadow', shadowBlur: 8, shadowOpacity: 0.2 },
  'safety glass': { fill: '#C8E6C9', opacity: 0.9, effectType: 'fill' },
  'bulletproof': { fill: '#A5D6A7', opacity: 0.95, effectType: 'fill', strokeWidth: 6 },
  
  // Frame Color Presets
  'white': { fill: '#FFFFFF', stroke: '#FFFFFF', strokeWidth: 4, effectType: 'frame' },
  'black': { fill: '#212121', stroke: '#212121', strokeWidth: 4, effectType: 'frame' },
  'silver': { fill: '#C0C0C0', stroke: '#C0C0C0', strokeWidth: 3, effectType: 'frame' },
  'bronze': { fill: '#CD7F32', stroke: '#CD7F32', strokeWidth: 4, effectType: 'frame' },
  'gold': { fill: '#FFD700', stroke: '#FFD700', strokeWidth: 4, effectType: 'frame' },
  'brown': { fill: '#795548', stroke: '#795548', strokeWidth: 5, effectType: 'frame' },
  'wood': { fill: '#8D6E63', stroke: '#8D6E63', strokeWidth: 6, effectType: 'frame' },
  'wood-grain': { fill: '#A1887F', stroke: '#6D4C41', strokeWidth: 5, effectType: 'pattern', patternType: 'lines', patternDensity: 3 },
  'aluminum': { fill: '#90A4AE', stroke: '#90A4AE', strokeWidth: 3, effectType: 'frame' },
  'analok': { fill: '#5D4037', stroke: '#5D4037', strokeWidth: 4, effectType: 'frame' },
  'chrome': { fill: '#E0E0E0', stroke: '#BDBDBD', strokeWidth: 3, effectType: 'gradient', gradientEnd: '#FAFAFA', gradientDirection: 'horizontal' },
  'stainless steel': { fill: '#CFD8DC', stroke: '#B0BEC5', strokeWidth: 3, effectType: 'gradient', gradientEnd: '#ECEFF1', gradientDirection: 'vertical' },
  'matte black': { fill: '#263238', stroke: '#263238', strokeWidth: 4, effectType: 'frame' },
  'brushed nickel': { fill: '#9E9E9E', stroke: '#757575', strokeWidth: 3, effectType: 'frame' },
  'rose gold': { fill: '#E8B4B8', stroke: '#C9A0A0', strokeWidth: 4, effectType: 'frame' },
  
  // Edge Finish Presets
  'beveled': { edgeStyle: 'beveled', strokeWidth: 3, effectType: 'edge' },
  'polished': { edgeStyle: 'solid', strokeWidth: 2, stroke: '#EEEEEE', effectType: 'edge' },
  'raw': { edgeStyle: 'solid', strokeWidth: 1, stroke: '#9E9E9E', effectType: 'edge' },
  'flat polished': { edgeStyle: 'solid', strokeWidth: 2, effectType: 'edge' },
  'pencil edge': { edgeStyle: 'rounded', cornerRadius: 5, effectType: 'edge' },
  'rounded edges': { edgeStyle: 'rounded', cornerRadius: 10, effectType: 'edge' },
  
  // Layout/Shape Presets
  'rectangle': { shape: 'rectangle', cornerRadius: 0 },
  'round': { shape: 'circle' },
  'circle': { shape: 'circle' },
  'oval': { shape: 'oval' },
  'square': { shape: 'rectangle', cornerRadius: 0 },
  'l-shape': { shape: 'l-shape', panelLayout: 'L' },
  'u-shape': { shape: 'u-shape', panelLayout: 'U' },
  'straight': { shape: 'rectangle', panelLayout: 'straight' },
  'neo-angle': { shape: 'neo-angle', panelLayout: 'neo' },
  
  // Pattern Presets
  'french type': { effectType: 'pattern', patternType: 'grid', patternDensity: 8 },
  'french': { effectType: 'pattern', patternType: 'grid', patternDensity: 8 },
  'colonial': { effectType: 'pattern', patternType: 'grid', patternDensity: 6 },
  'prairie': { effectType: 'pattern', patternType: 'grid', patternDensity: 4 },
  'standard': { effectType: 'fill' },
  'internal grids': { effectType: 'pattern', patternType: 'grid', patternDensity: 6 },
  'external grids': { effectType: 'pattern', patternType: 'grid', patternDensity: 6, strokeWidth: 3 },
  
  // Material Presets
  'mdf': { fill: '#D7CCC8', opacity: 0.95, effectType: 'fill' },
  'metal': { fill: '#90A4AE', opacity: 1, effectType: 'gradient', gradientEnd: '#CFD8DC', gradientDirection: 'vertical' },
  'glass': { fill: '#E0F2F1', opacity: 0.85, effectType: 'fill' },
  'laminate': { fill: '#EFEBE9', opacity: 0.95, effectType: 'fill' },
  'quartz': { fill: '#E0E0E0', opacity: 1, effectType: 'pattern', patternType: 'dots', patternDensity: 3 },
  'granite': { fill: '#9E9E9E', opacity: 1, effectType: 'pattern', patternType: 'dots', patternDensity: 8 },
  
  // Finish Type Presets
  'matte': { opacity: 0.95, effectType: 'fill' },
  'glossy': { effectType: 'gradient', gradientEnd: '#FFFFFF', gradientDirection: 'diagonal', opacity: 0.9 },
  'semi-gloss': { effectType: 'gradient', gradientEnd: '#FAFAFA', gradientDirection: 'vertical', opacity: 0.95 },
  'satin': { opacity: 0.9, effectType: 'fill' },
  'textured': { effectType: 'pattern', patternType: 'dots', patternDensity: 5 },
  
  // Lighting Presets
  'integrated led': { effectType: 'shadow', shadowColor: '#FFEB3B', shadowBlur: 20, shadowOpacity: 0.5, shadowOffset: 0 },
  'backlighting': { effectType: 'shadow', shadowColor: '#FFFFFF', shadowBlur: 25, shadowOpacity: 0.6, shadowOffset: 0 },
  'warm white': { effectType: 'shadow', shadowColor: '#FFF8E1', shadowBlur: 15, shadowOpacity: 0.4, shadowOffset: 0 },
  'cool white': { effectType: 'shadow', shadowColor: '#E3F2FD', shadowBlur: 15, shadowOpacity: 0.4, shadowOffset: 0 },
  'rgb': { effectType: 'shadow', shadowColor: '#E040FB', shadowBlur: 20, shadowOpacity: 0.5, shadowOffset: 0 },
  
  // Mirror-Specific Presets
  // Mirror Tint Presets
  'grey (smoked)': { fill: '#607D8B', opacity: 0.5, effectType: 'fill' },
  'grey smoked': { fill: '#607D8B', opacity: 0.5, effectType: 'fill' },
  'smoked': { fill: '#607D8B', opacity: 0.5, effectType: 'fill' },
  'mirror bronze': { fill: '#795548', opacity: 0.6, effectType: 'fill' },
  'mirror black': { fill: '#263238', opacity: 0.7, effectType: 'fill' },
  'mirror clear': { fill: '#E0F2F1', opacity: 0.9, effectType: 'fill' },
  
  // Mirror Frame Type Presets
  'frameless': { stroke: 'transparent', strokeWidth: 0, effectType: 'frame' },
  'standard frame': { stroke: '#333333', strokeWidth: 6, effectType: 'frame' },
  'thin frame': { stroke: '#333333', strokeWidth: 3, effectType: 'frame' },
  'grid frame': { stroke: '#333333', strokeWidth: 4, effectType: 'pattern', patternType: 'grid', patternDensity: 4 },
  
  // Mirror Orientation Presets
  'vertical': { rotation: 0, effectType: 'orientation' },
  'horizontal': { rotation: 90, effectType: 'orientation' },
  'full-body': { effectType: 'fill', opacity: 0.95 },
  
  // Mirror Lighting Presets
  'led backlight': { effectType: 'shadow', shadowColor: '#FFFFFF', shadowBlur: 30, shadowOpacity: 0.7, shadowOffset: 0 },
  'led front light': { effectType: 'shadow', shadowColor: '#FFFFFF', shadowBlur: 20, shadowOpacity: 0.6, shadowOffset: 0 },
  'none': { effectType: 'fill' },
  'daylight': { effectType: 'shadow', shadowColor: '#E3F2FD', shadowBlur: 18, shadowOpacity: 0.5, shadowOffset: 0 },
  
  // Mirror Smart Features Presets
  'touch dimmer': { effectType: 'badge', badgeText: 'Dimmer', badgeColor: '#FF9800' },
  'defogger': { effectType: 'badge', badgeText: 'Defog', badgeColor: '#2196F3' },
  'motion sensor': { effectType: 'badge', badgeText: 'Motion', badgeColor: '#4CAF50' },
  'bluetooth speaker': { effectType: 'badge', badgeText: 'BT', badgeColor: '#9C27B0' },
  
  // Top Glass & Glass Board Presets (shared with mirrors for basic options)
  'custom shapes': { shape: 'rectangle', cornerRadius: 0, effectType: 'shape' }
};

/**
 * Get visual preset for a tag value
 * @param {string} tagValue - The tag value to look up
 * @returns {Object|null} Visual preset or null
 */
function getVisualPreset(tagValue) {
  if (!tagValue) return null;
  
  const normalizedValue = tagValue.toLowerCase().trim();
  
  // Exact match
  if (VISUAL_PRESETS[normalizedValue]) {
    return { ...VISUAL_PRESETS[normalizedValue] };
  }
  
  // Partial match - check if any preset key is contained in the value
  for (const [key, preset] of Object.entries(VISUAL_PRESETS)) {
    if (normalizedValue.includes(key) || key.includes(normalizedValue)) {
      return { ...preset };
    }
  }
  
  return null;
}

/**
 * Suggest visual configuration based on field ID and tag value
 * @param {string} fieldId - The field ID
 * @param {string} tagValue - The tag value being added
 * @returns {Object} Suggested visual configuration
 */
function suggestVisualConfig(fieldId, tagValue) {
  const category = getFieldVisualCategory(fieldId);
  const preset = getVisualPreset(tagValue);
  
  // Start with preset if available
  let suggestion = preset ? { ...preset } : {};
  
  // Apply category-specific defaults
  if (category) {
    switch (category.category) {
      case 'glass':
        suggestion = {
          effectType: 'fill',
          fill: '#E0F2F1',
          opacity: 0.9,
          ...suggestion
        };
        break;
        
      case 'frame':
        suggestion = {
          effectType: 'frame',
          stroke: '#333333',
          strokeWidth: 4,
          ...suggestion
        };
        break;
        
      case 'edge':
        suggestion = {
          effectType: 'edge',
          edgeStyle: 'solid',
          strokeWidth: 2,
          ...suggestion
        };
        break;
        
      case 'pattern':
        suggestion = {
          effectType: 'pattern',
          patternType: 'grid',
          patternDensity: 5,
          ...suggestion
        };
        break;
        
      case 'lighting':
        suggestion = {
          effectType: 'shadow',
          shadowBlur: 15,
          shadowOpacity: 0.4,
          shadowOffset: 0,
          shadowColor: '#FFFFFF',
          ...suggestion
        };
        break;
        
      case 'material':
        suggestion = {
          effectType: 'fill',
          fill: '#E0E0E0',
          opacity: 0.95,
          ...suggestion
        };
        break;
        
      default:
        // Default suggestion
        suggestion = {
          effectType: 'fill',
          fill: '#E0F2F1',
          opacity: 0.9,
          stroke: '#333333',
          strokeWidth: 4,
          ...suggestion
        };
    }
  }
  
  return suggestion;
}

/**
 * Auto-apply visual preset when tag name changes
 * @param {string} fieldId - Field ID
 * @param {string} tagValue - Tag value entered by admin
 */
function autoApplyVisualPreset(fieldId, tagValue) {
  const suggestion = suggestVisualConfig(fieldId, tagValue);
  
  if (!suggestion || Object.keys(suggestion).length === 0) return;
  
  console.log(`[Visual Presets] Auto-applying preset for "${tagValue}":`, suggestion);
  
  // Apply to form inputs
  const effectSelect = document.getElementById('tagKonvaEffect');
  const fillColor = document.getElementById('tagFillColor');
  const fillColorHex = document.getElementById('tagFillColorHex');
  const strokeColor = document.getElementById('tagStrokeColor');
  const strokeColorHex = document.getElementById('tagStrokeColorHex');
  const opacity = document.getElementById('tagOpacity');
  const opacityValue = document.getElementById('tagOpacityValue');
  const strokeWidth = document.getElementById('tagStrokeWidth');
  const strokeWidthValue = document.getElementById('tagStrokeWidthValue');
  
  // Apply basic settings
  if (effectSelect && suggestion.effectType) {
    effectSelect.value = suggestion.effectType;
    effectSelect.dispatchEvent(new Event('change'));
  }
  
  if (fillColor && suggestion.fill) {
    fillColor.value = suggestion.fill;
    if (fillColorHex) fillColorHex.value = suggestion.fill;
  }
  
  if (strokeColor && suggestion.stroke) {
    strokeColor.value = suggestion.stroke;
    if (strokeColorHex) strokeColorHex.value = suggestion.stroke;
  }
  
  if (opacity && suggestion.opacity !== undefined) {
    opacity.value = suggestion.opacity;
    if (opacityValue) opacityValue.textContent = suggestion.opacity;
  }
  
  if (strokeWidth && suggestion.strokeWidth !== undefined) {
    strokeWidth.value = suggestion.strokeWidth;
    if (strokeWidthValue) strokeWidthValue.textContent = suggestion.strokeWidth;
  }
  
  // Apply advanced settings
  if (suggestion.effectType === 'gradient') {
    const gradientEnd = document.getElementById('tagGradientEnd');
    const gradientDirection = document.getElementById('tagGradientDirection');
    if (gradientEnd && suggestion.gradientEnd) gradientEnd.value = suggestion.gradientEnd;
    if (gradientDirection && suggestion.gradientDirection) gradientDirection.value = suggestion.gradientDirection;
  }
  
  if (suggestion.effectType === 'shadow' || suggestion.shadowBlur) {
    const shadowBlur = document.getElementById('tagShadowBlur');
    const shadowBlurValue = document.getElementById('tagShadowBlurValue');
    const shadowOffset = document.getElementById('tagShadowOffset');
    const shadowOffsetValue = document.getElementById('tagShadowOffsetValue');
    const shadowColor = document.getElementById('tagShadowColor');
    const shadowOpacity = document.getElementById('tagShadowOpacity');
    const shadowOpacityValue = document.getElementById('tagShadowOpacityValue');
    
    if (shadowBlur && suggestion.shadowBlur !== undefined) {
      shadowBlur.value = suggestion.shadowBlur;
      if (shadowBlurValue) shadowBlurValue.textContent = suggestion.shadowBlur;
    }
    if (shadowOffset && suggestion.shadowOffset !== undefined) {
      shadowOffset.value = suggestion.shadowOffset;
      if (shadowOffsetValue) shadowOffsetValue.textContent = suggestion.shadowOffset;
    }
    if (shadowColor && suggestion.shadowColor) shadowColor.value = suggestion.shadowColor;
    if (shadowOpacity && suggestion.shadowOpacity !== undefined) {
      shadowOpacity.value = suggestion.shadowOpacity;
      if (shadowOpacityValue) shadowOpacityValue.textContent = suggestion.shadowOpacity;
    }
  }
  
  if (suggestion.effectType === 'pattern' || suggestion.patternType) {
    const patternType = document.getElementById('tagPatternType');
    const patternDensity = document.getElementById('tagPatternDensity');
    const patternDensityValue = document.getElementById('tagPatternDensityValue');
    
    if (patternType && suggestion.patternType) patternType.value = suggestion.patternType;
    if (patternDensity && suggestion.patternDensity !== undefined) {
      patternDensity.value = suggestion.patternDensity;
      if (patternDensityValue) patternDensityValue.textContent = suggestion.patternDensity;
    }
  }
  
  if (suggestion.effectType === 'edge' || suggestion.edgeStyle) {
    const edgeStyle = document.getElementById('tagEdgeStyle');
    const cornerRadius = document.getElementById('tagCornerRadius');
    const cornerRadiusValue = document.getElementById('tagCornerRadiusValue');
    
    if (edgeStyle && suggestion.edgeStyle) edgeStyle.value = suggestion.edgeStyle;
    if (cornerRadius && suggestion.cornerRadius !== undefined) {
      cornerRadius.value = suggestion.cornerRadius;
      if (cornerRadiusValue) cornerRadiusValue.textContent = suggestion.cornerRadius;
    }
  }
  
  // Update preview
  if (typeof updateTagKonvaPreview === 'function') {
    updateTagKonvaPreview();
  }
}

// Export to window for global access
if (typeof window !== 'undefined') {
  window.FIELD_VISUAL_CATEGORIES = FIELD_VISUAL_CATEGORIES;
  window.VISUAL_PRESETS = VISUAL_PRESETS;
  window.getFieldVisualCategory = getFieldVisualCategory;
  window.getVisualPreset = getVisualPreset;
  window.suggestVisualConfig = suggestVisualConfig;
  window.autoApplyVisualPreset = autoApplyVisualPreset;
}
