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
        },
        "Powder Coated White": {
            "color": "#F8F8F8",
            "width": 4
        },
        "Analok": {
            "color": "#F5F5DC",
            "width": 4
        },
        "Matte Gray": {
            "color": "#6B6B6B",
            "width": 4
        },
        "Matte Black": {
            "color": "#1A1A1A",
            "width": 4
        }
    },
    "glassType": {
        "Clear": {
            "fill": "rgba(173, 216, 230, 0.3)",
            "opacity": 0.8
        },
        "Ordinary": {
            "fill": "#E0F2F1",
            "opacity": 0.9
        },
        "Tempered": {
            "fill": "#E0F2F1",
            "opacity": 0.9
        },
        "Reflective": {
            "fill": "rgba(200, 200, 200, 0.6)",
            "opacity": 0.85
        }
    },
    "glassColor": {
        "Clear": {
            "fill": "rgba(255, 255, 255, 0.1)",
            "opacity": 0.9
        },
        "Bronze": {
            "fill": "rgba(205, 127, 50, 0.4)",
            "opacity": 0.7
        },
        "Frosted/Smoke": {
            "fill": "rgba(220, 220, 220, 0.7)",
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
