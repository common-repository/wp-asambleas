<?php
 

// This is the secret key for API authentication. You configured it in the settings menu of the license manager plugin.
define('YOUR_SPECIAL_SECRET_KEY', '5f0df0e28afc94.28766657'); //Rename this constant name so it is specific to your plugin or theme.

// This is the URL where API query request will be sent to. This should be the URL of the site where you have installed the main license manager plugin. Get this value from the integration help page.
define('YOUR_LICENSE_SERVER_URL', 'https://asamblea.co/'); //Rename this constant name so it is specific to your plugin or theme.

// This is a value that will be recorded in the license manager data so you can identify licenses for this item/product.
define('YOUR_ITEM_REFERENCE', 'License Plugin'); //Rename this constant name so it is specific to your plugin or theme.

add_action('admin_menu', 'wvp_sample_license_menu', 10000000000000000000000);

function wvp_sample_license_menu() {
//CS    add_submenu_page('edit.php?post_type=poll', __('License', 'wvp'), __('License', 'wvp'), 'manage_options', 'license_page', 'wvp_license_management_page');
}

function wvp_license_management_page() {
    echo '<div class="wrap">';
    echo '<h2>'.__('License Management', 'wvp').'</h2>';

    /*** License activate button was clicked ***/
    if (isset($_REQUEST['activate_license'])) {
        $license_key = $_REQUEST['sample_license_key'];

        // API query parameters
        $api_params = array(
            'slm_action' => 'slm_activate',
            'secret_key' => YOUR_SPECIAL_SECRET_KEY,
            'license_key' => $license_key,
            'registered_domain' => $_SERVER['SERVER_NAME'],
            'item_reference' => urlencode(YOUR_ITEM_REFERENCE),
        );

        // Send query to the license manager server
        $query = esc_url_raw(add_query_arg($api_params, YOUR_LICENSE_SERVER_URL));
        $response = wp_remote_get($query, array('timeout' => 20, 'sslverify' => false));

        // Check for error in the response
        if (is_wp_error($response)){
            echo __("Unexpected Error! The query returned with an error.", 'wvp');
        }

        //var_dump($response);//uncomment it if you want to look at the full response
        
        // License data.
        $license_data = json_decode(wp_remote_retrieve_body($response));
        
        // TODO - Do something with it.
        //var_dump($license_data);//uncomment it to look at the data
        
        if($license_data->result == 'success'){//Success was returned for the license activation
            
            //Uncomment the followng line to see the message that returned from the license server
            echo '<br />';
            echo '<div class="tw-bs4">';
                echo '<div class="alert alert-success">';
                    echo __('The following message was returned from the server: ', 'wvp').$license_data->message;
                echo '</div>';
            echo '</div>';
            
            //Save the license key in the options table
            update_option('wvp_license_key', $license_key); 
        }
        else{
            //Show error to the user. Probably entered incorrect license key.
            
            //Uncomment the followng line to see the message that returned from the license server
            echo '<br />';
            echo '<div class="tw-bs4">';
                echo '<div class="alert alert-danger">';
                    echo __('The following message was returned from the server: ', 'wvp').$license_data->message;
                echo '</div>';
            echo '</div>';
            delete_option('wvp_license_key'); 
        }

    }
    /*** End of license activation ***/
    
    /*** License activate button was clicked ***/
    if (isset($_REQUEST['deactivate_license'])) {
        $license_key = $_REQUEST['sample_license_key'];

        // API query parameters
        $api_params = array(
            'slm_action' => 'slm_deactivate',
            'secret_key' => YOUR_SPECIAL_SECRET_KEY,
            'license_key' => $license_key,
            'registered_domain' => $_SERVER['SERVER_NAME'],
            'item_reference' => urlencode(YOUR_ITEM_REFERENCE),
        );

        // Send query to the license manager server
        $query = esc_url_raw(add_query_arg($api_params, YOUR_LICENSE_SERVER_URL));
        $response = wp_remote_get($query, array('timeout' => 20, 'sslverify' => false));

        // Check for error in the response
        if (is_wp_error($response)){
            echo __("Unexpected Error! The query returned with an error.", 'wvp');
        }

        //var_dump($response);//uncomment it if you want to look at the full response
        
        // License data.
        $license_data = json_decode(wp_remote_retrieve_body($response));
        
        // TODO - Do something with it.
        //var_dump($license_data);//uncomment it to look at the data
        
        if($license_data->result == 'success'){//Success was returned for the license activation
            
            //Uncomment the followng line to see the message that returned from the license server
            echo '<br />';
            echo __('The following message was returned from the server: ', 'wvp').$license_data->message;
            
            //Remove the licensse key from the options table. It will need to be activated again.
            update_option('wvp_license_key', '');
        }
        else{
            //Show error to the user. Probably entered incorrect license key.
            
            //Uncomment the followng line to see the message that returned from the license server
            echo '<br />';
            echo __('The following message was returned from the server: ', 'wvp').$license_data->message;
        }
        
    }
    /*** End of sample license deactivation ***/
    
    ?>
    <div class="tw-bs4">
    <div class="alert alert-warning"><?php _e('Please enter the license key for this product to activate it. You were given a license key when you purchased this item', 'wvp'); ?></div>
    </div>
    <form action="" method="post">
        <table class="form-table">
            <tr>
                <th style="width:100px;"><label for="sample_license_key"><?php _e('License Key', 'wvp'); ?></label></th>
                <td ><input class="regular-text" type="text" id="sample_license_key" name="sample_license_key"  value="<?php echo get_option('wvp_license_key'); ?>" ></td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="activate_license" value="<?php _e('Activate', 'wvp'); ?>" class="button-primary" />
            <input type="submit" name="deactivate_license" value="<?php _e('Deactivate', 'wvp'); ?>" class="button" />
        </p>
    </form>
    <?php
    
    echo '</div>';
}