<?php
/* Template Name: Página de Preguntas Personalizada */
get_header();
?>
<div class="container-preguntas">
  <div class="preguntas-header">
    <h1>📚 Preguntas de la Comunidad</h1>
    <a href="<?php echo site_url('/ask'); ?>" class="btn-ask">➕ Hacer una pregunta</a>
  </div>

  <?php
  $args = [
    'post_type' => 'question',
    'post_status' => 'publish',
    'posts_per_page' => 10,
  ];
  $preguntas = new WP_Query($args);

  if ($preguntas->have_posts()) :
    while ($preguntas->have_posts()) : $preguntas->the_post();
      $autor = get_the_author();
      $fecha = get_the_date();
      $link = get_permalink();
      $votos = function_exists('ap_get_vote_count') ? ap_get_vote_count(get_the_ID()) : 0;
      $respuestas = function_exists('ap_get_answer_count') ? ap_get_answer_count(get_the_ID()) : 0;

      // Obtener categorías y etiquetas con seguridad
      $categorias = get_the_term_list(get_the_ID(), 'question_category', '', ', ');
      $etiquetas = get_the_term_list(get_the_ID(), 'question_tag', '', ', ');

      $excerpt = wp_trim_words(get_the_content(), 25);
  ?>
      <div class="pregunta-card">
        <div class="pregunta-header">
          <a href="<?php echo esc_url($link); ?>" class="pregunta-titulo"><?php the_title(); ?></a>
          <div class="pregunta-meta">
            <span>Publicado por <strong><?php echo esc_html($autor); ?></strong></span> |
            <span><?php echo esc_html($fecha); ?></span>
          </div>
        </div>

        <div class="pregunta-body">
          <p class="pregunta-descripcion"><?php echo esc_html($excerpt); ?></p>
          <div class="pregunta-info">
            <span class="info-item">🔼 <?php echo intval($votos); ?> votos</span>
            <span class="info-item">💬 <?php echo intval($respuestas); ?> respuestas</span>
            <?php if (!is_wp_error($categorias) && $categorias) : ?>
              <span class="info-item">📂 <?php echo $categorias; ?></span>
            <?php endif; ?>
            <?php if (!is_wp_error($etiquetas) && $etiquetas) : ?>
              <span class="info-item">🏷️ <?php echo $etiquetas; ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
  <?php
    endwhile;
    wp_reset_postdata();
  else :
    echo "<p class='no-questions'>No hay preguntas aún.</p>";
  endif;
  ?>
</div>

<?php get_footer(); ?>
