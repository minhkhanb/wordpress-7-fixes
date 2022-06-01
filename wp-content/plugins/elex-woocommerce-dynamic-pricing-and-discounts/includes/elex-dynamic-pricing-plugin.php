<?php

if ( ! class_exists( 'Elex_DP_Dynamic_Pricing_Plugin' ) ) {


	class Elex_DP_Dynamic_Pricing_Plugin {

		/**
		 * The loader that's responsible for maintaining and registering all hooks that power
		 * the plugin.
		 *
		 * @since    1.0.0
		 * @var      xa_dynamic_pricing_plugin_Loader    $loader    Maintains and registers all hooks for the plugin.
		 */
		protected $loader;

		/**
		 * The unique identifier of this plugin.
		 *
		 * @since    1.0.0
		 * @var      string    $xa_dynamic_pricing_plugin    The string used to uniquely identify this plugin.
		 */
		protected $xa_dynamic_pricing_plugin;

		/**
		 * The current version of the plugin.
		 *
		 * @since    1.0.0
		 * @var      string    $version    The current version of the plugin.
		 */
		protected $version;

		/**
		 * Define the core functionality of the plugin.
		 *
		 * Set the plugin name and the plugin version that can be used throughout the plugin.
		 * Load the dependencies, define the locale, and set the hooks for the admin area and
		 * the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {

			$this->xa_dynamic_pricing_plugin = 'xa-dynamic-pricing-plugin';
			$this->version = '3.0.1';

			$this->elex_dp_load_dependencies();
			$this->elex_dp_set_locale();
			if ( is_admin() ) {
				$this->elex_dp_define_admin_hooks();
			}

			if ( ! function_exists( 'elex_dp_plugin_override' ) ) {
				add_action( 'plugins_loaded', 'elex_dp_plugin_override' );

				function elex_dp_plugin_override() {
					if ( ! function_exists( 'WC' ) ) {

						function WC() {
							return $GLOBALS['woocommerce'];
						}
					}
				}
			}
		}

		/**
		 * Load the required dependencies for this plugin.
		 *
		 * Include the following files that make up the plugin:
		 *
		 * - xa_dynamic_pricing_plugin_Loader. Orchestrates the hooks of the plugin.
		 * - xa_dynamic_pricing_plugin_i18n. Defines internationalization functionality.
		 * - xa_dynamic_pricing_plugin_Admin. Defines all hooks for the admin area.
		 * - xa_dynamic_pricing_plugin_Public. Defines all hooks for the public side of the site.
		 *
		 * Create an instance of the loader which will be used to register the hooks
		 * with WordPress.
		 *
		 * @since    1.0.0
		 */
		private function elex_dp_load_dependencies() {

			/**
			 * The class responsible for organizing the actions and filters of the
			 * core plugin.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/elex-dynamic-pricing-plugin-loader.php';

			/**
			 * The class responsible for defining internationalization functionality
			 * of the plugin.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/elex-dynamic-pricing-plugin-i18n.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/elex-common-functions.php';

			/**
			 * The class responsible for defining all actions that occur in the admin area.
			 */
			if ( is_admin() ) {
				require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/elex-dynamic-pricing-plugin-admin.php';
			}
			/**
			 * The class responsible for defining all actions that occur in the public-facing
			 * side of the site.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/elex-dynamic-pricing-plugin-public.php';

			$this->loader = new Elex_DP_Dynamic_Pricing_Plugin_Loader();
		}

		/**
		 * Define the locale for this plugin for internationalization.
		 *
		 * Uses the Elex_DP_Dynamic_Pricing_Plugin_I18n class in order to set the domain and to register the hook
		 * with WordPress.
		 *
		 * @since    1.0.0
		 */
		private function elex_dp_set_locale() {

			$plugin_i18n = new Elex_DP_Dynamic_Pricing_Plugin_I18n();

			$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'elex_dp_load_plugin_textdomain' );
		}

		/**
		 * Register all of the hooks related to the admin area functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 */
		private function elex_dp_define_admin_hooks() {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/elex-admin-actions-function.php';  // class contains list of Function for Actions
			$list_of_actions_function = new Elex_DP_Admin_Actions_Function();

			$http_referer = '';
			if ( isset( $_SERVER['HTTP_REFERER'] ) && ! empty( $_SERVER['HTTP_REFERER'] ) ) {
				$http_referer = sanitize_text_field( $_SERVER['HTTP_REFERER'] );
			}

			if ( ( isset( $_GET['page'] ) && 'dynamic-pricing-main-page' == sanitize_text_field( $_GET['page'] ) ) || ( strpos( $http_referer, 'dynamic-pricing-main-page' ) > 0 ) ) {
				$plugin_admin = new Elex_DP_Dynamic_Pricing_Plugin_Admin( $this->elex_dp_get_dynamic_pricing_plugin(), $this->get_version() );

				$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'elex_dp_enqueue_styles' );
				$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'elex_dp_enqueue_scripts' );
				$this->loader->add_action( 'admin_print_styles', $list_of_actions_function, 'elex_dp_func_enqueue_search_product_enhanced_select' );
				$this->loader->add_action( 'admin_enqueue_scripts', $list_of_actions_function, 'elex_dp_func_enqueue_jquery' );
				$this->loader->add_action( 'admin_enqueue_scripts', $list_of_actions_function, 'elex_dp_func_enqueue_jquery_ui_datepicker' );
			}
			$this->loader->add_action( 'admin_menu', $list_of_actions_function, 'elex_dp_register_sub_menu' );
		}

		/**
		 * Run the loader to execute all of the hooks with WordPress.
		 *
		 * @since    1.0.0
		 */
		public function run() {
			$this->loader->run();
		}

		/**
		 * The name of the plugin used to uniquely identify it within the context of
		 * WordPress and to define internationalization functionality.
		 *
		 * @since     1.0.0
		 * @return    string    The name of the plugin.
		 */
		public function elex_dp_get_dynamic_pricing_plugin() {
			return $this->xa_dynamic_pricing_plugin;
		}

		/**
		 * The reference to the class that orchestrates the hooks with the plugin.
		 *
		 * @since     1.0.0
		 * @return    xa_dynamic_pricing_plugin_Loader    Orchestrates the hooks of the plugin.
		 */
		public function get_loader() {
			return $this->loader;
		}

		/**
		 * Retrieve the version number of the plugin.
		 *
		 * @since     1.0.0
		 * @return    string    The version number of the plugin.
		 */
		public function get_version() {
			return $this->version;
		}

	}

}
