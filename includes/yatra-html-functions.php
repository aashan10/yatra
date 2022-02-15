<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


if (!function_exists('yatra_html_form_help')) {
    function yatra_html_form_help($value = '')
    {
        if (!empty($value)) {
            echo '<span class="description">' . wp_kses_post($value) . '</span>';
        }
    }
}

if (!function_exists('yatra_html_form_label')) {
    function yatra_html_form_label($label, $field_id = '', $required = false)
    {
        $req = $required ? ' <span class="required">*</span>' : '';
        echo '<label for="' . esc_attr($field_id) . '">' . wp_kses_post($label) . $req . '</label>';
    }
}

if (!function_exists('yatra_html_form_custom_attr')) {

    function yatra_html_form_custom_attr($attr = array(), $other_attr = array())
    {
        $custom_attributes = array();

        if (!empty($attr) && is_array($attr)) {
            foreach ($attr as $attribute => $value) {
                if ($value != '') {
                    $custom_attributes[] = esc_attr($attribute) . '="' . esc_attr($value) . '"';
                }
            }
        }

        if (!empty($other_attr) && is_array($other_attr)) {
            foreach ($attr as $attribute => $value) {
                $custom_attributes[] = esc_attr($attribute) . '="' . esc_attr($value) . '"';
            }
        }

        return $custom_attributes;
    }
}

if (!function_exists('yatra_html_form_input')) {
    function yatra_html_form_input($args = array())
    {
        $defaults = array(
            'placeholder' => '',
            'required' => false,
            'type' => 'text',
            'class' => '',
            'tag' => '',
            'wrapper_class' => '',
            'label' => '',
            'name' => '',
            'id' => '',
            'value' => '',
            'help' => '',
            'addon' => '',
            'addon_pos' => 'before',
            'custom_attr' => array(),
            'options' => array(),
        );

        $field = wp_parse_args($args, $defaults);
        $field_id = empty($field['id']) ? $field['name'] : $field['id'];

        $field_attributes = array_merge(array(
            'name' => $field['name'],
            'id' => $field_id,
            'class' => $field['class'],
            'placeholder' => $field['placeholder'],
        ), $field['custom_attr']);

        if ($field['required']) {
            $field_attributes['required'] = 'required';
        }

        $custom_attributes = yatra_html_form_custom_attr($field_attributes);

        // open tag
        if (!empty($field['tag'])) {
            echo '<' . $field['tag'] . ' class="yatra-form-field ' . esc_attr($field['name']) . '_field ' . esc_attr($field['wrapper_class']) . '">';
        }

        if (!empty($field['label'])) {
            yatra_html_form_label($field['label'], $field_id, $field['required']);
        }

        if (!empty($field['addon'])) {
            echo '<div class="input-group">';

            if ($field['addon_pos'] == 'before') {
                echo '<span class="input-group-addon">' . $field['addon'] . '</span>';
            }
        }

        switch ($field['type']) {
            case 'text':
            case 'email':
            case 'number':
            case 'hidden':
            case 'date':
            case 'url':
                echo '<input type="' . $field['type'] . '" value="' . esc_attr($field['value']) . '" ' . implode(' ', $custom_attributes) . ' />';
                break;
            case 'single_select_page':
                $page_args = array(
                    'id' => $field['name'],
                    'name' => $field['name'],
                    'sort_column' => 'menu_order',
                    'sort_order' => 'ASC',
                    'show_option_none' => '',
                    'class' => $field['class'],
                    'echo' => 0,
                    'selected' => $field['value'],
                    'post_type' => 'page'

                );

                if (isset($value['args'])) {
                    $page_args = wp_parse_args($field['args'], $page_args);
                }
                echo wp_dropdown_pages($page_args);
                break;
            case 'select':
                if ($field['options']) {
                    echo '<select ' . implode(' ', $custom_attributes) . '>';
                    foreach ($field['options'] as $key => $value) {
                        printf("<option value='%s'%s>%s</option>\n", $key, selected($field['value'], $key, false), $value);
                    }
                    echo '</select>';
                }
                break;

            case 'textarea':
                echo '<textarea ' . implode(' ', $custom_attributes) . '>' . esc_textarea($field['value']) . '</textarea>';
                break;

            case 'wysiwyg':
                $editor_args = [
                    'editor_class' => $field['class'],
                    'textarea_rows' => isset($field['custom_attr']['rows']) ? $field['custom_attr']['rows'] : 10,
                    'media_buttons' => isset($field['custom_attr']['media']) ? $field['custom_attr']['media'] : false,
                    'teeny' => isset($field['custom_attr']['teeny']) ? $field['custom_attr']['teeny'] : true,

                ];

                wp_editor($field['value'], $field['name'], $editor_args);
                break;

            case 'checkbox':
                //echo '<input type="hidden" value="off" name="' . $field['name'] . '" />';
                echo '<span class="checkbox">';
                echo '<label for="' . esc_attr($field_attributes['id']) . '">';
                echo '<input type="checkbox" ' . checked($field['value'], 'on', false) . ' value="on" ' . implode(' ', $custom_attributes) . ' />';
                echo wp_kses_post($field['help']);
                echo '</label>';
                echo '</span>';
                break;

            case 'multicheckbox':

                echo '<span class="checkbox">';
                unset($custom_attributes['id']);

                foreach ($field['options'] as $key => $value) {
                    echo '<label for="' . esc_attr($field_attributes['id']) . '-' . $key . '">';
                    if (!empty($field['value'])) {
                        if (is_array($field['value'])) {
                            $checked = in_array($key, $field['value']) ? 'checked' : '';
                        } else if (is_string($field['value'])) {
                            $checked = in_array($key, explode(',', $field['value'])) ? 'checked' : '';
                        } else {
                            $checked = '';
                        }
                    } else {
                        $checked = '';
                    }

                    echo '<input type="checkbox" ' . $checked . ' id="' . esc_attr($field_attributes['id']) . '-' . $key . '" value="' . $key . '" ' . implode(' ', $custom_attributes) . ' />';
                    echo '<span class="checkbox-value">' . wp_kses_post($value) . '</span>';
                    echo '</label>';
                }
                echo '</span>';
                break;

            case 'radio':
                echo '<span class="checkbox">';
                if ($field['options']) {
                    foreach ($field['options'] as $key => $value) {
                        echo '<input type="radio" ' . checked($field['value'], $key, false) . ' value="' . $key . '" ' . implode(' ', $custom_attributes) . ' id="' . esc_attr($field_attributes['id']) . '-' . $key . '"/>' . $value . '&nbsp; <br><br>';
                    }
                }
                echo '</span>';
                break;

            default:
                break;
        }

        if (!empty($field['addon'])) {

            if ($field['addon_pos'] == 'after') {
                echo '<span class="input-group-addon">' . $field['addon'] . '</span>';
            }

            echo '</div>';
        }

        if ($field['type'] != 'checkbox') {
            yatra_html_form_help($field['help']);
        }

        // closing tag
        if (!empty($field['tag'])) {
            echo '</' . $field['tag'] . '>';
        }
    }
}


if (!function_exists('yatra_html_generate_dropdown')) {
    function yatra_html_generate_dropdown($values = array(), $selected = null)
    {
        $dropdown = '';

        if ($values) {
            foreach ($values as $key => $label) {
                $dropdown .= sprintf("<option value='%s'%s>%s</option>\n", $key, selected($selected, $key, false), $label);
            }
        }

        return $dropdown;
    }
}

if (!function_exists('yatra_html_show_notice')) {

    function yatra_html_show_notice($text, $type = 'updated')
    {
        ?>
        <div class="<?php echo esc_attr($type); ?>">
            <p><strong><?php echo $text; ?></strong></p>
        </div>
        <?php
    }
}
if (!function_exists('yatra_nice_input_number_field')) {
    function yatra_nice_input_number_field($field_name, $max = '', $min = '', $value = '', $class = '')
    {
        $max = $max === '' ? 9999 : absint($max);
        $min = $min === '' ? 0 : absint($min);
        $value = $value === '' ? $min : absint($value);
        $value = $value < $min ? $min : $value;
        $value = $value > $max ? $max : $value;
        ?>
        <div class="yatra-nice-input-number">
            <button type="button" class="nice-button minus-button">
                <span class="icon fa fa-minus"></span>
            </button>

            <input readonly
                   data-step="1"
                   data-max="<?php echo esc_attr($max); ?>"
                   data-min="<?php echo esc_attr($min) ?>"
                   id="<?php echo esc_attr($field_name) ?>"
                   type="number"
                   name="<?php echo esc_attr($field_name) ?>"
                   value="<?php echo absint($value) ?>"
                   class="<?php echo esc_attr($class); ?>"
            />
            <button type="button" class="nice-button plus-button">
                <span class="icon fa fa-plus"></span>
            </button>

        </div>
        <?php
    }
}
if (!function_exists('yatra_pricing_html')) {

    function yatra_pricing_html($pricing_array, $yatra_currency_symbol, $number_of_person)
    {
        ob_start();

        yatra_get_template('myaccount/tmpl-pricing-header.php');

        if ($pricing_array instanceof Yatra_Tour_Pricing) {


            yatra_get_template('myaccount/tmpl-pricing-item.php',

                array(
                    'pricing' => $pricing_array,
                    'currency' => $yatra_currency_symbol,
                    'person' => $number_of_person
                )

            );

        } else {


            foreach ($pricing_array as $pricing) {

                if ($pricing instanceof Yatra_Tour_Pricing) {

                    $person = isset($number_of_person[$pricing->getID()]) ? $number_of_person[$pricing->getID()] : '';

                    yatra_get_template('myaccount/tmpl-pricing-item.php',

                        array(
                            'pricing' => $pricing,
                            'currency' => $yatra_currency_symbol,
                            'person' => $person
                        )

                    );

                }
            }

        }
        yatra_get_template('myaccount/tmpl-pricing-footer.php');

        return ob_get_clean();

    }
}

if (!function_exists('yatra_tippy_tooltip')) {
    function yatra_tippy_tooltip($content, $echo = true)
    {
        $tippy_content = '<span class="yatra-tippy-tooltip dashicons dashicons-editor-help" data-tippy-content="' . esc_attr($content) . '"></span>';

        if ($echo) {
            echo $tippy_content;
        }
        return $tippy_content;
    }
}

if (!function_exists('yatra_privacy_agreement')) {

    function yatra_privacy_agreement($id)
    {
        $id = sanitize_text_field($id);

        $show_privacy_policy = get_option($id, 'no');

        if ('yes' === $show_privacy_policy) {

            $agree_label = get_option('yatra_privacy_policy_agree_label', __('Agree to Privacy Policy?', 'yatra'));

            $privacy_page = absint(get_option('wp_page_for_privacy_policy', 0));

            ob_start();
            if ($privacy_page > 0) {
                ?>

                <fieldset id="yatra-privacy-policy-agreement">

                    <div class="yatra-privacy-policy-agreement">
                        <input name="yatra_agree_to_privacy_policy" class="required" type="checkbox"
                               id="yatra-agree-to-privacy-policy" value="1"/>
                        <label for="yatra-agree-to-privacy-policy"><?php echo stripslashes($agree_label); ?></label>
                        <a href="<?php echo esc_url(get_permalink($privacy_page)) ?>"
                           target="_blank"><?php echo __('View Privacy Policy', 'yatra') ?></a>
                    </div>

                </fieldset>

                <?php
            } else {

                echo '<p class="yatra-warning">';
                echo __('Privacy policy page is not setup properly.', 'yatra');
                echo '</p>';
            }
            $html_output = ob_get_clean();

            echo apply_filters('yatra_checkout_privacy_policy_agreement_html', $html_output);
        }
    }
}

if (!function_exists('yatra_terms_agreement')) {

    function yatra_terms_agreement($id)
    {
        $id = sanitize_text_field($id);

        $show_terms_agreement = get_option($id, 'no');

        if ($show_terms_agreement === 'yes') {

            $agree_label = get_option('yatra_terms_and_conditions_agree_label', __('Agree to Privacy Policy?', 'yatra'));

            $terms_page = absint(get_option('yatra_terms_and_conditions_page', 0));

            ob_start();

            if ($terms_page > 0) {
                ?>

                <fieldset id="yatra-terms-agreement">

                    <div class="yatra-terms-agreement">
                        <input name="yatra_agree_to_terms_and_conditions" class="required" type="checkbox"
                               id="yatra-agree-to-terms" value="1"/>
                        <label for="yatra-agree-to-terms"><?php echo stripslashes($agree_label); ?></label>
                        <a href="<?php echo esc_url(get_permalink($terms_page)) ?>"
                           target="_blank"><?php echo __('View Terms & Conditions', 'yatra') ?></a>
                    </div>

                </fieldset>

                <?php
            } else {

                echo '<p class="yatra-warning">';
                echo __('Terms and conditions page is not setup properly.', 'yatra');
                echo '</p>';
            }
            $html_output = ob_get_clean();

            echo apply_filters('yatra_checkout_terms_and_conditions_agreement_html', $html_output);
        }
    }
}

if (!function_exists('yatra_calendar_booking_indicators')) {
    function yatra_calendar_booking_indicators()
    {
        $booking_text = get_option('yatra_available_for_booking_text', __('Available For Booking', 'yatra'));
        $enquiry_text = get_option('yatra_available_for_enquiry_text', __('Available For Enquiry Only', 'yatra'));
        $not_available_text = get_option('yatra_not_available_for_booking_enquiry_text', __('Not Available For Booking & Enquiry', 'yatra'));
        if ($booking_text != '' || $enquiry_text != '' || $not_available_text != '') {
            echo '<ul class="symbol yatra-calendar-booking-indicator-lists">';
            if ($booking_text != '') {
                echo '<li class="yatra-tippy-tooltip booking" data-tippy-content="' . esc_attr($booking_text) . '"><span>' . esc_html($booking_text) . '</span></li>';
            }
            if ($enquiry_text != '') {
                echo '<li class="yatra-tippy-tooltip enquery" data-tippy-content="' . esc_attr($enquiry_text) . '"><span>' . esc_html($enquiry_text) . '</span></li>';
            }
            if ($not_available_text != '') {
                echo '<li class="yatra-tippy-tooltip not-available" data-tippy-content="' . esc_attr($not_available_text) . '"><span>' . esc_html($not_available_text) . '</span></li>';
            }
            echo '</ul>';
        }
    }
}