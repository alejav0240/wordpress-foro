<?php
/*
Plugin Name: Importador y Exportador de Categorías Univalle
Description: Importa y exporta categorías desde y hacia un archivo CSV para preguntas y publicaciones de la carrera de Ingeniería de Sistemas - Univalle.
Version: 1.1
Author: Alejandro Chipana
*/

add_action('admin_menu', function () {
    add_menu_page(
        'Categorías Univalle',
        'Categorías Univalle',
        'manage_options',
        'importador-categorias-univalle',
        'icu_render_admin_page',
        'dashicons-database-import',
        80
    );
});

function icu_render_admin_page() {
    ?>
    <div class="wrap">
        <h1>Importar Categorías desde CSV</h1>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="categorias_csv" accept=".csv" required />
            <?php submit_button('Importar Categorías'); ?>
        </form>

        <hr>

        <h2>Exportar Categorías a CSV</h2>
        <form method="post">
            <input type="hidden" name="exportar_csv" value="1" />
            <?php submit_button('Exportar Categorías'); ?>
        </form>
    </div>
    <?php

    // === IMPORTACIÓN ===
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['categorias_csv'])) {
        $file = $_FILES['categorias_csv']['tmp_name'];

        if (($handle = fopen($file, "r")) !== false) {
            $row = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $row++;
                if ($row === 1) continue; // Saltar encabezado

                list($tipo, $nombre, $descripcion) = $data;

                $tipo = trim($tipo);
                $nombre = trim($nombre);
                $descripcion = trim($descripcion);

                if ($tipo === 'pregunta') {
                    $taxonomy = 'question_category'; // asegúrate de que existe
                } elseif ($tipo === 'publicacion') {
                    $taxonomy = 'category';
                } else {
                    continue;
                }

                if (!term_exists($nombre, $taxonomy)) {
                    wp_insert_term($nombre, $taxonomy, [
                        'description' => $descripcion,
                        'slug' => sanitize_title($nombre),
                    ]);
                }
            }
            fclose($handle);
            echo '<div class="notice notice-success"><p>Categorías importadas correctamente.</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>No se pudo leer el archivo CSV.</p></div>';
        }
    }

    // === EXPORTACIÓN ===
// === EXPORTACIÓN ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exportar_csv'])) {
    $filename = 'categorias_univalle_export_' . date('Y-m-d_H-i-s') . '.csv';

    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename=$filename");
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['tipo', 'nombre_categoria', 'slug', 'descripcion']);

    // Exportar categorías de publicaciones
    $categorias = get_terms([
        'taxonomy' => 'category',
        'hide_empty' => false,
    ]);

    foreach ($categorias as $cat) {
        fputcsv($output, ['publicacion', $cat->name, $cat->slug, $cat->description]);
    }

    // Exportar categorías de preguntas
    $preguntas = get_terms([
        'taxonomy' => 'question_category',
        'hide_empty' => false,
    ]);

    foreach ($preguntas as $cat) {
        fputcsv($output, ['pregunta', $cat->name, $cat->slug, $cat->description]);
    }

    fclose($output);
    exit;
}

}
