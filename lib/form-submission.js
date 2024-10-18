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

function mapFormData(formData) {
  const mappedData = new FormData();

  // Define your mapping
  const fieldMapping = {
    "first-name": "et_pb_contact_nombres_0",
    "last-name": "et_pb_contact_apellidos_0",
    "id-number": "et_pb_contact_dni_0",
    nationality: "et_pb_contact_nacionalidad_0",
    gender: "et_pb_contact_genero_0",
    "mobile-phone": "et_pb_contact_phone_0",
    "institutional-email": "et_pb_contact_email_0",
    department: "et_pb_contact_departamento_0",
    city: "et_pb_contact_ciudad_0",
    "institution-name": "organizacion",
    "campus-faculty": "organizacion_facultad",
    "specific-unit-career": "organizacion_facultad_carrera",
    "institutional-role": "et_pb_contact_rol_0",
    "pronii-category": "et_pb_contact_categoria_pronii_0",
    "orcid-id": "et_pb_contact_orcid_0",
    "scopus-id": "et_pb_contact_scopus_0",
    "wos-id": "et_pb_contact_wos_0",
  };

  // Map the fields
  for (let [key, value] of formData.entries()) {
    const mappedKey = fieldMapping[key] || key;
    mappedData.append(mappedKey, value);
  }

  // Handle special cases like date and research areas
  const birthDate = `${formData.get("birth-year")}-${formData.get("birth-month")}-${formData.get("birth-day")}`;
  mappedData.append("et_pb_contact_fecha_nacimiento_0", birthDate);

  const researchAreas = formData.getAll("research-area");
  researchAreas.forEach((area, index) => {
    mappedData.append(`et_pb_contact_area_investigacion_0_23_${index}`, area);
  });

  return mappedData;
}

function prepareFormData() {
  const form = document.getElementById("registration-form");
  const formData = new FormData(form);

  // Add any additional data that's not part of the form
  formData.append("et_pb_contact_fecha_ingreso_0", getCurrentDate());

  return mapFormData(formData);
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

  const birthYear = formData.get("birth-year");
  const birthMonth = formData.get("birth-month");
  const birthDay = formData.get("birth-day");
  if (!birthYear || !birthMonth || !birthDay)
    errors.push("Fecha de nacimiento");

  const phone = formData.get("et_pb_contact_phone_0");
  if (!/^\(\d{3}\)\s\d{3}-\d{3}$/.test(phone))
    errors.push("No. de teléfono celular");

  const email = formData.get("et_pb_contact_email_0");
  if (!validateEmail(email)) errors.push("Correo electrónico institucional");

  if (!formData.get("et_pb_contact_departamento_0"))
    errors.push("Departamento");

  if (!formData.get("et_pb_contact_ciudad_0")) errors.push("Ciudad");

  if (!formData.get("organizacion")) errors.push("Nombre de institución");

  if (!formData.get("organizacion_facultad")) errors.push("Sede o Facultad");

  if (!formData.get("organizacion_facultad_carrera"))
    errors.push("Unidad o Carrera específica");

  if (!formData.get("et_pb_contact_rol_0"))
    errors.push("Rol dentro de la institución");

  const hasResearchArea = [...formData.entries()].some(([key]) =>
    key.startsWith("et_pb_contact_area_investigacion_0_23_"),
  );
  if (!hasResearchArea) {
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
  console.log("Form submission started");

  hideErrorMessages();
  hideErrorMessage();
  const formData = prepareFormData();
  console.log("Form data prepared");

  const errors = validateForm(formData);
  console.log("Validation complete. Errors:", errors);

  if (errors.length > 0) {
    console.error("Form validation failed. Errors:", errors);
    displayErrorMessages(errors);
    showErrorMessage(
      "Por favor, corrija los errores en el formulario antes de enviarlo.",
    );
    return;
  }

  console.log("Form data valid, proceeding with submission");

  // Show loading indicator
  const loadingIndicator = document.getElementById("loading-indicator");
  loadingIndicator.classList.remove("hidden");

  // Set captcha cookie
  setCaptchaCookie();

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
    })
    .finally(() => {
      // Hide loading indicator
      loadingIndicator.classList.add("hidden");
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

// Format phone number as user types
document.getElementById("mobile-phone").addEventListener("input", function (e) {
  let x = e.target.value
    .replace(/\D/g, "")
    .match(/(\d{0,3})(\d{0,3})(\d{0,3})/);
  e.target.value = !x[2]
    ? x[1]
    : "(" + x[1] + ") " + x[2] + (x[3] ? "-" + x[3] : "");
});

// Implement auto-save functionality
let autoSaveTimer;
document.querySelectorAll("input, select").forEach((element) => {
  element.addEventListener("change", function () {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(() => {
      localStorage.setItem(
        "formData",
        JSON.stringify(
          Object.fromEntries(
            new FormData(document.getElementById("registration-form")),
          ),
        ),
      );
    }, 1000);
  });
});

// Load saved form data on page load
window.addEventListener("load", function () {
  const savedData = JSON.parse(localStorage.getItem("formData"));
  if (savedData) {
    Object.keys(savedData).forEach((key) => {
      const field = document.getElementsByName(key)[0];
      if (field) {
        field.value = savedData[key];
      }
    });
  }
});
