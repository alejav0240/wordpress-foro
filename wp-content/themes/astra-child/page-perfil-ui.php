<?php
/**
 * Template Name: Perfil UI Mejorado
 */
get_header();

$user_id = get_query_var('um_user');
if (!$user_id) {
    $user_id = get_current_user_id();
}
$user_info = get_userdata($user_id);

$username = get_query_var('user');

// Si no se pasa un usuario por la URL, usar el actual logueado
if (!$username) {
    $user = wp_get_current_user();
} else {
    $user = get_user_by('login', $username);
}

if (!$user) {
    echo 'Usuario no encontrado.';
} else {
    $puntos = gamipress_get_user_points_expended();
    echo json_encode($puntos);
    //mostrar_perfil_general($user);
}

?>


<?php get_footer(); ?>