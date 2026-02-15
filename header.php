<?php
/**
 * The Header for our theme.
 *
 * @package OceanWP WordPress theme
 */

?>
<!DOCTYPE html>
<html class="<?php echo esc_attr( oceanwp_html_classes() ); ?>" <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?> <?php oceanwp_schema_markup( 'html' ); ?>>

	<?php wp_body_open(); ?>

	<!-- Preloader Start -->
	<style>
		#oceanwp-preloader{position:fixed;top:0;left:0;width:100%;height:100%;height:100dvh;background:#fff;display:flex;align-items:center;justify-content:center;z-index:999999}
		.preloader-inner{text-align:center;padding:0 20px}
		.preloader-logo{max-width:160px;height:auto;margin-bottom:25px;animation:plPulse 1.5s ease-in-out infinite}
		.preloader-site-name{font-size:28px;font-weight:600;color:#333;margin:0 0 25px;letter-spacing:2px;animation:plPulse 1.5s ease-in-out infinite}
		.preloader-bar{width:200px;height:3px;background:#e0e0e0;border-radius:3px;overflow:hidden;margin:0 auto}
		.preloader-bar-inner{height:100%;background:#13aff0;border-radius:3px;animation:plProgress 1.8s ease-in-out infinite}
		@keyframes plPulse{0%,100%{opacity:1}50%{opacity:.5}}
		@keyframes plProgress{0%{width:0;margin-left:0}50%{width:70%;margin-left:0}100%{width:0;margin-left:100%}}
		@media(max-width:480px){.preloader-logo{max-width:120px}.preloader-site-name{font-size:22px}.preloader-bar{width:150px}}
	</style>
	<div id="oceanwp-preloader">
		<div class="preloader-inner">
			<?php
			$custom_logo_id = get_theme_mod( 'custom_logo' );
			if ( $custom_logo_id ) {
				$logo_url = wp_get_attachment_image_url( $custom_logo_id, 'medium' );
				echo '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" class="preloader-logo">';
			} else {
				echo '<h2 class="preloader-site-name">' . esc_html( get_bloginfo( 'name' ) ) . '</h2>';
			}
			?>
			<div class="preloader-bar"><div class="preloader-bar-inner"></div></div>
		</div>
	</div>
	<script>
		(function(){
			var d=document,p=d.getElementById("oceanwp-preloader");
			if(!p)return;
			function hide(){
				if(p.style.display==="none")return;
				p.style.transition="opacity .5s";
				p.style.opacity="0";
				setTimeout(function(){p.style.display="none"},500);
			}
			if(d.readyState==="complete"){hide()}
			else{window.addEventListener("load",hide)}
			setTimeout(hide,4000);
		})();
	</script>
	<!-- Preloader End -->

	<?php do_action( 'ocean_before_outer_wrap' ); ?>

	<div id="outer-wrap" class="site clr">

		<a class="skip-link screen-reader-text" href="#main"><?php echo esc_html( oceanwp_theme_strings( 'owp-string-header-skip-link', false ) ); ?></a>

		<?php do_action( 'ocean_before_wrap' ); ?>

		<div id="wrap" class="clr">

			<?php do_action( 'ocean_top_bar' ); ?>

			<?php do_action( 'ocean_header' ); ?>

			<?php do_action( 'ocean_before_main' ); ?>

			<main id="main" class="site-main clr"<?php oceanwp_schema_markup( 'main' ); ?> role="main">

				<?php do_action( 'ocean_page_header' ); ?>
