/* Registration Form Elements */
const form = document.querySelector(".registration-form");

const fields = [
  ...form.querySelectorAll("input[id][name], select[id][name], textarea[id][name]"),
];

const farmer = form.dataset.role === "farmer";
const popup = document.getElementById("popupCard");
const password = form.elements.password;

/* Validation Patterns */
const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const mobilePattern = /^[6-9][0-9]{9}$/;

const duplicateFields = ["username", "email", "mobile"];

/* Validation State */
const timers = new Map();
const touched = new Set();

let submitting = false;
let verified = false;
let revision = 0;

/* Display Registration Popup */
function showPopup(type, message) {
  const success = type === "success";

  popup.dataset.type = type;

  popup.querySelector(".popup-card").classList.toggle("error", !success);

  popup.querySelector("#popupIcon").textContent = success ? "✓" : "✕";

  popup.querySelector("#popupTitle").textContent = success
    ? "Registration complete"
    : "Registration unsuccessful";

  const text = popup.querySelector("#popupText");
  text.textContent = message;

  popup.querySelector("#closePopup").textContent = success
    ? "Continue to homepage"
    : "Return to form";

  if (!popup.open) {
    popup.showModal();
  }
}

/* Popup Close Events */
popup.addEventListener("cancel", (event) => {
  event.preventDefault();

  popup.querySelector("#closePopup").click();
});

popup.querySelector("#closePopup").addEventListener("click", () => {
  popup.close();

  if (popup.dataset.type === "success") {
    window.location.href = "../index.php";
  }
});

/* Show Server-Side Popup Message */
if (popup.dataset.message) {
  showPopup(popup.dataset.type, popup.dataset.message);
}

/* Password Validation Rules */
function passwordRules(value) {
  return [
    value.length >= 8,
    /[A-Z]/.test(value),
    /[a-z]/.test(value),
    /[0-9]/.test(value),
    /[^A-Za-z0-9]/.test(value),
  ];
}

/* Update Password Requirements and Strength */
function updatePasswordFeedback() {
  const passed = passwordRules(password.value);

  form.querySelectorAll(".password-requirements li").forEach((item, index) => {
    const valid = passed[index];

    item.dataset.label ||= item.textContent.replace(/^[✓✗]\s*/, "");

    item.textContent = (valid ? "✓ " : "✗ ") + item.dataset.label;

    item.classList.toggle("valid", valid);
    item.classList.toggle("invalid", !valid);

    item.style.color = valid ? "#198754" : "#dc3545";
  });

  const bar = document.getElementById("strengthBar");

  if (!bar) {
    return;
  }

  const count = passed.filter(Boolean).length;

  const labels = [
    "Not entered",
    "Very weak",
    "Weak",
    "Medium",
    "Strong",
    "Very strong",
  ];

  const colors = ["", "#dc3545", "#fd7e14", "#ffc107", "#0d6efd", "#198754"];

  bar.style.width = count * 20 + "%";
  bar.style.background = colors[count];

  document.getElementById("strengthText").textContent =
    "Password strength: " + labels[count];
}

/* Validate Individual Fields */
function validationMessage(field) {
  const value = field.value.trim();

  switch (field.name) {
    case "first_name":
    case "last_name":
    case "district":
    case "address":
    case "address_line":
    case "village":
      return value ? "" : "This field is required.";

    case "state":
    case "buyer_type":
      return value ? "" : "Please select an option.";

    case "username":
      if (!value) {
        return "Username is required.";
      }

      return value.length >= 3
        ? ""
        : "Username must contain at least 3 characters.";

    case "email":
      if (!value) {
        return "Email address is required.";
      }

      return emailPattern.test(value) ? "" : "Enter a valid email address.";

    case "mobile":
      if (!value) {
        return "Mobile number is required.";
      }

      return mobilePattern.test(value)
        ? ""
        : "Enter a valid 10-digit Indian mobile number.";

    case "password":
      if (!field.value) {
        return "Password is required.";
      }

      return passwordRules(field.value).every(Boolean)
        ? ""
        : "Password does not meet all the requirements below.";

    case "confirm_password":
      if (!field.value) {
        return "Please confirm your password.";
      }

      return field.value === password.value ? "" : "Passwords do not match.";

    case "pincode":
      if (farmer && !value) {
        return "Pincode is required.";
      }

      return value && !/^\d{6}$/.test(value)
        ? "Enter a valid 6-digit PIN code."
        : "";

    case "land_size":
      return value && !(Number(value) > 0 && Number.isFinite(Number(value)))
        ? "Enter a valid land size greater than zero."
        : "";

    default:
      return "";
  }
}

/* Display Field Validation Feedback */
function setFeedback(field, message, markValid = true) {
  let error = document.getElementById(field.id + "Error");

  if (!error && message) {
    error = document.createElement("small");

    error.id = field.id + "Error";
    error.className = "field-error";

    field.insertAdjacentElement("afterend", error);
  }

  if (error) {
    error.textContent = message;
  }

  field.classList.toggle("input-error", Boolean(message));

  field.classList.toggle(
    "input-valid",
    markValid && !message && Boolean(field.value.trim()),
  );
}

/* Check Duplicate Registration Details */
const requests = new Map();

async function checkDuplicate(field) {
  const value = field.value.trim();
  const request = Symbol();

  requests.set(field, request);

  const body = new FormData();

  body.set("field", field.name);
  body.set("value", value);

  let message = "";

  try {
    const response = await fetch("check_duplicate.php", {
      method: "POST",
      body,
    });

    if (!response.ok) {
      throw new Error("Verification failed");
    }

    const result = await response.json();

    if (result.exists) {
      message = result.message || "This value is already registered.";
    }
  } catch {
    message = "Unable to verify this value. Please try again.";
  }

  /* Ignore Outdated Responses */
  if (requests.get(field) !== request || field.value.trim() !== value) {
    return false;
  }

  setFeedback(field, message);

  return !message;
}

/* Attach Validation Events to Fields */
for (const field of fields) {
  const needsCheck = duplicateFields.includes(field.name);

  /* Validate While Typing */
  field.addEventListener("input", () => {
    revision++;

    const message = validationMessage(field);

    if (touched.has(field)) {
      setFeedback(field, message, !needsCheck);
    } else if (needsCheck) {
      setFeedback(field, "", false);
    }

    /* Update Password Feedback */
    if (field === password) {
      updatePasswordFeedback();

      const confirmation = form.elements.confirm_password;

      if (touched.has(confirmation)) {
        setFeedback(confirmation, validationMessage(confirmation));
      }
    }

    /* Schedule Duplicate Check */
    if (needsCheck) {
      clearTimeout(timers.get(field));

      requests.delete(field);

      if (!message) {
        timers.set(
          field,
          setTimeout(() => checkDuplicate(field), 450),
        );
      }
    }
  });

  /* Validate on Change */
  field.addEventListener("change", () => {
    touched.add(field);

    setFeedback(field, validationMessage(field));
  });

  /* Validate When Leaving Field */
  field.addEventListener("blur", () => {
    touched.add(field);

    const message = validationMessage(field);

    setFeedback(field, message);

    if (needsCheck && !message && !submitting) {
      clearTimeout(timers.get(field));

      checkDuplicate(field);
    }
  });
}

/* Validate Registration Before Submission */
form.addEventListener("submit", async (event) => {
  if (verified) {
    return;
  }

  event.preventDefault();

  if (submitting) {
    return;
  }

  let firstInvalid;

  /* Validate All Fields */
  for (const field of fields) {
    touched.add(field);

    const message = validationMessage(field);

    setFeedback(field, message);

    if (message && !firstInvalid) {
      firstInvalid = field;
    }
  }

  /* Handle Invalid Form */
  if (firstInvalid) {
    firstInvalid.focus();

    showPopup(
      "error",
      "Please correct the highlighted fields before submitting.",
    );

    return;
  }

  /* Verify Duplicate Fields */
  submitting = true;

  const startRevision = revision;

  const checks = duplicateFields.map((name) => {
    const field = form.elements[name];

    clearTimeout(timers.get(field));

    return checkDuplicate(field);
  });

  const results = await Promise.all(checks);

  submitting = false;

  if (revision !== startRevision) {
    return;
  }

  const failed = results.indexOf(false);

  if (failed !== -1) {
    form.elements[duplicateFields[failed]].focus();

    showPopup("error", "Please check the highlighted registration details.");

    return;
  }

  /* Submit Form to PHP */
  verified = true;

  form.requestSubmit(form.querySelector('button[name="register"]'));

  verified = false;
});

/* Reset Form After Browser Back/Forward Navigation */
window.addEventListener("pageshow", (event) => {
  const navigation = performance.getEntriesByType("navigation")[0];

  if (!event.persisted && navigation?.type !== "back_forward") {
    return;
  }

  revision++;

  form.reset();
  touched.clear();
  requests.clear();

  for (const timer of timers.values()) {
    clearTimeout(timer);
  }

  for (const field of fields) {
    setFeedback(field, "", false);
  }

  popup.close();

  updatePasswordFeedback();
});

/* Initialize Password Feedback */
updatePasswordFeedback();
