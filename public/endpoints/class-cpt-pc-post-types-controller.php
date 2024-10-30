<?php
/**
 * http://pluginscorner.com
 *
 * @package instant-post-types
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class CPT_PC_Post_Types_Controller extends CPT_PC_REST_Controller {

	public $base = 'post_types';

	/**
	 * Register Routes
	 */
	public function register_routes() {

		register_rest_route( self::$namespace . self::$version, '/' . $this->base, array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_post_type' ),
				'permission_callback' => array( $this, 'admin_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/(?P<ID>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_post_type' ),
				'permission_callback' => array( $this, 'admin_permissions_check' ),
				'args'                => array(),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'edit_post_type' ),
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
	public function create_post_type( $request ) {
		/**
		 * We should add the new post type
		 * @var WP_REST_Request $request
		 */
		$params = $request->get_params();

		$model = CPT_PC_Custom_Post_Type_Helpers::create_post_type( $params );

		if ( is_array( $model ) ) {
			return new WP_REST_Response( $model, 200 );
		}

		return new WP_Error( 'no-results', __( 'Post Type could not be added. Try again ! If the problem persists contact our support team', CPT_PC_Const::T ) );
	}

	/**
	 * Edit a post type
	 * @param $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function edit_post_type( $request ) {
		/**
		 * We should edit the post type
		 * @var WP_REST_Request $request
		 */
		$params = $request->get_params();

		$model = CPT_PC_Custom_Post_Type_Helpers::edit_post_type( $params );

		if ( is_array( $model ) ) {
			return new WP_REST_Response( $model, 200 );
		}

		return new WP_Error( 'no-results', __( 'Post Type could not be edited. Try again ! If the problem persists contact our support team.', CPT_PC_Const::T ) );
	}

	/**
	 * Delete a Post type
	 * @param $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_post_type( $request ) {
		/**
		 * We should edit the post type
		 * @var WP_REST_Request $request
		 */
		$id = $request->get_param( 'ID' );

		$result = CPT_PC_Custom_Post_Type_Helpers::delete_post_type( $id );

		if ( $result ) {
			return new WP_REST_Response( $result, 200 );
		}

		return new WP_Error( 'no-results', __( 'Post Type cannot be deleted. Try again ! If the problem persists contact our support team.', CPT_PC_Const::T ) );
	}
}