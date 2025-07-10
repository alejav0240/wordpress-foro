<?php
// includes/lcr-event-listeners.php

if (!defined('ABSPATH')) exit;

/**
 * Gancho para AnsPress: Cuando se guarda una nueva respuesta.
 * Programa la tarea de procesamiento de logros usando Action Scheduler.
 */
add_action('save_post_answer', function ($post_id, $post, $update) {
    error_log("🔥 [LCR] Hook save_post_answer disparado para post_id: $post_id");

    if ($update) {
        error_log("⛔ [LCR] save_post_answer: La publicación es una actualización. Saltando el procesamiento de logros.");
        return;
    }

    $user_id = get_current_user_id();
    if (!$user_id) {
        error_log("⛔ [LCR] save_post_answer: No se encontró el ID de usuario actual. Saltando el procesamiento de logros.");
        return;
    }

    $question_id = $post->post_parent; // Asumiendo que post_parent es la pregunta
    if (!$question_id) {
        error_log("⛔ [LCR] save_post_answer: No se pudo obtener question_id de post_parent. Saltando.");
        return;
    }

    // --- PROGRAMAR TAREA CON ACTION SCHEDULER ---
    if (!function_exists('as_schedule_single_action')) {
        error_log("funcion as_schedule_single_action");
        as_schedule_single_action(
            time() + 5, // Ejecutar en 5 segundos (o en el siguiente cron de WP)
            LCR_ACTION_HOOK,
            [$user_id, $question_id, 'answer'],
            'lcr_group_achievements' // Opcional: agrupar tareas
        );
        error_log("✅ [LCR] save_post_answer: Tarea de Action Scheduler programada para la respuesta $post_id, user: $user_id, question: $question_id.");
    } else {
        error_log("CRITICAL ERROR: [LCR] Action Scheduler no está disponible. No se pudo programar la tarea de logro para la respuesta $post_id.");
        // Fallback: Si AS no está, puedes intentar la llamada directa, aunque no es lo ideal.
        // Esto solo se ejecutaría si Action Scheduler no está presente en absoluto.
        // En un entorno de producción, AS debería estar siempre disponible.
        $core = new LCR_Core(); // Instanciar para llamar al método
        $core->process_achievement_task_callback($user_id, $question_id, 'answer');
    }

}, 10, 3);


/**
 * Gancho para Comentarios de WordPress: Cuando se aprueba un nuevo comentario.
 * Programa la tarea de procesamiento de logros usando Action Scheduler.
 */
add_action('comment_post', function ($comment_id, $comment_approved) {
    error_log("🔥 [LCR] Hook comment_post disparado para comment_id: $comment_id");

    if (1 !== $comment_approved) {
        error_log("⛔ [LCR] comment_post: El comentario no está aprobado (estado: $comment_approved). Saltando el procesamiento de logros.");
        return;
    }

    $comment = get_comment($comment_id);
    if (!$comment || empty($comment->user_id)) {
        error_log("⛔ [LCR] comment_post: No se pudo obtener el objeto del comentario o user_id para comment_id: $comment_id. Saltando.");
        return;
    }

    // --- PROGRAMAR TAREA CON ACTION SCHEDULER ---
    if (function_exists('as_schedule_single_action')) {
        as_schedule_single_action(
            time() + 5, // Ejecutar en 5 segundos
            LCR_ACTION_HOOK,
            [$comment->user_id, $comment->comment_post_ID, 'comment'],
            'lcr_group_achievements'
        );
        error_log("✅ [LCR] comment_post: Tarea de Action Scheduler programada para el comentario $comment_id, user: {$comment->user_id}, post: {$comment->comment_post_ID}.");
    } else {
        error_log("CRITICAL ERROR: [LCR] Action Scheduler no está disponible. No se pudo programar la tarea de logro para el comentario $comment_id.");
        $core = new LCR_Core();
        $core->process_achievement_task_callback($comment->user_id, $comment->comment_post_ID, 'comment');
    }

}, 10, 2);


/**
 * Gancho para Publicación de Posts: Cuando un post cambia a estado 'publish'.
 * Programa la tarea de procesamiento de logros usando Action Scheduler.
 */
add_action('publish_post', function ($post_id, $post) {
    error_log("🚀 [LCR] publish_post se disparó para post_id=$post_id, autor={$post->post_author}");

    if ($post->post_type === 'post' && $post->post_status === 'publish' && !empty($post->post_author)) {
        // --- PROGRAMAR TAREA CON ACTION SCHEDULER ---
        if (!function_exists('as_schedule_single_action')) {
            as_schedule_single_action(
                time() + 5, // Ejecutar en 5 segundos
                LCR_ACTION_HOOK,
                [$post->post_author, $post_id, 'post_publish'],
                'lcr_group_achievements'
            );
            error_log("✅ [LCR] publish_post: Tarea de Action Scheduler programada para la publicación $post_id, user: {$post->post_author}.");
        } else {
            error_log("CRITICAL ERROR: [LCR] Action Scheduler no está disponible. No se pudo programar la tarea de logro para la publicación $post_id.");
            $core = new LCR_Core();
            $core->process_achievement_task_callback($post->post_author, $post_id, 'post_publish');
        }
    } else {
        error_log("⛔ [LCR] publish_post: Las condiciones no se cumplen para el procesamiento (tipo de publicación no es 'post', o el estado no es 'publish', o el autor está vacío). Saltando.");
    }
}, 10, 2);