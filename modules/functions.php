<?php 

function get_own_shares( $user_id ){
	return (float)get_user_meta( $user_id, 'own_shares', true);
}
function get_total_shares( $user_id ){
	return (float)get_user_meta( $user_id, 'total_shares', true);
}
function get_proxys_amount( $user_id ){
	return (float)get_user_meta( $user_id, 'proxys_amount', true);
}
function wvp_get_total_quorum(){
	global $wpdb;

	$settings = get_option('wvp_options');

	// checke not online users
	$memberlistoffline = $atts['memberlistoffline'];

	$org_total_shares = (int)$settings['organization_total_shares'];


	$output_blocks =  $settings['quorum_table'];
	$show_online_users =  $settings['show_online_users'];

	//$time_limit = current_time('timestamp') - (int)$settings['user_online_lifetime']*60;

	if( $settings['user_online_lifetime_type'] == 'prev_online_minutes' ){
		$time_limit = current_time('timestamp') - (int)$settings['prev_online_minutes']*60;
	}
	if( $settings['user_online_lifetime_type'] == 'from_timestamp' ){
		$time_limit = strtotime( $settings['from_timestamp'] );
	}


	$all_users_that_cant_vote = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value != '1' " );

	$all_users_that_caN_vote = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '1' " );
 
	// remove administartors
	$tmp_cnt = [];
	if( count($all_users_that_caN_vote) > 0 ){
		foreach( $all_users_that_caN_vote as $_user ){
		 
			$user_extra_data = get_userdata(  $_user  );
			if( in_array( 'administrator',  $user_extra_data->roles ) ){
				continue;
			}
			$tmp_cnt[] = $_user;
		}
		$all_users_that_caN_vote  = $tmp_cnt;
	}
 
	$all_online_usrs = $wpdb->get_results("SELECT DISTINCT user_id as ID FROM {$wpdb->usermeta} WHERE meta_key = 'online_activity' AND meta_value > {$time_limit} AND user_id  IN ( ".implode( ",", $all_users_that_caN_vote )." )");
	

	$total_shares = 0;

	$total_proxies = 0;

	if(  count($all_online_usrs) > 0 )
	foreach( $all_online_usrs as $s_user ){
		$own_shares = get_own_shares( $s_user->ID ); 
		$total_shares = get_total_shares( $s_user->ID );   
		$total_proxies = $total_proxies + $own_shares + $total_shares;	
	}
	$val_c = $total_proxies*100 / $org_total_shares;

	// customize poll outptu
	$output_options = get_option('wvp_options');
	$columns_data = $output_options['quorum_settings']['show_item'];
	$labels_data = $output_options['quorum_settings']['block_value'];
	 
 
	$columns_table_data = $output_options['quorum_settings_table']['show_item'];
	$labels_table_data = $output_options['quorum_settings_table']['block_value'];


	$big_total_quorum = 0;

	$out = '
	<div class="tw-bs4">
		<table class="table">
			<tbody>';
			if( $columns_table_data['col_1'] == 'on' ){
			$out .= '
				<tr>
					<td class="">'.$labels_table_data[0].'</td>
					<td class="">'.count($all_online_usrs).'</td>
					
				</tr>';
			}
			if( $columns_table_data['col_2'] == 'on' ){
			$out .= '
				<tr>
					<td class="">'.$labels_table_data[1].'</td>
					<td class="">'.$total_proxies.'</td>
					
				</tr>';
			}
			if( $columns_table_data['col_3'] == 'on' ){
			$out .= '
				<tr>
					<td class="">'.$labels_table_data[2].'</td>
					<td class="">'.number_format( $val_c, 2 ).'%</td>
				</tr>';
			}

			if( $columns_table_data['col_4'] == 'on' ){
			$out .= '
				<tr>
					<td class="">'.$labels_table_data[3].'</td>
					<td class="">'.$org_total_shares.'</td>
					
				</tr>';
			}
			$out .= '
			</tbody>
		</table>';
		
		if( $show_online_users == 'yes' ){
			$out .= '	
			<h3>'.stripslashes( $output_options['user_list_title'] ).'</h3>
			<table class="table">
				<thead class="">
				<tr>';
				if( $columns_data['col_1'] == 'on' ){
					$out .= '
					<th class="">'.$labels_data[0].'</th>';
				}

				if( $columns_data['col_2'] == 'on' ){
					$out .= '
					<th class="">'.$labels_data[1].'</th>';
				}
				if( $columns_data['col_3'] == 'on' ){
					$out .= '
					<th class="">'.$labels_data[2].'</th>';
				}
				if( $columns_data['col_4'] == 'on' ){
					$out .= '
					<th class="">'.$labels_data[3].'</th>';
				}
				if( $columns_data['col_5'] == 'on' ){
					$out .= '
					<th class="">'.$labels_data[4].'</th>';
				}
				if( $columns_data['col_6'] == 'on' ){
					$out .= '
					<th class="">'.$labels_data[5].'</th>';
				}
				$out .= '
				</tr>
				</thead>
				<tbody class="">';
	
				$all_users = $all_online_usrs;
				$total_results = [];
			 
				foreach( $all_users as $s_user ){
	
				$val_a =  get_own_shares( $s_user->ID );  
				$val_b = get_total_shares( $s_user->ID );   
	
				$val_a_b = $val_a + $val_b;
	 
				$s_user = get_user_by( 'ID', $s_user->ID );
				 
				$out .= '
				<tr>';
					if( $columns_data['col_1'] == 'on' ){
						$out .= '<td class="">'.$s_user->first_name.'</td>';
					}
					if( $columns_data['col_2'] == 'on' ){
						$out .= '<td class="">'.$s_user->last_name.'</td>';
					}
					if( $columns_data['col_3'] == 'on' ){
						$out .= '<td class="">'.$val_a.'</td>';
					}
					if( $columns_data['col_4'] == 'on' ){
						$out .= '<td class="">'.get_proxys_amount( $s_user->ID ).'</td>';
					}
					if( $columns_data['col_5'] == 'on' ){
						$out .= '<td class="">'.$val_b.'</td>';
					}
					if( $columns_data['col_6'] == 'on' ){
						$out .= '<td class="">'.$val_a_b.'</td>';
					}
					$big_total_quorum = $big_total_quorum + $val_a_b;
		 $out .= '
				</tr>';
				}
				$out .= '
				</tbody>
		 
			</table>';
		}
		 

		 


	$out .= '
	</div>
	';


	return $big_total_quorum;
}


function get_browser_name($user_agent)
{
        // Make case insensitive.
        $t = strtolower($user_agent);

        // If the string *starts* with the string, strpos returns 0 (i.e., FALSE). Do a ghetto hack and start with a space.
        // "[strpos()] may return Boolean FALSE, but may also return a non-Boolean value which evaluates to FALSE."
        //     http://php.net/manual/en/function.strpos.php
        $t = " " . $t;

        // Humans / Regular Users     
        if     (strpos($t, 'opera'     ) || strpos($t, 'opr/')     ) return 'Opera'            ;
        elseif (strpos($t, 'edge'      )                           ) return 'Edge'             ;
        elseif (strpos($t, 'chrome'    )                           ) return 'Chrome'           ;
        elseif (strpos($t, 'safari'    )                           ) return 'Safari'           ;
        elseif (strpos($t, 'firefox'   )                           ) return 'Firefox'          ;
        elseif (strpos($t, 'msie'      ) || strpos($t, 'trident/7')) return 'Internet Explorer';

        // Search Engines 
        elseif (strpos($t, 'google'    )                           ) return '[Bot] Googlebot'   ;
        elseif (strpos($t, 'bing'      )                           ) return '[Bot] Bingbot'     ;
        elseif (strpos($t, 'slurp'     )                           ) return '[Bot] Yahoo! Slurp';
        elseif (strpos($t, 'duckduckgo')                           ) return '[Bot] DuckDuckBot' ;
        elseif (strpos($t, 'baidu'     )                           ) return '[Bot] Baidu'       ;
        elseif (strpos($t, 'yandex'    )                           ) return '[Bot] Yandex'      ;
        elseif (strpos($t, 'sogou'     )                           ) return '[Bot] Sogou'       ;
        elseif (strpos($t, 'exabot'    )                           ) return '[Bot] Exabot'      ;
        elseif (strpos($t, 'msn'       )                           ) return '[Bot] MSN'         ;

        // Common Tools and Bots
        elseif (strpos($t, 'mj12bot'   )                           ) return '[Bot] Majestic'     ;
        elseif (strpos($t, 'ahrefs'    )                           ) return '[Bot] Ahrefs'       ;
        elseif (strpos($t, 'semrush'   )                           ) return '[Bot] SEMRush'      ;
        elseif (strpos($t, 'rogerbot'  ) || strpos($t, 'dotbot')   ) return '[Bot] Moz or OpenSiteExplorer';
        elseif (strpos($t, 'frog'      ) || strpos($t, 'screaming')) return '[Bot] Screaming Frog';
       
        // Miscellaneous
        elseif (strpos($t, 'facebook'  )                           ) return '[Bot] Facebook'     ;
        elseif (strpos($t, 'pinterest' )                           ) return '[Bot] Pinterest'    ;
       
        // Check for strings commonly used in bot user agents  
        elseif (strpos($t, 'crawler' ) || strpos($t, 'api'    ) ||
                strpos($t, 'spider'  ) || strpos($t, 'http'   ) ||
                strpos($t, 'bot'     ) || strpos($t, 'archive') ||
                strpos($t, 'info'    ) || strpos($t, 'data'   )    ) return '[Bot] Other'   ;
       
        return 'Other (Unknown)';
}

// helper function get browser
function getBrowser() { 
	$u_agent = $_SERVER['HTTP_USER_AGENT'];
	$bname = 'Unknown';
	$platform = 'Unknown';
	$version= "";
  
	//First get the platform?
	if (preg_match('/linux/i', $u_agent)) {
	  $platform = 'linux';
	}elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
	  $platform = 'mac';
	}elseif (preg_match('/windows|win32/i', $u_agent)) {
	  $platform = 'windows';
	}
  
	// Next get the name of the useragent yes seperately and for good reason
	if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)){
	  $bname = 'Internet Explorer';
	  $ub = "MSIE";
	}elseif(preg_match('/Firefox/i',$u_agent)){
	  $bname = 'Mozilla Firefox';
	  $ub = "Firefox";
	}elseif(preg_match('/OPR/i',$u_agent)){
	  $bname = 'Opera';
	  $ub = "Opera";
	}elseif(preg_match('/Chrome/i',$u_agent) && !preg_match('/Edge/i',$u_agent)){
	  $bname = 'Google Chrome';
	  $ub = "Chrome";
	}elseif(preg_match('/Safari/i',$u_agent) && !preg_match('/Edge/i',$u_agent)){
	  $bname = 'Apple Safari';
	  $ub = "Safari";
	}elseif(preg_match('/Netscape/i',$u_agent)){
	  $bname = 'Netscape';
	  $ub = "Netscape";
	}elseif(preg_match('/Edge/i',$u_agent)){
	  $bname = 'Edge';
	  $ub = "Edge";
	}elseif(preg_match('/Trident/i',$u_agent)){
	  $bname = 'Internet Explorer';
	  $ub = "MSIE";
	}
  
	// finally get the correct version number
	$known = array('Version', $ub, 'other');
	$pattern = '#(?<browser>' . join('|', $known) .
  ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	if (!preg_match_all($pattern, $u_agent, $matches)) {
	  // we have no matching number just continue
	}
	// see how many we have
	$i = count($matches['browser']);
	if ($i != 1) {
	  //we will have two since we are not using 'other' argument yet
	  //see if version is before or after the name
	  if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
		  $version= $matches['version'][0];
	  }else {
		  $version= $matches['version'][1];
	  }
	}else {
	  $version= $matches['version'][0];
	}
  
	// check if we have a number
	if ($version==null || $version=="") {$version="?";}
  
	return array(
	  'userAgent' => $u_agent,
	  'name'      => $bname,
	  'version'   => $version,
	  'platform'  => $platform,
	  'pattern'    => $pattern
	);
} 

function wvp_get_user_email_content( $s_id, $template_content ){
	$userdata = get_user_by( 'ID', $s_id );
	$user_pass = get_user_meta( $s_id, 'password_to_send', true );
	$custom1 = get_user_meta( $s_id, 'custom1', true );
	$custom2 = get_user_meta( $s_id, 'custom2', true );
	$own_shares = get_own_shares( $s_id  );

	$received_shares = get_proxys_amount( $s_id  );
	$own_plus_received_shares = get_total_shares( $s_id  );

 
	// reset pass link
	global $gw_activate_template;
	if( $gw_activate_template ){
		extract( $gw_activate_template->result );
	}
	

	$url = is_multisite() ? get_blogaddress_by_id( (int) $blog_id ) : home_url('', 'http');
	$user = new WP_User( (int) $s_id );
	
	$adt_rp_key = get_password_reset_key( $user );

	if( is_wp_error( $adt_rp_key ) ){
		$adt_rp_key = '';
	}

	$user_login = $user->user_login;
	$rp_link = '<a href="' . network_site_url("wp-login.php?action=rp&key=$adt_rp_key&login=" . rawurlencode($user_login), 'login') . '">' . network_site_url("wp-login.php?action=rp&key=$adt_rp_key&login=" . rawurlencode($user_login), 'login') . '</a>';
	$reset_pass_link = $rp_link;

	$template_content_inner = str_replace('{first_name}', $userdata->first_name, $template_content);
	$template_content_inner = str_replace('{last_name}', $userdata->last_name, $template_content_inner);
	$template_content_inner = str_replace('{password}', $user_pass, $template_content_inner);
	$template_content_inner = str_replace('{username}', $userdata->user_login, $template_content_inner);

	$template_content_inner = str_replace('{custom1}', $custom1, $template_content_inner);
	$template_content_inner = str_replace('{custom2}', $custom2, $template_content_inner);
	$template_content_inner = str_replace('{own_shares}', $own_shares, $template_content_inner);

	/** v2.0 patch */
	$template_content_inner = str_replace('{received_shares}', $received_shares, $template_content_inner);
	$template_content_inner = str_replace('{own_plus_received_shares}', $own_plus_received_shares, $template_content_inner);
	$template_content_inner = str_replace('{resetpassword}', $reset_pass_link, $template_content_inner);	

	return $template_content_inner;
}

function wvp_rrmdir($dir) { 
	if (is_dir($dir)) { 
	  $objects = scandir($dir);
	  foreach ($objects as $object) { 
		if ($object != "." && $object != "..") { 
		  if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
			rrmdir($dir. DIRECTORY_SEPARATOR .$object);
		  else
			unlink($dir. DIRECTORY_SEPARATOR .$object); 
		} 
	  }
	  rmdir($dir); 
	} 
  }

  function wvp_auto_login( $user_id ) {
    //if ( !is_user_logged_in() ) {
 
        $user = get_user_by( 'ID', $user_id );
		$user_login = $user->user_login;
        $user_id = $user->ID;
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );
        do_action( 'wp_login', $user_login, $user );
    //}     
}

?>