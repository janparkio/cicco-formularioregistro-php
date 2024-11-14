/**
 * Test Data Generator Utility
 * Only included in development/testing environments
 */

// Sample test data matching the exact structure from the forms
const TEST_INSTITUTIONS = {
  "Universidad Nacional de Asunción. UNA": {
    "Facultad de Ciencias Exactas y Naturales": [
      "Lic. en Ciencias Mención Matemática Pura",
      "Lic. en Ciencias Mención Física",
      "Lic. en Ciencias Mención Química",
    ],
    "Facultad de Ingeniería": [
      "Ingeniería Civil",
      "Ingeniería Electrónica",
      "Ingeniería Mecánica",
    ],
  },
  "Universidad Católica": {
    "Facultad de Ciencias y Tecnología": [
      "Ingeniería Informática",
      "Ingeniería Civil",
      "Ingeniería Industrial",
    ],
    "Facultad de Ciencias de la Salud": ["Medicina", "Enfermería"],
  },
};

const TEST_REGIONS = {
  Central: ["San Lorenzo", "Fernando de la Mora", "Luque", "Lambaré"],
  Asunción: ["Asunción"],
};

const TEST_CAREERS = [
  { denominacion: "Ingeniería Civil", sector: "Oficial" },
  { denominacion: "Medicina", sector: "Oficial" },
  { denominacion: "Enfermería", sector: "Oficial" },
];

// Test data generator function
function generateTestFormData() {
  return {
    institution: "Universidad Nacional de Asunción. UNA",
    faculty: "Facultad de Ciencias Exactas y Naturales",
    career: "Lic. en Ciencias Mención Matemática Pura",
    region: "Central",
    city: "San Lorenzo",
  };
}

const TEST_DATA_GENERATOR = {
  // Counter for unique IDs
  _counter: parseInt(localStorage.getItem("testCounter") || "1"),

  // Get and increment counter
  getNextCounter() {
    const current = this._counter++;
    localStorage.setItem("testCounter", this._counter.toString());
    return current;
  },

  // Generate random date between two dates
  randomDate(start, end) {
    return new Date(
      start.getTime() + Math.random() * (end.getTime() - start.getTime())
    );
  },

  // Helper to get random item from array
  getRandomItem(array) {
    return array[Math.floor(Math.random() * array.length)];
  },

  // Helper to get random institution data
  getRandomInstitutionData() {
    const institution = this.getRandomItem(Object.keys(TEST_INSTITUTIONS));
    const faculties = Object.keys(TEST_INSTITUTIONS[institution]);
    const faculty = this.getRandomItem(faculties);
    const career = this.getRandomItem(TEST_INSTITUTIONS[institution][faculty]);
    
    return { institution, faculty, career };
  },

  // Helper to get random location data
  getRandomLocationData() {
    const region = this.getRandomItem(Object.keys(TEST_REGIONS));
    const city = this.getRandomItem(TEST_REGIONS[region]);
    
    return { region, city };
  },

  generateTestData() {
    const counter = this.getNextCounter();
    const birthDate = this.randomDate(new Date(1960, 0, 1), new Date(2000, 11, 31));
    
    // Get random institution and location data
    const { institution, faculty, career } = this.getRandomInstitutionData();
    const { region, city } = this.getRandomLocationData();

    return {
      "fecha-ingreso": new Date().toISOString().split('T')[0],
      "first-name": `TestUser${counter}`,
      "last-name": `TestLastName${counter}`,
      "id-number": String(1000000 + counter).padStart(7, "0"),
      "nationality": "paraguayo",
      "gender": this.getRandomItem(["M", "F"]),
      "birth-year": birthDate.getFullYear().toString(),
      "birth-month": (birthDate.getMonth() + 1).toString(), // +1 since getMonth() is 0-based
      "birth-day": birthDate.getDate().toString(),
      "mobile-phone": `(999) ${String(100000 + counter).slice(-3)}-${String(100000 + counter).slice(-3)}`,
      "institutional-email": `test.user${counter}@fpuna.edu.py`,
      // Use the actual test data
      "institution-name": institution,
      "campus-faculty": faculty,
      "specific-unit-career": career,
      "institutional-role": this.getRandomItem([
        "investigador-pronii",
        "investigador",
        "docente",
        "estudiante",
        "administrativo"
      ]),
      "pronii-category": this.getRandomItem([
        "ninguna",
        "nivel-i",
        "nivel-ii",
        "nivel-iii",
        "candidato"
      ]),
      "orcid-id": `0000-0002-${String(1000 + counter).padStart(4, "0")}-${String(counter).padStart(4, "0")}`,
      "scopus-id": `${57200000000 + counter}`,
      "wos-id": `X-${String(counter).padStart(4, "0")}-2024`,
      // Use the actual location data
      "department": region,
      "city": city,
      "research-area-natural": true,
      "captcha": generateCaptchaString()
    };
  },

  fillForm() {
    const testData = this.generateTestData();

    // First set institution and department to trigger cascading dropdowns
    const institutionField = document.getElementById('institution-name');
    const departmentField = document.getElementById('department');
    
    if (institutionField) {
      institutionField.value = testData['institution-name'];
      institutionField.dispatchEvent(new Event('change'));
    }
    
    if (departmentField) {
      departmentField.value = testData['department'];
      departmentField.dispatchEvent(new Event('change'));
    }

    // Wait for dropdowns to populate
    setTimeout(() => {
      // Then fill in faculty and city
      Object.entries(testData).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (!element) return;

        if (element.type === 'checkbox') {
          element.checked = value;
        } else {
          element.value = value;
        }

        element.dispatchEvent(new Event('change'));
      });
    }, 500);
  }
};

// Add keyboard shortcut handler
document.addEventListener('keydown', (event) => {
  if (event.shiftKey && event.key === 'K') {
    console.log('Filling form with test data...');
    TEST_DATA_GENERATOR.fillForm();
  }
});
