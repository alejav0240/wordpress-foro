# Página de Investigaciones ISI - Univalle

![WordPress](https://img.shields.io/badge/WordPress-6.x-blue?style=for-the-badge&logo=wordpress) ![PHP](https://img.shields.io/badge/PHP-7.4%2B-8892BF?style=for-the-badge&logo=php) ![MySQL](https://img.shields.io/badge/MySQL-5.6%2B-00758F?style=for-the-badge&logo=mysql) ![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge) 

Este proyecto es una plataforma web robusta, construida sobre WordPress, diseñada específicamente para la **difusión y gestión de investigaciones científicas** de la carrera de Ingeniería de Sistemas en Univalle. Su misión principal es establecer un espacio centralizado para la publicación de proyectos y artículos científicos generados por la comunidad universitaria, integrando un innovador sistema de gamificación para reconocer y fomentar la participación activa y la excelencia académica.

## ✨ Características Principales

*   **Plataforma de Divulgación Científica:** 📚 Permite a estudiantes, docentes e investigadores publicar y gestionar sus proyectos y artículos científicos de forma sencilla.
*   **Sistema de Gamificación Integrado:** 🏆 Incorpora GamiPress para otorgar puntos, logros y rangos, incentivando la interacción, la calidad de las contribuciones y el reconocimiento de la comunidad.
*   **Perfiles de Usuario Personalizados:** 👤 Gracias a Ultimate Member, cada usuario dispone de un perfil detallado para gestionar sus publicaciones, preguntas, respuestas y logros personales.
*   **Funcionalidad de Preguntas y Respuestas (Q&A):** 💬 Utiliza Anspress para facilitar un entorno de foro o Q&A, promoviendo la discusión y colaboración sobre los contenidos publicados.
*   **Categorización Específica de Univalle:** 🏷️ Organización del contenido mediante categorías personalizadas y un plugin importador para alinearse con la estructura académica de la universidad.
*   **Diseño Moderno y Adaptable:** 📱 Basado en el tema Astra y su tema hijo personalizado, garantiza una experiencia de usuario óptima y responsiva en cualquier dispositivo.
*   **Formularios Dinámicos:** 📝 Integración de Forminator para la creación de formularios avanzados, útiles para la presentación de proyectos o encuestas.

## 🚀 Requisitos Previos

Para desplegar y ejecutar este proyecto, necesitarás un entorno que cumpla con los siguientes requisitos:

*   **Servidor Web:** Apache o Nginx.
*   **PHP:** Versión 7.4 o superior (se recomienda PHP 8.x para un mejor rendimiento y seguridad).
*   **Base de Datos:** MySQL 5.6 o superior / MariaDB 10.1 o superior.
*   **WordPress:** Versión 6.x o superior.
*   **Composer:** (Opcional, pero recomendado) Para la gestión de dependencias de algunos plugins si fuera necesario.

## 💻 Instalación

Sigue estos pasos para configurar el proyecto en tu entorno local o servidor:

1.  **Clonar el Repositorio:**
    ```bash
    git clone https://github.com/tu-usuario/nombre-del-repositorio.git
    cd nombre-del-repositorio # Asume el nombre de tu directorio de proyecto
    ```
    *Si no usas Git, simplemente copia todos los archivos del proyecto al directorio raíz de tu servidor web (ej. `htdocs` para Apache, `www` para Nginx).*

2.  **Configurar la Base de Datos:**
    *   Crea una nueva base de datos MySQL (ej. `univalle_isi_db`).
    *   Importa el archivo SQL proporcionado. Utiliza `db/db.sql` para producción o `db/local.sql` para desarrollo.
        ```bash
        mysql -u tu_usuario -p tu_nombre_de_base_de_datos < db/db.sql
        ```
        *(Reemplaza `tu_usuario`, `tu_nombre_de_base_de_datos` y `db/db.sql` con tus valores).*

3.  **Configurar WordPress (wp-config.php):**
    *   Copia el archivo `wp-config-sample.php` y renómbralo a `wp-config.php`.
    *   Edita `wp-config.php` y actualiza los detalles de tu base de datos:
        ```php
        define( 'DB_NAME', 'tu_nombre_de_base_de_datos' );
        define( 'DB_USER', 'tu_usuario_de_base_de_datos' );
        define( 'DB_PASSWORD', 'tu_contraseña_de_base_de_datos' );
        define( 'DB_HOST', 'localhost' );
        // ... otras configuraciones
        ```
    *   Genera y actualiza las claves de seguridad (`AUTH_KEY`, `SECURE_AUTH_KEY`, etc.) con valores únicos desde [WordPress API](https://api.wordpress.org/secret-key/1.1/salt/).
    *   **Importante:** Asegúrate de que las URLs del sitio en la base de datos (`wp_options` tabla, `siteurl` y `home`) coincidan con la URL donde alojarás el proyecto. Si es una migración, considera usar herramientas como WP-CLI (`wp search-replace`) o plugins de migración.

4.  **Establecer Permisos de Archivos:**
    *   Asegúrate de que el directorio `wp-content/uploads` tenga permisos de escritura adecuados (comúnmente `755` o `775`, dependiendo de la configuración de tu servidor web).

5.  **Acceder al Sitio:**
    *   Una vez completada la configuración, accede a tu sitio web a través de la URL que hayas definido (ej. `http://localhost/nombre-del-proyecto/`).
    *   Para el panel de administración, navega a `tu_url_del_sitio/wp-admin`.

## 🛠️ Guía de Uso

### 📝 Publicación de Proyectos y Artículos

1.  Inicia sesión en el panel de administración de WordPress (`tu_url_del_sitio/wp-admin`).
2.  Navega a `Entradas` (o `Proyectos`, si se ha implementado un tipo de post personalizado) -> `Añadir nueva`.
3.  Crea tu contenido, asigna categorías y etiquetas relevantes de Univalle.
4.  Haz clic en `Publicar` para que tu investigación sea visible en la plataforma.

### 🏆 Módulo de Gamificación (GamiPress)

*   **Configuración:** El plugin GamiPress se gestiona desde el menú `GamiPress` en el panel de administración. Aquí puedes definir tipos de puntos, logros, rangos y los pasos para obtenerlos.
*   **Logros Personalizados:** El plugin `taxonomia-logro` permite crear taxonomías específicas para categorizar logros. Asegúrate de vincular estos logros con los eventos de GamiPress (ej., "Publicar 5 artículos", "Recibir 10 votos positivos").
*   **Interacción:** Los usuarios acumularán puntos y desbloquearán logros al realizar acciones específicas en el sitio, fomentando la participación activa.

### 💬 Interacción (Preguntas y Respuestas con Anspress)

*   **Funcionalidad Q&A:** Anspress proporciona un sistema completo de preguntas y respuestas. Los usuarios pueden formular preguntas, responder a otras y votar por el contenido más útil.
*   **Gestión Personal:** Explora las páginas `Mis Preguntas` y `Mis Respuestas` (accesibles desde el perfil de usuario) para gestionar tu actividad en el foro.

### 👤 Gestión de Perfiles de Usuario (Ultimate Member)

*   **Perfiles Detallados:** Ultimate Member permite una gestión robusta de perfiles de usuario. Los usuarios pueden personalizar su información, foto de perfil y ver sus actividades desde la página `Mi Perfil`.
*   **Roles y Permisos:** Los administradores pueden gestionar usuarios, roles y permisos detallados desde el panel de administración de WordPress, adaptando la experiencia a las necesidades de Univalle.

## 📂 Estructura del Proyecto

La estructura del proyecto sigue la convención estándar de WordPress, con directorios clave para la configuración, la base de datos, los temas y los plugins:
```
nombre-del-repositorio/
├── conf/                     # Archivos de configuración adicionales (si aplica)
├── db/                       # Archivos de la base de datos
│   ├── db.sql                # Base de datos para entornos de producción
│   └── local.sql             # Base de datos para entornos de desarrollo
├── wp-admin/                 # Archivos del panel de administración de WordPress
├── wp-content/               # Contiene temas, plugins y subidas de medios
│   ├── languages/            # Archivos de idioma de WordPress
│   ├── plugins/              # Plugins instalados y personalizados
│   │   ├── anspress-question-answer/ # Sistema de Preguntas y Respuestas
│   │   ├── forminator/               # Constructor de formularios
│   │   ├── gamipress/                # Módulo de gamificación
│   │   ├── importador-categorias-univalle/ # Plugin personalizado para categorías
│   │   ├── svg-support/              # Soporte para archivos SVG
│   │   ├── taxonomia-logro/          # Plugin personalizado para taxonomía de logros
│   │   ├── ultimate-addons-for-gutenberg/ # Mejoras para el editor Gutenberg
│   │   ├── ultimate-member/          # Gestión avanzada de perfiles de usuario
│   │   ├── wp-crontrol/              # Control de tareas cron de WordPress
│   │   ├── wp-smushit/               # Optimización de imágenes
│   │   └── wpdiscuz/                 # Sistema de comentarios avanzado
│   ├── themes/               # Temas instalados
│   │   ├── astra/            # Tema base principal
│   │   └── astra-child/      # Tema hijo con personalizaciones de Univalle
│   │       ├── functions.php       # Funciones personalizadas del tema hijo
│   │       ├── page-mis-publicaciones.php # Plantilla para "Mis Publicaciones"
│   │       ├── page-perfil-ui.php  # Plantilla para la interfaz de perfil
│   │       └── style.css           # Estilos personalizados del tema hijo
│   └── uploads/              # Archivos subidos por los usuarios (imágenes, documentos)
├── wp-includes/              # Archivos principales del core de WordPress
├── wp-config.php             # Archivo de configuración principal de WordPress
└── ... (otros archivos y directorios estándar de WordPress)
```

## 🌐 Tecnologías Utilizadas

*   **WordPress:** El CMS líder mundial para la gestión de contenido.
*   **PHP:** El lenguaje de programación principal del backend.
*   **MySQL:** El sistema de gestión de bases de datos relacional.
*   **HTML5 / CSS3 / JavaScript:** Tecnologías fundamentales para el desarrollo frontend.
*   **Tema Astra:** Un tema base de WordPress ligero y altamente personalizable.
*   **Plugins de WordPress:**
    *   **GamiPress:** Para la gamificación.
    *   **Anspress Question & Answer:** Para el sistema de preguntas y respuestas.
    *   **Ultimate Member:** Para la gestión de perfiles y comunidad.
    *   **Forminator:** Para la creación de formularios.
    *   **Ultimate Addons for Gutenberg:** Para mejorar el editor de bloques.
    *   **wpDiscuz:** Para un sistema de comentarios avanzado.
    *   **SVG Support:** Para la gestión de gráficos vectoriales escalables.
    *   **WP Smush It:** Para la optimización de imágenes.
    *   **WP Crontrol:** Para la gestión de tareas cron.
    *   **Plugins Personalizados:** `importador-categorias-univalle` y `taxonomia-logro`.