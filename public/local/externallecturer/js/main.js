// OLD AUTOCOMPLETE GLOBAL VARIABLE (COMMENTED OUT):
// var allPersonalData = []; // Global variable to store personal data

/*
    JAVASCRIPT TAB SYSTEM
    =====================
    To add JavaScript functionality for a new tab:
    1. If you need tab switching, uncomment and modify the tab switching logic below
    2. Add any new event listeners for buttons in the new tab
    3. Add any new functions specific to the new tab functionality
    4. Update the openTab function if needed for special handling
*/

document.addEventListener("DOMContentLoaded", function () {
  // Since we only have one tab now, no need for tab switching logic
  // To re-enable tab switching for multiple tabs, uncomment and modify:
  /*
  var activeTab = localStorage.getItem("activeTab");
  if (activeTab) {
    document.getElementById(activeTab).click();
  } else {
    document.getElementById("defaultOpen").click();
  }
  */

  // add lecturer listener
  var addLecturerButton = document.getElementById("add-lecturer");
  console.log("Looking for add-lecturer button...");
  if (addLecturerButton) {
    console.log("Add lecturer button found, adding event listener");
    addLecturerButton.addEventListener("click", function (event) {
      console.log("Add lecturer button clicked!");
      event.preventDefault();
      event.stopPropagation();
      var lecturerForm = document.getElementById("lecturer-form");
      if (lecturerForm) {
        lecturerForm.reset();
      } else {
        console.error("Lecturer form not found!");
      }
      var idField = document.getElementById("id");
      if (idField) {
        idField.value = "";
      } else {
        console.error("ID field not found!");
      }

      // Reset fields for new lecturer
      document.getElementById("name").value = "";
      document.getElementById("passport").value = "";
      document.getElementById("civil_number").value = "";
      var lecturerTypeField = document.getElementById("lecturer_type");
      if (lecturerTypeField) {
        lecturerTypeField.value = "external_visitor";
      }
      // OLD AUTOCOMPLETE CODE (COMMENTED OUT):
      // document.getElementById("passport").readOnly = true;
      // Hide any open suggestions
      // hideSuggestions();

      document.querySelector(".modal-title").innerText =
        M.str.local_externallecturer.addlecturermodal;
      document.getElementById("save-lecturer").innerText =
        M.str.local_externallecturer.save;

      var modal = document.getElementById("lecturer-modal");
      if (modal) {
        console.log("Opening lecturer modal");
        modal.style.display = "flex";
      } else {
        console.error("Lecturer modal not found!");
      }
    });
  } else {
    console.error("Add lecturer button not found!");
  }

  // add resident lecturer listener
  var addResidentButton = document.getElementById("add-resident-lecturer");
  if (addResidentButton) {
    addResidentButton.addEventListener("click", function () {
      var form = document.getElementById("resident-lecturer-form");
      form.reset();
      document.getElementById("resident-id").value = "";
      document.getElementById("resident-lecturer-type").value = "resident";

      document.querySelector("#resident-lecturer-modal .modal-title").innerText =
        M.str.local_externallecturer.addresidentlecturermodal || "Add Resident Lecturer";
      document.getElementById("save-resident-lecturer").innerText =
        M.str.local_externallecturer.save;

      var modal = document.getElementById("resident-lecturer-modal");
      modal.style.display = "flex";
    });
  }



  // Get all lecturer rows
  var lecturerRows = document.querySelectorAll(".lecturer-row");
  // Loop through each row and add click event listener
  lecturerRows.forEach(function (row) {
    row.addEventListener("click", function () {
      // Lecturer row click functionality removed as course enrollment is no longer available
    });
  });

  // Handle Edit lecturer Button Click
  document.querySelectorAll(".edit-lecturer-button").forEach(function (button) {
    button.addEventListener("click", function (event) {
      event.stopPropagation();
      // Populate the edit form with the current lecturer data
      document.getElementById("id").value = this.getAttribute("data-id");
      document.getElementById("name").value = this.getAttribute("data-name");
      document.getElementById("age").value = this.getAttribute("data-age");
      document.getElementById("specialization").value = this.getAttribute(
        "data-specialization"
      );
      document.getElementById("organization").value =
        this.getAttribute("data-organization");
      document.getElementById("degree").value =
        this.getAttribute("data-degree");
      document.getElementById("passport").value =
        this.getAttribute("data-passport");
      document.getElementById("civil_number").value =
        this.getAttribute("data-civil-number") || "";
      var nationalityAttr = this.getAttribute("data-nationality") || "";
      var lecturerTypeAttr = this.getAttribute("data-lecturer-type") || "external_visitor";
      var nationalityField = document.getElementById("nationality");
      if (nationalityField) {
        nationalityField.value = nationalityAttr;
      }
      var lecturerTypeField = document.getElementById("lecturer_type");
      if (lecturerTypeField) {
        lecturerTypeField.value = lecturerTypeAttr || "external_visitor";
      }

      // For editing, set fields with existing data and allow manual editing
      // OLD AUTOCOMPLETE CODE (COMMENTED OUT):
      // document.getElementById("passport").readOnly = false;
      // Hide any open suggestions
      // hideSuggestions();

      document.querySelector(".modal-title").innerText =
        M.str.local_externallecturer.editlecturermodal;
      document.getElementById("save-lecturer").innerText =
        M.str.local_externallecturer.savechanges;

      // Show the edit modal
      var editModal = document.getElementById("lecturer-modal");
      editModal.style.display = "flex";
    });
  });



  // Save/Add Lecturer (handle both add and edit)
  document
    .getElementById("save-lecturer")
    .addEventListener("click", function () {
      var formData = new FormData(document.getElementById("lecturer-form"));
      var id = document.getElementById("id").value;
      var form = document.getElementById("lecturer-form");

      if (!form.checkValidity()) {
        return form.reportValidity();
      }

      var actionUrl = id
        ? "actions/editlecturer.php"
        : "actions/addlecturer.php";

      fetch(actionUrl, {
        method: "POST",
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData,
      })
        .then(response => {
          console.log("Response status:", response.status);
          if (response.ok) {
            console.log("Lecturer saved successfully");
            var modal = document.getElementById("lecturer-modal");
            modal.style.display = "none";
            location.reload();
          } else {
            console.error("Server error:", response.status);
            alert("An error occurred while saving the lecturer. Please try again.");
          }
        })
        .catch((error) => {
          console.error("Network Error:", error);
          alert("Network error occurred. Please check your connection and try again.");
        });
    });

  document
    .getElementById("close-lecturer-modal")
    .addEventListener("click", function () {
      var modal = document.getElementById("lecturer-modal");
      modal.style.display = "none"; // Hide the modal
    });

  // save resident lecturer handler
  var saveResidentButton = document.getElementById("save-resident-lecturer");
  if (saveResidentButton) {
    saveResidentButton.addEventListener("click", function () {
      var form = document.getElementById("resident-lecturer-form");
      if (!form.checkValidity()) {
        return form.reportValidity();
      }

      var formData = new FormData(form);
      var actionUrl = "actions/addlecturer.php";

      fetch(actionUrl, {
        method: "POST",
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData,
      })
        .then(response => {
          if (response.ok) {
            var modal = document.getElementById("resident-lecturer-modal");
            modal.style.display = "none";
            location.reload();
          } else {
            alert("An error occurred while saving the lecturer. Please try again.");
          }
        })
        .catch((error) => {
          console.error("Network Error:", error);
          alert("Network error occurred. Please check your connection and try again.");
        });
    });
  }

  // close resident lecturer modal
  var closeResidentButton = document.getElementById("close-resident-lecturer-modal");
  if (closeResidentButton) {
    closeResidentButton.addEventListener("click", function () {
      var modal = document.getElementById("resident-lecturer-modal");
      modal.style.display = "none";
    });
  }

  window.onclick = function (event) {
    var lecturerModal = document.getElementById("lecturer-modal");
    var residentLecturerModal = document.getElementById("resident-lecturer-modal");

    // Check if the target clicked is the lecturer modal
    if (event.target == lecturerModal) {
      lecturerModal.style.display = "none"; // Hide the lecturer modal if the user clicks outside
    }

    if (event.target == residentLecturerModal) {
      residentLecturerModal.style.display = "none";
    }
  };

  // OLD AUTOCOMPLETE CODE (COMMENTED OUT):
  // Load personal data for autocomplete
  // loadPersonalData();

  // Handle autocomplete for name field
  // var nameField = document.getElementById("name");

  // nameField.addEventListener("input", function () {
  //   var query = this.value.trim();
  //   if (query.length >= 2) {
  //     showSuggestions(query);
  //   } else {
  //     hideSuggestions();
  //   }
  // });

  // nameField.addEventListener("focus", function () {
  //   var query = this.value.trim();
  //   if (query.length >= 2) {
  //     showSuggestions(query);
  //   }
  // });

  // Hide suggestions when clicking outside
  // document.addEventListener("click", function (event) {
  //   if (!event.target.closest(".autocomplete-container")) {
  //     hideSuggestions();
  //   }
  // });
});

// OLD AUTOCOMPLETE FUNCTIONS (COMMENTED OUT):
// Function to load personal data from Oracle database
// function loadPersonalData() {
//   console.log("Loading personal data from Oracle...");
//   fetch("ajax/get_personal_data.php")
//     .then((response) => {
//       console.log("Response status:", response.status);
//       return response.json();
//     })
//     .then((data) => {
//       console.log("Received data:", data);

//       // Check if data is an array and store it globally
//       if (Array.isArray(data)) {
//         allPersonalData = data;
//         console.log("Loaded", data.length, "personal records for autocomplete");
//       } else {
//         console.error("Data is not an array:", data);
//         allPersonalData = [];
//       }
//     })
//     .catch((error) => {
//       console.error("Error loading personal data:", error);
//       allPersonalData = [];
//     });
// }

// Function to show autocomplete suggestions
// function showSuggestions(query) {
//   var suggestionsDiv = document.getElementById("autocomplete-suggestions");
//   var filteredData = allPersonalData.filter(function (person) {
//     return (
//       person.fullname.toLowerCase().includes(query.toLowerCase()) ||
//       person.civil_number.includes(query) ||
//       person.first_name.toLowerCase().includes(query.toLowerCase()) ||
//       person.last_name.toLowerCase().includes(query.toLowerCase())
//     );
//   });

//   suggestionsDiv.innerHTML = "";

//   if (filteredData.length > 0) {
//     filteredData.slice(0, 10).forEach(function (person) {
//       // Limit to 10 suggestions
//       var suggestionItem = document.createElement("div");
//       suggestionItem.className = "suggestion-item";
//       suggestionItem.style.cssText =
//         "padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #eee;";
//       suggestionItem.textContent = person.display_text;

//       // Hover effects
//       suggestionItem.addEventListener("mouseenter", function () {
//         this.style.backgroundColor = "#f0f0f0";
//       });
//       suggestionItem.addEventListener("mouseleave", function () {
//         this.style.backgroundColor = "white";
//       });

//       // Click to select
//       suggestionItem.addEventListener("click", function () {
//         selectPerson(person);
//       });

//       suggestionsDiv.appendChild(suggestionItem);
//     });
//     suggestionsDiv.style.display = "block";
//   } else {
//     hideSuggestions();
//   }
// }

// Function to hide suggestions
// function hideSuggestions() {
//   var suggestionsDiv = document.getElementById("autocomplete-suggestions");
//   suggestionsDiv.style.display = "none";
// }

// Function to select a person and populate fields
// function selectPerson(person) {
//   var nameField = document.getElementById("name");
//   var passportField = document.getElementById("passport");
//   var civilNumberField = document.getElementById("civil_number");

//   // Fill the fields
//   nameField.value = person.fullname;
//   passportField.value = person.passport_number || person.civil_number; // Use passport_number if available, fallback to civil_number
//   civilNumberField.value = person.civil_number;

//   // Hide suggestions
//   hideSuggestions();
// }

/*
    TAB SWITCHING FUNCTION
    ======================
    This function handles switching between tabs.
    To add a new tab:
    1. Make sure your new tab content div has class="tab-content"
    2. Make sure your new tab button has class="tablinks"
    3. The function will automatically handle showing/hiding content
    4. Add any special initialization code for your new tab here if needed
*/
function openTab(evt, tabName) {
  var i, tabcontent, tablinks;

  // Hide all tab contents
  tabcontent = document.getElementsByClassName("tab-content");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Remove the 'active' class from all tab links
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  // Show the current tab and add 'active' class to the clicked button
  document.getElementById(tabName).style.display = "block";
  evt.currentTarget.className += " active";

  localStorage.setItem("activeTab", evt.currentTarget.id);
}



function deleteHandler(url, data) {
  // Show loading indicator or disable buttons to prevent double-clicks
  const deleteButtons = document.querySelectorAll('.delete-lecturer-button, .delete-course-button');
  deleteButtons.forEach(btn => {
    btn.disabled = true;
    btn.style.opacity = '0.6';
  });

  fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-Requested-With": "XMLHttpRequest"
    },
    body: JSON.stringify(data),
  })
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(result => {
      if (result.success) {
        // Show success message if available
        if (result.message) {
          console.log("Success:", result.message);
        }
        // Reload the page to reflect changes
        location.reload();
      } else {
        // Show error message
        const errorMsg = result.error || "An error occurred while deleting.";
        alert(errorMsg);
        console.error("Delete Error:", result.error);
        
        // Re-enable buttons on error
        deleteButtons.forEach(btn => {
          btn.disabled = false;
          btn.style.opacity = '1';
        });
      }
    })
    .catch((error) => {
      console.error("Network Error:", error);
      alert("Network error occurred. Please check your connection and try again.");
      
      // Re-enable buttons on error
      deleteButtons.forEach(btn => {
        btn.disabled = false;
        btn.style.opacity = '1';
      });
    });
}

// Resident lecturer civil number search functionality
document.addEventListener('DOMContentLoaded', function() {
  var civilSearchField = document.getElementById('resident-civil-search');
  var loadingDiv = document.getElementById('resident-civil-loading');
  var searchTimeout;

  if (civilSearchField) {
    civilSearchField.addEventListener('input', function() {
      var civilNumber = this.value.trim();
      
      // Clear previous timeout
      if (searchTimeout) {
        clearTimeout(searchTimeout);
      }
      
      // Clear fields if input is empty
      if (civilNumber === '') {
        clearResidentFormFields();
        return;
      }
      
      // Debounce the search
      searchTimeout = setTimeout(function() {
        searchByCivilNumber(civilNumber);
      }, 500); // Wait 500ms after user stops typing
    });
  }
});

// Function to search by civil number using Oracle database
function searchByCivilNumber(civilNumber) {
  var loadingDiv = document.getElementById('resident-civil-loading');
  
  // Show loading indicator
  if (loadingDiv) {
    loadingDiv.style.display = 'block';
  }
  
  // Make AJAX request to oracleFetch plugin
  fetch('../oracleFetch/ajax/search_by_civil.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: 'civil_number=' + encodeURIComponent(civilNumber)
  })
  .then(response => response.json())
  .then(data => {
    // Hide loading indicator
    if (loadingDiv) {
      loadingDiv.style.display = 'none';
    }
    
    if (data.success && data.data) {
      populateResidentFormFields(data.data);
    } else {
      clearResidentFormFields();
      console.log('No data found for civil number:', civilNumber);
    }
  })
  .catch(error => {
    // Hide loading indicator
    if (loadingDiv) {
      loadingDiv.style.display = 'none';
    }
    
    console.error('Error searching by civil number:', error);
    clearResidentFormFields();
  });
}

// Function to populate resident form fields with Oracle data
function populateResidentFormFields(personData) {
  var nameField = document.getElementById('resident-name');
  var civilNumberField = document.getElementById('resident-civil-number');
  var nationalityField = document.getElementById('resident-nationality');
  var passportField = document.getElementById('resident-passport'); // NEW

  if (nameField) {
    nameField.value = personData.fullname || '';
    nameField.setAttribute('readonly', 'readonly'); // lock the field
  }
  
  if (civilNumberField) {
    civilNumberField.value = personData.civil_number || '';
    civilNumberField.setAttribute('readonly', 'readonly'); // lock the field
  }
  
  if (nationalityField) {
    nationalityField.value = personData.nationality || '';
    nationalityField.setAttribute('readonly', 'readonly'); // lock the field

  }
 
  // NEW: auto-fill passport;
  if (passportField) {
    passportField.value = personData.passport_number  || '';
    passportField.setAttribute('readonly', 'readonly'); // lock the field

  }

}

// Function to clear resident form fields
function clearResidentFormFields() {
  var nameField = document.getElementById('resident-name');
  var civilNumberField = document.getElementById('resident-civil-number');
  var nationalityField = document.getElementById('resident-nationality');
  var passportField = document.getElementById('resident-passport'); // NEW
  
  if (nameField) {
    nameField.value = '';
  }
  
  if (civilNumberField) {
    civilNumberField.value = '';
  }
  
  if (nationalityField) {
    nationalityField.value = '';
  }
  // NEW
  if (passportField) {
    passportField.value = '';
  }
}
