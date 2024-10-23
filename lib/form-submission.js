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
  formData.append('et_pb_contact_nombres_0', rawFormData.get('first-name'));
  formData.append('et_pb_contact_apellidos_0', rawFormData.get('last-name'));
  formData.append('et_pb_contact_nacionalidad_0', rawFormData.get('nationality'));
  formData.append('et_pb_contact_dni_0', rawFormData.get('id-number'));
  formData.append('et_pb_contact_genero_0', rawFormData.get('gender'));
  formData.append('et_pb_contact_phone_0', rawFormData.get('mobile-phone'));
  formData.append('et_pb_contact_email_0', rawFormData.get('institutional-email'));
  formData.append('et_pb_contact_departamento_0', rawFormData.get('department'));
  formData.append('et_pb_contact_ciudad_0', rawFormData.get('city'));
  formData.append('organizacion', rawFormData.get('institution-name'));
  formData.append('organizacion_facultad', rawFormData.get('campus-faculty'));
  formData.append('organizacion_facultad_carrera', rawFormData.get('specific-unit-career'));
  formData.append('et_pb_contact_rol_0', rawFormData.get('institutional-role'));
  formData.append('et_pb_contact_categoria_pronii_0', rawFormData.get('pronii-category'));
  formData.append('et_pb_contact_orcid_0', rawFormData.get('orcid-id'));
  formData.append('et_pb_contact_scopus_0', rawFormData.get('scopus-id'));
  formData.append('et_pb_contact_wos_0', rawFormData.get('wos-id'));
  
  // Handle date fields
  const birthDate = `${rawFormData.get('birth-year')}-${rawFormData.get('birth-month')}-${rawFormData.get('birth-day')}`;
  formData.append('et_pb_contact_fecha_nacimiento_0', birthDate);
  
  // Handle research area
  formData.append('et_pb_contact_area_investigacion_0_23_0', rawFormData.get('research-area'));
  
  // Add captcha field
  formData.append('captcha', document.getElementById('captcha_token').value);
  formData.append('captcha_challenge', document.getElementById('captcha_token').value);
  
  return formData;
}

function validateForm(formData) {
  let errors = [];

  if (!formData.get("et_pb_contact_nombres_0")) errors.push("Nombres");
  if (!formData.get("et_pb_contact_apellidos_0")) errors.push("Apellidos");
  if (!formData.get("et_pb_contact_nacionalidad_0")) errors.push("Nacionalidad");
  if (!formData.get("et_pb_contact_dni_0")) errors.push("DNI");
  if (!formData.get("et_pb_contact_genero_0")) errors.push("Sexo");
  if (!formData.get("et_pb_contact_fecha_nacimiento_0")) errors.push("Fecha de nacimiento");
  if (!formData.get("et_pb_contact_phone_0")) errors.push("Teléfono");
  if (!formData.get("et_pb_contact_email_0")) errors.push("Email");
  if (!formData.get("et_pb_contact_departamento_0")) errors.push("Departamento");
  if (!formData.get("et_pb_contact_ciudad_0")) errors.push("Ciudad");
  if (!formData.get("organizacion")) errors.push("Institución");
  if (!formData.get("organizacion_facultad")) errors.push("Facultad");
  if (!formData.get("et_pb_contact_rol_0")) errors.push("Cargo Institución");

  // Validate specific unit or career
  const specificUnitCareer = formData.get("organizacion_facultad_carrera");
  if (!specificUnitCareer) {
    errors.push("Unidad o Carrera específica");
  }

  // Validate research areas
  const hasResearchArea = formData.get("et_pb_contact_area_investigacion_0_23_0");
  if (!hasResearchArea) {
    errors.push("Dominio científico de su interés");
  }

  if (!formData.get("captcha_challenge")) errors.push("Captcha");

  return errors;
}

function displayErrorMessages(errors) {
  errors.forEach((error) => {
    const element = document.getElementById(
      `${error.toLowerCase().replace(/ /g, "-")}-error`
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
    loadingIndicator.classList.toggle('hidden', !isLoading);
    submitButton.classList.toggle('hidden', isLoading);
  }
}

function submitForm(event) {
  event.preventDefault();

  // Clear any previous messages
  hideErrorMessages();
  hideErrorMessage();
  hideSuccessMessage();

  const form = event.target;
  const rawFormData = new FormData(form);

  // Validate form data
  const errors = validateForm(rawFormData);
  if (errors.length > 0) {
    displayErrorMessages(errors);
    showErrorMessage('Por favor, complete todos los campos requeridos.');
    return;
  }

  const preparedData = prepareFormData(rawFormData);

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

  fetch('https://cicco.conacyt.gov.py/solicitud_registro/procesar_ingreso_2023_NEW.php', {
    method: 'POST',
    body: preparedData,
    credentials: 'include',
    headers: {
      'Accept': 'text/plain, */*',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => {
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    return response.text();
  })
  .then(text => {
    console.log('Server response:', text);
    
    // Check for success or error indicators in the response
    if (text.includes('success') || text.includes('registro_exitoso')) {
      showSuccessMessage('Datos enviados correctamente para verificación');
      form.reset();
      document.getElementById('form-success-message')?.scrollIntoView({ 
        behavior: 'smooth', 
        block: 'start' 
      });
    } else if (text.includes('error') || text.includes('ERROR')) {
      throw new Error(text.includes('MSJ_ERROR') ? text : 'Error en el procesamiento del formulario');
    } else {
      throw new Error('Formato de respuesta inesperado');
    }
  })
  .catch(error => {
    console.error('Error en el envío:', error);
    showErrorMessage(
      'Error al enviar el formulario. Por favor, verifique los datos e inténtelo de nuevo.'
    );
  })
  .finally(() => {
    toggleLoadingState(false, loadingIndicator, submitButton);
  });
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

document.addEventListener("DOMContentLoaded", function () {
  // console.log("DOM loaded, initializing captcha...");
  fetchCaptchaToken();

  const form = document.getElementById("registration-form");
  if (form) {
    form.addEventListener("submit", submitForm);

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
