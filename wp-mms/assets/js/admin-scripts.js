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
    });

    // --- BOM Repeater ---

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
                select.append($('<option>', {
                    value: component.id,
                    text: component.title
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
});