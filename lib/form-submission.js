// Constants at the top of the file
const RESEARCH_AREAS = {
  "ciencias-naturales": {
    id: "0", 
    value: "Ciencias Naturales"
  },
  "ingenieria-tecnologia": {
    id: "1",
    value: "Ingeniería y Tecnología"
  },
  "ciencias-medicas-salud": {
    id: "2", 
    value: "Ciencias Médicas y de la Salud"
  },
  "ciencias-agricolas-veterinarias": {
    id: "3",
    value: "Ciencias Agrícolas y Veterinarias"
  },
  "ciencias-sociales": {
    id: "4",
    value: "Ciencias Sociales"
  },
  "humanidades-artes": {
    id: "5",
    value: "Humanidades y Artes"
  },
  "otras-areas": {
    id: "0", // Fallback to Ciencias Naturales
    value: "Otras Áreas"
  }
};

function handleResearchAreas(formData) {
  const selectedAreas = Object.keys(RESEARCH_AREAS).filter(id => 
    document.getElementById(id)?.checked
  );

  if (selectedAreas.length === 0) {
    return {
      isValid: false,
      error: {
        field: 'research-area',
        label: 'Área de Investigación',
        message: 'Por favor, seleccione al menos un área de investigación'
      }
    };
  }

  // Add selected areas to form data with correct field names
  selectedAreas.forEach(areaKey => {
    const area = RESEARCH_AREAS[areaKey];
    formData.append(
      `et_pb_contact_area_investigacion_0_23_${area.id}`,
      area.value
    );
  });

  formData.append('has_research_areas', 'true');
  return { isValid: true };
}

async function loadJsonData(url) {
  try {
    const response = await fetch(url);
    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
    return await response.json();
  } catch (error) {
    console.warn(`Warning: Could not load ${url}. Using fallback data.`);
    return {}; // Return empty object as fallback
  }
}

function getCurrentDate() {
  const now = new Date();
  return now.toISOString().split("T")[0];
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
      Math.floor(Math.random() * permittedChars.length)
    );
  }
  return result;
}

function prepareFormData(rawFormData) {
  const formData = new FormData();

  // Map form fields to expected server fields
  formData.append("et_pb_contact_nombres_0", rawFormData.get("first-name"));
  formData.append("et_pb_contact_apellidos_0", rawFormData.get("last-name"));
  formData.append(
    "et_pb_contact_nacionalidad_0",
    rawFormData.get("nationality")
  );
  formData.append("et_pb_contact_dni_0", rawFormData.get("id-number"));
  formData.append("et_pb_contact_genero_0", rawFormData.get("gender"));
  formData.append("et_pb_contact_phone_0", rawFormData.get("mobile-phone"));
  formData.append(
    "et_pb_contact_email_0",
    rawFormData.get("institutional-email")
  );
  formData.append(
    "et_pb_contact_departamento_0",
    rawFormData.get("department")
  );
  formData.append("et_pb_contact_ciudad_0", rawFormData.get("city"));
  formData.append("organizacion", rawFormData.get("institution-name"));
  formData.append("organizacion_facultad", rawFormData.get("campus-faculty"));
  formData.append(
    "organizacion_facultad_carrera",
    rawFormData.get("specific-unit-career")
  );
  formData.append("et_pb_contact_rol_0", rawFormData.get("institutional-role"));
  formData.append(
    "et_pb_contact_categoria_pronii_0",
    rawFormData.get("pronii-category")
  );
  formData.append("et_pb_contact_orcid_0", rawFormData.get("orcid-id"));
  formData.append("et_pb_contact_scopus_0", rawFormData.get("scopus-id"));
  formData.append("et_pb_contact_wos_0", rawFormData.get("wos-id"));

  // Handle date fields
  const birthDate = `${rawFormData.get("birth-year")}-${rawFormData.get(
    "birth-month"
  )}-${rawFormData.get("birth-day")}`;
  formData.append("et_pb_contact_fecha_nacimiento_0", birthDate);

  // Handle research areas
  handleResearchAreas(formData);

  // Add captcha field
  formData.append("captcha", document.getElementById("captcha_token").value);
  formData.append(
    "captcha_challenge",
    document.getElementById("captcha_token").value
  );

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
  const requiredFields = {
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

  for (const [fieldName, label] of Object.entries(requiredFields)) {
    const value = formData.get(fieldName);
    if (!value || value.trim() === "") {
      errors.push({
        field: fieldName,
        label: label,
        message: `Por favor, complete el campo: ${label}`
      });
    }
  }

  // Date validation
  const birthDate = `${formData.get("birth-year")}-${formData.get("birth-month")}-${formData.get("birth-day")}`;
  if (!birthDate.match(/^\d{4}-\d{1,2}-\d{1,2}$/)) {
    errors.push({
      field: "birth-date",
      label: "Fecha de Nacimiento",
      message: "Por favor, ingrese una fecha válida"
    });
  }

  return errors;
}

function displayErrorMessages(errors) {
  errors.forEach(error => {
    const errorElement = document.getElementById(`${error.field}-error`);
    if (errorElement) {
      errorElement.textContent = error.message;
      errorElement.classList.remove('hidden');
    }
    
    // Also highlight the invalid field
    const inputElement = document.getElementById(error.field);
    if (inputElement) {
      inputElement.classList.add('error');
    }
  });

  // Show general error message with count
  if (errors.length > 0) {
    showErrorMessage(
      `Se encontraron ${errors.length} errores. Por favor, revise los campos marcados.`
    );
  }
}

function hideErrorMessages() {
  // Remove all error messages and highlighting
  document.querySelectorAll('[id$="-error"]').forEach(el => {
    el.classList.add('hidden');
    el.textContent = '';
  });
  
  document.querySelectorAll('.error').forEach(el => {
    el.classList.remove('error');
  });
}

function setCaptchaCookie() {
  const captchaString = generateCaptchaString();
  document.cookie = `PHPSESSID=simulated_session_id; path=/; SameSite=Lax`;
  document.cookie = `captcha_text=${captchaString}; path=/; SameSite=Lax`;
}

function showErrorMessage(message) {
  const errorDiv = document.getElementById("form-error-message");
  const errorText = document.getElementById("error-text");
  if (errorDiv && errorText) {
    errorText.textContent = message;
    errorDiv.classList.remove("hidden");
    errorDiv.scrollIntoView({ behavior: "smooth", block: "start" });
  } else {
    console.error("Error message elements not found");
  }
}

function hideErrorMessage() {
  const errorDiv = document.getElementById("form-error-message");
  errorDiv.classList.add("hidden");
}

function showSuccessMessage(message) {
  const successDiv = document.getElementById("form-success-message");
  const successText = document.getElementById("success-text");
  if (successDiv && successText) {
    successText.textContent = message;
    successDiv.classList.remove("hidden");
    successDiv.scrollIntoView({ behavior: "smooth", block: "start" });
  } else {
    console.error("Success message elements not found");
  }
}

function hideSuccessMessage() {
  const successDiv = document.getElementById("form-success-message");
  if (successDiv) {
    successDiv.classList.add("hidden");
  }
}

function fetchCaptchaToken() {
  const statusElement = document.getElementById("captcha-status");
  const errorElement = document.getElementById("captcha-error-message");

  statusElement?.classList.remove("hidden");
  errorElement?.classList.add("hidden");

  // Use the absolute path to the captcha endpoint
  fetch(
    "https://cicco.conacyt.gov.py/solicitud_registro_usuario/components/captcha.php",
    {
      method: "GET",
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      credentials: "include", // Changed from 'same-origin' to 'include' for cross-origin requests
    }
  )
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success" && data.token) {
        document.getElementById("captcha_token").value = data.token;
        statusElement?.classList.add("hidden");
        // console.log("Captcha token set successfully");
      } else {
        throw new Error(data.message || "Invalid response");
      }
    })
    .catch((error) => {
      console.error("Captcha error:", error);
      statusElement?.classList.add("hidden");
      errorElement?.classList.remove("hidden");
      errorElement.textContent =
        "Error al cargar el token de seguridad. Por favor, recargue la página.";
    });
}

function toggleLoadingState(isLoading, loadingIndicator, submitButton) {
  if (loadingIndicator && submitButton) {
    loadingIndicator.classList.toggle("hidden", !isLoading);
    submitButton.classList.toggle("hidden", isLoading);
  }
}

async function handleFormSubmission(form, formData) {
  // Get and validate captcha token
  const captchaToken = document.getElementById("captcha_token").value;
  if (!captchaToken) {
    showErrorMessage(
      "Error de seguridad: Token no encontrado. Por favor, recargue la página."
    );
    return;
  }

  // Show loading state
  const loadingIndicator = document.getElementById("loading-indicator");
  const submitButton = form.querySelector('button[type="submit"]');
  toggleLoadingState(true, loadingIndicator, submitButton);

  try {
    const response = await fetch(
      "https://cicco.conacyt.gov.py/solicitud_registro/procesar_ingreso_2023_NEW.php",
      {
        method: "POST",
        body: formData,
        credentials: "include",
        headers: {
          Accept: "text/plain, */*",
          "X-Requested-With": "XMLHttpRequest",
        },
      }
    );

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const text = await response.text();
    console.log("Server response:", text);
    console.warn("Server response is not JSON:", text);

    if (text.includes("success") || text.includes("registro_exitoso")) {
      showSuccessMessage("Datos enviados correctamente para verificación");
      form.reset();
      document.getElementById("form-success-message")?.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
    } else if (text.includes("error") || text.includes("ERROR")) {
      throw new Error(
        text.includes("MSJ_ERROR")
          ? text
          : "Error en el procesamiento del formulario"
      );
    } else {
      throw new Error("Formato de respuesta inesperado");
    }
  } catch (error) {
    console.error("Error en el envío:", error);
    showErrorMessage(
      "Hubo un error al enviar el formulario. Por favor, inténtelo de nuevo."
    );
  } finally {
    toggleLoadingState(false, loadingIndicator, submitButton);
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

function initializeFormValidation(form) {
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    hideErrorMessages();
    
    const formData = new FormData(form);
    const errors = validateForm(formData);
    
    if (errors.length > 0) {
      displayErrorMessages(errors);
      return;
    }

    // Prepare form data with research areas
    const preparedData = prepareFormData(formData);
    await handleFormSubmission(form, preparedData);
  });

  // Add field-level validation
  const requiredFields = [
    'first-name', 'last-name', 'nationality', 'id-number',
    'institutional-email', 'institution-name-search',
    'campus-faculty', 'specific-unit-career', 'institutional-role'
  ];

  requiredFields.forEach(fieldId => {
    const field = document.getElementById(fieldId);
    if (field) {
      field.addEventListener('blur', () => {
        validateField(fieldId, `Por favor, complete el campo: ${field.labels[0]?.textContent || fieldId}`);
      });
    }
  });
}

document.addEventListener("DOMContentLoaded", function () {
  // console.log("DOM loaded, initializing captcha...");
  fetchCaptchaToken();

  const form = document.getElementById("registration-form");
  if (form) {
    initializeFormValidation(form);

    const institutionNameSearch = document.getElementById(
      "institution-name-search"
    );
    const campusFaculty = document.getElementById("campus-faculty");

    if (institutionNameSearch && campusFaculty) {
      institutionNameSearch.addEventListener("change", setInstitutionCookies);
      campusFaculty.addEventListener("change", setInstitutionCookies);
    }

    let autoSaveTimer;
    form.querySelectorAll("input, select").forEach((element) => {
      element.addEventListener("change", function () {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(() => {
          localStorage.setItem(
            "formData",
            JSON.stringify(Object.fromEntries(new FormData(form)))
          );
        }, 1000);
      });
    });

    // Load saved form data
    const savedData = JSON.parse(localStorage.getItem("formData"));
    if (savedData) {
      Object.keys(savedData).forEach((key) => {
        const field = form.elements[key];
        if (field) {
          field.value = savedData[key];
        }
      });
    }
  } else {
    console.error("Registration form not found");
  }
});
