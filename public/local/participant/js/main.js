var allEmployeeData = []; // Global variable to store employee data
var allLecturerData = []; // Global variable to store lecturer data

// Employee and Lecturer dropdowns with autocomplete functionality
document.addEventListener("DOMContentLoaded", function () {
  // Load employee data and populate dropdown
  loadEmployeeData();

  // Load lecturer data and populate dropdown
  loadLecturerData();

  // Set up the unified dropdown functionality
  setupEmployeeDropdown();
  setupLecturerDropdown();

  // Set up conditional field behavior
  setupConditionalFields();
});

// Function to load employee data from Oracle database
function loadEmployeeData() {
  fetch("ajax/get_employee_data.php")
    .then((response) => {
      if (!response.ok) {
        throw new Error("HTTP error! status: " + response.status);
      }
      return response.json();
    })
    .then((data) => {
      if (Array.isArray(data)) {
        allEmployeeData = data;
        populateEmployeeDropdown();
        initializeSearchableDropdown();
      } else {
        allEmployeeData = [];
      }
    })
    .catch((error) => {
      allEmployeeData = [];
    });
}

// Function to load lecturer data from database
function loadLecturerData() {
  fetch("ajax/get_external_lecturers.php")
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response was not ok: " + response.status);
      }
      return response.text().then((text) => {
        try {
          return JSON.parse(text);
        } catch (e) {
          throw e;
        }
      });
    })
    .then((data) => {
      if (Array.isArray(data)) {
        allLecturerData = data;
        populateLecturerDropdown();
        initializeLecturerSearchableDropdown();
      } else {
        allLecturerData = [];
      }
    })
    .catch((error) => {
      allLecturerData = [];
    });
}

// Function to populate the dropdown with employee data
function populateEmployeeDropdown() {
  var employeeSelector = document.getElementById("employee_selector");
  if (!employeeSelector) {
    return;
  }

  // Clear existing options except the first one
  employeeSelector.innerHTML =
    '<option value="">-- ' +
    (document.documentElement.lang === "ar"
      ? "ابدأ بالكتابة للبحث عن موظف..."
      : "Start typing to search for an employee...") +
    " --</option>";

  // Add employee options
  allEmployeeData.forEach(function (employee) {
    var option = document.createElement("option");
    option.value = employee.pf_number;
    option.textContent = employee.display_text;
    option.dataset.employeeData = JSON.stringify(employee);
    employeeSelector.appendChild(option);
  });
}

// Function to populate the dropdown with lecturer data
function populateLecturerDropdown() {
  var lecturerSelector = document.getElementById("lecturer_selector");
  if (!lecturerSelector) {
    return;
  }

  // Clear existing options except the first one
  lecturerSelector.innerHTML =
    '<option value="">-- ' +
    (document.documentElement.lang === "ar"
      ? "ابدأ بالكتابة للبحث عن محاضر..."
      : "Start typing to search for a lecturer...") +
    " --</option>";

  // Add lecturer options
  allLecturerData.forEach(function (lecturer) {
    var option = document.createElement("option");
    option.value = lecturer.id;
    option.textContent = lecturer.display_text;
    option.dataset.lecturerData = JSON.stringify(lecturer);
    lecturerSelector.appendChild(option);
  });
}

// Function to initialize searchable dropdown functionality
function initializeSearchableDropdown() {
  var employeeSelector = document.getElementById("employee_selector");
  if (!employeeSelector) {
    return;
  }

  // Create search input that will replace the dropdown when focused
  var searchInput = document.createElement("input");
  searchInput.type = "text";
  searchInput.className = "form-control";
  searchInput.placeholder = employeeSelector.options[0].text;
  searchInput.style.cssText =
    "display: none; width: 100%; height: calc(1.5em + 0.75rem + 2px); " +
    "padding: 0.375rem 0.75rem; font-size: 1rem; font-weight: 400; " +
    "line-height: 1.5; color: #495057; background-color: #fff; " +
    "border: 1px solid #ced4da; border-radius: 0.25rem;";
  searchInput.autocomplete = "off";

  // Create dropdown container for filtered results
  var dropdownContainer = document.createElement("div");
  dropdownContainer.className = "employee-dropdown-container";
  dropdownContainer.style.cssText = `
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    width: 100%;
    background: white;
    border: 1px solid #ced4da;
    border-top: none;
    border-radius: 0 0 0.25rem 0.25rem;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  `;

  // Insert elements
  employeeSelector.parentNode.insertBefore(
    searchInput,
    employeeSelector.nextSibling
  );
  employeeSelector.parentNode.insertBefore(
    dropdownContainer,
    searchInput.nextSibling
  );

  // Handle dropdown click to show search input
  employeeSelector.addEventListener("click", function () {
    if (this.selectedIndex <= 0) {
      showSearchMode();
    }
  });

  // Handle search input
  searchInput.addEventListener("input", function () {
    var query = this.value.trim();
    if (query.length >= 1) {
      showFilteredResults(query);
    } else {
      showAllResults();
    }
  });

  // Handle search input focus
  searchInput.addEventListener("focus", function () {
    dropdownContainer.style.display = "block";
    showAllResults();
  });

  // Handle click outside to hide
  document.addEventListener("click", function (event) {
    if (!event.target.closest(".employee-selector-container")) {
      hideSearchMode();
    }
  });

  // Handle selection change
  employeeSelector.addEventListener("change", function () {
    if (this.value) {
      var selectedOption = this.options[this.selectedIndex];
      var employeeData = JSON.parse(selectedOption.dataset.employeeData);
      selectEmployee(employeeData);
    }
  });

  function showSearchMode() {
    employeeSelector.style.display = "none";
    searchInput.style.display = "block";
    searchInput.focus();
    dropdownContainer.style.display = "block";
    showAllResults();
  }

  function hideSearchMode() {
    searchInput.style.display = "none";
    employeeSelector.style.display = "block";
    dropdownContainer.style.display = "none";
    searchInput.value = "";
  }

  function showAllResults() {
    dropdownContainer.innerHTML = "";
    allEmployeeData.slice(0, 10).forEach(function (employee) {
      createDropdownItem(employee);
    });
  }

  function showFilteredResults(query) {
    var filteredData = allEmployeeData.filter(function (employee) {
      return (
        employee.fullname.toLowerCase().includes(query.toLowerCase()) ||
        employee.pf_number.toLowerCase().includes(query.toLowerCase()) ||
        employee.first_name.toLowerCase().includes(query.toLowerCase()) ||
        employee.last_name.toLowerCase().includes(query.toLowerCase()) ||
        (employee.civil_number && employee.civil_number.includes(query))
      );
    });

    dropdownContainer.innerHTML = "";
    filteredData.slice(0, 10).forEach(function (employee) {
      createDropdownItem(employee);
    });
  }

  function createDropdownItem(employee) {
    var item = document.createElement("div");
    item.className = "dropdown-item";
    item.style.cssText =
      "padding: 0.5rem 0.75rem; cursor: pointer; border-bottom: 1px solid #e9ecef; font-size: 1rem; line-height: 1.5; color: #495057; background-color: #fff; transition: background-color 0.15s ease-in-out;";
    item.textContent = employee.display_text;

    // Hover effects
    item.addEventListener("mouseenter", function () {
      this.style.backgroundColor = "#f8f9fa";
    });
    item.addEventListener("mouseleave", function () {
      this.style.backgroundColor = "#fff";
    });

    // Click to select
    item.addEventListener("click", function () {
      selectEmployee(employee);
      hideSearchMode();
    });

    dropdownContainer.appendChild(item);
  }
}

// Function to initialize lecturer searchable dropdown functionality
function initializeLecturerSearchableDropdown() {
  var lecturerSelector = document.getElementById("lecturer_selector");
  if (!lecturerSelector) {
    return;
  }

  // Create search input that will replace the dropdown when focused
  var searchInput = document.createElement("input");
  searchInput.type = "text";
  searchInput.className = "form-control";
  searchInput.placeholder = lecturerSelector.options[0].text;
  searchInput.style.cssText =
    "display: none; width: 100%; height: calc(1.5em + 0.75rem + 2px); " +
    "padding: 0.375rem 0.75rem; font-size: 1rem; font-weight: 400; " +
    "line-height: 1.5; color: #495057; background-color: #fff; " +
    "border: 1px solid #ced4da; border-radius: 0.25rem;";
  searchInput.autocomplete = "off";

  // Create dropdown container for filtered results
  var dropdownContainer = document.createElement("div");
  dropdownContainer.className = "lecturer-dropdown-container";
  dropdownContainer.style.cssText = `
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    width: 100%;
    background: white;
    border: 1px solid #ced4da;
    border-top: none;
    border-radius: 0 0 0.25rem 0.25rem;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  `;

  // Insert elements
  lecturerSelector.parentNode.insertBefore(
    searchInput,
    lecturerSelector.nextSibling
  );
  lecturerSelector.parentNode.insertBefore(
    dropdownContainer,
    searchInput.nextSibling
  );

  // Handle dropdown click to show search input
  lecturerSelector.addEventListener("click", function () {
    if (this.selectedIndex <= 0) {
      showLecturerSearchMode();
    }
  });

  // Handle search input
  searchInput.addEventListener("input", function () {
    var query = this.value.trim();
    if (query.length >= 1) {
      showLecturerFilteredResults(query);
    } else {
      showLecturerAllResults();
    }
  });

  // Handle search input focus
  searchInput.addEventListener("focus", function () {
    dropdownContainer.style.display = "block";
    showLecturerAllResults();
  });

  // Handle click outside to hide
  document.addEventListener("click", function (event) {
    if (!event.target.closest(".lecturer-selector-container")) {
      hideLecturerSearchMode();
    }
  });

  // Handle selection change
  lecturerSelector.addEventListener("change", function () {
    if (this.value) {
      var selectedOption = this.options[this.selectedIndex];
      var lecturerData = JSON.parse(selectedOption.dataset.lecturerData);
      selectLecturer(lecturerData);
    }
  });

  function showLecturerSearchMode() {
    lecturerSelector.style.display = "none";
    searchInput.style.display = "block";
    searchInput.focus();
    dropdownContainer.style.display = "block";
    showLecturerAllResults();
  }

  function hideLecturerSearchMode() {
    searchInput.style.display = "none";
    lecturerSelector.style.display = "block";
    dropdownContainer.style.display = "none";
    searchInput.value = "";
  }

  function showLecturerAllResults() {
    dropdownContainer.innerHTML = "";
    allLecturerData.slice(0, 10).forEach(function (lecturer) {
      createLecturerDropdownItem(lecturer);
    });
  }

  function showLecturerFilteredResults(query) {
    var filteredData = allLecturerData.filter(function (lecturer) {
      return (
        lecturer.name.toLowerCase().includes(query.toLowerCase()) ||
        lecturer.organization.toLowerCase().includes(query.toLowerCase()) ||
        lecturer.specialization.toLowerCase().includes(query.toLowerCase())
      );
    });

    dropdownContainer.innerHTML = "";
    filteredData.slice(0, 10).forEach(function (lecturer) {
      createLecturerDropdownItem(lecturer);
    });
  }

  function createLecturerDropdownItem(lecturer) {
    var item = document.createElement("div");
    item.className = "dropdown-item";
    item.style.cssText =
      "padding: 0.5rem 0.75rem; cursor: pointer; border-bottom: 1px solid #e9ecef; font-size: 1rem; line-height: 1.5; color: #495057; background-color: #fff; transition: background-color 0.15s ease-in-out;";
    item.textContent = lecturer.display_text;

    // Hover effects
    item.addEventListener("mouseenter", function () {
      this.style.backgroundColor = "#f8f9fa";
    });
    item.addEventListener("mouseleave", function () {
      this.style.backgroundColor = "#fff";
    });

    // Click to select
    item.addEventListener("click", function () {
      selectLecturer(lecturer);
      hideLecturerSearchMode();
    });

    dropdownContainer.appendChild(item);
  }
}

// Function to set up the employee dropdown
function setupEmployeeDropdown() {
  var employeeSelector = document.getElementById("employee_selector");
  console.log("Employee selector found:", employeeSelector);
}

// Function to set up the lecturer dropdown
function setupLecturerDropdown() {
  var lecturerSelector = document.getElementById("lecturer_selector");
  console.log("Lecturer selector found:", lecturerSelector);
}

// Function to select an employee and populate fields
function selectEmployee(employee) {
  var employeeSelector = document.getElementById("employee_selector");
  var pfNumberField = document.getElementById("id_pf_number");
  var participantFullNameField = document.getElementById("id_participant_full_name");
  var employeeDataField = document.getElementById("id_employee_data");

  // Update dropdown to show selected employee
  if (employeeSelector) {
    // Find and select the option
    for (var i = 0; i < employeeSelector.options.length; i++) {
      if (employeeSelector.options[i].value === employee.pf_number) {
        employeeSelector.selectedIndex = i;
        break;
      }
    }
  }

  // Store the PF number
  if (pfNumberField) {
    pfNumberField.value = employee.pf_number;
  } else {
    // Try alternative ways to find the field
    var altField1 = document.querySelector('input[name="pf_number"]');
    if (altField1) {
      altField1.value = employee.pf_number;
    }
  }

  // Store the full name
  if (participantFullNameField) {
    participantFullNameField.value = employee.fullname;
  } else {
    // Try alternative ways to find the field
    var altField2 = document.querySelector('input[name="participant_full_name"]');
    if (altField2) {
      altField2.value = employee.fullname;
    }
  }

  // Store complete employee data as JSON
  if (employeeDataField) {
    employeeDataField.value = JSON.stringify(employee);
  } else {
    // Try alternative ways to find the field
    var altField3 = document.querySelector('input[name="employee_data"]');
    if (altField3) {
      altField3.value = JSON.stringify(employee);
    }
  }
}

// Function to select a lecturer and populate fields
function selectLecturer(lecturer) {
  var lecturerSelector = document.getElementById("lecturer_selector");
  var externalLecturerIdField = document.getElementById(
    "id_external_lecturer_id"
  );
  var lecturerDataField = document.getElementById("id_lecturer_data");

  // Update dropdown to show selected lecturer
  if (lecturerSelector) {
    // Find and select the option
    for (var i = 0; i < lecturerSelector.options.length; i++) {
      if (lecturerSelector.options[i].value == lecturer.id) {
        lecturerSelector.selectedIndex = i;
        break;
      }
    }
  }

  // Store the lecturer ID
  if (externalLecturerIdField) {
    externalLecturerIdField.value = lecturer.id;
  } else {
    // Try alternative ways to find the field
    var altField1 = document.querySelector(
      'input[name="external_lecturer_id"]'
    );
    if (altField1) {
      altField1.value = lecturer.id;
    }
  }

  // Store complete lecturer data as JSON
  if (lecturerDataField) {
    lecturerDataField.value = JSON.stringify(lecturer);
  } else {
    // Try alternative ways to find the field
    var altField2 = document.querySelector('input[name="lecturer_data"]');
    if (altField2) {
      altField2.value = JSON.stringify(lecturer);
    }
  }
}

// Function to set up conditional field behavior
function setupConditionalFields() {
  var participantTypeField = document.getElementById("id_participant_type_id");
  var employeeSelector = document.getElementById("employee_selector");
  var lecturerSelector = document.getElementById("lecturer_selector");

  if (participantTypeField && employeeSelector && lecturerSelector) {
    // Function to update field states
    function updateFieldStates() {
      var selectedType = participantTypeField.value;
      var isExternalLecturer = selectedType === "7";

      // Handle employee selector
      if (isExternalLecturer) {
        employeeSelector.disabled = true;
        employeeSelector.style.backgroundColor = "#f8f9fa";
        employeeSelector.style.color = "#6c757d";
        employeeSelector.style.cursor = "not-allowed";

        // Clear selected employee data
        employeeSelector.selectedIndex = 0;
        var employeeDataField = document.getElementById("id_employee_data");

        // internal_user_id removed
        if (employeeDataField) {
          employeeDataField.value = "";
        }
      } else {
        employeeSelector.disabled = false;
        employeeSelector.style.backgroundColor = "#fff";
        employeeSelector.style.color = "#495057";
        employeeSelector.style.cursor = "pointer";
      }

      // Handle lecturer selector
      if (isExternalLecturer) {
        lecturerSelector.disabled = false;
        lecturerSelector.style.backgroundColor = "#fff";
        lecturerSelector.style.color = "#495057";
        lecturerSelector.style.cursor = "pointer";
      } else {
        lecturerSelector.disabled = true;
        lecturerSelector.style.backgroundColor = "#f8f9fa";
        lecturerSelector.style.color = "#6c757d";
        lecturerSelector.style.cursor = "not-allowed";

        // Clear selected lecturer data
        lecturerSelector.selectedIndex = 0;
        var externalLecturerIdField = document.getElementById(
          "id_external_lecturer_id"
        );
        var lecturerDataField = document.getElementById("id_lecturer_data");

        if (externalLecturerIdField) {
          externalLecturerIdField.value = "";
        }
        if (lecturerDataField) {
          lecturerDataField.value = "";
        }
      }

      // Also disable/enable search functionality
      updateSearchFunctionality(!isExternalLecturer);
      updateLecturerSearchFunctionality(isExternalLecturer);
    }

    // Initial state check
    updateFieldStates();

    // Listen for changes
    participantTypeField.addEventListener("change", updateFieldStates);
  }
}

// Function to update search functionality based on field state
function updateSearchFunctionality(enabled) {
  var searchInput = document.querySelector(
    ".employee-selector-container input[type='text']"
  );
  var dropdownContainer = document.querySelector(
    ".employee-dropdown-container"
  );

  if (searchInput) {
    searchInput.disabled = !enabled;
    if (enabled) {
      searchInput.style.backgroundColor = "#fff";
      searchInput.style.color = "#495057";
      searchInput.style.cursor = "text";
    } else {
      searchInput.style.backgroundColor = "#f8f9fa";
      searchInput.style.color = "#6c757d";
      searchInput.style.cursor = "not-allowed";
    }
  }

  if (dropdownContainer && !enabled) {
    dropdownContainer.style.display = "none";
  }
}

function updateLecturerSearchFunctionality(enabled) {
  var searchInput = document.querySelector(
    ".lecturer-selector-container input[type='text']"
  );
  var dropdownContainer = document.querySelector(
    ".lecturer-dropdown-container"
  );

  if (searchInput) {
    searchInput.disabled = !enabled;
    if (enabled) {
      searchInput.style.backgroundColor = "#fff";
      searchInput.style.color = "#495057";
      searchInput.style.cursor = "text";
    } else {
      searchInput.style.backgroundColor = "#f8f9fa";
      searchInput.style.color = "#6c757d";
      searchInput.style.cursor = "not-allowed";
    }
  }

  if (dropdownContainer && !enabled) {
    dropdownContainer.style.display = "none";
  }
}

function rejectRequestHandler(data) {
  fetch("actions/reject_request.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: JSON.stringify(data),
  })
    .then((response) => response.json())
    .then((res) => {
      location.reload();
    })
    .catch((error) => {
      console.error("Error:", error);
    });
}

function approveRequestHandler(data) {
  fetch("actions/approve_request.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: JSON.stringify(data),
  })
    .then(() => {
      location.reload();
    })
    .catch((error) => {
      console.error("Error:", error);
    });
}
