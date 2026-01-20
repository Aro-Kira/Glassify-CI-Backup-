// Generated visual configurations for Windows customization
// Generated on: 2026-01-18 17:33:31

const windowsVisualConfigs = {
    "panelConfiguration": {
        "S | S (Sliding | Sliding)": {
            "pattern": "sliding",
            "panels": [
                "sliding",
                "sliding",
                "sliding"
            ]
        },
        "F | S (Fixed | Sliding)": {
            "pattern": "sliding",
            "panels": [
                "fixed",
                "sliding",
                "sliding"
            ]
        },
        "S | S | S | S (All Sliding)": {
            "pattern": "sliding",
            "panels": [
                "sliding",
                "sliding",
                "sliding",
                "sliding"
            ]
        },
        "F | S | S | F (Fixed | Sliding | Sliding | Fixed)": {
            "pattern": "sliding",
            "panels": [
                "fixed",
                "sliding",
                "sliding",
                "fixed",
                "sliding",
                "sliding",
                "fixed"
            ]
        }
    },
    "frameColor": {
        "Hanalok": {
            "color": "#F5F5DC",
            "width": 4
        },
        "White": {
            "color": "#FFFFFF",
            "width": 4
        },
        "Black": {
            "color": "#000000",
            "width": 4
        },
        "Gray": {
            "color": "#808080",
            "width": 4
        },
        "Wood Finish": {
            "color": "#8B4513",
            "width": 4
        }
    },
    "glassType": {
        "Clear": {
            "fill": "rgba(173, 216, 230, 0.3)",
            "opacity": 0.8
        }
    },
    "numberOfPanels": {
        "2 Panels": {
            "panels": 2
        },
        "4 Panels": {
            "panels": 4
        }
    },
    "operation": {
        "Awning (crank-out)": {
            "operation": "awning",
            "direction": "outward"
        },
        "Awning (push-out)": {
            "operation": "awning",
            "direction": "outward"
        },
        "Casement (hinge side configurable)": {
            "operation": "casement",
            "hinge": "configurable"
        }
    }
};

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = windowsVisualConfigs;
} else if (typeof window !== 'undefined') {
    window.windowsVisualConfigs = windowsVisualConfigs;
}
