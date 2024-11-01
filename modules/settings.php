<?php 

if( !class_exists('vooSettingsClassPoll') ){
class vooSettingsClassPoll{
	
	var $setttings_parameters;
	var $settings_prefix;
	var $message;
	var $is_form;
	
	function __construct( $prefix ){
		$this->setttings_prefix = $prefix;	
		
		if( isset( $_POST[$this->setttings_prefix.'save_settings_field'] ) ){
			if(  wp_verify_nonce($_POST[$this->setttings_prefix.'save_settings_field'], $this->setttings_prefix.'save_settings_action') ){
				$options = array();
				foreach( $_POST as $key=>$value ){
					$options[$key] = $value ;
				}
			 
				update_option( $this->setttings_prefix.'_options', $options );
	 
				if( $_GET['page'] == 'wvp_emailsend_email' && 1 == 2 ){

					$sent_amount = 0;
					$report = get_option('email_sending_report');
					if( count($report) > 0 ){
						foreach( $report as $user_id => $result ){
							$userdata = get_user_by('ID', $user_id);
							if( $result ){
								$sent_amount++;
							}else{
								
							}
							
						}
					}

					$this->message = '<div class="alert alert-success">'.$sent_amount.' '.__('Emails sent', 'wvp' ).'</div>';
				}else{
					$this->message = '<div class="alert alert-success">'.__('Settings saved', 'wvp' ).'</div>';
				}
				
				
				
			}
		}

		/** V1.15 message patch */
		if( isset( $_GET['msg'] ) ){
			if( $_GET['msg'] == '11'){
				$this->message = '<div class="alert alert-success">'.__('Polls created', 'wvp' ).'</div>';
			}
			if( $_GET['msg'] == '22'){
				$this->message = '<div class="alert alert-success">'.__('Polls imported', 'wvp' ).'</div>';
			}
			if( $_GET['msg'] == 'svg_import_success'){
				$this->message = '<div class="alert alert-success">'.str_replace( '{XX}', (int)$_GET['count'], __('A total of {XX} users are in the preapproved list', 'wvp' ) ).'</div>';
			}
			if( $_GET['msg'] == 'svg_import_error'){
				$this->message = '<div class="alert alert-danger">'.__('Error importing. Try again pls.', 'wvp' ).'</div>';
			}
		}
		if( isset( $_GET['max_amount_reached'] ) ){
			if( $_GET['max_amount_reached'] == '1'){
				$this->message = '<div class="alert alert-success">'.__('This user has received the maximum number of proxies allowed. Please modify that number above or remove some of his proxies to be able to assign this new proxy', $this->setttings_prefix ).'</div>';
			}
			
		}
		/** V1.15 message patch END */
		
	}
	
	function get_setting( $setting_name ){
		$inner_option = get_option( $this->setttings_prefix.'_options');
		return $inner_option[$setting_name];
	}
	
	function create_menu( $parameters ){
		$this->setttings_parameters = $parameters;		
			
		add_action('admin_menu', array( $this, 'add_menu_item'),10 );
		
	}
	
	 
	
	
	function add_menu_item(){
		
		foreach( $this->setttings_parameters as $single_option ){
			if( isset($single_option['is_form']) ){
				$this->is_form = true;
			}else{
				$this->is_form = false;
			}
			if( $single_option['type'] == 'menu' ){
				add_menu_page(  			 
				$single_option['page_title'], 
				$single_option['menu_title'], 
				$single_option['capability'], 
				$this->setttings_prefix.$single_option['menu_slug'], 
				array( $this, 'show_settings' ),
				$single_option['icon']
				);
			}
			if( $single_option['type'] == 'submenu' ){
				add_submenu_page(  
				$single_option['parent_slug'],  
				$single_option['page_title'], 
				$single_option['menu_title'], 
				$single_option['capability'], 
				$this->setttings_prefix.$single_option['menu_slug'], 
				array( $this, 'show_settings' ) 
				);
			}
			if( $single_option['type'] == 'option' ){
				add_options_page(  				  
				$single_option['page_title'], 
				$single_option['menu_title'], 
				$single_option['capability'], 
				$this->setttings_prefix.$single_option['menu_slug'], 
				array( $this, 'show_settings' ) 
				);
			}
		}
		 
	}
	
	function show_settings(){
		?>
		<div class="wrap tw-bs4">
		
		
		
		<h2><?php 
	 
		if( $this->setttings_parameters[0]['title'] != '' ){
			echo $this->setttings_parameters[0]['title'];
		}else{
			_e('Settings', 'wvp'); 
		}
		if( $this->setttings_parameters[0]['subtitle'] != '' ){
			echo '<p>'.$this->setttings_parameters[0]['subtitle'].'</p>';
		} 
		
		
		?>
		</h2>
		<hr/>
		<?php 
			echo $this->message;
		?>
		<?php if( $this->is_form ): ?>
		<form class="form-horizontal" method="post" action="" enctype="multipart/form-data" >
		<?php endif; ?>
		<?php 
		wp_nonce_field( $this->setttings_prefix.'save_settings_action', $this->setttings_prefix.'save_settings_field'  );  
		$config = get_option( $this->setttings_prefix.'_options'); 
		 
		?>  
		<fieldset>

			<?php 
		foreach( $this->setttings_parameters as $single_page ){	
			 
			foreach( $single_page['parameters'] as $key=>$value ){	
 
				$interface_element = new formElementsClassPoll( $value['type'], $value, $config[$value['name']] );
				echo $interface_element->get_code();	 
			 
			}
		}
			
			?>

				
				   
				</fieldset>  
		<?php if( $this->is_form ): ?>
		</form>
		<?php endif; ?>
		</div>
		<?php
	}
}	
}	
 
	
	
add_Action('init',  function (){
	$output_options = get_option('wvp_extra_options');
	if( !isset($output_options['lite_mode']) ){
		$output_options['lite_mode'] = '';
	}

	$config_big = 
	array(

		array(
			'type' => 'submenu',
			'is_form' => true,
			'parent_slug' => 'edit.php?post_type=poll',
			'page_title' => __('Import Polls', 'wvp'),
			'menu_title' => __('Import Polls', 'wvp'),
			'title' => __('Import Polls', 'wvp'),
			'capability' => 'edit_published_posts',
			'menu_slug' => 'import_polls',

			'parameters' => array(
		 
				array(
					'type' => 'textarea',
					'rows' => 10,
					'title' => __('Import questions. Write or paste questions you want to import. Write each question in a new line. All questions will get your default variants','wvp'),
					'name' => 'add_questions',
				),
				array(
//CS					'type' => 'save',
//CS					'title' => __('Submit','wvp'),
					
				),
				array(
					'type' => 'separator',
					 'title' => '&nbsp;'
					
				),
				array(
					'type' => 'separator',
					 'title' => '&nbsp;'
					
				),

				array(
					'type' => 'file',
					'title' => __('Import polls from CSV file','wvp'),
					'name' => 'csv_questions_import',
					'sub_text' => str_replace( '%s', plugins_url('/import_polls.csv', __FILE__ ),  __('Here you can create polls by importing them from a CSV file.<br/>
					The file must have these fields: Title, Poll type, Minimum variants, Maximum variants,  Variant 1., Variant 2, ... , Variant N<br/>
					Poll type: 1:  Single, 2: Muti <br/>
					<a href="%s" download>(Click here to download a sample csv file)</a>', 'wvp') )
				),
				
				array(
//CS					'type' => 'save',
//CS				'title' => __('Submit','wvp'),
					
				),
				
				 
			)
		)
	); 
	global $settings;

	$settings = new vooSettingsClassPoll( 'wvp_import' ); 
	$settings->create_menu(  $config_big   );

	$config_big = 
	array(

		array(
			'type' => 'submenu',
			'is_form' => true,
			'parent_slug' => 'edit.php?post_type=poll',
			'page_title' => __('Quorum Settings', 'wvp'),
			'menu_title' => __('Quorum Settings', 'wvp'),
			'capability' => 'edit_published_posts',
			'menu_slug' => 'quorum_settings',

			'parameters' => array(
		 
				array(
					'type' => 'select',
					'id' => 'user_online_lifetime_type',
					'title' => __('Choose the method you want to measure user online lifetime:','wvp'),
					'name' => 'user_online_lifetime_type',
					'value' => [ 
						'prev_online_minutes' => __( 'Previous online minutes', 'wvp' ),
						'from_timestamp' => __( 'From this specific date-time (Logged since this moment, for the rest of the current day): ', 'wvp' ) 
						]
				),
				array(
					'type' => 'text',
					'title' => __('Previous online minutes','wvp'),
					'name' => 'prev_online_minutes',
					//'name' => 'user_online_lifetime',
					'topclass' => 'd-none prev_online_minutes'
				),
				array(
					'type' => 'text',
					'title' => __('From Timestamp','wvp'),
					'name' => 'from_timestamp',
					'topclass' => 'd-none from_timestamp  ',
					'class' => ' datepicker'
					//'name' => 'user_online_lifetime',
				),
				array(
					'type' => 'text',
					'title' => __('Organization total shares','wvp'),
					'name' => 'organization_total_shares',
				),
				array(
					'type' => 'text',
					'title' => __('Minutes range to save quorum data','wvp'),
					'name' => 'minutes_to_save_quorum_data',
					'disabled' => (  $output_options['lite_mode'] == 'yes' ? true : false  )
				),
				array(
					'type' => 'select',
					'title' => __('Minutes range to save NAMES of logged users:','wvp'),
					'name' => 'minutes_to_save_names',
					'value' => array( 
						'1' => '1',
						'5' => '5',
						'10' => '10',
						'20' => '20',
						'30' => '30',
						'60' => '60',
					),
					'default' => '10'
				),
				/*
				array(
					'type' => 'multiselect',
					'title' => __('What items do you want to show in quorm table to users?','wvp'),
					'name' => 'quorum_table',
					'value' => array( 
						'total_users' => __('Total current users online at this moment','wvp' ),
						'total_online_shares' => __('Total online shares at this moment','wvp' ),
						'percent_of_shares' => __('% of shares out of the total for our organization, online at this moment','wvp' ),
						'org_total_shares' => __('Organization total shares','wvp' ),
					),
					'style' => 'width:100%;',
					'class' => 'col-12'
				),
				*/
				array(
					'type' => 'select',
					'title' => __('Do you want to show a list of connected users when showing the Quorum? ','wvp'),
					'name' => 'show_online_users',
					'value' => array( 
						'no' => __('No','wvp' ),
						'yes' => __('Yes','wvp' ),
						)
				),
				array(
					'type' => 'select',
					'title' => __('Do you want to show a list of non-connected users when showing the Quorum? ','wvp'),
					'name' => 'show_offline_users',
					'value' => array( 
						'no' => __('No','wvp' ),
						'yes' => __('Yes','wvp' ),
						)
				),

				array(
					'type' => 'select',
					'title' => __('Do you want to disable login in the website to all users that have  canvote = 0 ?','wvp'),
					'name' => 'disable_login_can_vote_0',
					'value' => array( 
						'no' => __('No','wvp' ),
						'yes' => __('Yes','wvp' ),
					),
					'default' => 'no'
				),
				array(
					'type' => 'select',
					'title' => __('Allow only a single session per user in the site?','wvp'),
					'name' => 'allow_only_single_session',
					'value' => array( 
						'no' => __('No','wvp' ),
						'yes' => __('Yes','wvp' ),
					),
					'default' => 'no'
				),

				array(
					'type' => 'quorum_config',
					'title' => __('Quorum Table Settings','wvp'),
					'name' => 'quorum_settings',
				),

				array(
					'type' => 'quorum_config_table',
					'title' => __('What items do you want to show in quorum table to users?','wvp'),
					'name' => 'quorum_settings_table',
				),
				array(
					'type' => 'text',
					'title' => __('Enter the number of decimals to use in the following values of front-end pages with [quorum] , [showallpollsanswers] and [show_poll_results id=\'XX\'] shortcodes : ','wvp'),
					'name' => 'decimal_amount',					
					'default' => __('2','wvp'),
				),
				array(
					'type' => 'select',
					'title' => __('Show above decimals in front-end for these values?   (If NO is selected, values will be shown with no decimals. Only with thousands separators): <br/><br/>
						From QUORUM SETTINGS<br/>
							&nbsp;&nbsp;&nbsp;Organization total shares <br/>
							&nbsp;&nbsp;&nbsp;Total online shares at this moment<br/>
							&nbsp;&nbsp;&nbsp;Total online own shares + received shares in proxies at this moment<br/>
							&nbsp;&nbsp;&nbsp;Total A + B<br/>
							<br/>
						From EXTRA SETTINGS:<br/>
						&nbsp;&nbsp;&nbsp;Total shares in this answer<br/>
						&nbsp;&nbsp;&nbsp;Total own shares + shares from proxies of users that voted in this poll<br/>
						&nbsp;&nbsp;&nbsp;Shares that didn’t vote<br/>
						&nbsp;&nbsp;&nbsp;Quorum at the closing time minus shares that voted<br/>
						','wvp'),
					'name' => 'show_decimal_amount',					
					'value' => array( 
						'no' => __('No','wvp' ),
						'yes' => __('Yes','wvp' ),
					),
					'default' => 'no'
				),
				array(
					'type' => 'text',
					'title' => __('User List Title','wvp'),
					'name' => 'user_list_title',
					'default' => __('Lista de usuarios conectados en este momento','wvp'),
				),
				array(
					'type' => 'text',
					'title' => __('Offline User List Title','wvp'),
					'name' => 'offline_user_list_title',
					'default' => __('Lista de usuarios desconectados en este momento','wvp'),
				),
				array(
					'type' => 'data',
					'name' => 'show_item',
				),
				array(
					'type' => 'data',
					'name' => 'block_value',
				),
				array(
					'type' => 'logout_button',
					'name' => 'logout_button',
					'title' => __('Logout all users now','wvp'),
					'href' => admin_url( 'edit.php?post_type=poll&page=wvpquorum_settings&logoutall=1' )
				),
				array(
					'type' => 'total_current_shares',
					'title' => __('Total current shares of users in the database:','wvp'),
				),
				array(
					'type' => 'save',
					'title' => __('Save','wvp'),
					
				),
				
				 
			)
		)
	); 
	global $settings;

	$settings = new vooSettingsClassPoll( 'wvp' ); 
	$settings->create_menu(  $config_big   );


	$config_big = 
	array(

		array(
			'type' => 'submenu',
			//'parent_slug' => 'edit.php?post_type=poll',
			'parent_slug' => false,
			'page_title' => __('Data Log', 'wvp'),
			'menu_title' => __('Data Log', 'wvp'),
			'capability' => 'edit_published_posts',
			'menu_slug' => 'data_log',

			'parameters' => array(
		 
				array(
					'type' => 'data_log',
					'title' => __('User Online Lifetime','wvp'),
					'name' => 'user_online_lifetime',
				),
				 
				
				 
			)
		)
	); 
	global $settings;

 
	$settings = new vooSettingsClassPoll( 'wvp_data' ); 
	$settings->create_menu(  $config_big   );

	// export polls results
	$config_big = 
	array(

		array(
			'type' => 'submenu',
			//'parent_slug' => 'edit.php?post_type=poll',
			'parent_slug' => false,
			'page_title' => __('Export all poll results', 'wvp'),
			'menu_title' => __('Export all poll results', 'wvp'),
			'capability' => 'edit_published_posts',
			'menu_slug' => 'export_poll_results',

			'parameters' => array(
		 
				array(
					'type' => 'export_poll_results',
					'title' => __('export_poll_results','wvp'),
					'name' => 'export_poll_results',
				),
			
				
				 
			)
		)
	); 
	global $settings;

 
	$settings = new vooSettingsClassPoll( 'wvp_data' ); 
	$settings->create_menu(  $config_big   );

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

	// export polls results
	$config_big = 
	array(

		array(
			'type' => 'submenu',
			'parent_slug' => 'edit.php?post_type=poll',
			'page_title' => __('Send email', 'wvp'),
			'menu_title' => __('Send email', 'wvp'),
			'capability' => 'edit_published_posts',
			'menu_slug' => 'send_email',
			'is_form' => true,
			'parameters' => array(
		 
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
					'type' => 'link',
					'title' => __('Send Emails','wvp'),
					//'href' => admin_url('edit.php?post_type=poll&page=wvp_emailsend_email&test_email=1'),
					'href' => '#',
					'class' => 'btn btn-success send_full_emails',
					'top_text' => __('After you change the selected users, please click the SAVE settings button first at bottom of the page, then click the following Send Email button','wvp')
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
					'type' => 'select',
					'title' => '<br/>'.__('Choose the template content you want to use for sending the message:','wvp'),
					'value' =>  array( 'global' => __('Global', 'wvp'), 'custom' => __('Custom','wvp') ),
					'name' => 'tempalte_type',
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
					'type' => 'wide_editor',
					'title' => __('Custom Template:<br/>To use line brakes, use &lt;p&gt; &lt;/p&gt; or &lt;div&gt; &lt;/div&gt; to separate them. Don\'t use &lt;br&gt;','wvp'),
					'name' => 'custom_template',
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
				array(
					'type' => 'save',
					'title' => __('Save','wvp'),
					'name' => 'send_message',
				),
				 
			)
		)
	); 
	global $settings;

 
	//$settings = new vooSettingsClassPoll( 'wvp_email' ); 
	//$settings->create_menu(  $config_big   );

	$config_big = 
	array(

		array(
			'type' => 'submenu',
			//'parent_slug' => 'edit.php?post_type=poll',
			'parent_slug' => false,
			'page_title' => __('Quorum Users', 'wvp'),
			'menu_title' => __('Quorum Users', 'wvp'),
			'capability' => 'edit_published_posts',
			'menu_slug' => 'quorum_users',
			'is_form' => false,
			'parameters' => array(
		 
				array(
					'type' => 'quorum_users',
					'title' => __('Quorum Users','wvp'),
					'name' => 'quorum_users',
				),
				 
				
				 
			)
		)
	); 
	global $settings;

 
	$settings = new vooSettingsClassPoll( 'wvp_data' ); 
	$settings->create_menu(  $config_big   );

	$config_big = 
	array(

		array(
			'type' => 'submenu',
			//'parent_slug' => 'edit.php?post_type=poll',
			'parent_slug' => false,
			'page_title' => __('Quorum Users 2', 'wvp'),
			'menu_title' => __('Quorum Users 2', 'wvp'),
			'capability' => 'edit_published_posts',
			'menu_slug' => 'quorum_users2',
			'is_form' => false,
			'parameters' => array(
		 
				array(
					'type' => 'quorum_users2',
					'title' => __('Quorum Users','wvp'),
					'name' => 'quorum_users2',
				),
				 
				
				 
			)
		)
	); 
	global $settings;

 
	$settings = new vooSettingsClassPoll( 'wvp_data' ); 
	$settings->create_menu(  $config_big   );



	/** Email open report */
	$config_big = 
	array(

		array(
			'type' => 'submenu',
			//'parent_slug' => 'edit.php?post_type=poll',
			'parent_slug' => false,
			'page_title' => __('Email Open Report', 'wvp'),
			'title' => __('Email Tracking', 'wvp'),
			'subtitle' => __('The IPs shown, may not be the real ones, due to email servers restrictions and users privacy.', 'wvp'),
			'menu_title' => __('Email Open Report', 'wvp'),
			'capability' => 'edit_published_posts',
			'menu_slug' => 'email_open_report',
			'is_form' => false,
			'parameters' => array(
		 
				array(
					'type' => 'email_open_report',
					'title' => __('Email Open Report','wvp'),
					'name' => 'email_open_report',
				),
				 
				
				 
			)
		)
	); 
	global $settings;

 
	$settings = new vooSettingsClassPoll( 'wvp_data' ); 
	$settings->create_menu(  $config_big   );

	/** Email open report END */

	$config_big = 
	array(

		array(
			'type' => 'submenu',
			//'parent_slug' => 'edit.php?post_type=poll',
			'parent_slug' => false,
			'page_title' => __('Online Users Log', 'wvp'),
			'menu_title' => __('Online Users Log', 'wvp'),
			'capability' => 'edit_published_posts',
			'menu_slug' => '_online_user_log',

			'parameters' => array(
		 
				array(
					'type' => 'online_user_log',
					'title' => __('List of logged users','wvp'),
					'name' => 'online_user_log',
				),
				 
				
				 
			)
		)
	); 
	$settings = new vooSettingsClassPoll( 'wvp_data' ); 
	$settings->create_menu(  $config_big   );

	$config_big = 
	array(

		array(
			'type' => 'submenu',
			//'parent_slug' => 'edit.php?post_type=poll',
			'parent_slug' => false,
			'page_title' => __('Online Users Log 2', 'wvp'),
			'menu_title' => __('Online Users Log 2', 'wvp'),
			'capability' => 'edit_published_posts',
			'menu_slug' => '_online_user_log2',

			'parameters' => array(
		 
				array(
					'type' => 'online_user_log2',
					'title' => __('List of logged users','wvp'),
					'name' => 'online_user_log2',
				),
				 
				
				 
			)
		)
	); 
	$settings = new vooSettingsClassPoll( 'wvp_data' ); 
	$settings->create_menu(  $config_big   );

	// new reporting 1.13
	$config_big = 
	array(

		array(
			'type' => 'submenu',
			'parent_slug' => 'edit.php?post_type=poll',
			'page_title' => __('Reports', 'wvp'),
			'menu_title' => __('Reports', 'wvp'),
			'capability' => 'edit_published_posts',
			'menu_slug' => '_reports',

			'parameters' => array(
		 
				array(
					'type' => 'reporting',
					'title' => __('List of logged users','wvp'),
					'name' => 'online_user_log',
				),
				 
				
				 
			)
		)
	); 
	$settings = new vooSettingsClassPoll( 'wvp_data' ); 
	$settings->create_menu(  $config_big   );


	// get all users
	global $wpdb;
	$all_users = $wpdb->get_results("SELECT *   FROM {$wpdb->users}  " );
 
	$user_list = [];
	foreach( $all_users as $s_user ){
		$user_name = [];
		$user_name[] = $s_user->user_login;
		if( isset( $s_user->first_name ) ){
			$user_name[] = $s_user->first_name;
		}
		if( isset( $s_user->last_name ) ){
			$user_name[] = $s_user->last_name;
		}
		$user_list[$s_user->ID] = implode( ' ', $user_name );
	}

	$config_big = 
	array(

		array(
			'type' => 'submenu',
			'parent_slug' => 'edit.php?post_type=poll',
			'page_title' => __('Proxy Admin', 'wvp'),
			'menu_title' => __('Proxy Admin', 'wvp'),
			'capability' => 'edit_published_posts',
			'menu_slug' => 'proxy_admin',
			'is_form' => true,
			'parameters' => array(
		 
	 
				array(
					'type' => 'text',
					'title' => __('Admin emails where to send proxy assignment/rejection/retirement notifications (Separate with commas):','wvp'),
					'name' => 'admin_emails',
					'sub_text' => '',
				),
				array(
					'type' => 'multiselect',
					'title' => __('Users that can’t receive proxies from other users','wvp'),
					'name' => 'users_cant_recieve_proxies',
					'value' => $user_list,
					'sub_text' => '',
				),
				array(
					'type' => 'text',
					'title' => __('Maximum number of proxies that a single user may receive','wvp'),
					'name' => 'max_proxy_amount',
					'sub_text' => '',
				),
				 
				array(
					'type' => 'save',
					'title' => __('Save','wvp'),
	 
				),

				array(
					'type' => 'form_end',
					
	 
				),
				array(
					'type' => 'separator',
					'title' => '',
					 
				),
				array(
					'type' => 'proxy_admin',
					'title' => __('User Online Lifetime','wvp'),
					'name' => 'user_online_lifetime',
				),


				
				 
			)
		)
	); 
	global $settings;

 
	$settings = new vooSettingsClassPoll( 'wvp_data' ); 
	$settings->create_menu(  $config_big   );


	$config_big = 
	array(

		array(
			'type' => 'submenu',
			'parent_slug' => 'edit.php?post_type=poll',
			'page_title' => __('Extra Settings', 'wvp'),
			'menu_title' => __('Extra Settings', 'wvp'),
			'capability' => 'edit_published_posts',
			'menu_slug' => 'settings',
			'is_form' => true,
			'parameters' => array(
		 
				array(
					'type' => 'shortcode_config',
					'title' => __('Poll Answers Settings','wvp'),
					'name' => 'poll_settings',
				),

				array(
					'type' => 'data',
					'name' => 'show_item',
				),

				array(
					'type' => 'data',
					'name' => 'block_value',
				),

				array(
					'type' => 'select',
					'title' => __('Do you want to include in the email body the answers given by users?','wvp'),
					'name' => 'include_answers',
					'value' => array( 'yes' => __('Yes','wvp'), 'no' => __('No','wvp') )
				),

				array(
					'type' => 'poll_open_stats',
					'title' => __('Poll data settings:','wvp'),
					'name' => 'poll_open_stats',
				),

				array(
					'type' => 'vote_users_table',
					'title' => __('Vote Users Table settings:','wvp'),
					'name' => 'vote_users_table',
				),


				array(
					'type' => 'select',
					'title' => __('Enable lite mode?','wvp'),
					'name' => 'lite_mode',
					'value' => array( 'no' => __('No','wvp'), 'yes' => __('Yes','wvp') ),
					'subtext' => __('If you enable this option, when you edit or create a poll, the lists of users that voted and have not voted, will not be shown. Names of users in Quorum will not be saved.', 'wvp')
				),


				array(
					'type' => 'select',
					'title' => __('Enable POLLS WITH TIMER?','wvp'),
					'name' => 'enable_timer',
					'value' => array( 'no' => __('No','wvp'), 'yes' => __('Yes','wvp') ),
					'subtext' => __('If you enable this option, when you edit or create a poll, the lists of users that voted and have not voted, will not be shown. Names of users in Quorum will not be saved.', 'wvp')
				),
				array(
					'type' => 'text',
					'title' => __('Interval (sec)','wvp'),
					'name' => 'interval_in_sec',
					'subtext' => __(' ', 'wvp')
				),
				
				array(
					'type' => 'save',
					'title' => __('Save','wvp'),
				),
				 
			)
		)
	); 
	global $settings;

 
	$settings = new vooSettingsClassPoll( 'wvp_extra' ); 
	$settings->create_menu(  $config_big   );

	$config_big = 
	array(

		array(
			'type' => 'submenu',
			'parent_slug' => 'edit.php?post_type=poll',
			'page_title' => __('Variants Settings', 'wvp'),
			'menu_title' => __('Variants Settings', 'wvp'),
			'capability' => 'edit_published_posts',
			'menu_slug' => 'settings',
			'is_form' => true,
			'parameters' => array(
		 
				array(
					'type' => 'textarea',
					'title' => __('Default Answer Variants','wvp'),
					'name' => 'default_answer_variants',
					'style' => 'height:300px;',
					'default' => "Sí\nNo\nVoto en blanco\nMe abstengo",
					'subtext' => __('Please, enter default variants one per line.', 'wvp'),
				),

				array(
					'type' => 'text',
					'title' => __('Default text to show in every poll after a user  submits his answer: ','wvp'),
					'name' => 'default_submit_message',
				 
					'default' => "Su voto ha sido registrado.",
					'subtext' => __('', 'wvp'),
				),


				array(
					'type' => 'text',
					'title' => __('Enter Default emails where you want to send poll answers to be used when creating new polls (separate using commas)','wvp'),
					'name' => 'wordpress_default_emails',
					'id' => 'wordpress_default_emails',
					'style' => 'height:300px;',
					'default' => "",
					'subtext' => __('', 'wvp'),
				),

				array(
					'type' => 'save',
					'id' => 'submit_default_settings',
					'title' => __('Save','wvp'),
				),
				 
			)
		)
	); 
	global $settings;

 
	$settings = new vooSettingsClassPoll( 'wvp_variants' ); 
	$settings->create_menu(  $config_big   );

	/**
	 * reg form
	 */
	$config_big = 
	array(

		array(
			'type' => 'submenu',
			'parent_slug' => 'edit.php?post_type=poll',
			'page_title' => __('Registration form for pre-approved users', 'wvp'),
			'title' => __('Registration form for pre-approved users', 'wvp'),
			'subtitle' => __('This allows you to include a form in a page to allow users to register and automatically be accepted If you have previously added them in this page. Registration for all other users will not be accepted.  Just put the shortcode [registration_form] in the page where you want to show the form.', 'wvp'),
			'menu_title' => __('Registration form for pre-approved users', 'wvp'),
			'capability' => 'edit_published_posts',
			'menu_slug' => 'settings',
			'is_form' => true,
			'parameters' => array(
		 
				array(
					'type' => 'settings_break',
					'title' => __('Form fields','wvp'),
					'subtitle' => __('Select the fields you want to show in the form where the [registration_form] shortcode is placed:','wvp'),
				),

				array(
					'type' => 'reg_form_settings',
					'name' => 'reg_form_settings',
				),

	 
				array(
					'type' => 'text',
					'title' => __('File attachments extensions allowed:','wvp'),
					'name' => 'attach_extensions',
					'id' => 'attach_extensions',
					'style' => ' ',
					'default' => "jpg, pdf, png, zip, doc, docx",
					'subtext' => __('', 'wvp'),
				),
				array(
					'type' => 'text',
					'title' => __('File attachments max size allowed in Mb:','wvp'),
					'name' => 'max_size_allowed',
					'id' => 'max_size_allowed',
					'style' => ' ',
					'default' => "5",
					'subtext' => __('', 'wvp'),
				),

				array(
					'type' => 'save',
					'id' => '',
					'title' => __('Save','wvp'),
				),


				array(
					'type' => 'settings_break',
					'title' => __('Pre-approved users','wvp'),
				),

				array(
					'type' => 'file',
					'title' => __('CSV file with users','wvp'),
					'name' => 'csv_file_for_users',
					'id' => 'csv_file_for_users',
					'style' => ' ',
					'default' => "",
					'subtext' => str_replace( '%s', get_option('home').'?action=check_preexisted_users', __('Please, select CSV file with user data, each user in separate line, comma separatedю You can check currect list of users <a target="_blank" href="%s">here</a>', 'wvp') ),
				),

				array(
					'type' => 'save',
					'id' => '',
					'title' => __('Upload','wvp'),
				),

				array(
					'type' => 'settings_break',
					'title' => __('Settings','wvp'),
				),
				 

				array(
					'type' => 'select',
					'title' => __('Need to validate both EMAIL and USERNAME?','wvp'),
					'name' => 'validate_email_and_username',
					'id' => 'validate_email_and_username',
					'style' => ' ',
					'default' => "",
					'value' => [
						'yes' => __('Yes', 'wvp'),
						'no' => __('No', 'wvp')
					],
					'subtext' => __('Hint: If marked YES, to approve the user, he needs to enter both the EMAIL and USERNAME  as were entered by admin.  If only one of both is entered OK, Please this will be shown to the user "Only one field of  your USERNAME/EMAIL was found in the system. Both your username and email must be the ones we have in our records to approve your registration.. Please review and try again. If you think this is an error, please contact support”.   If marked NO, user will be approved if he enters either the USERNAME or the EMAIL of one preapproved user in the previous field.', 'wvp'),
				),

				array(
					'type' => 'text',
					'title' => __('Confirmation message to show to the user if he was found in the pre-approved list when he fills the registration form:','wvp'),
					'name' => 'confirmation_message',
					'id' => 'confirmation_message',
					'style' => ' ',
					'default' => __("You were found in the system and your registration was successful", 'wvp'),
					'subtext' => __('', 'wvp'),
				),
				array(
					'type' => 'text',
					'title' => __('Mesage to show to the user if he was already approved or registered:','wvp'),
					'name' => 'user_approved_message',
					'id' => '',
					'style' => ' ',
					'default' => __("Error: A user with the data entered is already in the system, so you can’t register again", 'wvp'),
					'subtext' => __('', 'wvp'),
				),
				array(
					'type' => 'text',
					'title' => __('Message to show to the user if he is not found in the pre-approved list: ','wvp'),
					'name' => 'user_not_found_message',
					'id' => '',
					'style' => ' ',
					'default' => __("You were not found in the system, so can’t be registered. If you think this is an error, please contact support", 'wvp'),
					'subtext' => __('', 'wvp'),
				),
				array(
					'type' => 'text',
					'title' => __('Form submit button label: ','wvp'),
					'name' => 'form_submit_label',
					'id' => '',
					'style' => ' ',
					'default' => __("Submit", 'wvp'),
					'subtext' => __('', 'wvp'),
				),
				array(
					'type' => 'text',
					'title' => __('Emails to send notifications of  registrations, separated by comma:','wvp'),
					'name' => 'form_submission_emails',
					'id' => '',
					'style' => ' ',
					'default' => __("", 'wvp'),
					'subtext' => __('', 'wvp'),
				),
				array(
					'type' => 'button',
					'title' => __('Click here to delete all attachments received in registration forms','wvp'),
					'name' => 'delete_all_submission_attachments',
					'id' => 'delete_all_submission_attachments',
					'style' => ' ',
					'href' => admin_url( 'edit.php?post_type=poll&page=wvp_regformsettings&wvp_action=drop_csv_users_files' ),
					'default' => __("", 'wvp'),
					'subtext' => __('', 'wvp'),
				),

				array(
					'type' => 'select',
					'title' => __('Do you want to send a confirmation email to users when they register?','wvp'),
					'name' => 'send_confirmation_email',
					'id' => 'send_confirmation_email',
					'style' => ' ',
					'default' => "",
					'value' => [
						'yes' => __('Yes', 'wvp'),
						'no' => __('No', 'wvp')
					],
					'subtext' => __('', 'wvp'),
				),
				array(
					'type' => 'text',
					'title' => __('Email from NAME:','wvp'),
					'name' => 'email_from_name',
					'id' => '',
					'style' => ' ',
					'default' => __("", 'wvp'),
					'subtext' => __('', 'wvp'),
				),
				array(
					'type' => 'text',
					'title' => __('Email from EMAIL:','wvp'),
					'name' => 'email_from_email',
					'id' => '',
					'style' => ' ',
					'default' => __("", 'wvp'),
					'subtext' => __('', 'wvp'),
				),
				array(
					'type' => 'text',
					'title' => __('Email Subject:','wvp'),
					'name' => 'email_subject',
					'id' => '',
					'style' => ' ',
					'default' => __("", 'wvp'),
					'subtext' => __('', 'wvp'),
				),
				array(
					'type' => 'wide_editor',
					'title' => __('Message to send','wvp'),
					'name' => 'message_to_send',
					'id' => '',
					'style' => ' ',
					'default' => __("", 'wvp'),
					'subtext' => __('Allowed variables:<br/> 
					Username: {username}<br/>
					First Name: {first_name}<br/>
					Last Name: {last_name}
					', 'wvp'),
				),

				array(
					'type' => 'save',
					'id' => '',
					'title' => __('Save','wvp'),
				),
			)
		)
	); 
	global $settings;

 
	$settings = new vooSettingsClassPoll( 'wvp_regform' ); 
	$settings->create_menu(  $config_big   );
 
	
	$config_big = 
	array(

		array(
			'type' => 'submenu',
			'title' => __('Help', 'wvp'),
			'parent_slug' => 'edit.php?post_type=poll',
			'page_title' => __('Help', 'wvp'),
			'menu_title' => __('Help', 'wvp'),
			'capability' => 'edit_published_posts',
			'menu_slug' => 'help',

			'parameters' => array(
		 
				array(
					'type' => 'help_block',
				)
					
				
				 
			)
		)
	); 
	global $settings;

 
	$settings = new vooSettingsClassPoll( 'wvp_data' ); 
	$settings->create_menu(  $config_big   );



	// email log
	$config_big = 
	array(

		array(
			'type' => 'submenu',
			//'parent_slug' => 'edit.php?post_type=poll',
			'page_title' => __('Email Log', 'wvp'),
			'menu_title' => __('Email Log', 'wvp'),
			'capability' => 'edit_published_posts',
			'menu_slug' => 'email_log',
			'is_form' => false,
			'parameters' => array(
		 
				array(
					'type' => 'email_log',
					'title' => __('Email Log','wvp'),
					'name' => 'email_log',
				),
				 
				
				 
			)
		)
	); 
	global $settings;

 
	$settings = new vooSettingsClassPoll( 'wvp_data' ); 
	$settings->create_menu(  $config_big   );

	//. export voting results
	// email log
	$config_big = 
	array(

		array(
			'type' => 'submenu',
			//'parent_slug' => 'edit.php?post_type=poll',
			'parent_slug' => false,
			'page_title' => __('Export single CSV with results summary', 'wvp'),
			'menu_title' => __('Export single CSV with results summary', 'wvp'),
			'capability' => 'edit_published_posts',
			'menu_slug' => 'export_poll_results_csv',
			'is_form' => true,
			'parameters' => array(
		 
				array(
					'type' => 'export_poll_results_with_csv',
					'title' => __('export_poll_results_with_pdf','wvp'),
					'name' => 'export_poll_results_with_csv',
				),
				 
				
				 
			)
		)
	); 
	$settings = new vooSettingsClassPoll( 'wvp_data' ); 
	$settings->create_menu(  $config_big   );


	//. export voting results PDF
	// email log
	$config_big = 
	array(

		array(
			'type' => 'submenu',
			//'parent_slug' => 'edit.php?post_type=poll',
			'parent_slug' => false,
			'page_title' => __('Export PDF with all results', 'wvp'),
			'menu_title' => __('Export PDF with all results', 'wvp'),
			'capability' => 'edit_published_posts',
			'menu_slug' => 'export_poll_results_with_pdf',
			'is_form' => true,
			'parameters' => array(
		 
				array(
					'type' => 'export_poll_results_with_pdf',
					'title' => __('export_poll_results_with_pdf','wvp'),
					'name' => 'export_poll_results_with_pdf',
				),
				 
				
				 
			)
		)
	); 
	$settings = new vooSettingsClassPoll( 'wvp_data' ); 
	$settings->create_menu(  $config_big   );


	//. relogin functionality
	$config_big = 
	array(

		array(
			'type' => 'submenu',
			'parent_slug' => 'edit.php?post_type=poll',
			//'parent_slug' => false,
			'page_title' => __('Relogin Settings', 'wvp'),
			'menu_title' => __('Relogin Settings', 'wvp'),
			'capability' => 'edit_published_posts',
			'menu_slug' => 'relogin_settings',
			'is_form' => true,
			'parameters' => array(
		 
				array(
					'type' => 'select',
					'title' => __('Allow only Relogin','wvp'),
					'name' => 'allow_only_relogin',
					'value' => ['no' => __('No', 'wvp'), 'yes' => __('Yes', 'wvp')],
					'sub_text' => __('Allow only re-login to the site to users that have previously made at least one successful login BEFORE this server/time (Wordpress installation time) of same day. All other users will not be able to login','wvp'),
				),
				array(
					'type' => 'text',
					'title' => __('Enter TIME','wvp'),
					'name' => 'event_start_time',
					'class' => 'date_time_picker',
			 
					'sub_text' => '',
				),
				array(
					'type' => 'save',
					'title' => __('Save','wvp'),
					 
				),
				 
			)
		)
	); 
	$settings = new vooSettingsClassPoll( 'wvp_relogin' ); 
	$settings->create_menu(  $config_big   );


} );
	
 

?>