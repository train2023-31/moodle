// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * AMD module for guest autocomplete functionality
 *
 * @module     local_residencebooking/guest_autocomplete
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(["jquery"], function ($) {
  /**
   * Transport function for autocomplete.
   * Handles AJAX requests to the guest search endpoint.
   *
   * @param {String} selector - The selector for the autocomplete field
   * @param {String} query - The search query
   * @param {Function} callback - Success callback function
   * @param {Function} failure - Error callback function
   */
  var transport = function (selector, query, callback, failure) {
    $.get({
      url: M.cfg.wwwroot + "/local/residencebooking/ajax/guest_search.php",
      data: {
        term: query || "",
      },
      dataType: "json",
    })
      .done(function (response) {
        if (response && response.results) {
          callback(response.results);
        } else {
          callback([]);
        }
      })
      .fail(function (jqXHR, textStatus, errorThrown) {
        failure(textStatus + ": " + errorThrown);
      });
  };

  /**
   * Process the results from the autocomplete search.
   * Converts the response format to match autocomplete requirements.
   *
   * @param {String} selector - The selector for the autocomplete field
   * @param {Array} results - Array of search results
   * @returns {Array} Processed results in autocomplete format
   */
  var processResults = function (selector, results) {
    // Convert from {id: ..., text: ..., pf_number: ...} to {value: ..., label: ..., pf_number: ...} format
    var processedResults = [];
    if (Array.isArray(results)) {
      results.forEach(function (item) {
        processedResults.push({
          value: item.id,
          label: item.text,
          pf_number: item.pf_number
        });
      });
    }
    return processedResults;
  };

  /**
   * Initialize the autocomplete with service number population functionality.
   * Sets up event listeners and handles automatic PF number extraction.
   *
   * @param {String} guestSelector - CSS selector for the guest name field
   * @param {String} serviceNumberSelector - CSS selector for the service number field
   */
  var initAutocomplete = function(guestSelector, serviceNumberSelector) {
    // Wait for the page to be fully loaded
    $(document).ready(function() {
      // Find the actual autocomplete input field
      var guestField = $(guestSelector);
      var serviceNumberField = $(serviceNumberSelector);
      
      // Fallback selectors if primary selectors fail
      if (guestField.length === 0) {
        guestField = $('input[name="guest_name"]');
        if (guestField.length === 0) {
          return; // Exit if no guest field found
        }
      }
      
      if (serviceNumberField.length === 0) {
        serviceNumberField = $('input[name="service_number"]');
        if (serviceNumberField.length === 0) {
          return; // Exit if no service number field found
        }
      }
      
      /**
       * Extract PF number from display text using regex pattern.
       * 
       * @param {String} text - The text to extract PF number from
       * @returns {String|null} The extracted PF number or null if not found
       */
      function extractPFNumber(text) {
        if (!text) return null;
        var pfMatch = text.match(/PF(\d+)/);
        return pfMatch ? 'PF' + pfMatch[1] : null;
      }
      
      /**
       * Populate the service number field with PF number.
       * Uses direct extraction first, then falls back to AJAX call.
       * 
       * @param {String} selectedText - The selected guest text
       */
      function populateServiceNumber(selectedText) {
        // Method 1: Direct PF extraction from display text (fastest)
        var pfNumber = extractPFNumber(selectedText);
        if (pfNumber) {
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
          if (response.success && response.pf_number) {
            serviceNumberField.val(response.pf_number);
          }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
          // Silent fail - user can manually enter PF number
        });
      }
      
      // Event listener for field value changes
      guestField.on('change', function() {
        var selectedGuest = $(this).val();
        
        if (selectedGuest && selectedGuest.trim() !== '') {
          populateServiceNumber(selectedGuest);
        } else {
          serviceNumberField.val(''); // Clear service number when guest is cleared
        }
      });
      
      // Event listener for Select2 selection events
      guestField.on('select2:select', function(e) {
        var selectedGuest = e.params.data.text || e.params.data.id;
        populateServiceNumber(selectedGuest);
      });
      
      // Event listener for input events (catches typing and selection)
      guestField.on('input', function() {
        var selectedGuest = $(this).val();
        if (selectedGuest && selectedGuest.includes('PF')) {
          populateServiceNumber(selectedGuest);
        }
      });
      
      // Handle existing values on page load (e.g., form editing)
      setTimeout(function() {
        var currentValue = guestField.val();
        if (currentValue && currentValue.includes('PF')) {
          populateServiceNumber(currentValue);
        }
      }, 1000);
    });
  };

  // Return the module with public methods
  return {
    transport: transport,
    processResults: processResults,
    initAutocomplete: initAutocomplete
  };
});
