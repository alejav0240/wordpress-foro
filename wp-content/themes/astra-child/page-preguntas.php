<?php
/* Template Name: Página de Preguntas Personalizada */
get_header();
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

</style>

<div class="pagina-preguntas">
  <div class="encabezado-preguntas">
    <h1>📚 Preguntas de la Comunidad</h1>
    <a href="<?php echo esc_url(site_url('/ask')); ?>" class="boton-hacer-pregunta">
      + Hacer una pregunta
    </a>
  </div>

  <?php
  $args = [
    'post_type' => 'question',
    'post_status' => 'publish',
    'posts_per_page' => 10,
  ];
  $preguntas = new WP_Query($args);

  if ($preguntas->have_posts()):
    echo '<div class="lista-preguntas">';
    while ($preguntas->have_posts()):
      $preguntas->the_post();
      $autor = get_the_author();
      $fecha = get_the_date();
      $link = get_permalink();
      $votos = ap_get_votes($preguntas->post->ID);
      $views= ap_get_post_field( 'views' );
      $last_active = ap_get_recent_activity();
      $respuestas = ap_get_answers_count();
      $excerpt = wp_trim_words(get_the_content(), 25);
      $last_active_fecha = ap_get_last_active( get_question_id() );
      $activity_name = get_user($last_active->user_id)->user_nicename;
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
              <span><?php echo ($fecha); ?></span> |
            <span><?php echo ($last_active_fecha); ?> <strong><?php echo ($last_active->action['verb']); ?></strong> por  <?php echo ($activity_name); ?></span>
          </div>
        </header>

        <div class="contenido-pregunta">
          <p class="descripcion-pregunta"><?php echo esc_html($excerpt); ?></p>
          <div class="informacion-pregunta">
            <span class="info-item">🔼 <?php echo intval(count($votos)); ?> votos</span>
            <span class="info-item">💬 <?php echo intval($respuestas); ?> respuestas</span>
            <span class="info-item">👁️ <?php echo intval($views); ?> vistas </span>
            <?php if (!is_wp_error($categorias) && $categorias): ?>
              <span class="info-item">📂 <?php echo $categorias; ?></span>
            <?php endif; ?>
            <?php if (!is_wp_error($etiquetas) && $etiquetas): ?>
              <span class="info-item">🏷️ <?php echo $etiquetas; ?></span>
            <?php endif; ?>
          </div>
        </div>
      </article>
      <?php
    endwhile;
    echo '</div>';
    wp_reset_postdata();
  else:
    echo "<p class='sin-preguntas'>No hay preguntas aún.</p>";
  endif;
  ?>
</div>

<?php get_footer(); ?>