<?php

/**
 * The template for displaying the footer
 *
 * Contains the opening of the #site-footer div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Twenty_Twenty
 * @since Twenty Twenty 1.0
 */

?>

<div role="contentinfo" class="footer container-full">
	<div class="footer-block container">
		<div class="company-info">
			<div class="logo">
				<img src="<?php echo get_template_directory_uri() ?>/assets/images/footer-logo.png" alt="footer logo">
			</div>
			<div class="get-help">
				<a class="btn btn-primary" href="">Get Help</a>
			</div>
		</div>
		<ul class="userful-links">
			<div class="links-heading">Useful Links</div>
			<li><a href="">Home</a></li>
			<li><a href="">Web Design</a></li>
			<li><a href="">Graphic Design</a></li>
			<li><a href="">Video Production</a></li>
		</ul>
		<ul>
			<div class="links-heading">Quicks Links</div>
			<li><a href="">Marketing</a></li>
			<li><a href="">App Development</a></li>
			<li><a href="">Terms & Conditions</a></li>
			<li><a href="">Contact us</a></li>
			<li><a href="">Privacy ploicy</a></li>
		</ul>
		<ul>
			<div class="links-heading">Contact Us</div>
			<span class="bold-text">Phone:</span>
			<li>1234.4567.89</li>
			<span class="bold-text">Address:</span>
			<li>Lorem ipsum 123 city abc
				ipsum dolor site mit</li>
			<li class="social-links">
				<a href="" class=""><i class="fab fa-facebook-f"></i></a>
				<a href="" class=""><i class="fab fa-twitter"></i></a>
				<a href="" class=""><i class="fab fa-linkedin-in"></i></a>
				<a href="" class=""><i class="fab fa-youtube"></i></a>
			</li>
		</ul>
	</div>
	<div class="copy-right container-full">
		<span>
			<?php bloginfo('name'); ?> -
			Copyright © <?php
						echo date_i18n(
							/* translators: Copyright date format, see https://www.php.net/date */
							_x('Y', 'copyright date format', 'twentytwenty')
						);
						?></span>
	</div>
</div>
</div> <!-- Main Container Ends here -->

<?php wp_footer(); ?>

</body>

</html>