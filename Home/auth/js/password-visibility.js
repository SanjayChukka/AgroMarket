/* Password Visibility Toggle */
for (const input of document.querySelectorAll('input[type="password"]')) {
  /* Create Password Wrapper */
  const wrapper = document.createElement("span");
  wrapper.className = "password-control";

  input.before(wrapper);
  wrapper.append(input);

  /* Create Show/Hide Button */
  const button = document.createElement("button");

  button.type = "button";
  button.className = "password-eye";

  /* Accessibility Attributes */
  const label = input.labels[0]?.textContent.trim() || "password";

  button.setAttribute("aria-label", "Show " + label);
  button.setAttribute("aria-pressed", "false");
  button.setAttribute("aria-controls", input.id);

  button.title = "Show password";

  /* Eye Icon */
  button.innerHTML = `
        <svg
            viewBox="0 0 24 24"
            aria-hidden="true"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
        >
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
            <circle cx="12" cy="12" r="3"/>
            <path class="eye-slash" d="M3 3l18 18"/>
        </svg>
    `;

  wrapper.append(button);

  /* Prevent Button from Taking Input Focus */
  button.addEventListener("mousedown", (event) => {
    event.preventDefault();
  });

  /* Toggle Password Visibility */
  button.addEventListener("click", () => {
    const reveal = input.type === "password";

    const start = input.selectionStart;
    const end = input.selectionEnd;

    input.type = reveal ? "text" : "password";

    input.setSelectionRange(start, end);

    button.setAttribute("aria-pressed", String(reveal));

    button.title = reveal ? "Hide password" : "Show password";
  });
}
