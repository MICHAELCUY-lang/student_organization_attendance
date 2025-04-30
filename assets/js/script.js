// assets/js/script.js

/**
 * Student Organization Attendance System
 * Custom JavaScript functions
 */

// Document ready function
$(document).ready(function () {
  // Initialize tooltips
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Initialize popovers
  var popoverTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="popover"]')
  );
  var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl);
  });

  // Auto-hide alert messages after 5 seconds
  setTimeout(function () {
    $(".alert").not(".alert-permanent").alert("close");
  }, 5000);

  // Handle sidebar toggle in mobile view
  $(".sidebar-toggle").on("click", function () {
    $(".sidebar").toggleClass("show");
  });

  // Click outside sidebar to close it (mobile)
  $(document).on("click", function (e) {
    if ($(window).width() < 768) {
      if (
        !$(e.target).closest(".sidebar").length &&
        !$(e.target).closest(".sidebar-toggle").length
      ) {
        $(".sidebar").removeClass("show");
      }
    }
  });

  // Prevent form resubmission on page refresh
  if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
  }

  // Add confirm dialog to delete buttons
  $(".btn-delete").on("click", function (e) {
    return confirm("Are you sure you want to delete this item?");
  });

  // Date picker initialization for date inputs
  if ($(".datepicker").length) {
    $(".datepicker").flatpickr({
      dateFormat: "Y-m-d",
      allowInput: true,
    });
  }

  // Time picker initialization for time inputs
  if ($(".timepicker").length) {
    $(".timepicker").flatpickr({
      enableTime: true,
      noCalendar: true,
      dateFormat: "H:i",
      time_24hr: true,
      allowInput: true,
    });
  }

  // Select2 initialization for select elements
  if ($(".select2").length) {
    $(".select2").select2({
      theme: "bootstrap4",
      width: "100%",
    });
  }

  // DataTables initialization
  if ($(".datatable").length) {
    $(".datatable").each(function () {
      var options = {
        responsive: true,
        lengthMenu: [
          [10, 25, 50, -1],
          [10, 25, 50, "All"],
        ],
        language: {
          search: "_INPUT_",
          searchPlaceholder: "Search...",
          lengthMenu: "_MENU_ records per page",
          zeroRecords: "No matching records found",
          info: "Showing _START_ to _END_ of _TOTAL_ records",
          infoEmpty: "No records available",
          infoFiltered: "(filtered from _MAX_ total records)",
        },
      };

      // Check for custom options in data attribute
      var customOptions = $(this).data("options");
      if (customOptions) {
        options = $.extend({}, options, customOptions);
      }

      $(this).DataTable(options);
    });
  }

  // Checkbox "select all" functionality
  $("#selectAll").on("click", function () {
    $(".checkbox-item").prop("checked", this.checked);
  });

  $(".checkbox-item").on("click", function () {
    if ($(".checkbox-item:checked").length === $(".checkbox-item").length) {
      $("#selectAll").prop("checked", true);
    } else {
      $("#selectAll").prop("checked", false);
    }
  });

  // Multi-step form navigation
  $(".next-step").on("click", function () {
    var currentStep = $(this).closest(".step");
    var nextStep = $(this).closest(".step").next(".step");

    // Validate current step
    if (validateStep(currentStep)) {
      currentStep.removeClass("active").addClass("done");
      nextStep.addClass("active");

      // Update progress
      var progress = (nextStep.index() / $(".step").length) * 100;
      $(".progress-bar").css("width", progress + "%");
    }
  });

  $(".prev-step").on("click", function () {
    var currentStep = $(this).closest(".step");
    var prevStep = $(this).closest(".step").prev(".step");

    currentStep.removeClass("active");
    prevStep.removeClass("done").addClass("active");

    // Update progress
    var progress = (prevStep.index() / $(".step").length) * 100;
    $(".progress-bar").css("width", progress + "%");
  });

  // Form validation function
  function validateStep(step) {
    var isValid = true;

    // Check required fields
    step
      .find("input[required], select[required], textarea[required]")
      .each(function () {
        if ($(this).val() === "") {
          isValid = false;
          $(this).addClass("is-invalid");
        } else {
          $(this).removeClass("is-invalid");
        }
      });

    // Show error message if validation fails
    if (!isValid) {
      step.find(".validation-message").show();
    }

    return isValid;
  }

  // Attendance status color coding
  $(".attendance-status").each(function () {
    var status = $(this).data("status");
    switch (status) {
      case "hadir":
        $(this).addClass("text-success");
        break;
      case "izin":
        $(this).addClass("text-warning");
        break;
      case "alpa":
        $(this).addClass("text-danger");
        break;
      case "telat":
        $(this).addClass("text-info");
        break;
      default:
        $(this).addClass("text-secondary");
    }
  });

  // Dynamically add/remove form inputs
  $(".add-input-row").on("click", function () {
    var container = $(this).closest(".form-group").find(".input-rows");
    var newRow = container.find(".input-row:first").clone();

    // Clear values and increment IDs
    newRow
      .find("input, select")
      .val("")
      .each(function () {
        var id = $(this).attr("id");
        if (id) {
          var index = parseInt(id.match(/\d+/)[0]) + 1;
          var newId = id.replace(/\d+/, index);
          $(this).attr("id", newId);
          $(this).attr("name", newId);
        }
      });

    // Add remove button to new row
    if (newRow.find(".remove-row").length === 0) {
      newRow.append(
        '<button type="button" class="btn btn-sm btn-danger remove-row"><i class="bi bi-dash-circle"></i></button>'
      );
    }

    container.append(newRow);
  });

  // Remove input row
  $(document).on("click", ".remove-row", function () {
    $(this).closest(".input-row").remove();
  });

  // Live search functionality
  $("#liveSearch").on("keyup", function () {
    var value = $(this).val().toLowerCase();
    $(".searchable-item").filter(function () {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
  });

  // Print report button
  $(".btn-print").on("click", function () {
    window.print();
  });

  // Export to Excel function
  $(".btn-excel").on("click", function () {
    var table = $(this).data("table");
    exportTableToExcel(table);
  });

  // Function to export HTML table to Excel
  function exportTableToExcel(tableId, filename = "") {
    var table = document.getElementById(tableId);
    var html = table.outerHTML;

    if (filename === "") {
      filename = "export_" + new Date().toISOString().substring(0, 10) + ".xls";
    }

    var blob = new Blob([html], { type: "application/vnd.ms-excel" });
    var link = document.createElement("a");

    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
  }

  // Export to PDF function
  $(".btn-pdf").on("click", function () {
    var element = document.getElementById($(this).data("element"));
    var opt = {
      margin: 0.5,
      filename: "report_" + new Date().toISOString().substring(0, 10) + ".pdf",
      image: { type: "jpeg", quality: 0.98 },
      html2canvas: { scale: 2 },
      jsPDF: { unit: "in", format: "letter", orientation: "portrait" },
    };

    // Use html2pdf library if available
    if (typeof html2pdf !== "undefined") {
      html2pdf().set(opt).from(element).save();
    } else {
      console.error("html2pdf library not loaded");
      alert("PDF export library not loaded. Please try again later.");
    }
  });
});
