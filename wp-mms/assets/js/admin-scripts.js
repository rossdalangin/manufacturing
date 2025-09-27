jQuery(document).ready(function($) {
    // Handle adding a new line item
    $('#add-line-item').on('click', function(e) {
        e.preventDefault();

        // Get the template
        var template = $('#line-item-template').html();

        // Create a new index for the row
        var newIndex = $('#line-items-container .line-item').length;

        // Replace the placeholder index with the new index
        template = template.replace(/{index}/g, newIndex);

        // Append the new row to the table
        $('#line-items-container').append(template);
    });

    // Handle removing a line item
    $('#line-items-container').on('click', '.remove-line-item', function(e) {
        e.preventDefault();

        // Remove the parent table row
        $(this).closest('.line-item').remove();

        // After removing, re-index the remaining rows to ensure data saves correctly
        $('#line-items-container .line-item').each(function(index) {
            $(this).find('select, input').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    // Replace the index in the name attribute (e.g., wp_mms_line_items[0][product_id])
                    var newName = name.replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', newName);
                }
            });
        });
    });
});