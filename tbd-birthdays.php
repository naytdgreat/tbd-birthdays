<?php

/**
 * Plugin Name: TBD Birthdays
 * Description: Celebrate People's birthday on your website
 * Author: Joshua Ibrahim
 * Author URI: https://whitelaketechnologies.com
 * Version: 1.0.0
 * Text Domain: tbd-birthdays
 */

if (!defined('ABSPATH')) {
    echo "What are you trying to do yeah?";
    exit;
}

class TbdBirthdays
{
    public function __construct()
    {
        add_action('init', array($this, 'create_custom_post_type'));
        add_action('add_meta_boxes', array($this, 'add_birthdays_meta_boxes'));
        add_action('save_post', array($this, 'save_birthdays_meta'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_media_uploader'));
        add_action('rest_api_init', array($this, 'register_custom_fields'));
    }

    public function create_custom_post_type()
    {
        $args = array(
            'public' => true,
            'has_archive' => true,
            'supports' => array('title'), // Support for title
            'labels' => array(
                'name' => 'TBD Birthdays',
                'singular_name' => 'Birthday Entry'
            ),
            'menu_icon' => 'dashicons-buddicons-community',
            'show_in_rest' => true, // Enable REST API support
            'rest_base' => 'birthdays', // Optional: specify a custom base for the endpoint
        );

        register_post_type('tbd_birthdays', $args);
    }

    public function add_birthdays_meta_boxes()
    {
        add_meta_box(
            'birthdays_details',
            'Birthday Details',
            array($this, 'birthdays_meta_box_callback'),
            'tbd_birthdays',
            'normal',
            'high'
        );
    }

    public function enqueue_media_uploader()
    {
        wp_enqueue_media();
        wp_enqueue_script(
            'birthdays-image-uploader',
            plugins_url('birthday-image-uploader.js', __FILE__),
            array('jquery'),
            null,
            true
        );
    }

    public function birthdays_meta_box_callback($post)
    {
        wp_nonce_field('save_birthdays_meta', 'birthdays_meta_nonce');

        $full_name = get_post_meta($post->ID, '_full_name', true);
        $birthday_message = get_post_meta($post->ID, '_birthday_message', true);
        $image = get_post_meta($post->ID, '_birthday_image', true); // Fetch the image URL

?>
        <p>
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo esc_attr($full_name); ?>" style="width:100%;" required>
        </p>
        <p>
            <label for="birthday_message">Birthday Message:</label>
            <textarea id="birthday_message" name="birthday_message" style="width:100%;" required><?php echo esc_textarea($birthday_message); ?></textarea>
        </p>
        <p>
            <label for="birthday_image">Birthday Photo:</label>
            <input type="text" id="birthday_image" name="birthday_image" value="<?php echo esc_url($image); ?>" style="width:70%;">
            <button type="button" id="birthday_image_button" class="button">Select Image</button>
        </p>
<?php
    }

    public function save_birthdays_meta($post_id)
    {
        if (!isset($_POST['birthdays_meta_nonce']) || !wp_verify_nonce($_POST['birthdays_meta_nonce'], 'save_birthdays_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save Full Name
        if (!empty($_POST['full_name'])) {
            update_post_meta($post_id, '_full_name', sanitize_text_field($_POST['full_name']));
        }

        // Save Birthday Message
        if (isset($_POST['birthday_message'])) {
            update_post_meta($post_id, '_birthday_message', sanitize_textarea_field($_POST['birthday_message']));
        }

        // Save Birthday Image
        if (isset($_POST['birthday_image'])) {
            update_post_meta($post_id, '_birthday_image', esc_url_raw($_POST['birthday_image']));
        }
    }

    public function register_custom_fields()
    {
        register_rest_field('tbd_birthdays', 'full_name', array(
            'get_callback' => function ($data) {
                return get_post_meta($data['id'], '_full_name', true);
            },
        ));

        register_rest_field('tbd_birthdays', 'birthday_message', array(
            'get_callback' => function ($data) {
                return get_post_meta($data['id'], '_birthday_message', true);
            },
        ));

        register_rest_field('tbd_birthdays', 'birthday_image', array(
            'get_callback' => function ($data) {
                return get_post_meta($data['id'], '_birthday_image', true);
            },
        ));
    }
}

new TbdBirthdays;
