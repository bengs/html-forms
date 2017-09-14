<?php

namespace HTML_Forms;

class Forms
{

    private $plugin_file;

    public function __construct($plugin_file)
    {
        $this->plugin_file = $plugin_file;
    }

    public function hook()
    {
        add_action('init', array($this, 'register'));
        add_action('init', array($this, 'listen'));
        add_action('wp_enqueue_scripts', array($this, 'assets'));
    }

    public function register()
    {
        // register post type
        register_post_type('html-form', array(
                'labels' => array(
                    'name' => 'HTML Forms',
                    'singular_name' => 'HTML Form',
                ),
                'public' => false
            )
        );

        add_shortcode('html_form', array($this, 'shortcode'));
    }

    public function assets()
    {
        wp_enqueue_script('html-forms', plugins_url('assets/js/public.js', $this->plugin_file), array(), HTML_FORMS_VERSION, true);
        wp_localize_script('html-forms', 'hf_js_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
        ));
    }

    private function array_get( $array, $key, $default = null ) {
        if ( is_null( $key ) ) {
            return $array;
        }

        if ( isset( $array[$key] ) ) {
            return $array[$key];
        }

        $segments = explode( '.', $key );
        foreach ( $segments as $segment) {
            if ( ! is_array( $array ) || ! array_key_exists( $segment, $array ) ) {
                return $default;
            }
            $array = $array[$segment];
        }

        return $array;
    }

    private function validate_form(Form $form, $data) {

        foreach ($form->get_required_fields() as $field_name) {
            $value = $this->array_get( $data, $field_name );
            if ( empty( $value ) ) {
                return 'required_field_missing';
            }
        }

        foreach ($form->get_email_fields() as $field_name) {
            $value = $this->array_get( $data, $field_name );
            if ( ! empty( $value ) && ! is_email( $value ) ) {
                return 'invalid_email';
            }
        }

        return 'success';
    }

    public function listen() {
        if (empty($_POST['_hf_form_id'])) {
            return;
        }

        $form_id = (int)$_POST['_hf_form_id'];
        $form = hf_get_form($form_id);
        $case = $this->validate_form($form, $_POST);

        // filter out all field names starting with _
        $data = array_filter( $_POST, function( $k ) {
            return ! empty( $k ) && $k[0] !== '_';
        }, ARRAY_FILTER_USE_KEY );

        if ($case === 'success') {
            $submission = new Submission();
            $submission->form_id = $form_id;
            $submission->data = $data;
            $submission->ip_address = $_SERVER['REMOTE_ADDR'];
            $submission->user_agent = $_SERVER['HTTP_USER_AGENT'];
            $submission->save();

            // TODO: Process form actions

            $data = array(
                'message' => array(
                    'type' => 'success',
                    'text' => $form->messages['success'],
                ),
                'hide_form' => (bool)$form->settings['hide_after_success'],
            );

            if (!empty($form->settings['redirect_url'])) {
                $data['redirect_url'] = $form->settings['redirect_url'];
            }
        } else {
            $data = array(
                'message' => array(
                    'type' => 'warning',
                    'text' => $form->messages[$case],
                )
            );
        }

        send_origin_headers();
        send_nosniff_header();
        nocache_headers();

        wp_send_json($data, 200);
        exit;
    }

    public function shortcode($attributes = array(), $content = '')
    {
        $form = hf_get_form($attributes['slug']);
        return $form . $content;
    }
}