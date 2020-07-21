<div class="partners container-full">
    <div class="container">
        <h2>
            <span class="clr-red">Our</span>
            <span>Partners</span>
        </h2>
        <div class="partners-block">
            <?php foreach (get_field('partner_logos') as $logo) { ?>
                <img src="<?php echo $logo['brand_image'] ?>" alt="last-pass">
            <?php } ?>
        </div>
    </div>
</div>