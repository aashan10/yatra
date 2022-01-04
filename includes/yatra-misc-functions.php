<?php
if (!function_exists('yatra_get_discount_deals_lists')) {

    function yatra_get_discount_deals_lists($atts = array())
    {
        $order = isset($atts['order']) ? sanitize_text_field($atts['order']) : 'DESC';
        $order = in_array(strtolower($order), array('asc', 'desc')) ? $order : 'desc';
        $args = array(
            'meta_query' => array(
                array(
                    'key' => 'yatra_tour_meta_sales_price',
                    'value' => array('', 0, '0'),
                    'compare' => 'NOT IN'
                )
            ),
            'post_type' => 'tour',
            'order' => $order,
            'posts_per_page' => 9
        );
        $posts = get_posts($args);


        $grid_class = 'yatra-col-sm-6 yatra-col-md-4';

        echo '<div class="yatra-discount-deals-list-container">';

        echo '<div class="yatra-row yatra-discount-deals-wrap">';

        foreach ($posts as $item) {

            $data['data'] = array(
                'id' => $item->ID,
                'title' => $item->post_title,
                'excerpt' => $item->post_excerpt,
                'permalink' => get_permalink($item->ID),
                'image' => '',
                'class' => $grid_class
            );

            $attachment_id = (int)get_post_thumbnail_id($item);

            if (($attachment_id) > 0) {

                $attachment_link = wp_get_attachment_image_url($attachment_id, 'full');

                $data['data']['image'] = $attachment_link;
            }

            yatra_get_template('tmpl-deals-item.php', $data);

        }
        echo '</div>';

        echo '</div>';

    }

}

if (!function_exists('yatra_get_tour_lists')) {

    function yatra_get_tour_lists($atts = array())
    {
        $order = isset($atts['order']) ? sanitize_text_field($atts['order']) : 'DESC';

        $order = in_array(strtolower($order), array('asc', 'desc')) ? $order : 'desc';

        $featured = isset($atts['featured']) ? absint($atts['featured']) : 2;

        $posts_per_page = isset($atts['posts_per_page']) ? absint($atts['posts_per_page']) : 9;

        $columns = isset($atts['columns']) ? absint($atts['columns']) : 3;

        $meta_query = array();


        switch ($featured) {

            case 0:
                $meta_query[] =

                    array(
                        'relation' => 'OR',
                        array(
                            'key' => 'yatra_tour_meta_tour_featured',
                            'value' => array(1, '1'),
                            'compare' => 'NOT IN'
                        ),
                        array(
                            'key' => 'yatra_tour_meta_tour_featured',
                            'compare' => 'NOT EXISTS',
                            'value' => 'null',
                        )
                    );
                break;

            case 1:
                $meta_query[] =
                    array(
                        'key' => 'yatra_tour_meta_tour_featured',
                        'value' => array(1, '1'),
                        'compare' => 'IN'

                    );
                break;
        }

        $args = array(
            'post_type' => 'tour',
            'order' => $order,
            'posts_per_page' => $posts_per_page
        );


        if (count($meta_query) > 0) {

            $args['meta_query'] = $meta_query;
        }

        $posts = get_posts($args);

        $grid_class = 'yatra-col-sm-6 ';

        switch ($columns) {
            case 2:
                $grid_class .= 'yatra-col-md-6';
                break;
            case 3:
                $grid_class .= 'yatra-col-md-4';
                break;
            case 4:
                $grid_class .= 'yatra-col-md-3';
                break;
            default:
                $grid_class .= 'yatra-col-md-4';
        }

        echo '<div class="yatra-tour-list-container">';

        echo '<div class="yatra-row yatra-tour-list-wrap">';

        foreach ($posts as $item) {

            $data['data'] = array(
                'id' => $item->ID,
                'title' => $item->post_title,
                'excerpt' => $item->post_excerpt,
                'permalink' => get_permalink($item->ID),
                'image' => '',
                'class' => $grid_class,
            );

            $attachment_id = (int)get_post_thumbnail_id($item);

            if (($attachment_id) > 0) {

                $attachment_link = wp_get_attachment_image_url($attachment_id, 'full');

                $data['data']['image'] = $attachment_link;
            }

            yatra_get_template('tmpl-tour-item.php', $data);

        }
        echo '</div>';

        echo '</div>';


    }

}

function yatra_export($args = array())
{
    $defaults = array(

        'content' => array(),

    );

    $args = wp_parse_args($args, $defaults);

    do_action('export_yatra', $args);

    $sitename = strtolower(sanitize_key(get_bloginfo('name')));

    if (!empty($sitename)) {

        $sitename .= '.';
    }

    $content = $args['content'];

    $content = is_string($content) ? $content : json_encode($content);

    $date = gmdate('Y-m-d');

    $wp_filename = $sitename . 'yatra.' . $date . '.json';

    $filename = apply_filters('export_wp_filename', $wp_filename, $sitename, $date);

    header('Content-Description: File Transfer');

    header('Content-Disposition: attachment; filename=' . $filename);

    header('Content-Type: text/json; charset=' . get_option('blog_charset'), true);

    echo $content;

    exit;

}

if (!function_exists('yatra_get_tour_view_details_button_text')) {
    function yatra_get_tour_view_details_button_text()
    {
        return get_option('yatra_tour_view_details_button_text', __('View Details', 'yatra'));
    }
}
