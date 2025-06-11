<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if (!function_exists('chld_thm_cfg_locale_css')):
    function chld_thm_cfg_locale_css($uri)
    {
        if (empty($uri) && is_rtl() && file_exists(get_template_directory() . '/rtl.css'))
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter('locale_stylesheet_uri', 'chld_thm_cfg_locale_css');

if (!function_exists('child_theme_configurator_css')):
    function child_theme_configurator_css()
    {
        wp_enqueue_style('chld_thm_cfg_child', trailingslashit(get_stylesheet_directory_uri()) . 'style.css', array('astra-theme-css'));
    }
endif;
add_action('wp_enqueue_scripts', 'child_theme_configurator_css', 10);

// END ENQUEUE PARENT ACTION
function limitar_busqueda_post_y_question($query)
{
    if ($query->is_search() && $query->is_main_query() && !is_admin()) {
        $query->set('post_type', array('post', 'question')); // Entradas de blog + preguntas de AnsPress
    }
}
add_action('pre_get_posts', 'limitar_busqueda_post_y_question');

add_filter('ap_user_link', 'custom_ap_user_link', 10, 3);

function custom_ap_user_link($link, $user_id, $sub)
{
    // Dividir la URL en partes
    $url_parts = explode('/', $link);

    // Asegurarse de que las posiciones necesarias existan
    if (isset($url_parts[0], $url_parts[1], $url_parts[2])) {
        // Cambiar la posición 3 a 'user'
        $url_parts[3] = 'user';

        // Si hay una subpágina, usarla directamente después de 'user'
        if (!empty($sub)) {
            $url_parts[4] = $sub;
        } else {
            // Si no hay subpágina, eliminar cualquier valor adicional
            unset($url_parts[4]);
        }

        // Reconstruir la URL
        $link = implode('/', $url_parts);
    }

    return $link;
}

function generate_user_profile_url()
{
    // Obtener el enlace del perfil del usuario
    $perfil_url = ap_get_profile_link();
    $perfil_url_parts = explode('/', $perfil_url);

    // Asegurarse de que las posiciones necesarias existan
    if (isset($perfil_url_parts[0], $perfil_url_parts[1], $perfil_url_parts[2], $perfil_url_parts[5])) {
        // Construir la nueva URL
        $perfil_url_parts[3] = 'user'; // Cambiar la posición 3 a 'user'
        $new_url = $perfil_url_parts[0] . '//' . $perfil_url_parts[2] . '/' . $perfil_url_parts[3] . '/' . $perfil_url_parts[5];
    } else {
        // Manejar el caso en que las posiciones no existan
        $new_url = "http://vallecode.local/user";
    }

    return $new_url;
}

function custom_login_redirect($login_url, $redirect)
{
    $page_id = UM()->options()->get('core_login');
    if (get_post($page_id)) {
        $login_url = 'http://vallecode.local/login';
    }
    return $login_url;
}
add_filter('login_url', 'custom_login_redirect', 10, 2);

add_filter('anspress_user_profile_url', 'custom_profile_redirect', 10, 2);

/**
 * Maneja el envío del formulario de Forminator para crear una publicación en WordPress.
 *
 * @param int $form_id ID del formulario enviado.
 */
function handle_forminator_submission($form_id)
{
    if ((int) $form_id !== 153) {
        return;
    }

    $entry = Forminator_Form_Entry_Model::get_latest_entry_by_form_id($form_id);
    if (!$entry) {
        error_log('No se encontró entrada para el formulario.');
        return;
    }

    $meta_data = $entry->meta_data;

    error_log('Meta data: ' . print_r($meta_data, true));

    // Extraer campos del formulario
    $titulo = $meta_data['text-1']['value'] ?? 'Título de prueba';
    $contenido = $meta_data['textarea-1']['value'] ?? 'Contenido de prueba para la nueva entrada.';
    $imagen_url = $meta_data['upload-1']['value']['file']['file_url'] ?? '';
    $archivos = $meta_data['upload-2']['value']['file']['file_url'] ?? [];
    $categoria = $meta_data['select-1']['value'] ?? '';
    $github_url = $meta_data['url-1']['value'] ?? '';

    // Agregar enlace a GitHub si se proporcionó
    if (!empty($github_url)) {
        $contenido .= '<p><strong>Repositorio en GitHub:</strong> <a href="' . esc_url($github_url) . '" target="_blank">' . esc_html($github_url) . '</a></p>';
    }

    // Agregar archivos adjuntos al contenido
    if (!empty($archivos)) {
        $contenido .= generate_attachment_list($archivos);
    }



    // Crear el post
    $post_id = wp_insert_post([
        'post_title' => wp_strip_all_tags($titulo),
        'post_content' => $contenido,
        'post_status' => 'pending',
        'post_author' => get_current_user_id(),
        'post_type' => 'post',
        'post_category' => [$categoria],
    ]);

    if (is_wp_error($post_id)) {
        error_log('Error al insertar la publicación: ' . $post_id->get_error_message());
        return;
    }

    // Asignar imagen destacada si existe
    if (!empty($imagen_url)) {
        attach_featured_image($imagen_url, $post_id);
    }

    error_log('Publicación creada con éxito. ID: ' . $post_id);
}
add_action('forminator_form_after_save_entry', 'handle_forminator_submission');

/**
 * Genera una lista HTML de archivos adjuntos.
 *
 * @param array $files Archivos subidos (estructura de Forminator).
 * @return string HTML.
 */
/**
 * Genera una lista HTML de archivos adjuntos.
 *
 * @param array $files Archivos subidos (estructura de Forminator).
 * @return string HTML.
 */
function generate_attachment_list($files)
{
    $html = '<h3>📌 <strong>Archivos Adjuntos</strong></h3>';

    foreach ((array) $files as $file) {
        $file_url = is_array($file) && isset($file['file']['file_url']) ? $file['file']['file_url'] : $file;
        if (!$file_url) {
            continue;
        }

        $basename = basename($file_url);
        $parts = explode('-', $basename, 2);
        $display_name = $parts[1] ?? $basename;

        // Si es PDF, usar iframe para mostrar vista previa
        if (strtolower(pathinfo($file_url, PATHINFO_EXTENSION)) === 'pdf') {
            $html .= '<h4>' . esc_html($display_name) . '</h4>';
            $html .= '<iframe src="' . esc_url($file_url) . '" width="100%" height="600px" style="border:1px solid #ccc;"></iframe>';
        } else {
            // Otros archivos como enlaces normales
            $html .= '<ul><li><a href="' . esc_url($file_url) . '" target="_blank" rel="noopener">' . esc_html($display_name) . '</a></li></ul>';
        }
    }

    return $html;
}



/**
 * Asigna una imagen destacada al post desde una URL.
 *
 * @param string $image_url URL de la imagen.
 * @param int $post_id ID del post.
 */
function attach_featured_image($image_url, $post_id)
{
    $image_id = media_sideload_image($image_url, $post_id, null, 'id');
    if (!is_wp_error($image_id)) {
        set_post_thumbnail($post_id, $image_id);
    } else {
        error_log('Error al asignar imagen destacada: ' . $image_id->get_error_message());
    }
}



/**
 * Summary of listar_publicaciones_pendientes
 * @return string
 * @throws
 * @param 
 * @access public
 * @since 1.0.0
 * @see
 */

function listar_publicaciones_pendientes()
{
    if (!is_user_logged_in()) {
        return '<div class="moderation-message error">Debes iniciar sesión para ver esta página.</div>';
    }

    $user = wp_get_current_user();

    if (!in_array('moderador', $user->roles) && !in_array('administrator', $user->roles)) {
        return '<div class="moderation-message error">No tienes permisos para ver esta página.</div>';
    }

    $mensajes = '';

    if (isset($_GET['aprobar_post']) && is_numeric($_GET['aprobar_post'])) {
        $post_id = intval($_GET['aprobar_post']);
        wp_update_post([
            'ID' => $post_id,
            'post_status' => 'publish'
        ]);
        $mensajes .= '<div class="moderation-message success">✅ Publicación aprobada correctamente.</div>';
    }

    if (isset($_GET['eliminar_post']) && is_numeric($_GET['eliminar_post'])) {
        $post_id = intval($_GET['eliminar_post']);
        wp_delete_post($post_id, true);
        $mensajes .= '<div class="moderation-message danger">🗑️ Publicación eliminada correctamente.</div>';
    }

    $args = array(
        'post_type' => 'post',
        'post_status' => 'pending',
        'posts_per_page' => 10,
    );
    $query = new WP_Query($args);

    $output = '<div class="moderation-container">';
    $output .= '<h2 class="moderation-title">📋 Publicaciones Pendientes</h2>';
    $output .= $mensajes;

    if ($query->have_posts()) {
        $output .= '<table class="moderation-table">';
        $output .= '<thead><tr><th>Título</th><th>Acciones</th></tr></thead><tbody>';

        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            $output .= '<tr>';
            $output .= '<td>' . get_the_title() . '</td>';
            $output .= '<td class="acciones">
                <a href="' . get_permalink($post_id) . '" target="_blank" class="btn btn-preview">Previsualizar</a>
                <a href="?aprobar_post=' . $post_id . '" class="btn btn-approve">Aprobar</a>
                <a href="?eliminar_post=' . $post_id . '" class="btn btn-delete" onclick="return confirm(\'¿Seguro que deseas eliminar esta publicación?\')">Eliminar</a>
            </td>';
            $output .= '</tr>';
        }

        $output .= '</tbody></table>';
    } else {
        $output .= '<p class="moderation-message info">No hay publicaciones pendientes.</p>';
    }

    $output .= '</div>';

    wp_reset_postdata();
    return $output;
}

// Registrar el shortcode
add_shortcode('publicaciones_pendientes', 'listar_publicaciones_pendientes');

/*
 * Summary of custom_profile_redirect
 * @return string
 * @throws
 * @param 
 * @access public
 * @since 1.0.0
 * @see
 */
function add_rating_to_comment($comment_id, $rating)
{
    error_log("comentario id " . $comment_id . " rating  " . $rating . "");
    // Obtener datos del comentario
    $comment = get_comment($comment_id);
    error_log('Comentario: ' . print_r($comment, true));
    if (!$comment)
        return;

    $comment_author_id = $comment->user_id; // ID del autor del comentario
    if (!$comment_author_id)
        return; // Evitar calificar comentarios de usuarios anónimos

    // Obtener el número total de votos y el promedio actual del comentario
    $total_votos = get_comment_meta($comment_id, '_wc_total_votos', true) ?: 0;
    $promedio_actual = get_comment_meta($comment_id, '_wc_promedio_estrellas', true) ?: 0;

    // Recalcular promedio
    $nuevo_total_votos = $total_votos + 1;
    $nuevo_promedio = (($promedio_actual * $total_votos) + $rating) / $nuevo_total_votos;

    // Guardar los nuevos valores en los metadatos del comentario
    update_comment_meta($comment_id, '_wc_total_votos', $nuevo_total_votos);
    update_comment_meta($comment_id, '_wc_promedio_estrellas', $nuevo_promedio);

    // Determinar los puntos a otorgar según el promedio
    $puntos = round($nuevo_promedio); // Ajustar la cantidad de puntos según la lógica deseada

    // Asignar puntos con GamiPress
    gamipress_award_points_to_user($comment_author_id, $puntos, 'nombre_del_tipo_de_puntos');
}

add_action("wpdiscuz_add_rating", "add_rating_to_comment", 10, 2);


function mostrar_top_usuarios_por_puntos($atts)
{
    $atts = shortcode_atts(array(
        'tipo' => 'puntos',
        'limite' => 10
    ), $atts, 'top_usuarios_puntos');

    global $wpdb;
    $meta_key_to_query = '_gamipress_' . $atts['tipo'] . '_points';
    $limit_results = (int) $atts['limite'];

    $resultados = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT u.ID, u.display_name, u.user_url, 
                    CAST(um.meta_value AS UNSIGNED) AS puntos
             FROM {$wpdb->users} AS u
             INNER JOIN {$wpdb->usermeta} AS um ON u.ID = um.user_id
             WHERE um.meta_key = %s
             ORDER BY puntos DESC
             LIMIT %d",
            $meta_key_to_query,
            $limit_results
        )
    );

    if (empty($resultados)) {
        return '<div class="gp-no-results">No se encontraron usuarios con puntos.</div>';
    }

    // CSS para el diseño moderno
    $salida = '<style>
    .gp-top-container {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        max-width: 800px;
        margin: 0 auto;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        border-radius: 12px;
        overflow: hidden;
    }
    .gp-top-header {
        background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
        color: white;
        padding: 20px 25px;
        border-bottom: 1px solid #e1e8ed;
    }
    .gp-top-header h3 {
        margin: 0;
        font-size: 1.6rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .gp-top-list {
        list-style: none;
        padding: 0;
        margin: 0;
        background: white;
    }
    .gp-top-item {
        display: flex;
        align-items: center;
        padding: 15px 25px;
        border-bottom: 1px solid #f0f4f8;
        transition: all 0.3s ease;
    }
    .gp-top-item:hover {
        background-color: #f8fafd;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }
    .gp-rank {
        font-size: 1.4rem;
        font-weight: 700;
        width: 40px;
        text-align: center;
        margin-right: 15px;
    }
    .gp-top-1 { color: #FFD700; text-shadow: 0 0 2px rgba(0,0,0,0.2); }
    .gp-top-2 { color: #C0C0C0; text-shadow: 0 0 2px rgba(0,0,0,0.2); }
    .gp-top-3 { color: #CD7F32; text-shadow: 0 0 2px rgba(0,0,0,0.2); }
    .gp-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        overflow: hidden;
        margin-right: 18px;
        border: 3px solid #f0f4f8;
    }
    .gp-user-info {
        flex-grow: 1;
    }
    .gp-user-name {
        font-weight: 600;
        font-size: 1.1rem;
        color: #2d3748;
        text-decoration: none;
        display: block;
        margin-bottom: 3px;
    }
    .gp-user-name:hover {
        color: #4299e1;
    }
    .gp-points {
        font-size: 1.3rem;
        font-weight: 700;
        color: #4299e1;
        min-width: 100px;
        text-align: right;
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }
    .gp-points::after {
        content: " puntos";
        font-size: 0.9rem;
        font-weight: 500;
        color: #718096;
        margin-left: 5px;
    }
    @media (max-width: 600px) {
        .gp-top-header h3 { font-size: 1.3rem; }
        .gp-top-item { padding: 12px 15px; }
        .gp-rank { font-size: 1.2rem; width: 30px; }
        .gp-avatar { width: 42px; height: 42px; margin-right: 12px; }
        .gp-points { font-size: 1.1rem; }
    }
    </style>';

    // Construir el ranking
    $tipo_formateado = ucwords(str_replace('-', ' ', $atts['tipo']));
    $salida .= '<div class="gp-top-container">';
    $salida .= '<div class="gp-top-header">';
    $salida .= '<h3 style="color:white><span class="dashicons dashicons-awards" style="margin-right:10px;"></span> Top ' . $limit_results . ' - ' . $tipo_formateado . '</h3>';
    $salida .= '</div>';
    $salida .= '<ul class="gp-top-list">';

    $posicion = 1;
    foreach ($resultados as $usuario) {
        $avatar = get_avatar($usuario->ID, 96);
        $nombre = esc_html($usuario->display_name);
        $puntos = number_format_i18n($usuario->puntos);
        $user_link = get_author_posts_url($usuario->ID);
        $rank_class = ($posicion <= 3) ? 'gp-top-' . $posicion : '';

        $salida .= '<li class="gp-top-item">';
        $salida .= '<div class="gp-rank ' . $rank_class . '">#' . $posicion . '</div>';
        $salida .= '<div class="gp-avatar">' . $avatar . '</div>';
        $salida .= '<div class="gp-user-info">';
        $salida .= '<a href="' . esc_url($user_link) . '" class="gp-user-name">' . $nombre . '</a>';
        $salida .= '</div>';
        $salida .= '<div class="gp-points">' . $puntos . '</div>';
        $salida .= '</li>';

        $posicion++;
    }

    $salida .= '</ul></div>';
    return $salida;
}
add_shortcode('top_usuarios_puntos', 'mostrar_top_usuarios_por_puntos');

add_filter('ap_question_form_fields', 'my_custom_anspress_category_dropdown');
function my_custom_anspress_category_dropdown($fields)
{
    $fields['category'] = array(
        'type' => 'term-select',
        'label' => __('Categoría', 'anspress-question-answer'),
        'desc' => __('Selecciona la categoría de tu pregunta.', 'anspress-question-answer'),
        'taxonomy' => 'question_category',
        'required' => true,
        'placeholder' => __('Selecciona una categoría', 'anspress-question-answer'),
    );
    return $fields;
}


// 1. Registrar el trigger personalizado para GamiPress
function my_prefix_custom_activity_triggers_for_anspress_category($triggers)
{
    $triggers['AnsPress Events'] = array(
        'my_prefix_anspress_category_event' => __('Publicar una pregunta en la categoría Programación', 'gamipress'),
    );
    return $triggers;
}
add_filter('gamipress_activity_triggers', 'my_prefix_custom_activity_triggers_for_anspress_category');

// 2. Escuchar el hook que se ejecuta al publicar una pregunta
function my_prefix_trigger_gamipress_on_anspress_question($post_id)
{
    // Verificar que sea una pregunta de AnsPress
    if (get_post_type($post_id) !== 'question') {
        return;
    }

    // Obtener las categorías asignadas a la pregunta
    $categories = wp_get_post_terms($post_id, 'question_category', array('fields' => 'slugs'));

    // Aquí defines el slug de la categoría que quieres rastrear
    $categoria_objetivo = 'programacion'; // Cambia esto por el slug real de tu categoría

    if (in_array($categoria_objetivo, $categories)) {
        // Disparar el evento de GamiPress
        gamipress_trigger_event(array(
            'event' => 'my_prefix_anspress_category_event',
            'user_id' => get_post_field('post_author', $post_id),
            'post_id' => $post_id,
        ));
    }
}
add_action('save_post_question', 'my_prefix_trigger_gamipress_on_anspress_question', 20);


// Agrega soporte para /perfil/usuario
function perfil_rewrite_rules()
{
    add_rewrite_rule(
        '^perfil/([^/]+)/?$',
        'index.php?pagename=perfil&user=$matches[1]',
        'top'
    );
}
add_action('init', 'perfil_rewrite_rules');

// Permitir que se use el query var "user"
function agregar_query_vars_personalizadas($vars)
{
    $vars[] = 'user';
    return $vars;
}
add_filter('query_vars', 'agregar_query_vars_personalizadas');



function mostrar_perfil_general($user)
{
    if (!$user) {
        echo '<p>Usuario no encontrado.</p>';
        return;
    }

    $user_id = $user->ID;
    ?>

    <div class="perfil-general"
        style="max-width: 800px; margin: auto; padding: 2rem; border: 1px solid #ddd; border-radius: 10px;">
        <div style="text-align: center;">
            <img src="<?php echo esc_url(get_avatar_url($user_id)); ?>" style="width: 120px; border-radius: 50%;"
                alt="Avatar de <?php echo esc_attr($user->display_name); ?>">
            <h2><?php echo esc_html($user->display_name); ?></h2>
            <p><?php echo esc_html($user->user_email); ?></p>
        </div>

        <div style="margin-top: 2rem;">
            <h3>Puntos</h3>
            <p><?php echo do_shortcode('[gamipress_user_points user_id="' . $user_id . '"]'); ?></p>

            <h3>Rangos</h3>
            <p><?php echo do_shortcode('[gamipress_user_rank user_id="' . $user_id . '"]'); ?></p>

            <h3>Logros</h3>
            <p><?php echo do_shortcode('[gamipress_user_achievements user_id="' . $user_id . '" type="all"]'); ?></p>
        </div>

        <div style="margin-top: 2rem;">
            <h3>Últimas preguntas</h3>
            <?php echo do_shortcode('[anspress user="' . $user_id . '" type="question" orderby="date" order="DESC" limit="5"]'); ?>
        </div>
    </div>

    <?php
}
