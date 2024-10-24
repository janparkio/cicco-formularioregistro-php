// Constants and configurations
const RESEARCH_AREAS = {
  "ciencias-naturales": { id: "0", value: "Ciencias Naturales" },
  "ingenieria-tecnologia": { id: "1", value: "Ingeniería y Tecnología" },
  "ciencias-medicas-salud": { id: "2", value: "Ciencias Médicas y de la Salud" },
  "ciencias-agricolas-veterinarias": { id: "3", value: "Ciencias Agrícolas y Veterinarias" },
  "ciencias-sociales": { id: "4", value: "Ciencias Sociales" },
  "humanidades-artes": { id: "5", value: "Humanidades y Artes" },
  "otras-areas": { id: "0", value: "Otras Áreas" }
};

const REQUIRED_FIELDS = {
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
  et_pb_contact_rol_0: "Rol Institucional"
};

const FIELD_MAPPINGS = {
  "first-name": "et_pb_contact_nombres_0",
  "last-name": "et_pb_contact_apellidos_0", 
  "nationality": "et_pb_contact_nacionalidad_0",
  "id-number": "et_pb_contact_dni_0",
  "gender": "et_pb_contact_genero_0",
  "mobile-phone": "et_pb_contact_phone_0",
  "institutional-email": "et_pb_contact_email_0",
  "department": "et_pb_contact_departamento_0",
  "city": "et_pb_contact_ciudad_0",
  "institution-name": "organizacion",
  "campus-faculty": "organizacion_facultad",
  "specific-unit-career": "organizacion_facultad_carrera",
  "institutional-role": "et_pb_contact_rol_0",
  "pronii-category": "et_pb_contact_categoria_pronii_0",
  "orcid-id": "et_pb_contact_orcid_0",
  "scopus-id": "et_pb_contact_scopus_0",
  "wos-id": "et_pb_contact_wos_0"
};

// Utility functions
const getCurrentDate = () => new Date().toISOString().split("T")[0];
const validateEmail = email => /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(String(email).toLowerCase());
const generateCaptchaString = (length = 6) => Array(length).fill(0).map(() => "ABCDEFGHJKLMNPQRSTUVWXYZ"[Math.floor(Math.random() * 23)]).join('');

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

// Form handling functions
function handleResearchAreas(formData) {
  const selectedAreas = Object.keys(RESEARCH_AREAS).filter(id => document.getElementById(id)?.checked);

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
    formData.append(`et_pb_contact_area_investigacion_0_23_${id}`, value);
  });

  formData.append('has_research_areas', 'true');
  return { isValid: true };
}

function prepareFormData(rawFormData) {
  const formData = new FormData();

  // Map form fields
  Object.entries(FIELD_MAPPINGS).forEach(([rawField, serverField]) => {
    formData.append(serverField, rawFormData.get(rawField));
  });

  // Handle date fields
  const birthDate = `${rawFormData.get("birth-year")}-${rawFormData.get("birth-month")}-${rawFormData.get("birth-day")}`;
  formData.append("et_pb_contact_fecha_nacimiento_0", birthDate);

  // Handle research areas and captcha
  handleResearchAreas(formData);
  const captchaToken = document.getElementById("captcha_token").value;
  formData.append("captcha", captchaToken);
  formData.append("captcha_challenge", captchaToken);

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
          message: `Por favor, complete el campo: ${REQUIRED_FIELDS[serverField]}`
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
      message: "Por favor, complete la fecha de nacimiento"
    });
  }
  
  return errors;
}

// UI functions
const toggleElement = (element, show) => element?.classList.toggle("hidden", !show);

function showMessage(type, message) {
  const messageDiv = document.getElementById(`form-${type}-message`);
  const textElement = document.getElementById(`${type}-text`);
  
  if (messageDiv && textElement) {
    textElement.textContent = message;
    toggleElement(messageDiv, true);
    messageDiv.scrollIntoView({ behavior: "smooth", block: "start" });
  }
}

const showErrorMessage = message => showMessage('error', message);
const showSuccessMessage = message => showMessage('success', message);
const hideMessage = type => toggleElement(document.getElementById(`form-${type}-message`), false);
const hideErrorMessage = () => hideMessage('error');
const hideSuccessMessage = () => hideMessage('success');

function displayErrorMessages(errors) {
  hideErrorMessages(); // Clear previous errors first
  
  errors.forEach(({field, message}) => {
    // Map the server-side field name to client-side if needed
    const clientFieldId = Object.entries(FIELD_MAPPINGS)
      .find(([_, serverField]) => serverField === field)?.[0] || field;
    
    const errorElement = document.getElementById(`${clientFieldId}-error`);
    const inputElement = document.getElementById(clientFieldId);
    
    if (errorElement) {
      errorElement.textContent = message;
      toggleElement(errorElement, true);
    }
    
    if (inputElement) {
      inputElement.classList.add('error');
      // Scroll to the first error field
      if (!document.querySelector('.error')) {
        inputElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }
  });

  if (errors.length) {
    const errorSummary = document.createElement('ul');
    errors.forEach(({message}) => {
      const li = document.createElement('li');
      li.textContent = message;
      errorSummary.appendChild(li);
    });
    
    showErrorMessage(`Se encontraron ${errors.length} errores:`);
    const errorMessageElement = document.getElementById('error-text');
    if (errorMessageElement) {
      errorMessageElement.appendChild(errorSummary);
    }
  }
}

function hideErrorMessages() {
  document.querySelectorAll('[id$="-error"]').forEach(el => {
    el.classList.add('hidden');
    el.textContent = '';
  });
  
  document.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
}

function setCaptchaCookie() {
  const captchaString = generateCaptchaString();
  document.cookie = `PHPSESSID=simulated_session_id; path=/; SameSite=Lax`;
  document.cookie = `captcha_text=${captchaString}; path=/; SameSite=Lax`;
}

async function fetchCaptchaToken() {
  const statusElement = document.getElementById("captcha-status");
  const errorElement = document.getElementById("captcha-error-message");

  toggleElement(statusElement, true);
  toggleElement(errorElement, false);

  try {
    const response = await fetch(
      "https://cicco.conacyt.gov.py/solicitud_registro_usuario/components/captcha.php",
      {
        method: "GET",
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        credentials: "include",
      }
    );
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const data = await response.json();
    
    if (data.status === "success" && data.token) {
      document.getElementById("captcha_token").value = data.token;
      toggleElement(statusElement, false);
    } else {
      throw new Error(data.message || "Invalid response");
    }
  } catch (error) {
    console.error("Captcha error:", error);
    toggleElement(statusElement, false);
    toggleElement(errorElement, true);
    errorElement.textContent = "Error al cargar el token de seguridad. Por favor, recargue la página.";
  }
}

async function handleFormSubmission(form, formData) {
  const captchaToken = document.getElementById("captcha_token").value;
  if (!captchaToken) {
    showErrorMessage("Error de seguridad: Token no encontrado. Por favor, recargue la página.");
    return;
  }

  const loadingIndicator = document.getElementById("loading-indicator");
  const submitButton = form.querySelector('button[type="submit"]');
  toggleElement(loadingIndicator, true);
  toggleElement(submitButton, false);

  try {
    const response = await fetch(
      "https://cicco.conacyt.gov.py/solicitud_registro_usuario/lib/process_registration.php",
      {
        method: "POST",
        body: formData,
        credentials: "include",
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
      }
    );

    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

    const result = await response.json();

    if (result.success) {
      showSuccessMessage(result.message);
      form.reset();
      document.getElementById("form-success-message")?.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
    } else {
      displayErrorMessages(result.errors);
      showErrorMessage(result.message);
    }
  } catch (error) {
    console.error("Error en el envío:", error);
    showErrorMessage("Hubo un error al enviar el formulario. Por favor, inténtelo de nuevo.");
  } finally {
    toggleElement(loadingIndicator, false);
    toggleElement(submitButton, true);
  }
}

function setInstitutionCookies() {
  const institution = document.getElementById("institution-name-search").value;
  const faculty = document.getElementById("campus-faculty").value;
  document.cookie = `Organizacion=${encodeURIComponent(institution)}; path=/; SameSite=Lax`;
  document.cookie = `Facultad=${encodeURIComponent(faculty)}; path=/; SameSite=Lax`;
}

function validateField(fieldId, errorMessage) {
  const field = document.getElementById(fieldId);
  const errorElement = document.getElementById(`${fieldId}-error`);
  
  if (!field) return true;
  
  let isValid = field.checkValidity();
  
  if (fieldId === 'id-number') {
    isValid = /^\d{6,10}$/.test(field.value.trim());
    errorMessage = isValid ? '' : 'El número de cédula debe tener entre 6 y 10 dígitos';
  }

  if (!isValid && errorElement) {
    errorElement.textContent = errorMessage;
    toggleElement(errorElement, true);
    field.classList.add('error');
  } else if (errorElement) {
    toggleElement(errorElement, false);
    field.classList.remove('error');
  }

  return isValid;
}

function setupAutosave(form) {
  let autoSaveTimer;
  form.addEventListener('change', (event) => {
    if (event.target.matches('input, select')) {
      clearTimeout(autoSaveTimer);
      autoSaveTimer = setTimeout(() => {
        try {
          localStorage.setItem('formData', JSON.stringify(Object.fromEntries(new FormData(form))));
        } catch (error) {
          console.error('Error saving form data:', error);
        }
      }, 1000);
    }
  });
}

function loadSavedFormData(form) {
  try {
    const savedData = JSON.parse(localStorage.getItem('formData'));
    if (savedData) {
      Object.entries(savedData).forEach(([key, value]) => {
        const field = form.elements[key];
        if (field) field.value = value;
      });
    }
  } catch (error) {
    console.error('Error loading saved form data:', error);
  }
}

function initializeForm(form) {
  // Form submission handler
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    hideMessage('error');
    hideMessage('success');
    
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
      console.error('Form submission error:', error);
      showMessage('error', 'Error al enviar el formulario. Por favor, intente nuevamente.');
    }
  });

  // Field validation and institution field handlers
  form.addEventListener('blur', (event) => {
    const fieldId = event.target.id;
    if (REQUIRED_FIELDS.hasOwnProperty(fieldId)) {
      validateField(fieldId);
    }
  });

  form.addEventListener('change', (event) => {
    const fieldId = event.target.id;
    if (['institution-name-search', 'campus-faculty'].includes(fieldId)) {
      setInstitutionCookies();
    }
  });

  // Autosave functionality
  setupAutosave(form);
  loadSavedFormData(form);
}

// Initialize on page load
document.addEventListener("DOMContentLoaded", () => {
  fetchCaptchaToken();
  const form = document.getElementById("registration-form");
  if (form) {
    initializeForm(form);
  } else {
    console.error("Registration form not found");
  }
});
