<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_App_Imgur_Settings {

	/**
	 * The single instance of LTPLE_App_Imgur_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public function __construct ( $parent ) {
		
		$this->parent = $parent;
		
		$this->plugin 		 	= new stdClass();
		$this->plugin->slug  	= 'live-template-editor-app-imgur';
		
		add_action('ltple_plugin_settings', array($this, 'plugin_info' ) );
		
		add_action('ltple_plugin_settings', array($this, 'settings_fields' ) );
		
		add_action( 'ltple_admin_menu' , array( $this, 'add_menu_items' ) );	
	}
	
	public function plugin_info(){
		
		$this->parent->settings->addons['app-imgur-plugin'] = array(
			
			'title' 		=> 'App Imgur Plugin',
			'addon_link' 	=> 'https://github.com/rafasashi/live-template-editor-app-imgur',
			'addon_name' 	=> 'live-template-editor-app-imgur',
			'source_url' 	=> 'https://github.com/rafasashi/live-template-editor-app-imgur/archive/master.zip',
			'description'	=> 'Imgur API integrator for Live Template Editor',
			'author' 		=> 'Rafasashi',
			'author_link' 	=> 'https://profiles.wordpress.org/rafasashi/',
		);		
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	public function settings_fields () {
		
	}
	
	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_items () {
		
		//add menu in wordpress dashboard
		/*
		add_submenu_page(
			'live-template-editor-client',
			__( 'App Imgur', $this->plugin->slug ),
			__( 'App Imgur', $this->plugin->slug ),
			'edit_pages',
			'edit.php?post_type=post'
		);
		*/
	}
}
