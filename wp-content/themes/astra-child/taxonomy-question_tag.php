<?php
get_header();

$term = get_queried_object(); // Obtiene la categoría o etiqueta actual
$order = $_GET['orderby'] ?? 'date';
$paged = get_query_var('paged') ?: 1;

$args = [
    'post_type' => 'question',
    'tax_query' => [[
        'taxonomy' => get_query_var('taxonomy'),
        'field'    => 'slug',
        'terms'    => $term->slug,
    ]],
    'orderby' => $order,
    'order' => 'DESC',
    'posts_per_page' => 10,
    'paged' => $paged
];

$query = new WP_Query($args);
?>
<style>
    .pagina-preguntas {
        max-width: 900px;
        margin: 0 auto;
        padding: 3rem 1rem;
        min-height: 100vh;
    }

    .encabezado-preguntas {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    .encabezado-preguntas h1 {
        font-size: 2.25rem;
        color: #1a202c;
        font-weight: 700;
        margin: 0;
    }

    .boton-hacer-pregunta {
        background-color: #ff002fa8;
        color: #fff;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        text-decoration: none;
        font-weight: 500;
        transition: background-color 0.3s ease;
        margin-top: 0.5rem;
    }

    .boton-hacer-pregunta:hover {
        color: #fff;
        background-color: #bf0628;
    }

    .lista-preguntas {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .pregunta-tarjeta {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.5rem;
        transition: box-shadow 0.2s ease;
    }

    .pregunta-tarjeta:hover {
        box-shadow: 0 6px 18px rgba(0,0,0,0.06);
    }

    .pregunta-cabecera {
        margin-bottom: 0.75rem;
    }

    .titulo-pregunta {
        font-size: 1.4rem;
        font-weight: 600;
        color: #bf0628;
        text-decoration: none;
    }

    .titulo-pregunta:hover {
        text-decoration: underline;
    }

    .meta-pregunta {
        font-size: 0.9rem;
        color: #718096;
        margin-top: 0.25rem;
    }

    .descripcion-pregunta {
        font-size: 1rem;
        color: #2d3748;
        margin-top: 0.75rem;
        line-height: 1.5;
    }

    .informacion-pregunta {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        font-size: 0.95rem;
        color: #4a5568;
        margin-top: 1rem;
    }

    .sin-preguntas {
        text-align: center;
        color: #4a5568;
        font-size: 1.1rem;
        margin-top: 2rem;
    }

    .filter-form {
        margin-bottom: 1.5rem;
        display: flex;
        gap: 1rem;
        align-items: center;
        font-size: 0.95rem;
    }

    .filter-form select {
        padding: 0.4rem 0.6rem;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

</style>

<main class="pagina-preguntas">
    <div class="container">
        <header class="encabezado-preguntas">
            <h1>📚 Preguntas de la Comunidad con Etiqueta :<?php echo esc_html($term->name); ?></h1>
            <?php if ($term->description): ?>
                <p><?php echo esc_html($term->description); ?></p>
            <?php endif; ?>
        </header>

        <form method="get" class="filter-form">
            <label for="orderby">Ordenar por:</label>
            <select name="orderby" id="orderby" onchange="this.form.submit()">
                <option value="date" <?php selected($order, 'date'); ?>>Más recientes</option>
                <option value="title" <?php selected($order, 'title'); ?>>Título A-Z</option>
            </select>
        </form>

        <?php if ($query->have_posts()): ?>
            <div class="lista-preguntas">
                <?php while ($query->have_posts()): $query->the_post();
                    $autor = get_the_author();
                    $fecha = get_the_date();
                    $link = get_permalink();
                    $votos = function_exists('ap_get_vote_count') ? ap_get_vote_count(get_the_ID()) : 0;
                    $respuestas = function_exists('ap_get_answer_count') ? ap_get_answer_count(get_the_ID()) : 0;
                    $excerpt = wp_trim_words(get_the_content(), 25);

                    $categorias = get_the_term_list(get_the_ID(), 'question_category', '', ', ');
                    $etiquetas = get_the_term_list(get_the_ID(), 'question_tag', '', ', ');
                    ?>

                    <article class="pregunta-tarjeta">
                        <header class="pregunta-cabecera">
                            <a href="<?php echo esc_url($link); ?>" class="titulo-pregunta">
                                <?php echo esc_html(get_the_title()); ?>
                            </a>
                            <div class="meta-pregunta">
                                <span>Publicado por <strong><?php echo esc_html($autor); ?></strong></span> |
                                <span><?php echo esc_html($fecha); ?></span>
                            </div>
                        </header>

                        <div class="contenido-pregunta">
                            <p class="descripcion-pregunta"><?php echo esc_html($excerpt); ?></p>
                            <div class="informacion-pregunta">
                                <span class="info-item">🔼 <?php echo intval($votos); ?> votos</span>
                                <span class="info-item">💬 <?php echo intval($respuestas); ?> respuestas</span>
                                <?php if (!is_wp_error($categorias) && $categorias): ?>
                                    <span class="info-item">📂 <?php echo $categorias; ?></span>
                                <?php endif; ?>
                                <?php if (!is_wp_error($etiquetas) && $etiquetas): ?>
                                    <span class="info-item">🏷️ <?php echo $etiquetas; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <div class="pagination">
                <?php
                the_posts_pagination([
                    'mid_size' => 2,
                    'prev_text' => '← Anterior',
                    'next_text' => 'Siguiente →',
                ]);
                ?>
            </div>
        <?php else: ?>
            <p class='sin-preguntas'>No hay preguntas aún.</p>
        <?php endif; ?>

        <?php wp_reset_postdata(); ?>
    </div>
</main>

<?php get_footer(); ?>
