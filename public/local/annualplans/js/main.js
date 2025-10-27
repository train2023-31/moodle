document.addEventListener("DOMContentLoaded", function () {
  // Existing showModal function remains unchanged
  window.showModal = function (title, message, onConfirm) {
    // Create the modal wrapper
    var modal = document.createElement("div");
    modal.id = "customModal";
    modal.className = "custom-modal";

    // Create the modal content
    var modalContent = document.createElement("div");
    modalContent.className = "modal-content";

    var modalTitle = document.createElement("span");
    modalTitle.className = "modal-title";
    modalTitle.textContent = title;

    var modalMessage = document.createElement("div"); // Changed from 'p' to 'div' to support HTML
    modalMessage.className = "modal-message";
    modalMessage.innerHTML = message; // Changed to innerHTML to allow HTML content

    var modalButtons = document.createElement("div");
    modalButtons.className = "modal-buttons";

    var confirmButton = document.createElement("button");
    confirmButton.id = "confirmButton";
    confirmButton.textContent = "نعم";
    confirmButton.className = "btn btn-proceed";
    confirmButton.addEventListener("click", function () {
      let proceed = true;
      if (onConfirm) {
        const result = onConfirm();
        if (result === false) {
          proceed = false; // validation failed inside onConfirm
        }
      }
      if (proceed) {
        hideModal(modal); // Hide only if confirmed
      }
    });

    var cancelButton = document.createElement("button");
    cancelButton.id = "cancelButton";
    cancelButton.textContent = "إلغاء";
    cancelButton.className = "btn btn-cancel";
    cancelButton.addEventListener("click", function () {
      hideModal(modal); // Just hide and remove the modal
    });

    modalButtons.appendChild(confirmButton);
    modalButtons.appendChild(cancelButton);

    modalContent.appendChild(modalTitle);
    modalContent.appendChild(modalMessage);
    modalContent.appendChild(modalButtons);

    modal.appendChild(modalContent);
    document.body.appendChild(modal);

    // Show the modal with fade-in animation
    setTimeout(function () {
      modal.classList.add("show");
    }, 10); // Slight delay to trigger the transition

    confirmButton.focus();

    // Add an event listener to close the modal when clicked outside the modal content
    modal.addEventListener("click", function (event) {
      if (event.target === modal) {
        hideModal(modal); // Hide and remove the modal
      }
    });
  };

  // Function to hide and remove the modal from the DOM with fade-out effect
  function hideModal(modal) {
    modal.classList.remove("show"); // Remove the show class for fade-out effect
    modal.addEventListener(
      "transitionend",
      function () {
        document.body.removeChild(modal); // Remove modal after fade-out
      },
      { once: true }
    );
  }

  // Handler for the delete annual plan button (existing code)
  var deleteButton = document.getElementById("id_deleteannualplan");
  if (deleteButton) {
    deleteButton.addEventListener("click", function (event) {
      event.preventDefault();
      var closestForm = this.closest("form");

      // Define the modal message with a textarea for the deletion note
      var message = `
              <p>هل أنت متأكد من حذف هذه الخطة؟</p>
               <p><small>ستحذف جميع الدورات المرتبطة بهذه الخطة </small></p>
              <div class="form-group">
                  <textarea id="deletionNote" name="deletionNote" class="form-control" rows="4" placeholder="يرجى كتابة ملاحظة حول حذف الخطة" required></textarea>
                  <div class="invalid-feedback" style="color: red; display: none;">
                      يجب كتابة ملاحظة قبل حذف الخطة.
                  </div>
              </div>
          `;

      // Show the modal with the custom message
      showModal("حذف الخطة", message, function () {
        // Retrieve the note from the textarea
        var noteTextarea = document.getElementById("deletionNote");
        if (!noteTextarea) {
          alert("حدث خطأ أثناء جمع الملاحظة. يرجى المحاولة مرة أخرى.");
          return;
        }

        var note = noteTextarea.value.trim();

        if (note === "") {
          alert("يجب كتابة ملاحظة قبل حذف الخطة.");
          // Prevent form submission
          return;
        }

        // Set the note in the hidden field
        var deletionNoteField = closestForm.querySelector(
          'input[name="deletion_note"]'
        );
        if (deletionNoteField) {
          deletionNoteField.value = note;
        } else {
          // Create the hidden field if it doesn't exist
          deletionNoteField = document.createElement("input");
          deletionNoteField.type = "hidden";
          deletionNoteField.name = "deletion_note";
          deletionNoteField.value = note;
          closestForm.appendChild(deletionNoteField);
        }

        // Submit the form
        closestForm.submit();
      });
    });
  }

  // Existing showDeleteCourseModal function remains unchanged
  window.showDeleteCourseModal = function (courseId) {
    // Define the modal message with a textarea for the deletion note
    var message = `
          <p>هل انت متأكد من حذف الدورة؟</p>
          <textarea id="deletionNote-${courseId}" class="form-control" rows="4" placeholder="يرجى كتابة ملاحظة حول حذف الدورة" required></textarea>
          <div class="invalid-feedback" style="color: red; display: none;">
              يجب كتابة ملاحظة قبل حذف الدورة.
          </div>
      `;

    // Show the modal with the custom message
    showModal(" حذف الدورة", message, function () {
      // Retrieve the note from the textarea
      var noteTextarea = document.getElementById(`deletionNote-${courseId}`);
      if (!noteTextarea) {
        alert("حدث خطأ أثناء جمع الملاحظة. يرجى المحاولة مرة أخرى.");
        return;
      }

      var note = noteTextarea.value.trim();

      if (note === "") {
        // Show validation error
        var feedback = noteTextarea.nextElementSibling;
        if (feedback) {
          feedback.style.display = "block";
        }
        noteTextarea.classList.add("is-invalid");
        return false; // tell showModal not to close
      } else {
        // Hide validation error if any
        var feedback = noteTextarea.nextElementSibling;
        if (feedback) {
          feedback.style.display = "none";
        }
        noteTextarea.classList.remove("is-invalid");
      }

      // Set the note in the hidden field safely (courseId may contain special chars)
      var deleteForm = document.getElementById(
        `delete-course-form-${courseId}`
      );
      var deletionNoteField = deleteForm
        ? deleteForm.querySelector('input[name="deletion_note"]')
        : null;
      if (deletionNoteField) {
        deletionNoteField.value = note;
      } else {
        // Create the hidden field if it doesn't exist (fallback)
        deletionNoteField = document.createElement("input");
        deletionNoteField.type = "hidden";
        deletionNoteField.name = "deletion_note";
        deletionNoteField.value = note;
        if (deleteForm) {
          deleteForm.appendChild(deletionNoteField);
        }
      }

      // Submit the form
      deleteCourseHandler(courseId);
      return true;
    });

    // Automatically focus on the textarea after modal is shown
    setTimeout(function () {
      var textarea = document.getElementById(`deletionNote-${courseId}`);
      if (textarea) {
        textarea.focus();
      }
    }, 300); // Adjust timeout as needed based on modal animation duration
  };

  // Existing deleteCourseHandler function remains unchanged
  window.deleteCourseHandler = function (courseId) {
    document.getElementById(`delete-course-form-${courseId}`).submit();
  };

  // Existing confirmCourseHandler function (if needed)
  window.confirmCourseHandler = function (checkbox, courseId) {
    document.getElementById(`form_${courseId}`).submit();
    checkbox.checkbox = !checkbox.checked;
  };

  // *** New Functions for Approving and Unapproving Courses ***

  // Function to handle checkbox clicks
  window.handleCheckboxClick = function (event, checkbox, courseId) {
    event.preventDefault(); // Prevent the default checkbox state change

    var isChecked = checkbox.checked; // Current state before the click

    if (isChecked) {
      // User has checked the box – approve course
      showApproveCourseModal(courseId, function () {
        // Ensure the box remains checked after confirmation
        checkbox.checked = true;
      });
    } else {
      // User has unchecked the box – unapprove course (requires note)
      showUnapproveCourseModal(courseId, function () {
        // Ensure the box stays unchecked after confirmation
        checkbox.checked = false;
      });
    }
  };

  // Function to show the unapprove modal with a note input
  window.showUnapproveCourseModal = function (courseId, afterConfirm) {
    var message = `
          <p>هل أنت متأكد من رغبتك في إلغاء اعتماد الدورة؟</p>
          <p><small>ملاحظة: سيتم حذف جميع محتويات الدورة عند إلغاء اعتمادها</small></p>
          <div class="form-group">
              <textarea id="unapprovedNote-${courseId}" name="unapprove_note" class="form-control" rows="4" placeholder="يرجى كتابة ملاحظة حول إلغاء اعتماد الدورة" required></textarea>
              <div class="invalid-feedback" style="color: red; display: none;">
                  يجب كتابة ملاحظة قبل إلغاء اعتماد الدورة.
              </div>
          </div>
      `;

    showModal(" إلغاء اعتماد الدورة", message, function () {
      var noteTextarea = document.getElementById(`unapprovedNote-${courseId}`);
      if (!noteTextarea) {
        alert("حدث خطأ أثناء جمع الملاحظة. يرجى المحاولة مرة أخرى.");
        return;
      }

      var note = noteTextarea.value.trim();

      if (note === "") {
        // Show validation error
        var feedback = noteTextarea.nextElementSibling;
        if (feedback) {
          feedback.style.display = "block";
        }
        noteTextarea.classList.add("is-invalid");
        return false; // tell showModal not to close
      } else {
        // Hide validation error
        var feedback = noteTextarea.nextElementSibling;
        if (feedback) {
          feedback.style.display = "none";
        }
        noteTextarea.classList.remove("is-invalid");
      }

      // Attach note as hidden field to the form safely (courseId may contain special chars)
      var parentForm = document.getElementById(`form_${courseId}`);
      var unapprovedNoteField = parentForm
        ? parentForm.querySelector('input[name="unapprove_note"]')
        : null;
      if (unapprovedNoteField) {
        unapprovedNoteField.value = note;
      } else {
        unapprovedNoteField = document.createElement("input");
        unapprovedNoteField.type = "hidden";
        unapprovedNoteField.name = "unapprove_note";
        unapprovedNoteField.value = note;
        if (parentForm) {
          parentForm.appendChild(unapprovedNoteField);
        }
      }

      // Submit form
      unapproveCourseHandler(courseId);
      if (afterConfirm) afterConfirm();
      return true;
    });

    // Focus on textarea after modal shows
    setTimeout(function () {
      var textarea = document.getElementById(`unapprovedNote-${courseId}`);
      if (textarea) {
        textarea.focus();
      }
    }, 300);
  };

  // Function to show the approve modal (confirmation only)
  window.showApproveCourseModal = function (courseId, afterConfirm) {
    var message = `
          <p>هل أنت متأكد من رغبتك في اعتماد الدورة؟</p>
      `;

    showModal(" اعتماد الدورة", message, function () {
      // No note required, just submit the form
      approveCourseHandler(courseId);
      if (afterConfirm) afterConfirm();
    });
  };

  // Handler for approving a course
  window.approveCourseHandler = function (courseId) {
    document.getElementById(`form_${courseId}`).submit();
  };

  // Handler for unapproving a course
  window.unapproveCourseHandler = function (courseId) {
    document.getElementById(`form_${courseId}`).submit();
  };
});
