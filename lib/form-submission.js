// Constants and configurations
const RESEARCH_AREAS = {
  "ciencias_naturales": {
    id: "0",
    value: "Ciencias Naturales",
    serverValue: "Ciencias Naturales"
  },
  "ingenieria_tecnologia": {
    id: "1",
    value: "Ingeniería y Tecnología",
    serverValue: "Ingenieria y Tecnologia"
  },
  "ciencias_medicas_salud": {
    id: "2",
    value: "Ciencias Médicas y de la Salud",
    serverValue: "Ciencias Medicas y de la Salud"
  },
  "ciencias_agricolas_veterinarias": {
    id: "3",
    value: "Ciencias Agrícolas y Veterinarias",
    serverValue: "Ciencias Agricolas y Veterinarias"
  },
  "ciencias_sociales": {
    id: "4",
    value: "Ciencias Sociales",
    serverValue: "Ciencias Sociales"
  },
  "humanidades_artes": {
    id: "5",
    value: "Humanidades y Artes",
    serverValue: "Humanidades y Artes"
  }
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
const getCurrentDate = () => {
    const now = new Date();
    return now.getFullYear() + '-' + 
           String(now.getMonth() + 1).padStart(2, '0') + '-' + 
           String(now.getDate()).padStart(2, '0') + ' ' +
           String(now.getHours()).padStart(2, '0') + ':' +
           String(now.getMinutes()).padStart(2, '0') + ':' + 
           String(now.getSeconds()).padStart(2, '0');
};

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
 * Normalizes Spanish text by removing accents and special characters
 * @param {string} text - Text to normalize
 * @returns {string} Normalized text
 */
function normalizeSpanishText(text) {
  return text.normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '') // Remove accents
    .replace(/[ñÑ]/g, 'n')           // Replace ñ with n
    .replace(/[üÜ]/g, 'u');          // Replace ü with u
}

/**
 * Sanitizes input values with configurable cleaning options
 * @param {string} value - The value to sanitize
 * @param {Object} options - Optional configuration for sanitization
 * @returns {string} Sanitized value
 */
function sanitizeValue(value, options = {}) {
  if (value === null || value === undefined) return '';
  
  const defaultOptions = {
    removeQuotes: true,
    removeBackslashes: true,
    trimStart: true,
    trimEnd: true,
    cleanCommasAtEnds: true,
    normalizeSpanish: false, // New option for Spanish text normalization
    ...options
  };

  let cleaned = String(value);

  if (defaultOptions.removeQuotes) {
    cleaned = cleaned.replace(/['"]/g, '');
  }
  
  if (defaultOptions.removeBackslashes) {
    cleaned = cleaned.replace(/\\/g, '');
  }

  if (defaultOptions.normalizeSpanish) {
    cleaned = normalizeSpanishText(cleaned);
  }

  if (defaultOptions.cleanCommasAtEnds) {
    cleaned = cleaned.replace(/^,+|,+$/g, ''); // Remove commas only at start/end
  }
  
  if (defaultOptions.trimStart) {
    cleaned = cleaned.trimStart();
  }
  
  if (defaultOptions.trimEnd) {
    cleaned = cleaned.trimEnd(); 
  }

  return cleaned;
}

/**
 * Handles the processing and validation of research areas in the form
 * @param {FormData} formData - The form data object to append research area values to
 * @returns {Object} Object containing validation result with isValid boolean and error details if invalid
 */
function handleResearchAreas(formData) {
  const sanitizeAreaValue = (value) => {
    return value
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '') // Remove accents
      .replace(/[ñÑ]/g, 'n')           // Replace ñ with n
      .replace(/[^a-zA-Z0-9\s]/g, '')  // Remove any other special characters
      .replace(/\s+/g, ' ')            // Normalize spaces
      .trim();
  };

  const selectedAreas = Object.keys(RESEARCH_AREAS).filter(id => 
    document.getElementById(id)?.checked
  );

  console.log('Selected areas IDs:', selectedAreas);

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

  // Clear existing research area fields
  Object.values(RESEARCH_AREAS).forEach(area => {
    const serverField = `et_pb_contact_area_investigacion_0_23_${area.id}`;
    formData.delete(serverField);
  });
  formData.delete('et_pb_contact_area_investigacion_0');

  // Set selected values with sanitization
  selectedAreas.forEach(areaKey => {
    const area = RESEARCH_AREAS[areaKey];
    const serverField = `et_pb_contact_area_investigacion_0_23_${area.id}`;
    formData.append(serverField, sanitizeAreaValue(area.serverValue));
    
    console.log('Setting research area:', {
      field: serverField,
      value: sanitizeAreaValue(area.serverValue),
      original: area.value
    });
  });

  // Create sanitized joined value for compatibility
  const joinedValue = selectedAreas
    .map(key => sanitizeAreaValue(RESEARCH_AREAS[key].serverValue))
    .join(',');
  formData.append('et_pb_contact_area_investigacion_0', joinedValue);

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

  // Add fecha_ingreso as first field with current date
  const currentDate = getCurrentDate();
  formData.append("et_pb_contact_fecha_ingreso_0", currentDate);
  console.log('Added fecha_ingreso:', currentDate);

  // UC1: Map and validate required form fields
  Object.entries(FIELD_MAPPINGS).forEach(([clientField, serverField]) => {
    // Skip fecha_ingreso since we already added it
    if (serverField === 'et_pb_contact_fecha_ingreso_0') {
      return;
    }
    
    const value = rawFormData.get(clientField);
    if (value?.trim()) {
      let sanitizedValue = value;

      // Manejo especial para la institución
      if (serverField === 'organizacion') {
        // const orgCookie = document.cookie
        //   .split('; ')
        //   .find(row => row.startsWith('Organizacion='))
        //   ?.split('=')[1];
          
        // if (orgCookie) {
        //   // Primero obtenemos el valor exacto para validación
        //   let cookieValue = decodeURIComponent(orgCookie);
          
          // Luego sanitizamos para el envío
          sanitizedValue = value
            .replace(/['"]/g, '')         // Remover comillas
            .replace(/\\/g, '')           // Remover backslashes
            .replace(/\s+/g, ' ')         // Normalizar espacios
            .trim();                      // Limpiar espacios
          
        //   console.log('Cookie original:', orgCookie);
        //   console.log('Cookie decodificada:', cookieValue);
        //   console.log('Valor sanitizado final:', sanitizedValue);
        // }
      } else {
        // Para otros campos, aplicar sanitización normal
        sanitizedValue = sanitizeValue(value, {
          trimStart: true,
          trimEnd: true,
          cleanCommasAtEnds: true,
          normalizeSpanish: ['et_pb_contact_nombres_0', 'et_pb_contact_apellidos_0'].includes(serverField)
        });
      }

      formData.append(serverField, sanitizedValue);
      console.log(`${clientField} -> ${serverField}: ${sanitizedValue}`);
    }
    
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

  // Add current date as fecha_ingreso
  const currentDate = new Date().toISOString().split('T')[0];
  formData.append('et_pb_contact_fecha_ingreso_0', currentDate);

  // Research area validation
  const researchAreaResult = handleResearchAreas(formData);
  if (!researchAreaResult.isValid) {
    errors.push(researchAreaResult.error);
  }

  // Required fields validation
  Object.entries(FIELD_MAPPINGS).forEach(([clientField, serverField]) => {
    // Skip fecha_ingreso validation since we add it manually
    if (serverField === 'et_pb_contact_fecha_ingreso_0') {
      return;
    }

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

// Function to display field-specific error messages
function showFieldError(field, message) {
  // Remove any existing error message
  const existingError = field.parentElement.querySelector('.field-error');
  if (existingError) {
      existingError.remove();
  }
  
  // Add error message
  const errorDiv = document.createElement('div');
  errorDiv.className = 'field-error';
  errorDiv.textContent = message;
  field.parentElement.appendChild(errorDiv);
  
  // Add error styling
  field.classList.add('error');
  
  // Remove error styling when field is changed
  field.addEventListener('change', function() {
      field.classList.remove('error');
      const errorMsg = field.parentElement.querySelector('.field-error');
      if (errorMsg) {
          errorMsg.remove();
      }
  });
}


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
    // Log complete form data before submission
    const formDataLog = {};
    for (let [key, value] of formData.entries()) {
      formDataLog[key] = value;
    }
    console.log("Submitting form data:", formDataLog);

    const response = await fetch(form.action, {
      method: "POST", 
      body: formData,
      credentials: "include",
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
    });

    // Add status code check
    if (!response.ok) {
      const errorText = await response.text();
      console.error("Server Error:", {
        status: response.status,
        statusText: response.statusText,
        body: errorText
      });
      
      throw new Error(
        `Server error (${response.status}): ${errorText || 'No error details available'}`
      );
    }

    const responseText = await response.text();
    console.log("Raw server response:", responseText);

    // Only try parsing if we have content
    if (!responseText.trim()) {
      throw new Error("Server returned empty response");
    }

    let result;
    try {
      result = JSON.parse(responseText);
    } catch (error) {
      console.error("JSON parsing error:", error);
      console.error("Raw response:", responseText);
      throw new Error(
        `Invalid JSON response: ${responseText.substring(0, 100)}...`
      );
    }

    console.log("Server Response:", result);

    // Log attempt data
    const attemptData = {
      timestamp: new Date().toISOString().replace('T', ' ').split('.')[0],
      ip: "",
      success: result.success,
      request_data: {
        fecha_ingreso: formDataLog.et_pb_contact_fecha_ingreso_0,
        nombres: formDataLog.et_pb_contact_nombres_0,
        apellidos: formDataLog.et_pb_contact_apellidos_0,
        dni: formDataLog.et_pb_contact_dni_0,
        nacionalidad: formDataLog.et_pb_contact_nacionalidad_0,
        genero: formDataLog.et_pb_contact_genero_0,
        fecha_nacimiento: formDataLog.et_pb_contact_fecha_nacimiento_0,
        telefono: formDataLog.et_pb_contact_phone_0,
        email: formDataLog.et_pb_contact_email_0,
        institucion: formDataLog.organizacion,
        facultad: formDataLog.organizacion_facultad,
        carrera: formDataLog.organizacion_facultad_carrera,
        rol: formDataLog.et_pb_contact_rol_0,
        categoria_pronii: formDataLog.et_pb_contact_pronii_categoria_0,
        contact_orcid: formDataLog.et_pb_contact_orcid_0,
        contact_scopus: formDataLog.et_pb_contact_scopus_0,
        contact_wos: formDataLog.et_pb_contact_wos_0,
        departamento: formDataLog.et_pb_contact_departamento_0,
        ciudad: formDataLog.et_pb_contact_ciudad_0,
        ciencias_naturales: formDataLog.et_pb_contact_area_investigacion_0_23_0 || "",
        ingenieria_tecnologia: formDataLog.et_pb_contact_area_investigacion_0_23_1 || "",
        ciencias_medicas_salud: formDataLog.et_pb_contact_area_investigacion_0_23_2 || "",
        ciencias_agricolas_veterinarias: formDataLog.et_pb_contact_area_investigacion_0_23_3 || "",
        ciencias_sociales: formDataLog.et_pb_contact_area_investigacion_0_23_4 || "",
        humanidades_artes: formDataLog.et_pb_contact_area_investigacion_0_23_5 || ""
      },
      response: {
        success: result.success,
        message: result.message,
        redirect: result.debug?.redirect || null
      },
      captcha_used: true,
      user_agent: navigator.userAgent
    };

    console.log("Attempt data logged:", attemptData);

    if (!result.success) {
      console.error('Form validation failed:', result);
      
      // Display specific error messages
      if (result.errors && result.errors.length > 0) {
        result.errors.forEach(error => {
          if (error.includes('Facultad')) {
            // Handle faculty-specific errors
            const facultyField = document.querySelector('[name="organizacion_facultad"]');
            if (facultyField) {
              facultyField.classList.add('error');
              showFieldError(facultyField, error);
            }
          }
          // Add other specific error handlers as needed
        });
        
        showMessage('error', result.message);
      } else {
        showMessage('error', result.message || 'Error desconocido en el procesamiento del formulario.');
      }
      
      // Log the complete error information
      console.group('Form Submission Error Details');
      console.log('Error Response:', result);
      console.log('Form Data:', formData);
      console.groupEnd();
      
      throw new Error(result.message || 'Error en el procesamiento del formulario.');
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
    
    // Enhanced error messaging
    let errorMessage = "Error al enviar el formulario. ";
    if (error.message.includes("CAPTCHA")) {
      errorMessage += "Por favor, verifique el código CAPTCHA e intente nuevamente.";
      loadCaptcha(); // Reload CAPTCHA on failure
    } else if (error.message.includes("500")) {
      errorMessage += "Error interno del servidor. Por favor, intente más tarde.";
    } else {
      errorMessage += "Por favor, intente nuevamente.";
    }
    
    showMessage("error", errorMessage);
  } finally {
    toggleElement(loadingIndicator, false);
    toggleElement(submitButton, true);
    console.groupEnd();
  }
}

// function setInstitutionCookies() {
//   const institution = document.getElementById("institution-name-search").value;
//   const faculty = document.getElementById("campus-faculty").value;
//   document.cookie = `Organizacion=${encodeURIComponent(
//     institution
//   )}; path=/; SameSite=Lax`;
//   document.cookie = `Facultad=${encodeURIComponent(
//     faculty
//   )}; path=/; SameSite=Lax`;
// }

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

  // Institution field change handler - commented out since cookies are no longer used
  // form.addEventListener("change", (event) => {
  //   const fieldId = event.target.id;
  //   if (["institution-name-search", "campus-faculty"].includes(fieldId)) {
  //     setInstitutionCookies();
  //   }
  // });

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

