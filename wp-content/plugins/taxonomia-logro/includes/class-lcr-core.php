<?php
// includes/class-lcr-core.php

if (!defined('ABSPATH')) exit;

class LCR_Core {

    public function __construct() {
        // Registra el hook de Action Scheduler
        add_action(LCR_ACTION_HOOK, array($this, 'process_achievement_task_callback'), 10, 3);
    }

    /**
     * Función principal para rastrear y otorgar logros basada en acciones del usuario.
     * Esta función es la que será programada por Action Scheduler.
     *
     * @param int $user_id ID del usuario que realizó la acción.
     * @param int $parent_post_id ID de la publicación padre (e.g., pregunta para una respuesta, publicación para un comentario/post).
     * @param string $trigger_type Tipo de acción que disparó la función ('answer', 'comment', 'post_publish').
     */
    public function process_achievement_task_callback($user_id, $parent_post_id, $trigger_type) {
        error_log("INFO: [LCR] LCR_Core::process_achievement_task_callback (user_id: $user_id, parent_post_id: $parent_post_id, trigger_type: $trigger_type) - INICIO (via Action Scheduler)");

        if (!$user_id || !$parent_post_id) {
            error_log('WARNING: [LCR] Falta ID de usuario o ID de publicación padre en la tarea de Action Scheduler. Abortando.');
            return;
        }

        // Asegurarse de que GamiPress esté activo y sus funciones disponibles ANTES de continuar.
        // Esta verificación es CRÍTICA aquí, ya que Action Scheduler garantiza que WordPress está completamente cargado.
        if (!class_exists('GamiPress') || !function_exists('gamipress_award_achievement_to_user')) {
            error_log('CRITICAL ERROR: [LCR] GamiPress no parece estar activo o sus funciones principales no están disponibles. Asegúrese de que GamiPress esté instalado y activado. La tarea de Action Scheduler para el usuario ' . $user_id . ' Falló.');
            return; // No reintentar, ya que GamiPress no está disponible
        }

        $actual_parent_post_type = get_post_type($parent_post_id);
        if (!$actual_parent_post_type) {
            error_log("WARNING: [LCR] No se pudo obtener el tipo de publicación para parent_post_id: $parent_post_id en la tarea de Action Scheduler. Abortando.");
            return;
        }
        error_log("DEBUG: [LCR] Tipo de publicación padre real para $parent_post_id: $actual_parent_post_type (desde tarea AS).");

        $all_rules = lcr_get_achievement_rules(); // Usar la función auxiliar

        if (empty($all_rules)) {
            error_log('INFO: [LCR] No se encontraron reglas de logros definidas. No hay nada que procesar (desde tarea AS).');
            return;
        }

        foreach ($all_rules as $rule) {
            error_log("DEBUG: [LCR] Procesando regla ID: " . (isset($rule['id']) ? $rule['id'] : 'N/A') . " para el tipo de disparo: $trigger_type (desde tarea AS).");

            if (!isset($rule['parent_post_type'], $rule['taxonomy_slug'], $rule['term_slug'], $rule['logro_id'], $rule['cantidad'])) {
                error_log('WARNING: [LCR] Regla de logro incompleta, saltando (desde tarea AS). Faltan campos requeridos. Datos de la regla: ' . print_r($rule, true));
                continue;
            }

            $rule_parent_post_type = $rule['parent_post_type'];
            $taxonomy_slug = $rule['taxonomy_slug'];
            $term_slug = $rule['term_slug'];
            $logro_id = (int) $rule['logro_id'];
            $cantidad_requerida = (int) $rule['cantidad'];

            // Comprobar si el usuario ya tiene el logro (prevención de re-otorgamiento si el logro no es repetible)
            if (lcr_user_has_achievement($user_id, $logro_id)) { // Usar la función auxiliar
                error_log("DEBUG: [LCR] El usuario $user_id ya tiene el logro $logro_id. Saltando la regla ID " . $rule['id'] . " (desde tarea AS).");
                continue;
            }

            // 1. Validar que el tipo de publicación de la regla coincida con el tipo de publicación real del padre.
            if ($rule_parent_post_type !== $actual_parent_post_type) {
                error_log("DEBUG: [LCR] Tipo de publicación de la regla ('$rule_parent_post_type') no coincide con el tipo de publicación padre real ('$actual_parent_post_type') para regla ID " . $rule['id'] . ". Saltando (desde tarea AS).");
                continue;
            }

            // 2. Verificar si la publicación padre está asociada al término/categoría de la regla.
            $parent_terms = wp_get_post_terms($parent_post_id, $taxonomy_slug, ['fields' => 'slugs']);

            if (is_wp_error($parent_terms)) {
                error_log("ERROR: [LCR] Error obteniendo términos para la publicación padre $parent_post_id y taxonomía $taxonomy_slug para regla ID " . $rule['id'] . ": " . $parent_terms->get_error_message() . " (desde tarea AS).");
                continue;
            }

            if (!in_array($term_slug, $parent_terms)) {
                error_log("DEBUG: [LCR] Término '$term_slug' no encontrado en la publicación padre $parent_post_id para la taxonomía '$taxonomy_slug' para regla ID " . $rule['id'] . ". Saltando (desde tarea AS).");
                continue;
            }
            error_log("DEBUG: [LCR] La publicación padre $parent_post_id SÍ está en el término objetivo '$term_slug' para la taxonomía '$taxonomy_slug'. Procediendo con el conteo para la regla ID " . $rule['id'] . " (desde tarea AS).");

            // 3. Contar las acciones del usuario para este tipo de contenido en esta categoría.
            $user_action_count = 0;
            switch ($trigger_type) {
                case 'answer':
                    $user_action_count = lcr_count_user_actions_in_term($user_id, 'answer', $taxonomy_slug, $term_slug); // Usar la función auxiliar
                    break;
                case 'comment':
                    $user_action_count = lcr_count_user_actions_in_term($user_id, 'comment', $taxonomy_slug, $term_slug); // Usar la función auxiliar
                    break;
                case 'post_publish':
                    $user_action_count = lcr_count_user_actions_in_term($user_id, 'post', $taxonomy_slug, $term_slug); // Usar la función auxiliar
                    break;
                default:
                    error_log("WARNING: [LCR] Tipo de disparo desconocido: $trigger_type para la regla ID " . $rule['id'] . ". Saltando (desde tarea AS).");
                    continue 2;
            }

            error_log("INFO: [LCR] Usuario $user_id tiene $user_action_count acciones para el tipo '$trigger_type' en '$taxonomy_slug/$term_slug'. Requerido: $cantidad_requerida (desde tarea AS).");

            // 4. Si el usuario ha alcanzado la cantidad requerida y no tiene ya el logro, otorgarlo.
            if ($user_action_count >= $cantidad_requerida) {
                error_log("INFO: [LCR] El usuario $user_id ha cumplido los requisitos para el logro $logro_id (desde tarea AS).");
                $awarded = gamipress_award_achievement_to_user($logro_id, $user_id);
                if ($awarded) {
                    error_log("SUCCESS: [LCR] Logro $logro_id otorgado exitosamente al usuario $user_id (desde tarea AS).");
                } else {
                    error_log("ERROR: [LCR] Fallo al otorgar el logro $logro_id al usuario $user_id (GamiPress retornó false) (desde tarea AS). Podría ser que ya lo tenía o un problema de configuración de GamiPress.");
                }
            } else {
                error_log("INFO: [LCR] El usuario $user_id aún no ha cumplido la cantidad requerida ($cantidad_requerida) para el logro $logro_id. Acciones actuales: $user_action_count (desde tarea AS).");
            }
        }
        error_log("INFO: [LCR] LCR_Core::process_achievement_task_callback - FIN (via Action Scheduler).");
    }
}