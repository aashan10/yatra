<?php

class Yatra_Core_Tour_Availability
{
    public function __construct()
    {

        add_action('yatra_availability_page_output', array($this, 'output'));

        add_action('admin_enqueue_scripts', array($this, 'load_admin_scripts'), 11);

        add_action('yatra_availability_calendar_tour_list', array($this, 'calendar_tour_list'));

    }


    public function load_admin_scripts()
    {
        $screen = get_current_screen();

        $screen_id = isset($screen->id) ? $screen->id : '';


        if ($screen_id != 'tour_page_yatra-availability') {
            return;
        }
        wp_enqueue_style('yatra-availability-style', YATRA_PLUGIN_URI . '/assets/admin/css/availability.css', array(
            'yatra-fullcalendar-css'
        ), YATRA_VERSION);

        wp_enqueue_script('yatra-availability-script', YATRA_PLUGIN_URI . '/assets/admin/js/availability.js',
            array('yatra-fullcalendar-js', 'yatra-popper', 'yatra-tippy')
            , YATRA_VERSION);

        $yatra_availability_params = array(

            'ajax_url' => admin_url('admin-ajax.php'),
            'tour_availability' => array(
                'action' => 'yatra_tour_availability',
                'nonce' => wp_create_nonce('wp_yatra_tour_availability_nonce')
            ),
            'day_wise_tour_availability' => array(
                'action' => 'yatra_day_wise_tour_availability',
                'nonce' => wp_create_nonce('wp_yatra_day_wise_tour_availability_nonce')
            ),
            ''
        );

        wp_localize_script('yatra-availability-script', 'yatra_availability_params', $yatra_availability_params);
    }

    public function output()
    {


        echo '<br/>';

        echo '<br/>';

        $this->calendar();
    }

    private function calendar()
    {
        echo '<div  id="yatra-availability-calendar-container">';
        echo '<div class="yatra-availability-calendar-header">';
        echo '<input type="hidden" value="" id="yatra-availability-calendar-tour-id"/>';
        echo '<ul class="symbol">';
        echo '<li class="yatra-tippy-tooltip booking" data-tippy-content="Available for booking">For Booking</li>';
        echo '<li class="yatra-tippy-tooltip enquery" data-tippy-content="Available for enquiry only">For Enquiry Only</li>';
        echo '<li class="yatra-tippy-tooltip not-available" data-tippy-content="Booking & enquiry not available">Not Available for Booking & Enquiry</li>';
        echo '</ul>';
        echo '</div>';

        echo '<div class="yatra-availability-calendar-content-body">';

        do_action('yatra_availability_calendar_tour_list');

        echo '<div  id="yatra-availability-calendar-wrap">';

        echo '<div  id="yatra-availability-calendar">';


        echo '</div>';

        echo '</div>';

        echo '</div>';

        echo '</div>';

    }

    public static function get_availability($tour_id, $start_date, $end_date)
    {

        $fixed_departure = (boolean)get_post_meta($tour_id, 'yatra_tour_meta_tour_fixed_departure', true);

        $yatra_tour_availability = yatra_tour_meta_availability_date_ranges($tour_id);

        if (!$fixed_departure || (count($yatra_tour_availability) < 1)) {
            $start_date = new DateTime($start_date);
            $end_date = new DateTime($end_date);
            $end_date->modify('-1 day');
            $yatra_tour_availability = array(
                array(
                    'start' => $start_date->format("Y-m-d"),
                    'end' => $end_date->format("Y-m-d")
                )
            );
        }

        $all_responses = array();

        foreach ($yatra_tour_availability as $availability) {

            $begin = new DateTime($availability['start']);

            $end = new DateTime($availability['end']);

            $availability_data = yatra()->tour->get_availability_pricing(
                1, $begin->format("Y-m-d"), $end->format("Y-m-d"), array(
                'pricing',
                'sales_price',
                'label'
            ),
                $tour_id
            );


            for ($i = $begin; $i <= $end; $i->modify('+1 day')) {

                $single_date = $i->format("Y-m-d");


                $all_responses[] = self::get_single_availability($single_date, $tour_id, $availability_data);

            }
        }


        return $all_responses;
    }

    private static function get_single_availability($start_date, $tour_id, $availability_data)
    {

        $current_date = date('Y-m-d');

        $response = array();

        $availability_data_index = str_replace(' ', '', ($start_date . '00:00:00_' . $start_date . '23:59:59'));

        $availability_data_array = isset($availability_data[$availability_data_index]) ? $availability_data[$availability_data_index] : array();

        $is_active = isset($availability_data_array['active']) ? (boolean)$availability_data_array['active'] : false;

        $max_travellers = isset($availability_data_array['max_travellers']) ? yatra_maybeintempty($availability_data_array['max_travellers']) : '';

        $booked_travellers = isset($availability_data_array['booked_travellers']) ? yatra_maybeintempty($availability_data_array['booked_travellers']) : '';

        $availability = isset($availability_data_array['availability']) ? sanitize_text_field($availability_data_array['availability']) : 'booking';

        $availability_label = yatra_tour_availability_status($availability);

        $pricing = isset($availability_data_array['pricing']) ? $availability_data_array['pricing'] : array();

        $is_full = $max_travellers <= $booked_travellers && $booked_travellers != '' & $max_travellers != '';

        $is_expired = (strtotime($start_date) < strtotime($current_date));

        if ('' != $start_date) {

            $yatra_multiple_pricing = get_post_meta($tour_id, 'yatra_multiple_pricing', true);

            if (!$yatra_multiple_pricing) {

                $pricing = isset($pricing[0]) ? $pricing[0] : array();

                $regular = isset($pricing['regular_price']) ? $pricing['regular_price'] : get_post_meta($tour_id, 'yatra_tour_meta_regular_price', true);

                $discounted = isset($pricing['sales_price']) ? $pricing['sales_price'] : get_post_meta($tour_id, 'yatra_tour_meta_sales_price', true);

                $pricing_label = get_post_meta($tour_id, 'yatra_tour_meta_pricing_label', true);

                $final_pricing = '' === $discounted ? $regular : $discounted;

                $current_currency_symbol = '$';//yatra_get_current_currency_symbol();

                $title = "{$pricing_label}: {$current_currency_symbol}{$final_pricing}";

                if ($is_full) {
                    $title = __('Booking Full', 'yatra');
                }

                $response = array(
                    "title" => $title,
                    "start" => $start_date,
                    "description" => "<strong>{$availability_label}</strong><hr/>{$pricing_label}: {$current_currency_symbol}{$final_pricing}",
                    "is_active" => $is_active,
                    "availability" => $availability,
                    'is_full' => $is_full,
                    'is_expired' => $is_expired


                );
            } else {

                $title = '';

                $description = "<strong>{$availability_label}</strong><hr/>";

                foreach ($yatra_multiple_pricing as $pricing_index => $single_pricing) {

                    $single_pricing = isset($pricing[$pricing_index]) ? $pricing[$pricing_index] : $single_pricing;

                    $regular = $single_pricing['regular_price'];

                    $discounted = $single_pricing['sales_price'];

                    $final_pricing = '' === $discounted ? $regular : $discounted;

                    $pricing_label = $single_pricing['pricing_label'];

                    $current_currency_symbol = '$';//yatra_get_current_currency_symbol();

                    $title .= "{$pricing_label}: {$current_currency_symbol}{$final_pricing} <br/> ";

                    $description .= "{$pricing_label}&nbsp;:&nbsp; <strong style='float:right;'>{$current_currency_symbol}{$final_pricing}</strong> <br/> ";

                }
                if ($is_full) {
                    $title = __('Booking Full', 'yatra');
                }
                $response = array(
                    "title" => $title,
                    //"event" => $title,
                    "start" => $start_date,
                    "description" => $description,
                    "is_active" => $is_active,
                    "availability" => $availability,
                    'is_full' => $is_full,
                    'is_expired' => $is_expired


                );
            }


        }


        return $response;
    }

    public static function get_day_wise_availability_form($tour_id, $start_date, $end_date, $content_only = false)
    {

        yatra()->tour->maybe_initialize($tour_id);

        $availability_pricing = array();

        $availability_pricing_index = str_replace(' ', '', ($start_date . '00:00:00_' . $end_date . '23:59:59'));

        $yatra_availability['max_travellers'] = get_post_meta($tour_id, 'yatra_tour_maximum_number_of_traveller', true);

        $yatra_availability['availability_for'] = 'booking';

        $pricings = yatra()->tour->get_pricing();

        $pricing_type = yatra()->tour->get_pricing_type();

        $active_status = false;

        if ($start_date == $end_date) {

            $availability_pricing = yatra()->tour->get_availability_pricing(1, $start_date, $end_date, array(), $tour_id);

            $availability_pricing_today = isset($availability_pricing[$availability_pricing_index]) ? $availability_pricing[$availability_pricing_index] : array();

            $yatra_availability['max_travellers'] = isset($availability_pricing_today['max_travellers']) ? absint($availability_pricing_today['max_travellers']) : $yatra_availability['max_travellers'];

            $yatra_availability['availability_for'] = isset($availability_pricing_today['availability']) ? sanitize_text_field($availability_pricing_today['availability']) : $yatra_availability['availability_for'];

            $dynamic_pricing_type = isset($availability_pricing_today['pricing_type']) ? sanitize_text_field($availability_pricing_today['pricing_type']) : '';

            $dynamic_pricing = isset($availability_pricing_today['pricing']) ? ($availability_pricing_today['pricing']) : array();

            $active_status = isset($availability_pricing_today['active']) ? (boolean)($availability_pricing_today['active']) : false;

            if (count($dynamic_pricing) > 0) {

                if ($dynamic_pricing_type === $pricing_type && $pricing_type == "single") {

                    $dynamic_pricing[0]['pricing_label'] = $pricings[0]['pricing_label'];
                    $dynamic_pricing[0]['pricing_description'] = $pricings[0]['pricing_description'];
                    $pricings = $dynamic_pricing;

                } else if ($dynamic_pricing_type === $pricing_type && $pricing_type == "multi") {

                    $pricings = yatra_parse_args($dynamic_pricing, $pricings, true);
                }
            }
        }


        yatra()->tour->maybe_flush();

        $currency = get_option('yatra_currency');

        $currency_symbol = yatra_get_currency_symbols($currency);


        $template = '';

        $form_class = $active_status === false ? 'form yatra-deactivate-form' : 'form';

        ob_start();

        echo '<form id="yatra-availability-calendar-popup-form" method="post" class="' . esc_attr($form_class) . '">';

        if (!$content_only) {

            $yatra_tour_meta_availability_date_ranges = yatra_tour_meta_availability_date_ranges($tour_id);

            yatra_load_admin_template('availability.availability-calendar-date', array(
                'selected_dates' => array(
                    'start' => $start_date,
                    'end' => $end_date
                ),
                'availability_dates' => $yatra_tour_meta_availability_date_ranges,
                'active_status' => $active_status
            ));


        }

        echo '<div class="yatra-availability-calendar-pricing-content">';

        yatra_load_admin_template('availability.availability-calendar-header', array(
            'start_date' => $start_date,
            'end_date' => $end_date,
            'pricing_type' => $pricing_type,
            'tour_id' => $tour_id,
            'yatra_availability' => $yatra_availability
        ));
        foreach ($pricings as $pricing_option_id => $pricing) {

            yatra_load_admin_template('availability.availability-calendar', array(
                'id' => $pricing_option_id,
                'currency_symbol' => $currency_symbol,
                'pricing_option_id' => 'yatra_availability_pricing[' . $pricing_option_id . ']',
                'pricing' => $pricing
            ));


        }

        wp_nonce_field('wp_yatra_day_wise_tour_availability_save_nonce', 'yatra_nonce', true, true);

        echo '<input type="hidden" name="action" value="yatra_day_wise_tour_availability_save"/>';
        echo '<input type="submit" style="display: none"/>';

        echo '</div>';

        echo '</form>';

        $template .= ob_get_clean();


        $response = array(
            'title' => $start_date . ' - ' . $end_date,
            'data' => $template,
            'fixed_date_ranges' => yatra_tour_meta_availability_date_ranges($tour_id)
        );
        echo json_encode($response);
        exit;
    }

    public function calendar_tour_list()
    {
        $the_query = new WP_Query(
            array('posts_per_page' => 30,
                'post_type' => 'tour',
                'paged' => get_query_var('paged') ? get_query_var('paged') : 1)
        );
        echo '<ul class="yatra-availability-tour-lists">';
        while ($the_query->have_posts()):

            $the_query->the_post();
            echo '<li>';
            echo '<a data-id="' . absint(get_the_ID()) . '" target="_blank" href="' . esc_url(get_the_permalink()) . '">#' . absint(get_the_ID()) . ' - ' . esc_html(get_the_title()) . '</a>';
            echo '</li>';

        endwhile;
        echo '</ul>';
    }
}

new Yatra_Core_Tour_Availability();