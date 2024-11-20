jQuery(document).ready(function($) {
    let mediaUploader;

    $('#birthday_image_button').click(function(e) {
        e.preventDefault();

        // If the uploader object has already been created, reopen the dialog
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        // Extend the wp.media object
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Birthday Image',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        // When an image is selected, grab the URL and set it as the value of the input field
        mediaUploader.on('select', function() {
            const attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#birthday_image').val(attachment.url);
        });

        // Open the uploader dialog
        mediaUploader.open();
    });
});
