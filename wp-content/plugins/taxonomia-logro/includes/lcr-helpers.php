<?php
// includes/lcr-helpers.php

if (!defined('ABSPATH')) exit;

/**
 * Obtiene todas las reglas de logros definidas en la tabla.
 *
 * @return array Lista de reglas de logros.
 */
function lcr_get_achievement_rules() {
    global $wpdb;
    $tabla = $wpdb->prefix . LCR_DB_TABLE;
    $rules = $wpdb->get_results("SELECT * FROM $tabla", ARRAY_A);
    return $rules;
}

/**
 * Obtiene una regla de logro específica por su ID.
 *
 * @param int $rule_id ID de la regla a buscar.
 * @return array|null Regla de logro o null si no se encuentra.
 */
function lcr_get_achievement_rule_by_id($rule_id) {
    global $wpdb;
    $tabla = $wpdb->prefix . LCR_DB_TABLE;
    $rule = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tabla WHERE id = %d", $rule_id), ARRAY_A);
    return $rule;
}

/**
 * Verifica si un usuario ya tiene un logro GamiPress específico.
 *
 * @param int $user_id ID del usuario.
 * @param int $achievement_id ID del logro de GamiPress.
 * @return bool True si el usuario tiene el logro, false en caso contrario.
 */
function lcr_user_has_achievement($user_id, $achievement_id) {
    if (!function_exists('gamipress_has_achievement')) {
        error_log('WARNING: [LCR] GamiPress functions not available when checking achievement for user ' . $user_id . ' achievement ' . $achievement_id);
        return false;
    }
    return gamipress_has_achievement($achievement_id, $user_id);
}

/**
 * Cuenta el número de publicaciones, respuestas o comentarios de un usuario en una categoría específica.
 *
 * @param int $user_id ID del usuario.
 * @param string $post_type Tipo de publicación ('post', 'answer', 'comment', etc.).
 * @param string $taxonomy_slug Slug de la taxonomía (ej: 'category', 'question_category', etc.).
 * @param string $term_slug Slug del término/categoría.
 * @return int El número de elementos que cumplen los criterios.
 */
function lcr_count_user_actions_in_term($user_id, $post_type, $taxonomy_slug, $term_slug)
{
    $count = 0;

    // Puedes mantener la verificación del término si quieres, pero tus SQL ya lo hacen.
    // Aunque tus SQL asumen taxonomías y post_types fijos, la función original es más flexible.
    // Mantendré la verificación aquí por seguridad y para no romper la lógica existente si no se cumple.
    $term = get_term_by('slug', $term_slug, $taxonomy_slug);
    if (!$term || is_wp_error($term)) {
        error_log("WARNING: [LCR] No se encontró el término '$term_slug' en la taxonomía '$taxonomy_slug' para el conteo de acciones del usuario $user_id.");
        return 0;
    }
    error_log("parametros : ".$post_type." ".$taxonomy_slug." ".$term_slug);
    switch ($post_type) {
        case 'post':
            if ($taxonomy_slug === 'category') {
                $count = lcr_get_user_post_count_by_category($user_id, $term_slug);
                error_log("DEBUG: [LCR] Conteo de 'post' publicados para usuario $user_id en '$taxonomy_slug/$term_slug' (vía SQL directo): $count.");
            } else {
                // Fallback a WP_Query si no es la taxonomía 'category' esperada por la función SQL
                $args = [
                    'author' => $user_id,
                    'posts_per_page' => -1,
                    'fields' => 'ids',
                    'post_type' => $post_type,
                    'post_status' => 'publish',
                    'tax_query' => [
                        [
                            'taxonomy' => $taxonomy_slug,
                            'field' => 'slug',
                            'terms' => $term_slug,
                        ],
                    ],
                ];
                $query = new WP_Query($args);
                $count = $query->found_posts;
                wp_reset_postdata();
                error_log("DEBUG: [LCR] Conteo de '$post_type' publicados para usuario $user_id en '$taxonomy_slug/$term_slug' (vía WP_Query): $count.");
            }
            break;

        case 'answer':
            if ($taxonomy_slug === 'question_category') { // Asumiendo que las respuestas siempre están ligadas a preguntas con esta taxonomía
                $count = lcr_get_user_answer_count_by_question_category($user_id, $term_slug);
                error_log("DEBUG: [LCR] Conteo de 'answer' publicados para usuario $user_id en '$taxonomy_slug/$term_slug' (vía SQL directo): $count.");
            } else {
                // Fallback a WP_Query si no es la taxonomía 'question_category' esperada por la función SQL
                $args = [
                    'author' => $user_id,
                    'posts_per_page' => -1,
                    'fields' => 'ids',
                    'post_type' => $post_type,
                    'post_status' => 'publish',
                    'tax_query' => [
                        [
                            'taxonomy' => $taxonomy_slug,
                            'field' => 'slug',
                            'terms' => $term_slug,
                        ],
                    ],
                ];
                $query = new WP_Query($args);
                $count = $query->found_posts;
                wp_reset_postdata();
                error_log("DEBUG: [LCR] Conteo de '$post_type' publicados para usuario $user_id en '$taxonomy_slug/$term_slug' (vía WP_Query): $count.");
            }
            break;

        case 'comment':
            // La función SQL para comentarios es más compleja, se mantiene la lógica original
            $post_ids_in_term = get_posts([
                'post_type' => 'any',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'tax_query' => [
                    [
                        'taxonomy' => $taxonomy_slug,
                        'field' => 'slug',
                        'terms' => $term_slug,
                    ],
                ],
                'post_status' => 'publish',
            ]);

            if (empty($post_ids_in_term)) {
                error_log("DEBUG: [LCR] No se encontraron publicaciones en la categoría '$term_slug' para la taxonomía '$taxonomy_slug' para el conteo de comentarios.");
                return 0;
            }

            $comment_args = [
                'user_id' => $user_id,
                'status' => 'approve',
                'count' => true,
                'post_id__in' => $post_ids_in_term,
            ];
            $count = get_comments($comment_args);
            error_log("DEBUG: [LCR] Conteo de comentarios aprobados para usuario $user_id en '$taxonomy_slug/$term_slug': $count.");
            break;

        default:
            error_log("WARNING: [LCR] Tipo de publicación no manejado por lcr_count_user_actions_in_term: $post_type.");
            break;
    }

    return $count;
}

// ... (tus nuevas funciones SQL lcr_get_user_post_count_by_category y lcr_get_user_answer_count_by_question_category) ...

/**
 * Cuenta el número de publicaciones de un usuario en una categoría específica.
 *
 * @param int $user_id ID del usuario.
 * @param string $category_slug Slug de la categoría de WordPress.
 * @return int El número de publicaciones que cumplen los criterios.
 */
function lcr_get_user_post_count_by_category($user_id, $category_slug)
{
    global $wpdb;
    $sql = "
        SELECT COUNT(*) FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
        INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
        INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
        WHERE p.post_type = 'post'
        AND p.post_status = 'publish'
        AND tt.taxonomy = 'category'
        AND t.slug = %s
        AND p.post_author = %d
    ";
    $count = $wpdb->get_var($wpdb->prepare($sql, $category_slug, $user_id));
    return (int)$count;
}

/**
 * Cuenta el número de respuestas de un usuario en una categoría de pregunta de AnsPress específica.
 *
 * @param int $user_id ID del usuario.
 * @param string $question_category_slug Slug de la categoría de pregunta de AnsPress.
 * @return int El número de respuestas que cumplen los criterios.
 */
function lcr_get_user_answer_count_by_question_category($user_id, $question_category_slug)
{
    global $wpdb;
    $sql = "
        SELECT COUNT(*) FROM {$wpdb->posts} a
        INNER JOIN {$wpdb->posts} q ON a.post_parent = q.ID
        INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = q.ID
        INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
        INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
        WHERE a.post_type = 'answer'
        AND a.post_status = 'publish'
        AND q.post_type = 'question'
        AND tt.taxonomy = 'question_category'
        AND t.slug = %s
        AND a.post_author = %d
    ";
    $count = $wpdb->get_var($wpdb->prepare($sql, $question_category_slug, $user_id));
    return (int)$count;
}