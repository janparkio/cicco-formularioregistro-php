# Formulario de Registro de Usuario - CICCO Conacyt

Este proyecto contiene el código fuente para el formulario de registro de usuario de CICCO (Consejo Nacional de Ciencia y Tecnología de Paraguay).

## Descripción

El formulario de registro permite a los usuarios crear una cuenta en el sistema CICCO. Utiliza HTML, PHP, JavaScript y Tailwind CSS para la interfaz y funcionalidad.

## Estructura del Proyecto

```
┣ 📂components
┃ ┗ 📜form-front.php
┣ 📂data
┃ ┣ 📜INSTITUCIONES_2023_NEW.json
┃ ┣ 📜nacionalidades.json
┃ ┗ 📜REGION_CIUDAD.json
┣ 📂dist           // carpeta generada al momento del despliegue
┣ 📂img
┣ 📂lib
┃ ┣ 📜form_integrate_ldap.php
┃ ┣ 📜form-submission.js
┃ ┣ 📜RegistrationLogger.php
┃ ┗ 📜procesar_ingreso_2023_NEW.php
┣ 📂logs          // carpeta para almacenar registros
┃ ┣ 📜attempts.json
┃ ┗ 📜registrations.log
┣ 📂pages         // páginas adicionales
┃ ┣ 📜registration_stats.php
┃ ┗ 📜register_success.php
┣ 📜index.php
┣ 📜input.css
┣ 📜package.json
┣ 📜tailwind.config.js
┣ 📜build.js
```

## Instalación y Configuración Local

1. Clona este repositorio:
   ```bash
   git clone [URL del repositorio]
   ```

2. Instala las dependencias:
   ```bash
   npm install
   ```

3. Crea la carpeta logs y asegura los permisos:
   ```bash
   mkdir logs
   chmod 755 logs
   ```

4. Para desarrollo, ejecuta:
   ```bash
   npm run dev
   ```

5. Para construir el proyecto:
   ```bash
   npm run build
   ```

## Estructura de URLs

El sistema implementa una estructura de URLs limpia:

- `/` - Formulario principal
- `/registration_stats` - Estadísticas de registro (requiere autenticación)
- `/register_success` - Página de éxito de registro

## Despliegue

1. Ejecuta el build del proyecto:
   ```bash
   npm run build
   ```

2. El contenido de la carpeta `/dist` se sube al servidor en la ruta:
   ```
   https://cicco.conacyt.gov.py/solicitud_registro_usuario/
   ```

3. Asegúrate de que la carpeta `logs` en el servidor tenga los permisos correctos:
   ```bash
   chmod 755 logs
   ```

## Sistema de Logs

El sistema ahora incluye un registro detallado de intentos de registro:

- `logs/attempts.json` - Registro de todos los intentos de registro
- `logs/registrations.log` - Log detallado del sistema

Para acceder a las estadísticas, visita `/registration_stats` con las credenciales proporcionadas.

## Características Nuevas

- Sistema de routing limpio para URLs más amigables
- Sistema de logging mejorado para seguimiento de registros
- Página de estadísticas con autenticación básica
- Mejor manejo de errores y feedback al usuario
- Optimización de assets y build process

## Problemas Conocidos y Soluciones

### Resueltos:
- ✅ Estructura de URLs mejorada
- ✅ Sistema de logs implementado
- ✅ Manejo de assets optimizado
- ✅ Optimización del manejo de CAPTCHA

### Pendientes:
1. Integración con LDAP [A confirmar]

## Seguridad

- Las credenciales para `/registration_stats` deben ser cambiadas en producción
- Los archivos de log están protegidos fuera del directorio público
- Se implementa autenticación básica para acceso a estadísticas

## Mantenimiento

Para actualizar el sistema:
1. Realizar cambios en el código fuente
2. Ejecutar `npm run build`
3. Subir solo el contenido de la carpeta `/dist`
4. Verificar permisos de carpetas y archivos en el servidor

## Contacto

Para cualquier consulta o reporte de problemas, por favor contactar a ayuda@janpark.net.

## Contribución

1. Crear un branch para la feature: `git checkout -b feature/nueva-caracteristica`
2. Commit de cambios: `git commit -am 'feat: agregar nueva caracteristica'`
3. Push al branch: `git push origin feature/nueva-caracteristica`
4. Crear Pull Request