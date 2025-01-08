// Search Institution Class
class InstitutionSearch {
  constructor(options = {}) {
    this.institutions = [];
    this.institutionData = null;
    this.fuse = null;
    this.elements = {
      searchInput: document.getElementById('institution-name-search'),
      dropdown: document.getElementById('institution-name-dropdown'),
      hiddenInput: document.getElementById('institution-name'),
      allInstitutionsModal: document.getElementById('all-institutions-modal'),
      allInstitutionsBody: document.getElementById('all-institutions-body'),
      showAllButton: document.getElementById('show-all-institutions'),
      requestNewButton: document.getElementById('request-new-institution'),
      closeAllInstitutionsModal: document.getElementById('close-all-institutions-modal'),
      modalSearchInput: document.getElementById('modal-search-input'),
      clearInstitution: document.getElementById('clear-institution')
    };

    // Load geographic data
    fetch('data/REGION_CIUDAD.json')
      .then(response => response.json())
      .then(data => {
        this.geographicData = data;
        this.initGeographicSelectors();
      })
      .catch(error => console.error('Error loading geographic data:', error));

    this.initNationalitySelector();

    this.debug = options.debug || false; // Add debug flag
  }

  init(institutionData) {
    if (this.debug) console.log('Initializing with data:', institutionData);
    this.institutionData = institutionData;
    this.processInstitutions();
    this.initializeFuse();
    this.bindEvents();
    
    if (this.debug) {
      console.log('Elements:', this.elements);
      console.log('Institutions processed:', this.institutions);
      console.log('Fuse initialized:', this.fuse);
    }
  }

  processInstitutions() {
    // Only get top-level institution names
    this.institutions = Object.keys(this.institutionData).map(institutionName => ({
      name: institutionName,
      value: institutionName,
      normalizedName: this.normalizeText(institutionName)
    }));    
  }

  normalizeText(text) {
    return text
      .toLowerCase()
      .trim()
      .normalize('NFD')                    // Decompose accented characters
      .replace(/[\u0300-\u036f]/g, '')    // Remove diacritics
      .replace(/[^a-z0-9\s]/g, '');       // Remove special characters
  }

  initializeFuse() {
    this.fuse = new Fuse(this.institutions, {
      keys: ['name', 'searchText'],
      threshold: 0.3,
      minMatchCharLength: 3,
      ignoreLocation: true,
      ignoreFieldNorm: true,
      useExtendedSearch: true,
      findAllMatches: true,
      includeScore: true
    });
  }

  bindEvents() {
    // Search input events
    this.elements.searchInput.addEventListener('input', (e) => this.handleSearch(e));
    this.elements.searchInput.addEventListener('click', () => this.showAllInstitutions());

    // Modal events
    this.elements.showAllButton.addEventListener('click', () => this.showAllInstitutions());
    this.elements.closeAllInstitutionsModal.addEventListener('click', () => this.hideModal());
    this.elements.modalSearchInput?.addEventListener('input', (e) => this.handleModalSearch(e));

    // Clear button event
    this.elements.clearInstitution?.addEventListener('click', () => this.clearInstitution());

    // Add faculty change event listener
    const facultySelect = document.getElementById('campus-faculty');
    if (facultySelect) {
      facultySelect.addEventListener('change', (e) => {
        const selectedInstitution = this.elements.hiddenInput.value;
        if (selectedInstitution && e.target.value) {
          this.updateCareerSelect(selectedInstitution, e.target.value);
          document.querySelector('.career-field')?.classList.remove('hidden');
        } else {
          document.querySelector('.career-field')?.classList.add('hidden');
        }
      });
    }
  }

  handleSearch(event) {
    const searchTerm = this.normalizeText(event.target.value);
    
    if (!searchTerm) {
      this.elements.dropdown.classList.add('hidden');
      return;
    }
    
    const results = this.institutions.filter(inst => 
      inst.normalizedName.includes(searchTerm)
    );
    
    this.populateDropdown(results);
  }

  populateDropdown(items) {
    this.elements.dropdown.innerHTML = '';
    
    if (items.length === 0) {
      const noResults = document.createElement('div');
      noResults.textContent = 'No se encontraron resultados';
      noResults.classList.add('p-2', 'text-gray-500', 'text-sm');
      this.elements.dropdown.appendChild(noResults);
    } else {
      items.slice(0, 10).forEach((item) => {
        const div = document.createElement('div');
        div.textContent = item.name;  // Just show institution name
        div.classList.add(
          'cursor-pointer',
          'select-none',
          'relative',
          'py-2',
          'pl-3',
          'pr-9',
          'hover:bg-primary-600',
          'hover:text-white'
        );
        div.addEventListener('click', () => this.selectInstitution(item));
        this.elements.dropdown.appendChild(div);
      });
    }
    
    this.elements.dropdown.classList.remove('hidden');
  }

  selectInstitution(institution) {
    this.elements.searchInput.value = institution.name;
    this.elements.hiddenInput.value = institution.value;
    this.elements.searchInput.classList.add('ring-2', 'ring-green-500');
    this.elements.dropdown.classList.add('hidden');
    this.elements.clearInstitution.classList.remove('hidden');
    
    // Show faculty field
    const facultyField = document.querySelector('.faculty-field');
    if (facultyField) {
      facultyField.classList.remove('hidden');
    }
    
    // Hide career field until faculty is selected
    const careerField = document.querySelector('.career-field');
    if (careerField) {
      careerField.classList.add('hidden');
    }
    
    this.updateFacultySelect(institution.value);
    this.hideModal();
  }

  updateFacultySelect(institutionName) {
    const facultySelect = document.getElementById('campus-faculty');
    const careerSelect = document.getElementById('specific-unit-career');

    if (!facultySelect || !careerSelect) return;

    const institutionData = this.institutionData[institutionName];
    if (!institutionData) return;

    // If institutionData is an array, it means it's direct units
    if (Array.isArray(institutionData)) {
      facultySelect.innerHTML = '<option value="">Seleccione una opción</option>';
      const option = document.createElement('option');
      option.value = "direct";
      option.textContent = "Unidad directa";
      facultySelect.appendChild(option);
      facultySelect.value = "direct";
      this.updateCareerSelect(institutionName, "direct");
      return;
    }

    // Get faculties from the institution
    const faculties = Object.keys(institutionData);
    
    // Populate faculty select
    facultySelect.innerHTML = '<option value="">Seleccione una opción</option>';
    faculties.sort().forEach(faculty => {
      const option = document.createElement('option');
      option.value = faculty;
      option.textContent = faculty;
      facultySelect.appendChild(option);
    });
    
    facultySelect.disabled = false;
    careerSelect.disabled = true;
    careerSelect.value = '';
  }

  updateCareerSelect(institutionName, facultyName) {
    // Get the input and datalist elements
    const careerInput = document.getElementById('specific-unit-career');
    const careerDatalist = document.getElementById('career-options');

    if (!careerInput || !careerDatalist) {
      if (this.debug) console.error('Career input or datalist elements not found');
      return;
    }

    const institutionData = this.institutionData[institutionName];
    if (!institutionData) {
      if (this.debug) console.error('No institution data found for:', institutionName);
      return;
    }

    let units;
    if (Array.isArray(institutionData)) {
      units = institutionData;
    } else if (facultyName === "direct") {
      units = institutionData;
    } else {
      units = institutionData[facultyName] || [];
    }

    if (this.debug) console.log('Units to display:', units);

    // Clear and populate the datalist
    careerDatalist.innerHTML = '';
    units.sort().forEach(unit => {
      const option = document.createElement('option');
      option.value = unit;
      careerDatalist.appendChild(option);
    });

    // Enable the input and show the field
    careerInput.disabled = false;
    careerInput.value = ''; // Clear the input
    
    // Show the career field container
    const careerField = document.querySelector('.career-field');
    if (careerField) {
      careerField.classList.remove('hidden');
    }
  }

  showAllInstitutions() {
    const tbody = this.elements.allInstitutionsBody;
    if (!tbody) return;
    
    tbody.innerHTML = '';

    const sortedInstitutions = this.institutions.sort((a, b) => 
      a.name.localeCompare(b.name)
    );

    sortedInstitutions.forEach(institution => {
      const row = document.createElement('tr');
      row.classList.add('hover:bg-gray-100', 'transition-colors', 'cursor-pointer');
      
      const cell = document.createElement('td');
      cell.textContent = institution.name;
      cell.classList.add('px-4', 'py-2');
      
      row.appendChild(cell);
      row.addEventListener('click', () => {
        const selectedRow = tbody.querySelector('tr.selected');
        if (selectedRow) {
          selectedRow.classList.remove('selected', 'bg-blue-100');
        }
        row.classList.add('selected', 'bg-blue-100');
        this.selectInstitution(institution);
      });
      
      tbody.appendChild(row);
    });

    this.addRequestInstitutionRow(tbody);
    this.elements.allInstitutionsModal.classList.remove('hidden');
    this.elements.modalSearchInput?.focus();
  }

  hideModal() {
    this.elements.allInstitutionsModal.classList.add('hidden');
  }

  clearInstitution() {
    this.elements.searchInput.value = '';
    this.elements.hiddenInput.value = '';
    this.elements.searchInput.classList.remove('ring-2', 'ring-green-500');
    this.elements.clearInstitution.classList.add('hidden');
    document.querySelector('.faculty-field').classList.add('hidden');
    document.querySelector('.career-field').classList.add('hidden');
  }

  addRequestInstitutionRow(tbody) {
    const requestRow = document.createElement('tr');
    requestRow.innerHTML = `
      <td class="px-4 py-4 text-center border-t">
        <a href="https://cicco.conacyt.gov.py/contactos/" 
           class="text-primary-600 hover:text-primary-900 text-sm font-medium inline-flex items-center">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          No encuentro mi institución, quiero solicitar su adición
        </a>
      </td>
    `;
    tbody.appendChild(requestRow);
  }

  populateSelect(selectElement, options) {
    selectElement.innerHTML = '<option value="">Seleccione una opción</option>';
    options
      .sort((a, b) => a.localeCompare(b))
      .forEach(option => {
        const optionElement = document.createElement('option');
        optionElement.value = option;
        optionElement.textContent = option;
        selectElement.appendChild(optionElement);
      });
  }

  handleModalSearch(event) {
    const searchTerm = this.normalizeText(event.target.value);
    const rows = this.elements.allInstitutionsBody.getElementsByTagName('tr');
    
    Array.from(rows).forEach(row => {
      if (row.cells[0]?.textContent) {
        const text = this.normalizeText(row.cells[0].textContent);
        row.style.display = text.includes(searchTerm) ? '' : 'none';
      }
    });
  }

  initGeographicSelectors() {
    const departmentSelect = document.getElementById('department');
    const citySelect = document.getElementById('city');

    if (!departmentSelect || !citySelect) return;

    // Load geographic data
    fetch('data/REGION_CIUDAD.json')
      .then(response => response.json())
      .then(data => {
        if (!data.pais) {
          console.error('No se encontró la lista de regiones');
          return;
        }

        // Populate departments directly from pais array
        departmentSelect.innerHTML = '<option value="">Seleccione un departamento</option>';
        data.pais.forEach(region => {
          const option = document.createElement('option');
          option.value = region.nombre_region;
          option.textContent = region.nombre_region;
          departmentSelect.appendChild(option);
        });

        // Handle department change
        departmentSelect.addEventListener('change', (e) => {
          const selectedDepartment = e.target.value;
          citySelect.innerHTML = '<option value="">Seleccione una ciudad</option>';
          citySelect.disabled = !selectedDepartment;

          if (selectedDepartment) {
            // Find the selected region and its cities
            const selectedRegion = data.pais.find(region => 
              region.nombre_region === selectedDepartment
            );
            
            if (selectedRegion && selectedRegion.ciudades) {
              selectedRegion.ciudades.forEach(cityData => {
                const option = document.createElement('option');
                option.value = cityData.ciudad;
                option.textContent = cityData.ciudad;
                citySelect.appendChild(option);
              });
            }
          }
        });
      })
      .catch(error => console.error('Error loading geographic data:', error));
  }

  initNationalitySelector() {
    const nationalitySelect = document.getElementById('nationality');
    
    if (!nationalitySelect) return;

    // Load nationality data
    fetch('data/nacionalidades.json')
      .then(response => response.json())
      .then(data => {
        if (!data.paises) {
          console.error('No se encontró la lista de países');
          return;
        }

        // Populate nationality select directly from paises array
        nationalitySelect.innerHTML = '<option value="">Seleccione una opción</option>';
        data.paises.forEach(pais => {
          const option = document.createElement('option');
          option.value = pais.nombre;
          option.textContent = pais.nombre;
          nationalitySelect.appendChild(option);
        });
      })
      .catch(error => console.error('Error loading nationality data:', error));
  }
}

// Export the class instead of initializing immediately
window.InstitutionSearch = InstitutionSearch; 