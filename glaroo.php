<?php

/**
 * @package Plugin
 */
/*
Plugin Name: Glaroo
Plugin URI: https://glaroo.com
Description: To get started: activate the Plugin plugin and then go to your Plugin Settings page to set up your webhook and other information. Glaroo aggregates data for better data-informed decisions
Version: 1.0
Requires at least: 5.0
Requires PHP: 5.2
Author: Glaroo
Author URI: https://glaroo.com
Text Domain: plugin
*/

// Create a submenu page under Settings
function plugin_settings_submenu_page()
{
    add_submenu_page(
        'options-general.php',     // Parent menu slug
        'Glaroo Settings',         // Page title
        'Glaroo Settings',         // Menu title
        'manage_options',          // Capability required to access the page
        'glaroo-settings',         // Menu slug
        'plugin_settings_callback' // Callback function to display the page content
    );
}
add_action('admin_menu', 'plugin_settings_submenu_page');

// Callback function to display the submenu page content
function plugin_settings_callback()
{
?>
    <style>
        ::-webkit-input-placeholder {
            font-style: italic;
        }

        :-moz-placeholder {
            font-style: italic;
        }

        ::-moz-placeholder {
            font-style: italic;
        }

        :-ms-input-placeholder {
            font-style: italic;
        }

        p.submit {
            display: contents;
        }

        #message-container {
    margin-top: 10px;
}

.in-progress-message,
.success-message,
.error-message {
    padding: 10px;
    border-radius: 4px;
    font-weight: bold;
    text-align: center;
}

.in-progress-message {
    /* background-color: #ffc107;
    color: #fff; */
    width:200px;
}

.success-message {
    background-color: #28a745;
    color: #fff;
    width: 160px;
}

.error-message {
    background-color: #dc3545;
    color: #fff;
    width: 160px;
}
.d-none{
    display:none;
}
    </style>

    <div class="wrap">
        <h1>Glaroo Settings</h1>
        <p>Enter below details for connecting with the Webhook.</p>

        <h2>Get a Glaroo Account</h2>
        <p><em>This plugin requires a Glaroo account in order to aggregate data and provide insights. Please sign up below.</em></p>

        <h3>What is Glaroo?</h3>
        <p>Glaroo is a data aggregation and insights platform. You provide your data from Wordpress, GA, Search Console, etc and we aggregate it all together in a seamless platform. We provide better insights on better data.</p>

        <p><strong><a href="https://glaroo.com" title="glaroo">Register for a free Glaroo account</a></strong></p>

        <form method="post" action="options.php">
            <?php
            // Output security fields
            settings_fields('plugin-settings');
            // Output setting sections
            do_settings_sections('plugin-settings');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Webhook URL:</th>
                    <td><input type="text" placeholder="Enter webhook URL" size="35" name="webhook_url" value="<?php echo esc_attr(get_option('webhook_url')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">API Key:</th>
                    <td><input type="text" placeholder="Enter API key" name="api_key" value="<?php echo esc_attr(get_option('api_key')); ?>" /></td>
                </tr>
                <!-- <tr valign="top">
                    <th scope="row">Site ID:</th>
                    <td><input type="text" placeholder="Enter Site ID" name="site_id" value="<?php // echo esc_attr(get_option('site_id')); ?>" /></td>
                </tr> -->
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <br><br>
    <div class="wrap">
        <?php $plugin_dir_path = plugin_dir_url( __FILE__ ); ?>
        <h1>Bulk Post Data</h1>
        <p>Hit the button to bulk post data.</p>
        <p class="submit">
            <input type="button" name="postdata" id="postdata" class="button button-primary" value="Bulk Post Data">
            <img id="loader" class="d-none" src="<?php echo  $plugin_dir_path; ?>/images/spinner.gif">
            <div id="message-container">
                <p class="d-none" class="in-progress-message"></p>
            </div>
        </p>

    </div>
<?php
}

// Register custom settings fields
function custom_settings_init()
{
    // Register the settings fields
    register_setting('plugin-settings', 'webhook_url', 'validate_webhook_url');
    register_setting('plugin-settings', 'api_key', 'validate_api_key');
    // register_setting('plugin-settings', 'site_id', 'validate_site_id');
}
add_action('admin_init', 'custom_settings_init');

// Validation function for webhook URL
function validate_webhook_url($input)
{
    $url = $input;
    // Validate if the URL is empty
    if ($url == '') {
        add_settings_error('webhook_url', 'empty_webhook_url', 'Webhook URL is a required field.', 'error');
        $input = get_option('webhook_url'); // Reset the value to the previous valid value
        return '';
    }

    // Validate the URL format
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        add_settings_error('webhook_url', 'invalid_webhook_url', 'Invalid webhook URL, Please enter a valid URL.', 'error');
        $input = get_option('webhook_url'); // Reset the value to the previous valid value
        return $input;
    }

    return $input;
}

// Validation function for API key
function validate_api_key($input)
{
    if ($input == '') {
        add_settings_error('api_key', 'invalid_api_key', 'API Key is a required field.', 'error');
        $input = get_option('api_key'); // Reset the value to the previous valid value
    }
    return $input;
}

// Validation function for API key
function validate_site_id($input)
{
    if ($input == '') {
        add_settings_error('site_id', 'invalid_site_id', 'Site ID is a required field.', 'error');
        $input = get_option('site_id'); // Reset the value to the previous valid value
    }
    return $input;
}

add_action('save_post', 'post_data_to_webhook');
function post_data_to_webhook($post_id)
{
    // Get the post data
    $post = get_post($post_id);


    if ($post->post_status == 'publish') 
    {
        // Get the webhook URL from the options
        $webhook_url = get_option('webhook_url');
        $site_id = get_option('site_id');

        $concatenatedURL = $webhook_url;

        // Prepare the data to be sent
        $data = array(
            'title' => $post->post_title,
            'slug' => get_permalink(),
            'content' => $post->post_content,
            'meta_data' => get_meta_data($post->ID),
            'taxonomy_data' => get_taxonomy_data($post->ID),
            'author_data' => get_author_data($post->post_author),
            'publish_date' => $post->post_date,
            'post_type' => get_post_type($post->ID),
        );
        

        // Create a new HTTP POST request
        $args = array(
            'body' => json_encode($data),
            'timeout' => '60',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(
                'Content-Type' => 'application/json'
            )
        );

        
        // Send the data to the webhook URL using wp_remote_post()
        $response = wp_remote_post($concatenatedURL, $args);

        // Check if the request was successful
        if (is_wp_error($response)) {
            // Handle the error
            wp_send_json_error($response->get_error_message());
            error_log('Webhook request failed: ' . $response->get_error_message());
            return;
        } else {
            // The request was successful, you can handle the response if needed
            $response_code = wp_remote_retrieve_response_code($response);
            
            $response_body = wp_remote_retrieve_body($response);
            
            return $response_body;
        }
    }
}

// Taxonomy data
function get_taxonomy_data($post_id)
{
    $term_list = get_the_terms($post_id, 'category');
    $taxonomy_data = [];
    foreach ($term_list as $term_single) {
        $taxonomy_data[] = array(
            'name' => $term_single->name,
            'slug' => $term_single->slug,
            'description' => $term_single->description,
        );
    }
    return $taxonomy_data;
}

// Author data
function get_author_data($author_id)
{
    $user_info = get_userdata($author_id);
    $author_data = array(
        'id' => $user_info->ID,
        'email' => $user_info->user_email,
        'user_registered' => $user_info->user_registered,
        'display_name' => $user_info->display_name,
        'user_nicename' => $user_info->user_nicename,
    );
    return $author_data;
}

// Retrieve all post meta for the given post ID
function get_meta_data($post_id)
{
    $all_meta = get_post_meta($post_id);
    $meta_data = [];
    foreach ($all_meta as $key => $values) {
        foreach ($values as $value) {
            $meta_data[$key] = $value;
        }
    }
    return $meta_data;
}

add_action('admin_enqueue_scripts', 'enqueue_custom_script');
function enqueue_custom_script()
{
    wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-3.6.0.min.js', array(), '3.6.0', false);
    wp_enqueue_script('custom-script', plugins_url('/custom.js', __FILE__), array('jquery'), '1.0', true);
    // Pass necessary data to the JavaScript file
    wp_localize_script('custom-script', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'ajax_nonce' => wp_create_nonce('get_all_post_data')
    ));
}

add_action('wp_ajax_get_all_post_data', 'get_all_post_data_callback');
add_action('wp_ajax_nopriv_get_all_post_data', 'get_all_post_data_callback');
function get_all_post_data_callback()
{
    check_ajax_referer('get_all_post_data', 'security');

    // Get all post data
    $posts = get_posts(array(
        'post_type'      => array('post', 'page'),  // Include both posts and pages
        'numberposts'    => -1,
    ));

    // Prepare the response
    $response = array();
    foreach ($posts as $post) {

        $response[] = array(
            'title' => $post->post_title,
            'slug' => get_permalink(),
            'content' => $post->post_content,
            'meta_data' => get_meta_data($post->ID),
            'taxonomy_data' => get_taxonomy_data($post->ID),
            'author_data' => get_author_data($post->post_author),
            'publish_date' => $post->post_date,
            'post_type' => get_post_type($post->ID),
        );
    }

    // Send the response as JSON
    //wp_send_json($response);

    // Create a new HTTP POST request
    $args = array(
        'body' => json_encode($response),
        'timeout' => '60',
        'redirection' => '5',
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => array(
            'Content-Type' => 'application/json'
        )
    );
    $webhook_url = get_option('webhook_url');
    $site_id = get_option('site_id');

    $concatenatedURL = $webhook_url;

        
    // Send the data to the webhook URL using wp_remote_post()
    $response = wp_remote_post($concatenatedURL, $args);

        // Check if the request was successful
    if (is_wp_error($response)) {
        // Handle the error
        wp_send_json_error($response->get_error_message());
        error_log('Webhook request failed: ' . $response->get_error_message());
        return;
    } else {
            // The request was successful, you can handle the response if needed
            $response_code = wp_remote_retrieve_response_code($response);
            
            $response_body = wp_remote_retrieve_body($response);

            return $response_body;
        }
}
?>
