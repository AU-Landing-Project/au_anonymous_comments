define(['require', 'jquery'], function(require, $) {

    $('.moderation-controls').insertBefore($('form.elgg-form-comment-save'));

    $(document).on('click', '.comment_moderate input[type="checkbox"]', function(e) {
        if ($(this).is(':checked')) {
            
        }
        else {
            
        }
    });
});