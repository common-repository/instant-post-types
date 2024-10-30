<?php
/**
 * http://pluginscorner.com
 *
 * @package instant-post-types
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class CPT_PC_Tutorial_Controller extends CPT_PC_REST_Controller {

	public $base = 'tutorial';

	/**
	 * Register Routes
	 */
	public function register_routes() {

		register_rest_route( self::$namespace . self::$version, '/' . $this->base, array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'save_tutorial' ),
				'permission_callback' => array( $this, 'admin_permissions_check' ),
				'args'                => array(),
			),
		) );
	}

	/**
	 * Add post type to the db
	 *
	 * @param $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function save_tutorial( $request ) {
		/**
		 * We should add the new post type
		 *
		 * @var WP_REST_Request $request
		 */
		$params = $request->get_params();
		update_option( 'cpt_pc_tutorial_finished', $params['tutorial_finished'] );
		update_option( 'cpt_pc_tutorial_step', $params['tutorial_step'] );

		return new WP_REST_Response( __( $params, CPT_PC_Const::T ), 200 );
	}
}
