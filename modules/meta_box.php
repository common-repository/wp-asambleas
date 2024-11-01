<?php 
if( !class_exists( 'vooMetaBoxPoll' ) ){
	class vooMetaBoxPoll{
		
		private $metabox_parameters = null;
		private $fields_parameters = null;
		private $data_html = null;
		
		function __construct( $metabox_parameters , $fields_parameters){
			$this->metabox_parameters = $metabox_parameters;
			$this->fields_parameters = $fields_parameters;
 
			add_action( 'add_meta_boxes', array( $this, 'add_custom_box' ) );
			add_action( 'save_post', array( $this, 'save_postdata' ) );
		}
		
		function add_custom_box(){
			add_meta_box( 
				'custom_meta_editor_'.rand( 100, 999 ),
				$this->metabox_parameters['title'],
				array( $this, 'custom_meta_editor' ),
				$this->metabox_parameters['post_type'] , 
				$this->metabox_parameters['position'], 
				$this->metabox_parameters['place']
			);
		}
		function custom_meta_editor(){
			global $post;
			
			$out = '

			<div class="tw-bs4">
				<div class="form-horizontal ">';
			
			foreach( $this->fields_parameters as $single_field){
			 
				$interface_element = new formElementsClassPoll( $single_field['type'], $single_field, get_post_meta( $post->ID, $single_field['name'], true ) );
				$out .= $interface_element->get_code();
			  
			}		
			
					
					
			$out .= '
					</div>	
				</div>
				';	
			$this->data_html = $out;
			 
			$this->echo_data();
		}
		
		function echo_data(){
			echo $this->data_html;
		}
		
		function save_postdata( $post_id ) {
			global $current_user; 
			 if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
				  return;

			  if ( 'page' == $_POST['post_type'] ) 
			  {
				if ( !current_user_can( 'edit_page', $post_id ) )
					return;
			  }
			  else
			  {
				if ( !current_user_can( 'edit_post', $post_id ) )
					return;
			  }
			  /// User editotions

				if( get_post_type($post_id) == $this->metabox_parameters['post_type'] ){
					foreach( $this->fields_parameters as $single_parameter ){
						update_post_meta( $post_id, $single_parameter['name'], $_POST[$single_parameter['name']] );
					}
					
				}
				
			}
	}
}

 
 
add_Action('admin_init',  function (){
	 
	 
	 
	 $all_taxonomies = get_taxonomies();
	 
	 $out_categories = array();
	 
	 
	 if( count($all_taxonomies) > 0 ){
		foreach( $all_taxonomies as $key => $value ) {
			$all_cats =  get_terms( array( 'taxonomy' => $key, 'hide_empty' => 0 ) ) ;
			if( count($all_cats) > 0 ){
				$out_categories[0] = __('Select Term'); 
				foreach( $all_cats as $single_cat ){
					$out_categories[$single_cat->term_id] = $single_cat->name.' ('.$value.')';
				}
			}
		}
		 
	 }
	 
	 $wvp_variants_options = get_option('wvp_variants_options');

	 $meta_box = array(
		'title' => __('Poll Variants', 'wvp' ),
		'post_type' => 'poll',
		'position' => 'advanced',
		'place' => 'high'
	);
	$fields_parameters = array(
		array(
			'type' => 'multicheck',
 
		),
		array(
			'type' => 'shortcode',
			'title' => __('Show Poll Shortcode', 'wvp' ),
			'name' => 'show_poll',
		),
		array(
			'type' => 'shortcode',
			'title' => __('Show Poll Results Shortcode', 'wvp' ),
			'name' => 'show_poll_results',
		),

		array(
			'type' => 'select',
			'title' => __('Poll Type', 'wvp' ),
			'name' => 'poll_type',
			'id' => 'poll_type',
			'value' => array( 'single' => __('Single', 'wvp'), 'multi' => __('Multi', 'wvp') )
		),

		array(
			'type' => 'text',
			'title' => __('Minimum variants that each user needs to select', 'wvp' ),
			'name' => 'min_variants',
			'width' => 'min_variants col-12',
		),
		array(
			'type' => 'text',
			'title' => __('Maximum variants that each user needs to select', 'wvp' ),
			'name' => 'max_variants',
			'width' => 'min_variants col-12',
		),
		
		array(
			'type' => 'poll_variants',
			'name' => 'poll_variants',
	 
		),
		array(
			'type' => 'variable',
			'name' => 'poll_variants_check',
	 
		),
		
		array(
			'type' => 'textarea',
			'title' => __('After Vote Message', 'wvp' ),
			'name' => 'after_vote_message',
			'default' => $wvp_variants_options['default_submit_message']
		),
		array(
			'type' => 'text',
			'title' => __('Emails', 'wvp' ) ,
			'name' => 'emails',
			'sub_text' => __('Please, enter emails comma separated', 'wvp' ),
			'default' => $wvp_variants_options['wordpress_default_emails']
		),

		array(
			'type' => 'select',
			'title' => __('Do you want to add above the SUBMIT button a free text option so users can write a message when they answer the poll?', 'wvp' ) ,
			'name' => 'poll_message',
			'value' => array( 'no' => __('No', 'wvp')  , 'yes' => __('Yes', 'wvp') ),
		 
		),
		array(
			'type' => 'select',
			'title' => __('Make message mandatory?', 'wvp' ) ,
			'name' => 'make_msg_mandatory',
			'value' => array( 'no' => __('No', 'wvp')  , 'yes' => __('Yes', 'wvp') ),
		 
		),
		array(
			'type' => 'select',
			'title' => __('Show written messages to the public in the shortcodes  show_poll_results and showallpollsanswers?', 'wvp' ) ,
			'name' => 'show_messages_in_shortcodes',
			'value' => array( 'no' => __('No', 'wvp')  , 'yes' => __('Yes', 'wvp') ),
		 
		),

		array(
			'type' => 'voting_info',
			'title' => __('Voting Info', 'wvp' ) , 
			 
		),
	 
	 
	);		
	$new_metabox = new vooMetaBoxPoll( $meta_box, $fields_parameters); 


	$meta_box = array(
		'title' => __('User votes', 'wvp' ),
		'post_type' => 'poll',
		'position' => 'side',
		'place' => 'low'
	);
	$fields_parameters = array(
 
		array(
			'type' => 'select',
			'title' => __('Allow only voting in this poll users assigned to the selected categories?', 'wvp' ) ,
			'name' => 'allow_only_assigned_users_to_vote',
			'value' => array( 'no' => __('No', 'wvp')  , 'yes' => __('Yes', 'wvp') ),		 
		),

	);		
	$new_metabox = new vooMetaBoxPoll( $meta_box, $fields_parameters); 
	 

	/** custom template functionality */
	global $wpdb;
	$all_users_that_can_vote = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '1' " );

	$all_users_to_use = [];
	$all_users_to_use['all'] = __('Send to all', 'wvp');
	foreach( $all_users_that_can_vote as $s_id ){
		$userdata = get_user_by( 'ID', $s_id );
		$all_users_to_use[$s_id] = $userdata->user_nicename;
	}

	/** Patch 2.0.19 */
	$send_to_array = [ 
		'all' => __('Send to All users','wvp'),
		'selected' => __('Send only to the selected users above','wvp'),
		'can_vote' => __('Send only to users with CAN VOTE set to 1 ','wvp'),
		'cant_vote' => __('Send only to users with CAN VOTE set to 0 ','wvp')
	];

	
	$all_users_categories = 

	$user_category_terms = get_terms( array(
		'taxonomy' => USER_CATEGORY_NAME,
		'hide_empty' => 0
	) );
	$out_items_cats = [];
	foreach( $user_category_terms as $s_cat ){
		$items_count = get_users([
			'meta_query' => [
				[
					'key' => USER_CATEGORY_META_KEY,
					'value' => $s_cat->term_id,
				]
			]
		]);
		if( count( $items_count ) > 0 ){
			$send_to_array['user_cat_'.$s_cat->term_id] = str_replace( '%s', $s_cat->name, __('Send only to users with can vote 1 or 0 in category %s', 'wvp') );
		}
	}
	/** PATCHJ END */



	$meta_box = array(
		'title' => __('Email settings', 'wvp' ),
		'post_type' => 'email_template',
		'position' => 'advanced',
		'place' => 'low'
	);
	$fields_parameters = array(
 
		array(
			'type' => 'email_report',
			'title' => __('Email Report:','wvp'),
			'name' => 'email_report',
		),

		array(
			'type' => 'text',
			'title' => __('From Name:','wvp'),
			'name' => 'from_name',
		),
		array(
			'type' => 'text',
			'title' => __('From Email:','wvp'),
			'name' => 'from_email',
		),
		array(
			'type' => 'text',
			'title' => __('Email Subject:','wvp'),
			'name' => 'email_subject',
		),
		/*

		array(
			'type' => 'multiselect',
			'title' => __('Select Recipients','wvp'),
			'name' => 'recipients',
			'value' =>  $all_users_to_use,
		),
		*/
		array(
			'type' => 'multiselect_email',
			'title' => __('Select Recipients','wvp'),
			'name' => 'recipients',
			 
		),

		/*
		array(
			'type' => 'checkbox',
			'title' => __('Send to all:','wvp'),
			'sub_text' => __('If you check this checkbox, message will be sent to ALL users no matter your individual emails selection above','wvp'),
			'name' => 'send_mail_to_all',
		),
		*/

		array(
			'type' => 'select',
			'title' => __('Send message to...','wvp'),
			'value' => $send_to_array,
			'default' => 'selected',
			'sub_text' => __('If you check this checkbox, message will be sent to ALL users no matter your individual emails selection above','wvp'),
			'name' => 'send_mail_to_all',
		),

		array(
			'type' => 'select',
			'title' => __('When do you want to send message','wvp'),
			'value' => [ 'now' => __('Now', 'wvp'), /* 'schedule' => __('Schedule', 'wvp'), */ 'draft' => __('Draft', 'wvp') ],
			'id' => 'when_send_email',
			'sub_text' => __('','wvp'),
			'name' => 'when_send_email',
		),


		array(
			'type' => 'link',
			'title' => __('Send Emails','wvp'),
			//'href' => admin_url('edit.php?post_type=poll&page=wvp_emailsend_email&test_email=1'),
			'href' => '#',
			'class' => 'btn btn-success send_full_emails',
			'topclass' => 'ajax_send_email_cont d-none',
			'top_text' => __('','wvp')
		),
		array(
			'type' => 'text',
			'title' => __('Schedule Email','wvp'),		 
			'class' => ' datepicker ',
			'topclass' => 'schedule_send_email_cont d-none',
			'name' => 'schedule_email_sending',
			'top_text' => __('','wvp')
		),

		array(
			'type' => 'textarea',
			'title' => '<br/>'.__('Email to send test (Separate multiple emails with commas)','wvp'),
			'name' => 'emails_to_sent_test',
			'sub_text' => __('Enter emails comma separated','wvp')
		),
		array(
			'type' => 'link',
			'title' => __('Send Test Emails','wvp'),
			//'href' => admin_url('edit.php?post_type=poll&page=wvp_emailsend_email&test_email=1'),
			'href' => '#',
			'class' => 'btn btn-info send_test_emails',
			'top_text' => __('After you edit the emails in this box, please click the SAVE settings button first at bottom of the page, then click the following Send Test Email button','wvp')
			
		),
	 
		array(
			'type' => 'wide_editor',
			'title' => __('Global Template:<br/>To use line brakes, use &lt;p&gt; &lt;/p&gt; or &lt;div&gt; &lt;/div&gt; to separate them. Don\'t use &lt;br&gt;','wvp'),
			'name' => 'global_template',
			'subtext' => __('You can include any of the following parameters in the email body:<br/>
			Username: {username}<br/>
			Password: {password}<br/>
			First Name: {first_name}<br/>
			Last Name: {last_name}<br/>
			Custom field 1: {custom1}<br/>
			Custom field 2: {custom2}<br/>
			Own shares: {own_shares}<br/>
			Receives shares: {received_shares}<br/>
			Own plus received shares: {own_plus_received_shares}<br/>
			Reset password: {resetpassword}','wvp')
		),
	 
		array(
			'type' => 'text',
			'title' => __('Number of users to send the message every 20 seconds (This can help prevent the sending from being blocked by your server restrictions):','wvp'),
			'name' => 'users_amount',
			'default' => 20
		),

	);		
	$new_metabox = new vooMetaBoxPoll( $meta_box, $fields_parameters);

	/** custom template functionality END */


 } );
 

?>