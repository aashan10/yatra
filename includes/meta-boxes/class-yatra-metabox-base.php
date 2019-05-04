<?php
if (!class_exists('Yatra_Metabox_Base')) {
    abstract class Yatra_Metabox_Base
    {
        abstract function save($post_id);

        abstract function metabox_form();

        abstract function metabox_config($key);

        abstract function callback($args);

        public function sanitize($field_value, $meta_field = array())
        {
            $updated_value = '';

            switch ($meta_field['type']) {
                // Allow only integers in number fields
                case 'number':
                    $updated_value = absint($field_value);
                    break;
                // Allow some tags in textareas
                case 'textarea':

                    if (isset($meta_field['allowed_tags'])) {

                        $allowed_tags = $meta_field['allowed_tags'];

                    } else {

                        $allowed_tags = array(
                            'p' => array(),
                            'em' => array(),
                            'strong' => array(),
                            'a' => array(
                                'href' => array(),
                            ),
                        );
                    }
                    $updated_value = wp_kses($field_value, $allowed_tags);
                    break;
                // No allowed tags for all other fields
                case 'url':
                case 'image':
                    $updated_value = esc_url_raw($field_value);
                    break;
                case 'select':
                    $is_multiple = isset($meta_field['is_multiple']) && (boolean)$meta_field['is_multiple'] ? true : false;

                    if ($is_multiple) {

                        $array = array_map('sanitize_text_field', wp_unslash($field_value));

                        $updated_value = array_map('wp_kses_post', $array);

                    } else {

                        $updated_value = wp_kses_post(sanitize_text_field($field_value));
                    }
                    break;
                default:
                    $updated_value = wp_kses_post(sanitize_text_field($field_value));
                    break;

            }


            return $updated_value;
        }

        public function metabox_html($field = array())
        {
            global $post;

            $post_id = $post->ID;

            $extra_attributes = array();

            if (!isset($field['name']) || !isset($field['type'])) {
                return;
            }
            if (empty($field['name'])) {

                return;
            }
            $field_key = $field['name'];

            $post_meta = get_post_meta($post_id, $field_key, true);

            $value = isset($post_meta) ? $post_meta : $field['default'];

            $extra_attributes = isset($field['extra_attributes']) ? $field['extra_attributes'] : array();

            $extra_attribute_text = '';

            foreach ($extra_attributes as $attribute_key => $attribute_value) {

                $extra_attribute_text .= ' ' . esc_html($attribute_key) . '="' . esc_attr($attribute_value) . '"';
            }

            $field_type = $field['type'];

            $wrap_class = isset($field['wrap_class']) ? $field['wrap_class'] : '';

            $row_start = isset($field['row_start']) ? (boolean)$field['row_start'] : false;

            if ($row_start) {

                echo '<div class="yatra-field-row">';
            }

            echo '<div class="yatra-field-wrap ' . esc_attr($wrap_class) . '">';

            switch ($field['type']) {
                case "text":
                case "number":
                    ?>
                    <p>
                        <label
                                for="<?php echo esc_attr(($field_key)); ?>"><?php echo esc_html($field['title']); ?>
                            :</label>
                        <input class="widefat"
                               id="<?php echo esc_attr(($field_key)); ?>"
                               name="<?php echo esc_attr(($field_key)); ?>"
                               type="<?php echo esc_attr($field_type) ?>"
                               value="<?php echo esc_attr($value); ?>" <?php echo $extra_attribute_text; ?>/>


                    </p>
                    <?php
                    break;
                case "textarea":

                    $editor = isset($field['editor']) ? (boolean)$field['editor'] : false;

                    $editor_settings = isset($field['editor_settings']) ? $field['editor_settings'] : array();

                    $editor_height = isset($editor_settings['editor_height']) ? (int)$field['editor_height'] : 350;

                    $editor_default_settings = array(
                        'textarea_name' => $field_key,
                        'tinymce' => array(
                            'init_instance_callback ' => 'function(inst) {
                                       $("#" + inst.id + "_ifr").css({minHeight: "' . $editor_height . 'px"});
                                }'
                        ),


                    );


                    $editor_settings = wp_parse_args($editor_default_settings, $editor_settings);

                    ?>
                    <p>
                    <label
                            for="<?php echo esc_attr(($field_key)); ?>"><?php echo esc_html($field['title']); ?>
                        :</label>
                    <?php
                    if ($editor) {
                        echo '</p>';
                        wp_editor($value, $field_key, $editor_settings);
                    } else {
                        ?>
                        <textarea class="widefat"
                                  id="<?php echo esc_attr(($field_key)); ?>"
                                  name="<?php echo esc_attr(($field_key)); ?>"
                            <?php echo $extra_attribute_text; ?>

                        ><?php echo esc_html($value); ?></textarea>


                        </p>
                    <?php }
                    break;
                case "select":
                    ?>
                    <p>
                        <label
                                for="<?php echo esc_attr(($field_key)); ?>"><?php echo esc_html($field['title']); ?>
                            :</label>
                        <?php
                        $options = isset($field['options']) ? $field['options'] : array();
                        $is_multi_select = isset($field['is_multiple']) ? (boolean)$field['is_multiple'] : false;
                        $is_select2 = isset($field['select2']) ? (boolean)$field['select2'] : false;
                        if ($is_multi_select) {
                            $extra_attribute_text .= ' multiple="multiple"';
                        }
                        $select_class = 'widefat';
                        $select_class .= $is_select2 ? ' yatra-select2' : '';
                        ?>

                        <select class="<?php echo esc_attr($select_class); ?>"
                                id="<?php echo esc_attr(($field_key)); ?>"
                                name="<?php echo esc_attr(($field_key));
                                echo $is_multi_select ? '[]' : ''; ?>"
                            <?php echo $extra_attribute_text; ?>>
                            <?php foreach ($options as $option_key => $option_value) {

                                if (!$is_multi_select) {
                                    if (is_array($value)) {
                                        $value = $value[0];
                                    }
                                    $selected = $option_key == $value ? true : false;
                                } else {
                                    if (!is_array($value)) {
                                        $value = array($value);
                                    }
                                    $selected = in_array($option_key, $value) ? true : false;
                                }

                                ?>
                                <option <?php echo $selected ? 'selected="selected"' : ''; ?>
                                value="<?php echo esc_attr($option_key); ?>"><?php echo esc_html($option_value) ?></option><?php
                            }
                            ?>
                        </select>


                    </p>
                    <?php
                    break;
                case "image":
                    ?>
                    <p><label
                                for="<?php echo esc_attr(($field_key)); ?>"><?php echo esc_html($field['title']); ?>
                            :</label>
                    <div class="media-uploader" id="<?php echo('background_image'); ?>">
                        <div class="custom_media_preview">
                            <img style="<?php echo empty($value) ? 'display:none;' : '' ?>max-width:100%;"
                                 class="media_preview_image"
                                 src="<?php echo esc_url($value); ?>" alt=""/>

                            <input class="widefat custom_media_input" type="hidden"
                                   id="<?php echo esc_attr(($field_key)); ?>"
                                   name="<?php echo esc_attr(($field_key)); ?>"
                                <?php echo $extra_attribute_text; ?>
                                   type="text" value="<?php echo esc_html($value); ?>"/>
                            <button class="media_upload button"
                                    id="<?php echo('background_image'); ?>"
                                    data-choose="<?php esc_attr_e('Choose an image', 'mantranews'); ?>"
                                    data-update="<?php esc_attr_e('Use image', 'mantranews'); ?>"
                                    style="width:100%;margin-top:6px;margin-right:30px;"><?php esc_html_e('Select an Image', 'mantranews'); ?></button>
                        </div>

                    </div>
                    </p>
                    <?php
                    break;

            }
            echo "</div>";

            $row_end = isset($field['row_end']) ? (boolean)$field['row_end'] : false;

            if ($row_end) {

                echo '</div>';
            }


        }
    }
}