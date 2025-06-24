<?php
/**
 * Plugin Name: Custom Contact Form
 * Plugin URI:  Plugin URL Link
 * Author:      Nayem Hasan Bijoy
 * Author URI:  Plugin Author Link
 * Description: This is custom form plugin
 * Version:     0.1.0
 * License:     GPL-2.0+
 * License URL: http://www.gnu.org/licenses/gpl-2.0.txt
 * text-domain: ccf
*/

// Prevent direct access
if (!defined('ABSPATH')) exit;

// Create table on plugin activation
register_activation_hook(__FILE__, 'ccf_activate_plugin');

function ccf_activate_plugin() {
    global $wpdb;
    $table = $wpdb->prefix . 'ccf_submissions';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        email varchar(100) NOT NULL,
        message text NOT NULL,
        submitted_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/** Submission Hendler and Data sanitization*/

function ccf_handle_form_submission(){
    if(!isset($_POST['ccf_form_nonce_field']) || !wp_verify_nonce($_POST['ccf_form_nonce_field'],'ccf_form_nonce_action')){
        wp_die('Security Check Faild');
    }
    if(!empty($_POST['ccf_honey'])){
        wp_die('Bot Detected.');
    }

    $name = sanitize_text_field($_POST['ccf_name']);
    $email = sanitize_email($_POST['ccf_email']);
    $message = sanitize_textarea_field($_POST['ccf_message']);

    if(empty($name) || !is_email($email) || empty($message)){
        wp_die('Invalid Input');
    }
    global $wpdb;

    $table = $wpdb->prefix . 'ccf_submissions';

    $wpdb->insert(
        $table,
        array(
            'name' => $name,
            'email' => $email,
            'message' => $message,
            'submitted_at' => current_time('mysql'),
        ),
        array('%s','%s','%s','%s')
    );

    /** Send mail */

    $to = get_option('admin_email');
    $subject = 'New contact form submission';
    $body = "Name:$name\n Email: $email\n Massage:$message";
    $headers = array('Content_Type: text/plain; charset=UTF-8');

    wp_mail($to,$subject,$body,$headers);
    wp_redirect(home_url('/Thank-you'));
    exit;
}
add_action('admin_post_nopriv_ccf_handle_form', 'ccf_handle_form_submission');
add_action('admin_post_ccf_handle_form', 'ccf_handle_form_submission');

/** Enqueue Style */

function ccf_enqueue_style(){
    wp_enqueue_style('CCF-style', plugins_url('CCF-style.css',__FILE__));
}
add_action('wp_enqueue_scripts','ccf_enqueue_style');


/**Adding Admin Page */

function ccf_add_theme_page(){
    add_menu_page('From for admin','Form','manage_options','custom-contact-page','ccf_create_page','dashicons-forms','101');
}
add_action('admin_menu','ccf_add_theme_page');

/** Enqueue admin page Style */

function ccf_enqueue_admin_page_style(){
    wp_enqueue_style('CCF-admin-page-style', plugins_url('CCF-admin-page-style.css',__FILE__));
}
add_action('admin_enqueue_scripts','ccf_enqueue_admin_page_style');

function ccf_create_page(){
    global $wpdb;
    $table = $wpdb->prefix . 'ccf_submissions';

    $results = $wpdb->get_results("SELECT * FROM $table ORDER BY submitted_at ASC"); 

    ?>
    <div class="ccf_main_area">
        <div class="ccf_body_area ccf_common">
            <h3 id="title"><?php echo esc_attr('Form page customizar'); ?></h3>
            <form action="options.php" method="post">
                <?php wp_nonce_field('update-options');?>

                <!-- background color -->
                <label for="ccf_background_color">
                    <?php echo esc_attr('Background Color :');?>
                </label>
                <input type="color" name="ccf_background_color" value="<?php echo get_option('ccf_background_color'); ?>">

                <!-- container color -->
                <label for="ccf_container_color">
                    <?php echo esc_attr('Container Color :');?>
                </label>
                <input type="color" name="ccf_container_color" value="<?php echo get_option('ccf_container_color'); ?>">

                <!-- container text color -->
                <label for="ccf_container_text_color">
                    <?php echo esc_attr('Container Text Color :');?>
                </label>
                <input type="color" name="ccf_container_text_color" value="<?php echo get_option('ccf_container_text_color'); ?>">                

                <!-- button color -->
                <label for="ccf_button_color">
                    <?php echo esc_attr('Button Color :');?>
                </label>
                <input type="color" name="ccf_button_color" value="<?php echo get_option('ccf_button_color'); ?>">

                <!-- button text color -->
                <label for="ccf_button_text_color">
                    <?php echo esc_attr('Button Text Color :');?>
                </label>
                <input type="color" name="ccf_button_text_color" value="<?php echo get_option('ccf_button_text_color'); ?>">

                <!-- container width -->
                <label for="ccf_container_width">
                    <?php echo esc_attr('Container Max Width :');?>
                </label>
                <input type="text" name="ccf_container_width" value="<?php echo get_option('ccf_container_width'); ?>">

                <!-- button width -->
                <label for="ccf_button_width">
                    <?php echo esc_attr('Button Width :');?>
                </label>
                <input type="text" name="ccf_button_width" value="<?php echo get_option('ccf_button_width'); ?>">

                <!-- button height -->
                <label for="ccf_button_height">
                    <?php echo esc_attr('Button Height :');?>
                </label>
                <input type="text" name="ccf_button_height" value="<?php echo get_option('ccf_button_height'); ?>">                                                                                               

                <input type="hidden" name="action" value="update">
                <input type="hidden" name="page_options" value="ccf_background_color,
                ccf_container_color,ccf_button_color,ccf_container_text_color,ccf_container_width,
                ccf_button_width,ccf_button_height,ccf_button_text_color">

                <input type="submit" name="submit" class="button button-primary" value="<?php _e('Save Changes','ccf') ?>">
            </form>
        </div>
        <div class="ccf_sidebar_area ccf_common">
            <h3 id="title"><?php echo esc_attr('About This Plugin'); ?></h3>
            <p><b>Custom Form Plugin</b> is a lightweight and easy-to-use WordPress plugin that allows you to create and 
                manage contact forms with full customization. Whether you're building a simple contact form or a 
                more advanced input form, this plugin gives you complete control over form fields, layout, and 
                submission behavior.</p>
        </div>
    </div>

    <!-- /**Form Submission table Data */ -->

    <div class="wrap">
        <h2><?php _e('Users Submission Data','ccf');?></h2>
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <td><?php _e('ID','ccf'); ?></td>
                    <td><?php _e('Name','ccf'); ?></td>
                    <td><?php _e('Email','ccf'); ?></td>
                    <td><?php _e('Message','ccf'); ?></td>
                    <td><?php _e('Submitted At','ccf'); ?></td>
                </tr>
            </thead>
            <tbody>
                <?php if ($results): ?>
                    <?php foreach($results as $row): ?>
                        <tr>
                            <td><?php echo esc_html($row->id); ?></td>
                            <td><?php echo esc_html($row->name); ?></td>
                            <td><?php echo esc_html($row->email); ?></td>
                            <td><?php echo esc_html(wp_trim_words($row->message)); ?></td>
                            <td><?php echo esc_html($row->submitted_at); ?></td>
                        </tr>
                    <?php endforeach ?>
                    <?php else: ?>
                        <tr>
                            <td><?php _e('No Submisstion Found', 'ccf') ?></td>
                        </tr>
                    <?php endif?>        
            </tbody>
        </table>
    </div>
    <?php
}

function ccf_custom_enqueue_register(){
    wp_enqueue_style('login_enqueue_register', plugins_url('CCF-admin-page-style.css',__FILE__),false,'0.1.0');
}
add_action('login_enqueue_scripts','ccf_custom_enqueue_register');

function ccf_custom_user_interface(){
    ?>
    <style>
        body {
            background-color: <?php echo esc_attr(get_option('ccf_background_color','#fff'));?> !important;
        }
        .container {
            background-color: <?php echo esc_attr(get_option('ccf_container_color','#fff'));?> !important;
            color: <?php echo esc_attr(get_option('ccf_container_text_color','#fff'));?> !important;
            max-width: <?php echo esc_attr(get_option('ccf_container_width','#fff'));?> !important;
        }
        .submit {
            background-color: <?php echo esc_attr(get_option('ccf_button_color','#fff'));?> !important;
            color: <?php echo esc_attr(get_option('ccf_button_text_color','#fff'));?> !important;
            width: <?php echo esc_attr(get_option('ccf_button_width'));?> !important;
            height: <?php echo esc_attr(get_option('ccf_button_height'));?> !important;
        }
    </style>
    <?php
}
add_action('wp_enqueue_scripts','ccf_custom_user_interface');

?>