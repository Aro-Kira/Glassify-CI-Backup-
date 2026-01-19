<?php
/**
 * Generated customization defaults from CUSTOMIZATION_REFERENCE.md
 * Generated on: 2026-01-19 08:29:04
 */

return array (
  'Windows_Sliding' => 
  array (
    0 => 
    array (
      'type' => 'tags',
      'label' => 'Number of Panels',
      'id' => 'numberOfPanels',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => '2 Panels',
        1 => '4 Panels',
      ),
    ),
    1 => 
    array (
      'type' => 'tags',
      'label' => 'Transom Type (Top / Bottom Fixed Panel)',
      'id' => 'transomTypeTopBottomFixedPanel',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'None',
        1 => 'Fixed Transom Head (Fixed glass at top)',
        2 => 'Fixed Transom Sill (Fixed glass at bottom)',
      ),
    ),
    2 => 
    array (
      'type' => 'tags',
      'label' => 'Track System (Sliding Rail Count)',
      'id' => 'trackSystemSlidingRailCount',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => '2 Tracks',
        1 => '3 Tracks',
      ),
    ),
    3 => 
    array (
      'type' => 'tags',
      'label' => 'Panel Configuration',
      'id' => 'panelConfiguration',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'S | S (Sliding | Sliding)',
        1 => 'F | S (Fixed | Sliding)',
        2 => 'S | S | S | S (All Sliding)',
        3 => 'F | S | S | F (Fixed | Sliding | Sliding | Fixed)',
      ),
    ),
    4 => 
    array (
      'type' => 'tags',
      'label' => 'Frame Color',
      'id' => 'frameColor',
      'stepNumber' => 3,
      'options' => 
      array (
        0 => 'Hanalok',
        1 => 'White',
        2 => 'Black',
        3 => 'Gray',
        4 => 'Wood Finish',
      ),
    ),
    5 => 
    array (
      'type' => 'tags',
      'label' => 'Glass Type',
      'id' => 'glassType',
      'stepNumber' => 3,
      'options' => 
      array (
        0 => 'Clear',
        1 => 'Ultra Clear',
        2 => 'Bronze',
        3 => 'Light Green',
        4 => 'Dark Gray',
        5 => 'Copperfree Mirror',
        6 => 'Euro Gray',
        7 => 'Ford Blue',
        8 => 'Reflective: Clear',
        9 => 'Reflective: Gray',
        10 => 'Reflective: Light Blue',
        11 => 'Reflective: Dark Blue',
        12 => 'Reflective: Light Green',
        13 => 'Reflective: Dark Green',
        14 => 'Reflective: Light Bronze',
        15 => 'Tempered: Clear',
        16 => 'Tempered: Bronze',
      ),
    ),
    6 => 
    array (
      'type' => 'tags',
      'label' => 'Glass Thickness',
      'id' => 'glassThickness',
      'stepNumber' => 3,
      'options' => 
      array (
        0 => '6mm',
      ),
    ),
    7 => 
    array (
      'type' => 'tags',
      'label' => 'Lock Type',
      'id' => 'lockType',
      'stepNumber' => 4,
      'options' => 
      array (
        0 => 'Center Lok 904 Big',
        1 => 'Flushlok #12',
        2 => 'Durable Flushlok',
        3 => 'New Auto Flushlock',
      ),
    ),
    8 => 
    array (
      'type' => 'tags',
      'label' => 'Roller Type',
      'id' => 'rollerType',
      'stepNumber' => 4,
      'options' => 
      array (
        0 => 'Single Panel Roller',
        1 => 'Blue Single Roller',
        2 => 'Blue Double Roller',
      ),
    ),
    9 => 
    array (
      'type' => 'tags',
      'label' => 'Screen',
      'id' => 'screen',
      'stepNumber' => 4,
      'options' => 
      array (
        0 => 'With Screen',
        1 => 'Without Screen',
      ),
    ),
  ),
  'Windows_Awning' => 
  array (
    0 => 
    array (
      'type' => 'tags',
      'label' => 'Glass Type',
      'id' => 'glassType',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Clear',
        1 => 'Tinted',
        2 => 'Tinted (bronze/brown)',
        3 => 'Frosted',
        4 => 'Low-E',
        5 => 'Laminated',
      ),
    ),
    1 => 
    array (
      'type' => 'tags',
      'label' => 'Frame Color/Material',
      'id' => 'frameColormaterial',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'White',
        1 => 'Black',
        2 => 'Brown',
        3 => 'Silver',
        4 => 'Bronze',
        5 => 'Custom colors',
      ),
    ),
    2 => 
    array (
      'type' => 'tags',
      'label' => 'Operation',
      'id' => 'operation',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Awning (crank-out)',
        1 => 'Awning (push-out)',
      ),
    ),
    3 => 
    array (
      'type' => 'tags',
      'label' => 'Size Configuration',
      'id' => 'sizeConfiguration',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'Single panel',
        1 => 'Multiple panels',
      ),
    ),
    4 => 
    array (
      'type' => 'tags',
      'label' => 'Opening Direction',
      'id' => 'openingDirection',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'Top-hinged',
      ),
    ),
    5 => 
    array (
      'type' => 'number',
      'label' => 'Thickness (mm)',
      'id' => 'thicknessMm',
      'stepNumber' => 2,
      'min' => 1.0,
      'step' => 0.1,
    ),
  ),
  'Windows_Casement' => 
  array (
    0 => 
    array (
      'type' => 'tags',
      'label' => 'Glass Type',
      'id' => 'glassType',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Clear',
        1 => 'Tinted',
        2 => 'Frosted',
        3 => 'Low-E',
        4 => 'Laminated',
      ),
    ),
    1 => 
    array (
      'type' => 'tags',
      'label' => 'Frame Color/Material',
      'id' => 'frameColormaterial',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'White',
        1 => 'Black',
        2 => 'Brown (wood-grain)',
        3 => 'Silver',
        4 => 'Bronze',
        5 => 'Custom colors',
      ),
    ),
    2 => 
    array (
      'type' => 'tags',
      'label' => 'Operation',
      'id' => 'operation',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Casement (hinge side configurable)',
      ),
    ),
    3 => 
    array (
      'type' => 'tags',
      'label' => 'Number of Panels',
      'id' => 'numberOfPanels',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'Single panel',
        1 => 'Multiple panels',
      ),
    ),
    4 => 
    array (
      'type' => 'tags',
      'label' => 'Hinge Side',
      'id' => 'hingeSide',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'Left-hinged',
        1 => 'Right-hinged',
      ),
    ),
    5 => 
    array (
      'type' => 'tags',
      'label' => 'Configuration',
      'id' => 'configuration',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'Two casement windows with fixed transom',
        1 => 'Custom configurations',
      ),
    ),
    6 => 
    array (
      'type' => 'tags',
      'label' => 'Transom Options',
      'id' => 'transomOptions',
      'stepNumber' => 3,
      'options' => 
      array (
        0 => 'Different transom sizes',
        1 => 'Shapes',
        2 => 'Mullion options',
      ),
    ),
    7 => 
    array (
      'type' => 'number',
      'label' => 'Thickness (mm)',
      'id' => 'thicknessMm',
      'stepNumber' => 3,
      'min' => 1.0,
      'step' => 0.1,
    ),
  ),
  'Windows_Fixed Glass' => 
  array (
    0 => 
    array (
      'type' => 'tags',
      'label' => 'Glass Type',
      'id' => 'glassType',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Clear',
        1 => 'Tinted',
        2 => 'Frosted',
        3 => 'Low-E',
        4 => 'Reflective coatings',
        5 => 'Safety glass',
        6 => 'Laminated',
      ),
    ),
    1 => 
    array (
      'type' => 'tags',
      'label' => 'Frame Color/Material',
      'id' => 'frameColormaterial',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'White',
        1 => 'Black',
        2 => 'Dark Grey/Black',
        3 => 'Brown',
        4 => 'Silver',
        5 => 'Bronze',
        6 => 'Custom colors',
      ),
    ),
    2 => 
    array (
      'type' => 'tags',
      'label' => 'Configuration',
      'id' => 'configuration',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Fixed corner glass',
        1 => 'Various angles (90°, 135°, custom)',
        2 => 'Standard fixed',
      ),
    ),
    3 => 
    array (
      'type' => 'tags',
      'label' => 'Usage',
      'id' => 'usage',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'Structural/architectural feature (non-operable)',
        1 => 'Standard fixed',
      ),
    ),
    4 => 
    array (
      'type' => 'tags',
      'label' => 'Installation Method',
      'id' => 'installationMethod',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'Various integration methods',
        1 => 'Standard mounting',
      ),
    ),
    5 => 
    array (
      'type' => 'number',
      'label' => 'Thickness (mm)',
      'id' => 'thicknessMm',
      'stepNumber' => 2,
      'min' => 1.0,
      'step' => 0.1,
    ),
  ),
  'Doors_Sliding' => 
  array (
    0 => 
    array (
      'type' => 'tags',
      'label' => 'Glass Type',
      'id' => 'glassType',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Clear',
        1 => 'Tinted',
        2 => 'Frosted',
        3 => 'Low-E',
        4 => 'Tempered',
        5 => 'Laminated',
        6 => 'Laminated safety glass',
      ),
    ),
    1 => 
    array (
      'type' => 'tags',
      'label' => 'Frame Material/Color',
      'id' => 'frameMaterialcolor',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Aluminum',
        1 => 'Black',
        2 => 'White',
        3 => 'Bronze',
        4 => 'Brown (wood-look)',
        5 => 'Silver',
        6 => 'Custom colors',
      ),
    ),
    2 => 
    array (
      'type' => 'tags',
      'label' => 'Panel Count',
      'id' => 'panelCount',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => '2-panel',
        1 => '3-panel',
        2 => '4-panel',
        3 => 'More panels',
      ),
    ),
    3 => 
    array (
      'type' => 'tags',
      'label' => 'Operation',
      'id' => 'operation',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'Sliding (single)',
        1 => 'Sliding (double)',
        2 => 'Sliding (multi-track)',
      ),
    ),
    4 => 
    array (
      'type' => 'tags',
      'label' => 'Panel Configuration',
      'id' => 'panelConfiguration',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'Central sliding panels with fixed outer panels',
        1 => 'All sliding',
        2 => '2 sliding + 2 fixed',
        3 => '2 sliding only',
        4 => '3 sliding',
        5 => 'Custom',
      ),
    ),
    5 => 
    array (
      'type' => 'tags',
      'label' => 'Handle Type',
      'id' => 'handleType',
      'stepNumber' => 3,
      'options' => 
      array (
        0 => 'Various pull handles',
        1 => 'Knob handles',
        2 => 'Square handles',
        3 => 'Bar-style',
        4 => 'Round',
        5 => 'Square matte black',
      ),
    ),
    6 => 
    array (
      'type' => 'tags',
      'label' => 'Hardware Finish',
      'id' => 'hardwareFinish',
      'stepNumber' => 3,
      'options' => 
      array (
        0 => 'Chrome/Stainless Steel',
        1 => 'Polished Chrome/Stainless Steel',
        2 => 'Black Matte',
        3 => 'Gold',
        4 => 'Brushed Nickel',
        5 => 'Bronze',
      ),
    ),
  ),
  'Doors_Frameless' => 
  array (
    0 => 
    array (
      'type' => 'tags',
      'label' => 'Glass Type',
      'id' => 'glassType',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Clear',
        1 => 'Tinted',
        2 => 'Frosted',
        3 => 'Laminated',
        4 => 'Laminated safety glass',
      ),
    ),
    1 => 
    array (
      'type' => 'tags',
      'label' => 'Door Type',
      'id' => 'doorType',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Single swing',
        1 => 'Double swing',
        2 => 'Single French door',
        3 => 'Double French doors',
      ),
    ),
    2 => 
    array (
      'type' => 'tags',
      'label' => 'Door Swing',
      'id' => 'doorSwing',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Left swing',
        1 => 'Right swing',
        2 => 'Left-hinged',
        3 => 'Right-hinged',
      ),
    ),
    3 => 
    array (
      'type' => 'tags',
      'label' => 'Fixed Panels',
      'id' => 'fixedPanels',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'With fixed side/transom panels',
        1 => 'Without fixed panels',
        2 => '0 fixed panels',
        3 => '1 fixed panel',
        4 => '2 fixed panels',
        5 => 'More fixed panels',
        6 => 'With fixed side panel (left or right)',
        7 => 'With fixed transom',
        8 => 'Both',
      ),
    ),
    4 => 
    array (
      'type' => 'tags',
      'label' => 'Configuration',
      'id' => 'configuration',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'With fixed side panel (left or right)',
        1 => 'With fixed transom',
        2 => 'Both',
        3 => 'Single swing door',
        4 => 'Double swing door',
      ),
    ),
    5 => 
    array (
      'type' => 'tags',
      'label' => 'Handle Style',
      'id' => 'handleStyle',
      'stepNumber' => 3,
      'options' => 
      array (
        0 => 'Various pull handle designs',
        1 => 'Various pull handles',
        2 => 'Decorative handles',
      ),
    ),
    6 => 
    array (
      'type' => 'tags',
      'label' => 'Hardware Finish',
      'id' => 'hardwareFinish',
      'stepNumber' => 3,
      'options' => 
      array (
        0 => 'Polished Chrome/Stainless Steel',
        1 => 'Matte Black',
        2 => 'Brushed Nickel',
        3 => 'Gold',
        4 => 'Chrome/Stainless Steel',
      ),
    ),
    7 => 
    array (
      'type' => 'tags',
      'label' => 'Grid Pattern',
      'id' => 'gridPattern',
      'stepNumber' => 3,
      'options' => 
      array (
        0 => 'Internal grids',
        1 => 'External grids',
        2 => 'Colonial',
        3 => 'Prairie',
        4 => 'Custom grid designs',
        5 => 'French type grid',
      ),
    ),
    8 => 
    array (
      'type' => 'tags',
      'label' => 'Glass Treatment',
      'id' => 'glassTreatment',
      'stepNumber' => 4,
      'options' => 
      array (
        0 => 'Frosted stripes (horizontal/vertical)',
        1 => 'Custom patterns',
        2 => 'Colors',
        3 => 'Frosted sticker (customizable patterns, opacity, colors)',
      ),
    ),
    9 => 
    array (
      'type' => 'tags',
      'label' => 'Installation',
      'id' => 'installation',
      'stepNumber' => 4,
      'options' => 
      array (
        0 => 'Patch fittings (minimalist hardware)',
        1 => 'Standard',
      ),
    ),
    10 => 
    array (
      'type' => 'tags',
      'label' => 'Hardware',
      'id' => 'hardware',
      'stepNumber' => 4,
      'options' => 
      array (
        0 => 'Push/pull handles',
        1 => 'Locks',
        2 => 'Closers',
        3 => 'Multi-point locks',
      ),
    ),
  ),
  'Partitions_Frameless Glass' => 
  array (
    0 => 
    array (
      'type' => 'tags',
      'label' => 'Layout',
      'id' => 'layout',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'L-shape',
        1 => 'Straight',
        2 => 'U-shape',
        3 => 'L-type',
        4 => 'Neo-angle',
        5 => 'Square',
        6 => 'Bay',
        7 => 'Other corner layouts',
      ),
    ),
    1 => 
    array (
      'type' => 'tags',
      'label' => 'Glass Type',
      'id' => 'glassType',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Clear',
        1 => 'Frosted',
        2 => 'Tinted',
        3 => 'Frosted (full or partial)',
        4 => 'Clear with frosted sticker',
        5 => 'Fully frosted',
      ),
    ),
    2 => 
    array (
      'type' => 'tags',
      'label' => 'Finish',
      'id' => 'finish',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Clear',
        1 => 'Frosted',
        2 => 'Patterned',
      ),
    ),
    3 => 
    array (
      'type' => 'tags',
      'label' => 'Configuration',
      'id' => 'configuration',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'Single partition',
        1 => 'Multiple partitions',
        2 => '2 fixed panels',
        3 => '3 fixed panels',
        4 => 'Custom configurations',
      ),
    ),
    4 => 
    array (
      'type' => 'tags',
      'label' => 'Hardware Color',
      'id' => 'hardwareColor',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'Black',
        1 => 'Silver',
        2 => 'Gold',
        3 => 'White',
        4 => 'Bronze',
        5 => 'Chrome/Stainless Steel',
        6 => 'Black Matte',
        7 => 'Brushed Nickel',
        8 => 'Stainless Steel',
      ),
    ),
    5 => 
    array (
      'type' => 'tags',
      'label' => 'Mounting Hardware',
      'id' => 'mountingHardware',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'Stainless Fixed Bracket',
        1 => 'Gold U-Channel',
        2 => 'Analok U-Channel (anodized aluminum)',
        3 => 'Stainless U-Channel',
        4 => 'Other bracket types',
        5 => 'Standard mounting',
      ),
    ),
    6 => 
    array (
      'type' => 'number',
      'label' => 'Glass Thickness (mm)',
      'id' => 'glassThicknessMm',
      'stepNumber' => 2,
      'min' => 1.0,
      'step' => 0.1,
    ),
  ),
  'Partitions_Shower Enclosure' => 
  array (
    0 => 
    array (
      'type' => 'tags',
      'label' => 'Layout',
      'id' => 'layout',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'L-shape',
        1 => 'Straight',
        2 => 'U-shape',
        3 => 'L-type',
        4 => 'Neo-angle',
        5 => 'Square',
        6 => 'Bay',
        7 => 'Other corner layouts',
      ),
    ),
    1 => 
    array (
      'type' => 'tags',
      'label' => 'Configuration',
      'id' => 'configuration',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Fixed and swing',
        1 => 'Swing with small fixed glass',
        2 => 'Single sliding door',
        3 => 'Double sliding doors',
        4 => 'Sliding with fixed panels',
        5 => 'Single sliding',
        6 => 'Double sliding',
        7 => 'With fixed panels',
        8 => '2 fixed panels',
        9 => '3 fixed panels',
        10 => 'Custom configurations',
      ),
    ),
    2 => 
    array (
      'type' => 'tags',
      'label' => 'Glass Type',
      'id' => 'glassType',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Clear',
        1 => 'Frosted',
        2 => 'Tinted',
        3 => 'Frosted (full or partial)',
        4 => 'Clear with frosted sticker',
        5 => 'Fully frosted',
        6 => 'Custom frosting patterns',
        7 => 'Frosted (full or partial with custom patterns/heights)',
      ),
    ),
    3 => 
    array (
      'type' => 'tags',
      'label' => 'Glass Treatment',
      'id' => 'glassTreatment',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'Frosted sticker (customizable patterns, opacity, colors)',
        1 => 'Clear',
        2 => 'Custom patterns',
        3 => 'Heights (top clear, bottom frosted)',
        4 => 'Colors',
      ),
    ),
    4 => 
    array (
      'type' => 'number',
      'label' => 'Glass Thickness (mm)',
      'id' => 'glassThicknessMm',
      'stepNumber' => 2,
      'min' => 1.0,
      'step' => 0.1,
    ),
    5 => 
    array (
      'type' => 'tags',
      'label' => 'Hardware Finish',
      'id' => 'hardwareFinish',
      'stepNumber' => 3,
      'options' => 
      array (
        0 => 'Chrome/Stainless Steel',
        1 => 'Black Matte',
        2 => 'Gold',
        3 => 'Brushed Nickel',
        4 => 'Polished Chrome/Stainless Steel',
        5 => 'Matte Black (handles, hinges, connectors)',
        6 => 'Matte Black (rail, rollers, handles)',
        7 => 'Matte Black (hinges, handle, top bracing bar)',
        8 => 'Stainless Steel',
        9 => 'Black',
        10 => 'Silver',
        11 => 'Bronze',
      ),
    ),
    6 => 
    array (
      'type' => 'tags',
      'label' => 'Handle Style',
      'id' => 'handleStyle',
      'stepNumber' => 3,
      'options' => 
      array (
        0 => 'Various pull handle designs',
        1 => 'Various pull handles',
        2 => 'Knob handles',
        3 => 'Square handles',
        4 => 'Square matte black',
        5 => 'Round',
        6 => 'Bar-style',
      ),
    ),
    7 => 
    array (
      'type' => 'tags',
      'label' => 'Door Swing',
      'id' => 'doorSwing',
      'stepNumber' => 3,
      'options' => 
      array (
        0 => 'Left-hinged',
        1 => 'Right-hinged',
        2 => 'Left swing',
        3 => 'Right swing',
      ),
    ),
    8 => 
    array (
      'type' => 'tags',
      'label' => 'Mounting',
      'id' => 'mounting',
      'stepNumber' => 3,
      'options' => 
      array (
        0 => 'Standard mounting',
        1 => 'Custom mounting methods',
      ),
    ),
  ),
  'Partitions_Fixed Glass' => 
  array (
    0 => 
    array (
      'type' => 'tags',
      'label' => 'Layout',
      'id' => 'layout',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'L-shape',
        1 => 'Straight',
        2 => 'U-shape',
      ),
    ),
    1 => 
    array (
      'type' => 'tags',
      'label' => 'Glass Type',
      'id' => 'glassType',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Clear',
        1 => 'Frosted',
        2 => 'Tinted',
      ),
    ),
    2 => 
    array (
      'type' => 'tags',
      'label' => 'Finish',
      'id' => 'finish',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Clear',
        1 => 'Frosted',
        2 => 'Patterned',
      ),
    ),
    3 => 
    array (
      'type' => 'tags',
      'label' => 'Configuration',
      'id' => 'configuration',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'Single partition',
        1 => 'Multiple partitions',
        2 => '2 fixed panels',
        3 => '3 fixed panels',
        4 => 'Custom configurations',
      ),
    ),
    4 => 
    array (
      'type' => 'tags',
      'label' => 'Mounting Hardware',
      'id' => 'mountingHardware',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'Stainless Fixed Bracket',
        1 => 'Gold U-Channel',
        2 => 'Analok U-Channel (anodized aluminum)',
        3 => 'Stainless U-Channel',
        4 => 'Other bracket types',
        5 => 'Standard mounting',
      ),
    ),
    5 => 
    array (
      'type' => 'tags',
      'label' => 'Hardware Finish',
      'id' => 'hardwareFinish',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'Stainless Steel',
        1 => 'Black',
        2 => 'Gold',
        3 => 'Silver',
        4 => 'Bronze',
        5 => 'Analok (dark/bronze finish)',
        6 => 'Chrome/Stainless Steel',
      ),
    ),
    6 => 
    array (
      'type' => 'number',
      'label' => 'Glass Thickness (mm)',
      'id' => 'glassThicknessMm',
      'stepNumber' => 2,
      'min' => 1.0,
      'step' => 0.1,
    ),
  ),
  'Specialty_Mirrors' => 
  array (
    0 => 
    array (
      'type' => 'tags',
      'label' => 'Shape',
      'id' => 'shape',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Rectangle/Square',
        1 => 'Oval',
        2 => 'Arched',
      ),
    ),
    1 => 
    array (
      'type' => 'tags',
      'label' => 'Frame Type',
      'id' => 'frameType',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Framed',
        1 => 'Frameless',
      ),
    ),
    2 => 
    array (
      'type' => 'tags',
      'label' => 'Frame Color',
      'id' => 'frameColor',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'White',
        1 => 'Black',
        2 => 'Gold',
      ),
    ),
    3 => 
    array (
      'type' => 'tags',
      'label' => 'Edge Finish',
      'id' => 'edgeFinish',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Machine Polished Edges',
        1 => 'Beveled Edge',
      ),
    ),
    4 => 
    array (
      'type' => 'tags',
      'label' => 'Glass Type',
      'id' => 'glassType',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'Copper Free and Lead Free Mirror',
      ),
    ),
    5 => 
    array (
      'type' => 'tags',
      'label' => 'Thickness',
      'id' => 'thickness',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => '6mm',
      ),
    ),
    6 => 
    array (
      'type' => 'tags',
      'label' => 'Tint',
      'id' => 'tint',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'Clear',
        1 => 'Bronze',
        2 => 'Grey (Smoked)',
        3 => 'Black',
      ),
    ),
    7 => 
    array (
      'type' => 'tags',
      'label' => 'Orientation',
      'id' => 'orientation',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'Vertical',
        1 => 'Horizontal',
        2 => 'Full-body',
      ),
    ),
    8 => 
    array (
      'type' => 'number',
      'label' => 'Corner Radius (in)',
      'id' => 'cornerRadiusIn',
      'stepNumber' => 3,
      'min' => 0.0,
      'step' => 0.1,
    ),
    9 => 
    array (
      'type' => 'tags',
      'label' => 'Mounting Method',
      'id' => 'mountingMethod',
      'stepNumber' => 3,
      'options' => 
      array (
        0 => 'Wall-mounted',
        1 => 'Freestanding',
        2 => 'Leaning',
        3 => 'Adhesive',
        4 => 'Hanging',
      ),
    ),
    10 => 
    array (
      'type' => 'tags',
      'label' => 'Lighting',
      'id' => 'lighting',
      'stepNumber' => 4,
      'options' => 
      array (
        0 => 'None',
        1 => 'LED Backlight',
        2 => 'LED Front Light',
      ),
    ),
    11 => 
    array (
      'type' => 'tags',
      'label' => 'LED Color',
      'id' => 'ledColor',
      'stepNumber' => 4,
      'options' => 
      array (
        0 => 'Warm White',
        1 => 'Cool White',
        2 => 'Daylight',
        3 => 'System',
      ),
    ),
    12 => 
    array (
      'type' => 'tags',
      'label' => 'Smart Features',
      'id' => 'smartFeatures',
      'stepNumber' => 4,
      'options' => 
      array (
        0 => 'Touch Dimmer',
        1 => 'Defogger',
        2 => 'Motion Sensor',
        3 => 'System',
      ),
    ),
  ),
  'Specialty_Top Glass' => 
  array (
    0 => 
    array (
      'type' => 'tags',
      'label' => 'Shape',
      'id' => 'shape',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Round',
        1 => 'Rectangle',
        2 => 'Oval',
        3 => 'Square',
        4 => 'Custom shapes',
      ),
    ),
    1 => 
    array (
      'type' => 'tags',
      'label' => 'Edge Finish',
      'id' => 'edgeFinish',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Beveled',
        1 => 'Polished',
        2 => 'Raw',
        3 => 'Beveled edge',
        4 => 'Flat polished edge',
        5 => 'Pencil edge',
      ),
    ),
    2 => 
    array (
      'type' => 'number',
      'label' => 'Corner Radius (in)',
      'id' => 'cornerRadiusIn',
      'stepNumber' => 2,
      'min' => 0.0,
      'step' => 0.1,
    ),
    3 => 
    array (
      'type' => 'tags',
      'label' => 'Mounting Method',
      'id' => 'mountingMethod',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'Wall-mounted',
        1 => 'Stand',
        2 => 'Adhesive',
      ),
    ),
  ),
  'Specialty_Glass Board' => 
  array (
    0 => 
    array (
      'type' => 'tags',
      'label' => 'Shape',
      'id' => 'shape',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Round',
        1 => 'Rectangle',
        2 => 'Oval',
        3 => 'Square',
        4 => 'Custom shapes',
      ),
    ),
    1 => 
    array (
      'type' => 'tags',
      'label' => 'Edge Finish',
      'id' => 'edgeFinish',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Beveled',
        1 => 'Polished',
        2 => 'Raw',
        3 => 'Beveled edge',
        4 => 'Flat polished edge',
        5 => 'Pencil edge',
      ),
    ),
    2 => 
    array (
      'type' => 'number',
      'label' => 'Corner Radius (in)',
      'id' => 'cornerRadiusIn',
      'stepNumber' => 2,
      'min' => 0.0,
      'step' => 0.1,
    ),
    3 => 
    array (
      'type' => 'tags',
      'label' => 'Mounting Method',
      'id' => 'mountingMethod',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'Wall-mounted',
        1 => 'Stand',
        2 => 'Adhesive',
      ),
    ),
  ),
  'Commercial_Storefront' => 
  array (
    0 => 
    array (
      'type' => 'tags',
      'label' => 'Glass Type',
      'id' => 'glassType',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Clear',
        1 => 'Tinted',
        2 => 'Frosted',
        3 => 'Laminated safety glass',
      ),
    ),
    1 => 
    array (
      'type' => 'tags',
      'label' => 'Safety Glass Type',
      'id' => 'safetyGlassType',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Tempered',
        1 => 'Laminated',
        2 => 'Bulletproof',
        3 => 'Laminated safety glass',
      ),
    ),
    2 => 
    array (
      'type' => 'tags',
      'label' => 'Handrail Type',
      'id' => 'handrailType',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'Stainless steel',
        1 => 'Aluminum',
        2 => 'Glass',
      ),
    ),
    3 => 
    array (
      'type' => 'tags',
      'label' => 'Mounting System',
      'id' => 'mountingSystem',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'Clamp',
        1 => 'Bolt',
        2 => 'Adhesive',
        3 => 'Patch fittings (minimalist hardware)',
      ),
    ),
    4 => 
    array (
      'type' => 'tags',
      'label' => 'Hardware Finish',
      'id' => 'hardwareFinish',
      'stepNumber' => 2,
      'options' => 
      array (
        0 => 'Polished Chrome/Stainless Steel',
        1 => 'Matte Black',
        2 => 'Brushed Nickel',
        3 => 'Gold',
      ),
    ),
  ),
  'Commercial_Glass Balcony' => 
  array (
    0 => 
    array (
      'type' => 'tags',
      'label' => 'Safety Glass Type',
      'id' => 'safetyGlassType',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Tempered',
        1 => 'Laminated',
        2 => 'Bulletproof',
      ),
    ),
    1 => 
    array (
      'type' => 'tags',
      'label' => 'Handrail Type',
      'id' => 'handrailType',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Stainless steel',
        1 => 'Aluminum',
        2 => 'Glass',
      ),
    ),
    2 => 
    array (
      'type' => 'tags',
      'label' => 'Mounting System',
      'id' => 'mountingSystem',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Clamp',
        1 => 'Bolt',
        2 => 'Adhesive',
      ),
    ),
  ),
  'Commercial_Stair Railings' => 
  array (
    0 => 
    array (
      'type' => 'tags',
      'label' => 'Safety Glass Type',
      'id' => 'safetyGlassType',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Tempered',
        1 => 'Laminated',
        2 => 'Bulletproof',
      ),
    ),
    1 => 
    array (
      'type' => 'tags',
      'label' => 'Handrail Type',
      'id' => 'handrailType',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Stainless steel',
        1 => 'Aluminum',
        2 => 'Glass',
      ),
    ),
    2 => 
    array (
      'type' => 'tags',
      'label' => 'Mounting System',
      'id' => 'mountingSystem',
      'stepNumber' => 1,
      'options' => 
      array (
        0 => 'Clamp',
        1 => 'Bolt',
        2 => 'Adhesive',
      ),
    ),
  ),
  'Windows_Sliding_stepNames' => 
  array (
    1 => 'Window Type',
    2 => 'Sliding System & Size',
    3 => 'Frame & Glass',
    4 => 'Hardware & Accessories',
  ),
  'Windows_Awning_stepNames' => 
  array (
    1 => 'Basic Options',
    2 => 'Configuration & Details',
  ),
  'Windows_Casement_stepNames' => 
  array (
    1 => 'Basic Options',
    2 => 'Panel Configuration',
    3 => 'Advanced Options',
  ),
  'Windows_Fixed Glass_stepNames' => 
  array (
    1 => 'Basic Options',
    2 => 'Installation & Details',
  ),
  'Doors_Sliding_stepNames' => 
  array (
    1 => 'Basic Options',
    2 => 'Operation & Configuration',
    3 => 'Hardware & Features',
  ),
  'Doors_Frameless_stepNames' => 
  array (
    1 => 'Basic Options',
    2 => 'Panel Configuration',
    3 => 'Design & Hardware',
    4 => 'Glass Treatment & Installation',
  ),
  'Partitions_Frameless Glass_stepNames' => 
  array (
    1 => 'Basic Options',
    2 => 'Configuration & Hardware',
  ),
  'Partitions_Shower Enclosure_stepNames' => 
  array (
    1 => 'Basic Options',
    2 => 'Glass Treatment',
    3 => 'Hardware & Installation',
  ),
  'Partitions_Fixed Glass_stepNames' => 
  array (
    1 => 'Basic Options',
    2 => 'Configuration & Hardware',
  ),
  'Specialty_Mirrors_stepNames' => 
  array (
    1 => 'Shape & Frame',
    2 => 'Glass & Finish',
    3 => 'Size & Installation',
    4 => 'Lighting & Features',
  ),
  'Specialty_Top Glass_stepNames' => 
  array (
    1 => 'Basic Options',
    2 => 'Details & Installation',
  ),
  'Specialty_Glass Board_stepNames' => 
  array (
    1 => 'Basic Options',
    2 => 'Details & Installation',
  ),
  'Commercial_Storefront_stepNames' => 
  array (
    1 => 'Basic Options',
    2 => 'Hardware & Installation',
  ),
  'Commercial_Glass Balcony_stepNames' => 
  array (
    1 => 'Basic Options',
  ),
  'Commercial_Stair Railings_stepNames' => 
  array (
    1 => 'Basic Options',
  ),
);
