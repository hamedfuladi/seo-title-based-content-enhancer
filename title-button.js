(function() {
    tinymce.PluginManager.add('seo_title_based_content_enhancer_title_button', function(editor, url) {
        editor.addButton('seo_title_based_content_enhancer_title_button', {
            title: 'Insert Title',
            text: 'Dynamic Title',
            onclick: function() {
                editor.insertContent('[title]');
            }
        });
    });
})();
