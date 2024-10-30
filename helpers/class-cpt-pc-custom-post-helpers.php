<?php
/**
 * http://pluginscorner.com
 *
 * @package instant-post-types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class CPT_PC_Custom_Post_Type_Helpers extends CPT_PC_Helpers {

	/**
	 * @var string
	 */
	private static $prefix;

	/**
	 * @var string
	 */ 
	private static $table_name;

	/**
	 * @var string
	 */
	private static $plugin_prefix = 'cpt_pc_';

	/**
	 * CPT_PC_Custom_Post_Type_Helpers constructor.
	 *
	 * @param string $component_name
	 */
	public function __construct( $component_name = '' ) {
		parent::__construct( $component_name );
		$this->load_helper( 'helpers/class-cpt-pc-inflector.php' );

		self::$prefix = self::$wpdb->prefix . self::$plugin_prefix;
	}

	/**
	 * Add the actions
	 */
	public function add_actions() {
		$this->loader->add_action( 'init', $this, 'register_custom_taxonomies' );
		$this->loader->add_action( 'init', $this, 'register_custom_post_types' );
		$this->loader->run("init");
	}

	/**
	 * Register the custom post types
	 */
	public function register_custom_post_types() {
		$post_types = self::get_post_types();

		foreach ( $post_types as $post_type ) {
			$post_type['label'] = $post_type['labels']['name'];

			if ( ! post_type_exists( $post_type['slug'] ) ) {
				register_post_type( $post_type['slug'], $post_type );
			}
		}
	}

	/**
	 * Register the custom post types
	 */
	public function register_custom_taxonomies() {
		$taxonomies = self::get_taxonomies();

		foreach ( $taxonomies as $taxonomy ) {
			$taxonomy['label'] = $taxonomy['labels']['name'];

			if ( ! taxonomy_exists( $taxonomy['slug'] ) ) {
				/**
				 * TODO: Capabilities should be added at a later phase
				 */
				unset($taxonomy['capabilities']);

				register_taxonomy( $taxonomy['slug'], $taxonomy['post_types'], $taxonomy );
			}
		}
	}

	/**
	 * Gets all the post types after they were registered
	 * @return array
	 */
	public static function get_all_post_types() {
		$args   = array(
			'_builtin' => true,
		);
		$output = 'objects';

		$post_types = get_post_types( $args, $output );

		$post_types = array_values( $post_types );

		/**
		 * Get custom post types
		 */
		$args   = array(
			'_builtin' => false,
		);
		$output = 'objects';

		$custom_post_types = get_post_types( $args, $output );

		$custom_post_types = array_values( $custom_post_types );

		$post_types = array_merge( $post_types, $custom_post_types );

		$post_types = self::convert_post_types( $post_types );

		return $post_types;
	}

	/**
	 * Converts the post type objects to arrays
	 *
	 * @param $post_types
	 *
	 * @return array
	 */
	public static function convert_post_types( $post_types ) {
		$converted_post_types = array();

		foreach ( $post_types as $post_type ) {
			$post_type->slug        = $post_type->name;
			$converted_post_types[] = (array) $post_type;
		}

		return $converted_post_types;
	}

	/**
	 * Return either all post types or a specific post type by ID
	 *
	 * @param null $id
	 *
	 * @return array|null|object
	 */
	public static function get_post_types( $id = null ) {
		self::$table_name = self::cpt_pc_table_name( 'custom_post_types' );
		$query            = 'SELECT * FROM ' . self::$table_name;

		if ( $id ) {
			$query .= ' WHERE ID = %d';
			$query = self::$wpdb->prepare( $query, array( $id ) );
		}

		$results = self::$wpdb->get_results( $query, ARRAY_A );
		$taxonomies = self::get_taxonomies();

		if ( ! empty( $results ) ) {
			foreach ( $results as $key => $result ) {

				foreach ( $result as $k => $value ) {
					if ( $k == 'ID' ) {
						$result[ $k ] = (int) $value;
					}
					if ( ( $value == '0' && $k != 'ID' ) ) {
						$result[ $k ] = false;
					}

					if ( ( $value == '1' && $k != 'ID' ) ) {
						$result[ $k ] = true;
					}
				}

				$result_taxonomies = maybe_unserialize( $result['taxonomies'] );
//				dump($result_taxonomies);

				if(!empty($taxonomies)) {
					foreach ($taxonomies as $taxonomy) {
						if(in_array($result['slug'], $taxonomy['post_types']) && in_array($taxonomy['slug'], $result_taxonomies)) {
							$result_taxonomies[] = $taxonomy['slug'];

                            register_taxonomy_for_object_type($taxonomy['slug'], $result['slug']);
						}
					}
				}

				$results[ $key ]                 = $result;
				$results[ $key ]['labels']       = maybe_unserialize( $result['labels'] );
				$results[ $key ]['supports']     = maybe_unserialize( $result['supports'] );
				$results[ $key ]['capabilities'] = maybe_unserialize( $result['capabilities'] );
				$results[ $key ]['taxonomies']   = $result_taxonomies;
				$results[ $key ]['rewrite']      = maybe_unserialize( $result['rewrite'] );
			}
		}

		return $results;
	}

	/**
	 * Return either all post types or a specific post type by ID
	 *
	 * @param null $id
	 *
	 * @return array|null|object
	 */
	public static function get_taxonomies( $id = null ) {
		self::$table_name = self::cpt_pc_table_name( 'custom_taxonomies' );
		$query            = 'SELECT * FROM ' . self::$table_name;

		if ( $id ) {
			$query .= ' WHERE ID = %d';
			$query = self::$wpdb->prepare( $query, array( $id ) );
		}

		$results = self::$wpdb->get_results( $query, ARRAY_A );

		if ( ! empty( $results ) ) {
			foreach ( $results as $key => $result ) {

				foreach ( $result as $k => $value ) {
					if ( $k == 'ID' ) {
						$result[ $k ] = (int) $value;
					}
					if ( ( $value == '0' && $k != 'ID' ) ) {
						$result[ $k ] = false;
					}

					if ( ( $value == '1' && $k != 'ID' ) ) {
						$result[ $k ] = true;
					}
				}

				$results[ $key ]                 = $result;
				$results[ $key ]['labels']       = maybe_unserialize( $result['labels'] );
				$results[ $key ]['post_types']   = maybe_unserialize( $result['post_types'] );
				$results[ $key ]['capabilities'] = maybe_unserialize( $result['capabilities'] );
				$results[ $key ]['rewrite']      = maybe_unserialize( $result['rewrite'] );
			}
		}

		return $results;
	}

	/**
	 * Construct the table name
	 *
	 * @param $tablename
	 *
	 * @return string
	 */
	private static function cpt_pc_table_name( $tablename ) {
		return self::$prefix . $tablename;
	}

	/**
	 * Get the taxonomies from the DB
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public static function get_all_taxonomies( $args = array() ) {

		$output = 'objects';

		return get_taxonomies( $args, $output );
	}

	/**
	 * Create a post type
	 *
	 * @param $params
	 *
	 * @return string
	 */
	public static function create_post_type( $params ) {

		$model                  = $params;
		self::$table_name       = self::cpt_pc_table_name( 'custom_post_types' );
		$params['labels']       = maybe_serialize( $params['labels'] );
		$params['supports']     = maybe_serialize( $params['supports'] );
		$params['capabilities'] = maybe_serialize( $params['capabilities'] );
		$params['taxonomies']   = maybe_serialize( $params['taxonomies'] );
		$params['rewrite']      = maybe_serialize( $params['rewrite'] );
		self::$wpdb->hide_errors();

		$result = self::$wpdb->insert(
			self::$table_name,
			$params,
			array(
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
			)
		);

		if ( $result ) {
			$id          = self::$wpdb->insert_id;
			$model['ID'] = $id;

			return $model;
		}

		return 'Unknown error';
	}

	/**
	 * Create a post type
	 *
	 * @param $params
	 *
	 * @return string
	 */
	public static function create_taxonomy( $params ) {

		$model                  = $params;
		self::$table_name       = self::cpt_pc_table_name( 'custom_taxonomies' );
		$params['labels']       = maybe_serialize( $params['labels'] );
		$params['post_types']   = maybe_serialize( $params['post_types'] );
		$params['capabilities'] = maybe_serialize( $params['capabilities'] );
		$params['rewrite']      = maybe_serialize( $params['rewrite'] );
		self::$wpdb->hide_errors();

		$result = self::$wpdb->insert(
			self::$table_name,
			$params,
			array(
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%d',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
			)
		);

		if ( $result ) {
			$id          = self::$wpdb->insert_id;
			$model['ID'] = $id;

			return $model;
		}

		return 'Unknown error';
	}

	/**
	 * Edit the post type
	 *
	 * @param $params
	 *
	 * @return mixed
	 */
	public static function edit_taxonomy( $params ) {
		$id = $params['ID'];
		unset( $params[ $id ] );
		$model = $params;
		unset( $params['ID'] );

		self::$table_name       = self::cpt_pc_table_name( 'custom_taxonomies' );
		$params['labels']       = maybe_serialize( $params['labels'] );
		$params['post_types']   = maybe_serialize( $params['post_types'] );
		$params['capabilities'] = maybe_serialize( $params['capabilities'] );
		$params['rewrite']      = maybe_serialize( $params['rewrite'] );
		self::$wpdb->hide_errors();

		$result = self::$wpdb->update(
			self::$table_name,
			$params,
			array( 'ID' => $id ),
			array(
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%d',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
			),
			array( '%d' )
		);

		if ( $result ) {

			return $model;
		}

		return 'Unknown Error';
	}

	/**
	 * Edit the post type
	 *
	 * @param $params
	 *
	 * @return mixed
	 */
	public static function edit_post_type( $params ) {
		$id = $params['ID'];
		unset( $params[ $id ] );
		$model = $params;
		unset( $params['ID'] );

		self::$table_name       = self::cpt_pc_table_name( 'custom_post_types' );
		$params['labels']       = maybe_serialize( $params['labels'] );
		$params['supports']     = maybe_serialize( $params['supports'] );
		$params['capabilities'] = maybe_serialize( $params['capabilities'] );
		$params['taxonomies']   = maybe_serialize( $params['taxonomies'] );
		$params['rewrite']      = maybe_serialize( $params['rewrite'] );
		self::$wpdb->hide_errors();



		$result = self::$wpdb->update(
			self::$table_name,
			$params,
			array( 'ID' => $id ),
			array(
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
				'%d',
				'%d',
			),
			array( '%d' )
		);

		if ( $result ) {
			return $model;
		}

		return 'Unknown Error';
	}

	/**
	 * Delete a custom taxonomy
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	public static function delete_taxonomy( $id ) {
		self::$table_name = self::cpt_pc_table_name( 'custom_taxonomies' );

		$result = self::$wpdb->delete( self::$table_name, array( 'ID' => $id ), array( '%d' ) );

		return $result;
	}

	/**
	 * Delete a custom post type
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	public static function delete_post_type( $id ) {
		self::$table_name = self::cpt_pc_table_name( 'custom_post_types' );

		$result = self::$wpdb->delete( self::$table_name, array( 'ID' => $id ), array( '%d' ) );

		return $result;
	}
}
