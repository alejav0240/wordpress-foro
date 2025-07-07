// admin/lcr-admin.js

jQuery(document).ready(function($) {
    var ajaxurl = lcr_ajax_object.ajax_url;
    var nonce = lcr_ajax_object.nonce;
    var editingRuleData = lcr_ajax_object.editing_rule_data;

    function loadTaxonomies(postType, selectedTaxonomySlug) {
        if (!postType) {
            $('#lcr_taxonomy_slug').html('<option value="">Seleccione un tipo de contenido primero</option>').prop('disabled', true);
            $('#lcr_term_slug').html('<option value="">Seleccione una taxonomía primero</option>').prop('disabled', true);
            return;
        }
        $('#lcr_taxonomy_slug').prop('disabled', false).html('<option value="">Cargando taxonomías...</option>');
        $('#lcr_term_slug').prop('disabled', true).html('<option value="">Seleccione una taxonomía primero</option>');

        $.post(ajaxurl, {
            action: 'lcr_get_taxonomies',
            nonce: nonce,
            post_type: postType
        }, function(response) {
            var options = '<option value="">-- Seleccione una taxonomía --</option>';
            if (response.success && response.taxonomies.length > 0) {
                $.each(response.taxonomies, function(i, taxonomy) {
                    var selected = (selectedTaxonomySlug && selectedTaxonomySlug === taxonomy.slug) ? 'selected' : '';
                    options += '<option value="' + taxonomy.slug + '" ' + selected + '>' + taxonomy.label + '</option>';
                });
            } else {
                options = '<option value="">No se encontraron taxonomías</option>';
            }
            $('#lcr_taxonomy_slug').html(options);

            if (selectedTaxonomySlug) {
                loadTerms($('#lcr_taxonomy_slug').val(), editingRuleData ? editingRuleData.term_slug : null);
            }
        });
    }

    function loadTerms(taxonomySlug, selectedTermSlug) {
        if (!taxonomySlug) {
            $('#lcr_term_slug').html('<option value="">Seleccione una taxonomía primero</option>').prop('disabled', true);
            return;
        }
        $('#lcr_term_slug').prop('disabled', false).html('<option value="">Cargando términos...</option>');

        $.post(ajaxurl, {
            action: 'lcr_get_terms',
            nonce: nonce,
            taxonomy_slug: taxonomySlug
        }, function(response) {
            var options = '<option value="">-- Seleccione un término --</option>';
            if (response.success && response.terms.length > 0) {
                $.each(response.terms, function(i, term) {
                    var selected = (selectedTermSlug && selectedTermSlug === term.slug) ? 'selected' : '';
                    options += '<option value="' + term.slug + '" ' + selected + '>' + term.label + '</option>';
                });
            } else {
                options = '<option value="">No se encontraron términos</option>';
            }
            $('#lcr_term_slug').html(options);
        });
    }

    // Event listeners
    $('#lcr_parent_post_type').on('change', function() {
        loadTaxonomies($(this).val(), null);
    });

    $('#lcr_taxonomy_slug').on('change', function() {
        loadTerms($(this).val(), null);
    });

    // Cargar datos al cargar la página si estamos editando
    if (editingRuleData) {
        $('#lcr_parent_post_type').val(editingRuleData.parent_post_type);
        loadTaxonomies(editingRuleData.parent_post_type, editingRuleData.taxonomy_slug);
        // loadTerms para el término se llama dentro de loadTaxonomies si hay un selectedTaxonomySlug
    } else {
        // Cargar las opciones iniciales si no estamos editando
        loadTaxonomies($('#lcr_parent_post_type').val(), null);
    }
});