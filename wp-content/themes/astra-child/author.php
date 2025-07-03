<?php
get_header();

$author = get_queried_object();
$author_id = $author->ID;

$datos_usuario = obtener_datos_gamipress_usuario($author_id);

// Obtener recuentos
$total_posts = count_user_posts($author_id, 'post');
$total_questions = count_user_posts($author_id, 'question');
$total_answers = count_user_posts($author_id, 'answer'); // AnsPress usa este CPT

?>

    <style>
        /* Variables de color */
        :root {
            --primary-color: #0073aa;
            --secondary-color: #00a0d2;
            --text-color: #333;
            --text-light: #666;
            --text-lighter: #888;
            --bg-color: #fff;
            --bg-light: #f8f9fa;
            --border-color: #eee;
            --border-radius: 8px;
            --box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        /* Estilos base */
        .author-page {
            width: 100%;
            background-color: var(--bg-light);
            padding: 2rem 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            line-height: 1.6;
            color: var(--text-color);
        }

        .author-page .container {
            max-width: 1200px;
            width: 90%;
            margin: 0 auto;
            padding: 2rem;
            background: var(--bg-color);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        /* Encabezado del autor */
        .author-header {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            align-items: flex-start;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 2rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 768px) {
            .author-header {
                flex-direction: row;
                align-items: center;
            }
        }

        .author-avatar img {
            border-radius: 50%;
            width: 96px;
            height: 96px;
            object-fit: cover;
            border: 3px solid var(--bg-light);
            box-shadow: var(--box-shadow);
        }

        .author-info h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
            color: var(--text-color);
        }

        .author-info p {
            margin: 0 0 1rem 0;
            color: var(--text-light);
            max-width: 600px;
        }

        .author-stats {
            list-style: none;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            font-size: 0.95rem;
        }

        .author-stats li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .author-stats strong {
            font-size: 1.1rem;
            color: var(--primary-color);
        }

        /* Secciones principales */
        .gris-gami {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        @media (min-width: 992px) {
            .gris-gami {
                grid-template-columns: 1fr 1fr;
                grid-column-gap: 3rem;
            }
        }

        .gris-span {
            grid-column: 1 / -1;
        }

        /* Estilos para Gamipress */
        .perfil-gamipress {
            width: 100%;
        }

        .perfil-gamipress h2 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--border-color);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .perfil-gamipress .bloque {
            margin-bottom: 2.5rem;
        }

        .perfil-gamipress .items {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        @media (min-width: 576px) {
            .perfil-gamipress .items {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
        }

        .perfil-gamipress .item {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: inherit;
            border: 1px solid var(--border-color);
            padding: 1rem;
            border-radius: var(--border-radius);
            background: var(--bg-light);
            transition: var(--transition);
        }

        .perfil-gamipress .item:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-color: var(--secondary-color);
        }

        .perfil-gamipress .item img,
        .perfil-gamipress .icono-fallback {
            width: 56px;
            height: 56px;
            object-fit: cover;
            border-radius: var(--border-radius);
            background: #e9ecef;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .perfil-gamipress .icono-fallback {
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            color: var(--text-light);
        }

        .perfil-gamipress .info {
            display: flex;
            flex-direction: column;
        }

        .perfil-gamipress .info strong {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .perfil-gamipress .info p {
            margin: 0;
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .perfil-gamipress .grupo-logros {
            margin-top: 1.5rem;
            width: 100%;
        }

        .perfil-gamipress .grupo-logros h3 {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: var(--text-color);
            border-bottom: 1px dashed var(--border-color);
            padding-bottom: 0.5rem;
        }

        .perfil-gamipress .vacio {
            font-style: italic;
            color: var(--text-lighter);
            padding: 1rem;
            text-align: center;
        }

        /* Secciones de contenido */
        .author-section {
            margin-bottom: 3rem;
        }

        .author-section h2 {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .author-posts {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .author-posts li {
            display: flex;
            align-items: flex-start;
            gap: 1.5rem;
            margin-bottom: 1.25rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--border-color);
            transition: var(--transition);
        }

        .author-posts li:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .author-posts li .thumb {
            flex-shrink: 0;
        }

        .author-posts li .thumb img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .author-posts li a {
            font-weight: 600;
            color: var(--text-color);
            text-decoration: none;
            transition: var(--transition);
            margin-bottom: 0.5rem;
            display: inline-block;
        }

        .author-posts li a:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }

        .meta {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-top: 0.25rem;
        }

        /* Estilos para las respuestas */
        .author-posts li.answer-item {
            flex-direction: column;
            gap: 0.5rem;
        }

        .answer-content {
            font-size: 0.95rem;
            color: var(--text-light);
            line-height: 1.5;
        }

        /* Efectos y transiciones */
        @media (hover: hover) {
            .perfil-gamipress .item:hover {
                transform: translateY(-3px);
            }

            .author-posts li a:hover {
                transform: translateX(3px);
            }
        }

        /* Iconos para las secciones */
        .author-section h2::before {
            display: inline-block;
            width: 1.5em;
            height: 1.5em;
            margin-right: 0.5rem;
        }

        .author-section:nth-of-type(1) h2::before {
            content: "📝";
        }

        .author-section:nth-of-type(2) h2::before {
            content: "❓";
        }

        .author-section:nth-of-type(3) h2::before {
            content: "💬";
        }
    </style>

<main class="author-page">
    <div class="container perfil-gamipress">
        <!-- Perfil del autor -->
        <section class="author-header">
            <?php echo get_avatar($author_id, 96); ?>
            <div class="author-info">
                <h1><?php echo esc_html($author->display_name); ?></h1>
                <?php if ($author->description): ?>
                    <p><?php echo esc_html($author->description); ?></p>
                <?php endif; ?>

                <ul class="author-stats">
                    <li><strong><?php echo $total_posts; ?></strong> publicaciones</li>
                    <li><strong><?php echo $total_questions; ?></strong> preguntas</li>
                    <li><strong><?php echo $total_answers; ?></strong> respuestas</li>
                </ul>
            </div>
        </section>

        <div class="gris-gami">
            <?php if (!empty($datos_usuario['puntos'])): ?>
                <section class="bloque">
                    <h2>Puntos</h2>
                    <div class="items">
                        <?php foreach ($datos_usuario['puntos'] as $key => $punto): ?>
                            <div class="item" title="<?php echo esc_attr($punto['nombre']['plural_name']); ?>">
                                <img src="<?php echo esc_url($punto['icono']); ?>" alt="<?php echo esc_attr($punto['nombre']['singular_name']); ?>">
                                <div class="info">
                                    <strong><?php echo esc_html($punto['nombre']['singular_name']); ?></strong>
                                    <p><?php echo intval($punto['cantidad']); ?> puntos</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- RANGOS -->
            <?php if (!empty($datos_usuario['rangos'])): ?>
                <section class="bloque">
                    <h2>Rangos</h2>
                    <div class="items">
                        <?php foreach ($datos_usuario['rangos'] as $key => $rango): ?>
                            <a class="item" href="<?php echo esc_url($rango['enlace']); ?>" title="<?php echo esc_attr($rango['rango_actual']); ?>">
                                <img src="<?php echo esc_url($rango['icono']); ?>" alt="<?php echo esc_attr($rango['rango_actual']); ?>">
                                <div class="info">
                                    <strong><?php echo esc_html($rango['nombre']['singular_name']); ?></strong>
                                    <p><?php echo esc_html($rango['rango_actual']); ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- LOGROS -->
            <?php if (!empty($datos_usuario['logros'])): ?>
                <?php
                // Filtra los logros con lista no vacía
                $logros_con_datos = array_filter($datos_usuario['logros'], function($tipo) {
                    return !empty($tipo['lista']);
                });
                ?>
                <?php if (!empty($logros_con_datos)): ?>
                    <section class="bloque gris-span">
                        <h2>Logros</h2>
                        <div class="items">
                            <?php foreach ($logros_con_datos as $tipo_logro): ?>
                                <div class="grupo-logros">
                                    <h3><?php echo esc_html($tipo_logro['nombre']['plural_name']); ?></h3>
                                    <?php foreach ($tipo_logro['lista'] as $logro): ?>
                                        <a class="item" href="<?php echo esc_url($logro['enlace']); ?>">
                                            <?php if (!empty($logro['icono'])): ?>
                                                <img src="<?php echo esc_url($logro['icono']); ?>" alt="<?php echo esc_attr($logro['titulo']); ?>">
                                            <?php else: ?>
                                                <div class="icono-fallback">🏆</div>
                                            <?php endif; ?>
                                            <div class="info">
                                                <strong><?php echo esc_html($logro['titulo']); ?></strong>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- ÚLTIMAS PUBLICACIONES -->
        <section class="author-section">
            <h2>📝 Últimas publicaciones</h2>
            <ul class="author-posts">
                <?php
                $posts = get_posts([
                    'author' => $author_id,
                    'post_type' => 'post',
                    'posts_per_page' => 5,
                ]);
                foreach ($posts as $post):
                    setup_postdata($post);
                    ?>
                    <li>
                        <?php if (has_post_thumbnail($post->ID)): ?>
                            <div class="thumb"><?php echo get_the_post_thumbnail($post->ID, 'thumbnail'); ?></div>
                        <?php endif; ?>
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        <div class="meta">Publicado el <?php echo get_the_date(); ?></div>
                    </li>
                <?php endforeach; wp_reset_postdata(); ?>
            </ul>
        </section>

        <!-- PREGUNTAS DEL AUTOR -->
        <section class="author-section">
            <h2>❓ Preguntas hechas</h2>
            <ul class="author-posts">
                <?php
                $preguntas = get_posts([
                    'author' => $author_id,
                    'post_type' => 'question',
                    'posts_per_page' => 5,
                ]);
                foreach ($preguntas as $post):
                    setup_postdata($post);
                    ?>
                    <li>
                        <?php if (has_post_thumbnail($post->ID)): ?>
                            <div class="thumb"><?php echo get_the_post_thumbnail($post->ID, 'thumbnail'); ?></div>
                        <?php endif; ?>
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        <div class="meta">Publicada el <?php echo get_the_date(); ?></div>
                    </li>
                <?php endforeach; wp_reset_postdata(); ?>
            </ul>
        </section>

        <!-- RESPUESTAS DEL AUTOR -->
        <section class="author-section">
            <h2>💬 Respuestas dadas</h2>
            <ul class="author-posts">
                <?php
                $respuestas = get_posts([
                    'author' => $author_id,
                    'post_type' => 'answer',
                    'posts_per_page' => 5,
                ]);
                foreach ($respuestas as $post):
                    setup_postdata($post);
                    $question_id = get_post_meta($post->ID, '_ap_question_id', true);
                    ?>
                    <li>
                        <a href="<?php echo get_permalink($question_id); ?>">
                            <?php echo wp_trim_words(strip_tags($post->post_content), 12, '...'); ?>
                        </a>
                        <div class="meta">Respondida el <?php echo get_the_date(); ?></div>
                    </li>
                <?php endforeach; wp_reset_postdata(); ?>
            </ul>
        </section>

    </div>
</main>

<?php get_footer(); ?>