/**
 * Guest Autocomplete with Service Number Population
 * 
 * This file provides automatic service number population functionality
 * for the residence booking form when a guest is selected.
 * 
 * @package    local_requestservices
 * @subpackage js
 * @copyright  2025 Your Organization
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Initialize guest autocomplete with service number population
 * 
 * @param {string} guestSelector - CSS selector for the guest name field
 * @param {string} serviceNumberSelector - CSS selector for the service number field
 */
function initGuestAutocomplete(guestSelector, serviceNumberSelector) {
    // Wait for the page to be fully loaded
    $(document).ready(function() {
        // Wait a bit for the form to be fully rendered
        setTimeout(function() {
            var guestField = $(guestSelector);
            var serviceNumberField = $(serviceNumberSelector);
            
            if (guestField.length === 0 || serviceNumberField.length === 0) {
                console.log("Guest or service number field not found");
                return;
            }
            
            console.log("Initializing guest autocomplete with service number population");
            
            /**
             * Extract PF number from display text using regex pattern
             * 
             * @param {string} text - The text to extract PF number from
             * @returns {string|null} The extracted PF number or null if not found
             */
            function extractPFNumber(text) {
                if (!text) return null;
                var pfMatch = text.match(/PF(\d+)/);
                return pfMatch ? "PF" + pfMatch[1] : null;
            }
            
            /**
             * Populate the service number field with PF number
             * Uses direct extraction first, then falls back to AJAX call.
             * 
             * @param {string} selectedText - The selected guest text
             */
            function populateServiceNumber(selectedText) {
                console.log("Attempting to populate service number for:", selectedText);
                
                // Method 1: Direct PF extraction from display text (fastest)
                var pfNumber = extractPFNumber(selectedText);
                if (pfNumber) {
                    console.log("Found PF number in text:", pfNumber);
                    serviceNumberField.val(pfNumber);
                    return;
                }
                
                // Method 2: AJAX fallback call if no PF number in display text
                $.get({
                    url: M.cfg.wwwroot + "/local/residencebooking/ajax/get_pf_number.php",
                    data: { guest_name: selectedText },
                    dataType: "json"
                })
                .done(function(response) {
                    console.log("AJAX response:", response);
                    if (response.success && response.pf_number) {
                        serviceNumberField.val(response.pf_number);
                        console.log("Set service number via AJAX:", response.pf_number);
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.log("AJAX failed:", textStatus, errorThrown);
                });
            }
            
            // Event listener for field value changes
            guestField.on("change", function() {
                var selectedGuest = $(this).val();
                console.log("Guest field changed:", selectedGuest);
                
                if (selectedGuest && selectedGuest.trim() !== "") {
                    populateServiceNumber(selectedGuest);
                } else {
                    serviceNumberField.val(""); // Clear service number when guest is cleared
                }
            });
            
            // Event listener for Select2 selection events (if using Select2)
            guestField.on("select2:select", function(e) {
                var selectedGuest = e.params.data.text || e.params.data.id;
                console.log("Select2 selection:", selectedGuest);
                populateServiceNumber(selectedGuest);
            });
            
            // Event listener for input events (catches typing and selection)
            guestField.on("input", function() {
                var selectedGuest = $(this).val();
                if (selectedGuest && selectedGuest.includes("PF")) {
                    console.log("Input with PF detected:", selectedGuest);
                    populateServiceNumber(selectedGuest);
                }
            });
            
            // Event listener for blur events (when field loses focus)
            guestField.on("blur", function() {
                var selectedGuest = $(this).val();
                if (selectedGuest && selectedGuest.trim() !== "") {
                    console.log("Guest field blur:", selectedGuest);
                    populateServiceNumber(selectedGuest);
                }
            });
            
            // Handle existing values on page load (e.g., form editing)
            var currentValue = guestField.val();
            if (currentValue && currentValue.includes("PF")) {
                console.log("Existing value with PF:", currentValue);
                populateServiceNumber(currentValue);
            }
            
            console.log("Guest autocomplete initialization completed");
        }, 500); // Wait 500ms for form to be fully rendered
    });
}

// Auto-initialize if called directly (for backward compatibility)
$(document).ready(function() {
    // Check if we're on the residencebooking tab
    if (window.location.href.indexOf('tab=residencebooking') !== -1) {
        initGuestAutocomplete('#id_guest_name', '#id_service_number');
    }
});
