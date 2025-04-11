<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_LLM_Settings {
    private $options;

    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
    }

    public function add_plugin_page() {
        add_menu_page(
            'Open LLM Settings',
            'Open LLM',
            'manage_options',
            'wp-llm-settings',
            array($this, 'create_admin_page'),
            'dashicons-format-chat'
        );
    }

    public function create_admin_page() {
        $this->options = get_option('wp_llm_settings');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('wp_llm_options');
                do_settings_sections('wp-llm-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function page_init() {
        register_setting(
            'wp_llm_options',
            'wp_llm_settings',
            array($this, 'sanitize')
        );

        // API Settings Section
        add_settings_section(
            'wp_llm_general_section',
            'API Settings',
            array($this, 'section_info'),
            'wp-llm-settings'
        );

        add_settings_field(
            'api_key',
            'LLM API Key',
            array($this, 'api_key_callback'),
            'wp-llm-settings',
            'wp_llm_general_section'
        );

        // Permissions Section
        add_settings_section(
            'wp_llm_permissions_section',
            'Access Permissions',
            array($this, 'permissions_section_info'),
            'wp-llm-settings'
        );

        add_settings_field(
            'allow_guest',
            'Guest Access',
            array($this, 'allow_guest_callback'),
            'wp-llm-settings',
            'wp_llm_permissions_section'
        );

        add_settings_field(
            'allowed_roles',
            'Allowed User Roles',
            array($this, 'allowed_roles_callback'),
            'wp-llm-settings',
            'wp_llm_permissions_section'
        );
    }

    public function sanitize($input) {
        $new_input = array();
        
        if (isset($input['api_key'])) {
            $new_input['api_key'] = sanitize_text_field($input['api_key']);
        }

        $new_input['allow_guest'] = isset($input['allow_guest']) ? 1 : 0;
        
        if (isset($input['allowed_roles']) && is_array($input['allowed_roles'])) {
            $new_input['allowed_roles'] = array_map('sanitize_text_field', $input['allowed_roles']);
        } else {
            $new_input['allowed_roles'] = array('administrator');
        }
        
        return $new_input;
    }

    public function section_info() {
        echo 'Enter your LLM API settings below:';
    }

    public function api_key_callback() {
        $value = isset($this->options['api_key']) ? $this->options['api_key'] : '';
        printf(
            '<input type="password" id="api_key" name="wp_llm_settings[api_key]" value="%s" class="regular-text">',
            esc_attr($value)
        );
    }

    public function permissions_section_info() {
        echo 'Configure who can access the LLM chat functionality:';
    }

    public function allow_guest_callback() {
        $checked = isset($this->options['allow_guest']) && $this->options['allow_guest'] ? 'checked' : '';
        printf(
            '<label>
                <input type="checkbox" name="wp_llm_settings[allow_guest]" value="1" %s>
                Allow non-logged-in users to access the chat
            </label>
            <p class="description">Warning: Enabling guest access may increase API usage.</p>',
            esc_attr($checked)
        );
    }

    public function allowed_roles_callback() {
        $roles = wp_roles()->get_names();
        $saved_roles = isset($this->options['allowed_roles']) ? $this->options['allowed_roles'] : array('administrator');
        
        echo '<fieldset>';
        foreach ($roles as $role_id => $role_name) {
            $checked = in_array($role_id, $saved_roles) ? 'checked' : '';
            printf(
                '<label style="display: block; margin-bottom: 5px;">
                    <input type="checkbox" name="wp_llm_settings[allowed_roles][]" 
                        value="%s" %s> %s
                </label>',
                esc_attr($role_id),
                esc_attr($checked),
                esc_html($role_name)
            );
        }
        echo '</fieldset>';
        echo '<p class="description">Select which user roles can access the chat when logged in.</p>';
    }

}