# SISTEMA DATA LAN

### Instalar dependencias de Laravel
En la raíz de tu aplicación Laravel, ejecuta el comando ``php composer.phar install`` (o ``composer install``) para instalar todas las dependencias del framework.

### Migrar las tablas

Para migrar las tablas y configurar la estructura mínima necesaria para que esta aplicación muestre algunos datos, debes abrir tu terminal, localizar y entrar en el directorio de este proyecto y ejecutar el siguiente comando:

``php artisan migrate``

### Generar datos de prueba

Una vez que tengas todas las tablas de tu base de datos configuradas, puedes generar datos de prueba utilizando los seeders predefinidos de la base de datos. Para hacerlo, ejecuta el siguiente comando en tu terminal:

``php artisan db:seed``


### Compilar el front-end

Para compilar todos los archivos CSS y JS del front-end de este sitio, se necesita instalar las dependencias de NPM. Para ello, abre la terminal, escribe npm install y presiona la tecla ``Enter``.

Luego, ejecuta ``npm run dev`` en la terminal para iniciar un servidor de desarrollo que recompilará los recursos estáticos al realizar cambios en la plantilla.

Cuando hayas terminado los cambios, ejecuta ``npm run build`` para compilar y minificar los archivos para producción.

### Iniciar el backend de Laravel

Para que esta instalación de Laravel funcione correctamente en tu máquina local, puedes ejecutar el siguiente comando en tu ventana de terminal:

``php artisan serve``

Deberías recibir un mensaje similar a este:
``Starting Laravel development server: http://127.0.0.1:8000`` simplemente copia la URL en tu navegador y estarás listo para probar tu nueva aplicación Laravel.
