<?php
/* Template Name: Categorías Personalizadas */
get_header(); ?>

<style>
    .categoria-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 4rem 1rem;
        min-width: 100%;
    }

    .categoria-title {
        font-size: 2.5rem;
        text-align: center;
        margin-bottom: 3rem;
        color: #222;
    }

    .categoria-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
    }

    .categoria-card {
        width: min-content;
        position: relative;
        background: #EAEAEA;
        border-radius: 1rem;
        padding: 2rem;
        color: white;
        text-decoration: none;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
    }

    .categoria-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2);
    }

    .categoria-card h2 {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    }

    .categoria-card p {
        font-size: 0.95rem;
        opacity: 0.85;
        margin-bottom: 1rem;
    }

    .categoria-badge {
        display: inline-block;
        background: rgba(0, 0, 0, 0.3);
        border-radius: 999px;
        padding: 0.4rem 0.8rem;
        font-size: 0.75rem;
        font-weight: bold;
    }

    @media (max-width: 600px) {
        .categoria-title {
            font-size: 2rem;
        }

        .categoria-card {
            padding: 1.5rem;
        }
    }
</style>

<div class="categoria-container">
    <h1 class="categoria-title">Categorías de Publicaciónes</h1>
    <div class="categoria-grid">
        <?php
        $categories = get_categories();

        if (!empty($categories)) {
            foreach ($categories as $category) {
                $category_link = get_category_link($category->term_id);
                $post_count = $category->count;
                ?>
                <a href="<?php echo esc_url($category_link); ?>" class="categoria-card">
                    <h2><?php echo esc_html($category->name); ?></h2>
                    <?php if ($category->description): ?>
                        <p><?php echo esc_html($category->description); ?></p>
                    <?php endif; ?>
                    <span class="categoria-badge">
                        <?php echo $post_count; ?> publicación<?php echo $post_count !== 1 ? 'es' : ''; ?>
                    </span>
                </a>
                <?php
            }
        } else {
            echo '<p style="text-align:center; color:#666;">No hay categorías disponibles.</p>';
        }
        ?>
    </div>
</div>

<?php get_footer(); ?>