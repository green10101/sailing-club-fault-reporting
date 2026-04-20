document.addEventListener("DOMContentLoaded", function () {
  var singleSubmitForms = document.querySelectorAll(
    'form[data-single-submit="true"]',
  );

  singleSubmitForms.forEach(function (form) {
    form.addEventListener("submit", function (event) {
      if (form.dataset.submitting === "true") {
        event.preventDefault();
        return;
      }

      if (typeof form.reportValidity === "function" && !form.reportValidity()) {
        return;
      }

      form.dataset.submitting = "true";

      var submitButtons = form.querySelectorAll(
        'button[type="submit"], input[type="submit"]',
      );
      submitButtons.forEach(function (button) {
        button.disabled = true;

        var submittingText =
          button.getAttribute("data-submitting-text") || "Submitting...";
        if (button.tagName === "BUTTON") {
          button.textContent = submittingText;
        } else {
          button.value = submittingText;
        }
      });

      var statusMessage = form.querySelector("[data-submit-status]");
      if (statusMessage) {
        statusMessage.textContent = "Submitting report. Please wait...";
      }
    });
  });
});
