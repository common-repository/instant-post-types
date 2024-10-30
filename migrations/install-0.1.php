<?php
/**
 * TheOnePlugin - https://theoneplugin.com
 * Created by St0rK.
 * User: stork
 * Date: 4/8/2017
 * Time: 3:49 PM
 *
 * @package custom-fields
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class CPT_PC_Install_DB
 */
class CPT_PC_Install_DB {
	/**
	 * global wpdb instance
	 * @var wpdb wpdb
	 */
	private $wpdb;

	/**
	 * @var string
	 */
	private $table_name;

	/**
	 * @var string
	 */
	private $plugin_prefix = 'cpt_pc_';

	/**
	 * @var string
	 */
	private $prefix;
	
	/**
	 * CPT_PC_Install_DB constructor.
	 */
	public function __construct() {
		/**
		 * don't do squat if we don't have an upgrade set up
		 */
		if ( ! defined( 'CPT_PC_DATABASE_UPGRADE' ) ) {
			exit;
		}
		/**
		 * set the wpdb within the class
		 */
		global $wpdb;
		/** @var wpdb wpdb */
		$this->wpdb   = $wpdb;
		$this->prefix = $this->wpdb->prefix . $this->plugin_prefix;

		$this->create_tables();
	}

	/**
	 * Wrapper for all the tables needed on the install
	 */
	private function create_tables() {
		$this->create_post_type_table();
		$this->create_taxonomy_table();
	}

	/**
	 * Create the post types table if it doesn't exist yet
	 */
	private function create_post_type_table() {

		$this->table_name = $this->prefix . 'custom_post_types';

		$sql = "CREATE TABLE  IF NOT EXISTS {$this->table_name} (
			ID INT( 11 ) NOT NULL AUTO_INCREMENT,
			slug VARCHAR( 255 ) NOT NULL,
			labels LONGTEXT NULL,
			description TINYTEXT NULL,
			public TINYINT( 1 ) DEFAULT '1',
			exclude_from_search TINYINT( 1 ) DEFAULT '0',
			publicly_queryable TINYINT( 1 ) DEFAULT '1',
			show_ui TINYINT( 1 ) DEFAULT '1',
			show_in_nav_menus TINYINT( 1 ) DEFAULT '1',
			show_in_menu TINYINT( 1 ) DEFAULT '1',
			show_in_admin_bar TINYINT( 1 ) DEFAULT '1',
			menu_position TINYINT( 255 ) DEFAULT '5',
			menu_icon VARCHAR( 255 ) NULL,
			capabilities LONGTEXT NULL,
			map_meta_cap LONGTEXT NULL,
			hierarchical TINYINT( 1 ) DEFAULT '0',
			supports LONGTEXT NULL,
			register_meta_box_cb  VARCHAR( 255 ) NULL,
			taxonomies LONGTEXT NULL,
			has_archive TINYINT( 1 ) DEFAULT '1',
			rewrite LONGTEXT NULL,
			query_var TINYINT( 1 ) DEFAULT '1',
			can_export TINYINT( 1 ) DEFAULT '1',
			delete_with_user TINYINT( 1 ) DEFAULT '1',
			UNIQUE KEY (slug),
			PRIMARY KEY (ID)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

		$this->wpdb->query( $sql );
	}

	/**
	 * Create the post types table if it doesn't exist yet
	 */
	private function create_taxonomy_table() {
		$this->table_name = $this->prefix . 'custom_taxonomies';
		$sql = "CREATE TABLE  IF NOT EXISTS {$this->table_name} (
			ID INT( 11 ) NOT NULL AUTO_INCREMENT,
			slug VARCHAR( 255 ) NOT NULL,
			post_types LONGTEXT NULL,
			labels LONGTEXT NULL,
			description TINYTEXT NULL,
			public TINYINT( 1 ) DEFAULT '1',
			show_ui TINYINT( 1 ) DEFAULT '1',
			show_in_menu TINYINT( 1 ) DEFAULT '1',
			show_in_nav_menus TINYINT( 1 ) DEFAULT '1',
			show_tagcloud TINYINT( 1 ) DEFAULT '1',
			show_in_quick_edit TINYINT( 1 ) DEFAULT '1',
			meta_box_cb  VARCHAR( 255 ) NULL,
			show_admin_column TINYINT( 1 ) DEFAULT '0',
			hierarchical TINYINT( 1 ) DEFAULT '0',
			update_count_callback  VARCHAR( 255 ) NULL,
			query_var  VARCHAR( 255 ) NULL,
			rewrite LONGTEXT NULL,
			capabilities LONGTEXT NULL,
			sort TINYINT( 1 ) DEFAULT '0',
			UNIQUE KEY (slug),
			PRIMARY KEY (ID)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

		$this->wpdb->query( $sql );
	}
}
