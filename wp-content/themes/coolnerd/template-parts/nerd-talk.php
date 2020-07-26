<div class="tech">
    <div class="tech-block">
        <div class="tech-bg">
            <img src="<?php echo get_field('tech_left_image'); ?>" alt="technology">
        </div>
        <div class="tech-text">
            <h2>
                <div class="clr-red"><?php echo get_field('tech_right_heading_1'); ?></div>
                <div><?php echo get_field('tech_right_heading_2'); ?></div>
            </h2>
            <?php echo get_field('tech_description'); ?>
            <a class="btn btn-primary" href="<?php echo get_field('tech_talk_button'); ?>"><?php echo get_field('tech_talk_button_text'); ?></a>
        </div>
    </div>
</div>