<?php get_header(); ?>

<main class="search-results container">
  <div class="search-header">
    <h1>Resultados para: <span class="search-term">"<?php echo esc_html(get_search_query()); ?>"</span></h1>
    <?php global $wp_query; ?>
    <p class="results-count"><?php echo $wp_query->found_posts; ?> resultado(s) encontrado(s)</p>
  </div>

  <?php if (have_posts()) : ?>
    <div class="result-list">
      <?php while (have_posts()) : the_post(); ?>
        <article class="result-card">
          <a href="<?php the_permalink(); ?>" class="result-link">
            <?php if (has_post_thumbnail()) : ?>
              <div class="result-thumbnail">
                <?php the_post_thumbnail('medium'); ?>
              </div>
            <?php endif; ?>
            <div class="result-content">
              <h2><?php the_title(); ?></h2>
              <p class="excerpt"><?php echo wp_trim_words(get_the_excerpt(), 25); ?></p>
              <div class="result-meta">
                <span class="post-type">
                  <?php 
                    $post_type = get_post_type_object(get_post_type())->labels->singular_name;
                    echo ($post_type === 'Entrada') ? 'Publicación' : $post_type;
                  ?>
                </span>
                <span class="post-date"><?php echo get_the_date(); ?></span>
              </div>
            </div>
          </a>
        </article>
      <?php endwhile; ?>
    </div>

    <div class="pagination">
      <?php the_posts_pagination(array(
        'mid_size' => 2,
        'prev_text' => '← Anterior',
        'next_text' => 'Siguiente →',
      )); ?>
    </div>

  <?php else : ?>
    <div class="no-results">
      <h2>Sin resultados</h2>
      <p>No se encontraron publicaciones que coincidan con tu búsqueda.</p>
      <a href="<?php echo home_url(); ?>" class="btn-home">Volver al inicio</a>
    </div>
  <?php endif; ?>
</main>

<style>
body {
  background-color: #f5f7fa;
  font-family: 'Segoe UI', sans-serif;
}

.container {
  max-width: 1200px;
  margin: auto;
  padding: 2rem;
}

.search-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.search-term {
  color: #0077cc;
  font-weight: 600;
}

.results-count {
  color: #666;
  font-size: 1rem;
  margin-top: 0.5rem;
}

.result-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 2rem;
}

.result-card {
  background: #ffffff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.result-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.result-link {
  text-decoration: none;
  color: inherit;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.result-thumbnail img {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.result-content {
  padding: 1.5rem;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.result-content h2 {
  font-size: 1.3rem;
  margin: 0 0 1rem;
  color: #222;
}

.excerpt {
  color: #555;
  margin-bottom: 1rem;
  line-height: 1.6;
  font-size: 0.95rem;
}

.result-meta {
  display: flex;
  justify-content: space-between;
  color: #999;
  font-size: 0.85rem;
}

.pagination {
  margin-top: 3rem;
  text-align: center;
}

.pagination .nav-links {
  display: inline-flex;
  gap: 1rem;
  background: #fff;
  padding: 0.75rem 1.25rem;
  border-radius: 8px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.pagination a,
.pagination span {
  padding: 0.5rem 0.9rem;
  border-radius: 6px;
  color: #0077cc;
  font-weight: 500;
  transition: background 0.2s;
}

.pagination .current {
  background: #0077cc;
  color: #fff;
}

.pagination a:hover {
  background: #e6f0ff;
}

.no-results {
  text-align: center;
  background: #fff;
  padding: 3rem;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.no-results h2 {
  color: #333;
  margin-bottom: 1rem;
}

.btn-home {
  display: inline-block;
  margin-top: 1rem;
  background: #0077cc;
  color: #fff;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  transition: background 0.3s;
}

.btn-home:hover {
  background: #005fa3;
}

@media (max-width: 768px) {
  .result-list {
    grid-template-columns: 1fr;
  }
}
</style>

<?php get_footer(); ?>
