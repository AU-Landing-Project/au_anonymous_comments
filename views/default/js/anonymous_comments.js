define(['require', 'jquery'], function(require, $) {

    $('.moderation-controls').insertBefore($('form.elgg-form-comment-save'));

    $(document).on('click', '.moderated-comment input[type="checkbox"]', function(e) {
        var val = $(this).val();
        var $hidden_inputs = $('.moderation-controls .hidden-inputs');
        if ($(this).is(':checked')) {
            
            // add hidden input to our form
            if (!$('input[value="'+val+'"]', $hidden_inputs).length) {
                var $html = '<input type="hidden" name="guid[]" value="'+val+'">';
                $($html).appendTo($hidden_inputs);
            }
            
        }
        else {
            
            // remove hidden input from our form
            if ($('input[value="'+val+'"]', $hidden_inputs).length) {
                $('input[value="'+val+'"]', $hidden_inputs).remove();
            }
            
        }
    });
});