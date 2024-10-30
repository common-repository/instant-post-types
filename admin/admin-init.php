<?php
/**
 * http://pluginscorner.com
 *
 * @package instant-post-types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class CPT_PC_Custom_Fields_Admin_Init extends CPT_PC_Init {

	public function init() {
		$this->db_check();
	}

	/**
	 * Add the actions
	 */
	public function add_actions() {
		/**
		 * Add the wrapper for the header
		 */
		$this->loader->add_action( 'admin_footer', $this, 'add_footer_wrapper' );
//		$this->loader->add_action( 'edit_page_form', $this, 'add_metabox_wrapper' );
//		$this->loader->add_action( 'edit_form_advanced', $this, 'add_metabox_wrapper' );
//		$this->loader->add_action( 'submitpost_box', $this, 'add_metabox_wrapper' );
//		$this->loader->add_action( 'submitpage_box', $this, 'add_metabox_wrapper' );
//		$this->loader->add_action( 'dbx_post_sidebar', $this, 'add_metabox_wrapper' );
//		add_action( 'add_meta_boxes', array( $this, 'adding_custom_meta_boxes' ), 10, 2 );
		/**
		 * Enqueue admin scripts
		 */
		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_scripts' );
	}

	function adding_custom_meta_boxes( $post_type, $post ) {
		add_meta_box(
			'my-meta-box2',
			__( 'My Meta Box' ),
			array( $this, 'render_my_meta_box' ),
			'post',
			'advanced',
			'default'
		);
	}

	public function render_my_meta_box() {

	}

	public function add_metabox_wrapper() {
		echo 'add metabox';
	}

	/**
	 * Enqueue the scripts for the admin area (this should be available for ALL pages within the admin)
	 */
	public function enqueue_scripts() {
		$this->enqueue_style( 'ect-styles-css', CPT_PC_Const::plugin_uri( ) . $this->include_dir . '/css/styles.css' );
		/**
		 * Enqueue the admin js which will hold all the backbone stuff
		 */
		$this->enqueue_script( 'cpt-pc-admin-js', CPT_PC_Const::plugin_uri() . $this->include_dir . '/js/dist/main.min.js', array(
			'jquery',
			'backbone',
			'wp-color-picker',
		), false, true );

		wp_localize_script( 'cpt-pc-admin-js', 'CPT_PC', $this->get_localization() );
	}

	/**
	 * Return data for localizing
	 *
	 * @return array
	 */
	private function get_localization() {

		return array(
			'screen'   => get_current_screen(),
			'tutorial' => array(
				'tutorial_finished' => get_option('cpt_pc_tutorial_finished'),
				'tutorial_step' => get_option('cpt_pc_tutorial_step', 1),
			),
			'routes'   => array(
				'post_types' => $this->get_route_url( 'post_types' ),
				'taxonomy'   => $this->get_route_url( 'taxonomy' ),
				'tutorial'   => $this->get_route_url( 'tutorial' ),
			),
			'data'     => array(
				'all_post_types'      => CPT_PC_Custom_Post_Type_Helpers::get_all_post_types(),
				'post_types'          => CPT_PC_Custom_Post_Type_Helpers::get_post_types(),
				'taxonomies'          => CPT_PC_Custom_Post_Type_Helpers::get_taxonomies(),
				'built_in_taxonomies' => CPT_PC_Custom_Post_Type_Helpers::get_all_taxonomies( array(
					'public'   => true,
					'_builtin' => true,
				) ),
			),
		);
	}

	/**
	 * Check if we have the latest db tables if not we should update them
	 */
	private function db_check() {
		new CPT_PC_DB_Manager();
	}

	/**
	 * Add the wrapers
	 */
	public function add_footer_wrapper() {
		echo '<div id="cpt-pc-admin-bar" class="cpt-pc-material-container"><div class="cpt-pc-admin-bar-container cpt-pc-main-container"></div></div><div class="cpt-pc-post-types-container cpt-pc-material-container"></div>';
	}
}
