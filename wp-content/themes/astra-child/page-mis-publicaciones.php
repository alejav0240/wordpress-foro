<?php
/* Template Name: Página Mis Publicaciones */
get_header(); ?>
<style>
    .pagina-mis-publicaciones {
    padding: 4rem 0;
    min-height: 100vh;
}

.contenedor-pagina {
    max-width: 768px;
    margin: 0 auto;
    padding: 0 1rem;
}

.cabecera-pagina {
    text-align: center;
    margin-bottom: 2rem;
}

.titulo-pagina {
    font-size: 2.5rem;
    font-weight: bold;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.descripcion-pagina {
    font-size: 1.125rem;
    color: #4a5568;
}

.seccion-publicaciones {
    background: #ffffff;
    border-radius: 1rem;
    margin-bottom: 1rem;
    padding: 2rem;
    box-shadow: 0 10px 20px rgba(0,0,0,0.5);
}

.encabezado-seccion {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.titulo-seccion {
    font-size: 1.5rem;
    color: #1a202c;
    font-weight: 600;
}

.boton-nueva-publicacion {
    background-color: #ff002fa8;
    color: #fff;
    padding: 0.5rem 1rem;
    border-radius: 0.75rem;
    text-decoration: none;
    font-weight: 500;
    transition: background-color 0.3s ease;
}

.boton-nueva-publicacion:hover {
    color: #fff;
    background-color: #bf0628;
}

.lista-publicaciones {
    display: grid;
    gap: 1rem;
}

.tarjeta-publicacion {
    background: #FDFAF7;
    padding: 1.25rem;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
    transition: background 0.2s, box-shadow 0.2s;
}

.tarjeta-publicacion:hover {
    background: #ffffff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

.titulo-publicacion {
    font-size: 1.2rem;
    font-weight: 600;
    color: #bf0628;
    text-decoration: none;
}

.titulo-publicacion:hover {
    text-decoration: underline;
}

.fecha-publicacion {
    font-size: 0.9rem;
    color: #718096;
    margin-top: 0.25rem;
}

.resumen-publicacion {
    font-size: 1rem;
    color: #2d3748;
    margin-top: 0.5rem;
    line-height: 1.5;
}

.mensaje-sin-publicaciones {
    text-align: center;
    color: #4a5568;
    font-size: 1rem;
}

</style>

<div id="primary" class="pagina-mis-publicaciones">
    <main id="main" class="contenedor-pagina">

        <?php if (have_posts()):
            while (have_posts()):
                the_post(); ?>

                <header class="cabecera-pagina">
                    <h1 class="titulo-pagina"><?php echo esc_html(get_the_title()); ?></h1>
                    <p class="descripcion-pagina"><?php the_content(); ?></p>
                </header>

            <?php endwhile;
        endif; ?>

        <section class="seccion-publicaciones">
            <div class="encabezado-seccion">
                <h2 class="titulo-seccion">
                    <?php esc_html_e('Mis últimas publicaciones', 'tu-textdomain'); ?>
                </h2>
                <a href="/questions/ask/" class="boton-nueva-publicacion">+ Nueva Pregunta</a>
            </div>

            <?php
            $current_user_id = get_current_user_id();
            $args = array(
                'post_type' => 'post',
                'posts_per_page' => 5,
                'author' => $current_user_id,
                'post_status' => 'publish',
            );

            $user_questions = new WP_Query($args);

            if ($user_questions->have_posts()):
                echo '<div class="lista-publicaciones">';
                while ($user_questions->have_posts()):
                    $user_questions->the_post(); ?>

                    <article class="tarjeta-publicacion">
                        <a href="<?php echo esc_url(get_permalink()); ?>" class="titulo-publicacion">
                            <?php echo esc_html(get_the_title()); ?>
                        </a>
                        <p class="fecha-publicacion">
                            Publicada el <?php echo esc_html(get_the_date()); ?>
                        </p>
                        <p class="resumen-publicacion">
                            <?php echo esc_html(wp_trim_words(get_the_excerpt(), 20)); ?>
                        </p>
                    </article>

                <?php endwhile;
                echo '</div>';
                wp_reset_postdata();
            else:
                echo '<p class="mensaje-sin-publicaciones">Aún no has publicado ninguna publicacion.</p>';
            endif;
            ?>
        </section>
    </main>
</div>

<?php get_footer(); ?>