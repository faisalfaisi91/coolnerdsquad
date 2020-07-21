<?php
/**
 * Template Name: Widget
 * Description: The default widget template.
 * Force: view-layout-normal
 */


$continuous_slide = ( isset( $atts['slideshow_settings']['continuous_sliding'] ) && '1' == $atts['slideshow_settings']['continuous_sliding'] ) ? 'true' : 'false';

do_action( 'wpmtst_before_view' );
?>

<div class="strong-view strong-widget <?php wpmtst_container_class(); ?>"<?php wpmtst_container_data(); ?>>
	<?php do_action( 'wpmtst_view_header' ); ?>

	<div class="strong-content <?php wpmtst_content_class(); ?>">
	<?php do_action( 'wpmtst_before_content', $atts ); ?>

		<?php while ( $query->have_posts() ) : $query->the_post(); ?>
		<div class="<?php wpmtst_post_class($atts); ?>">
            <div class="wpmtst-testimonial-inner testimonial-inner">
				<?php do_action( 'wpmtst_before_testimonial' ); ?>
                
				<?php wpmtst_the_title( 'h3', 'wpmtst-testimonial-heading' ); ?>

				<div <?php echo ('slideshow' == $atts['mode']) ? 'data-infinite-loop="'.esc_attr($continuous_slide).'"' : ''; ?>  class="wpmtst-testimonial-content testimonial-content">
          <?php wpmtst_the_thumbnail(); ?>
					<div class="maybe-clear"></div>
					<?php wpmtst_the_content(); ?>
					<?php do_action( 'wpmtst_after_testimonial_content' ); ?>
				</div>

				<?php wpmtst_the_client(); ?>

				<div class="clear"></div>

				<?php do_action( 'wpmtst_after_testimonial' ,$atts); ?>
			</div>

		</div>
		<?php endwhile; ?>

		<?php do_action( 'wpmtst_after_testimonial' ,$atts); ?>
	</div>

	<?php do_action( 'wpmtst_view_footer' ); ?>
</div>

<?php do_action( 'wpmtst_after_view' ); ?>