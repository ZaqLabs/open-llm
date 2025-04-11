<?php
/**
 * Plugin Name:       Open LLM
 * Description:       Add LLM Chat to your WordPress site
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            Abdulrasaq Lawani
 * Author URI:        https://www.zaqlabs.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       open_llm
 *
 * @package CreateBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Include WordPress REST API
require_once ABSPATH . 'wp-includes/rest-api.php';

// Include settings page
require_once plugin_dir_path(__FILE__) . 'admin/class-wp-llm-settings.php';

// Initialize settings
if (is_admin()) {
    new WP_LLM_Settings();
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_wp_llm_block_init() {
	register_block_type( __DIR__ . '/build/open_llm' );
}
add_action( 'init', 'create_block_wp_llm_block_init' );

/**
 * Register REST API endpoints for the LLM chat functionality
 */
function wp_llm_register_rest_routes() {
    register_rest_route(
        'wp-llm/v1',
        '/chat',
        array(
            'methods'             => 'POST',
            'callback'           => 'wp_llm_handle_chat_request',
            'permission_callback' => 'wp_llm_check_permission',
            'args'               => array(
                'message' => array(
                    'required'          => true,
                    'type'             => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        )
    );
}
add_action( 'rest_api_init', 'wp_llm_register_rest_routes' );

/**
 * Handle chat request and return response
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error The response object.
 */
function wp_llm_handle_chat_request($request) {
    $message = $request->get_param('message');
    $options = get_option('wp_llm_settings');

    $body = json_encode([
        'model' => 'gpt-4o-mini',
        'store' => true,
        'messages' => [
            [
             'role' => 'user',
             'content' => $message
            ]
        ]
    ]);

    // var_dump($options['api_key']);die;

    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization'=> 'Bearer ' . $options['api_key']
        ),
        'body' => $body,
        'timeout' => 30
    ));

    

    if (is_wp_error($response)) {

        return new WP_Error(
            'api_error',
            'Failed to connect to LLM endpoint: ' . $response->get_error_message(),
            array('status' => 500)
        );
    }

    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    $result_message = $response_body['choices'][0]['message']['content'];
    
    if (!$response_body || !isset($result_message)) {
       
        return new WP_Error(
            'invalid_response',
            $response_body ?? 'Invalid response from LLM endpoint',
            array('status' => 500)
        );
    }

    return rest_ensure_response(array(
        'message' => $result_message,
        'status' => 'success'
    ));
}

/**
 * Enqueue scripts and localize data for the REST API
 */
function wp_llm_enqueue_scripts() {
    wp_enqueue_script( 'wp-api' );
    wp_localize_script( 'wp-api', 'wpApiSettings', array(
        'nonce' => wp_create_nonce( 'wp_rest' )
    ));
}
add_action( 'wp_enqueue_scripts', 'wp_llm_enqueue_scripts' );

/**
 * Check permission for accessing the chat functionality
 */
function wp_llm_check_permission() {
    $options = get_option('wp_llm_settings');
    
    // Check for guest access
    if (!is_user_logged_in()) {
        return isset($options['allow_guest']) && $options['allow_guest'];
    }
    
    // Check user roles
    $allowed_roles = isset($options['allowed_roles']) ? $options['allowed_roles'] : array('administrator');
    $user = wp_get_current_user();
    
    foreach ($user->roles as $role) {
        if (in_array($role, $allowed_roles)) {
            return true;
        }
    }
    
    return false;
}


