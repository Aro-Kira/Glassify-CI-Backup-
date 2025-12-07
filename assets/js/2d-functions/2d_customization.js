// --- DOM ELEMENTS AND STATE ---
const btnCustomize = document.getElementById('btn-customize');
const btnStandard = document.getElementById('btn-standard');
const customWrapper = document.getElementById('custom-wrapper');
const standardWrapper = document.getElementById('standard-wrapper');
const priceBox = document.getElementById('price-box');
const standardSubtitle = document.getElementById('standard-subtitle');
const nextBtn = document.getElementById('next-btn');
const backBtn = document.getElementById('back-btn');
const backGroup = document.getElementById('back-group');
const step1 = document.getElementById('step-1');
const step2 = document.getElementById('step-2');
const step3 = document.getElementById('step-3');
const crumbMain = document.getElementById('crumb-main');
const breadcrumbsContainer = document.getElementById('breadcrumbs-container');
const nextNote = document.getElementById('next-note');
const backNote = document.getElementById('back-note');
const inputHeight = document.getElementById('input-height');
const btnUnitHeight = document.getElementById('btn-unit-height');
const inputWidth = document.getElementById('input-width');
const btnUnitWidth = document.getElementById('btn-unit-width');
const shapeCards = document.querySelectorAll('.option-card[data-shape]');

// Modal elements
const uploadModal = document.getElementById('upload-modal');
const openModalBtn = document.getElementById('open-modal-btn');
const modalCloseBtn = document.getElementById('modal-close-btn');
const modalCancelBtn = document.getElementById('modal-cancel-btn');
const modalDoneBtn = document.getElementById('modal-done-btn');
const browseFilesBtn = document.getElementById('browse-files-btn');
const fileInput = document.getElementById('file-input');
const uploadedFilesContainer = document.getElementById('uploaded-files-container');
const dropzone = document.getElementById('dropzone');
let uploadedFiles = [];
const MAX_FILE_SIZE_MB = 25;


// --- APPLICATION STATE ---
let currentStep = 1;
let isStandardMode = false;

// CUSTOM STATE VARIABLES
let currentShape = 'rectangle';
let currentGlassType = 'tempered';
let currentThickness = '5mm';
let currentEdgeWork = 'flat-polish';
let currentFrameType = 'vinyl';
let currentDimensions = {
    height: { value: 45, unit: 'in' },
    width: { value: 35, unit: 'in' }
};

const unitMap = {
    'in': { name: 'Inches', toMm: 25.4 },
    'cm': { name: 'Centimeters', toMm: 10 },
    'mm': { name: 'Millimeters', toMm: 1 }
};


// --- KONVA.JS VISUALIZATION LOGIC ---

const KONVA_CONTAINER_ID = 'konva-container';
const konvaWrapper = document.getElementById(KONVA_CONTAINER_ID);
const STAGE_SIZE = konvaWrapper.offsetWidth;

const PADDING = 40;
const DRAWING_SIZE = STAGE_SIZE - PADDING * 2;
const DIM_OFFSET = 15;

// --- VISUAL CONFIGURATION ---
const glassStyles = {
    'tempered': { fill: '#E0F2F1', opacity: 0.9 },
    'laminated': { fill: '#CFD8DC', opacity: 0.95 },
    'double': { fill: '#B2DFDB', opacity: 0.9 },
    'low-e': { fill: '#Dcedc8', opacity: 0.85 },
    'tinted': { fill: '#546E7A', opacity: 0.7 },
    'frosted': { fill: '#FFFFFF', opacity: 0.95 }
};

const frameStyles = {
    'vinyl': { color: '#333333', width: 4 },
    'aluminum': { color: '#90A4AE', width: 3 },
    'wood': { color: '#795548', width: 6 }
};

// Initialize Konva
const stage = new Konva.Stage({
    container: KONVA_CONTAINER_ID,
    width: STAGE_SIZE,
    height: STAGE_SIZE,
});

const layer = new Konva.Layer();
stage.add(layer);

/**
 * Renders the 2D window figure.
 */
function renderWindow(widthIn, heightIn, unit, shape, glassType, thickness, edgeWork, frameType) {
    layer.destroyChildren();

    // Ratio and Scale
    const actualRatio = widthIn / heightIn;
    let windowWidth, windowHeight;

    if (actualRatio > 1) {
        windowWidth = DRAWING_SIZE;
        windowHeight = DRAWING_SIZE / actualRatio;
    } else {
        windowHeight = DRAWING_SIZE;
        windowWidth = DRAWING_SIZE * actualRatio;
    }

    const offsetX = (STAGE_SIZE - windowWidth) / 2;
    const offsetY = (STAGE_SIZE - windowHeight) / 2;

    // Styles
    const gStyle = glassStyles[glassType] || glassStyles['tempered'];
    const fStyle = frameStyles[frameType] || frameStyles['vinyl'];

    // Draw Frame
    const frame = new Konva.Rect({
        x: offsetX,
        y: offsetY,
        width: windowWidth,
        height: windowHeight,
        fill: gStyle.fill,
        opacity: gStyle.opacity,
        stroke: fStyle.color,
        strokeWidth: fStyle.width,
        listening: false,
    });
    layer.add(frame);

    // Draw Interior Panels
    const paneWidth = windowWidth / 3;
    const paneStrokeWidth = Math.max(1, fStyle.width - 2);

    for (let i = 1; i < 3; i++) {
        const dividerX = offsetX + paneWidth * i;
        layer.add(new Konva.Line({
            points: [dividerX, offsetY, dividerX, offsetY + windowHeight],
            stroke: fStyle.color,
            strokeWidth: paneStrokeWidth,
            listening: false,
        }));
        const ventY = offsetY + windowHeight * 0.25;
        layer.add(new Konva.Line({
            points: [dividerX - paneWidth, ventY, dividerX, ventY],
            stroke: fStyle.color,
            strokeWidth: paneStrokeWidth,
            listening: false,
        }));
        layer.add(new Konva.Circle({
            x: dividerX - (paneWidth / 2),
            y: offsetY + windowHeight * 0.75,
            radius: 3,
            fill: fStyle.color,
            listening: false,
        }));
    }
    const ventY = offsetY + windowHeight * 0.25;
    layer.add(new Konva.Line({
        points: [offsetX + paneWidth * 2, ventY, offsetX + windowWidth, ventY],
        stroke: fStyle.color,
        strokeWidth: paneStrokeWidth,
        listening: false,
    }));


    // Draw Dimensions
    const dimColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-dark').trim();

    // Width
    layer.add(new Konva.Line({ points: [offsetX, offsetY, offsetX, offsetY - DIM_OFFSET - 5], stroke: dimColor, strokeWidth: 1 }));
    layer.add(new Konva.Line({ points: [offsetX + windowWidth, offsetY, offsetX + windowWidth, offsetY - DIM_OFFSET - 5], stroke: dimColor, strokeWidth: 1 }));
    layer.add(new Konva.Line({ points: [offsetX, offsetY - DIM_OFFSET, offsetX + windowWidth, offsetY - DIM_OFFSET], stroke: dimColor, strokeWidth: 1, dash: [4, 4] }));
    layer.add(new Konva.Text({
        x: offsetX + windowWidth / 2,
        y: offsetY - DIM_OFFSET - 12,
        text: `${widthIn}${unit}`,
        fontSize: 10,
        fontFamily: 'Montserrat',
        fill: dimColor,
        offsetX: (widthIn.toString().length * 6) / 2,
        listening: false,
    }));

    // Height
    layer.add(new Konva.Line({ points: [offsetX + windowWidth, offsetY, offsetX + windowWidth + DIM_OFFSET + 5, offsetY], stroke: dimColor, strokeWidth: 1 }));
    layer.add(new Konva.Line({ points: [offsetX + windowWidth, offsetY + windowHeight, offsetX + windowWidth + DIM_OFFSET + 5, offsetY + windowHeight], stroke: dimColor, strokeWidth: 1 }));
    layer.add(new Konva.Line({ points: [offsetX + windowWidth + DIM_OFFSET, offsetY, offsetX + windowWidth + DIM_OFFSET, offsetY + windowHeight], stroke: dimColor, strokeWidth: 1, dash: [4, 4] }));
    layer.add(new Konva.Text({
        x: offsetX + windowWidth + DIM_OFFSET + 8,
        y: offsetY + windowHeight / 2,
        text: `${heightIn}${unit}`,
        fontSize: 10,
        fontFamily: 'Montserrat',
        fill: dimColor,
        rotation: 90,
        offsetX: (heightIn.toString().length * 6) / 2,
        listening: false,
    }));

    // Annotations
    const formatEdge = edgeWork.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    const annotationText = `Thickness: ${thickness}  |  Edge: ${formatEdge}`;

    layer.add(new Konva.Text({
        x: offsetX + windowWidth / 2,
        y: offsetY + windowHeight + 15,
        text: annotationText,
        fontSize: 11,
        fontStyle: 'bold',
        fontFamily: 'Montserrat',
        fill: '#555',
        offsetX: (annotationText.length * 6) / 2,
        listening: false,
    }));

    layer.draw();
}

// --- INITIAL RENDER & UPDATES ---
function renderCustomState() {
    // 1. Draw the visual representation
    renderWindow(
        currentDimensions.width.value,
        currentDimensions.height.value,
        currentDimensions.width.unit,
        currentShape,
        currentGlassType,
        currentThickness,
        currentEdgeWork,
        currentFrameType
    );

    // 2. NEW: Update the estimated price immediately
    updateRealTimePriceDisplay();
}

// Helper to render standard size with default "Standard" aesthetics
function renderStandardState(width, height) {
    renderWindow(
        width,
        height,
        'in', // Standard uses inches
        'rectangle', // Force Rectangle
        'tempered', // Force Standard Glass
        '5mm',      // Force Standard Thickness
        'flat-polish', // Force Standard Edge
        'vinyl'     // Force Standard Frame
    );
}

window.onload = renderCustomState;


// --- TOGGLE MODE LOGIC (UPDATED) ---

btnCustomize.addEventListener('click', () => {
    if (!isStandardMode) return;
    isStandardMode = false;

    // UI Updates
    btnCustomize.classList.add('active'); btnCustomize.classList.remove('inactive');
    btnStandard.classList.remove('active'); btnStandard.classList.add('inactive');
    customWrapper.classList.remove('hidden-step'); standardWrapper.classList.add('hidden-step');
    priceBox.classList.remove('hidden-step'); standardSubtitle.classList.add('hidden-step');
    updateBreadcrumbs(currentStep);

    // DRAWING UPDATE: Restore the User's Custom State
    renderCustomState();
});

btnStandard.addEventListener('click', () => {
    if (isStandardMode) return;
    isStandardMode = true;

    // UI Updates
    btnStandard.classList.add('active'); btnStandard.classList.remove('inactive');
    btnCustomize.classList.remove('active'); btnCustomize.classList.add('inactive');
    standardWrapper.classList.remove('hidden-step'); customWrapper.classList.add('hidden-step');
    priceBox.classList.add('hidden-step'); standardSubtitle.classList.remove('hidden-step');
    resetBreadcrumbsToStandard();

    // DRAWING UPDATE: Force Standard Look
    // Get the currently selected standard card values
    const activeStdCard = document.querySelector('#standard-wrapper .option-card.active');
    if (activeStdCard) {
        const h = activeStdCard.dataset.height;
        const w = activeStdCard.dataset.width;
        renderStandardState(w, h);
    }
});


// --- STANDARD BUTTON LISTENERS (NEW) ---
const standardCards = document.querySelectorAll('#standard-wrapper .option-card');
standardCards.forEach(card => {
    card.addEventListener('click', function () {
        // Visual toggle
        standardCards.forEach(c => c.classList.remove('active'));
        this.classList.add('active');

        // Render Standard
        const h = this.dataset.height;
        const w = this.dataset.width;
        renderStandardState(w, h);
    });
});


// --- CUSTOM EVENT LISTENERS (EXISTING) ---

function updateDimensions(type, value, unit) {
    if (isNaN(value) || value <= 0) return;
    currentDimensions[type] = { value: parseFloat(value), unit };
    renderCustomState(); // Call the wrapper function
}

// Input Listeners
inputHeight.addEventListener('input', (e) => {
    updateDimensions('height', e.target.value, btnUnitHeight.dataset.currentUnit);
});
inputWidth.addEventListener('input', (e) => {
    updateDimensions('width', e.target.value, btnUnitWidth.dataset.currentUnit);
});

// Unit Dropdowns
document.getElementById('dropdown-height').addEventListener('click', (e) => {
    if (e.target.classList.contains('unit-option')) {
        updateDimensions('height', inputHeight.value, e.target.dataset.value);
    }
});
document.getElementById('dropdown-width').addEventListener('click', (e) => {
    if (e.target.classList.contains('unit-option')) {
        updateDimensions('width', inputWidth.value, e.target.dataset.value);
    }
});

// Shape Selection
shapeCards.forEach(card => {
    card.addEventListener('click', function () {
        const section = this.closest('div[class$="-section"]');
        if (section) section.querySelectorAll('.option-card').forEach(sib => sib.classList.remove('active'));
        this.classList.add('active');
        currentShape = this.dataset.shape;
        renderCustomState();
    });
});

// Type & Thickness
const glassTypeCards = document.querySelectorAll('.option-card[data-glass-type]');
glassTypeCards.forEach(card => {
    card.addEventListener('click', function () {
        const section = this.closest('.type-section');
        section.querySelectorAll('.option-card').forEach(sib => sib.classList.remove('active'));
        this.classList.add('active');
        currentGlassType = this.dataset.glassType;
        renderCustomState();
    });
});

const thicknessCards = document.querySelectorAll('.option-card[data-thickness]');
thicknessCards.forEach(card => {
    card.addEventListener('click', function () {
        const section = this.closest('.thickness-section');
        section.querySelectorAll('.option-card').forEach(sib => sib.classList.remove('active'));
        this.classList.add('active');
        currentThickness = this.dataset.thickness;
        renderCustomState();
    });
});

// Edge & Frame
const edgeCards = document.querySelectorAll('.option-card[data-edge-work]');
edgeCards.forEach(card => {
    card.addEventListener('click', function () {
        const section = this.closest('.edge-section');
        section.querySelectorAll('.option-card').forEach(sib => sib.classList.remove('active'));
        this.classList.add('active');
        currentEdgeWork = this.dataset.edgeWork;
        renderCustomState();
    });
});

const frameCards = document.querySelectorAll('.option-card[data-frame-type]');
frameCards.forEach(card => {
    card.addEventListener('click', function () {
        const section = this.closest('.frame-section');
        section.querySelectorAll('.option-card').forEach(sib => sib.classList.remove('active'));
        this.classList.add('active');
        currentFrameType = this.dataset.frameType;
        renderCustomState();
    });
});

// --- GENERAL UTILITIES (Unit Setup, Modal, Navigation) ---

function setupUnitDropdown(btnId, dropdownId, inputId, dimensionType) {
    const btn = document.getElementById(btnId);
    const dropdown = document.getElementById(dropdownId);
    const input = document.getElementById(inputId);
    btn.addEventListener('click', (e) => { e.stopPropagation(); document.querySelectorAll('.unit-dropdown').forEach(d => d !== dropdown && d.classList.add('hidden-step')); dropdown.classList.toggle('hidden-step'); });
    dropdown.querySelectorAll('.unit-option').forEach(opt => {
        opt.addEventListener('click', (e) => {
            e.stopPropagation();
            const targetUnit = opt.dataset.value;
            const currentUnit = btn.dataset.currentUnit;
            btn.innerHTML = `${unitMap[targetUnit].name} <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 12l4 4 4-4"></path></svg>`;
            const val = parseFloat(input.value);
            if (!isNaN(val)) { input.value = Math.round((val * unitMap[currentUnit].toMm / unitMap[targetUnit].toMm) * 100) / 100; }
            btn.dataset.currentUnit = targetUnit;
            const otherType = dimensionType === 'height' ? 'width' : 'height';
            const otherBtn = document.getElementById(`btn-unit-${otherType}`);
            const otherInput = document.getElementById(`input-${otherType}`);
            if (otherBtn.dataset.currentUnit !== targetUnit) {
                const otherVal = parseFloat(otherInput.value);
                if (!isNaN(otherVal)) { otherInput.value = Math.round((otherVal * unitMap[otherBtn.dataset.currentUnit].toMm / unitMap[targetUnit].toMm) * 100) / 100; }
                otherBtn.dataset.currentUnit = targetUnit;
                otherBtn.innerHTML = `${unitMap[targetUnit].name} <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 12l4 4 4-4"></path></svg>`;
            }
            updateDimensions('height', inputHeight.value, targetUnit);
            dropdown.classList.add('hidden-step');
        });
    });
}
setupUnitDropdown('btn-unit-height', 'dropdown-height', 'input-height', 'height');
setupUnitDropdown('btn-unit-width', 'dropdown-width', 'input-width', 'width');

document.addEventListener('click', (e) => {
    if (!e.target.closest('.unit-control')) document.querySelectorAll('.unit-dropdown').forEach(d => d.classList.add('hidden-step'));
    if (e.target === uploadModal) closeUploadModal();
});

// Navigation Logic (single next/back handler covers standard & custom flows)
nextBtn.addEventListener('click', () => {
    // Standard mode: move to standard step or finalize
    if (isStandardMode) {
        if (currentStep < 2) {
            goToStep(2);
        } else {
            // Finalize standard order
            console.log('Finalizing Standard Order...');
            showOrderSummary();
            logOrderSummary();
        }
        return;
    }

    // Custom mode normal flow
    if (currentStep === 1) goToStep(2);
    else if (currentStep === 2) goToStep(3);
    else {
        // Step 3 -> Finalize custom order
        console.log('Finalizing Custom Order...');
        showOrderSummary();
        logOrderSummary();
    }
});


backBtn.addEventListener('click', () => {
    if (currentStep === 2) goToStep(1);
    else if (currentStep === 3) goToStep(2);
});

function goToStep(targetStep) {
    // Hide all steps first
    [step1, step2, step3].forEach(s => s.classList.add('hidden-step'));

    // Show the target step
    if (targetStep === 1) step1.classList.remove('hidden-step');
    if (targetStep === 2) step2.classList.remove('hidden-step');
    if (targetStep === 3) step3.classList.remove('hidden-step');

    // Update UI
    updateActionArea(targetStep);
    updateBreadcrumbs(targetStep);

    // Update currentStep AFTER UI
    currentStep = targetStep;
}


function updateActionArea(step) {
    if (step === 1) { backGroup.classList.add('hidden-step'); nextBtn.innerHTML = `Next <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>`; nextNote.innerText = 'Glass Type & Thickness'; backNote.innerText = ''; }
    if (step === 2) { backGroup.classList.remove('hidden-step'); nextBtn.innerHTML = `Next <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>`; backNote.innerText = 'Glass Shape'; nextNote.innerText = 'Edge Work & Frame Type'; }
    if (step === 3) { backGroup.classList.remove('hidden-step'); nextBtn.innerHTML = `Finalize Order <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>`; backNote.innerText = 'Type & Thickness'; nextNote.innerText = ''; }
}

function updateBreadcrumbs(step) {
    crumbMain.innerText = 'Glass Shape'; crumbMain.classList.add('active');
    removeCrumb('crumb-step2'); removeCrumb('crumb-step3');
    if (step >= 2) { crumbMain.classList.remove('active'); addBreadcrumb('Type & Thickness', 'crumb-step2', step === 2); }
    if (step === 3) { document.getElementById('crumb-step2')?.classList.remove('active'); addBreadcrumb('Edge Work & Frame', 'crumb-step3', true); }
}

function resetBreadcrumbsToStandard() {
    crumbMain.innerText = 'Standard';
    crumbMain.classList.add('active');
    removeCrumb('crumb-step2');
    removeCrumb('crumb-step3');
    currentStep = 1; // Reset currentStep for Standard Mode
}


function addBreadcrumb(text, id, isActive) {
    if (document.getElementById(id)) return;
    const newChevron = document.createElement('span'); newChevron.className = 'chevron-right'; newChevron.id = 'chevron-' + id;
    const newCrumb = document.createElement('span'); newCrumb.className = isActive ? 'active' : ''; newCrumb.id = id; newCrumb.innerText = text;
    breadcrumbsContainer.appendChild(newChevron); breadcrumbsContainer.appendChild(newCrumb);
}

function removeCrumb(id) {
    document.getElementById(id)?.remove();
    document.getElementById('chevron-' + id)?.remove();
}

// Modal & File Logic
function closeUploadModal() { uploadModal.classList.add('hidden-step'); }
openModalBtn.addEventListener('click', () => { uploadModal.classList.remove('hidden-step'); });
modalCloseBtn.addEventListener('click', closeUploadModal);
modalCancelBtn.addEventListener('click', closeUploadModal);
modalDoneBtn.addEventListener('click', closeUploadModal);

browseFilesBtn.addEventListener('click', () => { fileInput.click(); });
fileInput.addEventListener('change', (e) => { handleFiles(e.target.files); fileInput.value = ''; });

dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.add('drag-over'); });
dropzone.addEventListener('dragleave', (e) => { e.preventDefault(); dropzone.classList.remove('drag-over'); });
dropzone.addEventListener('drop', (e) => { e.preventDefault(); dropzone.classList.remove('drag-over'); handleFiles(e.dataTransfer.files); });

function handleFiles(files) {
    if (files.length === 0) return;
    const placeholder = uploadedFilesContainer.querySelector('.placeholder-text');
    if (placeholder) placeholder.remove();

    Array.from(files).forEach(file => {
        const fileExtension = file.name.split('.').pop().toLowerCase();
        if (!['jpg', 'jpeg', 'png', 'pdf'].includes(fileExtension)) {
            console.error(`File type not supported`); return;
        }
        const newFile = {
            id: Date.now() + Math.random(), name: file.name, size: file.size, progress: 0,
            status: 'uploading', isError: file.size > MAX_FILE_SIZE_MB * 1024 * 1024, extension: fileExtension
        };
        uploadedFiles.push(newFile);
        renderFileItem(newFile);
        if (newFile.isError) { newFile.status = 'error'; updateFileItem(newFile); }
        else { simulateUpload(newFile); }
    });
}

function simulateUpload(file) {
    let progress = 0;
    const uploadTimer = setInterval(() => {
        progress += 2;
        if (progress >= 100) { clearInterval(uploadTimer); file.progress = 100; file.status = 'completed'; }
        else { file.progress = progress; }
        updateFileItem(file);
    }, 30);
}

function getFileIconSvg(ext) {
    if (ext === 'pdf') return `<svg viewBox="0 0 24 24" stroke="#CC3333" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>`;
    if (ext === 'png') return `<svg viewBox="0 0 24 24" stroke="#00A78F" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><rect x="8" y="12" width="8" height="8" rx="1" fill="#fff" stroke="#00A78F"/><circle cx="12" cy="16" r="2" fill="#00A78F"/></svg>`;
    return `<svg viewBox="0 0 24 24" stroke="#E69500" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><circle cx="9.5" cy="9.5" r="1.5"/><polyline points="15 8 22 17 17 22 10 16"/></svg>`;
}

function renderFileItem(file) {
    const item = document.createElement('div');
    item.className = 'file-item';
    item.id = `file-item-${file.id}`;
    item.innerHTML = `<div class="file-icon-wrapper">${getFileIconSvg(file.extension)}</div><div class="file-details"><div class="file-name-progress"><span class="file-name-text">${file.name}</span><span class="file-status status-${file.status}">${file.status}</span></div><div class="progress-bar-container"><div class="progress-bar" style="width: ${file.progress}%;"></div></div></div><button class="cancel-btn" data-file-id="${file.id}">Cancel</button>`;
    uploadedFilesContainer.appendChild(item);
    item.querySelector('.cancel-btn').addEventListener('click', deleteFile);
}

function updateFileItem(file) {
    const item = document.getElementById(`file-item-${file.id}`);
    if (!item) return;
    item.querySelector('.progress-bar').style.width = `${file.progress}%`;
    item.querySelector('.file-status').textContent = file.status === 'error' ? 'Error' : (file.status === 'completed' ? 'Completed' : `${file.progress}%`);
    item.querySelector('.file-status').className = `file-status status-${file.status}`;
}

function deleteFile(e) {
    const fileId = parseFloat(e.target.dataset.fileId);
    uploadedFiles = uploadedFiles.filter(file => file.id !== fileId);
    document.getElementById(`file-item-${fileId}`).remove();
    if (uploadedFiles.length === 0) uploadedFilesContainer.innerHTML = '<p class="placeholder-text">No files uploaded yet.</p>';
}

// --- PRICING LOGIC (Philippines Context) ---
// Price per square inch based on product base price
const BASE_PRICE_PER_SQ_IN = productBasePrice / 100; // Convert base price to per sq inch rate

const pricingDatabase = {
    baseRatePerSqIn: BASE_PRICE_PER_SQ_IN,
    
    // Price additions (in Pesos) for each option
    prices: {
        // Shapes - Additional cost based on cutting complexity
        shapes: {
            'rectangle': { multiplier: 1.0, label: 'Rectangle', desc: 'Standard' },
            'square': { multiplier: 1.05, label: 'Square', desc: '+5%' },
            'triangle': { multiplier: 1.15, label: 'Triangle', desc: '+15%' },
            'pentagon': { multiplier: 1.25, label: 'Pentagon', desc: '+25%' }
        },
        
        // Glass Types - Price per sq inch multiplier
        glassTypes: {
            'tempered': { multiplier: 1.0, label: 'Tempered', desc: 'Standard Safety' },
            'laminated': { multiplier: 1.35, label: 'Laminated', desc: '+35%' },
            'double': { multiplier: 1.5, label: 'Double Pane', desc: '+50%' },
            'low-e': { multiplier: 1.4, label: 'Low-E', desc: '+40%' },
            'tinted': { multiplier: 1.2, label: 'Tinted', desc: '+20%' },
            'frosted': { multiplier: 1.15, label: 'Frosted', desc: '+15%' }
        },
        
        // Thickness - Additional price per mm above 3mm
        thickness: {
            '3mm': { multiplier: 0.85, label: '3mm', desc: '-15%' },
            '5mm': { multiplier: 1.0, label: '5mm', desc: 'Standard' },
            '6mm': { multiplier: 1.1, label: '6mm', desc: '+10%' },
            '8mm': { multiplier: 1.25, label: '8mm', desc: '+25%' },
            '10mm': { multiplier: 1.4, label: '10mm', desc: '+40%' },
            '12mm': { multiplier: 1.6, label: '12mm', desc: '+60%' }
        },
        
        // Frame Types - Fixed price additions
        frames: {
            'vinyl': { price: 0, multiplier: 1.0, label: 'Vinyl', desc: 'Included' },
            'aluminum': { price: 350, multiplier: 1.15, label: 'Aluminum', desc: '+₱350' },
            'wood': { price: 800, multiplier: 1.35, label: 'Wood', desc: '+₱800' }
        },
        
        // Edge Work - Fixed price additions
        edges: {
            'flat-polish': { price: 0, label: 'Flat Polish', desc: 'Included' },
            'metered': { price: 250, label: 'Metered', desc: '+₱250' },
            'beveled': { price: 550, label: 'Beveled', desc: '+₱550' },
            'seamed': { price: 150, label: 'Seamed', desc: '+₱150' }
        }
    },
    
    minimumPrice: 1500 // Minimum order price
};

// Store calculated price breakdown
let priceBreakdown = {
    baseArea: 0,
    shapeAddon: 0,
    typeAddon: 0,
    thicknessAddon: 0,
    frameAddon: 0,
    edgeAddon: 0,
    total: 0
};

function calculateTotal() {
    // 1. Convert dimensions to Inches for calculation
    let h_in = currentDimensions.height.value;
    let w_in = currentDimensions.width.value;
    const unit = currentDimensions.height.unit;

    if (unit === 'cm') { h_in /= 2.54; w_in /= 2.54; }
    if (unit === 'mm') { h_in /= 25.4; w_in /= 25.4; }

    const areaSqIn = h_in * w_in;

    // 2. Calculate base area cost
    const baseAreaCost = areaSqIn * pricingDatabase.baseRatePerSqIn;
    priceBreakdown.baseArea = baseAreaCost;

    // 3. Get multipliers and calculate addons
    const shapeData = pricingDatabase.prices.shapes[currentShape] || pricingDatabase.prices.shapes['rectangle'];
    const typeData = pricingDatabase.prices.glassTypes[currentGlassType] || pricingDatabase.prices.glassTypes['tempered'];
    const thickData = pricingDatabase.prices.thickness[currentThickness] || pricingDatabase.prices.thickness['5mm'];
    const frameData = pricingDatabase.prices.frames[currentFrameType] || pricingDatabase.prices.frames['vinyl'];
    const edgeData = pricingDatabase.prices.edges[currentEdgeWork] || pricingDatabase.prices.edges['flat-polish'];

    // Calculate individual addon costs
    priceBreakdown.shapeAddon = baseAreaCost * (shapeData.multiplier - 1);
    priceBreakdown.typeAddon = baseAreaCost * (typeData.multiplier - 1);
    priceBreakdown.thicknessAddon = baseAreaCost * (thickData.multiplier - 1);
    priceBreakdown.frameAddon = frameData.price + (baseAreaCost * (frameData.multiplier - 1));
    priceBreakdown.edgeAddon = edgeData.price;

    // 4. Calculate total
    let total = baseAreaCost * shapeData.multiplier * typeData.multiplier * thickData.multiplier * frameData.multiplier;
    total += frameData.price + edgeData.price;

    // Apply minimum price constraint
    if (total < pricingDatabase.minimumPrice) {
        total = pricingDatabase.minimumPrice;
    }

    priceBreakdown.total = total;
    return total;
}

// Format price in PHP
function formatPrice(amount) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2
    }).format(amount);
}

// --- REAL-TIME PRICE UPDATE WITH BREAKDOWN ---
function updateRealTimePriceDisplay() {
    // 1. Calculate total (also updates priceBreakdown)
    const total = calculateTotal();

    // 2. Update main price display
    const priceValue = document.getElementById('total-price');
    if (priceValue) {
        priceValue.textContent = formatPrice(total);
    }

    // 3. Update breakdown details
    updatePriceBreakdown();
}

function updatePriceBreakdown() {
    // Get pricing data
    const shapeData = pricingDatabase.prices.shapes[currentShape];
    const typeData = pricingDatabase.prices.glassTypes[currentGlassType];
    const thickData = pricingDatabase.prices.thickness[currentThickness];
    const frameData = pricingDatabase.prices.frames[currentFrameType];
    const edgeData = pricingDatabase.prices.edges[currentEdgeWork];

    // Update base area cost
    const costArea = document.getElementById('cost-area');
    if (costArea) costArea.textContent = formatPrice(priceBreakdown.baseArea);

    // Update shape
    const labelShape = document.getElementById('label-shape');
    const costShape = document.getElementById('cost-shape');
    if (labelShape) labelShape.textContent = shapeData.label;
    if (costShape) {
        costShape.textContent = priceBreakdown.shapeAddon > 0 
            ? '+' + formatPrice(priceBreakdown.shapeAddon) 
            : 'Included';
    }

    // Update glass type
    const labelType = document.getElementById('label-type');
    const costType = document.getElementById('cost-type');
    if (labelType) labelType.textContent = typeData.label;
    if (costType) {
        costType.textContent = priceBreakdown.typeAddon > 0 
            ? '+' + formatPrice(priceBreakdown.typeAddon) 
            : 'Standard';
    }

    // Update thickness
    const labelThickness = document.getElementById('label-thickness');
    const costThickness = document.getElementById('cost-thickness');
    if (labelThickness) labelThickness.textContent = thickData.label;
    if (costThickness) {
        if (priceBreakdown.thicknessAddon > 0) {
            costThickness.textContent = '+' + formatPrice(priceBreakdown.thicknessAddon);
        } else if (priceBreakdown.thicknessAddon < 0) {
            costThickness.textContent = formatPrice(priceBreakdown.thicknessAddon);
        } else {
            costThickness.textContent = 'Standard';
        }
    }

    // Update frame
    const labelFrame = document.getElementById('label-frame');
    const costFrame = document.getElementById('cost-frame');
    if (labelFrame) labelFrame.textContent = frameData.label;
    if (costFrame) {
        costFrame.textContent = priceBreakdown.frameAddon > 0 
            ? '+' + formatPrice(priceBreakdown.frameAddon) 
            : 'Included';
    }

    // Update edge work
    const labelEdge = document.getElementById('label-edge');
    const costEdge = document.getElementById('cost-edge');
    if (labelEdge) labelEdge.textContent = edgeData.label;
    if (costEdge) {
        costEdge.textContent = priceBreakdown.edgeAddon > 0 
            ? '+' + formatPrice(priceBreakdown.edgeAddon) 
            : 'Included';
    }
}

// Toggle price breakdown visibility
const breakdownToggle = document.getElementById('breakdown-toggle');
const breakdownDetails = document.getElementById('breakdown-details');

if (breakdownToggle && breakdownDetails) {
    breakdownToggle.addEventListener('click', () => {
        breakdownDetails.classList.toggle('hidden-step');
        breakdownToggle.classList.toggle('active');
    });
}

console.log("Pricing system initialized with base rate:", pricingDatabase.baseRatePerSqIn);

// --- KONVA IMAGE EXPORT FUNCTIONS ---

// Generate high-quality image from Konva stage
function getKonvaImageData(pixelRatio = 3) {
    return stage.toDataURL({ 
        pixelRatio: pixelRatio,
        mimeType: 'image/png'
    });
}

// Store the design image data globally for cart submission
let currentDesignImageData = null;

// --- SUMMARY VIEW LOGIC ---

function showOrderSummary() {
    // 1. Hide Builder UI
    customWrapper.classList.add('hidden-step');
    standardWrapper.classList.add('hidden-step');
    priceBox.classList.add('hidden-step');
    document.querySelector('.build-toggle').classList.add('hidden-step');
    document.getElementById('standard-subtitle').classList.add('hidden-step');

    // --- Hide Related Products & Testimonials ---
    document.getElementById('related-products-section').classList.add('hidden-step');
    document.getElementById('testimonials-section').classList.add('hidden-step');

    // 2. Show Summary UI
    const summaryWrapper = document.getElementById('summary-wrapper');
    summaryWrapper.classList.remove('hidden-step');

    // 3. Generate and display design preview image
    currentDesignImageData = getKonvaImageData(3);
    const designPreviewImg = document.getElementById('design-preview-img');
    if (designPreviewImg) {
        designPreviewImg.src = currentDesignImageData;
    }

    // 4. Update Summary Data with price breakdown
    const shapeData = pricingDatabase.prices.shapes[currentShape];
    const typeData = pricingDatabase.prices.glassTypes[currentGlassType];
    const thickData = pricingDatabase.prices.thickness[currentThickness];
    const frameData = pricingDatabase.prices.frames[currentFrameType];
    const edgeData = pricingDatabase.prices.edges[currentEdgeWork];

    // Shape
    document.getElementById('sum-shape').textContent = shapeData.label;
    const sumShapePrice = document.getElementById('sum-shape-price');
    if (sumShapePrice) {
        sumShapePrice.textContent = priceBreakdown.shapeAddon > 0 
            ? '+' + formatPrice(priceBreakdown.shapeAddon) 
            : shapeData.desc;
    }

    // Dimensions
    document.getElementById('sum-dim').textContent =
        `${currentDimensions.width.value}${currentDimensions.width.unit} × ${currentDimensions.height.value}${currentDimensions.height.unit}`;
    const sumDimPrice = document.getElementById('sum-dim-price');
    if (sumDimPrice) {
        sumDimPrice.textContent = 'Base: ' + formatPrice(priceBreakdown.baseArea);
    }

    // Glass Type
    document.getElementById('sum-type').textContent = typeData.label;
    const sumTypePrice = document.getElementById('sum-type-price');
    if (sumTypePrice) {
        sumTypePrice.textContent = priceBreakdown.typeAddon > 0 
            ? '+' + formatPrice(priceBreakdown.typeAddon)
            : typeData.desc;
    }

    // Thickness
    document.getElementById('sum-thick').textContent = thickData.label;
    const sumThickPrice = document.getElementById('sum-thick-price');
    if (sumThickPrice) {
        if (priceBreakdown.thicknessAddon !== 0) {
            sumThickPrice.textContent = (priceBreakdown.thicknessAddon > 0 ? '+' : '') + formatPrice(priceBreakdown.thicknessAddon);
        } else {
            sumThickPrice.textContent = thickData.desc;
        }
    }

    // Edge Work
    document.getElementById('sum-edge').textContent = edgeData.label;
    const sumEdgePrice = document.getElementById('sum-edge-price');
    if (sumEdgePrice) {
        sumEdgePrice.textContent = priceBreakdown.edgeAddon > 0 
            ? '+' + formatPrice(priceBreakdown.edgeAddon)
            : edgeData.desc;
    }

    // Frame Type
    document.getElementById('sum-frame').textContent = frameData.label;
    const sumFramePrice = document.getElementById('sum-frame-price');
    if (sumFramePrice) {
        sumFramePrice.textContent = priceBreakdown.frameAddon > 0 
            ? '+' + formatPrice(priceBreakdown.frameAddon)
            : frameData.desc;
    }

    // Engraving
    const engravingInput = document.querySelector('#step-3 .engraving-section input');
    const engravingText = engravingInput ? engravingInput.value : '';
    document.getElementById('sum-engrave').textContent = engravingText || 'None';

    // 5. Update Total Price
    const totalPrice = calculateTotal();
    document.getElementById('sum-total').textContent = formatPrice(totalPrice);

    // 6. Update Breadcrumbs
    crumbMain.innerText = 'Review Order';
    crumbMain.classList.add('active');
    removeCrumb('crumb-step2');
    removeCrumb('crumb-step3');
}

function editConfiguration() {
    // Hide Summary
    document.getElementById('summary-wrapper').classList.add('hidden-step');

    // --- NEW: Show Related Products & Testimonials again ---
    document.getElementById('related-products-section').classList.remove('hidden-step');
    document.getElementById('testimonials-section').classList.remove('hidden-step');

    // Show Toggle and Subtitle
    document.querySelector('.build-toggle').classList.remove('hidden-step');

    // Determine which wrapper to show based on mode
    if (isStandardMode) {
        standardWrapper.classList.remove('hidden-step');
        document.getElementById('standard-subtitle').classList.remove('hidden-step');
    } else {
        customWrapper.classList.remove('hidden-step');
        priceBox.classList.remove('hidden-step');
        // Return to Step 3 to allow immediate editing
        goToStep(3);
    }
}

// Helper Utils
function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}
function formatText(str) {
    if (!str) return '';
    return str.split('-').map(word => capitalize(word)).join(' ');
}

// --- EVENT LISTENER UPDATES ---

// 2. Finalize Button (Standard Flow)
// FIND the onclick attribute in the HTML for the Standard finalize button
// OR add this listener (recommended to remove onclick="alert..." from HTML first)
const stdFinalizeBtn = document.querySelector('#standard-wrapper .next-btn');
if (stdFinalizeBtn) {
    stdFinalizeBtn.onclick = null; // Remove inline alert
    stdFinalizeBtn.addEventListener('click', () => {
        console.log("Finalizing Standard Order...");
        showOrderSummary();
        logOrderSummary();
    });
}

// 3. Edit Order Button
document.getElementById('edit-order-btn').addEventListener('click', editConfiguration);


// --- 2D PREVIEW MODAL LOGIC ---
// (This handles the pop-up when clicking "2D Preview")
const previewLabel = document.querySelector('.preview-label');
const previewModal = document.getElementById('preview-modal');
const zoomedImg = document.getElementById('zoomed-preview-img');
const previewCloseBtn = document.getElementById('preview-close-btn');
const downloadDesignBtn = document.getElementById('download-design-btn');

// Check if elements exist to avoid errors
if (previewLabel && previewModal && zoomedImg) {
    // Open Modal
    previewLabel.addEventListener('click', () => {
        // Generate a high-quality image from the Konva Stage
        const dataUrl = getKonvaImageData(3);
        zoomedImg.src = dataUrl;
        previewModal.classList.remove('hidden-step');
    });

    // Close Modal (Click Outside)
    previewModal.addEventListener('click', (e) => {
        if (e.target === previewModal) {
            previewModal.classList.add('hidden-step');
        }
    });

    // Close button
    if (previewCloseBtn) {
        previewCloseBtn.addEventListener('click', () => {
            previewModal.classList.add('hidden-step');
        });
    }

    // Download button
    if (downloadDesignBtn) {
        downloadDesignBtn.addEventListener('click', () => {
            downloadDesign();
        });
    }
}

// Download design image function
function downloadDesign() {
    const dataUrl = getKonvaImageData(4); // Higher quality for download
    const link = document.createElement('a');
    link.download = `glassify-design-${Date.now()}.png`;
    link.href = dataUrl;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Make design image data accessible globally for cart submission
window.getDesignImageData = function() {
    return currentDesignImageData || getKonvaImageData(3);
};

// Get current customization state for cart
window.getCustomizationState = function() {
    return {
        shape: currentShape,
        glassType: currentGlassType,
        thickness: currentThickness,
        edgeWork: currentEdgeWork,
        frameType: currentFrameType,
        dimensions: currentDimensions,
        priceBreakdown: priceBreakdown
    };
};


// --- BUY NOW REDIRECT LOGIC ---
// Handler moved to addtocustomization.js for proper AJAX handling


function logOrderSummary() {
    console.log("=== ORDER SUMMARY (from database + customizations) ===");

    // From PHP (database)
    console.log("Product ID:", selectedProduct.id);
    console.log("Product Name:", selectedProduct.name);
    console.log("Category:", selectedProduct.category);
    console.log("Material:", selectedProduct.material);
    console.log("Base Price:", selectedProduct.price);

    // From your customization UI
    console.log("Shape:", currentShape);
    console.log("Glass Type:", currentGlassType);
    console.log("Thickness:", currentThickness);
    console.log("Edge Work:", currentEdgeWork);
    console.log("Frame Type:", currentFrameType);

    console.log("Dimensions:", {
        height: currentDimensions.height.value + currentDimensions.height.unit,
        width: currentDimensions.width.value + currentDimensions.width.unit
    });

    console.log("=== END SUMMARY ===");
}



