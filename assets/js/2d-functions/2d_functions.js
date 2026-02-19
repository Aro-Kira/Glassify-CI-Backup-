var Page2DModeling = {

    init: function () {
        this.custom2D();
        this.orderOptions();
        this.productPreview();
        this.uploadFile();
        this.wishlistButton();
    },

    /* ---------------------------------------------------------
       1) 2D CUSTOMIZER (formerly customization.js + part of order-option.js)
    --------------------------------------------------------- */
    custom2D: function () {
        const diagramContainer = document.querySelector(".preview-diagram");
        if (!diagramContainer) return;

        diagramContainer.innerHTML = `<canvas id="diagramCanvas" width="300" height="300"></canvas>`;
        const canvas = document.getElementById("diagramCanvas");
        const ctx = canvas.getContext("2d");

        const state = {
            shape: "Rectangle",
            height: 10,
            heightFraction: '0"',
            width: 10,
            widthFraction: '0"',
            type: "Tempered",
            thickness: "8mm",
            edge: "Flat Polish",
            frame: "Vinyl",
            engraving: "",
            rows: 1,
            columns: 1
        };

        let mode = "custom";

        function fractionToDecimal(f) {
            return f === '1/2"' ? 0.5 :
                f === '1/4"' ? 0.25 :
                f === '3/4"' ? 0.75 :
                f === '3/8"' ? 0.375 : 0;
        }

        function drawDiagram() {
            if (mode === "standard") {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                updateTable();
                return;
            }

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            let h = state.height + fractionToDecimal(state.heightFraction);
            let w = state.width + fractionToDecimal(state.widthFraction);

            if (state.shape === "Square") {
                const side = Math.min(h, w);
                h = w = side;
            }

            const ratio = w / h;
            let drawW = 200;
            let drawH = drawW / ratio;
            if (drawH > 200) {
                drawH = 200;
                drawW = drawH * ratio;
            }

            const x = (canvas.width - drawW) / 2;
            const y = (canvas.height - drawH) / 2;

            ctx.lineWidth = 5;
            ctx.strokeStyle =
                state.frame === "Aluminum" ? "silver" :
                    state.frame === "Wood" ? "brown" : "black";

            ctx.fillStyle = {
                Tempered: "rgba(173,216,230,0.4)",
                Laminated: "rgba(135,206,235,0.5)",
                Double: "rgba(173,216,230,0.6)",
                "Low-E": "rgba(200,255,200,0.5)",
                Tinted: "rgba(100,100,100,0.4)",
                Frosted: "rgba(220,220,220,0.6)"
            }[state.type] || "rgba(173,216,230,0.4)";

            ctx.fillRect(x, y, drawW, drawH);
            ctx.strokeRect(x, y, drawW, drawH);

            const rowHeight = drawH / state.rows;
            const colWidth = drawW / state.columns;
            ctx.lineWidth = 2;
            ctx.strokeStyle = "black";

            for (let r = 1; r < state.rows; r++) {
                ctx.beginPath();
                ctx.moveTo(x, y + r * rowHeight);
                ctx.lineTo(x + drawW, y + r * rowHeight);
                ctx.stroke();
            }

            for (let c = 1; c < state.columns; c++) {
                ctx.beginPath();
                ctx.moveTo(x + c * colWidth, y);
                ctx.lineTo(x + c * colWidth, y + drawH);
                ctx.stroke();
            }

            if (state.engraving) {
                ctx.fillStyle = "black";
                ctx.font = "14px Arial";
                ctx.textAlign = "center";
                ctx.fillText(state.engraving, canvas.width / 2, y + drawH + 20);
            }

            updateTable();
        }

        function calculatePrice() {
            if (mode === "standard") return null;

            const h = state.height + fractionToDecimal(state.heightFraction);
            const w = state.width + fractionToDecimal(state.widthFraction);

            const areaFeet = (h * w) / 144;
            const baseRate = 50;

            const thicknessMultiplier = { "5mm": 1, "6mm": 1.1, "8mm": 1.2, "10mm": 1.3, "12mm": 1.4 }[state.thickness];
            const typeMultiplier = { Tempered: 1.2, Laminated: 1.3, Double: 1.4, "Low-E": 1.5, Tinted: 1.1, Frosted: 1.15 }[state.type];
            const frameCost = { Vinyl: 200, Aluminum: 300, Wood: 400 }[state.frame];
            const engravingCost = state.engraving ? 500 : 0;

            return parseFloat((areaFeet * baseRate * thicknessMultiplier * typeMultiplier + frameCost + engravingCost).toFixed(2));
        }

        function updateTable() {
            const table = document.querySelector(".price-details table");
            if (!table) return;

            table.querySelector("tr:nth-child(1) td:nth-child(2)").textContent = state.shape;
            table.querySelector("tr:nth-child(2) td:nth-child(2)").textContent =
                mode === "standard" ? `${state.height}" x ${state.width}"` :
                    `${state.height}" ${state.heightFraction}, ${state.width}" ${state.widthFraction}`;
            table.querySelector("tr:nth-child(3) td:nth-child(2)").textContent = state.type;
            table.querySelector("tr:nth-child(4) td:nth-child(2)").textContent = state.thickness;
            table.querySelector("tr:nth-child(5) td:nth-child(2)").textContent = state.edge;
            table.querySelector("tr:nth-child(6) td:nth-child(2)").textContent = state.frame;
            table.querySelector("tr:nth-child(7) td:nth-child(2)").textContent = state.engraving || "N/A";

            const price = calculatePrice();
            table.querySelector(".total-row td:nth-child(2)").textContent =
                price !== null ? `₱${price.toLocaleString(undefined, { minimumFractionDigits: 2 })}` : "—";
        }

        document.querySelectorAll(".option-group .option-btn").forEach(btn => {
            btn.addEventListener("click", () => {
                const group = btn.closest(".option-group").querySelector("h3").textContent;
                btn.parentNode.querySelectorAll(".option-btn").forEach(b => b.classList.remove("active"));
                btn.classList.add("active");

                if (group.includes("Shape")) state.shape = btn.textContent;
                if (group.includes("Type")) state.type = btn.textContent;
                if (group.includes("Thickness")) state.thickness = btn.textContent;
                if (group.includes("Edge")) state.edge = btn.textContent;
                if (group.includes("Frame")) state.frame = btn.textContent;

                mode = "custom";
                drawDiagram();
            });
        });

        document.querySelectorAll(".dimension-input-group select").forEach(select => {
            select.addEventListener("change", () => {
                const label = select.closest(".dimension-input-group").querySelector("label").textContent;
                if (label === "Height") state.height = parseInt(select.value);
                if (label === "Width") state.width = parseInt(select.value);
                if (label === "Fraction") {
                    const parent = select.closest(".dimension-inputs");
                    const labelName = parent.previousElementSibling.querySelector("label").textContent.toLowerCase();
                    state[labelName + "Fraction"] = select.value;
                }
                mode = "custom";
                drawDiagram();
            });
        });

        const engravingInput = document.querySelector(".engraving-input input");
        if (engravingInput) {
            engravingInput.addEventListener("input", e => {
                state.engraving = e.target.value;
                drawDiagram();
            });
        }

        document.getElementById("rows")?.addEventListener("change", e => { state.rows = parseInt(e.target.value) || 1; drawDiagram(); });
        document.getElementById("columns")?.addEventListener("change", e => { state.columns = parseInt(e.target.value) || 1; drawDiagram(); });

        const customBtn = document.getElementById("custom-build-btn");
        const standardBtn = document.getElementById("standard-btn");
        const customContent = document.getElementById("custom-build-content");
        const standardContent = document.getElementById("standard-content");

        customBtn?.addEventListener("click", () => {
            mode = "custom";
            customBtn.classList.add("active");
            standardBtn.classList.remove("active");
            customContent.classList.add("active");
            standardContent.classList.remove("active");
            drawDiagram();
        });

        standardBtn?.addEventListener("click", () => {
            mode = "standard";
            standardBtn.classList.add("active");
            customBtn.classList.remove("active");
            standardContent.classList.add("active");
            customContent.classList.remove("active");
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            updateTable();
        });

        document.querySelectorAll("#standard-content .size-buttons .option-btn").forEach(btn => {
            btn.addEventListener("click", () => {
                const match = btn.textContent.match(/(\d+)"\s*x\s*(\d+)"/);
                if (match) {
                    state.height = +match[1];
                    state.width = +match[2];
                    mode = "standard";
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    updateTable();
                }
            });
        });

        drawDiagram();
    },

    /* ---------------------------------------------------------
       2) OPTION BUTTON GROUP HIGHLIGHTING (order-option.js)
    --------------------------------------------------------- */
    orderOptions: function () {
        document.querySelectorAll('.shape-buttons, .size-buttons').forEach(group => {
            group.querySelectorAll('.option-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    group.querySelectorAll('.option-btn').forEach(inner => inner.classList.remove('active'));
                    btn.classList.add('active');
                });
            });
        });
    },

    /* ---------------------------------------------------------
       3) IMAGE PREVIEW SLIDER (product-preview.js)
    --------------------------------------------------------- */
    productPreview: function () {
        const imageContainer = document.getElementById('product-image-container');
        if (!imageContainer) return;

        const prevBtn = document.getElementById('prev-image');
        const nextBtn = document.getElementById('next-image');
        const imageCountDisplay = document.getElementById('image-counter');

        const productImages = imageContainer.querySelectorAll('.main-product-image');
        if (productImages.length === 0) return;

        let currentImageIndex = 0;

        function updateSlider() {
            // Hide all images
            productImages.forEach((img, idx) => {
                img.style.display = idx === currentImageIndex ? 'block' : 'none';
                if (idx === currentImageIndex) {
                    img.classList.add('active');
                } else {
                    img.classList.remove('active');
                }
            });

            // Update counter
            if (imageCountDisplay) {
                imageCountDisplay.textContent = `${currentImageIndex + 1}/${productImages.length}`;
            }
        }

        nextBtn?.addEventListener('click', () => {
            currentImageIndex = (currentImageIndex + 1) % productImages.length;
            updateSlider();
        });

        prevBtn?.addEventListener('click', () => {
            currentImageIndex = (currentImageIndex - 1 + productImages.length) % productImages.length;
            updateSlider();
        });

        // Initialize with first image visible
        updateSlider();
    },

    /* ---------------------------------------------------------
       4) UPLOAD FILE POPUP (upload-file.js)
    --------------------------------------------------------- */
    uploadFile: function () {
    // Popup functions attached to Page2DModeling
    this.openPopup = function () {
        document.getElementById("uploadPopup").style.display = "flex";
    };

    this.closePopup = function () {
        document.getElementById("uploadPopup").style.display = "none";
    };

    const fileInput = document.getElementById("fileInput");
    const filePlaceholder = document.getElementById("filePlaceholder");

    if (fileInput) {
        fileInput.addEventListener("change", function (event) {
            const files = event.target.files;
            if (files.length) {
                filePlaceholder.innerHTML = ""; // clear placeholder
                Array.from(files).forEach(file => {
                    const div = document.createElement("div");
                    div.textContent = `${file.name} (${Math.round(file.size / 1024)} KB)`;
                    filePlaceholder.appendChild(div);
                });
            }
        });
    }

    // Optional: drag-and-drop support
    const uploadArea = document.querySelector(".upload-area");
    if (uploadArea) {
        uploadArea.addEventListener("dragover", e => e.preventDefault());
        uploadArea.addEventListener("drop", e => {
            e.preventDefault();
            const files = e.dataTransfer.files;
            if (files.length) {
                fileInput.files = files; // update input
                fileInput.dispatchEvent(new Event("change")); // trigger change
            }
        });
    }
},


   /* ---------------------------------------------------------
   5) WISHLIST BUTTON (wishlist-btn.js)
--------------------------------------------------------- */
wishlistButton: function () {
    // Attach the function to Page2DModeling
   this.toggleWishlist = function(button) {
    const heart = button.querySelector(".heart-icon");
    if (heart) {
        heart.classList.toggle("heart-active");

        // Trigger pop animation
        heart.classList.remove("heart-pop"); // reset
        void heart.offsetWidth; // force reflow to restart animation
        heart.classList.add("heart-pop");
    }
};

}
};

document.addEventListener("DOMContentLoaded", function () {
    Page2DModeling.init();
});
