<form id="registration-form" method="post" action="https://cicco.conacyt.gov.py/solicitud_registro_usuario/lib/process_registration.php">
  <div class="space-y-12">
    <div>
      <h2 class="text-base font-semibold leading-7 text-gray-900">Información Personal</h2>
      <p class="mt-1 text-sm leading-6 text-gray-600">Por favor, proporcione su información personal.</p>

      <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
        <!-- Nombres -->
        <div class="sm:col-span-3">
          <label for="first-name" class="block text-sm font-medium leading-6 text-gray-900">Nombres</label>
          <div class="mt-2">
            <input type="text" name="first-name" id="first-name" autocomplete="given-name" required
              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
            <span id="first-name-error" class="mt-2 text-sm text-red-500 hidden"></span>
          </div>
        </div>

        <!-- Apellidos -->
        <div class="sm:col-span-3">
          <label for="last-name" class="block text-sm font-medium leading-6 text-gray-900">Apellidos</label>
          <div class="mt-2">
            <input type="text" name="last-name" id="last-name" autocomplete="family-name" required
              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
            <span id="last-name-error" class="mt-2 text-sm text-red-500 hidden"></span>
          </div>
        </div>

        <!-- Nacionalidad -->
        <div class="sm:col-span-3">
          <label for="nationality" class="block text-sm font-medium leading-6 text-gray-900">País de origen</label>
          <div class="mt-2">
            <select id="nationality" name="nationality" autocomplete="country-name" required
              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:max-w-xs sm:text-sm sm:leading-6">
              <option value="">Seleccione un país</option>
            </select>
            <span id="nationality-error" class="mt-2 text-sm text-red-500 hidden"></span>
          </div>
        </div>

        <!-- No. de Cédula de Identidad -->
        <div class="sm:col-span-3">
          <label for="id-number" class="block text-sm font-medium leading-6 text-gray-900">No. de Cédula de
            Identidad</label>
          <div class="mt-2">
            <input type="text" name="id-number" id="id-number" autocomplete="off" required pattern="\d{6,10}"
              title="Ingrese un número de cédula válido (6 a 10 dígitos)"
              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6 peer">
            <span id="id-validation" class="mt-2 text-sm text-red-500 hidden">
              El número de cédula o DNI debe tener entre 5 y 15 dígitos.
            </span>
            <span id="id-number-error" class="mt-2 text-sm text-red-500 hidden"></span>
          </div>
          <p id="username-display" class="mt-2 text-sm text-gray-500"></p>
        </div>
        <script>
          document.addEventListener('DOMContentLoaded', function () {
            const idInput = document.getElementById('id-number');
            const usernameDisplay = document.getElementById('username-display');
            const idValidation = document.getElementById('id-validation');

            idInput.addEventListener('input', function () {
              const idValue = idInput.value;
              usernameDisplay.textContent = idValue ? `Su usuario será: cona${idValue}` : '';
            });

            idInput.addEventListener('blur', function () {
              if (!idInput.checkValidity()) {
                idValidation.classList.remove('hidden');
              } else {
                idValidation.classList.add('hidden');
              }
            });
          });
        </script>

        <!-- Sexo -->
        <div class="sm:col-span-3">
          <label for="gender" class="block text-sm font-medium leading-6 text-gray-900">Sexo</label>
          <div class="mt-2">
            <div class="flex items-center gap-x-3">
              <input id="gender-woman" name="gender" type="radio" value="Femenino" required
                class="h-4 w-4 border-gray-300 text-primary-600 focus:ring-primary-600">
              <label for="gender-woman" class="block text-sm font-medium leading-6 text-gray-900">Femenino</label>
            </div>
            <div class="flex items-center gap-x-3">
              <input id="gender-man" name="gender" type="radio" value="Masculino" required
                class="h-4 w-4 border-gray-300 text-primary-600 focus:ring-primary-600">
              <label for="gender-man" class="block text-sm font-medium leading-6 text-gray-900">Masculino</label>
            </div>
          </div>
          <span id="gender-error" class="mt-2 text-sm text-red-500 hidden"></span>
        </div>

        <!-- Fecha de nacimiento -->
        <div class="sm:col-span-3">
          <label for="birth-year" class="block text-sm font-medium leading-6 text-gray-900">Fecha de nacimiento</label>
          <div class="mt-2 grid grid-cols-9 gap-3">
            <div class="col-span-3">
              <label for="birth-year" class="sr-only">Año</label>
              <select id="birth-year" name="birth-year" required
                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                <option value="">Año</option>
                <?php
                $currentYear = date("Y");
                $minYear = $currentYear - 120;
                $maxYear = $currentYear - 5;
                for ($year = $maxYear; $year >= $minYear; $year--) {
                  echo "<option value=\"$year\">$year</option>";
                }
                ?>
              </select>
            </div>
            <div class="col-span-4">
              <label for="birth-month" class="sr-only">Mes</label>
              <select id="birth-month" name="birth-month" required
                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                <option value="">Mes</option>
                <?php
                $months = [
                  1 => "Enero",
                  2 => "Febrero",
                  3 => "Marzo",
                  4 => "Abril",
                  5 => "Mayo",
                  6 => "Junio",
                  7 => "Julio",
                  8 => "Agosto",
                  9 => "Septiembre",
                  10 => "Octubre",
                  11 => "Noviembre",
                  12 => "Diciembre",
                ];
                foreach ($months as $num => $name) {
                  echo "<option value=\"$num\">$name</option>";
                }
                ?>
              </select>
            </div>
            <div class="col-span-2">
              <label for="birth-day" class="sr-only">Día</label>
              <select id="birth-day" name="birth-day" required
                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                <option value="">Día</option>
                <?php for ($day = 1; $day <= 31; $day++) {
                  echo "<option value=\"$day\">$day</option>";
                } ?>
              </select>
            </div>
          </div>
          <span id="birth-year-error" class="mt-2 text-sm text-red-500 hidden"></span>
          <span id="birth-month-error" class="mt-2 text-sm text-red-500 hidden"></span>
          <span id="birth-day-error" class="mt-2 text-sm text-red-500 hidden"></span>
          <p id="age-display" class="mt-2 text-sm text-gray-500 cursor-pointer"></p>
          <p id="age-hidden" class="mt-2 text-sm text-gray-500 cursor-pointer hidden">Edad: Oculta</p>
        </div>
        <script>
          document.addEventListener('DOMContentLoaded', function () {
            const yearSelect = document.getElementById('birth-year');
            const monthSelect = document.getElementById('birth-month');
            const daySelect = document.getElementById('birth-day');
            const ageDisplay = document.getElementById('age-display');
            const ageHidden = document.getElementById('age-hidden');

            function calculateAge() {
              const year = yearSelect.value;
              const month = monthSelect.value;
              const day = daySelect.value;

              if (year && month && day) {
                const birthDate = new Date(year, month - 1, day);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();

                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                  age--;
                }

                ageDisplay.textContent = `Edad: ${age} años`;
              } else {
                ageDisplay.textContent = '';
              }
            }

            yearSelect.addEventListener('change', calculateAge);
            monthSelect.addEventListener('change', calculateAge);
            daySelect.addEventListener('change', calculateAge);

            ageDisplay.addEventListener('click', function () {
              ageDisplay.classList.add('hidden');
              ageHidden.classList.remove('hidden');
            });

            ageHidden.addEventListener('click', function () {
              ageHidden.classList.add('hidden');
              ageDisplay.classList.remove('hidden');
            });
          });
        </script>
        <!-- END::Fecha de nacimiento -->

        <!-- No. de teléfono celular -->
        <div class="sm:col-span-4">
          <label for="mobile-phone" class="block text-sm font-medium leading-6 text-gray-900">No. de teléfono
            celular</label>
          <div class="mt-2 flex rounded-md shadow-sm">
            <span
              class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
              +595
            </span>
            <input type="tel" name="mobile-phone" id="mobile-phone" autocomplete="tel"
              class="flex-1 block w-full rounded-none rounded-r-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6 peer"
              placeholder="(999) 123-456" pattern="\(\d{3}\)\s\d{3}-\d{3}" maxlength="13" required>
          </div>
          <span class="mt-2 hidden text-sm text-red-500 peer-[&:not(:placeholder-shown):not(:focus):invalid]:block">
            Ingrese un número de teléfono válido en el formato "(999) 123-456".
          </span>
          <span id="phone-validation" class="mt-2 text-sm text-red-500 hidden">
            El número de teléfono debe tener exactamente 9 dígitos.
          </span>
          <span id="phone-zero-validation" class="mt-2 text-sm text-red-500 hidden">
            Tu número no debe incluir el 0 del comienzo, para utilizar el formato internacional.
          </span>
          <span id="mobile-phone-error" class="mt-2 text-sm text-red-500 hidden"></span>
        </div>
        <script>
          document.addEventListener('DOMContentLoaded', function () {
            const phoneInput = document.getElementById('mobile-phone');
            const phoneValidation = document.getElementById('phone-validation');
            const phoneZeroValidation = document.getElementById('phone-zero-validation');

            phoneInput.addEventListener('input', function () {
              let phoneValue = phoneInput.value.replace(/\D/g, '');
              if (phoneValue.length > 0) {
                phoneValue = phoneValue.match(/(\d{0,3})(\d{0,3})(\d{0,3})/);
                phoneInput.value = !phoneValue[2] ? `(${phoneValue[1]}` : `(${phoneValue[1]}) ${phoneValue[2]}${phoneValue[3] ? '-' + phoneValue[3] : ''}`;
              }
              if (phoneValue[1].length + (phoneValue[2] ? phoneValue[2].length : 0) + (phoneValue[3] ? phoneValue[3].length : 0) !== 9) {
                phoneValidation.classList.remove('hidden');
              } else {
                phoneValidation.classList.add('hidden');
              }
              if (phoneValue[1] && phoneValue[1].charAt(0) === '0') {
                phoneZeroValidation.classList.remove('hidden');
              } else {
                phoneZeroValidation.classList.add('hidden');
              }
            });

            phoneInput.addEventListener('blur', function () {
              const phoneValue = phoneInput.value.replace(/\D/g, '');
              if (phoneValue.length !== 9) {
                phoneValidation.classList.remove('hidden');
              } else {
                phoneValidation.classList.add('hidden');
              }
              if (phoneValue.charAt(0) === '0') {
                phoneZeroValidation.classList.remove('hidden');
              } else {
                phoneZeroValidation.classList.add('hidden');
              }
            });
          });
        </script>

        <!-- Departamento -->
        <div class="sm:col-span-3">
          <label for="department" class="block text-sm font-medium leading-6 text-gray-900">Departamento</label>
          <div class="mt-2">
            <select id="department" name="department" required
              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
              <option value="">Seleccione un departamento</option>
            </select>
            <span id="department-error" class="mt-2 text-sm text-red-500 hidden"></span>
          </div>
        </div>

        <!-- Ciudad -->
        <div class="sm:col-span-3">
          <label for="city" class="block text-sm font-medium leading-6 text-gray-900">Ciudad</label>
          <div class="mt-2">
            <select id="city" name="city" required disabled
              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
              <option value="">Seleccione una ciudad</option>
            </select>
            <span id="city-error" class="mt-2 text-sm text-red-500 hidden"></span>
          </div>
        </div>
      </div>
      <div class="border-t border-gray-900/10 pt-12 mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
        <div class="col-span-6">
          <h3 class="text-base font-semibold leading-7 text-gray-900">Información Institucional</h3>
          <p class="mt-1 text-sm leading-6 text-gray-600">Por favor, proporcione la información de su
            facultad/universidad/institución.</p>
        </div>

        <!-- Correo electrónico institucional -->
        <div class="col-span-6 sm:col-span-4">
          <label for="institutional-email" class="block text-sm font-medium leading-6 text-gray-900">Correo
            electrónico</label>
          <div class="mt-2">
            <input type="email" name="institutional-email" id="institutional-email" autocomplete="email" required
              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
            <span id="institutional-email-error" class="mt-2 text-sm text-red-500 hidden"></span>
          </div>
        </div>

        <!-- Nombre de institución -->
        <div class="sm:col-span-4">
          <label for="institution-name-search" class="block text-sm font-medium leading-6 text-gray-900">Nombre de institución</label>
          <div class="mt-2 relative">
            <input type="text" id="institution-name-search" name="institution-name" readonly
              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6 cursor-pointer"
              placeholder="Seleccione una institución..." required>
            <button type="button" id="clear-institution" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 hidden">
              <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
              </svg>
            </button>
            <div id="institution-name-dropdown"
              class="absolute z-10 w-full mt-1 bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm hidden">
            </div>
            <input type="hidden" id="institution-name" name="institution-name">
          </div>
          <span id="institution-name-error" class="mt-2 text-sm text-red-500 hidden"></span>
          <button id="show-all-institutions" type="button" class="mt-2 text-sm text-primary-600">Mostrar todas las instituciones</button>
          <button id="request-new-institution" type="button" class="mt-2 ml-2 text-sm text-primary-600">Solicitar nueva institución</button>
        </div>

        <!-- Modal for all institutions -->
        <div id="all-institutions-modal"
          class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
          <div class="bg-white rounded-lg max-w-4xl w-full h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="p-4 bg-gray-100">
              <div class="mt-2">
                <input type="text" id="modal-search-input" placeholder="Empieza a escribir el nombre de la institución..." 
                  class="w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
              </div>
            </div>
            <!-- Content -->
            <div class="flex-grow overflow-y-auto p-4">
              <div class="overflow-x-auto">
                <table class="table-auto w-full">
                  <!-- <thead class="sticky top-0 bg-gray-100">
                    <tr>
                      <th class="px-4 py-2 text-left">Nombre de la institución</th>
                    </tr>
                  </thead> -->
                  <tbody id="all-institutions-body" class="divide-y divide-gray-200">
                    <!-- Institutions will be populated here -->
                  </tbody>
                </table>
              </div>
            </div>
            <!-- Footer -->
            <div class="p-4 bg-gray-100">
              <div class="flex justify-end space-x-2">
                <button type="button" id="close-all-institutions-modal"
                  class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">Cerrar</button>
                <button type="button" id="select-institution"
                  class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition-colors">Seleccionar institución</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal para solicitar nueva institución -->
        <!-- <div id="new-institution-modal"
          class="fixed inset-0 bg-gray-500 bg-opacity-75 items-center justify-center hidden">
          <div class="bg-white p-6 rounded-lg">
            <h3 class="text-lg font-medium mb-4">Solicitar nueva institución</h3>
            <form id="new-institution-form">
              <input type="text" id="new-institution-name"
                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
                placeholder="Nombre de la institución">
              <button type="submit" class="mt-4 bg-primary-600 text-white px-4 py-2 rounded-md">Enviar
                solicitud</button>
              <button type="button" id="close-modal" class="mt-4 ml-2 text-gray-600">Cancelar</button>
            </form>
          </div>
        </div> -->

        <!-- Sede o Facultad -->
        <div class="col-span-6 sm:col-span-3 faculty-field hidden">
          <label for="campus-faculty" class="block text-sm font-medium leading-6 text-gray-900">Sede o Facultad</label>
          <div class="mt-2">
            <select id="campus-faculty" name="campus-faculty" required disabled
              class="block z-0 w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
              <option value="">Seleccione una facultad</option>
            </select>
            <span id="campus-faculty-error" class="mt-2 text-sm text-red-500 hidden"></span>
            <p class="mt-2 text-sm text-gray-500">Si no encuentra su sede o facultad, por favor seleccione "No aplica".</p>
          </div>
        </div>

        <!-- Unidad o Carrera específica -->
        <div class="col-span-6 sm:col-span-3 career-field hidden">
          <label for="specific-unit-career" class="block text-sm font-medium leading-6 text-gray-900">Unidad o Carrera específica</label>
          <div class="mt-2 relative">
            <input 
              type="text" 
              id="specific-unit-career" 
              name="specific-unit-career" 
              required 
              disabled
              list="career-options"
              autocomplete="off"
              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
              placeholder="Seleccione o escriba una carrera">
            <datalist id="career-options">
              <!-- Options will be populated dynamically -->
            </datalist>
            <span id="specific-unit-career-error" class="mt-2 text-sm text-red-500 hidden"></span>
          </div>
        </div>

        <!-- Rol dentro de la institución -->
        <div class="col-span-6 sm:col-span-3">
          <label for="institutional-role" class="block text-sm font-medium leading-6 text-gray-900">Rol dentro de la
            institución</label>
          <div class="mt-2">
            <select id="institutional-role" name="institutional-role" required
              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
              <option value="">Seleccione un rol</option>
              <option value="investigador">Investigador</option>
              <option value="investigador-pronii">Investigador PRONII</option>
              <option value="docente">Docente universitario</option>
              <option value="estudiante">Estudiante universitario</option>
              <option value="administrativo">Personal administrativo</option>
              <option value="tecnico">Personal técnico</option>
              <option value="consultor">Consultor/Asesor</option>
            </select>
            <span id="institutional-role-error" class="mt-2 text-sm text-red-500 hidden"></span>
          </div>
        </div>

        <!-- Inicio de la sección de información de investigadores -->
        <div class="col-span-6">
          <h3 class="text-base font-semibold leading-7 text-gray-900">Información de Investigadores</h3>
          <p class="mt-1 text-sm leading-6 text-gray-600">Por favor, proporcione la información de su actividad
            investigativa.</p>
        </div>

        <!-- Categoría en el PRONII -->
        <div class="col-span-6 sm:col-span-3">
          <label for="pronii-category" class="block text-sm font-medium leading-6 text-gray-900">Categoría en el
            PRONII</label>
          <div class="mt-2">
            <select id="pronii-category" name="pronii-category" required
              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
              <option value="ninguna">Ninguna</option>
              <option value="candidato">Candidato</option>
              <option value="nivel-i">Nivel I</option>
              <option value="nivel-ii">Nivel II</option>
              <option value="nivel-iii">Nivel III</option>
            </select>
            <span id="pronii-category-error" class="mt-2 text-sm text-red-500 hidden"></span>
          </div>
        </div>

        <!-- ID de ORCID -->
        <div class="col-span-6 sm:col-span-3">
          <label for="orcid-id" class="block text-sm font-medium leading-6 text-gray-900">ID de ORCID</label>
          <div class="mt-2">
            <input type="text" name="orcid-id" id="orcid-id" pattern="\d{4}-\d{4}-\d{4}-\d{3}[\dX]"
              title="Formato: 0000-0000-0000-0000"
              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6"
              placeholder="0000-0000-0000-0000">
            <span id="orcid-id-error" class="mt-2 text-sm text-red-500 hidden"></span>
          </div>
        </div>

        <!-- ID de Scopus -->
        <div class="col-span-6 sm:col-span-3">
          <label for="scopus-id" class="block text-sm font-medium leading-6 text-gray-900">ID de Scopus</label>
          <div class="mt-2">
            <input type="text" name="scopus-id" id="scopus-id" pattern="\d{10,11}"
              title="Ingrese un ID de Scopus válido"
              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
            <span id="scopus-id-error" class="mt-2 text-sm text-red-500 hidden"></span>
          </div>
        </div>

        <!-- ID de WoS -->
        <div class="col-span-6 sm:col-span-3">
          <label for="wos-id" class="block text-sm font-medium leading-6 text-gray-900">ID de WoS (Web of
            Science)</label>
          <div class="mt-2">
            <input type="text" name="wos-id" id="wos-id" pattern="[A-Z]-\d{4}-\d{4}" title="Formato: X-0000-0000"
              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
            <span id="wos-id-error" class="mt-2 text-sm text-red-500 hidden"></span>
          </div>
        </div>

        <!-- Investigadores: Mensaje de información -->
        <div id="hint-message" class="col-span-6 bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4"
          style="display: none;">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M9 9a1 1 0 112 0v5a1 1 0 11-2 0V9zM10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0 1 1 0 002 0z"
                  clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-blue-700">
                Como asumimos que no eres un investigador, hemos configurado tu categoría PRONII a 'Ninguna' y dejamos
                vacíos los IDs de ORCID, Scopus, y WoS.
              </p>
              <button id="show-fields" type="button" class="mt-2 text-sm font-medium text-blue-700 hover:text-blue-600">
                Sí, tengo esos datos.
              </button>
            </div>
          </div>
        </div>

        <script>
          document.getElementById('institutional-role').addEventListener('change', function () {
            var role = this.value;
            var proniiField = document.getElementById('pronii-category');
            var orcidField = document.getElementById('orcid-id');
            var scopusField = document.getElementById('scopus-id');
            var wosField = document.getElementById('wos-id');
            var message = document.getElementById('hint-message');
            var showFieldsButton = document.getElementById('show-fields');

            if (role === 'estudiante' || role === 'administrativo' || role === 'tecnico' || role === 'consultor') {
              proniiField.value = 'ninguna';
              orcidField.value = '';
              scopusField.value = '';
              wosField.value = '';

              // Hiding fields
              proniiField.parentElement.parentElement.style.display = 'none';
              orcidField.parentElement.parentElement.style.display = 'none';
              scopusField.parentElement.parentElement.style.display = 'none';
              wosField.parentElement.parentElement.style.display = 'none';

              message.style.display = 'block';
            } else {
              proniiField.parentElement.parentElement.style.display = '';
              orcidField.parentElement.parentElement.style.display = '';
              scopusField.parentElement.parentElement.style.display = '';
              wosField.parentElement.parentElement.style.display = '';
              message.style.display = 'none';
            }

            showFieldsButton.style.display = role === 'estudiante' || role === 'administrativo' || role === 'tecnico' || role === 'consultor' ? 'block' : 'none';
          });

          document.getElementById('show-fields').addEventListener('click', function () {
            var proniiField = document.getElementById('pronii-category');
            var orcidField = document.getElementById('orcid-id');
            var scopusField = document.getElementById('scopus-id');
            var wosField = document.getElementById('wos-id');
            var message = document.getElementById('hint-message');

            proniiField.parentElement.parentElement.style.display = '';
            orcidField.parentElement.parentElement.style.display = '';
            scopusField.parentElement.parentElement.style.display = '';
            wosField.parentElement.parentElement.style.display = '';
            message.style.display = 'none';
          });
        </script>

        <!-- Área principal de investigación -->
        <div class="col-span-6 sm:col-span-4">
          <label class="block text-sm font-medium leading-6 text-gray-900">Área principal de investigación</label>
          <div class="mt-2 space-y-2">
            <div class="flex items-center">
              <input id="ciencias_naturales" name="research-area" type="checkbox" value="ciencias_naturales"
                class="h-4 w-4 border-gray-300 text-primary-600 focus:ring-primary-600">
              <label for="ciencias_naturales" class="ml-3 block text-sm font-medium leading-6 text-gray-900">Ciencias
                Naturales</label>
            </div>
            <div class="flex items-center">
              <input id="ingenieria_tecnologia" name="research-area" type="checkbox" value="ingenieria_tecnologia"
                class="h-4 w-4 border-gray-300 text-primary-600 focus:ring-primary-600">
              <label for="ingenieria_tecnologia"
                class="ml-3 block text-sm font-medium leading-6 text-gray-900">Ingeniería
                y Tecnología</label>
            </div>
            <div class="flex items-center">
              <input id="ciencias_medicas_salud" name="research-area" type="checkbox" value="ciencias_medicas_salud"
                class="h-4 w-4 border-gray-300 text-primary-600 focus:ring-primary-600">
              <label for="ciencias_medicas_salud"
                class="ml-3 block text-sm font-medium leading-6 text-gray-900">Ciencias
                Médicas y de la Salud</label>
            </div>
            <div class="flex items-center">
              <input id="ciencias_agricolas_veterinarias" name="research-area" type="checkbox"
                value="ciencias_agricolas_veterinarias"
                class="h-4 w-4 border-gray-300 text-primary-600 focus:ring-primary-600">
              <label for="ciencias_agricolas_veterinarias"
                class="ml-3 block text-sm font-medium leading-6 text-gray-900">Ciencias Agrícolas y Veterinarias</label>
            </div>
            <div class="flex items-center">
              <input id="ciencias_sociales" name="research-area" type="checkbox" value="ciencias_sociales"
                class="h-4 w-4 border-gray-300 text-primary-600 focus:ring-primary-600">
              <label for="ciencias_sociales" class="ml-3 block text-sm font-medium leading-6 text-gray-900">Ciencias Sociales</label>
            </div>
            <div class="flex items-center">
              <input id="humanidades_artes" name="research-area" type="checkbox" value="humanidades_artes"
                class="h-4 w-4 border-gray-300 text-primary-600 focus:ring-primary-600">
              <label for="humanidades_artes" class="ml-3 block text-sm font-medium leading-6 text-gray-900">Humanidades
                y Artes</label>
            </div>
            <!-- <div class="flex items-center">
              <input id="otras-areas" name="research-area" type="checkbox" value="otras-areas"
                class="h-4 w-4 border-gray-300 text-primary-600 focus:ring-primary-600">
              <label for="otras-areas" class="ml-3 block text-sm font-medium leading-6 text-gray-900">Otras
                áreas</label>
            </div> -->
          </div>
          <span id="research-area-error" class="mt-2 text-sm text-red-500 hidden"></span>
        </div>
        
        <!-- CAPTCHA Challenge -->
        <div class="col-span-6 sm:col-span-4">
          <label for="captcha-input" class="block text-sm font-medium text-gray-700">CAPTCHA</label>
          <div class="mt-1 flex items-center">
            <img id="captcha-image" src="/solicitud_registro_usuario/lib/captcha.php" alt="CAPTCHA" class="mr-2">
            <input type="text" id="captcha-input" name="captcha" required
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
          </div>
          <button type="button" onclick="loadCaptcha()" class="mt-2 text-sm text-indigo-600 hover:text-indigo-500">
            Recargar CAPTCHA
          </button>
        </div>
      </div>
        
      <!-- Error message - Logic -->
      <div id="form-error-message"
        class="hidden bg-red-100 mt-10 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <strong class="font-bold">Hubo un error, contacte a soporte.</strong>
        <p class="block sm:inline" id="error-text"></p>
      </div>

      <!-- Success message - Logic -->
      <div id="form-success-message"
        class="hidden bg-green-100 mt-10 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
        role="alert">
        <strong class="font-bold">Envío exitoso</strong>
        <p class="block sm:inline" id="success-text"></p>
      </div>

      <!-- Submit button and loading indicator -->
      <div class="mt-6 flex items-center justify-end gap-x-6">
        <!-- Loading indicator -->
        <div id="loading-indicator" class="hidden flex items-center">
          <svg class="animate-spin h-5 w-5 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
          </svg>
          <span class="ml-2">Enviando...</span>
        </div>
        <!-- Submit button -->
        <button type="submit"
          class="rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
          Enviar solicitud de creación
        </button>
      </div>
</form>

<!-- Form Main Logic -->
<script src="https://cdn.jsdelivr.net/npm/fuse.js/dist/fuse.js"></script>
<script src="lib/form-submission.js"></script>
<script src="components/search-institutions.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Global variables
  let institutionData, geographicData, nationalityData;

  // Helper function to populate select elements
  const populateSelect = (elementId, options) => {
    const selectElement = document.getElementById(elementId);
    if (!selectElement) return;

    selectElement.innerHTML = '<option value="">Seleccione una opción</option>';
    options.forEach(option => {
      const optionElement = document.createElement('option');
      optionElement.value = option.value || option;
      optionElement.textContent = option.text || option;
      selectElement.appendChild(optionElement);
    });
  };

  // Load JSON data
  const loadJSON = async (url) => {
    try {
      const response = await fetch(url);
      return response.json();
    } catch (error) {
      console.error(`Error loading JSON from ${url}:`, error);
      return null;
    }
  };

  // Initialize form
  const initForm = async () => {
    try {
      [institutionData, geographicData, nationalityData] = await Promise.all([
        loadJSON('data/INSTITUCIONES_2023_NEW.json'),
        loadJSON('data/REGION_CIUDAD.json'),
        loadJSON('data/nacionalidades.json')
      ]);

      if (!institutionData || !geographicData || !nationalityData) {
        throw new Error('Failed to load one or more required data files');
      }

      // Initialize institution search
      const institutionSearch = new InstitutionSearch();
      institutionSearch.init(institutionData);

      // Initialize geographic selectors
      const departmentSelect = document.getElementById('department');
      const citySelect = document.getElementById('city');
      
      if (departmentSelect && citySelect && geographicData.pais) {
        // Populate departments
        departmentSelect.innerHTML = '<option value="">Seleccione un departamento</option>';
        geographicData.pais
          .sort((a, b) => a.nombre_region.localeCompare(b.nombre_region))
          .forEach(region => {
            const option = document.createElement('option');
            option.value = region.nombre_region;
            option.textContent = region.nombre_region;
            departmentSelect.appendChild(option);
          });

        // Handle department change
        departmentSelect.addEventListener('change', function() {
          const selectedDepartment = this.value;
          citySelect.innerHTML = '<option value="">Seleccione una ciudad</option>';
          citySelect.disabled = !selectedDepartment;

          if (selectedDepartment) {
            const selectedRegion = geographicData.pais.find(
              region => region.nombre_region === selectedDepartment
            );

            if (selectedRegion && selectedRegion.ciudades) {
              selectedRegion.ciudades
                .sort((a, b) => a.ciudad.localeCompare(b.ciudad))
                .forEach(cityData => {
                  const option = document.createElement('option');
                  option.value = cityData.ciudad;
                  option.textContent = cityData.ciudad;
                  citySelect.appendChild(option);
                });
            }
          }
        });
      }

      // Initialize nationality selector
      const nationalitySelect = document.getElementById('nationality');
      if (nationalitySelect && nationalityData.paises) {
        nationalitySelect.innerHTML = '<option value="">Seleccione un país</option>';
        nationalityData.paises
          .sort((a, b) => a.nombre.localeCompare(b.nombre))
          .forEach(pais => {
            const option = document.createElement('option');
            option.value = pais.datos[0].masculino;
            option.textContent = pais.nombre;
            nationalitySelect.appendChild(option);
          });
      }

      handleInstitutionSelection();

    } catch (error) {
      console.error('Error initializing form:', error);
    }
  };

  // Initialize the form
  initForm();
});
</script>

<style>
  /* Normalizar apariencia del datalist */
  input[list]::-webkit-calendar-picker-indicator {
    color: transparent;
    background: transparent;
    cursor: pointer;
    height: 100%;
    position: absolute;
    right: 0;
    top: 0;
    width: 2.5rem;
    z-index: 1;
  }

  input[list] {
    padding-right: 2.5rem; /* Espacio para el icono */
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236B7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
  }

  /* Ocultar el icono nativo en Firefox */
  input[list]::-moz-list-bullet {
    display: none;
  }
</style>