<?php
/**
 * Plugin Name: Mi GamiPress Integrador
 * Description: Un plugin de ejemplo que interactúa con GamiPress para otorgar logros y mostrar información.
 * Version: 1.0
 * Author: Tu Nombre
 * Text Domain: mi-gamipress-integrador
 */

// Evitar acceso directo al archivo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Función para inicializar el plugin.
 * Se asegura de que GamiPress esté activo antes de cargar nuestra lógica.
 */
function mi_gamipress_integrador_init() {
    // Comprobar si GamiPress está activo
    if ( class_exists( 'GamiPress' ) ) {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-gamipress-handler.php';
        new Mi_GamiPress_Integrador_Handler();
    } else {
        // Opcional: Mostrar un aviso si GamiPress no está activo
        add_action( 'admin_notices', 'mi_gamipress_integrador_admin_notice' );
    }
}
add_action( 'plugins_loaded', 'mi_gamipress_integrador_init' );

/**
 * Aviso de administración si GamiPress no está activo.
 */
function mi_gamipress_integrador_admin_notice() {
    ?>
    <div class="notice notice-warning is-dismissible">
        <p><strong>Mi GamiPress Integrador</strong> requiere que el plugin <strong>GamiPress</strong> esté instalado y activo para funcionar correctamente.</p>
    </div>
    <?php
}

// Opcional: Funciones de activación/desactivación
register_activation_hook( __FILE__, 'mi_gamipress_integrador_activate' );
function mi_gamipress_integrador_activate() {
    // Puedes añadir lógica de activación aquí si la necesitas
}