function getCurrentDate() {
  return new Date().toISOString().split("T")[0];
}

function validateEmail(email) {
  const re = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  return re.test(String(email).toLowerCase());
}

function generateCaptchaString(length = 6) {
  const permittedChars = "ABCDEFGHJKLMNPQRSTUVWXYZ";
  let result = "";
  for (let i = 0; i < length; i++) {
    result += permittedChars.charAt(
      Math.floor(Math.random() * permittedChars.length),
    );
  }
  return result;
}

function prepareFormData() {
  const formData = new FormData();

  // Fecha de registro (current date)
  formData.append("et_pb_contact_fecha_ingreso_0", getCurrentDate());

  // Personal Information
  formData.append(
    "et_pb_contact_nombres_0",
    document.getElementById("first-name").value,
  );
  formData.append(
    "et_pb_contact_apellidos_0",
    document.getElementById("last-name").value,
  );
  formData.append(
    "et_pb_contact_dni_0",
    document.getElementById("id-number").value,
  );
  formData.append(
    "et_pb_contact_nacionalidad_0",
    document.getElementById("nationality").value,
  );
  formData.append(
    "et_pb_contact_genero_0",
    document.querySelector('input[name="gender"]:checked')?.value,
  );

  const birthDate = `${document.getElementById("birth-year").value}-${document.getElementById("birth-month").value}-${document.getElementById("birth-day").value}`;
  formData.append("et_pb_contact_fecha_nacimiento_0", birthDate);

  formData.append(
    "et_pb_contact_phone_0",
    document.getElementById("mobile-phone").value.replace(/\D/g, ""),
  );
  formData.append(
    "et_pb_contact_email_0",
    document.getElementById("institutional-email").value,
  );

  // Institution Information
  formData.append(
    "organizacion",
    document.getElementById("institution-name").value,
  );
  formData.append(
    "organizacion_facultad",
    document.getElementById("campus-faculty").value,
  );
  formData.append(
    "organizacion_facultad_carrera",
    document.getElementById("specific-unit-career").value,
  );
  formData.append(
    "et_pb_contact_rol_0",
    document.getElementById("institutional-role").value,
  );

  // Research Information
  formData.append(
    "et_pb_contact_pronii_categoria_0",
    document.getElementById("pronii-category").value,
  );
  formData.append(
    "et_pb_contact_orcid_0",
    document.getElementById("orcid-id").value,
  );
  formData.append(
    "et_pb_contact_scopus_0",
    document.getElementById("scopus-id").value,
  );
  formData.append(
    "et_pb_contact_wos_0",
    document.getElementById("wos-id").value,
  );

  // Location Information
  formData.append(
    "et_pb_contact_departamento_0",
    document.getElementById("department").value,
  );
  formData.append(
    "et_pb_contact_ciudad_0",
    document.getElementById("city").value,
  );

  // Research Areas
  const researchAreas = document.querySelectorAll(
    'input[name="research-area"]:checked',
  );
  researchAreas.forEach((area, index) => {
    formData.append(
      `et_pb_contact_area_investigacion_0_23_${index}`,
      area.value,
    );
  });

  // Generate and append captcha
  const captchaString = generateCaptchaString();
  formData.append("captcha_challenge", captchaString);
  formData.append("captcha", captchaString);

  return formData;
}

function validateForm(formData) {
  let errors = [];

  if (!formData.get("et_pb_contact_nombres_0")) errors.push("Nombres");
  if (!formData.get("et_pb_contact_apellidos_0")) errors.push("Apellidos");

  const dni = formData.get("et_pb_contact_dni_0");
  if (!/^\d{5,15}$/.test(dni)) errors.push("No. de Cédula de Identidad");

  if (!formData.get("et_pb_contact_nacionalidad_0"))
    errors.push("Nacionalidad");
  if (!formData.get("et_pb_contact_genero_0")) errors.push("Sexo");
  if (!formData.get("et_pb_contact_fecha_nacimiento_0"))
    errors.push("Fecha de nacimiento");

  const phone = formData.get("et_pb_contact_phone_0");
  if (!/^\d{9}$/.test(phone)) errors.push("No. de teléfono celular");

  if (!validateEmail(formData.get("et_pb_contact_email_0")))
    errors.push("Correo electrónico institucional");

  if (!formData.get("et_pb_contact_departamento_0"))
    errors.push("Departamento");
  if (!formData.get("et_pb_contact_ciudad_0")) errors.push("Ciudad");

  if (!formData.get("organizacion")) errors.push("Nombre de institución");
  if (!formData.get("organizacion_facultad")) errors.push("Sede o Facultad");
  if (!formData.get("organizacion_facultad_carrera"))
    errors.push("Unidad o Carrera específica");
  if (!formData.get("et_pb_contact_rol_0"))
    errors.push("Rol dentro de la institución");

  if (
    ![...formData.entries()].some(([key]) =>
      key.startsWith("et_pb_contact_area_investigacion_0_23_"),
    )
  ) {
    errors.push("Área principal de investigación");
  }

  return errors;
}

function displayErrorMessages(errors) {
  errors.forEach((error) => {
    const element = document.getElementById(
      `${error.toLowerCase().replace(/ /g, "-")}-error`,
    );
    if (element) {
      element.textContent = `Por favor, complete el campo: ${error}`;
      element.classList.remove("hidden");
    }
  });
}

function hideErrorMessages() {
  const errorElements = document.querySelectorAll('[id$="-error"]');
  errorElements.forEach((elem) => {
    elem.textContent = "";
    elem.classList.add("hidden");
  });
}

function setCaptchaCookie() {
  const captchaString = generateCaptchaString();
  document.cookie = `PHPSESSID=simulated_session_id; path=/`;
  document.cookie = `captcha_text=${captchaString}; path=/`;
}

function showErrorMessage(message) {
  const errorDiv = document.getElementById("form-error-message");
  const errorText = document.getElementById("error-text");
  errorText.textContent = message;
  errorDiv.classList.remove("hidden");
  errorDiv.scrollIntoView({ behavior: "smooth", block: "start" });
}

function hideErrorMessage() {
  const errorDiv = document.getElementById("form-error-message");
  errorDiv.classList.add("hidden");
}

function submitForm(event) {
  event.preventDefault();

  hideErrorMessages();
  hideErrorMessage();
  const captchaString = setCaptchaCookie();
  const formData = new FormData(event.target);
  formData.append("captcha_challenge", captchaString);
  formData.append("captcha", captchaString);

  const errors = validateForm(formData);

  if (errors.length > 0) {
    console.error("Form validation failed. Errors:", errors);
    displayErrorMessages(errors);
    showErrorMessage(
      "Por favor, corrija los errores en el formulario antes de enviarlo.",
    );
    return;
  }

  console.log("Form data prepared:", Object.fromEntries(formData));

  // Submit the form
  fetch(
    "https://cicco.conacyt.gov.py/solicitud_registro/procesar_ingreso_2023_NEW.php",
    {
      method: "POST",
      body: formData,
    },
  )
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.text();
    })
    .then((result) => {
      console.log("Server response:", result);
      if (result.includes("Location:")) {
        const url = result.split("Location: ")[1].trim();
        window.location.href = url;
      } else {
        hideErrorMessage();
        alert("Formulario enviado con éxito!");
      }
    })
    .catch((error) => {
      console.error("Error submitting form:", error);
      showErrorMessage(
        "Hubo un error al enviar el formulario. Por favor, inténtelo de nuevo. Los datos del formulario se han mantenido.",
      );
    });
}

// Attach submit event to the form
document
  .getElementById("registration-form")
  .addEventListener("submit", submitForm);

// Add real-time validation
document.querySelectorAll("input, select").forEach((element) => {
  element.addEventListener("blur", function () {
    const formData = new FormData();
    formData.append(this.name, this.value);
    const errors = validateForm(formData);
    if (errors.length > 0) {
      displayErrorMessages(errors);
    } else {
      hideErrorMessages();
    }
  });
});

// Make sure at least one research area is selected
const researchAreas = document.querySelectorAll('input[name="research-area"]');
researchAreas.forEach((checkbox) => {
  checkbox.addEventListener("change", function () {
    const atLeastOneChecked = [...researchAreas].some((cb) => cb.checked);
    researchAreas.forEach((cb) => (cb.required = !atLeastOneChecked));
  });
});

// Make ORCID, Scopus, and WoS IDs optional
document.getElementById("orcid-id").required = false;
document.getElementById("scopus-id").required = false;
document.getElementById("wos-id").required = false;
