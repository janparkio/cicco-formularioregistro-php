// Constants and configurations
const RESEARCH_AREAS = {
  "ciencias-naturales": {
    id: "0",
    value: "Ciencias Naturales",
    pythonKey: "ciencias_naturales",
  },
  "ingenieria-tecnologia": {
    id: "1", 
    value: "Ingeniería y Tecnología",
    pythonKey: "ingenieria_tecnologia",
  },
  "ciencias-medicas-salud": {
    id: "2",
    value: "Ciencias Médicas y de la Salud", 
    pythonKey: "ciencias_medicas_salud",
  },
  "ciencias-agricolas-veterinarias": {
    id: "3",
    value: "Ciencias Agrícolas y Veterinarias",
    pythonKey: "ciencias_agricolas_veterinarias",
  },
  "ciencias-sociales": {
    id: "4",
    value: "Ciencias Sociales",
    pythonKey: "ciencias_sociales",
  },
  "humanidades-artes": {
    id: "5",
    value: "Humanidades y Artes",
    pythonKey: "humanidades_artes",
  },
  "otras-areas": {
    id: "0",
    value: "Otras Áreas",
    pythonKey: "otras_areas",
  },
};

const REQUIRED_FIELDS = {
  et_pb_contact_fecha_ingreso_0: "Fecha de Ingreso",
  et_pb_contact_nombres_0: "Nombres",
  et_pb_contact_apellidos_0: "Apellidos",
  et_pb_contact_nacionalidad_0: "Nacionalidad",
  et_pb_contact_dni_0: "Número de Documento",
  et_pb_contact_genero_0: "Género",
  et_pb_contact_phone_0: "Teléfono",
  et_pb_contact_email_0: "Correo Institucional",
  et_pb_contact_departamento_0: "Departamento",
  et_pb_contact_ciudad_0: "Ciudad",
  organizacion: "Institución",
  organizacion_facultad: "Facultad",
  organizacion_facultad_carrera: "Unidad/Carrera",
  et_pb_contact_rol_0: "Rol Institucional",
};

/**
 * Maps form field IDs to their corresponding backend field names
 * Used for translating between frontend form submissions and backend processing
 * 
 * Key categories:
 * - Personal info (name, birth date, contact)
 * - Institutional info (organization, role)
 * - Research identifiers (ORCID, Scopus, WoS)
 * - Location data (department, city) 
 * - Research areas (natural sciences, engineering, etc)
 */
const FIELD_MAPPINGS = {
  "fecha-ingreso": "et_pb_contact_fecha_ingreso_0",
  "first-name": "et_pb_contact_nombres_0",
  "last-name": "et_pb_contact_apellidos_0", 
  "id-number": "et_pb_contact_dni_0",
  nationality: "et_pb_contact_nacionalidad_0",
  gender: "et_pb_contact_genero_0",
  "birth-date": "et_pb_contact_fecha_nacimiento_0",
  "mobile-phone": "et_pb_contact_phone_0",
  "institutional-email": "et_pb_contact_email_0",
  "institution-name": "organizacion",
  "campus-faculty": "organizacion_facultad",
  "specific-unit-career": "organizacion_facultad_carrera",
  "institutional-role": "et_pb_contact_rol_0",
  "pronii-category": "et_pb_contact_pronii_categoria_0",
  "orcid-id": "et_pb_contact_orcid_0",
  "scopus-id": "et_pb_contact_scopus_0", 
  "wos-id": "et_pb_contact_wos_0",
  department: "et_pb_contact_departamento_0",
  city: "et_pb_contact_ciudad_0",
  "research-area-natural": "et_pb_contact_area_investigacion_0_23_0",
  "research-area-engineering": "et_pb_contact_area_investigacion_0_23_1",
  "research-area-medical": "et_pb_contact_area_investigacion_0_23_2",
  "research-area-agricultural": "et_pb_contact_area_investigacion_0_23_3",
  "research-area-social": "et_pb_contact_area_investigacion_0_23_4",
  "research-area-humanities": "et_pb_contact_area_investigacion_0_23_5"
};

// Utility functions
const getCurrentDate = () => new Date().toISOString().split("T")[0];
const validateEmail = (email) =>
  /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(
    String(email).toLowerCase()
  );
const generateCaptchaString = (length = 6) =>
  Array(length)
    .fill(0)
    .map(() => "ABCDEFGHJKLMNPQRSTUVWXYZ"[Math.floor(Math.random() * 23)])
    .join("");

async function loadJsonData(url) {
  try {
    const response = await fetch(url);
    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
    return await response.json();
  } catch (error) {
    console.warn(`Warning: Could not load ${url}. Using fallback data.`);
    return {};
  }
}

/**
 * Formats a date string to ensure proper YYYY-MM-DD format with leading zeros
 * @param {string} year - The year value
 * @param {string} month - The month value 
 * @param {string} day - The day value
 * @returns {string} Formatted date string in YYYY-MM-DD format
 */
function formatDateString(year, month, day) {
  month = month.padStart(2, '0');
  day = day.padStart(2, '0');
  return `${year}-${month}-${day}`;
}

/**
 * Handles the processing and validation of research areas in the form
 * @param {FormData} formData - The form data object to append research area values to
 * @returns {Object} Object containing validation result with isValid boolean and error details if invalid
 */
function handleResearchAreas(formData) {
  const selectedAreas = Object.keys(RESEARCH_AREAS).filter(id => 
    document.getElementById(id)?.checked
  );

  if (!selectedAreas.length) {
    return {
      isValid: false,
      error: {
        field: 'research-area',
        label: 'Área de Investigación',
        message: 'Por favor, seleccione al menos un área de investigación'
      }
    };
  }

  selectedAreas.forEach(areaKey => {
    const {id, value} = RESEARCH_AREAS[areaKey];
    const fieldName = `et_pb_contact_area_investigacion_0_23_${id}`;
    formData.append(fieldName, value);
    console.log(`Adding research area: ${fieldName} = ${value}`);
  });

  return { isValid: true };
}

/**
 * Prepares form data for submission by mapping fields, handling dates,
 * research areas and captcha validation
 * @param {FormData} rawFormData - The raw form data from the form submission
 * @returns {FormData} Processed form data ready for server submission
 */
function prepareFormData(rawFormData) {
  const formData = new FormData();
  
  console.group('Form Data Preparation');
  console.log('Raw form data:', Object.fromEntries(rawFormData));

  // Add fecha_ingreso as first field
  const currentDate = new Date().toISOString().split('T')[0];
  formData.append("et_pb_contact_fecha_ingreso_0", currentDate);
  console.log('Added fecha_ingreso:', currentDate);

  // UC1: Map and validate required form fields
  Object.entries(FIELD_MAPPINGS).forEach(([clientField, serverField]) => {
    const value = rawFormData.get(clientField);
    if (value?.trim()) { // Only append if value exists and isn't empty
      formData.append(serverField, value);
      console.log(`${clientField} -> ${serverField}: ${value}`);
    }
    
    // Keep validation warning for required fields
    if (REQUIRED_FIELDS[serverField] && !value?.trim()) {
      console.warn(`Required field ${serverField} is empty`);
    }
  });

  // UC2: Format and validate birth date with proper formatting
  const birthYear = rawFormData.get("birth-year");
  const birthMonth = rawFormData.get("birth-month");
  const birthDay = rawFormData.get("birth-day");
  
  if (birthYear && birthMonth && birthDay) {
    const birthDate = formatDateString(birthYear, birthMonth, birthDay);
    formData.append("et_pb_contact_fecha_nacimiento_0", birthDate);
    console.log('Birth date:', birthDate);
    
    if (!birthDate.match(/^\d{4}-\d{2}-\d{2}$/)) {
      console.warn('Invalid birth date format:', birthDate);
    }
  }

  // UC3: Validate research areas selection
  const researchResult = handleResearchAreas(formData);
  if (!researchResult.isValid) {
    console.warn('Research area validation failed:', researchResult.error);
  } else {
    console.log('Research areas processed successfully');
  }

  // UC4: Verify CAPTCHA token
  const captchaInput = document.getElementById("captcha-input");
  if (captchaInput?.value) {
    formData.append("captcha", captchaInput.value);
    console.log("CAPTCHA value added:", captchaInput.value);
  } else {
    console.warn("Missing CAPTCHA input value");
  }

  // Debug log final form data
  console.group('Final Form Data');
  for (let [key, value] of formData.entries()) {
    console.log(`${key}: ${value}`);
  }
  console.groupEnd();
  
  return formData;
}

function validateForm(formData) {
  const errors = [];

  // Research area validation
  const researchAreaResult = handleResearchAreas(formData);
  if (!researchAreaResult.isValid) {
    errors.push(researchAreaResult.error);
  }

  // Required fields validation
  Object.entries(FIELD_MAPPINGS).forEach(([clientField, serverField]) => {
    if (REQUIRED_FIELDS[serverField]) {
      const value = formData.get(clientField);
      if (!value?.trim()) {
        errors.push({
          field: serverField,
          label: REQUIRED_FIELDS[serverField],
          message: `Por favor, complete el campo: ${REQUIRED_FIELDS[serverField]}`,
        });
      }
    }
  });

  // Date validation
  const birthYear = formData.get("birth-year");
  const birthMonth = formData.get("birth-month");
  const birthDay = formData.get("birth-day");

  if (!birthYear || !birthMonth || !birthDay) {
    errors.push({
      field: "birth-date",
      label: "Fecha de Nacimiento",
      message: "Por favor, complete la fecha de nacimiento",
    });
  }

  return errors;
}

// UI functions
const toggleElement = (element, show) =>
  element?.classList.toggle("hidden", !show);

function showMessage(type, message) {
  const messageDiv = document.getElementById(`form-${type}-message`);
  const textElement = document.getElementById(`${type}-text`);

  if (messageDiv && textElement) {
    textElement.textContent = message;
    toggleElement(messageDiv, true);
    messageDiv.scrollIntoView({ behavior: "smooth", block: "start" });
  }
}

const showErrorMessage = (message) => showMessage("error", message);
const showSuccessMessage = (message) => showMessage("success", message);
const hideMessage = (type) =>
  toggleElement(document.getElementById(`form-${type}-message`), false);
const hideErrorMessage = () => hideMessage("error");
const hideSuccessMessage = () => hideMessage("success");

function displayErrorMessages(errors) {
  hideErrorMessages(); // Clear previous errors first

  errors.forEach(({ field, message }) => {
    // Map the server-side field name to client-side if needed
    const clientFieldId =
      Object.entries(FIELD_MAPPINGS).find(
        ([_, serverField]) => serverField === field
      )?.[0] || field;

    const errorElement = document.getElementById(`${clientFieldId}-error`);
    const inputElement = document.getElementById(clientFieldId);

    if (errorElement) {
      errorElement.textContent = message;
      toggleElement(errorElement, true);
    }

    if (inputElement) {
      inputElement.classList.add("error");
      // Scroll to the first error field
      if (!document.querySelector(".error")) {
        inputElement.scrollIntoView({ behavior: "smooth", block: "center" });
      }
    }
  });

  if (errors.length) {
    const errorSummary = document.createElement("ul");
    errors.forEach(({ message }) => {
      const li = document.createElement("li");
      li.textContent = message;
      errorSummary.appendChild(li);
    });

    showErrorMessage(`Se encontraron ${errors.length} errores:`);
    const errorMessageElement = document.getElementById("error-text");
    if (errorMessageElement) {
      errorMessageElement.appendChild(errorSummary);
    }
  }
}

function hideErrorMessages() {
  document.querySelectorAll('[id$="-error"]').forEach((el) => {
    el.classList.add("hidden");
    el.textContent = "";
  });

  document
    .querySelectorAll(".error")
    .forEach((el) => el.classList.remove("error"));
}

function setCaptchaCookie() {
  const captchaString = generateCaptchaString();
  document.cookie = `PHPSESSID=simulated_session_id; path=/; SameSite=Lax`;
  document.cookie = `captcha_text=${captchaString}; path=/; SameSite=Lax`;
}

// Simplified CAPTCHA loading function
async function loadCaptcha() {
  const captchaImg = document.getElementById("captcha-image");
  const captchaInput = document.getElementById("captcha-input");

  if (!captchaImg || !captchaInput) {
    console.error("CAPTCHA elements not found");
    return;
  }

  try {
    // Load the CAPTCHA
    const timestamp = new Date().getTime();
    captchaImg.src = "/solicitud_registro_usuario/lib/captcha.php?" + timestamp;
    captchaInput.value = "";

    console.log("CAPTCHA loading attempted");
  } catch (error) {
    console.error("Error loading CAPTCHA:", error);
    showMessage(
      "error",
      "Error al cargar el CAPTCHA. Por favor, recargue la página."
    );
  }
}

// Simplified form submission
async function handleFormSubmission(form, formData) {
  console.group("Form Submission");

  const captchaInput = document.getElementById("captcha-input");
  if (!captchaInput?.value) {
    showMessage("error", "Por favor, complete el CAPTCHA");
    console.error("CAPTCHA input empty");
    return;
  }

  // Add CAPTCHA value to form data
  formData.append("captcha", captchaInput.value);

  const loadingIndicator = document.getElementById("loading-indicator");
  const submitButton = form.querySelector('button[type="submit"]');

  toggleElement(loadingIndicator, true);
  toggleElement(submitButton, false);

  try {
    const response = await fetch(form.action, {
      method: "POST",
      body: formData,
      credentials: "include",
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
    });

    const responseText = await response.text();
    console.log("Raw server response:", responseText);

    let result;
    try {
      result = JSON.parse(responseText);
    } catch (error) {
      console.error("JSON parsing error:", error);
      throw new Error(
        `Server returned invalid JSON: ${responseText.substring(0, 100)}...`
      );
    }

    console.log("Server Response:", result);

    if (!result.success) {
      if (result.message?.includes("CAPTCHA")) {
        showMessage("error", result.message);
        loadCaptcha(); // Reload CAPTCHA on failure
      } else {
        displayErrorMessages(
          result.errors || [{ message: result.message || "Unknown error" }]
        );
      }
      throw new Error(
        result.message ||
          "Error desconocido en el procesamiento del formulario."
      );
    }

    // Handle debug data if available
    if (result.debug) {
      console.group('Form Submission Debug Data');
      if (result.debug.formData) {
        console.log('Form Data:', result.debug.formData);
      }
      if (result.debug.processedData) {
        console.log('Processed Data:', result.debug.processedData);
      }
      if (result.debug.timestamp) {
        console.log('Timestamp:', result.debug.timestamp);
      }
      console.groupEnd();

      // Handle redirect if present in debug data
      if (result.debug.redirect) {
        window.location.href = result.debug.redirect;
      }
    }

    // Handle success
    console.log("Submission Successful:", result.message);
    showSuccessMessage(result.message);
    form.reset();
    loadCaptcha(); // Reload CAPTCHA on success

    if (result.debug?.redirect) {
      window.location.href = result.debug.redirect;
    } else {
      document.getElementById("form-success-message")?.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
    }
  } catch (error) {
    console.error("Form submission error:", error);
    showMessage(
      "error",
      error.message ||
        "Error al enviar el formulario. Por favor, intente nuevamente."
    );
  } finally {
    toggleElement(loadingIndicator, false);
    toggleElement(submitButton, true);
    console.groupEnd();
  }
}

function setInstitutionCookies() {
  const institution = document.getElementById("institution-name-search").value;
  const faculty = document.getElementById("campus-faculty").value;
  document.cookie = `Organizacion=${encodeURIComponent(
    institution
  )}; path=/; SameSite=Lax`;
  document.cookie = `Facultad=${encodeURIComponent(
    faculty
  )}; path=/; SameSite=Lax`;
}

function validateField(fieldId, errorMessage) {
  const field = document.getElementById(fieldId);
  const errorElement = document.getElementById(`${fieldId}-error`);

  if (!field) return true;

  let isValid = field.checkValidity();

  if (fieldId === "id-number") {
    isValid = /^\d{6,15}$/.test(field.value.trim());
    errorMessage = isValid
      ? ""
      : "El número de cédula debe tener entre 6 y 15 dígitos";
  }

  if (!isValid && errorElement) {
    errorElement.textContent = errorMessage;
    toggleElement(errorElement, true);
    field.classList.add("error");
  } else if (errorElement) {
    toggleElement(errorElement, false);
    field.classList.remove("error");
  }

  return isValid;
}

function setupAutosave(form) {
  let autoSaveTimer;
  form.addEventListener("change", (event) => {
    if (event.target.matches("input, select")) {
      clearTimeout(autoSaveTimer);
      autoSaveTimer = setTimeout(() => {
        try {
          localStorage.setItem(
            "formData",
            JSON.stringify(Object.fromEntries(new FormData(form)))
          );
        } catch (error) {
          console.error("Error saving form data:", error);
        }
      }, 1000);
    }
  });
}

function loadSavedFormData(form) {
  try {
    const savedData = JSON.parse(localStorage.getItem("formData"));
    if (savedData) {
      Object.entries(savedData).forEach(([key, value]) => {
        const field = form.elements[key];
        if (field) field.value = value;
      });
    }
  } catch (error) {
    console.error("Error loading saved form data:", error);
  }
}

function initializeForm(form) {
  // Form submission handler
  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    hideMessage("error");
    hideMessage("success");

    const formData = new FormData(form);
    const errors = validateForm(formData);

    if (errors.length > 0) {
      displayErrorMessages(errors);
      return;
    }

    try {
      const preparedData = prepareFormData(formData);
      await handleFormSubmission(form, preparedData);
    } catch (error) {
      console.error("Form submission error:", error);
      showMessage(
        "error",
        "Error al enviar el formulario. Por favor, intente nuevamente."
      );
    }
  });

  // Field validation and institution field handlers
  form.addEventListener("blur", (event) => {
    const fieldId = event.target.id;
    if (REQUIRED_FIELDS.hasOwnProperty(fieldId)) {
      validateField(fieldId);
    }
  });

  form.addEventListener("change", (event) => {
    const fieldId = event.target.id;
    if (["institution-name-search", "campus-faculty"].includes(fieldId)) {
      setInstitutionCookies();
    }
  });

  // Autosave functionality
  setupAutosave(form);
  loadSavedFormData(form);
}

// Initialize on page load
document.addEventListener("DOMContentLoaded", () => {
  // fetchCaptchaToken(); // TODO: Uncomment this when the server is ready

  const form = document.getElementById("registration-form");
  if (form) {
    initializeForm(form);
    loadCaptcha(); // Load CAPTCHA after form initialization
  } else {
    console.error("Registration form not found");
  }
});
