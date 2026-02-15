<?php
/**
 * Theme functions and definitions.
 *
 * Sets up the theme and provides some helper functions
 *
 * When using a child theme (see http://codex.wordpress.org/Theme_Development
 * and http://codex.wordpress.org/Child_Themes), you can override certain
 * functions (those wrapped in a function_exists() call) by defining them first
 * in your child theme's functions.php file. The child theme's functions.php
 * file is included before the parent theme's file, so the child theme
 * functions would be used.
 *
 *
 * For more information on hooks, actions, and filters,
 * see http://codex.wordpress.org/Plugin_API
 *
 * @package OceanWP WordPress theme
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Core Constants.
define( 'OCEANWP_THEME_DIR', get_template_directory() );
define( 'OCEANWP_THEME_URI', get_template_directory_uri() );

/**
 * OceanWP theme class
 */
final class OCEANWP_Theme_Class {

	/**
	 * Main Theme Class Constructor
	 *
	 * @since   1.0.0
	 */
	public function __construct() {
		// Migrate
		$this->migration();

		// Define theme constants.
		$this->oceanwp_constants();

		// Load required files.
		$this->oceanwp_has_setup();

		// Load framework classes.
		add_action( 'after_setup_theme', array( 'OCEANWP_Theme_Class', 'classes' ), 4 );

		// Setup theme => add_theme_support, register_nav_menus, load_theme_textdomain, etc.
		add_action( 'after_setup_theme', array( 'OCEANWP_Theme_Class', 'theme_setup' ), 10 );

		// Fires after the theme is switched.
		add_action( 'switch_theme', array( 'OCEANWP_Theme_Class', 'theme_switch' ) );

		// register sidebar widget areas.
		add_action( 'widgets_init', array( 'OCEANWP_Theme_Class', 'register_sidebars' ) );

		// Registers theme_mod strings into Polylang.
		if ( class_exists( 'Polylang' ) ) {
			add_action( 'after_setup_theme', array( 'OCEANWP_Theme_Class', 'polylang_register_string' ) );
		}

		/** Admin only actions */
		if ( is_admin() ) {

			// Load scripts in the WP admin.
			add_action( 'admin_enqueue_scripts', array( 'OCEANWP_Theme_Class', 'admin_scripts' ) );

			// Outputs custom CSS for the admin.
			add_action( 'admin_head', array( 'OCEANWP_Theme_Class', 'admin_inline_css' ) );

			/** Non Admin actions */
		} else {
			// Load theme js.
			add_action( 'wp_enqueue_scripts', array( 'OCEANWP_Theme_Class', 'theme_js' ) );

			// Load theme CSS.
			add_action( 'wp_enqueue_scripts', array( 'OCEANWP_Theme_Class', 'theme_css' ) );

			// Load his file in last.
			add_action( 'wp_enqueue_scripts', array( 'OCEANWP_Theme_Class', 'custom_style_css' ), 9999 );

			// Remove Customizer CSS script from Front-end.
			add_action( 'init', array( 'OCEANWP_Theme_Class', 'remove_customizer_custom_css' ) );

			// Add a pingback url auto-discovery header for singularly identifiable articles.
			add_action( 'wp_head', array( 'OCEANWP_Theme_Class', 'pingback_header' ), 1 );

			// Add meta viewport tag to header.
			add_action( 'wp_head', array( 'OCEANWP_Theme_Class', 'meta_viewport' ), 1 );

			// Add an X-UA-Compatible header.
			add_filter( 'wp_headers', array( 'OCEANWP_Theme_Class', 'x_ua_compatible_headers' ) );

			// Outputs custom CSS to the head.
			add_action( 'wp_head', array( 'OCEANWP_Theme_Class', 'custom_css' ), 999 );

			// Minify the WP custom CSS because WordPress doesn't do it by default.
			add_filter( 'wp_get_custom_css', array( 'OCEANWP_Theme_Class', 'minify_custom_css' ) );

			// Alter the search posts per page.
			add_action( 'pre_get_posts', array( 'OCEANWP_Theme_Class', 'search_posts_per_page' ) );

			// Alter WP categories widget to display count inside a span.
			add_filter( 'wp_list_categories', array( 'OCEANWP_Theme_Class', 'wp_list_categories_args' ) );

			// Add a responsive wrapper to the WordPress oembed output.
			add_filter( 'embed_oembed_html', array( 'OCEANWP_Theme_Class', 'add_responsive_wrap_to_oembeds' ), 99, 4 );

			// Adds classes the post class.
			add_filter( 'post_class', array( 'OCEANWP_Theme_Class', 'post_class' ) );

			// Add schema markup to the authors post link.
			add_filter( 'the_author_posts_link', array( 'OCEANWP_Theme_Class', 'the_author_posts_link' ) );

			// Add support for Elementor Pro locations.
			add_action( 'elementor/theme/register_locations', array( 'OCEANWP_Theme_Class', 'register_elementor_locations' ) );

			// Remove the default lightbox script for the beaver builder plugin.
			add_filter( 'fl_builder_override_lightbox', array( 'OCEANWP_Theme_Class', 'remove_bb_lightbox' ) );

			add_filter( 'ocean_enqueue_generated_files', '__return_false' );
		}
	}

	/**
	 * Migration Functinality
	 *
	 * @since   1.0.0
	 */
	public static function migration() {
		if ( get_theme_mod( 'ocean_disable_emoji', false ) ) {
			set_theme_mod( 'ocean_performance_emoji', 'disabled' );
		}

		if ( get_theme_mod( 'ocean_disable_lightbox', false ) ) {
			set_theme_mod( 'ocean_performance_lightbox', 'disabled' );
		}
	}

	/**
	 * Define Constants
	 *
	 * @since   1.0.0
	 */
	public static function oceanwp_constants() {

		$version = self::theme_version();

		// Theme version.
		define( 'OCEANWP_THEME_VERSION', $version );

		// Javascript and CSS Paths.
		define( 'OCEANWP_JS_DIR_URI', OCEANWP_THEME_URI . '/assets/js/' );
		define( 'OCEANWP_CSS_DIR_URI', OCEANWP_THEME_URI . '/assets/css/' );

		// Include Paths.
		define( 'OCEANWP_INC_DIR', OCEANWP_THEME_DIR . '/inc/' );
		define( 'OCEANWP_INC_DIR_URI', OCEANWP_THEME_URI . '/inc/' );

		// Check if plugins are active.
		define( 'OCEAN_EXTRA_ACTIVE', class_exists( 'Ocean_Extra' ) );
		define( 'OCEANWP_STICKY_HEADER_ACTIVE', class_exists( 'Ocean_Sticky_Header' ) );
		define( 'OCEANWP_STICKY_FOOTER_ACTIVE', class_exists( 'Ocean_Sticky_Footer' ) );
		define( 'OCEANWP_ECOMM_ACTIVE', class_exists( 'Ocean_eCommerce' ) );
		define( 'OCEANWP_ELEMENTOR_ACTIVE', class_exists( 'Elementor\Plugin' ) );
		define( 'OCEANWP_BEAVER_BUILDER_ACTIVE', class_exists( 'FLBuilder' ) );
		define( 'OCEANWP_WOOCOMMERCE_ACTIVE', class_exists( 'WooCommerce' ) );
		define( 'OCEANWP_EDD_ACTIVE', class_exists( 'Easy_Digital_Downloads' ) );
		define( 'OCEANWP_LIFTERLMS_ACTIVE', class_exists( 'LifterLMS' ) );
		define( 'OCEANWP_ALNP_ACTIVE', class_exists( 'Auto_Load_Next_Post' ) );
		define( 'OCEANWP_LEARNDASH_ACTIVE', class_exists( 'SFWD_LMS' ) );
	}

	/**
	 * Load all core theme function files
	 *
	 * @since 1.0.0oceanwp_has_setup
	 */
	public static function oceanwp_has_setup() {

		$dir = OCEANWP_INC_DIR;

		require_once $dir . 'helpers.php';
		require_once $dir . 'header-content.php';
		require_once $dir . 'oceanwp-strings.php';
		require_once $dir . 'oceanwp-svg.php';
		require_once $dir . 'oceanwp-theme-icons.php';
		require_once $dir . 'template-helpers.php';
		require_once $dir . 'ngo-projects.php';
		require_once $dir . 'customizer/webfonts.php';
		require_once $dir . 'walker/init.php';
		require_once $dir . 'walker/menu-walker.php';
		require_once $dir . 'third/class-gutenberg.php';
		require_once $dir . 'third/class-elementor.php';
		require_once $dir . 'third/class-beaver-themer.php';
		require_once $dir . 'third/class-bbpress.php';
		require_once $dir . 'third/class-buddypress.php';
		require_once $dir . 'third/class-lifterlms.php';
		require_once $dir . 'third/class-learndash.php';
		require_once $dir . 'third/class-sensei.php';
		require_once $dir . 'third/class-social-login.php';
		require_once $dir . 'third/class-amp.php';
		require_once $dir . 'third/class-pwa.php';

		// WooCommerce.
		if ( OCEANWP_WOOCOMMERCE_ACTIVE ) {
			require_once $dir . 'woocommerce/woocommerce-config.php';
		}

		// Easy Digital Downloads.
		if ( OCEANWP_EDD_ACTIVE ) {
			require_once $dir . 'edd/edd-config.php';
		}

	}

	/**
	 * Returns current theme version
	 *
	 * @since   1.0.0
	 */
	public static function theme_version() {

		// Get theme data.
		$theme = wp_get_theme();

		// Return theme version.
		return $theme->get( 'Version' );

	}

	/**
	 * Compare WordPress version
	 *
	 * @access public
	 * @since 1.8.3
	 * @param  string $version - A WordPress version to compare against current version.
	 * @return boolean
	 */
	public static function is_wp_version( $version = '5.4' ) {

		global $wp_version;

		// WordPress version.
		return version_compare( strtolower( $wp_version ), strtolower( $version ), '>=' );

	}


	/**
	 * Check for AMP endpoint
	 *
	 * @return bool
	 * @since 1.8.7
	 */
	public static function oceanwp_is_amp() {
		return function_exists( 'is_amp_endpoint' ) && is_amp_endpoint();
	}

	/**
	 * Load theme classes
	 *
	 * @since   1.0.0
	 */
	public static function classes() {

		// Admin only classes.
		if ( is_admin() ) {

			// Recommend plugins.
			require_once OCEANWP_INC_DIR . 'activation-notice/class-oceanwp-plugin-manager.php';
			require_once OCEANWP_INC_DIR . 'activation-notice/template.php';

			// Ajax Actions
			if (defined('DOING_AJAX') && DOING_AJAX) {
				require OCEANWP_INC_DIR . 'activation-notice/api.php';
			}

			// Front-end classes.
		}

		// Breadcrumbs class.
		require_once OCEANWP_INC_DIR . 'breadcrumbs.php';

		// Customizer class.
		require_once OCEANWP_INC_DIR . 'customizer/customizer.php';

	}

	/**
	 * Theme Setup
	 *
	 * @since   1.0.0
	 */
	public static function theme_setup() {

		// Load text domain.
		load_theme_textdomain( 'oceanwp', OCEANWP_THEME_DIR . '/languages' );

		// Get globals.
		global $content_width;

		// Set content width based on theme's default design.
		if ( ! isset( $content_width ) ) {
			$content_width = 1200;
		}

		// Register navigation menus.
		register_nav_menus(
			array(
				'topbar_menu' => esc_html__( 'Top Bar', 'oceanwp' ),
				'main_menu'   => esc_html__( 'Main', 'oceanwp' ),
				'footer_menu' => esc_html__( 'Footer', 'oceanwp' ),
				'mobile_menu' => esc_html__( 'Mobile (optional)', 'oceanwp' ),
			)
		);

		// Enable support for Post Formats.
		add_theme_support( 'post-formats', array( 'video', 'gallery', 'audio', 'quote', 'link' ) );

		// Enable support for <title> tag.
		add_theme_support( 'title-tag' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		// Enable support for Post Thumbnails on posts and pages.
		add_theme_support( 'post-thumbnails' );

		/**
		 * Enable support for header image
		 */
		add_theme_support(
			'custom-header',
			apply_filters(
				'ocean_custom_header_args',
				array(
					'width'       => 2000,
					'height'      => 1200,
					'flex-height' => true,
					'video'       => true,
					'video-active-callback' => '__return_true'
				)
			)
		);

		/**
		 * Enable support for site logo
		 */
		add_theme_support(
			'custom-logo',
			apply_filters(
				'ocean_custom_logo_args',
				array(
					'height'      => 45,
					'width'       => 164,
					'flex-height' => true,
					'flex-width'  => true,
				)
			)
		);

		/*
		 * Switch default core markup for search form, comment form, comments, galleries, captions and widgets
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
				'widgets',
			)
		);

		// Declare WooCommerce support.
		add_theme_support( 'woocommerce' );
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );

		// Add editor style.
		add_editor_style( 'assets/css/editor-style.min.css' );

		// Declare support for selective refreshing of widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		// Theme log.
		self::oceanwp_theme_log();
	}

	/**
	 * Theme Switch
	 *
	 * @since   4.0.7
	 */
	public static function theme_switch() {
		self::oceanwp_theme_log();
	}

	/**
	 * Log the installed version
	 *
	 * @since 4.0.7
	 */
	public static function oceanwp_theme_log() {

		$parent_theme  = wp_get_theme()->parent();
		$current_theme = wp_get_theme();
		$theme_version = '';

		if ( ! empty( $parent_theme) ) {
			$theme_version = $parent_theme->get('Version');
		} else {
			$theme_version = $current_theme->get('Version');
		}

		if ( ! get_option( 'oceanwp_theme_installed_version')) {
			update_option( 'oceanwp_theme_installed_version', $theme_version );
		}
	}

	/**
	 * Adds the meta tag to the site header
	 *
	 * @since 1.1.0
	 */
	public static function pingback_header() {

		if ( is_singular() && pings_open() ) {
			printf( '<link rel="pingback" href="%s">' . "\n", esc_url( get_bloginfo( 'pingback_url' ) ) );
		}

	}

	/**
	 * Adds the meta tag to the site header
	 *
	 * @since 1.0.0
	 */
	public static function meta_viewport() {

		// Meta viewport.
		$viewport = '<meta name="viewport" content="width=device-width, initial-scale=1">';

		// Apply filters for child theme tweaking.
		echo apply_filters( 'ocean_meta_viewport', $viewport ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	}

	/**
	 * Load scripts in the WP admin
	 *
	 * @since 1.0.0
	 */
	public static function admin_scripts() {
		global $pagenow;
		if ( 'nav-menus.php' === $pagenow ) {
			wp_enqueue_style( 'oceanwp-menus', OCEANWP_INC_DIR_URI . 'walker/assets/menus.css', false, OCEANWP_THEME_VERSION );
		}
	}

	/**
	 * Load front-end scripts
	 *
	 * @since   1.0.0
	 */
	public static function theme_css() {

		// Define dir.
		$dir           = OCEANWP_CSS_DIR_URI;
		$theme_version = OCEANWP_THEME_VERSION;

		// Remove font awesome style from plugins.
		wp_deregister_style( 'font-awesome' );
		wp_deregister_style( 'fontawesome' );

		// Enqueue font awesome style.
		if ( get_theme_mod( 'ocean_performance_fontawesome', 'enabled' ) === 'enabled' ) {
			wp_enqueue_style( 'font-awesome', OCEANWP_THEME_URI . '/assets/fonts/fontawesome/css/all.min.css', false, '6.7.2' );
		}

		// Enqueue simple line icons style.
		if ( get_theme_mod( 'ocean_performance_simple_line_icons', 'enabled' ) === 'enabled' ) {
			wp_enqueue_style( 'simple-line-icons', $dir . 'third/simple-line-icons.min.css', false, '2.4.0' );
		}

		// Enqueue Main style.
		wp_enqueue_style( 'oceanwp-style', $dir . 'style.min.css', false, $theme_version );

		// Blog Header styles.
		if ( 'default' !== get_theme_mod( 'oceanwp_single_post_header_style', 'default' )
			&& is_single() && 'post' === get_post_type() ) {
			wp_enqueue_style( 'oceanwp-blog-headers', $dir . 'blog/blog-post-headers.css', false, $theme_version );
		}

		// Register perfect-scrollbar plugin style.
		wp_register_style( 'ow-perfect-scrollbar', $dir . 'third/perfect-scrollbar.css', false, '1.5.0' );

		// Register hamburgers buttons to easily use them.
		wp_register_style( 'oceanwp-hamburgers', $dir . 'third/hamburgers/hamburgers.min.css', false, $theme_version );
		// Register hamburgers buttons styles.
		$hamburgers = oceanwp_hamburgers_styles();
		foreach ( $hamburgers as $class => $name ) {
			wp_register_style( 'oceanwp-' . $class . '', $dir . 'third/hamburgers/types/' . $class . '.css', false, $theme_version );
		}

		// Get mobile menu icon style.
		$mobile_menu = get_theme_mod( 'ocean_mobile_menu_open_hamburger', 'default' );
		// Enqueue mobile menu icon style.
		if ( ! empty( $mobile_menu ) && 'default' !== $mobile_menu ) {
			wp_enqueue_style( 'oceanwp-hamburgers' );
			wp_enqueue_style( 'oceanwp-' . $mobile_menu . '' );
		}

		// If Vertical header style.
		if ( 'vertical' === oceanwp_header_style() ) {
			wp_enqueue_style( 'oceanwp-hamburgers' );
			wp_enqueue_style( 'oceanwp-spin' );
			wp_enqueue_style( 'ow-perfect-scrollbar' );
		}
	}

	/**
	 * Returns all js needed for the front-end
	 *
	 * @since 1.0.0
	 */
	public static function theme_js() {

		if ( self::oceanwp_is_amp() ) {
			return;
		}

		// Get js directory uri.
		$dir = OCEANWP_JS_DIR_URI;

		// Get current theme version.
		$theme_version = OCEANWP_THEME_VERSION;

		// Get localized array.
		$localize_array = self::localize_array();

		// Main script dependencies.
		$main_script_dependencies = array( 'jquery' );

		// Comment reply.
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}

		// Add images loaded.
		wp_enqueue_script( 'imagesloaded' );

		/**
		 * Load Venors Scripts.
		 */

		// Isotop.
		wp_register_script( 'ow-isotop', $dir . 'vendors/isotope.pkgd.min.js', array(), '3.0.6', true );

		// Flickity.
		wp_register_script( 'ow-flickity', $dir . 'vendors/flickity.pkgd.min.js', array(), $theme_version, true );

		// Magnific Popup.
		wp_register_script( 'ow-magnific-popup', $dir . 'vendors/magnific-popup.min.js', array( 'jquery' ), $theme_version, true );

		// Sidr Mobile Menu.
		wp_register_script( 'ow-sidr', $dir . 'vendors/sidr.js', array(), $theme_version, true );

		// Perfect Scrollbar.
		wp_register_script( 'ow-perfect-scrollbar', $dir . 'vendors/perfect-scrollbar.min.js', array(), $theme_version, true );

		// Smooth Scroll.
		wp_register_script( 'ow-smoothscroll', $dir . 'vendors/smoothscroll.min.js', array(), $theme_version, false );

		/**
		 * Load Theme Scripts.
		 */

		// Theme script.
		wp_enqueue_script( 'oceanwp-main', $dir . 'theme.min.js', $main_script_dependencies, $theme_version, true );
		wp_localize_script( 'oceanwp-main', 'oceanwpLocalize', $localize_array );
		array_push( $main_script_dependencies, 'oceanwp-main' );

		// Blog Masonry script.
		if ( 'masonry' === oceanwp_blog_grid_style() ) {
			array_push( $main_script_dependencies, 'ow-isotop' );
			wp_enqueue_script( 'ow-isotop' );
			wp_enqueue_script( 'oceanwp-blog-masonry', $dir . 'blog-masonry.min.js', $main_script_dependencies, $theme_version, true );
		}

		// Menu script.
		switch ( oceanwp_header_style() ) {
			case 'full_screen':
				wp_enqueue_script( 'oceanwp-full-screen-menu', $dir . 'full-screen-menu.min.js', $main_script_dependencies, $theme_version, true );
				break;
			case 'vertical':
				array_push( $main_script_dependencies, 'ow-perfect-scrollbar' );
				wp_enqueue_script( 'ow-perfect-scrollbar' );
				wp_enqueue_script( 'oceanwp-vertical-header', $dir . 'vertical-header.min.js', $main_script_dependencies, $theme_version, true );
				break;
		}

		// Mobile Menu script.
		switch ( oceanwp_mobile_menu_style() ) {
			case 'dropdown':
				wp_enqueue_script( 'oceanwp-drop-down-mobile-menu', $dir . 'drop-down-mobile-menu.min.js', $main_script_dependencies, $theme_version, true );
				break;
			case 'fullscreen':
				wp_enqueue_script( 'oceanwp-full-screen-mobile-menu', $dir . 'full-screen-mobile-menu.min.js', $main_script_dependencies, $theme_version, true );
				break;
			case 'sidebar':
				array_push( $main_script_dependencies, 'ow-sidr' );
				wp_enqueue_script( 'ow-sidr' );
				wp_enqueue_script( 'oceanwp-sidebar-mobile-menu', $dir . 'sidebar-mobile-menu.min.js', $main_script_dependencies, $theme_version, true );
				break;
		}

		// Search script.
		switch ( oceanwp_menu_search_style() ) {
			case 'drop_down':
				wp_enqueue_script( 'oceanwp-drop-down-search', $dir . 'drop-down-search.min.js', $main_script_dependencies, $theme_version, true );
				break;
			case 'header_replace':
				wp_enqueue_script( 'oceanwp-header-replace-search', $dir . 'header-replace-search.min.js', $main_script_dependencies, $theme_version, true );
				break;
			case 'overlay':
				wp_enqueue_script( 'oceanwp-overlay-search', $dir . 'overlay-search.min.js', $main_script_dependencies, $theme_version, true );
				break;
		}

		// Mobile Search Icon Style.
		if ( oceanwp_mobile_menu_search_style() !== 'disabled' ) {
			wp_enqueue_script( 'oceanwp-mobile-search-icon', $dir . 'mobile-search-icon.min.js', $main_script_dependencies, $theme_version, true );
		}

		// Equal Height Elements script.
		if ( oceanwp_blog_entry_equal_heights() ) {
			wp_enqueue_script( 'oceanwp-equal-height-elements', $dir . 'equal-height-elements.min.js', $main_script_dependencies, $theme_version, true );
		}

		$perf_lightbox = get_theme_mod( 'ocean_performance_lightbox', 'enabled' );

		// Lightbox script.
		if ( oceanwp_gallery_is_lightbox_enabled() || $perf_lightbox === 'enabled' ) {
			array_push( $main_script_dependencies, 'ow-magnific-popup' );
			wp_enqueue_script( 'ow-magnific-popup' );
			wp_enqueue_script( 'oceanwp-lightbox', $dir . 'ow-lightbox.min.js', $main_script_dependencies, $theme_version, true );
		}

		// Slider script.
		array_push( $main_script_dependencies, 'ow-flickity' );
		wp_enqueue_script( 'ow-flickity' );
		wp_enqueue_script( 'oceanwp-slider', $dir . 'ow-slider.min.js', $main_script_dependencies, $theme_version, true );

		// Scroll Effect script.
		if ( get_theme_mod( 'ocean_performance_scroll_effect', 'enabled' ) === 'enabled' ) {
			wp_enqueue_script( 'oceanwp-scroll-effect', $dir . 'scroll-effect.min.js', $main_script_dependencies, $theme_version, true );
		}

		// Scroll to Top script.
		if ( oceanwp_display_scroll_up_button() ) {
			wp_enqueue_script( 'oceanwp-scroll-top', $dir . 'scroll-top.min.js', $main_script_dependencies, $theme_version, true );
		}

		// Custom Select script.
		if ( get_theme_mod( 'ocean_performance_custom_select', 'enabled' ) === 'enabled' ) {
			wp_enqueue_script( 'oceanwp-select', $dir . 'select.min.js', $main_script_dependencies, $theme_version, true );
		}

		// Infinite Scroll script.
		if ( 'infinite_scroll' === get_theme_mod( 'ocean_blog_pagination_style', 'standard' ) || 'infinite_scroll' === get_theme_mod( 'ocean_woo_pagination_style', 'standard' ) ) {
			wp_enqueue_script( 'oceanwp-infinite-scroll', $dir . 'ow-infinite-scroll.min.js', $main_script_dependencies, $theme_version, true );
		}

		// Load more pagination script
		if ( 'load_more' === get_theme_mod( 'ocean_blog_pagination_style', 'standard' ) || 'load_more' === get_theme_mod( 'ocean_woo_pagination_style', 'standard' ) ) {
			wp_enqueue_script( 'oceanwp-load-more', $dir . 'ow-load-more.min.js', $main_script_dependencies, $theme_version, true );
		}

		// WooCommerce scripts.
		if ( OCEANWP_WOOCOMMERCE_ACTIVE
		&& 'yes' !== get_theme_mod( 'ocean_woo_remove_custom_features', 'no' ) ) {
			wp_enqueue_script( 'oceanwp-woocommerce-custom-features', $dir . 'wp-plugins/woocommerce/woo-custom-features.min.js', array( 'jquery' ), $theme_version, true );
			wp_localize_script( 'oceanwp-woocommerce-custom-features', 'oceanwpLocalize', $localize_array );
		}

		// Register scripts for old addons.
		wp_register_script( 'nicescroll', $dir . 'vendors/support-old-oceanwp-addons/jquery.nicescroll.min.js', array( 'jquery' ), $theme_version, true );
	}

	/**
	 * Functions.js localize array
	 *
	 * @since 1.0.0
	 */
	public static function localize_array() {

		// Create array.
		$sidr_side     = get_theme_mod( 'ocean_mobile_menu_sidr_direction', 'left' );
		$sidr_side     = $sidr_side ? $sidr_side : 'left';
		$sidr_target   = get_theme_mod( 'ocean_mobile_menu_sidr_dropdown_target', 'link' );
		$sidr_target   = $sidr_target ? $sidr_target : 'link';
		$vh_target     = get_theme_mod( 'ocean_vertical_header_dropdown_target', 'link' );
		$vh_target     = $vh_target ? $vh_target : 'link';
		$scroll_offset = get_theme_mod( 'ocean_scroll_effect_offset_value' );
		$scroll_offset = $scroll_offset ? $scroll_offset : 0;
		$array       = array(
			'nonce'                 => wp_create_nonce( 'oceanwp' ),
			'isRTL'                 => is_rtl(),
			'menuSearchStyle'       => oceanwp_menu_search_style(),
			'mobileMenuSearchStyle' => oceanwp_mobile_menu_search_style(),
			'sidrSource'            => oceanwp_sidr_menu_source(),
			'sidrDisplace'          => get_theme_mod( 'ocean_mobile_menu_sidr_displace', true ) ? true : false,
			'sidrSide'              => $sidr_side,
			'sidrDropdownTarget'    => $sidr_target,
			'verticalHeaderTarget'  => $vh_target,
			'customScrollOffset'    => $scroll_offset,
			'customSelects'         => '.woocommerce-ordering .orderby, #dropdown_product_cat, .widget_categories select, .widget_archive select, .single-product .variations_form .variations select',
			'loadMoreLoadingText'   => esc_html__('Loading...', 'oceanwp'),
		);

		// WooCart.
		if ( OCEANWP_WOOCOMMERCE_ACTIVE ) {
			$array['wooCartStyle'] = oceanwp_menu_cart_style();
		}

		// Apply filters and return array.
		return apply_filters( 'ocean_localize_array', $array );
	}

	/**
	 * Add headers for IE to override IE's Compatibility View Settings
	 *
	 * @param obj $headers   header settings.
	 * @since 1.0.0
	 */
	public static function x_ua_compatible_headers( $headers ) {
		$headers['X-UA-Compatible'] = 'IE=edge';
		return $headers;
	}

	/**
	 * Registers sidebars
	 *
	 * @since   1.0.0
	 */
	public static function register_sidebars() {

		$heading = get_theme_mod( 'ocean_sidebar_widget_heading_tag', 'h4' );
		$heading = apply_filters( 'ocean_sidebar_widget_heading_tag', $heading );

		$foo_heading = get_theme_mod( 'ocean_footer_widget_heading_tag', 'h4' );
		$foo_heading = apply_filters( 'ocean_footer_widget_heading_tag', $foo_heading );

		// Default Sidebar.
		register_sidebar(
			array(
				'name'          => esc_html__( 'Default Sidebar', 'oceanwp' ),
				'id'            => 'sidebar',
				'description'   => esc_html__( 'Widgets in this area will be displayed in the left or right sidebar area if you choose the Left or Right Sidebar layout.', 'oceanwp' ),
				'before_widget' => '<div id="%1$s" class="sidebar-box %2$s clr">',
				'after_widget'  => '</div>',
				'before_title'  => '<' . $heading . ' class="widget-title">',
				'after_title'   => '</' . $heading . '>',
			)
		);

		// Left Sidebar.
		register_sidebar(
			array(
				'name'          => esc_html__( 'Left Sidebar', 'oceanwp' ),
				'id'            => 'sidebar-2',
				'description'   => esc_html__( 'Widgets in this area are used in the left sidebar region if you use the Both Sidebars layout.', 'oceanwp' ),
				'before_widget' => '<div id="%1$s" class="sidebar-box %2$s clr">',
				'after_widget'  => '</div>',
				'before_title'  => '<' . $heading . ' class="widget-title">',
				'after_title'   => '</' . $heading . '>',
			)
		);

		// Search Results Sidebar.
		if ( get_theme_mod( 'ocean_search_custom_sidebar', true ) ) {
			register_sidebar(
				array(
					'name'          => esc_html__( 'Search Results Sidebar', 'oceanwp' ),
					'id'            => 'search_sidebar',
					'description'   => esc_html__( 'Widgets in this area are used in the search result page.', 'oceanwp' ),
					'before_widget' => '<div id="%1$s" class="sidebar-box %2$s clr">',
					'after_widget'  => '</div>',
					'before_title'  => '<' . $heading . ' class="widget-title">',
					'after_title'   => '</' . $heading . '>',
				)
			);
		}

		// Footer 1.
		register_sidebar(
			array(
				'name'          => esc_html__( 'Footer 1', 'oceanwp' ),
				'id'            => 'footer-one',
				'description'   => esc_html__( 'Widgets in this area are used in the first footer region.', 'oceanwp' ),
				'before_widget' => '<div id="%1$s" class="footer-widget %2$s clr">',
				'after_widget'  => '</div>',
				'before_title'  => '<' . $foo_heading . ' class="widget-title">',
				'after_title'   => '</' . $foo_heading . '>',
			)
		);

		// Footer 2.
		register_sidebar(
			array(
				'name'          => esc_html__( 'Footer 2', 'oceanwp' ),
				'id'            => 'footer-two',
				'description'   => esc_html__( 'Widgets in this area are used in the second footer region.', 'oceanwp' ),
				'before_widget' => '<div id="%1$s" class="footer-widget %2$s clr">',
				'after_widget'  => '</div>',
				'before_title'  => '<' . $foo_heading . ' class="widget-title">',
				'after_title'   => '</' . $foo_heading . '>',
			)
		);

		// Footer 3.
		register_sidebar(
			array(
				'name'          => esc_html__( 'Footer 3', 'oceanwp' ),
				'id'            => 'footer-three',
				'description'   => esc_html__( 'Widgets in this area are used in the third footer region.', 'oceanwp' ),
				'before_widget' => '<div id="%1$s" class="footer-widget %2$s clr">',
				'after_widget'  => '</div>',
				'before_title'  => '<' . $foo_heading . ' class="widget-title">',
				'after_title'   => '</' . $foo_heading . '>',
			)
		);

		// Footer 4.
		register_sidebar(
			array(
				'name'          => esc_html__( 'Footer 4', 'oceanwp' ),
				'id'            => 'footer-four',
				'description'   => esc_html__( 'Widgets in this area are used in the fourth footer region.', 'oceanwp' ),
				'before_widget' => '<div id="%1$s" class="footer-widget %2$s clr">',
				'after_widget'  => '</div>',
				'before_title'  => '<' . $foo_heading . ' class="widget-title">',
				'after_title'   => '</' . $foo_heading . '>',
			)
		);

	}

	/**
	 * Registers theme_mod strings into Polylang.
	 *
	 * @since 1.1.4
	 */
	public static function polylang_register_string() {

		if ( function_exists( 'pll_register_string' ) && $strings = oceanwp_register_tm_strings() ) {
			foreach ( $strings as $string => $default ) {
				pll_register_string( $string, get_theme_mod( $string, $default ), 'Theme Mod', true );
			}
		}

	}

	/**
	 * All theme functions hook into the oceanwp_head_css filter for this function.
	 *
	 * @param obj $output output value.
	 * @since 1.0.0
	 */
	public static function custom_css( $output = null ) {

		// Add filter for adding custom css via other functions.
		$output = apply_filters( 'ocean_head_css', $output );

		// If Custom File is selected.
		if ( 'file' === get_theme_mod( 'ocean_customzer_styling', 'head' ) ) {

			global $wp_customize;
			$upload_dir = wp_upload_dir();

			// Render CSS in the head.
			if ( isset( $wp_customize ) || ! file_exists( $upload_dir['basedir'] . '/oceanwp/custom-style.css' ) ) {

				// Minify and output CSS in the wp_head.
				if ( ! empty( $output ) ) {
					echo "<!-- OceanWP CSS -->\n<style type=\"text/css\">\n" . wp_strip_all_tags( oceanwp_minify_css( $output ) ) . "\n</style>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
		} else {

			// Minify and output CSS in the wp_head.
			if ( ! empty( $output ) ) {
				echo "<!-- OceanWP CSS -->\n<style type=\"text/css\">\n" . wp_strip_all_tags( oceanwp_minify_css( $output ) ) . "\n</style>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}

	}

	/**
	 * Minify the WP custom CSS because WordPress doesn't do it by default.
	 *
	 * @param obj $css minify css.
	 * @since 1.1.9
	 */
	public static function minify_custom_css( $css ) {

		return oceanwp_minify_css( $css );

	}

	/**
	 * Include Custom CSS file if present.
	 *
	 * @param obj $output output value.
	 * @since 1.4.12
	 */
	public static function custom_style_css( $output = null ) {

		// If Custom File is not selected.
		if ( 'file' !== get_theme_mod( 'ocean_customzer_styling', 'head' ) ) {
			return;
		}

		global $wp_customize;
		$upload_dir = wp_upload_dir();

		// Get all the customier css.
		$output = apply_filters( 'ocean_head_css', $output );

		// Get Custom Panel CSS.
		$output_custom_css = wp_get_custom_css();

		// Minified the Custom CSS.
		$output .= oceanwp_minify_css( $output_custom_css );

		// Render CSS from the custom file.
		if ( ! isset( $wp_customize ) && file_exists( $upload_dir['basedir'] . '/oceanwp/custom-style.css' ) && ! empty( $output ) ) {
			wp_enqueue_style( 'oceanwp-custom', trailingslashit( $upload_dir['baseurl'] ) . 'oceanwp/custom-style.css', false, false );
		}
	}

	/**
	 * Remove Customizer style script from front-end
	 *
	 * @since 1.4.12
	 */
	public static function remove_customizer_custom_css() {

		// If Custom File is not selected.
		if ( 'file' !== get_theme_mod( 'ocean_customzer_styling', 'head' ) ) {
			return;
		}

		global $wp_customize;

		// Disable Custom CSS in the frontend head.
		remove_action( 'wp_head', 'wp_custom_css_cb', 11 );
		remove_action( 'wp_head', 'wp_custom_css_cb', 101 );

		// If custom CSS file exists and NOT in customizer screen.
		if ( isset( $wp_customize ) ) {
			add_action( 'wp_footer', 'wp_custom_css_cb', 9999 );
		}
	}

	/**
	 * Adds inline CSS for the admin
	 *
	 * @since 1.0.0
	 */
	public static function admin_inline_css() {
		echo '<style>div#setting-error-tgmpa{display:block;}</style>';
	}

	/**
	 * Alter the search posts per page
	 *
	 * @param obj $query query.
	 * @since 1.3.7
	 */
	public static function search_posts_per_page( $query ) {
		$posts_per_page = get_theme_mod( 'ocean_search_post_per_page', '8' );
		$posts_per_page = $posts_per_page ? $posts_per_page : '8';

		if ( $query->is_main_query() && is_search() ) {
			$query->set( 'posts_per_page', $posts_per_page );
		}
	}

	/**
	 * Alter wp list categories arguments.
	 * Adds a span around the counter for easier styling.
	 *
	 * @param obj $links link.
	 * @since 1.0.0
	 */
	public static function wp_list_categories_args( $links ) {
		$links = str_replace( '</a> (', '</a> <span class="cat-count-span">(', $links );
		$links = str_replace( ')', ')</span>', $links );
		return $links;
	}

	/**
	 * Alters the default oembed output.
	 * Adds special classes for responsive oembeds via CSS.
	 *
	 * @param obj $cache     cache.
	 * @param url $url       url.
	 * @param obj $attr      attributes.
	 * @param obj $post_ID   post id.
	 * @since 1.0.0
	 */
	public static function add_responsive_wrap_to_oembeds( $cache, $url, $attr, $post_ID ) {

		// Supported video embeds.
		$hosts = apply_filters(
			'ocean_oembed_responsive_hosts',
			array(
				'vimeo.com',
				'youtube.com',
				'youtu.be',
				'blip.tv',
				'money.cnn.com',
				'dailymotion.com',
				'flickr.com',
				'hulu.com',
				'kickstarter.com',
				'vine.co',
				'soundcloud.com',
				'#http://((m|www)\.)?youtube\.com/watch.*#i',
				'#https://((m|www)\.)?youtube\.com/watch.*#i',
				'#http://((m|www)\.)?youtube\.com/playlist.*#i',
				'#https://((m|www)\.)?youtube\.com/playlist.*#i',
				'#http://youtu\.be/.*#i',
				'#https://youtu\.be/.*#i',
				'#https?://(.+\.)?vimeo\.com/.*#i',
				'#https?://(www\.)?dailymotion\.com/.*#i',
				'#https?://dai\.ly/*#i',
				'#https?://(www\.)?hulu\.com/watch/.*#i',
				'#https?://wordpress\.tv/.*#i',
				'#https?://(www\.)?funnyordie\.com/videos/.*#i',
				'#https?://vine\.co/v/.*#i',
				'#https?://(www\.)?collegehumor\.com/video/.*#i',
				'#https?://(www\.|embed\.)?ted\.com/talks/.*#i',
			)
		);

		// Supports responsive.
		$supports_responsive = false;

		// Check if responsive wrap should be added.
		foreach ( $hosts as $host ) {
			if ( strpos( $url, $host ) !== false ) {
				$supports_responsive = true;
				break; // no need to loop further.
			}
		}

		// Output code.
		if ( $supports_responsive ) {
			return '<p class="responsive-video-wrap clr">' . $cache . '</p>';
		} else {
			return '<div class="oceanwp-oembed-wrap clr">' . $cache . '</div>';
		}

	}

	/**
	 * Adds extra classes to the post_class() output
	 *
	 * @param obj $classes   Return classes.
	 * @since 1.0.0
	 */
	public static function post_class( $classes ) {

		// Get post.
		global $post;

		// Add entry class.
		$classes[] = 'entry';

		// Add has media class.
		if ( has_post_thumbnail()
			|| get_post_meta( $post->ID, 'ocean_post_self_hosted_media', true )
			|| get_post_meta( $post->ID, 'ocean_post_oembed', true )
			|| get_post_meta( $post->ID, 'ocean_post_video_embed', true ) ) {
			$classes[] = 'has-media';
		}

		// Return classes.
		return $classes;

	}

	/**
	 * Add schema markup to the authors post link
	 *
	 * @param obj $link   Author link.
	 * @since 1.0.0
	 */
	public static function the_author_posts_link( $link ) {

		// Add schema markup.
		$schema = oceanwp_get_schema_markup( 'author_link' );
		if ( $schema ) {
			$link = str_replace( 'rel="author"', 'rel="author" ' . $schema, $link );
		}

		// Return link.
		return $link;

	}

	/**
	 * Add support for Elementor Pro locations
	 *
	 * @param obj $elementor_theme_manager    Elementor Instance.
	 * @since 1.5.6
	 */
	public static function register_elementor_locations( $elementor_theme_manager ) {
		$elementor_theme_manager->register_all_core_location();
	}

	/**
	 * Add schema markup to the authors post link
	 *
	 * @since 1.1.5
	 */
	public static function remove_bb_lightbox() {
		return true;
	}

}

/**--------------------------------------------------------------------------------
#region Freemius - This logic will only be executed when Ocean Extra is active and has the Freemius SDK
---------------------------------------------------------------------------------*/

if ( ! function_exists( 'owp_fs' ) ) {
	if ( class_exists( 'Ocean_Extra' ) &&
			defined( 'OE_FILE_PATH' ) &&
			file_exists( dirname( OE_FILE_PATH ) . '/includes/freemius/start.php' )
	) {
		/**
		 * Create a helper function for easy SDK access.
		 */
		function owp_fs() {
			global $owp_fs;

			if ( ! isset( $owp_fs ) ) {
				// Include Freemius SDK.
				require_once dirname( OE_FILE_PATH ) . '/includes/freemius/start.php';

				$owp_fs = fs_dynamic_init(
					array(
						'id'                             => '3752',
						'bundle_id'                      => '3767',
						'slug'                           => 'oceanwp',
						'type'                           => 'theme',
						'public_key'                     => 'pk_043077b34f20f5e11334af3c12493',
						'bundle_public_key'              => 'pk_c334eb1ae413deac41e30bf00b9dc',
						'is_premium'                     => false,
						'has_addons'                     => true,
						'has_paid_plans'                 => true,
						'menu'                           => array(
							'slug'    => 'oceanwp',
							'account' => true,
							'contact' => false,
							'support' => false,
						),
						'anonymous_mode' => true,
						'bundle_license_auto_activation' => true,
						'navigation'                     => 'menu',
						'is_org_compliant'               => true,
					)
				);
			}

			return $owp_fs;
		}

		// Init Freemius.
		owp_fs();
		// Signal that SDK was initiated.
		do_action( 'owp_fs_loaded' );
	}
}

// endregion

new OCEANWP_Theme_Class();


// 1. Create Custom Post Type for Notices
function mousumi_register_notice_post_type() {
    $labels = array(
        'name'                  => 'Notices',
        'singular_name'         => 'Notice',
        'menu_name'             => 'Notices',
        'add_new'               => 'Add New Notice',
        'add_new_item'          => 'Add New Notice',
        'edit_item'             => 'Edit Notice',
        'new_item'              => 'New Notice',
        'view_item'             => 'View Notice',
        'search_items'          => 'Search Notices',
        'not_found'             => 'No notices found',
        'not_found_in_trash'    => 'No notices found in trash',
        'all_items'             => 'All Notices',
    );
    
    $args = array(
        'labels'                => $labels,
        'public'                => true,
        'publicly_queryable'    => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_icon'             => 'dashicons-megaphone',
        'menu_position'         => 25,
        'supports'              => array('title', 'editor', 'excerpt', 'thumbnail'),
        'has_archive'           => true,
        'rewrite'               => array('slug' => 'notices'),
        'show_in_rest'          => true,
    );
    
    register_post_type('notice', $args);
}
add_action('init', 'mousumi_register_notice_post_type');

// Flush rewrite rules after theme setup
function mousumi_notice_rewrite_flush() {
    if (get_option('mousumi_notice_flush_rewrite') !== 'done') {
        mousumi_register_notice_post_type();
        flush_rewrite_rules();
        update_option('mousumi_notice_flush_rewrite', 'done');
    }
}
add_action('after_switch_theme', 'mousumi_notice_rewrite_flush');

// 2. Add PDF Upload Meta Box
function mousumi_notice_pdf_meta_box() {
    add_meta_box(
        'mousumi_notice_pdf',
        'Notice PDF Attachment',
        'mousumi_notice_pdf_callback',
        'notice',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'mousumi_notice_pdf_meta_box');

function mousumi_notice_pdf_callback($post) {
    wp_nonce_field('mousumi_notice_pdf_nonce', 'mousumi_notice_pdf_nonce');
    $pdf_id = get_post_meta($post->ID, '_notice_pdf_file', true);
    $pdf_url = $pdf_id ? wp_get_attachment_url($pdf_id) : '';
    ?>
    <div class="mousumi-pdf-upload-wrapper">
        <input type="hidden" id="notice_pdf_id" name="notice_pdf_id" value="<?php echo esc_attr($pdf_id); ?>">
        <input type="text" id="notice_pdf_url" name="notice_pdf_url" value="<?php echo esc_url($pdf_url); ?>" readonly style="width: 100%; margin-bottom: 10px;">
        <button type="button" class="button mousumi-upload-pdf-btn">
            <?php echo $pdf_url ? 'Change PDF' : 'Upload PDF'; ?>
        </button>
        <?php if($pdf_url): ?>
        <button type="button" class="button mousumi-remove-pdf-btn" style="margin-top: 5px;">Remove PDF</button>
        <p style="margin-top: 10px;"><a href="<?php echo esc_url($pdf_url); ?>" target="_blank">Preview PDF</a></p>
        <?php endif; ?>
        <p class="description" style="margin-top: 10px;">Upload a PDF file to attach with this notice. It will be displayed inline on the notice page.</p>
    </div>
    
    <script>
    jQuery(document).ready(function($){
        var mediaUploader;
        
        $('.mousumi-upload-pdf-btn').click(function(e) {
            e.preventDefault();
            
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            
            mediaUploader = wp.media({
                title: 'Upload PDF',
                button: {
                    text: 'Select PDF'
                },
                library: {
                    type: 'application/pdf'
                },
                multiple: false
            });
            
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#notice_pdf_id').val(attachment.id);
                $('#notice_pdf_url').val(attachment.url);
                $('.mousumi-upload-pdf-btn').text('Change PDF');
                if($('.mousumi-remove-pdf-btn').length === 0) {
                    $('.mousumi-upload-pdf-btn').after('<button type="button" class="button mousumi-remove-pdf-btn" style="margin-top: 5px;">Remove PDF</button><p style="margin-top: 10px;"><a href="' + attachment.url + '" target="_blank">Preview PDF</a></p>');
                }
            });
            
            mediaUploader.open();
        });
        
        $(document).on('click', '.mousumi-remove-pdf-btn', function(e) {
            e.preventDefault();
            $('#notice_pdf_id').val('');
            $('#notice_pdf_url').val('');
            $('.mousumi-upload-pdf-btn').text('Upload PDF');
            $('.mousumi-remove-pdf-btn').remove();
            $(this).next('p').remove();
        });
    });
    </script>
    <?php
}

function mousumi_save_notice_pdf($post_id) {
    if (!isset($_POST['mousumi_notice_pdf_nonce']) || !wp_verify_nonce($_POST['mousumi_notice_pdf_nonce'], 'mousumi_notice_pdf_nonce')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    if (isset($_POST['notice_pdf_id'])) {
        update_post_meta($post_id, '_notice_pdf_file', sanitize_text_field($_POST['notice_pdf_id']));
    } else {
        delete_post_meta($post_id, '_notice_pdf_file');
    }
}
add_action('save_post_notice', 'mousumi_save_notice_pdf');

// 3. Add Notice Settings Page
function mousumi_notice_settings_menu() {
    add_submenu_page(
        'edit.php?post_type=notice',
        'Notice Display Settings',
        'Display Settings',
        'manage_options',
        'notice-display-settings',
        'mousumi_notice_settings_page'
    );
}
add_action('admin_menu', 'mousumi_notice_settings_menu');

function mousumi_notice_settings_page() {
    if(isset($_POST['mousumi_notice_display_submit'])) {
        check_admin_referer('mousumi_notice_settings');
        update_option('mousumi_notice_section_title', sanitize_text_field($_POST['notice_section_title']));
        update_option('mousumi_notice_button_text', sanitize_text_field($_POST['notice_button_text']));
        update_option('mousumi_notice_read_more_text', sanitize_text_field($_POST['notice_read_more_text']));
        update_option('mousumi_notice_view_pdf_text', sanitize_text_field($_POST['notice_view_pdf_text']));
        echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
    }
    
    $section_title = get_option('mousumi_notice_section_title', 'Latest Notice');
    $button_text = get_option('mousumi_notice_button_text', 'View All Notices');
    $read_more_text = get_option('mousumi_notice_read_more_text', 'Read More');
    $view_pdf_text = get_option('mousumi_notice_view_pdf_text', 'View PDF');
    ?>
    
    <div class="wrap">
        <h1>📢 Notice Display Settings</h1>
        <p>Configure notice section text and labels</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('mousumi_notice_settings'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="notice_section_title">Section Title</label>
                    </th>
                    <td>
                        <input type="text" name="notice_section_title" id="notice_section_title" value="<?php echo esc_attr($section_title); ?>" class="regular-text">
                        <p class="description">Title shown above notices (e.g., "Latest Notice", "সর্বশেষ নোটিশ")</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="notice_button_text">View All Button Text</label>
                    </th>
                    <td>
                        <input type="text" name="notice_button_text" id="notice_button_text" value="<?php echo esc_attr($button_text); ?>" class="regular-text">
                        <p class="description">Button text for viewing all notices (e.g., "View All Notices", "সকল নোটিশ দেখুন")</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="notice_read_more_text">Read More Text</label>
                    </th>
                    <td>
                        <input type="text" name="notice_read_more_text" id="notice_read_more_text" value="<?php echo esc_attr($read_more_text); ?>" class="regular-text">
                        <p class="description">Text for read more link (e.g., "Read More", "বিস্তারিত পড়ুন")</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="notice_view_pdf_text">View PDF Text</label>
                    </th>
                    <td>
                        <input type="text" name="notice_view_pdf_text" id="notice_view_pdf_text" value="<?php echo esc_attr($view_pdf_text); ?>" class="regular-text">
                        <p class="description">Button text for PDF viewer (e.g., "View PDF", "পিডিএফ দেখুন")</p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button('Save Settings', 'primary', 'mousumi_notice_display_submit'); ?>
        </form>
        
        <hr>
        
        <div style="background: #fff; padding: 20px; border-left: 4px solid #0073aa; margin-top: 30px;">
            <h2>📖 How to Use:</h2>
            <ol style="line-height: 2;">
                <li><strong>Create Notices:</strong> Go to "Notices" → "Add New Notice"</li>
                <li><strong>Add PDF (Optional):</strong> Upload PDF in the right sidebar "Notice PDF Attachment"</li>
                <li><strong>Display Notices:</strong> Use shortcode in your page/post</li>
                <li><strong>PDF Display:</strong> PDF will show inline on the notice detail page</li>
            </ol>
            
            <h3>Shortcode Usage:</h3>
            <p><strong>Display Latest Notice:</strong></p>
            <code style="background: #f0f0f0; padding: 10px; display: block; margin: 10px 0;">[mousumi_latest_notice]</code>
            
            <p><strong>Display Multiple Notices (e.g., 3 notices):</strong></p>
            <code style="background: #f0f0f0; padding: 10px; display: block; margin: 10px 0;">[mousumi_latest_notice count="3"]</code>
        </div>
    </div>
    
    <style>
        .wrap h1 { color: #0073aa; }
        .form-table th { width: 200px; }
    </style>
    <?php
}

// 4. Display Latest Notice Section (Shortcode Only)
function mousumi_latest_notice_section($atts) {
    $atts = shortcode_atts(array(
        'count' => 1,
    ), $atts);
    
    $notices_query = new WP_Query(array(
        'post_type'      => 'notice',
        'posts_per_page' => intval($atts['count']),
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC'
    ));
    
    if(!$notices_query->have_posts()) {
        wp_reset_postdata();
        return '';
    }
    
    $section_title = get_option('mousumi_notice_section_title', 'Latest Notice');
    $button_text = get_option('mousumi_notice_button_text', 'View All Notices');
    $read_more_text = get_option('mousumi_notice_read_more_text', 'Read More');
    $view_pdf_text = get_option('mousumi_notice_view_pdf_text', 'View PDF');
    $archive_url = get_post_type_archive_link('notice');
    
    ob_start();
    ?>
    
    <section class="mousumi-notice-section">
        <div class="mousumi-notice-container">
            <div class="mousumi-notice-header">
                <h2 class="mousumi-notice-title">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <?php echo esc_html($section_title); ?>
                </h2>
                <a href="<?php echo esc_url($archive_url); ?>" class="mousumi-view-all-link">
                    <?php echo esc_html($button_text); ?>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </a>
            </div>
            
            <div class="mousumi-notices-grid">
                <?php while($notices_query->have_posts()): $notices_query->the_post(); 
                    $pdf_id = get_post_meta(get_the_ID(), '_notice_pdf_file', true);
                    $has_pdf = !empty($pdf_id);
                ?>
                <article class="mousumi-notice-card">
                    <div class="mousumi-notice-card-header">
                        <span class="mousumi-notice-date">
                            <?php echo get_the_date('M j, Y'); ?>
                        </span>
                        <div class="mousumi-notice-badges">
                            <span class="mousumi-notice-new-badge">New</span>
                            <?php if($has_pdf): ?>
                            <span class="mousumi-notice-pdf-badge">
                                <svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor">
                                    <path d="M14 4.5V14a2 2 0 0 1-2 2h-1v-1h1a1 1 0 0 0 1-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5L14 4.5z"/>
                                    <path d="M1.6 11.85H0v3.999h.791v-1.342h.803c.287 0 .531-.057.732-.173.203-.117.358-.275.463-.474a1.42 1.42 0 0 0 .161-.677c0-.25-.053-.476-.158-.677a1.176 1.176 0 0 0-.46-.477c-.2-.12-.443-.179-.732-.179Zm.545 1.333a.795.795 0 0 1-.085.38.574.574 0 0 1-.238.241.794.794 0 0 1-.375.082H.788V12.48h.66c.218 0 .389.06.512.181.123.122.185.296.185.522Zm1.217-1.333v3.999h1.46c.401 0 .734-.08.998-.237a1.45 1.45 0 0 0 .595-.689c.13-.3.196-.662.196-1.084 0-.42-.065-.778-.196-1.075a1.426 1.426 0 0 0-.589-.68c-.264-.156-.599-.234-1.005-.234H3.362Zm.791.645h.563c.248 0 .45.05.609.152a.89.89 0 0 1 .354.454c.079.201.118.452.118.753a2.3 2.3 0 0 1-.068.592 1.14 1.14 0 0 1-.196.422.8.8 0 0 1-.334.252 1.298 1.298 0 0 1-.483.082h-.563v-2.707Zm3.743 1.763v1.591h-.79V11.85h2.548v.653H7.896v1.117h1.606v.638H7.896Z"/>
                                </svg>
                                PDF
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <h3 class="mousumi-notice-card-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h3>
                    
                    <div class="mousumi-notice-excerpt">
                        <?php 
                        if(has_excerpt()) {
                            echo wp_trim_words(get_the_excerpt(), 25);
                        } else {
                            echo wp_trim_words(get_the_content(), 25);
                        }
                        ?>
                    </div>
                    
                    <a href="<?php the_permalink(); ?>" class="mousumi-read-more">
                        <?php echo esc_html($read_more_text); ?>
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
                            <path d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                        </svg>
                    </a>
                </article>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    
    <style>
        .mousumi-notice-section {
            padding: 50px 20px;
            background: #ffffff;
            font-family: 'SolaimanLipi', 'Kalpurush', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .mousumi-notice-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .mousumi-notice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .mousumi-notice-title {
            font-size: 26px;
            font-weight: 600;
            margin: 0;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .mousumi-notice-title svg {
            color: #3b82f6;
        }
        
        .mousumi-view-all-link {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            padding: 8px 16px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }
        
        .mousumi-view-all-link:hover {
            background: #eff6ff;
            border-color: #3b82f6;
            gap: 10px;
        }
        
        .mousumi-notices-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .mousumi-notice-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 24px;
            transition: all 0.3s ease;
        }
        
        .mousumi-notice-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-color: #d1d5db;
            transform: translateY(-2px);
        }
        
        .mousumi-notice-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        
        .mousumi-notice-date {
            color: #6b7280;
            font-size: 13px;
            font-weight: 500;
        }
        
        .mousumi-notice-badges {
            display: flex;
            gap: 6px;
        }
        
        .mousumi-notice-new-badge {
            background: #dbeafe;
            color: #1e40af;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .mousumi-notice-pdf-badge {
            background: #fef3c7;
            color: #92400e;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .mousumi-notice-card-title {
            font-size: 19px;
            font-weight: 600;
            margin: 0 0 12px 0;
            line-height: 1.5;
        }
        
        .mousumi-notice-card-title a {
            color: #1f2937;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        .mousumi-notice-card-title a:hover {
            color: #3b82f6;
        }
        
        .mousumi-notice-excerpt {
            color: #6b7280;
            line-height: 1.7;
            margin-bottom: 16px;
            font-size: 15px;
        }
        
        .mousumi-read-more {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }
        
        .mousumi-read-more:hover {
            color: #2563eb;
            gap: 10px;
        }
        
        @media (max-width: 768px) {
            .mousumi-notice-section {
                padding: 30px 15px;
            }
            
            .mousumi-notice-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .mousumi-notice-title {
                font-size: 22px;
            }
            
            .mousumi-notices-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .mousumi-notice-card {
                padding: 20px;
            }
        }
    </style>
    
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('mousumi_latest_notice', 'mousumi_latest_notice_section');

// 5. Archive & Single Notice Page Styles with PDF Viewer
function mousumi_notice_page_styles() {
    if(is_post_type_archive('notice') || is_singular('notice')) {
        ?>
        <style>
            /* Archive Page - Fixed Grid Layout */
            body.post-type-archive-notice #primary {
                max-width: 1200px;
                margin: 0 auto;
                padding: 40px 20px;
            }
            
            body.post-type-archive-notice .site-main > .entries {
                display: grid !important;
                grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)) !important;
                gap: 25px !important;
            }
            
            body.post-type-archive-notice .entry {
                background: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 10px;
                padding: 24px;
                margin-bottom: 0 !important;
                transition: all 0.3s ease;
                font-family: 'SolaimanLipi', 'Kalpurush', Arial, sans-serif;
                width: 100% !important;
                float: none !important;
            }
            
            body.post-type-archive-notice .entry:hover {
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                border-color: #d1d5db;
                transform: translateY(-2px);
            }
            
            body.post-type-archive-notice .entry-header {
                padding: 0;
                margin-bottom: 16px;
                border: none;
                background: transparent !important;
            }
            
            body.post-type-archive-notice .entry-title {
                font-size: 19px !important;
                font-weight: 600;
                margin: 0 0 10px 0 !important;
                line-height: 1.5;
            }
            
            body.post-type-archive-notice .entry-title a {
                color: #1f2937;
                text-decoration: none;
                transition: color 0.2s;
            }
            
            body.post-type-archive-notice .entry-title a:hover {
                color: #3b82f6;
            }
            
            body.post-type-archive-notice .entry-meta {
                color: #6b7280;
                font-size: 13px;
                font-weight: 500;
                margin-bottom: 12px;
            }
            
            body.post-type-archive-notice .entry-content,
            body.post-type-archive-notice .entry-summary {
                padding: 0 !important;
                color: #6b7280;
                line-height: 1.7;
                font-size: 15px;
            }
            
            /* Single Notice Page with PDF Viewer */
            body.single-notice #primary {
                max-width: 1000px;
                margin: 0 auto;
                padding: 40px 20px;
            }
            
            body.single-notice .entry {
                background: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 10px;
                overflow: hidden;
                font-family: 'SolaimanLipi', 'Kalpurush', Arial, sans-serif;
            }
            
            body.single-notice .entry-header {
                background: #f9fafb;
                padding: 35px;
                border-bottom: 1px solid #e5e7eb;
            }
            
            body.single-notice .entry-title {
                font-size: 28px !important;
                font-weight: 600;
                margin-bottom: 12px !important;
                color: #1f2937;
                line-height: 1.4;
            }
            
            body.single-notice .entry-meta {
                color: #6b7280;
                font-size: 14px;
            }
            
            body.single-notice .entry-content {
                padding: 35px;
                line-height: 1.8;
                font-size: 16px;
                color: #374151;
            }
            
            body.single-notice .entry-content p {
                margin-bottom: 18px;
            }
            
            /* PDF Viewer Styles */
            .mousumi-pdf-viewer-container {
                margin: 30px 0;
                border: 1px solid #e5e7eb;
                border-radius: 10px;
                overflow: hidden;
                background: #f9fafb;
            }
            
            .mousumi-pdf-viewer-header {
                background: #ffffff;
                padding: 15px 20px;
                border-bottom: 1px solid #e5e7eb;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .mousumi-pdf-title {
                font-weight: 600;
                color: #1f2937;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .mousumi-pdf-actions {
                display: flex;
                gap: 10px;
            }
            
            .mousumi-pdf-btn {
                padding: 8px 16px;
                background: #3b82f6;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 500;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                transition: all 0.2s;
                border: none;
                cursor: pointer;
            }
            
            .mousumi-pdf-btn:hover {
                background: #2563eb;
                color: white;
            }
            
            .mousumi-pdf-viewer {
                width: 100%;
                height: 800px;
                border: none;
            }
            
            .back-to-notices {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: #ffffff;
                color: #3b82f6 !important;
                padding: 12px 24px;
                border-radius: 6px;
                border: 1px solid #e5e7eb;
                text-decoration: none;
                font-weight: 500;
                margin: 0 35px 35px 35px;
                transition: all 0.2s;
            }
            
            .back-to-notices:hover {
                background: #eff6ff;
                border-color: #3b82f6;
                gap: 12px;
            }
            
            @media (max-width: 768px) {
                body.post-type-archive-notice .site-main > .entries {
                    grid-template-columns: 1fr !important;
                }
                
                body.single-notice .entry-title {
                    font-size: 24px !important;
                }
                
                body.single-notice .entry-header,
                body.single-notice .entry-content {
                    padding: 25px;
                }
                
                .mousumi-pdf-viewer {
                    height: 600px;
                }
                
                .mousumi-pdf-viewer-header {
                    flex-direction: column;
                    gap: 10px;
                    align-items: flex-start;
                }
                
                .back-to-notices {
                    margin: 0 25px 25px 25px;
                }
            }
        </style>
        <?php
    }
}
add_action('wp_head', 'mousumi_notice_page_styles');

// 6. Display PDF on Single Notice Page
function mousumi_display_pdf_viewer($content) {
    if(is_singular('notice')) {
        $pdf_id = get_post_meta(get_the_ID(), '_notice_pdf_file', true);
        
        if(!empty($pdf_id)) {
            $pdf_url = wp_get_attachment_url($pdf_id);
            $pdf_filename = basename($pdf_url);
            $view_pdf_text = get_option('mousumi_notice_view_pdf_text', 'View PDF');
            
            $pdf_viewer = '
            <div class="mousumi-pdf-viewer-container">
                <div class="mousumi-pdf-viewer-header">
                    <div class="mousumi-pdf-title">
                        <svg width="20" height="20" viewBox="0 0 16 16" fill="currentColor">
                            <path d="M14 4.5V14a2 2 0 0 1-2 2h-1v-1h1a1 1 0 0 0 1-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5L14 4.5z"/>
                            <path d="M1.6 11.85H0v3.999h.791v-1.342h.803c.287 0 .531-.057.732-.173.203-.117.358-.275.463-.474a1.42 1.42 0 0 0 .161-.677c0-.25-.053-.476-.158-.677a1.176 1.176 0 0 0-.46-.477c-.2-.12-.443-.179-.732-.179Zm.545 1.333a.795.795 0 0 1-.085.38.574.574 0 0 1-.238.241.794.794 0 0 1-.375.082H.788V12.48h.66c.218 0 .389.06.512.181.123.122.185.296.185.522Zm1.217-1.333v3.999h1.46c.401 0 .734-.08.998-.237a1.45 1.45 0 0 0 .595-.689c.13-.3.196-.662.196-1.084 0-.42-.065-.778-.196-1.075a1.426 1.426 0 0 0-.589-.68c-.264-.156-.599-.234-1.005-.234H3.362Zm.791.645h.563c.248 0 .45.05.609.152a.89.89 0 0 1 .354.454c.079.201.118.452.118.753a2.3 2.3 0 0 1-.068.592 1.14 1.14 0 0 1-.196.422.8.8 0 0 1-.334.252 1.298 1.298 0 0 1-.483.082h-.563v-2.707Zm3.743 1.763v1.591h-.79V11.85h2.548v.653H7.896v1.117h1.606v.638H7.896Z"/>
                        </svg>
                        ' . esc_html($pdf_filename) . '
                    </div>
                    <div class="mousumi-pdf-actions">
                        <a href="' . esc_url($pdf_url) . '" class="mousumi-pdf-btn" download>
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                            </svg>
                            Download
                        </a>
                        <a href="' . esc_url($pdf_url) . '" class="mousumi-pdf-btn" target="_blank">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                <path d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/>
                                <path d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/>
                            </svg>
                            Open in New Tab
                        </a>
                    </div>
                </div>
                <iframe src="' . esc_url($pdf_url) . '#toolbar=1&navpanes=1&scrollbar=1" class="mousumi-pdf-viewer" title="PDF Viewer"></iframe>
            </div>';
            
            $content .= $pdf_viewer;
        }
        
        // Add back button
        $archive_url = get_post_type_archive_link('notice');
        $content .= '<a href="' . esc_url($archive_url) . '" class="back-to-notices">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                <path d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
            </svg>
            Back to All Notices
        </a>';
    }
    
    return $content;
}
add_filter('the_content', 'mousumi_display_pdf_viewer');


/**
 * Multi-Item Carousel Gallery System
 * Shows 4-5 images at once that slide together
 */

// Previous code এর সাথে এই function গুলো replace করুন

// 3. Gallery Shortcode (UPDATED)
function mousumi_photo_gallery_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => '',
        'mode' => 'carousel', // carousel or grid
        'items' => 4, // How many items to show at once in carousel
        'columns' => 3, // For grid mode
        'autoplay' => 'true',
        'speed' => 3000,
    ), $atts);
    
    if(empty($atts['id'])) {
        return '<p style="color: red;">Please provide a gallery ID. Example: [photo_gallery id="123"]</p>';
    }
    
    $gallery_ids = get_post_meta($atts['id'], '_gallery_image_ids', true);
    
    if(empty($gallery_ids) || !is_array($gallery_ids)) {
        return '<p>No images found in this gallery.</p>';
    }
    
    $unique_id = 'gallery-' . uniqid();
    $items_to_show = intval($atts['items']);
    
    ob_start();
    
    if($atts['mode'] === 'carousel') {
        ?>
        <div class="mousumi-carousel-wrapper" id="<?php echo esc_attr($unique_id); ?>" data-items="<?php echo esc_attr($items_to_show); ?>">
            <div class="mousumi-carousel-track-container">
                <div class="mousumi-carousel-track">
                    <?php foreach($gallery_ids as $img_id): 
                        $img_full = wp_get_attachment_image_url($img_id, 'full');
                        $img_medium = wp_get_attachment_image_url($img_id, 'medium_large');
                        $img_alt = get_post_meta($img_id, '_wp_attachment_image_alt', true);
                    ?>
                    <div class="mousumi-carousel-item">
                        <div class="mousumi-carousel-image mousumi-photo-clickable" data-full="<?php echo esc_url($img_full); ?>">
                            <img src="<?php echo esc_url($img_medium); ?>" alt="<?php echo esc_attr($img_alt); ?>">
                            <div class="mousumi-carousel-overlay">
                                <div class="mousumi-zoom-icon">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <path d="m21 21-4.35-4.35"></path>
                                        <line x1="11" y1="8" x2="11" y2="14"></line>
                                        <line x1="8" y1="11" x2="14" y2="11"></line>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <button class="mousumi-carousel-nav mousumi-carousel-prev" aria-label="Previous">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            <button class="mousumi-carousel-nav mousumi-carousel-next" aria-label="Next">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
            
            <div class="mousumi-carousel-indicators"></div>
        </div>
        
        <script>
        (function() {
            var wrapper = document.getElementById('<?php echo esc_js($unique_id); ?>');
            var track = wrapper.querySelector('.mousumi-carousel-track');
            var items = track.querySelectorAll('.mousumi-carousel-item');
            var prevBtn = wrapper.querySelector('.mousumi-carousel-prev');
            var nextBtn = wrapper.querySelector('.mousumi-carousel-next');
            var indicators = wrapper.querySelector('.mousumi-carousel-indicators');
            
            var itemsToShow = parseInt(wrapper.dataset.items);
            var currentPosition = 0;
            var totalItems = items.length;
            var maxPosition = Math.max(0, totalItems - itemsToShow);
            var itemWidth = 100 / itemsToShow;
            var autoplayInterval;
            
            // Set item widths
            items.forEach(function(item) {
                item.style.minWidth = itemWidth + '%';
                item.style.maxWidth = itemWidth + '%';
            });
            
            // Create indicators
            var totalPages = Math.ceil(totalItems / itemsToShow);
            for(var i = 0; i < totalPages; i++) {
                var dot = document.createElement('span');
                dot.classList.add('mousumi-carousel-dot');
                if(i === 0) dot.classList.add('active');
                dot.dataset.page = i;
                dot.addEventListener('click', function() {
                    goToPage(parseInt(this.dataset.page));
                });
                indicators.appendChild(dot);
            }
            
            var dots = indicators.querySelectorAll('.mousumi-carousel-dot');
            
            function updateCarousel() {
                var offset = -(currentPosition * itemWidth);
                track.style.transform = 'translateX(' + offset + '%)';
                
                // Update dots
                var currentPage = Math.floor(currentPosition / itemsToShow);
                dots.forEach(function(dot, index) {
                    dot.classList.toggle('active', index === currentPage);
                });
                
                // Update button states
                prevBtn.style.opacity = currentPosition === 0 ? '0.5' : '1';
                prevBtn.style.cursor = currentPosition === 0 ? 'not-allowed' : 'pointer';
                nextBtn.style.opacity = currentPosition >= maxPosition ? '0.5' : '1';
                nextBtn.style.cursor = currentPosition >= maxPosition ? 'not-allowed' : 'pointer';
            }
            
            function goToPage(pageNum) {
                currentPosition = Math.min(pageNum * itemsToShow, maxPosition);
                updateCarousel();
            }
            
            function slideNext() {
                if(currentPosition >= maxPosition) {
                    currentPosition = 0; // Loop back to start
                } else {
                    currentPosition = Math.min(currentPosition + itemsToShow, maxPosition);
                }
                updateCarousel();
            }
            
            function slidePrev() {
                if(currentPosition === 0) {
                    currentPosition = maxPosition; // Loop to end
                } else {
                    currentPosition = Math.max(currentPosition - itemsToShow, 0);
                }
                updateCarousel();
            }
            
            prevBtn.addEventListener('click', slidePrev);
            nextBtn.addEventListener('click', slideNext);
            
            <?php if($atts['autoplay'] === 'true'): ?>
            function startAutoplay() {
                autoplayInterval = setInterval(slideNext, <?php echo intval($atts['speed']); ?>);
            }
            
            function stopAutoplay() {
                clearInterval(autoplayInterval);
            }
            
            startAutoplay();
            wrapper.addEventListener('mouseenter', stopAutoplay);
            wrapper.addEventListener('mouseleave', startAutoplay);
            <?php endif; ?>
            
            // Responsive: adjust items on resize
            function adjustItemsForScreen() {
                var width = window.innerWidth;
                if(width < 576) {
                    itemsToShow = 1;
                } else if(width < 768) {
                    itemsToShow = 2;
                } else if(width < 992) {
                    itemsToShow = 3;
                } else {
                    itemsToShow = parseInt(wrapper.dataset.items);
                }
                
                itemWidth = 100 / itemsToShow;
                maxPosition = Math.max(0, totalItems - itemsToShow);
                
                items.forEach(function(item) {
                    item.style.minWidth = itemWidth + '%';
                    item.style.maxWidth = itemWidth + '%';
                });
                
                currentPosition = Math.min(currentPosition, maxPosition);
                updateCarousel();
            }
            
            window.addEventListener('resize', adjustItemsForScreen);
            adjustItemsForScreen();
        })();
        </script>
        <?php
    } else {
        // Grid Mode (same as before)
        ?>
        <div class="mousumi-photo-grid" data-columns="<?php echo esc_attr($atts['columns']); ?>">
            <?php foreach($gallery_ids as $img_id): 
                $img_full = wp_get_attachment_image_url($img_id, 'full');
                $img_medium = wp_get_attachment_image_url($img_id, 'medium_large');
                $img_alt = get_post_meta($img_id, '_wp_attachment_image_alt', true);
            ?>
            <div class="mousumi-photo-grid-item mousumi-photo-clickable" data-full="<?php echo esc_url($img_full); ?>">
                <img src="<?php echo esc_url($img_medium); ?>" alt="<?php echo esc_attr($img_alt); ?>">
                <div class="mousumi-photo-zoom-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                        <line x1="11" y1="8" x2="11" y2="14"></line>
                        <line x1="8" y1="11" x2="14" y2="11"></line>
                    </svg>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
    
    // Lightbox (same as before)
    static $lightbox_added = false;
    if(!$lightbox_added) {
        ?>
        <div id="mousumi-photo-lightbox" class="mousumi-photo-lightbox">
            <button class="mousumi-lightbox-close" aria-label="Close">×</button>
            <button class="mousumi-lightbox-nav mousumi-lightbox-prev" aria-label="Previous">‹</button>
            <button class="mousumi-lightbox-nav mousumi-lightbox-next" aria-label="Next">›</button>
            <div class="mousumi-lightbox-content">
                <img src="" alt="" id="mousumi-lightbox-img">
            </div>
            <div class="mousumi-lightbox-counter">
                <span id="mousumi-lightbox-current">1</span> / <span id="mousumi-lightbox-total">1</span>
            </div>
        </div>
        
        <script>
        (function() {
            var lightbox = document.getElementById('mousumi-photo-lightbox');
            var lightboxImg = document.getElementById('mousumi-lightbox-img');
            var closeBtn = lightbox.querySelector('.mousumi-lightbox-close');
            var prevBtn = lightbox.querySelector('.mousumi-lightbox-prev');
            var nextBtn = lightbox.querySelector('.mousumi-lightbox-next');
            var currentCounter = document.getElementById('mousumi-lightbox-current');
            var totalCounter = document.getElementById('mousumi-lightbox-total');
            
            var images = [];
            var currentIndex = 0;
            
            document.addEventListener('click', function(e) {
                var clickedElement = e.target.closest('.mousumi-photo-clickable');
                if(!clickedElement) return;
                
                e.preventDefault();
                var container = clickedElement.closest('.mousumi-carousel-wrapper, .mousumi-photo-grid');
                
                images = [];
                var allClickable = container.querySelectorAll('.mousumi-photo-clickable');
                allClickable.forEach(function(item, index) {
                    var imgElement = item.querySelector('img');
                    images.push(item.dataset.full || imgElement.src);
                    if(item === clickedElement) currentIndex = index;
                });
                
                openLightbox();
            });
            
            function openLightbox() {
                lightbox.classList.add('active');
                document.body.style.overflow = 'hidden';
                showImage(currentIndex);
            }
            
            function closeLightbox() {
                lightbox.classList.remove('active');
                document.body.style.overflow = '';
            }
            
            function showImage(index) {
                currentIndex = index;
                if(currentIndex >= images.length) currentIndex = 0;
                if(currentIndex < 0) currentIndex = images.length - 1;
                
                lightboxImg.src = images[currentIndex];
                currentCounter.textContent = currentIndex + 1;
                totalCounter.textContent = images.length;
            }
            
            closeBtn.addEventListener('click', closeLightbox);
            prevBtn.addEventListener('click', function() { showImage(currentIndex - 1); });
            nextBtn.addEventListener('click', function() { showImage(currentIndex + 1); });
            
            lightbox.addEventListener('click', function(e) {
                if(e.target === lightbox) closeLightbox();
            });
            
            document.addEventListener('keydown', function(e) {
                if(!lightbox.classList.contains('active')) return;
                if(e.key === 'Escape') closeLightbox();
                if(e.key === 'ArrowLeft') showImage(currentIndex - 1);
                if(e.key === 'ArrowRight') showImage(currentIndex + 1);
            });
        })();
        </script>
        <?php
        $lightbox_added = true;
    }
    
    return ob_get_clean();
}
add_shortcode('photo_gallery', 'mousumi_photo_gallery_shortcode');

// 4. Gallery Styles (UPDATED)
function mousumi_photo_gallery_styles() {
    ?>
    <style>
        /* Multi-Item Carousel Styles */
        .mousumi-carousel-wrapper {
            position: relative;
            width: 100%;
            margin: 40px 0;
            padding: 0 60px;
            overflow: hidden;
        }
        
        .mousumi-carousel-track-container {
            overflow: hidden;
            border-radius: 12px;
        }
        
        .mousumi-carousel-track {
            display: flex;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            gap: 0;
        }
        
        .mousumi-carousel-item {
            flex-shrink: 0;
            padding: 0 10px;
        }
        
        .mousumi-carousel-image {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            background: #f5f5f5;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .mousumi-carousel-image:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }
        
        .mousumi-carousel-image img {
            width: 100%;
            height: 280px;
            object-fit: cover;
            display: block;
            transition: transform 0.5s;
        }
        
        .mousumi-carousel-image:hover img {
            transform: scale(1.1);
        }
        
        .mousumi-carousel-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.3s;
        }
        
        .mousumi-carousel-image:hover .mousumi-carousel-overlay {
            background: rgba(0, 0, 0, 0.5);
            opacity: 1;
        }
        
        .mousumi-zoom-icon {
            background: rgba(255, 255, 255, 0.95);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: scale(0);
            transition: transform 0.3s;
        }
        
        .mousumi-carousel-image:hover .mousumi-zoom-icon {
            transform: scale(1);
        }
        
        .mousumi-carousel-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.95);
            border: none;
            width: 55px;
            height: 55px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            z-index: 10;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .mousumi-carousel-nav:hover {
            background: #fff;
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 6px 16px rgba(0,0,0,0.2);
        }
        
        .mousumi-carousel-prev {
            left: 0;
        }
        
        .mousumi-carousel-next {
            right: 0;
        }
        
        .mousumi-carousel-indicators {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 30px;
        }
        
        .mousumi-carousel-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #d1d5db;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .mousumi-carousel-dot.active {
            background: #3b82f6;
            transform: scale(1.3);
            border-color: #93c5fd;
        }
        
        .mousumi-carousel-dot:hover {
            background: #9ca3af;
            transform: scale(1.2);
        }
        
        /* Grid Styles (same as before) */
        .mousumi-photo-grid {
            display: grid;
            gap: 20px;
            margin: 30px 0;
        }
        
        .mousumi-photo-grid[data-columns="2"] { grid-template-columns: repeat(2, 1fr); }
        .mousumi-photo-grid[data-columns="3"] { grid-template-columns: repeat(3, 1fr); }
        .mousumi-photo-grid[data-columns="4"] { grid-template-columns: repeat(4, 1fr); }
        
        .mousumi-photo-grid-item {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            cursor: pointer;
            background: #f5f5f5;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .mousumi-photo-grid-item::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0);
            transition: all 0.3s;
        }
        
        .mousumi-photo-grid-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .mousumi-photo-grid-item:hover::after {
            background: rgba(0, 0, 0, 0.4);
        }
        
        .mousumi-photo-grid-item img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            display: block;
            transition: transform 0.5s;
        }
        
        .mousumi-photo-grid-item:hover img {
            transform: scale(1.1);
        }
        
        .mousumi-photo-zoom-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            background: rgba(255,255,255,0.95);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            z-index: 2;
            pointer-events: none;
        }
        
        .mousumi-photo-grid-item:hover .mousumi-photo-zoom-icon {
            transform: translate(-50%, -50%) scale(1);
        }
        
        /* Lightbox */
        .mousumi-photo-lightbox {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.97);
            z-index: 999999;
            display: none;
            align-items: center;
            justify-content: center;
        }
        
        .mousumi-photo-lightbox.active {
            display: flex;
        }
        
        .mousumi-lightbox-content {
            max-width: 95%;
            max-height: 95%;
        }
        
        .mousumi-lightbox-content img {
            max-width: 100%;
            max-height: 90vh;
            width: auto;
            height: auto;
            display: block;
            border-radius: 8px;
        }
        
        .mousumi-lightbox-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.95);
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 32px;
            cursor: pointer;
            line-height: 1;
            transition: all 0.3s;
            z-index: 10;
        }
        
        .mousumi-lightbox-close:hover {
            background: #fff;
            transform: rotate(90deg);
        }
        
        .mousumi-lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.95);
            border: none;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            font-size: 42px;
            cursor: pointer;
            line-height: 1;
            transition: all 0.3s;
            z-index: 10;
        }
        
        .mousumi-lightbox-nav:hover {
            background: #fff;
            transform: translateY(-50%) scale(1.15);
        }
        
        .mousumi-lightbox-prev { left: 20px; }
        .mousumi-lightbox-next { right: 20px; }
        
        .mousumi-lightbox-counter {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.95);
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .mousumi-carousel-wrapper {
                padding: 0 50px;
            }
            
            .mousumi-carousel-image img {
                height: 240px;
            }
        }
        
        @media (max-width: 768px) {
            .mousumi-carousel-wrapper {
                padding: 0 40px;
            }
            
            .mousumi-carousel-nav {
                width: 45px;
                height: 45px;
            }
            
            .mousumi-carousel-image img {
                height: 200px;
            }
            
            .mousumi-photo-grid[data-columns="3"],
            .mousumi-photo-grid[data-columns="4"] {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .mousumi-photo-grid-item img {
                height: 220px;
            }
        }
        
        @media (max-width: 576px) {
            .mousumi-carousel-wrapper {
                padding: 0 35px;
            }
            
            .mousumi-carousel-image img {
                height: 180px;
            }
        }
    </style>
    <?php
}
add_action('wp_head', 'mousumi_photo_gallery_styles');


// 1. Create Playlist Gallery Custom Post Type
function mousumi_youtube_playlist_post_type() {
    $labels = array(
        'name'                  => 'YouTube Playlists',
        'singular_name'         => 'YouTube Playlist',
        'menu_name'             => 'YouTube Playlists',
        'add_new'               => 'Add New Playlist',
        'add_new_item'          => 'Add New Playlist',
        'edit_item'             => 'Edit Playlist',
        'view_item'             => 'View Playlist',
        'all_items'             => 'All Playlists',
    );
    
    $args = array(
        'labels'                => $labels,
        'public'                => false,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_icon'             => 'dashicons-video-alt3',
        'menu_position'         => 27,
        'supports'              => array('title'),
    );
    
    register_post_type('youtube_playlist', $args);
}
add_action('init', 'mousumi_youtube_playlist_post_type');

// 2. Add Playlist Meta Box
function mousumi_youtube_playlist_meta_box() {
    add_meta_box(
        'mousumi_youtube_playlist_data',
        'Playlist Settings',
        'mousumi_youtube_playlist_callback',
        'youtube_playlist',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'mousumi_youtube_playlist_meta_box');

function mousumi_youtube_playlist_callback($post) {
    wp_nonce_field('mousumi_youtube_playlist_nonce', 'mousumi_youtube_playlist_nonce');
    
    $playlist_id = get_post_meta($post->ID, '_playlist_id', true);
    $playlist_url = get_post_meta($post->ID, '_playlist_url', true);
    $display_mode = get_post_meta($post->ID, '_display_mode', true);
    $display_mode = $display_mode ? $display_mode : 'grid';
    ?>
    
    <div class="mousumi-playlist-settings">
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="playlist_url">YouTube Playlist URL</label>
                </th>
                <td>
                    <input type="url" name="playlist_url" id="playlist_url" value="<?php echo esc_url($playlist_url); ?>" class="large-text" placeholder="https://www.youtube.com/playlist?list=PLxxxxxx">
                    <p class="description">
                        <strong>How to get Playlist URL:</strong><br>
                        1. Go to your YouTube playlist<br>
                        2. Copy the full URL from browser address bar<br>
                        3. Paste it here
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="playlist_id">OR Playlist ID</label>
                </th>
                <td>
                    <input type="text" name="playlist_id" id="playlist_id" value="<?php echo esc_attr($playlist_id); ?>" class="regular-text" placeholder="PLxxxxxx">
                    <p class="description">
                        Playlist ID from URL (the part after "list=")
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="display_mode">Default Display Mode</label>
                </th>
                <td>
                    <select name="display_mode" id="display_mode">
                        <option value="grid" <?php selected($display_mode, 'grid'); ?>>Grid View</option>
                        <option value="list" <?php selected($display_mode, 'list'); ?>>List View</option>
                        <option value="carousel" <?php selected($display_mode, 'carousel'); ?>>Carousel View</option>
                    </select>
                    <p class="description">Default layout (can be overridden in shortcode)</p>
                </td>
            </tr>
        </table>
        
        <?php if(!empty($playlist_id) || !empty($playlist_url)): 
            $final_id = !empty($playlist_id) ? $playlist_id : '';
            if(empty($final_id) && !empty($playlist_url)) {
                parse_str(parse_url($playlist_url, PHP_URL_QUERY), $params);
                $final_id = isset($params['list']) ? $params['list'] : '';
            }
        ?>
        
        <div style="margin-top: 30px; padding: 20px; background: #f0f6fc; border-left: 4px solid #0073aa; border-radius: 4px;">
            <h3 style="margin: 0 0 15px 0;">📺 Shortcode Usage:</h3>
            
            <p><strong>Basic Usage:</strong></p>
            <code style="background: #fff; padding: 10px; display: block; margin: 10px 0;">[youtube_playlist id="<?php echo $post->ID; ?>"]</code>
            
            <p><strong>Grid View (3 columns):</strong></p>
            <code style="background: #fff; padding: 10px; display: block; margin: 10px 0;">[youtube_playlist id="<?php echo $post->ID; ?>" mode="grid" columns="3"]</code>
            
            <p><strong>Carousel View (4 items):</strong></p>
            <code style="background: #fff; padding: 10px; display: block; margin: 10px 0;">[youtube_playlist id="<?php echo $post->ID; ?>" mode="carousel" items="4"]</code>
            
            <p><strong>List View:</strong></p>
            <code style="background: #fff; padding: 10px; display: block; margin: 10px 0;">[youtube_playlist id="<?php echo $post->ID; ?>" mode="list"]</code>
            
            <?php if(!empty($final_id)): ?>
            <p style="margin-top: 20px;"><strong>Preview:</strong></p>
            <div style="background: #fff; padding: 15px; border-radius: 8px;">
                <iframe width="100%" height="315" src="https://www.youtube.com/embed/videoseries?list=<?php echo esc_attr($final_id); ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
            <?php endif; ?>
        </div>
        
        <?php endif; ?>
    </div>
    
    <script>
    jQuery(document).ready(function($){
        // Auto-extract playlist ID from URL
        $('#playlist_url').on('blur', function() {
            var url = $(this).val();
            if(url) {
                var urlParams = new URLSearchParams(url.split('?')[1]);
                var listId = urlParams.get('list');
                if(listId) {
                    $('#playlist_id').val(listId);
                }
            }
        });
    });
    </script>
    
    <style>
        .mousumi-playlist-settings .form-table th {
            width: 200px;
            padding: 20px 10px 20px 0;
        }
        .mousumi-playlist-settings .form-table td {
            padding: 20px 10px;
        }
        code {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
    <?php
}

function mousumi_save_youtube_playlist($post_id) {
    if (!isset($_POST['mousumi_youtube_playlist_nonce']) || !wp_verify_nonce($_POST['mousumi_youtube_playlist_nonce'], 'mousumi_youtube_playlist_nonce')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Extract playlist ID from URL if provided
    $playlist_url = isset($_POST['playlist_url']) ? esc_url_raw($_POST['playlist_url']) : '';
    $playlist_id = isset($_POST['playlist_id']) ? sanitize_text_field($_POST['playlist_id']) : '';
    
    if(!empty($playlist_url) && empty($playlist_id)) {
        parse_str(parse_url($playlist_url, PHP_URL_QUERY), $params);
        $playlist_id = isset($params['list']) ? sanitize_text_field($params['list']) : '';
    }
    
    update_post_meta($post_id, '_playlist_url', $playlist_url);
    update_post_meta($post_id, '_playlist_id', $playlist_id);
    update_post_meta($post_id, '_display_mode', sanitize_text_field($_POST['display_mode']));
}
add_action('save_post_youtube_playlist', 'mousumi_save_youtube_playlist');

// 3. Fetch Playlist Videos using oEmbed
function mousumi_get_playlist_videos($playlist_id, $limit = 20) {
    // Use YouTube's RSS feed to get video list
    $feed_url = "https://www.youtube.com/feeds/videos.xml?playlist_id=" . $playlist_id;
    
    $response = wp_remote_get($feed_url, array('timeout' => 15));
    
    if(is_wp_error($response)) {
        return array();
    }
    
    $body = wp_remote_retrieve_body($response);
    
    if(empty($body)) {
        return array();
    }
    
    // Parse XML
    $xml = simplexml_load_string($body);
    
    if(!$xml) {
        return array();
    }
    
    $videos = array();
    $count = 0;
    
    foreach($xml->entry as $entry) {
        if($count >= $limit) break;
        
        $media = $entry->children('http://search.yahoo.com/mrss/');
        $yt = $entry->children('http://www.youtube.com/xml/schemas/2015');
        
        $video_id = (string)$yt->videoId;
        
        $videos[] = array(
            'id' => $video_id,
            'title' => (string)$entry->title,
            'description' => (string)$media->group->description,
            'thumbnail' => "https://i.ytimg.com/vi/{$video_id}/hqdefault.jpg",
            'published' => (string)$entry->published,
        );
        
        $count++;
    }
    
    return $videos;
}

// 4. YouTube Playlist Shortcode
function mousumi_youtube_playlist_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => '',
        'mode' => '',
        'columns' => 3,
        'items' => 4,
        'limit' => 20,
        'autoplay' => 'true',
        'speed' => 3000,
    ), $atts);
    
    if(empty($atts['id'])) {
        return '<p style="color: red;">Please provide a playlist ID. Example: [youtube_playlist id="123"]</p>';
    }
    
    $playlist_id = get_post_meta($atts['id'], '_playlist_id', true);
    $default_mode = get_post_meta($atts['id'], '_display_mode', true);
    $display_mode = !empty($atts['mode']) ? $atts['mode'] : ($default_mode ? $default_mode : 'grid');
    
    if(empty($playlist_id)) {
        return '<p style="color: red;">Invalid playlist. Please configure the playlist settings.</p>';
    }
    
    $videos = mousumi_get_playlist_videos($playlist_id, intval($atts['limit']));
    
    if(empty($videos)) {
        return '<div class="youtube-playlist-error" style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">
            <strong>Note:</strong> Unable to fetch playlist videos. Showing embedded playlist instead.
            <div style="margin-top: 15px;">
                <iframe width="100%" height="400" src="https://www.youtube.com/embed/videoseries?list=' . esc_attr($playlist_id) . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        </div>';
    }
    
    $unique_id = 'yt-playlist-' . uniqid();
    
    ob_start();
    
    if($display_mode === 'carousel') {
        $items_to_show = intval($atts['items']);
        ?>
        <div class="mousumi-yt-playlist-carousel" id="<?php echo esc_attr($unique_id); ?>" data-items="<?php echo esc_attr($items_to_show); ?>">
            <div class="mousumi-yt-pl-track-container">
                <div class="mousumi-yt-pl-track">
                    <?php foreach($videos as $video): ?>
                    <div class="mousumi-yt-pl-item">
                        <div class="mousumi-yt-pl-video" data-video-id="<?php echo esc_attr($video['id']); ?>">
                            <div class="mousumi-yt-pl-thumbnail">
                                <img src="<?php echo esc_url($video['thumbnail']); ?>" alt="<?php echo esc_attr($video['title']); ?>">
                                <div class="mousumi-yt-pl-play-btn">
                                    <svg width="68" height="48" viewBox="0 0 68 48" fill="white">
                                        <path d="M66.52,7.74c-0.78-2.93-2.49-5.41-5.42-6.19C55.79,.13,34,0,34,0S12.21,.13,6.9,1.55 C3.97,2.33,2.27,4.81,1.48,7.74C0.06,13.05,0,24,0,24s0.06,10.95,1.48,16.26c0.78,2.93,2.49,5.41,5.42,6.19 C12.21,47.87,34,48,34,48s21.79-0.13,27.1-1.55c2.93-0.78,4.64-3.26,5.42-6.19C67.94,34.95,68,24,68,24S67.94,13.05,66.52,7.74z"></path>
                                        <path d="M 45,24 27,14 27,34" fill="#000"></path>
                                    </svg>
                                </div>
                                <div class="mousumi-yt-pl-duration"><?php echo human_time_diff(strtotime($video['published']), current_time('timestamp')) . ' ago'; ?></div>
                            </div>
                            <div class="mousumi-yt-pl-info">
                                <h3><?php echo esc_html($video['title']); ?></h3>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <button class="mousumi-yt-pl-nav mousumi-yt-pl-prev" aria-label="Previous">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            <button class="mousumi-yt-pl-nav mousumi-yt-pl-next" aria-label="Next">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
            
            <div class="mousumi-yt-pl-indicators"></div>
        </div>
        
        <script>
        (function() {
            var wrapper = document.getElementById('<?php echo esc_js($unique_id); ?>');
            var track = wrapper.querySelector('.mousumi-yt-pl-track');
            var items = track.querySelectorAll('.mousumi-yt-pl-item');
            var prevBtn = wrapper.querySelector('.mousumi-yt-pl-prev');
            var nextBtn = wrapper.querySelector('.mousumi-yt-pl-next');
            var indicators = wrapper.querySelector('.mousumi-yt-pl-indicators');
            
            var itemsToShow = parseInt(wrapper.dataset.items);
            var currentPosition = 0;
            var totalItems = items.length;
            var maxPosition = Math.max(0, totalItems - itemsToShow);
            var itemWidth = 100 / itemsToShow;
            var autoplayInterval;
            
            items.forEach(function(item) {
                item.style.minWidth = itemWidth + '%';
                item.style.maxWidth = itemWidth + '%';
            });
            
            var totalPages = Math.ceil(totalItems / itemsToShow);
            for(var i = 0; i < totalPages; i++) {
                var dot = document.createElement('span');
                dot.classList.add('mousumi-yt-pl-dot');
                if(i === 0) dot.classList.add('active');
                dot.dataset.page = i;
                dot.addEventListener('click', function() {
                    goToPage(parseInt(this.dataset.page));
                });
                indicators.appendChild(dot);
            }
            
            var dots = indicators.querySelectorAll('.mousumi-yt-pl-dot');
            
            function updateCarousel() {
                var offset = -(currentPosition * itemWidth);
                track.style.transform = 'translateX(' + offset + '%)';
                
                var currentPage = Math.floor(currentPosition / itemsToShow);
                dots.forEach(function(dot, index) {
                    dot.classList.toggle('active', index === currentPage);
                });
            }
            
            function goToPage(pageNum) {
                currentPosition = Math.min(pageNum * itemsToShow, maxPosition);
                updateCarousel();
            }
            
            function slideNext() {
                currentPosition = currentPosition >= maxPosition ? 0 : Math.min(currentPosition + itemsToShow, maxPosition);
                updateCarousel();
            }
            
            function slidePrev() {
                currentPosition = currentPosition === 0 ? maxPosition : Math.max(currentPosition - itemsToShow, 0);
                updateCarousel();
            }
            
            prevBtn.addEventListener('click', slidePrev);
            nextBtn.addEventListener('click', slideNext);
            
            <?php if($atts['autoplay'] === 'true'): ?>
            function startAutoplay() {
                autoplayInterval = setInterval(slideNext, <?php echo intval($atts['speed']); ?>);
            }
            
            function stopAutoplay() {
                clearInterval(autoplayInterval);
            }
            
            startAutoplay();
            wrapper.addEventListener('mouseenter', stopAutoplay);
            wrapper.addEventListener('mouseleave', startAutoplay);
            <?php endif; ?>
            
            function adjustItemsForScreen() {
                var width = window.innerWidth;
                if(width < 576) {
                    itemsToShow = 1;
                } else if(width < 768) {
                    itemsToShow = 2;
                } else if(width < 992) {
                    itemsToShow = Math.min(3, parseInt(wrapper.dataset.items));
                } else {
                    itemsToShow = parseInt(wrapper.dataset.items);
                }
                
                itemWidth = 100 / itemsToShow;
                maxPosition = Math.max(0, totalItems - itemsToShow);
                
                items.forEach(function(item) {
                    item.style.minWidth = itemWidth + '%';
                    item.style.maxWidth = itemWidth + '%';
                });
                
                currentPosition = Math.min(currentPosition, maxPosition);
                updateCarousel();
            }
            
            window.addEventListener('resize', adjustItemsForScreen);
            adjustItemsForScreen();
        })();
        </script>
        <?php
    } elseif($display_mode === 'list') {
        ?>
        <div class="mousumi-yt-playlist-list">
            <?php foreach($videos as $index => $video): ?>
            <div class="mousumi-yt-pl-list-item" data-video-id="<?php echo esc_attr($video['id']); ?>">
                <div class="mousumi-yt-pl-list-number"><?php echo $index + 1; ?></div>
                <div class="mousumi-yt-pl-list-thumbnail">
                    <img src="<?php echo esc_url($video['thumbnail']); ?>" alt="<?php echo esc_attr($video['title']); ?>">
                    <div class="mousumi-yt-pl-play-icon">
                        <svg width="40" height="40" viewBox="0 0 68 48" fill="white">
                            <path d="M66.52,7.74c-0.78-2.93-2.49-5.41-5.42-6.19C55.79,.13,34,0,34,0S12.21,.13,6.9,1.55 C3.97,2.33,2.27,4.81,1.48,7.74C0.06,13.05,0,24,0,24s0.06,10.95,1.48,16.26c0.78,2.93,2.49,5.41,5.42,6.19 C12.21,47.87,34,48,34,48s21.79-0.13,27.1-1.55c2.93-0.78,4.64-3.26,5.42-6.19C67.94,34.95,68,24,68,24S67.94,13.05,66.52,7.74z"></path>
                            <path d="M 45,24 27,14 27,34" fill="#000"></path>
                        </svg>
                    </div>
                </div>
                <div class="mousumi-yt-pl-list-content">
                    <h3><?php echo esc_html($video['title']); ?></h3>
                    <p class="mousumi-yt-pl-list-time"><?php echo human_time_diff(strtotime($video['published']), current_time('timestamp')) . ' ago'; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
    } else {
        // Grid Mode
        ?>
        <div class="mousumi-yt-playlist-grid" data-columns="<?php echo esc_attr($atts['columns']); ?>">
            <?php foreach($videos as $video): ?>
            <div class="mousumi-yt-pl-grid-item">
                <div class="mousumi-yt-pl-video" data-video-id="<?php echo esc_attr($video['id']); ?>">
                    <div class="mousumi-yt-pl-thumbnail">
                        <img src="<?php echo esc_url($video['thumbnail']); ?>" alt="<?php echo esc_attr($video['title']); ?>">
                        <div class="mousumi-yt-pl-play-btn">
                            <svg width="68" height="48" viewBox="0 0 68 48" fill="white">
                                <path d="M66.52,7.74c-0.78-2.93-2.49-5.41-5.42-6.19C55.79,.13,34,0,34,0S12.21,.13,6.9,1.55 C3.97,2.33,2.27,4.81,1.48,7.74C0.06,13.05,0,24,0,24s0.06,10.95,1.48,16.26c0.78,2.93,2.49,5.41,5.42,6.19 C12.21,47.87,34,48,34,48s21.79-0.13,27.1-1.55c2.93-0.78,4.64-3.26,5.42-6.19C67.94,34.95,68,24,68,24S67.94,13.05,66.52,7.74z"></path>
                                <path d="M 45,24 27,14 27,34" fill="#000"></path>
                            </svg>
                        </div>
                        <div class="mousumi-yt-pl-duration"><?php echo human_time_diff(strtotime($video['published']), current_time('timestamp')) . ' ago'; ?></div>
                    </div>
                    <div class="mousumi-yt-pl-info">
                        <h3><?php echo esc_html($video['title']); ?></h3>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
    
    // Video Player Modal (only add once)
    static $modal_added = false;
    if(!$modal_added) {
        ?>
        <div id="mousumi-yt-pl-modal" class="mousumi-yt-pl-modal">
            <button class="mousumi-yt-pl-modal-close" aria-label="Close">×</button>
            <div class="mousumi-yt-pl-modal-content">
                <div class="mousumi-yt-pl-player-wrapper">
                    <iframe id="mousumi-yt-pl-player" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            </div>
        </div>
        
        <script>
        (function() {
            var modal = document.getElementById('mousumi-yt-pl-modal');
            var player = document.getElementById('mousumi-yt-pl-player');
            var closeBtn = modal.querySelector('.mousumi-yt-pl-modal-close');
            
            document.addEventListener('click', function(e) {
                var videoElement = e.target.closest('.mousumi-yt-pl-video, .mousumi-yt-pl-list-item');
                if(!videoElement) return;
                
                e.preventDefault();
                var videoId = videoElement.dataset.videoId;
                player.src = 'https://www.youtube.com/embed/' + videoId + '?autoplay=1&rel=0';
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
            
            function closeModal() {
                modal.classList.remove('active');
                player.src = '';
                document.body.style.overflow = '';
            }
            
            closeBtn.addEventListener('click', closeModal);
            
            modal.addEventListener('click', function(e) {
                if(e.target === modal) closeModal();
            });
            
            document.addEventListener('keydown', function(e) {
                if(e.key === 'Escape' && modal.classList.contains('active')) {
                    closeModal();
                }
            });
        })();
        </script>
        <?php
        $modal_added = true;
    }
    
    return ob_get_clean();
}
add_shortcode('youtube_playlist', 'mousumi_youtube_playlist_shortcode');

// 5. YouTube Playlist Styles
function mousumi_youtube_playlist_styles() {
    ?>
    <style>
        /* Reuse previous YouTube gallery styles but with -pl- prefix */
        .mousumi-yt-playlist-carousel {
            position: relative;
            width: 100%;
            margin: 40px 0;
            padding: 0 60px;
        }
        
        .mousumi-yt-pl-track-container {
            overflow: hidden;
            border-radius: 12px;
        }
        
        .mousumi-yt-pl-track {
            display: flex;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .mousumi-yt-pl-item {
            flex-shrink: 0;
            padding: 0 10px;
        }
        
        .mousumi-yt-pl-video {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .mousumi-yt-pl-video:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }
        
        .mousumi-yt-pl-thumbnail {
            position: relative;
            padding-top: 56.25%;
            background: #000;
            overflow: hidden;
        }
        
        .mousumi-yt-pl-thumbnail img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .mousumi-yt-pl-video:hover .mousumi-yt-pl-thumbnail img {
            transform: scale(1.05);
        }
        
        .mousumi-yt-pl-play-btn,
        .mousumi-yt-pl-play-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            transition: all 0.3s;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
        }
        
        .mousumi-yt-pl-video:hover .mousumi-yt-pl-play-btn,
        .mousumi-yt-pl-list-item:hover .mousumi-yt-pl-play-icon {
            transform: translate(-50%, -50%) scale(1.15);
        }
        
        .mousumi-yt-pl-duration {
            position: absolute;
            bottom: 8px;
            right: 8px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .mousumi-yt-pl-info {
            padding: 15px;
        }
        
        .mousumi-yt-pl-info h3 {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .mousumi-yt-pl-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.95);
            border: none;
            width: 55px;
            height: 55px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            z-index: 10;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .mousumi-yt-pl-nav:hover {
            background: #fff;
            transform: translateY(-50%) scale(1.1);
        }
        
        .mousumi-yt-pl-prev { left: 0; }
        .mousumi-yt-pl-next { right: 0; }
        
        .mousumi-yt-pl-indicators {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 30px;
        }
        
        .mousumi-yt-pl-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #d1d5db;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .mousumi-yt-pl-dot.active {
            background: #ff0000;
            transform: scale(1.3);
        }
        
        /* Grid */
        .mousumi-yt-playlist-grid {
            display: grid;
            gap: 25px;
            margin: 40px 0;
        }
        
        .mousumi-yt-playlist-grid[data-columns="2"] { grid-template-columns: repeat(2, 1fr); }
        .mousumi-yt-playlist-grid[data-columns="3"] { grid-template-columns: repeat(3, 1fr); }
        .mousumi-yt-playlist-grid[data-columns="4"] { grid-template-columns: repeat(4, 1fr); }
        
        /* List View */
        .mousumi-yt-playlist-list {
            margin: 40px 0;
        }
        
        .mousumi-yt-pl-list-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 15px;
            background: #fff;
            border-radius: 12px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .mousumi-yt-pl-list-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        }
        
        .mousumi-yt-pl-list-number {
            font-size: 24px;
            font-weight: 700;
            color: #6b7280;
            min-width: 40px;
            text-align: center;
        }
        
        .mousumi-yt-pl-list-thumbnail {
            position: relative;
            width: 200px;
            flex-shrink: 0;
        }
        
        .mousumi-yt-pl-list-thumbnail img {
            width: 100%;
            height: 112px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .mousumi-yt-pl-list-content {
            flex: 1;
        }
        
        .mousumi-yt-pl-list-content h3 {
            margin: 0 0 8px 0;
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            line-height: 1.4;
        }
        
        .mousumi-yt-pl-list-time {
            margin: 0;
            font-size: 13px;
            color: #6b7280;
        }
        
        /* Modal */
        .mousumi-yt-pl-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.95);
            z-index: 999999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .mousumi-yt-pl-modal.active {
            display: flex;
        }
        
        .mousumi-yt-pl-modal-content {
            width: 100%;
            max-width: 1200px;
        }
        
        .mousumi-yt-pl-player-wrapper {
            position: relative;
            padding-top: 56.25%;
            background: #000;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .mousumi-yt-pl-player-wrapper iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        
        .mousumi-yt-pl-modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.95);
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 32px;
            cursor: pointer;
            line-height: 1;
            transition: all 0.3s;
            z-index: 10;
        }
        
        .mousumi-yt-pl-modal-close:hover {
            background: #fff;
            transform: rotate(90deg);
        }
        
        @media (max-width: 992px) {
            .mousumi-yt-playlist-carousel {
                padding: 0 50px;
            }
            
            .mousumi-yt-playlist-grid[data-columns="4"] {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .mousumi-yt-playlist-carousel {
                padding: 0 40px;
            }
            
            .mousumi-yt-playlist-grid[data-columns="3"],
            .mousumi-yt-playlist-grid[data-columns="4"] {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .mousumi-yt-pl-list-thumbnail {
                width: 120px;
            }
            
            .mousumi-yt-pl-list-thumbnail img {
                height: 68px;
            }
            
            .mousumi-yt-pl-list-content h3 {
                font-size: 14px;
            }
        }
        
        @media (max-width: 576px) {
            .mousumi-yt-playlist-carousel {
                padding: 0 35px;
            }
            
            .mousumi-yt-playlist-grid {
                grid-template-columns: 1fr !important;
            }
            
            .mousumi-yt-pl-list-item {
                flex-wrap: wrap;
            }
            
            .mousumi-yt-pl-list-thumbnail {
                width: 100%;
            }
        }
    </style>
    <?php
}
add_action('wp_head', 'mousumi_youtube_playlist_styles');

// 6. Admin Column - Show Shortcode
function mousumi_yt_playlist_admin_columns($columns) {
    $new_columns = array();
    foreach($columns as $key => $value) {
        $new_columns[$key] = $value;
        if($key === 'title') {
            $new_columns['shortcode'] = 'Shortcode';
            $new_columns['playlist'] = 'Playlist ID';
        }
    }
    return $new_columns;
}
add_filter('manage_youtube_playlist_posts_columns', 'mousumi_yt_playlist_admin_columns');

function mousumi_yt_playlist_admin_column_content($column, $post_id) {
    if($column === 'shortcode') {
        echo '<code>[youtube_playlist id="' . $post_id . '"]</code>';
    }
    if($column === 'playlist') {
        $playlist_id = get_post_meta($post_id, '_playlist_id', true);
        echo $playlist_id ? '<code>' . esc_html($playlist_id) . '</code>' : '—';
    }
}
add_action('manage_youtube_playlist_posts_custom_column', 'mousumi_yt_playlist_admin_column_content', 10, 2);

/**
 * Preloader - Minimal & Professional
 */
function oceanwp_preloader_styles() {
	if ( is_admin() ) return;

	$css = '
	html.preloader-active {
		overflow: hidden !important;
		height: 100% !important;
	}
	#oceanwp-preloader {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background: #ffffff;
		display: flex;
		align-items: center;
		justify-content: center;
		z-index: 999999;
		opacity: 1;
		visibility: visible;
		transition: opacity 0.6s ease, visibility 0.6s ease;
	}
	#oceanwp-preloader.loaded {
		opacity: 0;
		visibility: hidden;
		pointer-events: none;
	}
	.preloader-inner {
		text-align: center;
	}
	.preloader-logo {
		max-width: 160px;
		height: auto;
		margin-bottom: 25px;
		animation: preloaderPulse 1.5s ease-in-out infinite;
	}
	.preloader-site-name {
		font-size: 28px;
		font-weight: 600;
		color: #333;
		margin: 0 0 25px 0;
		letter-spacing: 2px;
		animation: preloaderPulse 1.5s ease-in-out infinite;
	}
	.preloader-bar {
		width: 200px;
		height: 3px;
		background: #e0e0e0;
		border-radius: 3px;
		overflow: hidden;
		margin: 0 auto;
	}
	.preloader-bar-inner {
		width: 0%;
		height: 100%;
		background: #13aff0;
		border-radius: 3px;
		animation: preloaderProgress 1.8s ease-in-out infinite;
	}
	@keyframes preloaderPulse {
		0%, 100% { opacity: 1; }
		50% { opacity: 0.5; }
	}
	@keyframes preloaderProgress {
		0% { width: 0%; margin-left: 0; }
		50% { width: 70%; margin-left: 0; }
		100% { width: 0%; margin-left: 100%; }
	}
	';

	wp_register_style( 'oceanwp-preloader', false );
	wp_enqueue_style( 'oceanwp-preloader' );
	wp_add_inline_style( 'oceanwp-preloader', $css );
}
add_action( 'wp_enqueue_scripts', 'oceanwp_preloader_styles', 1 );

function oceanwp_preloader_inline_script() {
	if ( is_admin() ) return;
	?>
	<script>
	document.documentElement.classList.add('preloader-active');
	</script>
	<?php
}
add_action( 'wp_head', 'oceanwp_preloader_inline_script', 1 );

function oceanwp_preloader_script() {
	if ( is_admin() ) return;

	$js = '
	(function() {
		function hidePreloader() {
			var preloader = document.getElementById("oceanwp-preloader");
			if (!preloader || preloader.classList.contains("loaded")) return;
			preloader.classList.add("loaded");
			document.documentElement.classList.remove("preloader-active");
			setTimeout(function() {
				preloader.style.display = "none";
			}, 600);
		}

		if (document.readyState === "complete") {
			hidePreloader();
		} else {
			window.addEventListener("load", hidePreloader);
		}

		// Safety fallback: max 5 seconds e preloader hide hobe
		setTimeout(hidePreloader, 5000);
	})();
	';

	wp_register_script( 'oceanwp-preloader-js', false, array(), false, true );
	wp_enqueue_script( 'oceanwp-preloader-js' );
	wp_add_inline_script( 'oceanwp-preloader-js', $js );
}
add_action( 'wp_enqueue_scripts', 'oceanwp_preloader_script', 1 );
