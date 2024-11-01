<?php 
if( !class_exists('vooAddStylesSync') ){
	class vooAddStylesSync{
		
		protected $plugin_prefix;
		protected $plugin_version;
		protected $files_list;
		
		public  function __construct( $prefix, $parameters ){
			
			$this->files_list = $parameters;
			$this->plugin_prefix = $prefix;
			$this->plugin_version = '1.0';
			
			add_action('wp_print_scripts', array( $this, 'add_script_fn') );
		}
		public function add_script_fn(){
			wp_enqueue_media();
			
			 foreach( $this->files_list as $key => $value ){
				 if( $key == 'common' ){
					foreach( $value as $single_line ) {
						$this->process_enq_line( $single_line );
					}
				 }
				 if( $key == 'admin' && is_admin() ){
					foreach( $value as $single_line ) {
						$this->process_enq_line( $single_line );
					}
				 }
				 if( $key == 'front' && !is_admin() ){
					foreach( $value as $single_line ) {
						$this->process_enq_line( $single_line );
					}
				 }
			 }
		}
		public function process_enq_line( $line ){
			$custom_id  = rand( 1000, 9999).basename( $line['url'] );
			if( $line['type'] == 'style' ){
				wp_enqueue_style( $this->plugin_prefix.$custom_id, $line['url'] ) ;
			}
			if( $line['type'] == 'script' ){
				
				$rand_prefix = rand(1000, 9999);
				if( isset( $line['id'] )  ){
					$script_prefix = $line['id'];
				}else{
					$script_prefix = $this->plugin_prefix.$custom_id.$rand_prefix;
				}
				
				if( isset($line['enq']) ){
					wp_register_script( $script_prefix, $line['url'], $line['enq'] ) ;
				}
				
				if( isset( $line['localization'] ) ){
			 
					wp_localize_script( $script_prefix, $this->plugin_prefix.'_local_data', $line['localization'] );
				}				
				wp_enqueue_script( $script_prefix  ) ;		
			}
		}
	}
}
add_Action('init', function(  ){
		$scripts_list = array(
		'common' => array(
			array( 'type' => 'style', 'url' => plugins_url('/inc/assets/css/tw-bs4.css', __FILE__ ) ),
			array( 'type' => 'style', 'url' => 'https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.bootstrap3.min.css' ),
			//array( 'type' => 'script', 'url' => 'https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js' ),
			array( 'type' => 'script', 'url' => plugins_url('/js/jquery.tablesort.min.js', __FILE__ ), 'enq' => array( 'jquery' ) ),
			array( 'type' => 'script', 'url' => plugins_url('/js/selectize.min.js', __FILE__ ), 'enq' => array( 'jquery' ) ),
			//array( 'type' => 'style', 'url' => plugins_url('/inc/fa/css/font-awesome.min.css', __FILE__ ) ),
		),
		'admin' => array(
			 
			
		 
			array( 
				'type' => 'script', 
				'url' => plugins_url('/js/admin.js', __FILE__ ), 
				'enq' => array( 'jquery', 'jquery-ui-core', 'jquery-effects-core', 'jquery-ui-datepicker'  ), 
				'localization' => array( 
				'add_url' => get_option('home').'/wp-admin/post-new.php?post_type=event',
				'nonce' => wp_create_nonce( 'ajax_call_nonce' ),
				'ajaxurl' => admin_url('admin-ajax.php'),
				'same_user' => __('You cant assign share to same user', 'wvp'),
				'invalid_email' => __('Please, verify emails!', 'wvp'),
				'ofstring' => __(' of ', 'wvp'),
				'processedstring' => __('Processed ', 'wvp'),
				'messages_sent' => __(' messages have been sent. Please wait and don’t close this window until finishing', 'wvp'),
				'confirm_drop' => __('Are you sure? This action is not reversible', 'wvp'),
				)
			),

			array( 'type' => 'style', 'url' => 'https://code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css' ),
			array( 'type' => 'style', 'url' => 'https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.css'),
			array( 'type' => 'script', 'url' => 'https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.js', 'enq' => array( 'jquery' ) ),
			
			array( 'type' => 'style', 'url' => plugins_url('/css/admin.css', __FILE__ ) ),
		),
		'front' => array(
	 
			array( 'type' => 'style', 'url' => plugins_url('/inc/fb/dist/jquery.fancybox.css', __FILE__ )),
			array( 'type' => 'script', 'url' => plugins_url('/inc/fb/dist/jquery.fancybox.js', __FILE__ ), 'enq' => array( 'jquery' ) ),
	 
			
			
			array( 
				'type' => 'script', 
				'url' => plugins_url('/js/front.js', __FILE__ ), 
				'enq' => array( 'jquery' ), 
				'localization' => array( 
					'add_url' => get_option('home').'/wp-admin/post-new.php?post_type=event',
					'nonce' => wp_create_nonce( 'ajax_call_nonce' ),
					'ajaxurl' => admin_url('admin-ajax.php'),
					'pick_variant' => __('You need to pick from %s1 to %s2 variants', 'wvp')
				)
			),

			array( 'type' => 'style', 'url' => plugins_url('/css/front.css', __FILE__ ) ),
		)
	);

 
	$insert_script = new vooAddStylesSync( 'wvp' , $scripts_list);
})


?>