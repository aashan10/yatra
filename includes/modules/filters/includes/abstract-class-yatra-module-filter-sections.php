<?php

abstract class Yatra_Module_Filter_Sections
{
    public function get_id()
    {
        return get_the_ID();
    }

    abstract function get_label();

    abstract function is_visible();

    abstract function render();

    public function taxonomy_filter_html($terms, $children = false)
    {

        $current_term_slugs = $this->get_selected_terms();

        $parent_count = 0;

        $term_count = 0;

        if (is_array($terms) && count($terms) > 0) {

            printf('<ul class="%1$s">', $children ? 'children' : 'yatra-terms-list');

            $invisible_terms = '';

            foreach ($terms as $term) {

                if ($term->parent && !$children) {

                    continue;
                }
                ob_start();

                $current_term_slug_string = in_array($term->slug, $current_term_slugs) ? $term->slug : '';

                printf('<li class="%1$s">', $children ? 'has-children' : 'item');
                printf(
                    '<label for="yatra-filter-term-item-%1$d">'
                    . '<input type="checkbox" %2$s value="%3$s" name="filter_%4$s" class="%5$s yatra-filter-item" id="yatra-filter-term-item-%6$d"/>'
                    . '<span class="yatra-filter-term-name">%7$s</span>'
                    . '</label>',
                    $term->term_id,
                    checked($term->slug, $current_term_slug_string, false), // phpcs:ignore
                    $term->slug,
                    $term->taxonomy,
                    $term->taxonomy,
                    $term->term_id,
                    $term->name
                );

                if (apply_filters('yatra_advanced_search_filters_show_tax_count', true)) {
                    printf('<span class="count">%1$s</span>', $term->count);
                }
                if (is_array($term->children) && count($term->children) > 0) {
                    $_children = array();
                    foreach ($term->children as $term_child) {
                        if (!isset($terms[$term_child])) {
                            continue;
                        }
                        $_children[$term_child] = $terms[$term_child];
                    }
                    $this->taxonomy_filter_html($_children, true);
                }
                print('</li>');

                $list = ob_get_clean();

                if ((++$parent_count > 4) && !$children) {
                    $invisible_terms .= $list;
                } else {
                    $term_count += count($term->children) + 1;
                    echo $list;
                }
            }
            if ($invisible_terms != '' && !$children) {
                printf(
                    '<li class="yatra-terms-more"><span class="show-more">%2$s <i class="icon fa fa-chevron-down"></i></span><ul class="yatra-terms-more-list">%1$s</ul><span class="show-less">%3$s <i class="icon fa fa-chevron-up"></i></span></li>',
                    $invisible_terms,
                    sprintf(__('Show all %s', 'yatra'), count($terms) - $term_count),
                    __('Show less', 'yatra')
                );
            }
            print('</ul>');
        }

    }

    public function get_selected_terms()
    {
        $current_term_slugs = array();

        $params = yatra_get_filter_params();

        if (isset($params->activity)) {

            $current_term_slugs = $params->activity;
        }
        if (isset($params->destination)) {

            $current_term_slugs = $params->destination;
        }

        return $current_term_slugs;
    }

}