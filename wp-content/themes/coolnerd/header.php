<?php

/**
 * Header file for the Cool Nerd Squad WordPress default theme.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Coolnerd_Squad
 * @since Cool Nerd Squad 1.0
 */

?>
<!DOCTYPE html>

<html class="no-js" <?php language_attributes(); ?>>

<head>

	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>
	<div class="container">
		<?php
		wp_body_open();
		?>
		<div class="navbar navbar-expand-lg">
			<div class="navbar-brand">
				<a class="" href="<?php echo site_url(); ?>">
					<?php
					twentytwenty_site_logo();
					?>
				</a>
			</div>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav">
					<?php
					if (has_nav_menu('primary')) {

						wp_nav_menu(
							array(
								'container'  => '',
								'items_wrap' => '%3$s',
								'theme_location' => 'primary',
							)
						);
					}
					?>
				</ul>
			</div>
		</div>