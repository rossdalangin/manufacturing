jQuery(document).ready(function($) {

    // --- Purchase Order Repeater ---
    $('#add-line-item').on('click', function(e) {
        e.preventDefault();
        var template = $('#line-item-template').html();
        var newIndex = $('#line-items-container .line-item').length;
        template = template.replace(/{index}/g, newIndex);
        $('#line-items-container').append(template);
    });

    $('#line-items-container').on('click', '.remove-line-item', function(e) {
        e.preventDefault();
        $(this).closest('.line-item').remove();
        // Re-index remaining rows
        $('#line-items-container .line-item').each(function(index) {
            $(this).find('select, input').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    var newName = name.replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', newName);
                }
            });
        });
    });

    // --- Requisition Repeater ---
    $('#add-requisition-item').on('click', function(e) {
        e.preventDefault();
        var template = $('#requisition-item-template').html();
        var newIndex = $('#requisition-items-container .requisition-item').length;
        template = template.replace(/{index}/g, newIndex);

        var newRow = $(template);
        $('#requisition-items-container').append(newRow);

        // Populate the select dropdown in the new row
        if (typeof wp_mms_data !== 'undefined' && typeof wp_mms_data.products !== 'undefined') {
            var select = newRow.find('.requisition-product-select');
            $.each(wp_mms_data.products, function(index, product) {
                select.append($('<option>', {
                    value: product.id,
                    text: product.title
                }));
            });
        }
    });

    $('#requisition-items-container').on('click', '.remove-requisition-item', function(e) {
        e.preventDefault();
        $(this).closest('.requisition-item').remove();
        // Re-index remaining rows
        $('#requisition-items-container .requisition-item').each(function(index) {
            $(this).find('select, input').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    var newName = name.replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', newName);
                }
            });
        });
    });

    // --- Scrap Repeater ---
    $('#add-scrap-item').on('click', function(e) {
        e.preventDefault();
        var template = $('#scrap-item-template').html();
        var newIndex = $('#scrap-items-container .scrap-item').length;
        template = template.replace(/{index}/g, newIndex);

        var newRow = $(template);
        $('#scrap-items-container').append(newRow);

        // Populate the select dropdown in the new row
        if (typeof wp_mms_data !== 'undefined' && typeof wp_mms_data.scrap_components !== 'undefined') {
            var select = newRow.find('.scrap-product-select');
            $.each(wp_mms_data.scrap_components, function(index, component) {
                select.append($('<option>', {
                    value: component.id,
                    text: component.title
                }));
            });
        }
    });

    $('#scrap-items-container').on('click', '.remove-scrap-item', function(e) {
        e.preventDefault();
        $(this).closest('.scrap-item').remove();
        // Re-index remaining rows
        $('#scrap-items-container .scrap-item').each(function(index) {
            $(this).find('select, input').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    var newName = name.replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', newName);
                }
            });
        });
    });

    // --- Production Order BOM Selector ---
    var bomSelector = {
        init: function() {
            $('#wp_mms_product_id').on('change', this.fetchBoms);
            this.populateInitialBoms();
        },

        fetchBoms: function() {
            var productId = $(this).val();
            var $bomSelect = $('#wp_mms_bom_id');
            $bomSelect.empty().append($('<option>', { value: '', text: 'Loading...' }));

            if (!productId) {
                $bomSelect.empty().append($('<option>', { value: '', text: 'Select a product first...' }));
                return;
            }

            $.post(ajaxurl, {
                action: 'get_boms_for_product',
                product_id: productId,
                nonce: wp_mms_data.get_boms_nonce
            }, function(response) {
                $bomSelect.empty();
                if (response.success && response.data.length) {
                    $bomSelect.append($('<option>', { value: '', text: 'Select a BOM Version' }));
                    $.each(response.data, function(index, bom) {
                        $bomSelect.append($('<option>', {
                            value: bom.id,
                            text: bom.title,
                            selected: wp_mms_data.selected_bom_id == bom.id
                        }));
                    });
                } else {
                    $bomSelect.append($('<option>', { value: '', text: 'No BOMs found for this product' }));
                }
            });
        },

        populateInitialBoms: function() {
            var initialProductId = $('#wp_mms_product_id').val();
            if (initialProductId) {
                $('#wp_mms_product_id').trigger('change');
            }
        }
    };

    if ($('#wp_mms_bom_id').length) {
        bomSelector.init();
    }

    // --- BOM Repeater ---
    $('#components-container').sortable({
        handle: '.component-handle',
        stop: function(event, ui) {
            // Re-index rows after sorting
            $('#components-container .component-item').each(function(index) {
                var componentIndex = index;
                $(this).find('select, input').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        var newName = name.replace(/\[\d+\]/, '[' + componentIndex + ']');
                        $(this).attr('name', newName);
                    }
                });
            });
        }
    });

    $('#components-container').on('click', '.add-alternative-item', function(e) {
        e.preventDefault();
        var $componentRow = $(this).closest('.component-item');
        var componentIndex = $componentRow.index();
        var template = $('#alternative-item-template').html().replace(/{component_index}/g, componentIndex);

        var $newAltRow = $(template);
        $componentRow.find('.alternatives-container').append($newAltRow);

        if (typeof wp_mms_data !== 'undefined' && typeof wp_mms_data.components !== 'undefined') {
            var select = $newAltRow.find('.alternative-product-select');
            $.each(wp_mms_data.components, function(index, component) {
                var displayText = component.title + ' (' + component.type + ')';
                select.append($('<option>', { value: component.id, text: displayText }));
            });
        }
    });

    $('#components-container').on('click', '.remove-alternative-item', function(e) {
        e.preventDefault();
        $(this).closest('.alternative-item').remove();
    });

    $('#add-component-item').on('click', function(e) {
        e.preventDefault();
        var template = $('#component-item-template').html();
        var newIndex = $('#components-container .component-item').length;
        template = template.replace(/{index}/g, newIndex);

        var newRow = $(template);
        $('#components-container').append(newRow);

        if (typeof wp_mms_data !== 'undefined' && typeof wp_mms_data.components !== 'undefined') {
            var select = newRow.find('.component-product-select');
            $.each(wp_mms_data.components, function(index, component) {
                var displayText = component.title + ' (' + component.type + ')';
                select.append($('<option>', {
                    value: component.id,
                    text: displayText
                }));
            });
        }
    });

    $('#components-container').on('click', '.remove-component-item', function(e) {
        e.preventDefault();
        $(this).closest('.component-item').remove();
        // Re-index remaining rows
        $('#components-container .component-item').each(function(index) {
            $(this).find('select, input').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    var newName = name.replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', newName);
                }
            });
        });
    });

    // --- Kanban Board ---
    if ($('#mms-kanban-board').length) {
        $('.kanban-column-body').sortable({
            connectWith: '.kanban-column-body',
            handle: '.kanban-card',
            placeholder: 'kanban-card-placeholder',
            forcePlaceholderSize: true,
            receive: function(event, ui) {
                var orderId = ui.item.data('order-id');
                var newStatus = $(this).data('status');

                $.post(wp_mms_kanban_data.ajax_url, {
                    action: 'wp_mms_update_order_status',
                    order_id: orderId,
                    new_status: newStatus,
                    nonce: wp_mms_kanban_data.nonce
                }, function(response) {
                    if (!response.success) {
                        alert('Error: ' + response.data);
                        $(ui.sender).sortable('cancel');
                    }
                });
            }
        }).disableSelection();
    }
});