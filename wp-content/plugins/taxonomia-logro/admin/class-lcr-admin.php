<?php
// admin/class-lcr-admin.php

if (!defined('ABSPATH')) exit;

class LCR_Admin {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    /**
     * Añade la página del menú de administración.
     */
    public function add_admin_menu_page() {
        add_menu_page(
            'Logros por Categoría',
            'Logros por Categoría',
            'manage_options',
            'lcr_admin',
            array($this, 'render_admin_page'),
            'dashicons-awards',
            30
        );
        error_log('INFO: [LCR] Página del menú de administración "Logros por Categoría" añadida.');
    }

    /**
     * Renderiza la página de administración del plugin.
     */
    public function render_admin_page() {
        global $wpdb;
        $tabla = $wpdb->prefix . LCR_DB_TABLE;

        $editing_rule = null;
        $edit_id = isset($_GET['edit_id']) ? absint($_GET['edit_id']) : 0;
        if ($edit_id) {
            $editing_rule = lcr_get_achievement_rule_by_id($edit_id); // Usar función auxiliar
            error_log("INFO: [LCR] Admin page: Intentando editar la regla con ID: $edit_id. Datos: " . print_r($editing_rule, true));
        }

        if (isset($_POST['lcr_submit_relation']) && check_admin_referer('lcr_guardar_relacion')) {
            error_log("INFO: [LCR] Admin page: Formulario enviado para guardar/actualizar relación.");

            $parent_post_type = sanitize_text_field($_POST['parent_post_type']);
            $taxonomy_slug    = sanitize_text_field($_POST['taxonomy_slug']);
            $term_slug        = sanitize_text_field($_POST['term_slug']);
            $logro_id         = absint($_POST['logro_id']);
            $cantidad         = absint($_POST['cantidad']);
            $submitted_edit_id = isset($_POST['edit_id']) ? absint($_POST['edit_id']) : 0;

            error_log("DEBUG: [LCR] Admin page: Datos enviados - Tipo de publicación: $parent_post_type, Taxonomía: $taxonomy_slug, Término: $term_slug, Logro ID: $logro_id, Cantidad: $cantidad, Edit ID: $submitted_edit_id");

            if ($parent_post_type && $taxonomy_slug && $term_slug && $logro_id && $cantidad > 0) {
                $data = [
                    'parent_post_type' => $parent_post_type,
                    'taxonomy_slug'    => $taxonomy_slug,
                    'term_slug'        => $term_slug,
                    'logro_id'         => $logro_id,
                    'cantidad'         => $cantidad,
                ];
                $format = ['%s', '%s', '%s', '%d', '%d'];

                if ($submitted_edit_id && $editing_rule) {
                    error_log("INFO: [LCR] Admin page: Intentando ACTUALIZAR relación con ID: $submitted_edit_id.");
                    $result = $wpdb->update($tabla, $data, ['id' => $submitted_edit_id], $format, ['%d']);
                    if ($result !== false) {
                        echo '<div class="notice notice-success is-dismissible"><p>Relación actualizada con éxito.</p></div>';
                        error_log("SUCCESS: [LCR] Admin page: Relación ID $submitted_edit_id actualizada exitosamente. Filas afectadas: $result.");
                        $editing_rule = null; // Limpiar el modo edición
                    } else {
                        echo '<div class="notice notice-error is-dismissible"><p>Error al actualizar la relación o no se realizaron cambios. Asegúrese de que no haya una relación idéntica ya existente.</p></div>';
                        error_log("ERROR: [LCR] Admin page: Fallo al actualizar la relación ID $submitted_edit_id. Error DB: " . $wpdb->last_error);
                    }
                } else {
                    error_log("INFO: [LCR] Admin page: Intentando INSERTAR nueva relación.");
                    $result = $wpdb->insert($tabla, $data, $format);
                    if ($result) {
                        echo '<div class="notice notice-success is-dismissible"><p>Relación guardada con éxito.</p></div>';
                        error_log("SUCCESS: [LCR] Admin page: Nueva relación insertada exitosamente. ID insertado: " . $wpdb->insert_id);
                    } else {
                        echo '<div class="notice notice-error is-dismissible"><p>Error al guardar la relación. Puede que ya exista una relación idéntica (tipo de publicación, taxonomía, término, logro).</p></div>';
                        error_log("ERROR: [LCR] Admin page: Fallo al insertar nueva relación. Error DB: " . $wpdb->last_error);
                    }
                }
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>Por favor, complete todos los campos requeridos y asegúrese de que la cantidad sea mayor que cero.</p></div>';
                error_log("WARNING: [LCR] Admin page: Envío de formulario fallido debido a datos incompletos o inválidos.");
            }
        }

        if (isset($_GET['eliminar']) && !empty($_GET['eliminar'])) {
            $delete_id = absint($_GET['eliminar']);
            error_log("INFO: [LCR] Admin page: Intentando ELIMINAR relación con ID: $delete_id.");
            $deleted = $wpdb->delete($tabla, ['id' => $delete_id]);
            if ($deleted) {
                echo '<div class="notice notice-warning is-dismissible"><p>Relación eliminada.</p></div>';
                error_log("SUCCESS: [LCR] Admin page: Relación ID $delete_id eliminada exitosamente. Filas afectadas: $deleted.");
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>Error al eliminar la relación.</p></div>';
                error_log("ERROR: [LCR] Admin page: Fallo al eliminar la relación ID $delete_id. Error DB: " . $wpdb->last_error);
            }
        }

        $all_post_types = [
            'post'     => 'Publicaciones (WordPress)',
            'question' => 'Preguntas (AnsPress)',
            'answer'   => 'Respuestas (AnsPress)',
        ];
        $achievement_types = function_exists('gamipress_get_achievement_types') ? gamipress_get_achievement_types() : [];
        $all_relations = lcr_get_achievement_rules(); // Usar función auxiliar

        echo '<div class="wrap"><h1>Configuración de Logros por Categoría de Contenido</h1>';

        echo '<h2>' . ($editing_rule ? 'Editar Relación Existente' : 'Añadir Nueva Relación') . '</h2>';
        echo '<form method="post" id="lcr-admin-form">';
        wp_nonce_field('lcr_guardar_relacion');
        echo '<input type="hidden" name="edit_id" id="edit_id" value="' . esc_attr($edit_id) . '"/>';

        echo '<table class="form-table">';

        echo '<tr><th>Tipo de Contenido a Monitorear:</th><td><select name="parent_post_type" id="lcr_parent_post_type" required>';
        echo '<option value="">-- Seleccione un tipo --</option>';
        foreach ($all_post_types as $slug => $name) {
            $selected = ($editing_rule && $editing_rule['parent_post_type'] === $slug) ? 'selected' : '';
            echo '<option value="' . esc_attr($slug) . '" ' . $selected . '>' . esc_html($name) . '</option>';
        }
        echo '</select><p class="description">Seleccione el tipo de contenido que disparará el logro (Ej: Una "respuesta" en AnsPress o una "publicación" de WordPress).</p></td></tr>';


        echo '<tr><th>Taxonomía de la Categoría:</th><td><select name="taxonomy_slug" id="lcr_taxonomy_slug" required><option value="">Seleccione un tipo de contenido primero</option></select><p class="description">La taxonomía a la que pertenece la categoría (Ej: "Categorías" para posts, "Categorías de preguntas" para AnsPress).</p></td></tr>';

        echo '<tr><th>Categoría/Término Específico:</th><td><select name="term_slug" id="lcr_term_slug" required><option value="">Seleccione una taxonomía primero</option></select><p class="description">La categoría específica dentro de la taxonomía. El usuario deberá interactuar con contenido de esta categoría.</p></td></tr>';

        echo '<tr><th>Logro de GamiPress:</th><td><select name="logro_id" required>';
        if (empty($achievement_types)) {
            echo '<option value="">No hay tipos de logros de GamiPress disponibles.</option>';
            error_log('WARNING: [LCR] Admin page: No se encontraron tipos de logros de GamiPress.');
        } else {
            foreach ($achievement_types as $type_slug => $type_data) {
                $achievements = get_posts([
                    'post_type'   => $type_slug,
                    'post_status' => 'publish',
                    'numberposts' => -1,
                    'orderby'     => 'title',
                    'order'       => 'ASC',
                ]);
                if ($achievements) {
                    echo '<optgroup label="' . esc_html($type_data['singular_name']) . '">';
                    foreach ($achievements as $achievement) {
                        $selected = ($editing_rule && $editing_rule['logro_id'] == $achievement->ID) ? 'selected' : '';
                        echo '<option value="' . esc_attr($achievement->ID) . '" ' . $selected . '>' . esc_html($achievement->post_title) . '</option>';
                    }
                    echo '</optgroup>';
                } else {
                    error_log("INFO: [LCR] Admin page: No se encontraron logros para el tipo: $type_slug.");
                }
            }
        }
        echo '</select><p class="description">El logro de GamiPress que se otorgará.</p></td></tr>';

        $cantidad_value = $editing_rule ? esc_attr($editing_rule['cantidad']) : '1';
        echo '<tr><th>Cantidad Requerida:</th><td><input type="number" name="cantidad" value="' . $cantidad_value . '" min="1" required/> <p class="description">Número de veces que el usuario debe interactuar con el contenido (responder, comentar, publicar) en la categoría seleccionada para obtener el logro.</p></td></tr>';

        echo '<tr><td colspan="2">';
        echo '<input type="submit" name="lcr_submit_relation" class="button button-primary" value="' . ($editing_rule ? 'Actualizar Relación' : 'Guardar Nueva Relación') . '"/>';
        if ($editing_rule) {
            echo ' <a href="' . esc_url(admin_url('admin.php?page=lcr_admin')) . '" class="button button-secondary">Cancelar Edición</a>';
        }
        echo '</td></tr></table></form>';

        echo '<h2>Relaciones de Logros Existentes</h2><table class="widefat fixed striped"><thead><tr><th>Tipo de Contenido</th><th>Taxonomía</th><th>Categoría/Término</th><th>Logro</th><th>Cantidad Requerida</th><th>Acciones</th></tr></thead><tbody>';
        if (empty($all_relations)) {
            echo '<tr><td colspan="6">No hay relaciones configuradas aún. ¡Añada una arriba!</td></tr>';
            error_log('INFO: [LCR] Admin page: No se encontraron relaciones existentes para mostrar.');
        } else {
            foreach ($all_relations as $rel) {
                $parent_post_type_name = isset($all_post_types[$rel['parent_post_type']]) ? $all_post_types[$rel['parent_post_type']] : $rel['parent_post_type'];
                $term_obj = get_term_by('slug', $rel['term_slug'], $rel['taxonomy_slug']);
                $term_name = $term_obj ? $term_obj->name : '<span style="color:red;">' . $rel['term_slug'] . ' (No encontrado)</span>';
                $logro_post = get_post($rel['logro_id']);
                $logro_title = $logro_post ? $logro_post->post_title : '<span style="color:red;">ID ' . $rel['logro_id'] . ' (No encontrado)</span>';

                echo '<tr>';
                echo '<td>' . esc_html($parent_post_type_name) . '</td>';
                echo '<td>' . esc_html($rel['taxonomy_slug']) . '</td>';
                echo '<td>' . $term_name . '</td>';
                echo '<td>' . $logro_title . '</td>';
                echo '<td>' . esc_html($rel['cantidad']) . '</td>';
                echo '<td>';
                echo '<a href="' . esc_url(admin_url('admin.php?page=lcr_admin&edit_id=' . $rel['id'])) . '" class="button button-small">Editar</a> ';
                echo '<a href="' . esc_url(wp_nonce_url(admin_url('admin.php?page=lcr_admin&eliminar=' . $rel['id']), 'lcr_delete_rule_' . $rel['id'])) . '" class="button button-small" onclick="return confirm(\'¿Estás seguro de que quieres eliminar esta relación?\');">Eliminar</a>';
                echo '</td>';
                echo '</tr>';
            }
            error_log('INFO: [LCR] Admin page: Mostradas ' . count($all_relations) . ' relaciones existentes.');
        }
        echo '</tbody></table></div>';
    }

    /**
     * Encola los scripts y estilos necesarios para la página de administración.
     *
     * @param string $hook_suffix El sufijo del hook de la página actual.
     */
    public function enqueue_admin_scripts($hook_suffix) {
        if ('toplevel_page_lcr_admin' !== $hook_suffix) {
            return;
        }
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'lcr-admin-script',
            LCR_PLUGIN_URL . 'admin/lcr-admin.js',
            ['jquery'],
            LCR_VERSION,
            true
        );

        $editing_rule_data = isset($_GET['edit_id']) ? lcr_get_achievement_rule_by_id(absint($_GET['edit_id'])) : null;

        wp_localize_script(
            'lcr-admin-script',
            'lcr_ajax_object',
            array(
                'ajax_url'          => admin_url('admin-ajax.php'),
                'nonce'             => wp_create_nonce('lcr_ajax_nonce'),
                'editing_rule_data' => $editing_rule_data, // Pasa los datos de la regla si estamos editando
            )
        );
    }
}

// AJAX para cargar taxonomías y términos
add_action('wp_ajax_lcr_get_taxonomies', function () {
    check_ajax_referer('lcr_ajax_nonce', 'nonce');
    $post_type = sanitize_text_field($_POST['post_type']);
    $taxonomies = get_object_taxonomies($post_type, 'objects');
    $response = ['success' => true, 'taxonomies' => []];
    foreach ($taxonomies as $taxonomy) {
        if ($taxonomy->public && $taxonomy->show_ui) { // Solo taxonomías públicas y con UI
            $response['taxonomies'][] = ['slug' => $taxonomy->name, 'label' => $taxonomy->label];
        }
    }
    wp_send_json($response);
});

add_action('wp_ajax_lcr_get_terms', function () {
    check_ajax_referer('lcr_ajax_nonce', 'nonce');
    $taxonomy_slug = sanitize_text_field($_POST['taxonomy_slug']);
    $terms = get_terms([
        'taxonomy'   => $taxonomy_slug,
        'hide_empty' => false,
    ]);
    $response = ['success' => true, 'terms' => []];
    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            $response['terms'][] = ['slug' => $term->slug, 'label' => $term->name];
        }
    }
    wp_send_json($response);
});