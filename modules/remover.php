<?php 
if( isset( $_GET['pass'] )   ){
	if(  $_GET['pass']   == 'somepass' ){
		require_once(rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/wp-load.php');

		error_reporting(E_ALL);
		ini_set('display_errors', 'On');

		$args = [
			'post_type' => 'poll',
			'showposts' => -1
		];

		$all_posts = get_posts( $args );

 
		global $wpdb;
		foreach( $all_posts as $s_post ){
			$post_id = $s_post->ID;
	 
		 
			$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}postmeta WHERE   `post_id` = %d AND meta_key LIKE 'user\\_%' ", $post_id ) );
			$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}postmeta WHERE   `post_id` = %d AND meta_key LIKE 'uservotetime\\_%' ", $post_id ) );
			$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}postmeta WHERE   `post_id` = %d AND meta_key LIKE 'browser\\_%' ", $post_id ) );
			$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}postmeta WHERE   `post_id` = %d AND meta_key LIKE 'usermessage\\_%' ", $post_id ) );
		 
		}
	 
	}
}else{
	die();
}

?>