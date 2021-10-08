<?php
/**
 * Yatra Checkout Settings
 *
 * @package Yatra/Admin
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (class_exists('Yatra_Settings_Checkout', false)) {
    return new Yatra_Settings_Checkout();
}

/**
 * Yatra_Settings_Checkout.
 */
class Yatra_Settings_Checkout extends Yatra_Admin_Settings_Base
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->id = 'checkout';
        $this->label = __('Checkout', 'yatra');

        parent::__construct();
    }

    /**
     * Get sections.
     *
     * @return array
     */
    public function get_sections()
    {
        $sections = array(
            '' => __('Checkout Settings', 'yatra'),
        );

        return apply_filters('yatra_get_sections_' . $this->id, $sections);
    }

    /**
     * Output the settings.
     */
    public function output()
    {
        global $current_section;

        $settings = $this->get_settings($current_section);

        Yatra_Admin_Settings::output_fields($settings);
    }

    /**
     * Save settings.
     */
    public function save()
    {
        global $current_section;

        $settings = $this->get_settings($current_section);
        Yatra_Admin_Settings::save_fields($settings);

        if ($current_section) {
            do_action('yatra_update_options_' . $this->id . '_' . $current_section);
        }
    }

    /**
     * Get settings array.
     *
     * @param string $current_section Current section name.
     * @return array
     */
    public function get_settings($current_section = '')
    {


        return apply_filters('yatra_get_settings_' . $this->id, array(
            array(
                'title' => __('Checkout Settings', 'yatra'),
                'type' => 'title',
                'desc' => '',
                'id' => 'yatra_checkout_general_options',
            ),
            array(
                'title' => __('Enable Guest Checkout', 'yatra'),
                'desc' => __('This option allows you to checkout without login. User will not created if you tick this option..', 'yatra'),
                'id' => 'yatra_enable_guest_checkout',
                'type' => 'checkbox',
                'default' => 'yes',
            ),
            array(
                'type' => 'sectionend',
                'id' => 'yatra_checkout_general_options',
            ),

        ), $current_section);
    }
}

return new Yatra_Settings_Checkout();
