<?php
/**
 * Plugin Name: Logros por Categoría de Contenido (GamiPress Integrado)
 * Description: Asigna logros GamiPress automáticamente a los usuarios al interactuar con contenido (respuestas, comentarios, publicaciones) N veces en categorías específicas de AnsPress o WordPress, utilizando Action Scheduler para un procesamiento robusto.
 * Version: 2.5.1
 * Author: Alejandro Chipana (Refactorizado por Gemini)
 * Requires Plugins: gamipress
 * Requires at least: 5.0
 * Tested up to: 6.5
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) exit; // Previene el acceso directo al archivo

// =================================================================================================
// 1. Constantes del Plugin
// =================================================================================================

define('LCR_VERSION', '2.5.1');
define('LCR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LCR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LCR_DB_TABLE', 'lcr_categoria_logro');
define('LCR_ACTION_HOOK', 'lcr_process_achievement_task'); // Hook para Action Scheduler

// =================================================================================================
// 2. Funciones de Activación y Desactivación
// =================================================================================================

/**
 * Hook de activación del plugin. Crea o actualiza la tabla de la base de datos.
 */
register_activation_hook(__FILE__, 'lcr_activate_plugin');
function lcr_activate_plugin() {
    global $wpdb;
    $tabla = $wpdb->prefix . LCR_DB_TABLE;
    $charset_collate = $wpdb->get_charset_collate();

    error_log('INFO: [LCR] Hook de activación del plugin disparado. Comprobando/creando tabla: ' . $tabla);

    $sql = "CREATE TABLE IF NOT EXISTS $tabla (
        id INT AUTO_INCREMENT PRIMARY KEY,
        parent_post_type VARCHAR(191) NOT NULL,
        taxonomy_slug VARCHAR(191) NOT NULL,
        term_slug VARCHAR(191) NOT NULL,
        logro_id BIGINT UNSIGNED NOT NULL,
        cantidad INT NOT NULL DEFAULT 1,
        UNIQUE KEY unique_relation (parent_post_type, taxonomy_slug, term_slug, logro_id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    if ($wpdb->last_error) {
        error_log('ERROR: [LCR] Fallo en la creación/actualización de la tabla de la base de datos: ' . $wpdb->last_error);
    } else {
        error_log('INFO: [LCR] Tabla ' . $tabla . ' comprobada/creada exitosamente.');
    }
}

/**
 * Hook de desactivación del plugin.
 * Opcional: Podrías limpiar tareas programadas de Action Scheduler aquí.
 */
register_deactivation_hook(__FILE__, 'lcr_deactivate_plugin');
function lcr_deactivate_plugin() {
    // Cancelar todas las tareas pendientes de nuestro grupo
    if (function_exists('as_unschedule_all_actions')) {
        as_unschedule_all_actions(LCR_ACTION_HOOK, [], 'lcr_group_achievements');
        error_log('INFO: [LCR] Tareas de Action Scheduler canceladas durante la desactivación.');
    }
}

// =================================================================================================
// 3. Carga de Archivos y Clases del Plugin
// =================================================================================================

/**
 * Función para cargar todos los componentes del plugin.
 * Se asegura de que GamiPress esté activo antes de cargar la lógica principal.
 */
function lcr_load_plugin_components() {
    // Cargar funciones auxiliares primero, ya que pueden ser usadas por otras clases.
    require_once LCR_PLUGIN_DIR . 'includes/lcr-helpers.php';

    // Comprobar si GamiPress está activo
    if (class_exists('GamiPress')) {
        // Cargar la clase principal de lógica de logros
        require_once LCR_PLUGIN_DIR . 'includes/class-lcr-core.php';
        new LCR_Core(); // Instanciar la clase para que sus hooks se registren

        // Cargar los escuchadores de eventos (hooks de WordPress/AnsPress)
        require_once LCR_PLUGIN_DIR . 'includes/lcr-event-listeners.php';

    } else {
        // Mostrar un aviso si GamiPress no está activo
        add_action('admin_notices', 'lcr_gamipress_missing_notice');
    }

    // Cargar la clase del panel de administración (siempre, independientemente de GamiPress,
    // para que el usuario pueda configurar reglas incluso si GamiPress está desactivado temporalmente)
    if (is_admin()) {
        require_once LCR_PLUGIN_DIR . 'admin/class-lcr-admin.php';
        new LCR_Admin(); // Instanciar la clase del admin
    }
}
add_action('plugins_loaded', 'lcr_load_plugin_components');

/**
 * Aviso de administración si GamiPress no está activo.
 */
function lcr_gamipress_missing_notice() {
    ?>
    <div class="notice notice-warning is-dismissible">
        <p><strong>Logros por Categoría de Contenido</strong> requiere que el plugin <strong>GamiPress</strong> esté instalado y activo para funcionar correctamente. Por favor, instale y active GamiPress.</p>
    </div>
    <?php
}