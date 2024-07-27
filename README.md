# FarmaCheck

FarmaCheck es una aplicación web que permite a las farmacias evaluar su estado actual y recibir recomendaciones personalizadas para mejorar sus operaciones y servicios. 

## Características

- Cuestionario interactivo de 20 preguntas para evaluar el estado de la farmacia.
- Diagnóstico dividido en 4 bloques expandibles y colapsables.
- Indicador de nivel actual de la farmacia.
- Identificación de áreas importantes basadas en las respuestas del cuestionario.
- Consejos y recomendaciones personalizadas.
- Formulario de registro para recibir más información y asistir a un webinario.

## Requisitos

- PHP 7.4 o superior
- MySQL
- Composer
- Servidor web (Apache, Nginx, etc.)

## Instalación

1. Clona el repositorio en tu máquina local:

    ```bash
    git clone https://github.com/tu-usuario/farmacheck.git
    ```

2. Navega al directorio del proyecto:

    ```bash
    cd farmacheck
    ```

3. Instala las dependencias de PHP utilizando Composer:

    ```bash
    composer install
    ```

4. Configura la base de datos MySQL:

    - Crea una base de datos llamada `farmacheck`.
    - Importa el esquema de la base de datos desde el archivo `schema.sql` (asegúrate de tener este archivo con la estructura de tus tablas).

    ```bash
    mysql -u root -p farmacheck < schema.sql
    ```

5. Configura las credenciales de la base de datos en los archivos PHP:

    Asegúrate de que los archivos PHP (`pregunta.php`, `diagnostico.php`, etc.) contengan las credenciales correctas para conectarse a la base de datos:

    ```php
    $mysqli = new mysqli('localhost', 'root', '', 'farmacheck');
    ```

6. Coloca los archivos en el directorio público de tu servidor web (por ejemplo, `htdocs` para XAMPP).

## Uso

1. Abre tu navegador web y navega a la URL donde está alojada la aplicación (por ejemplo, `http://localhost/farmacheck`).

2. Completa el cuestionario interactivo.

3. Al finalizar el cuestionario, se mostrará el diagnóstico con recomendaciones personalizadas.

## Estructura del Proyecto

- `index.html`: Página de inicio de la aplicación.
- `pregunta.php`: Página de las preguntas del cuestionario.
- `diagnostico.php`: Página de diagnóstico que muestra los resultados y recomendaciones.
- `src/`: Directorio que contiene los archivos de estilo CSS y las imágenes necesarias.
  - `css/`: Archivos CSS.
  - `images/`: Imágenes utilizadas en la aplicación.
- `schema.sql`: Archivo SQL con la estructura de la base de datos.

## Capturas de Pantalla

### Página de Inicio
![Página de Inicio](path/to/screenshot.png)

### Página de Preguntas
![Página de Preguntas](path/to/screenshot2.png)

### Página de Diagnóstico
![Página de Diagnóstico](path/to/screenshot3.png)

## Contribuciones

Las contribuciones son bienvenidas. Si deseas contribuir a este proyecto, por favor abre un issue o crea un pull request.

## Licencia

Este proyecto está licenciado bajo la [MIT License](LICENSE).
