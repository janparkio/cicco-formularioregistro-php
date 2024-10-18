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
    if (value !== null && value !== undefined) {
      mappedData.append(mappedKey, value);
    }
  }

  // Handle special cases like date and research areas
  const birthYear = formData.get("birth-year");
  const birthMonth = formData.get("birth-month");
  const birthDay = formData.get("birth-day");
  if (birthYear && birthMonth && birthDay) {
    const birthDate = `${birthYear}-${birthMonth.padStart(2, "0")}-${birthDay.padStart(2, "0")}`;
    mappedData.append("et_pb_contact_fecha_nacimiento_0", birthDate);
  }

  const researchAreas = formData.getAll("research-area");
  researchAreas.forEach((area, index) => {
    if (area) {
      mappedData.append(`et_pb_contact_area_investigacion_0_23_${index}`, area);
    }
  });

  return mappedData;
}

function prepareFormData() {
  const form = document.getElementById("registration-form");
  if (!form) {
    console.error("Registration form not found");
    return new FormData();
  }
  const formData = new FormData(form);

  // Add any additional data that's not part of the form
  formData.append("et_pb_contact_fecha_ingreso_0", getCurrentDate());

  // Handle the phone number
  const phoneInput = formData.get("mobile-phone");
  if (phoneInput) {
    const phoneValue = phoneInput.replace(/\D/g, "");
    formData.set("mobile-phone", phoneValue);
  }

  // Add CAPTCHA challenge
  const captchaChallenge = document.getElementById("captcha_challenge").value;
  if (captchaChallenge) {
    formData.append("captcha_challenge", captchaChallenge);
  }
  const captchaText = document.cookie
    .split("; ")
    .find((row) => row.startsWith("captcha_text="))
    ?.split("=")[1];
  if (captchaText) {
    formData.append("captcha_text", captchaText);
  }

  console.log("Original form data:", Object.fromEntries(formData));

  return mapFormData(formData);
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

  // Validate research areas
  const hasResearchArea = ["0", "1", "2", "3", "4", "5"].some((index) =>
    formData.get(`et_pb_contact_area_investigacion_0_23_${index}`),
  );
  if (!hasResearchArea) {
    errors.push("Dominio científico de su interés");
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

function submitForm(event) {
  event.preventDefault();
  console.log("Form submission started");

  hideErrorMessages();
  hideErrorMessage();
  hideSuccessMessage();

  const formData = prepareFormData();
  console.log("Form data prepared", Object.fromEntries(formData));

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

  const loadingIndicator = document.getElementById("loading-indicator");
  const submitButton = document.querySelector('button[type="submit"]');
  if (loadingIndicator && submitButton) {
    loadingIndicator.classList.remove("hidden");
    submitButton.classList.add("hidden");
  }

  const urlEncodedData = new URLSearchParams(formData);

  console.log("Data being sent to server:", urlEncodedData.toString());
  console.log("Cookies:", document.cookie);

  fetch(
    "https://cicco.conacyt.gov.py/solicitud_registro/procesar_ingreso_2023_NEW.php",
    {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: urlEncodedData.toString(),
      credentials: "include",
    },
  )
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.text();
    })
    .then((result) => {
      console.log("Full server response:", result);
      if (result.includes("Location:")) {
        const url = result.split("Location: ")[1].trim();
        window.location.href = url;
      } else if (result.includes("Error en datos de ingreso")) {
        const errorMessage = result.match(
          /<b>Datos a verificar:<br \/>(.*?)<\/b>/s,
        );
        if (errorMessage && errorMessage[1]) {
          showErrorMessage(errorMessage[1].replace(/<br \/>/g, ", "));
        } else {
          showErrorMessage(
            "Error en datos de ingreso. Por favor, verifique sus datos e intente nuevamente.",
          );
        }
      } else {
        showErrorMessage(
          "Hubo un error inesperado. Por favor, intente nuevamente.",
        );
      }
    })
    .catch((error) => {
      console.error("Error submitting form:", error);
      showErrorMessage(
        "Hubo un error al enviar el formulario. Por favor, inténtelo de nuevo.",
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
  document.cookie = `Organizacion=${encodeURIComponent(institution)}; path=/; SameSite=Lax`;
  document.cookie = `Facultad=${encodeURIComponent(faculty)}; path=/; SameSite=Lax`;
}

document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("registration-form");
  if (form) {
    form.addEventListener("submit", submitForm);

    const institutionNameSearch = document.getElementById(
      "institution-name-search",
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
            JSON.stringify(Object.fromEntries(new FormData(form))),
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
