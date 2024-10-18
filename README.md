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
┣ 📂dist // carpeta generada al momento del despliege
┣ 📂img
┣ 📂lib
┃ ┣ 📜form_integrate_ldap.php
┃ ┣ 📜form-submission.js
┃ ┣ 📜Formulario2023_NEW.html
┃ ┗ 📜procesar_ingreso_2023_NEW.php
┣ 📜index.php
┣ 📜input.css
┣ 📜package.json
┣ 📜tailwind.config.js
┣ 📜build.js
```

## Instalación y Configuración Local

1. Clona este repositorio:

   ```
   git clone [URL del repositorio]
   ```

2. Instala las dependencias:

   ```
   npm install
   ```

3. Para desarrollo, ejecuta:

   ```
   npm run dev
   ```

4. Para construir el proyecto:
   ```
   npm run build
   ```

## Despliegue

El contenido de la carpeta `/dist` se sube al servidor en la ruta:

```
https://cicco.conacyt.gov.py/solicitud_registro_usuario/
```

## Problemas Conocidos y Tareas Pendientes

Actualmente, hay un problema con el envío del formulario. El servidor está devolviendo una página HTML de error en lugar de procesar los datos correctamente.

### Para el Desarrollador:

1. Revisar `components/form-front.php` y `lib/form-submission.js` para asegurarse de que está manejando correctamente los datos del formulario.
2. Verificar que la validación del CAPTCHA esté funcionando correctamente.
3. Comprobar que las cookies para 'Organizacion' y 'Facultad' se estén leyendo y utilizando adecuadamente.
4. Considerar devolver respuestas JSON en lugar de HTML para un mejor manejo de errores en el lado del cliente.

### Posibles Soluciones:

1. Implementar un registro detallado (logging) en el servidor para identificar dónde falla el procesamiento.
2. Asegurar que todos los campos requeridos se estén enviando correctamente desde el cliente.
3. Verificar la compatibilidad entre los datos enviados por el cliente y los esperados por el servidor.
4. Potencialmente puede ser referente al LDAP, el cual no tengo conocimientos de como integrar.

## Contacto

Para cualquier consulta o reporte de problemas, por favor contactar a ayuda@janpark.net.
