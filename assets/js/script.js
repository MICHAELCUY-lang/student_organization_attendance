// assets/js/script.js

/**
 * Student Organization Attendance System
 * Custom JavaScript functions
 */

// Document ready function
$(document).ready(function () {
  // Initialize animations
  animateStatsCounter();

  // Prevent form resubmission on page refresh
  if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
  }

  // Add active class to parent when child link is active
  $(".sidebar-menu .sidebar-item .sidebar-link.active")
    .parents(".sidebar-item")
    .addClass("active");

  // Multi-step form navigation with validation
  setupMultiStepForm();

  // Checkbox "select all" functionality for attendance records
  setupSelectAllCheckboxes();

  // Live search functionality
  setupLiveSearch();

  // Enable responsive tables
  $(".table-responsive")
    .on("shown.bs.dropdown", function (e) {
      var t = $(this),
        m = $(e.target).find(".dropdown-menu"),
        tb = t.offset().top + t.height(),
        mb = m.offset().top + m.outerHeight(true),
        d = 20; // Space for shadow
      if (t[0].scrollWidth > t.innerWidth()) {
        if (mb + d > tb) {
          t.css("padding-bottom", mb + d - tb);
        }
      } else {
        t.css("overflow", "visible");
      }
    })
    .on("hidden.bs.dropdown", function () {
      $(this).css({ "padding-bottom": "", overflow: "" });
    });

  // Dynamically add/remove form inputs
  handleDynamicFormInputs();

  // Initialize custom tabs if present
  initCustomTabs();

  // Print and export functions
  setupPrintAndExport();

  // Chart initialization (if applicable)
  initializeCharts();
});

/**
 * Animate counter for dashboard statistics
 */
function animateStatsCounter() {
  $(".counter-value").each(function () {
    var $this = $(this);
    var countTo = parseInt($this.text());

    $({ countNum: 0 }).animate(
      { countNum: countTo },
      {
        duration: 1000,
        easing: "swing",
        step: function () {
          $this.text(Math.floor(this.countNum));
        },
        complete: function () {
          $this.text(this.countNum);
        },
      }
    );
  });
}

/**
 * Set up multi-step form with validation
 */
function setupMultiStepForm() {
  // Next step button click
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

      // Scroll to top of the form
      $("html, body").animate(
        {
          scrollTop: $(".step.active").offset().top - 100,
        },
        500
      );
    }
  });

  // Previous step button click
  $(".prev-step").on("click", function () {
    var currentStep = $(this).closest(".step");
    var prevStep = $(this).closest(".step").prev(".step");

    currentStep.removeClass("active");
    prevStep.removeClass("done").addClass("active");

    // Update progress
    var progress = (prevStep.index() / $(".step").length) * 100;
    $(".progress-bar").css("width", progress + "%");

    // Scroll to top of the form
    $("html, body").animate(
      {
        scrollTop: $(".step.active").offset().top - 100,
      },
      500
    );
  });
}

/**
 * Validate a form step
 * @param {jQuery} step The step element to validate
 * @returns {boolean} Whether the step is valid
 */
function validateStep(step) {
  var isValid = true;
  var firstInvalid = null;

  // Check required fields
  step
    .find("input[required], select[required], textarea[required]")
    .each(function () {
      $(this).removeClass("is-invalid");

      if ($(this).val() === "") {
        isValid = false;
        $(this).addClass("is-invalid");

        if (!firstInvalid) {
          firstInvalid = $(this);
        }
      }
    });

  // Check email format if required
  step.find("input[type='email']").each(function () {
    if ($(this).val() && !isValidEmail($(this).val())) {
      isValid = false;
      $(this).addClass("is-invalid");

      if (!firstInvalid) {
        firstInvalid = $(this);
      }
    }
  });

  // Focus on first invalid input
  if (firstInvalid) {
    firstInvalid.focus();
  }

  // Show error message if validation fails
  if (!isValid) {
    step.find(".validation-message").show();

    // Show toast notification
    Swal.fire({
      toast: true,
      position: "top-end",
      icon: "error",
      title: "Please fill all required fields",
      showConfirmButton: false,
      timer: 3000,
    });
  }

  return isValid;
}

/**
 * Validate email format
 * @param {string} email Email to validate
 * @returns {boolean} Whether the email is valid
 */
function isValidEmail(email) {
  var pattern =
    /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  return pattern.test(email);
}

/**
 * Set up "select all" checkboxes functionality
 */
function setupSelectAllCheckboxes() {
  $("#selectAll").on("click", function () {
    $(".checkbox-item:not(:disabled)").prop("checked", this.checked);

    // Toggle status options based on checkbox state
    if (this.checked) {
      $(".checkbox-item:not(:disabled)").each(function () {
        var memberId = $(this).attr("id").replace("member_", "");
        $("#status_" + memberId).slideDown();
      });
    } else {
      $(".checkbox-item:not(:disabled)").each(function () {
        var memberId = $(this).attr("id").replace("member_", "");
        $("#status_" + memberId).slideUp();
      });
    }
  });

  // Update "select all" checkbox when individual checkboxes change
  $(document).on("change", ".checkbox-item", function () {
    // Show/hide status options based on checkbox state
    var memberId = $(this).attr("id").replace("member_", "");
    if ($(this).is(":checked")) {
      $("#status_" + memberId).slideDown();
    } else {
      $("#status_" + memberId).slideUp();
    }

    // Update select all checkbox state
    if (
      $(".checkbox-item:checked").length ===
      $(".checkbox-item:not(:disabled)").length
    ) {
      $("#selectAll").prop("checked", true);
    } else {
      $("#selectAll").prop("checked", false);
    }
  });
}

/**
 * Set up live search functionality
 */
function setupLiveSearch() {
  $("#searchMembers, #searchItems, .search-input").on("keyup", function () {
    var value = $(this).val().toLowerCase();
    var target = $(this).data("search-target") || ".searchable-item";

    $(target).filter(function () {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });

    // Display no results message if no items are visible
    var visibleItems = $(target + ":visible").length;
    var noResultsMsg = $(this).closest(".card").find(".no-results-message");

    if (visibleItems === 0) {
      if (noResultsMsg.length === 0) {
        $(
          '<div class="no-results-message text-center text-muted my-3">No results found</div>'
        ).insertAfter($(this).closest(".card").find(".search-input-container"));
      }
    } else {
      noResultsMsg.remove();
    }
  });
}

/**
 * Handle dynamically adding and removing form inputs
 */
function handleDynamicFormInputs() {
  // Add new input row
  $(".add-input-row").on("click", function () {
    var container = $(this).closest(".form-group").find(".input-rows");
    var newRow = container.find(".input-row:first").clone(true);
    var rowIndex = container.find(".input-row").length;

    // Clear values and increment IDs
    newRow
      .find("input, select, textarea")
      .val("")
      .each(function () {
        var id = $(this).attr("id");
        if (id) {
          var newId = id.replace(/\d+/, rowIndex);
          $(this).attr("id", newId);
          $(this).attr("name", newId);
        }
      });

    // Add remove button to new row if it doesn't exist
    if (newRow.find(".remove-row").length === 0) {
      newRow.append(
        '<button type="button" class="btn btn-sm btn-outline-danger remove-row ms-2">' +
          '<i class="bi bi-dash-circle"></i></button>'
      );
    }

    // Apply fade-in animation
    newRow.css("display", "none");
    container.append(newRow);
    newRow.fadeIn(300);
  });

  // Remove input row
  $(document).on("click", ".remove-row", function () {
    $(this)
      .closest(".input-row")
      .fadeOut(300, function () {
        $(this).remove();
      });
  });
}

/**
 * Initialize custom tab functionality
 */
function initCustomTabs() {
  $(".custom-tab-link").on("click", function (e) {
    e.preventDefault();

    var targetId = $(this).attr("href");

    // Update active state on tabs
    $(".custom-tab-link").removeClass("active");
    $(this).addClass("active");

    // Hide all tab content
    $(".custom-tab-content").removeClass("active").hide();

    // Show target tab content
    $(targetId).addClass("active").fadeIn(300);
  });

  // Activate first tab by default if none is active
  if ($(".custom-tab-link.active").length === 0) {
    $(".custom-tab-link:first").click();
  }
}

/**
 * Set up print and export functionality
 */
function setupPrintAndExport() {
  // Print button
  $(".btn-print").on("click", function () {
    window.print();
  });

  // Export to Excel button
  $(".btn-excel").on("click", function () {
    var table = $(this).data("table");
    exportTableToExcel(table);
  });

  // Export to PDF button
  $(".btn-pdf").on("click", function () {
    var elementId = $(this).data("element");
    if (typeof html2pdf !== "undefined") {
      var element = document.getElementById(elementId);
      var opt = {
        margin: 1,
        filename: "report_" + getFormattedDate() + ".pdf",
        image: { type: "jpeg", quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: "cm", format: "a4", orientation: "portrait" },
      };

      html2pdf().set(opt).from(element).save();
    } else {
      alert("PDF export library not loaded. Please try again later.");
    }
  });
}

/**
 * Export HTML table to Excel
 * @param {string} tableId The ID of the table to export
 * @param {string} filename Optional filename for the export
 */
function exportTableToExcel(tableId, filename = "") {
  var table = document.getElementById(tableId);
  var html = table.outerHTML;

  // Generate filename if not provided
  if (filename === "") {
    filename = "export_" + getFormattedDate() + ".xls";
  }

  // Create download link
  var blob = new Blob([html], { type: "application/vnd.ms-excel" });
  var link = document.createElement("a");

  link.href = URL.createObjectURL(blob);
  link.download = filename;
  link.click();
}

/**
 * Get current date formatted as YYYY-MM-DD
 * @returns {string} Formatted date
 */
function getFormattedDate() {
  var date = new Date();
  var year = date.getFullYear();
  var month = String(date.getMonth() + 1).padStart(2, "0");
  var day = String(date.getDate()).padStart(2, "0");

  return year + "-" + month + "-" + day;
}

/**
 * Initialize charts on the dashboard or reports page
 */
function initializeCharts() {
  // Attendance Overview Chart (if element exists)
  if (document.getElementById("attendanceOverviewChart")) {
    var ctx = document
      .getElementById("attendanceOverviewChart")
      .getContext("2d");

    var chartData = JSON.parse(
      document
        .getElementById("attendanceOverviewChart")
        .getAttribute("data-chart")
    );

    new Chart(ctx, {
      type: "doughnut",
      data: {
        labels: ["Present", "Excused", "Absent", "Late"],
        datasets: [
          {
            data: [
              chartData.present || 0,
              chartData.excused || 0,
              chartData.absent || 0,
              chartData.late || 0,
            ],
            backgroundColor: [
              "#06d6a0", // Success - Present
              "#f9c74f", // Warning - Excused
              "#ef476f", // Danger - Absent
              "#4cc9f0", // Info - Late
            ],
            borderWidth: 0,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: "70%",
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              padding: 20,
              boxWidth: 12,
            },
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                var label = context.label || "";
                var value = context.raw || 0;
                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                var percentage =
                  total > 0 ? Math.round((value / total) * 100) : 0;
                return label + ": " + value + " (" + percentage + "%)";
              },
            },
          },
        },
      },
    });
  }

  // Attendance Trend Chart (if element exists)
  if (document.getElementById("attendanceTrendChart")) {
    var trendCtx = document
      .getElementById("attendanceTrendChart")
      .getContext("2d");
    var trendData = JSON.parse(
      document.getElementById("attendanceTrendChart").getAttribute("data-chart")
    );

    new Chart(trendCtx, {
      type: "line",
      data: {
        labels: trendData.labels,
        datasets: [
          {
            label: "Attendance Rate",
            data: trendData.values,
            borderColor: "#4361ee",
            backgroundColor: "rgba(67, 97, 238, 0.1)",
            tension: 0.3,
            fill: true,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            ticks: {
              callback: function (value) {
                return value + "%";
              },
            },
          },
        },
      },
    });
  }

  // Division Comparison Chart (if element exists)
  if (document.getElementById("divisionComparisonChart")) {
    var divisionCtx = document
      .getElementById("divisionComparisonChart")
      .getContext("2d");
    var divisionData = JSON.parse(
      document
        .getElementById("divisionComparisonChart")
        .getAttribute("data-chart")
    );

    new Chart(divisionCtx, {
      type: "bar",
      data: {
        labels: divisionData.divisions,
        datasets: [
          {
            label: "Attendance Rate",
            data: divisionData.rates,
            backgroundColor:
              divisionData.colors ||
              Array(divisionData.divisions.length).fill("#4361ee"),
            borderWidth: 0,
            borderRadius: 4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            ticks: {
              callback: function (value) {
                return value + "%";
              },
            },
          },
        },
      },
    });
  }
}
