<?php
/*
Plugin Name: WP-Asambleas
Plugin URI: https://asamblea.co/plugin-para-wordpress/
Description: Gestión de votaciones y asambleas virtuales
Version: 2.85.0
Author: PLATCOM
Author URI: https://asamblea.co
Stable tag: 2.85.0

*

//error_reporting(0);
//ini_set('display_errors', 'On');

/**  User category functionality  */
define( 'USER_CATEGORY_NAME', 'poll_category' );
define( 'USER_CATEGORY_META_KEY', '_user_category' );
define( 'USER_CATEGORY_NAME_META_KEY', '_user_category_name' );

// core initiation
if( !class_Exists('vooMainVote') ){
	class vooMainVote{
		public $locale;
		function __construct( $locale, $includes, $path ){
			$this->locale = $locale;
			
			// include files
			foreach( $includes as $single_path ){
				include( $path.$single_path );				
			}
			// calling localization
			add_action('plugins_loaded', array( $this, 'myplugin_init' ) );
			//register_activation_hook( __FILE__, 'plugin_activation' );
		}

		function plugin_activation(){
			flush_rewrite_rules();
		}

		function myplugin_init() {
		 	$plugin_dir = basename(dirname(__FILE__));
		 	load_plugin_textdomain( $this->locale , false, $plugin_dir );
		}
	}
	
	
}



// initiate main class
 
$obj = new vooMainVote('wvp', array(
	'modules/formElementsClass.php',
	'modules/settings.php',
	'modules/ajax.php',
	'modules/scripts.php',
	'modules/hooks.php',
	'modules/cpt.php',
	'modules/meta_box.php',
	'modules/shortcodes.php',
	'modules/functions.php',
), dirname(__FILE__).'/' );
 
 
register_activation_hook( __FILE__, 'wvp_activate' );
 
 
function wvp_activate() {
require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); 
 global $wpdb;
 $table_name = 'online_log';
 $table_name =  $wpdb->prefix.$table_name;
 
 
 //$wpdb->query("DROP TABLE ".$table_name );

$sql = "CREATE TABLE IF NOT EXISTS $table_name (  
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `date`  datetime ,  
  `total_users` bigint(20)  ,
  `total_online_shares` float(20) ,
  `percent_of_shares` float(20) ,  
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

 

dbDelta($sql);


$table_name = 'online_users_log';
$table_name =  $wpdb->prefix.$table_name;


//$wpdb->query("DROP TABLE ".$table_name );

$sql = "CREATE TABLE IF NOT EXISTS $table_name (  
 `id` mediumint(9) NOT NULL AUTO_INCREMENT,
 `date`  varchar(15) ,  
 `user` longtext,
 `own_shares` varchar(10),
 `recieved_proxies` varchar(10),
 `shares_in_proxies` varchar(10),
 UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";



dbDelta($sql);

$table_name = 'user_logins';
$table_name =  $wpdb->prefix.$table_name;


//$wpdb->query("DROP TABLE ".$table_name );

$sql = "CREATE TABLE IF NOT EXISTS $table_name (  
 `id` mediumint(9) NOT NULL AUTO_INCREMENT,
 `date`  varchar(15) ,  
 `user_id` bigint(20),
 UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";



dbDelta($sql);

}
