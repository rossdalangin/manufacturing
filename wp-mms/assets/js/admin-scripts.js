jQuery(document).ready(function($) {
    // --- Purchase Order Repeater ---

    // Handle adding a new line item
    $('#add-line-item').on('click', function(e) {
        e.preventDefault();
        var template = $('#line-item-template').html();
        var newIndex = $('#line-items-container .line-item').length;
        template = template.replace(/{index}/g, newIndex);
        $('#line-items-container').append(template);
    });

    // Handle removing a line item
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

    // --- Requisition Repeater ---

    // Handle adding a new requisition item
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

    // Handle removing a requisition item
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

    // --- Scrap Repeater ---

    // Handle adding a new scrap item
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

    // Handle removing a scrap item
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
        });
    });

    // --- BOM Repeater ---

    // Make rows sortable
    $('#components-container').sortable({
        handle: '.component-handle',
        stop: function(event, ui) {
            // Re-index rows after sorting
            $('#components-container .component-item').each(function(index) {
                $(this).find('select, input').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        var newName = name.replace(/\[\d+\]/, '[' + index + ']');
                        $(this).attr('name', newName);
                    }
                });
            });
        }
    });

    // Handle adding a new component item
    $('#add-component-item').on('click', function(e) {
        e.preventDefault();
        var template = $('#component-item-template').html();
        var newIndex = $('#components-container .component-item').length;
        template = template.replace(/{index}/g, newIndex);

        // Append the new row
        var newRow = $(template);
        $('#components-container').append(newRow);

        // Populate the select dropdown in the new row
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

    // Handle removing a component item
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

                // Send the data via AJAX
                $.post(wp_mms_kanban_data.ajax_url, {
                    action: 'wp_mms_update_order_status',
                    order_id: orderId,
                    new_status: newStatus,
                    nonce: wp_mms_kanban_data.nonce
                }, function(response) {
                    if (!response.success) {
                        // On failure, alert the user and cancel the move to prevent a mismatch between the UI and the database.
                        alert('Error: ' + response.data);
                        $(ui.sender).sortable('cancel');
                    }
                });
            }
        }).disableSelection();
    }
});