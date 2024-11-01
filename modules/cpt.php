<?php
if (!class_exists('vooCPTVote')) {
    class vooCPTVote
    {

        public $parameters;
        public $post_type;

        public function __construct($post_type)
        {

            $this->post_type = $post_type;

            add_action('init', array($this, 'add_post_type'), 1);
            register_activation_hook(__FILE__, array($this, 'add_post_type'));
            register_activation_hook(__FILE__, 'flush_rewrite_rules');
        }
        public function add_post_type()
        {

            $labels = array(
                'name' => __('Poll', 'wvp'),
                'singular_name' => __('Poll', 'wvp'),
                'add_new' => __('Add New', 'wvp'),
                'add_new_item' => __('Add New Poll', 'wvp'),
                'edit_item' => __('Edit Poll', 'wvp'),
                'new_item' => __('New Poll', 'wvp'),
                'all_items' => __('All Poll', 'wvp'),
                'view_item' => __('View Card', 'wvp'),
                'search_items' => __('Search Poll', 'wvp'),
                'not_found' => __('No Poll found', 'wvp'),
                'not_found_in_trash' => __('No Poll found in Trash', 'wvp'),
                'parent_item_colon' => '',
                'menu_name' => __('Polls', 'wvp'),

            );
            $args = array(
                'labels' => $labels,
                'public' => false,
                'publicly_queryable' => false,
                'show_ui' => true,
                'show_in_menu' => true,
                'query_var' => true,
                'rewrite' => true,
                'capability_type' => 'post',
                'has_archive' => true,
                'hierarchical' => false,
                'menu_position' => null,
                'supports' => array('title' /*'custom-fields' 'editor' , 'thumbnail', 'excerpt', 'custom-fields'   'custom-fields' 'custom-fields'  'editor', 'thumbnail', 'custom-fields'  'author', , 'custom-fields', 'editor'  */),
            );

            register_post_type($this->post_type, $args);
        }

    }
}

$new_pt = new vooCPTVote('poll');

if( !class_exists('vooCPTEmailTemplate') ){
	class vooCPTEmailTemplate{
		
		var $parameters;
		var $post_type;
		
		function __construct(  $post_type ){
		 
			$this->post_type = $post_type;
		 
			add_action( 'init', array( $this, 'add_post_type' ), 1 );
			register_activation_hook( __FILE__, array( $this, 'add_post_type' ) );	 
			register_activation_hook( __FILE__, 'flush_rewrite_rules' );
		}
		function add_post_type(){

			$labels = array(
				'name' => __('Email Template', 'wvp'),
				'singular_name' => __('Email Template', 'wvp'),
				'add_new' => __('Add New', 'wvp'),
				'add_new_item' => __('Add New Email Template', 'wvp'),
				'edit_item' => __('Edit Email Template', 'wvp'),
				'new_item' => __('New Email Template', 'wvp'),
				'all_items' => __('Email Templates', 'wvp'),
				'view_item' => __('View Email Template', 'wvp'),
				'search_items' => __('Search Email Template', 'wvp'),
				'not_found' =>  __('No Email Template found', 'wvp'),
				'not_found_in_trash' => __('No Email Template found in Trash', 'wvp'), 
				'parent_item_colon' => '',
				'menu_name' => __('Email Templates', 'wvp')
			
			  );
			  $args = array(
				'labels' => $labels,
				'public' => false,
				'publicly_queryable' => false,
				'show_ui' => true, 
				'show_in_menu' => 'edit.php?post_type=poll',
				'query_var' => true,
				'rewrite' => true,
				'capability_type' => 'post',
				'has_archive' => true, 
				'hierarchical' => false,
				'menu_position' => null,
				'supports' => array( 'title', /*'custom-fields' 'editor' , 'thumbnail', 'excerpt', 'custom-fields'   'custom-fields' 'custom-fields'  'editor', 'thumbnail', 'custom-fields'  'author', , 'custom-fields', 'editor'  */)
			  ); 
			

			register_post_type( $this->post_type, $args );
			}
 
	}
}
$new_pt = new vooCPTEmailTemplate(   'email_template' );


if (!class_exists('vooTaxVote')) {
    class vooTaxVote
    {

        public $parameters;
        public $post_type;
        public $tax_slug;

        public function __construct($tax_slug, $post_type)
        {
            //$this->parameters = $in_parameters;
            $this->post_type = $post_type;
            $this->tax_slug = $tax_slug;

            add_action('init', array($this, 'register_taxonomy'), 2);
            register_activation_hook(__FILE__, array($this, 'register_taxonomy'));
            //register_activation_hook( __FILE__, 'flush_rewrite_rules' );
        }
        public function register_taxonomy()
        {
            $labels = array(
                'name' => __('Poll Categories', 'wvp'),
                'singular_name' => __('Poll Categories', 'wvp'),
                'search_items' => __('Search Poll Categories', 'wvp'),
                'popular_items' => __('Popular Poll Categories', 'wvp'),
                'all_items' => __('All Poll Categories', 'wvp'),
                'parent_item' => null,
                'parent_item_colon' => null,
                'edit_item' => __('Edit Poll Category', 'wvp'),
                'update_item' => __('Update Category', 'wvp'),
                'add_new_item' => __('Add New Category', 'wvp'),
                'new_item_name' => __('New Category Name', 'wvp'),
                'separate_items_with_commas' => __('Separate Categories with commas', 'wvp'),
                'add_or_remove_items' => __('Add or remove Categories', 'wvp'),
                'choose_from_most_used' => __('Choose from the most used Categoryies', 'wvp'),
                'not_found' => __('No Categories found.', 'wvp'),
                'menu_name' => __('Poll categories', 'wvp'),
            );

            $args = array(
                'hierarchical' => true,
                'labels' => $labels,
                'show_ui' => true,
                'show_admin_column' => true,
                'update_count_callback' => '_update_post_term_count',
                'query_var' => true,
                'rewrite' => true,
            );
            register_taxonomy($this->tax_slug, $this->post_type, $args);
        }

    }
}

new vooTaxVote('poll_category', 'poll');

add_action('admin_menu', 'nopio_add_user_categories_admin_page');

function nopio_add_user_categories_admin_page()
{
    $taxonomy = get_taxonomy(USER_CATEGORY_NAME);
    add_users_page(
        esc_attr($taxonomy->labels->menu_name), //page title
        esc_attr($taxonomy->labels->menu_name), //menu title
        $taxonomy->cap->manage_terms, //capability
        'edit-tags.php?taxonomy=' . $taxonomy->name//menu slug
    );
}

add_filter('submenu_file', 'nopio_set_user_category_submenu_active');

function nopio_set_user_category_submenu_active($submenu_file)
{
    global $parent_file;
    if ('edit-tags.php?taxonomy=' . USER_CATEGORY_NAME == $submenu_file) {
        $parent_file = 'users.php';
    }
    return $submenu_file;
}

add_action('show_user_profile', 'nopio_admin_user_profile_category_select');
add_action('edit_user_profile', 'nopio_admin_user_profile_category_select');
add_action('user_new_form', 'nopio_admin_user_profile_category_select');

function nopio_admin_user_profile_category_select($user)
{
    global $current_user;

    $taxonomy = get_taxonomy(USER_CATEGORY_NAME);

    if (!current_user_can('administrator')) {
        return;
    }
    ?>
	<table class="form-table">
		<tr>
			<th>
				<label for="<?php echo USER_CATEGORY_META_KEY ?>"><?php _e('User Category', 'wvp');?></label>
			</th>
			<td>
				<?php
$user_category_terms = get_terms(array(
        'taxonomy' => USER_CATEGORY_NAME,
        'hide_empty' => 0,
    ));

    $select_options = array();
    $select_options[''] = __('Select category', 'wvp');
    foreach ($user_category_terms as $term) {
        $select_options[$term->term_id] = $term->name;
    }

    $meta_values = get_user_meta($user->ID, USER_CATEGORY_META_KEY, true);

    echo nopio_custom_form_select(
        USER_CATEGORY_META_KEY,
        $meta_values,
        $select_options,
        '',
        array()
    );
    ?>
			</td>
		</tr>
	</table>
	<?php
}

function nopio_custom_form_select($name, $value, $options, $default_var = '', $html_params = array())
{
    if (empty($options)) {
        $options = array('' => __('Select category', 'wvp'));
    }

    $html_params_string = '';

    if (!empty($html_params)) {
        if (array_key_exists('multiple', $html_params)) {
            $name .= '[]';
        }
        foreach ($html_params as $html_params_key => $html_params_value) {
            $html_params_string .= " {$html_params_key}='{$html_params_value}'";
        }
    }

    echo "<select name='{$name}'{$html_params_string}>";

    foreach ($options as $options_value => $options_label) {
        if ((is_array($value) && in_array($options_value, $value))
            || $options_value == $value) {
            $selected = " selected='selected'";
        } else {
            $selected = '';
        }
        if (empty($value) && !empty($default_var) && $options_value == $default_var) {
            $selected = " selected='selected'";
        }
        echo "<option value='{$options_value}'{$selected}>{$options_label}</option>";
    }

    echo "</select>";
}

add_action('personal_options_update', 'nopio_admin_save_user_categories');
add_action('edit_user_profile_update', 'nopio_admin_save_user_categories');
add_action('user_register', 'nopio_admin_save_user_categories');

function nopio_admin_save_user_categories($user_id)
{
    global $current_user;

    $tax = get_taxonomy(USER_CATEGORY_NAME);
    $user = get_userdata($user_id);

    if (!current_user_can('administrator')) {
        return false;
    }

    $new_categories_ids = $_POST[USER_CATEGORY_META_KEY];
    $user_meta = get_user_meta($user_id, USER_CATEGORY_META_KEY, true);
    $previous_categories_ids = array();

    if (!empty($user_meta)) {
        $previous_categories_ids = (array) $user_meta;
    }

    //if( ( current_user_can( 'administrator' )   ) ) {
    //    delete_user_meta( $user_id, USER_CATEGORY_META_KEY );
    //    nopio_update_users_categories_count( $previous_categories_ids, array() );
    //} else {

    $user_category_terms = get_terms(array(
        'taxonomy' => USER_CATEGORY_NAME,
        'hide_empty' => 0,
    ));

    foreach ($user_category_terms as $single_cat) {

        if ($single_cat->term_id == $new_categories_ids) {
            $selected_cat_name = $single_cat->name;
        }
    }

    update_user_meta($user_id, USER_CATEGORY_META_KEY, $new_categories_ids);
    update_user_meta($user_id, USER_CATEGORY_NAME_META_KEY, $selected_cat_name);

    nopio_update_users_categories_count($previous_categories_ids, $new_categories_ids);
    //}
}

function nopio_update_users_categories_count($previous_terms_ids, $new_terms_ids)
{
    global $wpdb;

    $terms_ids = array_unique(array_merge((array) $previous_terms_ids, (array) $new_terms_ids));

    if (count($terms_ids) < 1) {return;}

    foreach ($terms_ids as $term_id) {
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $wpdb->usermeta WHERE meta_key = %s AND meta_value LIKE %s",
                USER_CATEGORY_META_KEY,
                '%"' . $term_id . '"%'
            )
        );
        $wpdb->update($wpdb->term_taxonomy, array('count' => $count), array('term_taxonomy_id' => $term_id));
    }
}
/**  User category functionality   END  */

?>