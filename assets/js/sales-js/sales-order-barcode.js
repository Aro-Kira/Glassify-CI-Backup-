// ===========================================
        // sales-order-barcode.js
        // ===========================================

        // Function to generate barcode for a specific img element
        function generateBarcode(imgId, orderId) {
            const barcodeImg = document.getElementById(imgId);

            if (barcodeImg && orderId) {
                try {
                    // Clean the Order ID (remove # if present)
                    const cleanOrderId = orderId.replace('#', '');

                    // Generate barcode using JsBarcode
                    JsBarcode(barcodeImg, cleanOrderId, {
                        format: "CODE128", // Barcode type (alphanumeric)
                        width: 2,          // Line width
                        height: 40,        // Height in pixels
                        displayValue: true, // Show text below barcode
                        fontSize: 12,      // Text font size
                        margin: 10         // Margin around barcode
                    });
                } catch (error) {
                    console.error('Barcode generation failed:', error);
                }
            }
        }

        // ===========================================
        // sales-order-tabs.js (Basic Tab Switching)
        // ===========================================

        document.addEventListener('DOMContentLoaded', () => {
            const tabLinks = document.querySelectorAll('.tab-link');
            const sections = document.querySelectorAll('.order-section');

            tabLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();

                    // 1. Remove 'active' class from all links and sections
                    tabLinks.forEach(l => l.classList.remove('active'));
                    sections.forEach(s => s.classList.remove('active'));

                    // 2. Add 'active' class to the clicked link
                    link.classList.add('active');

                    // 3. Find the corresponding section and add 'active' class
                    const targetTab = link.getAttribute('data-tab');
                    const targetSection = document.getElementById(`tab-${targetTab}`);

                    if (targetSection) {
                        targetSection.classList.add('active');
                    }
                });
            });

            // Initial active state check
            const initialActiveLink = document.querySelector('.tab-link.active');
            if (initialActiveLink) {
                const initialTargetTab = initialActiveLink.getAttribute('data-tab');
                const initialTargetSection = document.getElementById(`tab-${initialTargetTab}`);
                if (initialTargetSection) {
                    initialTargetSection.classList.add('active');
                }
            }
        });


        // ===========================================
        // sales-order-approval-btn.js (Pending Review Logic)
        // ===========================================

        document.querySelectorAll('.btn-approve').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                // Get the Order ID from the 2nd column
                const orderId = row.querySelector('td:nth-child(2)').textContent.trim();

                // Show the popup 
                document.getElementById('approvalPopup').style.display = 'flex';

                // Call generateBarcode for the Pending Review popup with new ID
                generateBarcode('pendingBarcode', orderId);
            });
        });

        // ===========================================
        // sales-order-view-btn.js (Awaiting Admin Logic)
        // ===========================================

        document.querySelectorAll('.btn-view').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                // Get the Order ID from the 2nd column
                const orderId = row.querySelector('td:nth-child(2)').textContent.trim();

                // Show the popup 
                document.getElementById('awaitingPopup').style.display = 'flex';

                // Call generateBarcode for the Awaiting Admin popup (using existing ID)
                generateBarcode('barcodeAwaitingApproval', orderId);
            });
        });

        // ===========================================
        // sales-order-check-btn.js (Ready to Approve Logic)
        // ===========================================

        document.querySelectorAll('.btn-check').forEach(button => {
            button.addEventListener('click', function() {
                // Get the clicked row
                const row = this.closest('tr');

                // Get the Order ID from the row (e.g., "#GI001")
                const orderId = row.querySelector('td:nth-child(2)').textContent.trim(); // 2nd column is Order ID

                // Determine status and show popup
                const statusSpan = row.querySelector('.status');
                const status = statusSpan ? statusSpan.textContent.trim() : 'Pending';
                const isApproved = status === 'Approved';

                if (isApproved) {
                    document.getElementById('approvedPopup').style.display = 'flex';
                    // Generate barcode for approved popup with new ID
                    generateBarcode('readyApprovedBarcode', orderId);
                } else {
                    document.getElementById('disapprovedPopup').style.display = 'flex';
                    // Generate barcode for disapproved popup (ID remains the same)
                    generateBarcode('barcodeImageDisapproved', orderId);
                }
            });
        });

 