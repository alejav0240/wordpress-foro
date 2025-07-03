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
    $categoria_nombre = $meta_data['select-1']['value'] ?? '';
    $github_url = $meta_data['url-1']['value'] ?? '';

    $termino = get_term_by('name', $categoria_nombre, 'category');

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
        'post_category' => $termino ? [$termino->term_id] : [],
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
    <?php
}

function obtener_datos_gamipress_usuario($user_id) {
    if (!$user_id || !get_userdata($user_id)) {
        return null;
    }

    $datos = [];

    // Tipos de puntos
    $tipos_puntos = gamipress_get_points_types();
    foreach ($tipos_puntos as $slug => $data) {
        $cantidad = gamipress_get_user_points($user_id, $slug);
        $page_url = get_post_type_archive_link($slug); // Enlace del archivo de puntos (si existe)
        $icono_id = get_post_thumbnail_id($data['ID']);
        $icono_url = $icono_id ? wp_get_attachment_url($icono_id) : '';

        $datos['puntos'][$slug] = [
            'nombre' => $data,
            'cantidad' => $cantidad,
            'enlace' => $page_url,
            'icono' => $icono_url,
        ];
    }

    // Tipos de rangos
    $tipos_rangos = gamipress_get_rank_types();
    foreach ($tipos_rangos as $slug => $data) {
        $rango = gamipress_get_user_rank($user_id, $slug);
        $post_id = $rango ? $rango->ID : 0;

        $datos['rangos'][$slug] = [
            'nombre' => $data,
            'rango_actual' => $rango ? $rango->post_title : 'Sin rango',
            'enlace' => $rango ? get_permalink($post_id) : '',
            'icono' => $rango ? get_the_post_thumbnail_url($post_id) : '',
        ];
    }

    // Tipos de logros
    $tipos_logros = gamipress_get_achievement_types();
    foreach ($tipos_logros as $slug => $data) {
        $logros = gamipress_get_user_achievements([
            'user_id' => $user_id,
            'achievement_type' => $slug,
            'limit' => -1,
        ]);

        $lista_logros = array_map(function($logro) {
            return [
                'titulo' => get_the_title($logro->post_id),
                'enlace' => get_permalink($logro->post_id),
                'icono' => get_the_post_thumbnail_url($logro->post_id),
            ];
        }, $logros);

        $datos['logros'][$slug] = [
            'nombre' => $data,
            'lista' => $lista_logros,
        ];
    }

    return $datos;
}

// Intentar forzar la propiedad 'public' a true para los CPTs de AnsPress
add_action( 'init', 'anspress_force_cpt_public_property', 999 ); // Prioridad alta para asegurar que se ejecute tarde

function anspress_force_cpt_public_property() {
    global $wp_post_types;

    // Para el CPT de preguntas
    if ( isset( $wp_post_types['question'] ) ) {
        $wp_post_types['question']->public = true;
        $wp_post_types['question']->publicly_queryable = true;
        $wp_post_types['question']->exclude_from_search = false;
        $wp_post_types['question']->show_ui = true;
        $wp_post_types['question']->show_in_nav_menus = true;
        $wp_post_types['question']->show_in_menu = true; // Para asegurar que aparezca en el menú de admin si no lo hace
        $wp_post_types['question']->show_in_rest = true; // Si tu "Post Timeline" usa la API REST
    }

    // Para el CPT de respuestas (si también lo necesitas)
    if ( isset( $wp_post_types['answer'] ) ) {
        $wp_post_types['answer']->public = true;
        $wp_post_types['answer']->publicly_queryable = true;
        $wp_post_types['answer']->exclude_from_search = false;
        $wp_post_types['answer']->show_ui = true;
        $wp_post_types['answer']->show_in_nav_menus = true;
        $wp_post_types['answer']->show_in_menu = true;
        $wp_post_types['answer']->show_in_rest = true;
    }
}

// Hacer que las taxonomías de AnsPress sean públicamente consultables y visibles
function anspress_make_taxonomies_public() {
    global $wp_taxonomies;

    // Para la taxonomía de categorías de preguntas
    if ( isset( $wp_taxonomies['question_category'] ) ) {
        $wp_taxonomies['question_category']->public = true;
        $wp_taxonomies['question_category']->publicly_queryable = true;
        $wp_taxonomies['question_category']->show_ui = true;
        $wp_taxonomies['question_category']->show_in_nav_menus = true;
        $wp_taxonomies['question_category']->show_in_rest = true; // Crucial si el frontend usa REST API
    }

    // Para la taxonomía de etiquetas de preguntas
    if ( isset( $wp_taxonomies['question_tag'] ) ) {
        $wp_taxonomies['question_tag']->public = true;
        $wp_taxonomies['question_tag']->publicly_queryable = true;
        $wp_taxonomies['question_tag']->show_ui = true;
        $wp_taxonomies['question_tag']->show_in_nav_menus = true;
        $wp_taxonomies['question_tag']->show_in_rest = true; // Crucial si el frontend usa REST API
    }
}
add_action( 'init', 'anspress_make_taxonomies_public', 999 ); // Prioridad alta para ejecutar al final


// 🔁 Reforzar registro de taxonomías de AnsPress
add_action('init', function () {
    register_taxonomy('question_category', 'question', [
        'labels' => [
            'name' => 'Categorías de Preguntas',
            'singular_name' => 'Categoría de Pregunta'
        ],
        'public' => true,
        'rewrite' => ['slug' => 'question-category'],
        'hierarchical' => true,
    ]);

    register_taxonomy('question_tag', 'question', [
        'labels' => [
            'name' => 'Etiquetas de Preguntas',
            'singular_name' => 'Etiqueta de Pregunta'
        ],
        'public' => true,
        'rewrite' => ['slug' => 'question_tag'],
        'hierarchical' => false,
    ]);
}, 5);

// 🔄 Regenerar reglas de reescritura al activar el tema
add_action('after_switch_theme', 'flush_rewrite_rules');

// 🔁 Redireccionar /questions/categories/{slug} → /question-category/{slug}
// 🔁 Redireccionar /questions/tags/{slug} → /question_tag/{slug}
add_action('template_redirect', function () {
    $request_uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

    // Redirección para categorías
    if (preg_match('#^questions/categories/([^/]+)#', $request_uri, $matches)) {
        $slug = sanitize_title($matches[1]);
        wp_redirect(home_url('/question-category/' . $slug . '/'), 301);
        exit;
    }

    // Redirección para etiquetas
    if (preg_match('#^questions/tags/([^/]+)#', $request_uri, $matches)) {
        $slug = sanitize_title($matches[1]);
        wp_redirect(home_url('/question_tag/' . $slug . '/'), 301);
        exit;
    }

    // Redirección para mi-perfil
    if (preg_match('#^mi-perfil(?:/([^/]+))?#', $request_uri, $matches)) {
        $new_url = !empty($matches[1])
            ? home_url('/perfil/' . sanitize_user($matches[1]) . '/')
            : home_url('/perfil/');
        wp_redirect($new_url, 301);
        exit;
    }

    // Redirecciones directas simples
    switch ($request_uri) {
        case 'proyectos-academicos':
            wp_redirect(home_url('/proyectos-academicos/proyectos/'), 301);
            exit;

        case 'ranking-y-reputacion':
            wp_redirect(home_url('/ranking-y-reputacion/top-usuarios/'), 301);
            exit;

        case 'comunidad':
            wp_redirect(home_url('/comunidad/preguntas/'), 301);
            exit;
    }
});
