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

function prepareFormData(formData) {
  const preparedData = new FormData();

  // Map form fields to the expected server-side names
  const fieldMapping = {
    "first-name": "et_pb_contact_nombres_0",
    "last-name": "et_pb_contact_apellidos_0", 
    nationality: "et_pb_contact_nacionalidad_0",
    "id-number": "et_pb_contact_dni_0",
    gender: "et_pb_contact_genero_0",
    "mobile-phone": "et_pb_contact_phone_0",
    department: "et_pb_contact_departamento_0",
    city: "et_pb_contact_ciudad_0",
    "institutional-email": "et_pb_contact_email_0",
    "institution-name": "organizacion",
    "campus-faculty": "organizacion_facultad",
    "specific-unit-career": "organizacion_facultad_carrera",
    "institutional-role": "et_pb_contact_rol_0",
    "pronii-category": "et_pb_contact_categoria_pronii_0",
    "orcid-id": "et_pb_contact_orcid_0",
    "scopus-id": "et_pb_contact_scopus_0", 
    "wos-id": "et_pb_contact_wos_0",
    "research-area": "et_pb_contact_area_investigacion_0_23_0",
    captcha_challenge: "captcha_challenge",
  };

  for (const [key, value] of formData.entries()) {
    if (fieldMapping[key]) {
      preparedData.append(fieldMapping[key], value);
    } else {
      preparedData.append(key, value);
    }
  }

  // Handle date of birth
  const birthYear = formData.get("birth-year");
  const birthMonth = formData.get("birth-month");
  const birthDay = formData.get("birth-day");
  if (birthYear && birthMonth && birthDay) {
    const formattedDate = `${birthYear}-${birthMonth.padStart(
      2,
      "0"
    )}-${birthDay.padStart(2, "0")}`;
    preparedData.append("et_pb_contact_fecha_nacimiento_0", formattedDate);
  }

  // Handle research areas
  const researchAreas = formData.getAll("research-area");
  researchAreas.forEach((area) => {
    preparedData.append("et_pb_contact_area_investigacion_0_23_0", area);
  });

  return preparedData;
}

function validateForm(formData) {
  let errors = [];

  if (!formData.get("et_pb_contact_nombres_0")) errors.push("Nombres");
  if (!formData.get("et_pb_contact_apellidos_0")) errors.push("Apellidos");
  if (!formData.get("et_pb_contact_nacionalidad_0"))
    errors.push("Nacionalidad");
  if (!formData.get("et_pb_contact_dni_0")) errors.push("DNI");
  if (!formData.get("et_pb_contact_genero_0")) errors.push("Sexo");
  if (!formData.get("et_pb_contact_fecha_nacimiento_0"))
    errors.push("Fecha de nacimiento");
  if (!formData.get("et_pb_contact_phone_0")) errors.push("Teléfono");
  if (!formData.get("et_pb_contact_email_0")) errors.push("Email");
  if (!formData.get("et_pb_contact_departamento_0"))
    errors.push("Departamento");
  if (!formData.get("et_pb_contact_ciudad_0")) errors.push("Ciudad");
  if (!formData.get("organizacion")) errors.push("Institución");
  if (!formData.get("organizacion_facultad")) errors.push("Facultad");
  if (!formData.get("et_pb_contact_rol_0")) errors.push("Cargo Institución");

  // Validate specific unit or career
  const specificUnitCareer = formData.get("specific-unit-career");
  if (!specificUnitCareer) {
    errors.push("Unidad o Carrera específica");
  }

  // Validate research areas
  const hasResearchArea = formData.get(
    "et_pb_contact_area_investigacion_0_23_0"
  );
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
        console.log("Captcha token set successfully");
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
function submitForm(event) {
  event.preventDefault();

  // Clear any previous error messages
  hideErrorMessages();
  hideErrorMessage();

  const form = event.target;
  const rawFormData = new FormData(form);
  const preparedFormData = prepareFormData(rawFormData);

  // Get the captcha token
  const captchaToken = document.getElementById("captcha_token").value;
  if (!captchaToken) {
    showErrorMessage(
      "Error de seguridad: Token no encontrado. Por favor, recargue la página."
    );
    return;
  }

  // Add the captcha token to the form data
  preparedFormData.append("captcha_token", captchaToken);

  const loadingIndicator = document.getElementById("loading-indicator");
  const submitButton = form.querySelector('button[type="submit"]');

  if (loadingIndicator && submitButton) {
    loadingIndicator.classList.remove("hidden");
    submitButton.classList.add("hidden");
  }

  fetch('https://cicco.conacyt.gov.py/solicitud_registro/procesar_ingreso_2023_NEW_echo.php', {
    method: 'POST',
    body: preparedFormData,
    credentials: 'include',
    headers: {
      'Accept': 'text/plain, */*',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => {
    console.log('Response status:', response.status);
    return response.text();
  })
  .then(text => {
    console.log('Raw response:', text);
    
    // Check if the response contains the expected data dump
    if (text.includes('All POST Data')) {
      // Data was received by the server, show success
      showSuccessMessage('Datos enviados correctamente para verificación');
      
      // Optional: Display the received data in a more readable format
      console.log('Server received these fields:', 
        text.match(/\[(.*?)\]/g)?.map(field => field.replace(/[\[\]]/g, '')));
    } else {
      // If response doesn't contain the expected format, show as error
      showErrorMessage('Error: Respuesta inesperada del servidor');
    }
  })
  .catch(error => {
    console.error('Fetch error:', error);
    showErrorMessage(
      'Error al enviar el formulario. Por favor, inténtelo de nuevo.'
    );
  })
  .finally(() => {
    if (loadingIndicator && submitButton) {
      loadingIndicator.classList.add("hidden");
      submitButton.classList.remove("hidden");
    }
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
  console.log("DOM loaded, initializing captcha...");
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
