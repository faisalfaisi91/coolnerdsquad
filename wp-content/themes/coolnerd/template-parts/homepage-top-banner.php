<div class="header-banner container-full">
    <div class="container">
        <div class="header-banner-text">

            <!-- <h1><span class="clr-red">Cool</span> Nerd Squad</h1> -->
            <div class="header-banner-logo">
                <img src="<?php echo get_field('left_section_logo'); ?>" alt="banner-logo">
            </div>
            <p><?php echo get_field('left_section_content'); ?></p>
            <div>
                <?php if (!empty(get_field('learn_more_button'))) { ?>
                    <a href="<?php echo get_field('learn_more_button'); ?>" class="btn btn-primary">Learn More</a>
                <?php }
                if (!empty(get_field('contact_us_button'))) { ?>
                    <a href="<?php echo get_field('contact_us_button') ?>" class="btn btn-white">Contact Us</a>
                <?php } ?>
            </div>
        </div>
        <div class="header-banner-bg"></div>
    </div>
</div>