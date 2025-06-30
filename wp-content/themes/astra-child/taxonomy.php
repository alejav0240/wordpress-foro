<?php
get_header();
?>

<div class="container etiqueta-individual">
    <h1>🏷️ Etiqueta: <?php single_term_title(); ?></h1>

    <?php if (term_description()): ?>
        <div class="tag-description">
            <?php echo term_description(); ?>
        </div>
    <?php endif; ?>

    <?php if (have_posts()): ?>
        <ul class="preguntas-listado">
            <?php while (have_posts()):
                the_post(); ?>
                <li class="pregunta-item">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    <span class="pregunta-meta">🕒 <?php echo get_the_date(); ?> | 👤 <?php the_author(); ?></span>
                </li>
            <?php endwhile;
                the_posts_pagination([
                    'prev_text' => '« Anterior',
                    'next_text' => 'Siguiente »',
                ]);
            ?>
        </ul>
    <?php else: ?>
        <p>No hay preguntas con esta etiqueta.</p>
    <?php endif; ?>
</div>

<style>
    .etiqueta-individual {
        max-width: 960px;
        margin: auto;
        padding: 2rem;
    }

    .tag-description {
        margin-bottom: 1rem;
        color: #555;
        font-style: italic;
    }

    .preguntas-listado {
        list-style: none;
        padding: 0;
    }

    .pregunta-item {
        border-bottom: 1px solid #ddd;
        padding: 0.75rem 0;
    }

    .pregunta-item a {
        font-weight: bold;
        color: #0073aa;
        text-decoration: none;
    }

    .pregunta-item a:hover {
        text-decoration: underline;
    }

    .pregunta-meta {
        display: block;
        font-size: 0.85rem;
        color: #777;
    }
</style>

<?php
get_footer();
