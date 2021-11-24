<?php
if (!class_exists('Yatra_Custom_Post_Type_Coupons')) {

    class Yatra_Custom_Post_Type_Coupons
    {
        private $slug = 'yatra-coupons';


        public static function register_post_status()
        {

            $order_statuses = apply_filters(
                'yatra_register_booking_post_statuses',
                array(
                    'yatra-pending' => array(
                        'label' => _x('Pending booking', 'Booking status', 'yatra'),
                        'public' => true,
                        'exclude_from_search' => false,
                        'show_in_admin_all_list' => true,
                        'show_in_admin_status_list' => true,
                        /* translators: %s: number of orders */
                        'label_count' => _n_noop('Pending booking <span class="count">(%s)</span>', 'Pending bookings <span class="count">(%s)</span>', 'yatra'),
                    ),
                    'yatra-processing' => array(
                        'label' => _x('Processing', 'Booking status', 'yatra'),
                        'public' => true,
                        'exclude_from_search' => false,
                        'show_in_admin_all_list' => true,
                        'show_in_admin_status_list' => true,
                        /* translators: %s: number of orders */
                        'label_count' => _n_noop('Processing <span class="count">(%s)</span>', 'Processing <span class="count">(%s)</span>', 'yatra'),
                    ),
                    'yatra-on-hold' => array(
                        'label' => _x('On hold', 'Booking status', 'yatra'),
                        'public' => true,
                        'exclude_from_search' => false,
                        'show_in_admin_all_list' => true,
                        'show_in_admin_status_list' => true,
                        /* translators: %s: number of orders */
                        'label_count' => _n_noop('On hold <span class="count">(%s)</span>', 'On hold <span class="count">(%s)</span>', 'yatra'),
                    ),
                    'yatra-completed' => array(
                        'label' => _x('Completed', 'Booking status', 'yatra'),
                        'public' => true,
                        'exclude_from_search' => false,
                        'show_in_admin_all_list' => true,
                        'show_in_admin_status_list' => true,
                        /* translators: %s: number of orders */
                        'label_count' => _n_noop('Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>', 'yatra'),
                    ),
                    'yatra-cancelled' => array(
                        'label' => _x('Cancelled', 'Booking status', 'yatra'),
                        'public' => true,
                        'exclude_from_search' => false,
                        'show_in_admin_all_list' => true,
                        'show_in_admin_status_list' => true,
                        /* translators: %s: number of orders */
                        'label_count' => _n_noop('Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'yatra'),
                    ),
                )
            );

            foreach ($order_statuses as $order_status => $values) {
                // register_post_status($order_status, $values);
            }
        }

        public function remove($actions)
        {
            if (get_post_type() === $this->slug) {
                unset($actions['edit']);
                unset($actions['view']);
                unset($actions['trash']);
                unset($actions['inline hide-if-no-js']);
            }
            return $actions;
        }

        public function register()
        {
            $labels = array(
                'name' => _x('Coupons', 'post type general name', 'yatra'),
                'singular_name' => _x('Coupon', 'post type singular name', 'yatra'),
                'menu_name' => _x('Coupons', 'admin menu', 'yatra'),
                'name_admin_bar' => _x('Coupon', 'add new on admin bar', 'yatra'),
                'add_new' => _x('Add New', 'yatra', 'yatra'),
                'add_new_item' => __('Add New Coupon', 'yatra'),
                'new_item' => __('New Coupon', 'yatra'),
                'edit_item' => __('View Coupon', 'yatra'),
                'view_item' => __('View Coupon', 'yatra'),
                'all_items' => __('Coupons', 'yatra'),
                'search_items' => __('Search Coupons', 'yatra'),
                'parent_item_colon' => __('Parent Coupons:', 'yatra'),
                'not_found' => __('No Coupons found.', 'yatra'),
                'not_found_in_trash' => __('No Coupons found in Trash.', 'yatra'),
            );

            $args = array(
                'labels' => $labels,
                'description' => __('Description.', 'yatra'),
                'public' => false,
                'publicly_queryable' => false,
                'show_ui' => true,
                'show_in_menu' => 'edit.php?post_type=tour',
                'query_var' => true,
                'rewrite' => array('slug' => 'yatra-coupon'),
                'capability_type' => 'post',
                'has_archive' => false,
                'hierarchical' => false,
                'menu_position' => null,
                'supports' => array('title'),
                'menu_icon' => 'dashicons-location',
                'with_front' => true,
            );
            register_post_type($this->slug, $args);

        }


        public function init()
        {
            add_action('init', array($this, 'register'));
            add_action('init', array($this, 'register_post_status'));
            add_filter('post_row_actions', array($this, 'remove'));
            // add_action('do_meta_boxes', array($this, 'hide_metabox'));
        }

        public function hide_metabox()
        {


        }
    }
}
