<div class="yatra-tour-additional-info">
    <div class="yatra-tour-additional-info-item">
        <div class="icon-wrap">
            <i class="icon fa fa-user-clock"></i>
        </div>
        <div class="icon-content">
            <span class="info-title"><?php echo esc_html__('Duration', 'yatra'); ?></span>
            <p class="info-content"><?php echo esc_html($additional_info['tour_duration']); ?></p>
        </div>
    </div>
    <div class="yatra-tour-additional-info-item">
        <div class="icon-wrap">
            <i class="icon fa fa-globe"></i>
        </div>
        <div class="icon-content">
            <span class="info-title"><?php echo esc_html__('Country', 'yatra'); ?></span>
            <p class="info-content"><?php echo esc_html($additional_info['country']); ?></p>
        </div>
    </div>
    <div class="yatra-tour-additional-info-item"
         >
        <span class="question yatra-tippy-tooltip fa fa-info-circle"
              data-tippy-content="This might be different as per your booking date"></span>
        <div class="icon-wrap">
            <i class="icon fa fa-check"></i>
        </div>
        <div class="icon-content">
            <span class="info-title"><?php echo esc_html__('Maximum Traveller', 'yatra'); ?></span>
            <p class="info-content"><?php echo esc_html($additional_info['max_travellers']); ?></p>
        </div>
    </div>
    <div class="yatra-tour-additional-info-item"
    >
        <span class="question yatra-tippy-tooltip fa fa-info-circle"
              data-tippy-content="This might be different as per your booking date"></span>
        <div class="icon-wrap">
            <i class="icon fa fa-chair"></i>
        </div>
        <div class="icon-content">
            <span class="info-title"><?php echo esc_html__('Min Pax', 'yatra'); ?></span>
            <p class="info-content"><?php echo esc_html($additional_info['max_travellers']); ?></p>
        </div>
    </div>

</div>
<?php if (yatra_tour_has_attributes()) { ?>
    <div class="yatra-tour-attribute-info">
        <?php
        yatra_tour_custom_attributes_template(); ?>
    </div>
<?php } ?>
