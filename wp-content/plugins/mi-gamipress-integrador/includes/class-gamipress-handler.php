<?php
// includes/class-gamipress-handler.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mi_GamiPress_Integrador_Handler {

    // ID del logro que queremos otorgar.
    // DEBES REEMPLAZAR ESTO CON EL ID REAL DE UN LOGRO QUE CREES EN GAMIPRESS.
    const LOGRO_AUTOR_PROLIFICO_ID = 123; // ¡Cambia este ID!

    // ID del logro específico a verificar para el mensaje en el perfil.
    // DEBES REEMPLAZAR ESTO CON OTRO ID REAL DE UN LOGRO DE GAMIPRESS.
    const LOGRO_ESPECIAL_PERFIL_ID = 456; // ¡Cambia este ID!

    public function __construct() {
        // Hook para verificar al guardar un post
        add_action( 'save_post', array( $this, 'otorgar_logro_autor_prolifico' ), 10, 2 );

        // Hook para añadir contenido al perfil de usuario (en el frontend)
        add_action( 'show_user_profile', array( $this, 'mostrar_mensaje_logro_perfil' ) );
        add_action( 'edit_user_profile', array( $this, 'mostrar_mensaje_logro_perfil' ) ); // Para la página de edición de perfil en el admin
    }

    /**
     * Otorga un logro cuando un usuario publica un número específico de artículos.
     * Este ejemplo otorga un logro si el usuario tiene 5 o más posts publicados.
     *
     * @param int     $post_id El ID del post.
     * @param WP_Post $post    El objeto post.
     */
    public function otorgar_logro_autor_prolifico( $post_id, $post ) {
        // Solo para posts publicados y no para revisiones o auto-guardados
        if ( 'post' !== $post->post_type || 'publish' !== $post->post_status || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return;
        }

        $user_id = $post->post_author;

        // Si GamiPress está activo
        if ( function_exists( 'gamipress_award_achievement' ) ) {
            // Contar el número de posts publicados por el usuario
            $args = array(
                'author'        => $user_id,
                'post_type'     => 'post',
                'post_status'   => 'publish',
                'posts_per_page' => -1, // Obtener todos los posts
            );
            $user_posts = new WP_Query( $args );

            // Si el usuario tiene 5 o más posts y no ha ganado el logro todavía
            if ( $user_posts->post_count >= 5 && ! gamipress_has_achievement( self::LOGRO_AUTOR_PROLIFICO_ID, $user_id ) ) {
                // Otorgar el logro usando la API de GamiPress
                gamipress_award_achievement( self::LOGRO_AUTOR_PROLIFICO_ID, $user_id );
                // Opcional: Mostrar un aviso o log para depuración
                error_log( 'Logro "Autor Prolífico" otorgado al usuario ID: ' . $user_id );
            }
        }
    }

    /**
     * Muestra un mensaje personalizado en el perfil del usuario si ha conseguido un logro específico.
     *
     * @param WP_User $user Objeto de usuario actual.
     */
    public function mostrar_mensaje_logro_perfil( $user ) {
        // Asegurarse de que GamiPress esté activo y la función exista
        if ( function_exists( 'gamipress_has_achievement' ) ) {
            ?>
            <h3><?php _e( 'Estado de Logros GamiPress', 'mi-gamipress-integrador' ); ?></h3>
            <table class="form-table">
                <tr>
                    <th><label><?php _e( 'Logro Especial de Perfil', 'mi-gamipress-integrador' ); ?></label></th>
                    <td>
                        <?php
                        if ( gamipress_has_achievement( self::LOGRO_ESPECIAL_PERFIL_ID, $user->ID ) ) {
                            echo '<span style="color: green; font-weight: bold;">¡Felicidades! Has conseguido el Logro de Perfil Especial.</span>';
                        } else {
                            echo '<span style="color: red;">Aún no has conseguido el Logro de Perfil Especial. ¡Sigue interactuando!</span>';
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <?php
        }
    }
}