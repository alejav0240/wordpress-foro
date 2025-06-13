<?php
/**
 * Template Name: Perfil UI Mejorado
 */
get_header();

$user_id = get_query_var('um_user');
if (!$user_id) {
    $user_id = get_current_user_id();
}
$user_info = get_userdata($user_id);

$username = get_query_var('user');

if (!$username) {
    $user = wp_get_current_user();
} else {
    $user = get_user_by('login', $username);
}

if (!$user) {
    echo 'Usuario no encontrado.';
} else {
    mostrar_perfil_general($user);
    $user_id = $user->ID;
    $datos_usuario = obtener_datos_gamipress_usuario($user_id);


}
?>

<style>
    .perfil-gamipress {
        font-family: sans-serif;
        max-width: 800px;
        margin: auto;
    }

    .perfil-gamipress h2 {
        font-size: 1.5rem;
        margin-bottom: 0.5em;
        border-bottom: 2px solid #ddd;
    }

    .perfil-gamipress .bloque {
        margin-bottom: 2em;
    }

    .perfil-gamipress .items {
        display: flex;
        flex-wrap: wrap;
        gap: 1em;
    }

    .perfil-gamipress .item {
        display: flex;
        align-items: center;
        text-decoration: none;
        color: inherit;
        border: 1px solid #eee;
        padding: 0.5em;
        border-radius: 8px;
        width: calc(50% - 1em);
        background: #fafafa;
        transition: box-shadow 0.2s ease;
    }

    .perfil-gamipress .item:hover {
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .perfil-gamipress .item img,
    .perfil-gamipress .icono-fallback {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: 8px;
        background: #ddd;
        margin-right: 0.75em;
    }

    .perfil-gamipress .icono-fallback {
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 24px;
    }

    .perfil-gamipress .info {
        display: flex;
        flex-direction: column;
    }

    .perfil-gamipress .grupo-logros {
        margin-top: 1em;
        width: 100%;
    }

    .perfil-gamipress .grupo-logros h3 {
        font-size: 1.1rem;
        margin-bottom: 0.5em;
    }

    .perfil-gamipress .vacio {
        font-style: italic;
        color: #888;
        margin-left: 1em;
    }
</style>

<div class="perfil-gamipress">
    <!-- PUNTOS -->
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
    <section class="bloque">
        <h2>Logros</h2>
        <div class="items">
            <?php foreach ($datos_usuario['logros'] as $tipo_logro): ?>
            <div class="grupo-logros">
                <h3><?php echo esc_html($tipo_logro['nombre']['plural_name']); ?></h3>
                <?php if (!empty($tipo_logro['lista'])): ?>
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
                <?php else: ?>
                <p class="vacio">Sin logros aún.</p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>
<?php
get_footer();
?> 
