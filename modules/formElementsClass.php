<?php
if( !class_exists('formElementsClassPoll') ){
	class formElementsClassPoll{
		
		var  $type;
		var  $settings;
		var  $content;
	 
		function __construct( $type, $parameters, $value ){
	 
			$this->type = $type;
			$this->parameters = $parameters;
			$this->value = $value;
			
			
			$this->generate_result_block();
 
		}
		function generate_result_block(){
			global $post;
			$output_options = get_option('wvp_extra_options');
			switch( $this->type ){
				
				case "shortcode":
					$out .= '<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<div class="form-group">  
							<label class="control-label" for="'.$this->parameters['id'].'">'.$this->parameters['title'].'</label>  
							
							<input type="text" readonly class="form-control input-xlarge"   
							value="['.$this->parameters['name'].' id=\''.$post->ID.'\']"
							
							>  
							  <p class="help-block">'.$this->parameters['sub_text'].'</p>  
							
						  </div> 
					</div>';	
				break;
				case "multicheck":
					$out .= '<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<div class="form-group">  ';
							 
						$max_variants =  (int)get_post_meta( $post->ID, 'max_variants', true );
						$poll_variants = get_post_meta( $post->ID, 'poll_variants', true );
						if( is_array( $poll_variants ) ){
							$poll_variants =  count( $poll_variants );
						}else{
							$poll_variants =  0;
						}
						
						$poll_type =  get_post_meta( $post->ID, 'poll_type', true   );
						$diff = $poll_variants - $max_variants;
						if(  ( $diff <= 0 && $poll_type == 'multi' ) ){
							$out .= '<div class="alert alert-warning">'.__( 'Amount of variants need to be bigger then max  variants value', 'wvp' ).'</div>';	
						}

						$out .= '
					</div>';	
				break;
				case "poll_open_stats":

					$out .= '<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<div class="form-group">  
							<label class="control-label" for="'.$this->parameters['id'].'">'.$this->parameters['title'].'</label>  
							
							<table class="table">
								<thead>
									<tr>
										<th>'.__('Show?', 'wvp' ).'</th>
										<th>'.__('Column', 'wvp' ).'</th>
										<th class="">'.__('Column label - Write your text', 'wvp' ).'</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
										<input type="hidden" name="poll_open_stats[show_item][col_1]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_1'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_1'] ) ? ' checked ' : '' ).' name="poll_open_stats[show_item][col_1]" value="on" />
										</td>
										<td>'.__('Poll was closed at:', 'wvp' ).'</td>
										<td class="">
										<input type="text" name="poll_open_stats[block_value][]" value="'.( $this->value['block_value'][0] && $this->value['block_value'][0] != '' ? $this->value['block_value'][0] : __('Fecha/hora de cierre de la votación', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="poll_open_stats[show_item][col_2]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_2'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_2'] ) ? ' checked ' : '' ).' name="poll_open_stats[show_item][col_2]" value="on" /></td>
										<td>'.__('Quorum at the closing time:', 'wvp' ).'</td>
										<td class=""><input type="text" name="poll_open_stats[block_value][]" value="'.( $this->value['block_value'][1] && $this->value['block_value'][1] != '' ? $this->value['block_value'][1] : __('Quórum al momento del cierre', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="poll_open_stats[show_item][col_3]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_3'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_3'] ) ? ' checked ' : '' ).' name="poll_open_stats[show_item][col_3]" value="on" /></td>
										<td>'.__('Total users that answered this poll:', 'wvp' ).'</td>
										<td class=""><input type="text" name="poll_open_stats[block_value][]" value="'.( $this->value['block_value'][2] && $this->value['block_value'][2] != '' ? $this->value['block_value'][2] : __('Total de usuarios que votaron', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="poll_open_stats[show_item][col_4]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_4'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_4'] ) ? ' checked ' : '' ).' name="poll_open_stats[show_item][col_4]" value="on" /></td>
										<td>'.__('Total own shares + shares from proxies of users that voted in this poll:', 'wvp' ).'</td>
										<td class=""><input type="text" name="poll_open_stats[block_value][]" value="'.( $this->value['block_value'][3] && $this->value['block_value'][3] != '' ? $this->value['block_value'][3] : __('Total de coeficientes que participaron en esta votación', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="poll_open_stats[show_item][col_5]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_5'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_5'] ) ? ' checked ' : '' ).' name="poll_open_stats[show_item][col_5]" value="on" /></td>
										<td>'.__('Shares that didn’t vote:', 'wvp' ).'</td>
										<td class=""><input type="text" name="poll_open_stats[block_value][]" value="'.( $this->value['block_value'][4] && $this->value['block_value'][4] != '' ? $this->value['block_value'][4] : __('Shares that didn’t vote', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="poll_open_stats[show_item][col_6]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_6'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_6'] ) ? ' checked ' : '' ).' name="poll_open_stats[show_item][col_6]" value="on" /></td>
										<td>'.__('Quorum at the closing time minus shares that voted', 'wvp' ).'</td>
										<td class=""><input type="text" name="poll_open_stats[block_value][]" value="'.( $this->value['block_value'][5] && $this->value['block_value'][5] != '' ? $this->value['block_value'][5] : __('Quorum at the closing time minus shares that voted', 'wvp' ) ).'" /></td>
									</tr>
					 
								</tbody>
							</table> 
							
						  </div> 
					</div>';	
				break;
				case "reg_form_settings":
		
					$out .= '<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<div class="form-group">  
				 	
							<table class="table">
								<thead>
									<tr>
										<th>'.__('Enable/disable fields in the form', 'wvp' ).'</th>
										<th>'.__('Mandatory', 'wvp' ).'</th>
										<th>'.__('Field', 'wvp' ).'</th>
										<th class="">'.__('Field Label', 'wvp' ).'</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
											<input type="hidden" name="reg_form_settings[_filed_email]" value="off" />
											<input type="checkbox"   checked  disabled name="reg_form_settings[_filed_email]" value="on" />
										</td>
										<td>
											 
										</td>
										<td>
											'.__('Email (Mandatory)', 'wvp' ).'
										</td>
										<td class="">
											<input type="text" name="reg_form_settings[_filed_email_label]" value="'.( $this->value['_filed_email_label'] && $this->value['_filed_email_label'] != '' ? $this->value['_filed_email_label'] : __('', 'wvp' ) ).'" />
										</td>
									</tr>
									<tr>
										<td>
											<input type="hidden" name="reg_form_settings[_filed_username]" value="off" />
											<input type="checkbox" '.( $this->value['_filed_username'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['_filed_username'] ) ? ' checked ' : '' ).' name="reg_form_settings[_filed_username]" value="on" />
										</td>
										<td>
											<input type="hidden" name="reg_form_settings[_mandatory_username]" value="off" />
											<input type="checkbox" '.( $this->value['_mandatory_username'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['_mandatory_username'] ) ? ' checked ' : '' ).' name="reg_form_settings[_mandatory_username]" value="on" />
										</td>
										<td>
											'.__('Username', 'wvp' ).'
										</td>
										<td class="">
											<input type="text" name="reg_form_settings[_filed_username_label]" value="'.( $this->value['_filed_username_label'] && $this->value['_filed_username_label'] != '' ? $this->value['_filed_username_label'] : __('', 'wvp' ) ).'" />
										</td>
									</tr>
									<tr>
										<td>
											<input type="hidden" name="reg_form_settings[_filed_name]" value="off" />
											<input type="checkbox"   checked disabled   name="reg_form_settings[_filed_name]" value="on" />
										</td>
										<td>

										</td>
										<td>
											'.__('Name (Mandatory)', 'wvp' ).'
										</td>
										<td class="">
											<input type="text" name="reg_form_settings[_filed_name_label]" value="'.( $this->value['_filed_name_label'] && $this->value['_filed_name_label'] != '' ? $this->value['_filed_name_label'] : __('', 'wvp' ) ).'" />
										</td>
									</tr>
									<tr>
										<td>
											<input type="hidden" name="reg_form_settings[_filed_lastname]" value="off" />
											<input type="checkbox" '.( $this->value['_filed_lastname'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['_filed_lastname'] ) ? ' checked ' : '' ).' name="reg_form_settings[_filed_lastname]" value="on" />
										</td>
										<td>
											<input type="hidden" name="reg_form_settings[_mandatory_lastname]" value="off" />
											<input type="checkbox" '.( $this->value['_mandatory_lastname'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['_mandatory_lastname'] ) ? ' checked ' : '' ).' name="reg_form_settings[_mandatory_lastname]" value="on" />
										</td>
										<td>
											'.__('Last Name', 'wvp' ).'
										</td>
										<td class="">
											<input type="text" name="reg_form_settings[_filed_lastname_label]" value="'.( $this->value['_filed_lastname_label'] && $this->value['_filed_lastname_label'] != '' ? $this->value['_filed_lastname_label'] : __('', 'wvp' ) ).'" />
										</td>
									</tr>
									<tr>
										<td>
											<input type="hidden" name="reg_form_settings[_filed_password]" value="off" />
											<input type="checkbox"   checked  disabled  name="reg_form_settings[_filed_password]" value="on" />
										</td>
										<td>
											
										</td>
										<td>
											'.__('Password (Mandatory)', 'wvp' ).'
										</td>
										<td class="">
											<input type="text" name="reg_form_settings[_filed_password_label]" value="'.( $this->value['_filed_password_label'] && $this->value['_filed_password_label'] != '' ? $this->value['_filed_password_label'] : __('', 'wvp' ) ).'" />
										</td>
									</tr>
									<tr>
										<td>
											<input type="hidden" name="reg_form_settings[_filed_attach1]" value="off" />
											<input type="checkbox" '.( $this->value['_filed_attach1'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['_filed_attach1'] ) ? ' checked ' : '' ).' name="reg_form_settings[_filed_attach1]" value="on" />
										</td>
										<td>
											<input type="hidden" name="reg_form_settings[_mandatory_attach1]" value="off" />
											<input type="checkbox" '.( $this->value['_mandatory_attach1'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['_mandatory_attach1'] ) ? ' checked ' : '' ).' name="reg_form_settings[_mandatory_attach1]" value="on" />
										</td>
										<td>
											'.__('File Attachment 1', 'wvp' ).'
										</td>
										<td class="">
											<input type="text" name="reg_form_settings[_filed_attach1_label]" value="'.( $this->value['_filed_attach1_label'] && $this->value['_filed_attach1_label'] != '' ? $this->value['_filed_attach1_label'] : __('', 'wvp' ) ).'" />
										</td>
									</tr>
									<tr>
										<td>
											<input type="hidden" name="reg_form_settings[_filed_attach2]" value="off" />
											<input type="checkbox" '.( $this->value['_filed_attach2'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['_filed_attach2'] ) ? ' checked ' : '' ).' name="reg_form_settings[_filed_attach2]" value="on" />
										</td>
										<td>
											<input type="hidden" name="reg_form_settings[_mandatory_attach2]" value="off" />
											<input type="checkbox" '.( $this->value['_mandatory_attach2'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['_mandatory_attach2'] ) ? ' checked ' : '' ).' name="reg_form_settings[_mandatory_attach2]" value="on" />
										</td>
										<td>
											'.__('File Attachment 2', 'wvp' ).'
										</td>
										<td class="">
											<input type="text" name="reg_form_settings[_filed_attach2_label]" value="'.( $this->value['_filed_attach2_label'] && $this->value['_filed_attach2_label'] != '' ? $this->value['_filed_attach2_label'] : __('', 'wvp' ) ).'" />
										</td>
									</tr>
									<tr>
										<td>
											<input type="hidden" name="reg_form_settings[_filed_attach3]" value="off" />
											<input type="checkbox" '.( $this->value['_filed_attach3'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['_filed_attach3'] ) ? ' checked ' : '' ).' name="reg_form_settings[_filed_attach3]" value="on" />
										</td>
										<td>
											<input type="hidden" name="reg_form_settings[_mandatory_attach3]" value="off" />
											<input type="checkbox" '.( $this->value['_mandatory_attach3'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['_mandatory_attach3'] ) ? ' checked ' : '' ).' name="reg_form_settings[_mandatory_attach3]" value="on" />
										</td>
										<td>
											'.__('File Attachment 3', 'wvp' ).'
										</td>
										<td class="">
											<input type="text" name="reg_form_settings[_filed_attach3_label]" value="'.( $this->value['_filed_attach3_label'] && $this->value['_filed_attach3_label'] != '' ? $this->value['_filed_attach3_label'] : __('', 'wvp' ) ).'" />
										</td>
									</tr>
									<tr>
										<td>
											<input type="hidden" name="reg_form_settings[_filed_attach4]" value="off" />
											<input type="checkbox" '.( $this->value['_filed_attach4'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['_filed_attach4'] ) ? ' checked ' : '' ).' name="reg_form_settings[_filed_attach4]" value="on" />
										</td>
										<td>
											<input type="hidden" name="reg_form_settings[_mandatory_attach4]" value="off" />
											<input type="checkbox" '.( $this->value['_mandatory_attach4'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['_mandatory_attach4'] ) ? ' checked ' : '' ).' name="reg_form_settings[_mandatory_attach4]" value="on" />
										</td>
										<td>
											'.__('File Attachment 4', 'wvp' ).'
										</td>
										<td class="">
											<input type="text" name="reg_form_settings[_filed_attach4_label]" value="'.( $this->value['_filed_attach4_label'] && $this->value['_filed_attach4_label'] != '' ? $this->value['_filed_attach4_label'] : __('', 'wvp' ) ).'" />
										</td>
									</tr>
									<tr>
										<td>
											<input type="hidden" name="reg_form_settings[_filed_attach5]" value="off" />
											<input type="checkbox" '.( $this->value['_filed_attach5'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['_filed_attach5'] ) ? ' checked ' : '' ).' name="reg_form_settings[_filed_attach5]" value="on" />
										</td>
										<td>
											<input type="hidden" name="reg_form_settings[_mandatory_attach5]" value="off" />
											<input type="checkbox" '.( $this->value['_mandatory_attach5'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['_mandatory_attach5'] ) ? ' checked ' : '' ).' name="reg_form_settings[_mandatory_attach5]" value="on" />
										</td>
										<td>
											'.__('File Attachment 5', 'wvp' ).'
										</td>
										<td class="">
											<input type="text" name="reg_form_settings[_filed_attach5_label]" value="'.( $this->value['_filed_attach5_label'] && $this->value['_filed_attach5_label'] != '' ? $this->value['_filed_attach5_label'] : __('', 'wvp' ) ).'" />
										</td>
									</tr>
									<tr>
										<td>
											<input type="hidden" name="reg_form_settings[_filed_message]" value="off" />
											<input type="checkbox" '.( $this->value['_filed_message'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['_filed_message'] ) ? ' checked ' : '' ).' name="reg_form_settings[_filed_message]" value="on" />
										</td>
										<td>
											<input type="hidden" name="reg_form_settings[_mandatory_message]" value="off" />
											<input type="checkbox" '.( $this->value['_mandatory_message'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['_mandatory_message'] ) ? ' checked ' : '' ).' name="reg_form_settings[_mandatory_message]" value="on" />
										</td>
										<td>
											'.__('Message', 'wvp' ).'
										</td>
										<td class="">
											<input type="text" name="reg_form_settings[_filed_message_label]" value="'.( $this->value['_filed_message_label'] && $this->value['_filed_message_label'] != '' ? $this->value['_filed_message_label'] : __('', 'wvp' ) ).'" />
										</td>
									</tr>
									
									<!-- ############# -->
									<tr>
										<td>
											<input type="hidden" name="reg_form_settings[_filed_check1]" value="off" />
											<input type="checkbox" '.( $this->value['_filed_check1'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['_filed_check1'] ) ? ' checked ' : '' ).' name="reg_form_settings[_filed_check1]" value="on" />
										</td>
										<td>
											<input type="hidden" name="reg_form_settings[_mandatory_check1]" value="off" />
											<input type="checkbox" '.( $this->value['_mandatory_check1'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['_mandatory_check1'] ) ? ' checked ' : '' ).' name="reg_form_settings[_mandatory_check1]" value="on" />
										</td>
										<td>
											'.__('Checkbox 1', 'wvp' ).'
										</td>
										<td class="">
											<input type="text" name="reg_form_settings[_filed_check1_label]" value="'.( $this->value['_filed_check1_label'] && $this->value['_filed_check1_label'] != '' ? $this->value['_filed_check1_label'] : __('', 'wvp' ) ).'" />
										</td>
									</tr>
									<tr>
										<td>
											<input type="hidden" name="reg_form_settings[_filed_check2]" value="off" />
											<input type="checkbox" '.( $this->value['_filed_check2'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['_filed_check2'] ) ? ' checked ' : '' ).' name="reg_form_settings[_filed_check2]" value="on" />
										</td>
										<td>
											<input type="hidden" name="reg_form_settings[_mandatory_check2]" value="off" />
											<input type="checkbox" '.( $this->value['_mandatory_check2'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['_mandatory_check2'] ) ? ' checked ' : '' ).' name="reg_form_settings[_mandatory_check2]" value="on" />
										</td>
										<td>
											'.__('Checkbox 2', 'wvp' ).'
										</td>
										<td class="">
											<input type="text" name="reg_form_settings[_filed_check2_label]" value="'.( $this->value['_filed_check2_label'] && $this->value['_filed_check2_label'] != '' ? $this->value['_filed_check2_label'] : __('', 'wvp' ) ).'" />
										</td>
									</tr>
									<tr>
										<td>
											<input type="hidden" name="reg_form_settings[_filed_select1]" value="off" />
											<input type="checkbox" '.( $this->value['_filed_select1'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['_filed_select1'] ) ? ' checked ' : '' ).' name="reg_form_settings[_filed_select1]" value="on" />
										</td>
										<td>
											<input type="hidden" name="reg_form_settings[_mandatory_select1]" value="off" />
											<input type="checkbox" '.( $this->value['_mandatory_select1'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['_mandatory_select1'] ) ? ' checked ' : '' ).' name="reg_form_settings[_mandatory_select1]" value="on" />
										</td>
										<td>
											'.__('Select menu 1', 'wvp' ).'
										</td>
										<td class="">
											<input type="text" name="reg_form_settings[_filed_select1_label]" value="'.( $this->value['_filed_select1_label'] && $this->value['_filed_select1_label'] != '' ? $this->value['_filed_select1_label'] : __('', 'wvp' ) ).'" />

											<div class="mt-4">'.__('Options (Separate them by comma) ', 'wvp' ).'</div>
											<textarea  style="width:100%; height:100px;" name="reg_form_settings[_filed_select1_options_label]"   >'.( $this->value['_filed_select1_options_label'] && $this->value['_filed_select1_options_label'] != '' ? $this->value['_filed_select1_options_label'] : __('', 'wvp' ) ).'</textarea>
										</td>
									</tr>
									<tr>
										<td>
											<input type="hidden" name="reg_form_settings[_filed_select2]" value="off" />
											<input type="checkbox" '.( $this->value['_filed_select2'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['_filed_select2'] ) ? ' checked ' : '' ).' name="reg_form_settings[_filed_select2]" value="on" />
										</td>
										<td>
											<input type="hidden" name="reg_form_settings[_mandatory_select2]" value="off" />
											<input type="checkbox" '.( $this->value['_mandatory_select2'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['_mandatory_select2'] ) ? ' checked ' : '' ).' name="reg_form_settings[_mandatory_select2]" value="on" />
										</td>
										<td>
											'.__('Select menu 1', 'wvp' ).'
										</td>
										<td class="">
											<input type="text" name="reg_form_settings[_filed_select2_label]" value="'.( $this->value['_filed_select2_label'] && $this->value['_filed_select2_label'] != '' ? $this->value['_filed_select2_label'] : __('', 'wvp' ) ).'" />

											<div class="mt-4">'.__('Options (Separate them by comma) ', 'wvp' ).'</div>
											<textarea  style="width:100%; height:100px;" name="reg_form_settings[_filed_select2_options_label]"   >'.( $this->value['_filed_select2_options_label'] && $this->value['_filed_select2_options_label'] != '' ? $this->value['_filed_select2_options_label'] : __('', 'wvp' ) ).'</textarea>
										</td>
									</tr>
		 
					 
								</tbody>
							</table> 
							
						  </div> 
					</div>';	
				break;
				case "vote_users_table":

					$out .= '<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<div class="form-group">  
							<label class="control-label" for="'.$this->parameters['id'].'">'.$this->parameters['title'].'</label>  
							
							<table class="table">
								<thead>
									<tr>
										<th>'.__('Show?', 'wvp' ).'</th>
										<th>'.__('Column', 'wvp' ).'</th>
										<th class="">'.__('Column label - Write your text', 'wvp' ).'</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
										<input type="hidden" name="vote_users_table[show_item][col_1]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_1'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_1'] ) ? ' checked ' : '' ).' name="vote_users_table[show_item][col_1]" value="on" />
										</td>
										<td>'.__('Name', 'wvp' ).'</td>
										<td class="">
										<input type="text" name="vote_users_table[block_value][0]" value="'.( $this->value['block_value'][0] && $this->value['block_value'][0] != '' ? $this->value['block_value'][0] : __('Nombre', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="vote_users_table[show_item][col_2]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_2'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_2'] ) ? ' checked ' : '' ).' name="vote_users_table[show_item][col_2]" value="on" /></td>
										<td>'.__('Last Name', 'wvp' ).'</td>
										<td class=""><input type="text" name="vote_users_table[block_value][1]" value="'.( $this->value['block_value'][1] && $this->value['block_value'][1] != '' ? $this->value['block_value'][1] : __('Apellido', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="vote_users_table[show_item][col_3]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_3'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_3'] ) ? ' checked ' : '' ).' name="vote_users_table[show_item][col_3]" value="on" /></td>
										<td>'.__('Username', 'wvp' ).'</td>
										<td class=""><input type="text" name="vote_users_table[block_value][2]" value="'.( $this->value['block_value'][2] && $this->value['block_value'][2] != '' ? $this->value['block_value'][2] : __('Usuario', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="vote_users_table[show_item][col_6]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_6'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_6'] ) ? ' checked ' : '' ).' name="vote_users_table[show_item][col_6]" value="on" /></td>
										<td>'.__('Total own shares + received in proxies shares (A+B)', 'wvp' ).'</td>
										<td class=""><input type="text" name="vote_users_table[block_value][5]" value="'.( $this->value['block_value'][5] && $this->value['block_value'][5] != '' ? $this->value['block_value'][5] : __('Total own shares + received in proxies shares (A+B)', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="vote_users_table[show_item][col_4]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_4'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_4'] ) ? ' checked ' : '' ).' name="vote_users_table[show_item][col_4]" value="on" /></td>
										<td>'.__('User answer', 'wvp' ).'</td>
										<td class=""><input type="text" name="vote_users_table[block_value][3]" value="'.( $this->value['block_value'][3] && $this->value['block_value'][3] != '' ? $this->value['block_value'][3] : __('User answer', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="vote_users_table[show_item][col_5]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_5'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_5'] ) ? ' checked ' : '' ).' name="vote_users_table[show_item][col_5]" value="on" /></td>
										<td>'.__('User message', 'wvp' ).'</td>
										<td class=""><input type="text" name="vote_users_table[block_value][4]" value="'.( $this->value['block_value'][4] && $this->value['block_value'][4] != '' ? $this->value['block_value'][4] : __('User message', 'wvp' ) ).'" /></td>
									</tr>

									<tr>
										<td>
										<input type="hidden" name="vote_users_table[show_item][col_7]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_7'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_7'] ) ? ' checked ' : '' ).' name="vote_users_table[show_item][col_7]" value="on" /></td>
										<td>'.__('Own shares (A)', 'wvp' ).'</td>
										<td class=""><input type="text" name="vote_users_table[block_value][6]" value="'.( $this->value['block_value'][6] && $this->value['block_value'][6] != '' ? $this->value['block_value'][6] : __('Own shares (A)', 'wvp' ) ).'" /></td>
									</tr>

									<tr>
										<td>
										<input type="hidden" name="vote_users_table[show_item][col_8]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_8'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_8'] ) ? ' checked ' : '' ).' name="vote_users_table[show_item][col_8]" value="on" /></td>
										<td>'.__('Number of received shares', 'wvp' ).'</td>
										<td class=""><input type="text" name="vote_users_table[block_value][7]" value="'.( $this->value['block_value'][7] && $this->value['block_value'][7] != '' ? $this->value['block_value'][7] : __('Number of received shares', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="vote_users_table[show_item][col_9]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_9'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_9'] ) ? ' checked ' : '' ).' name="vote_users_table[show_item][col_9]" value="on" /></td>
										<td>'.__('Date-time of answer', 'wvp' ).'</td>
										<td class=""><input type="text" name="vote_users_table[block_value][8]" value="'.( $this->value['block_value'][8] && $this->value['block_value'][8] != '' ? $this->value['block_value'][8] : __('Date-time of answer', 'wvp' ) ).'" /></td>
									</tr>
				 
								</tbody>
							</table> 
							
						  </div> 
					</div>';	
				break;
				case "shortcode_config":

					$out .= '<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<div class="form-group">  
							<label class="control-label" for="'.$this->parameters['id'].'">'.$this->parameters['title'].'</label>  
							
							<table class="table">
								<thead>
									<tr>
										<th>'.__('Show?', 'wvp' ).'</th>
										<th>'.__('Column', 'wvp' ).'</th>
										<th class="">'.__('Column label - Write your text', 'wvp' ).'</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
										<input type="hidden" name="poll_settings[show_item][col_1]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_1'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_1'] ) ? ' checked ' : '' ).' name="poll_settings[show_item][col_1]" value="on" />
										
										
										</td>
										<td>'.__('Answer with horizontal bar', 'wvp' ).'</td>
										<td class="">
							 
										<input type="text" name="poll_settings[block_value][]" value="'.( $this->value['block_value'][0] && $this->value['block_value'][0] != '' ? $this->value['block_value'][0] : __('Respuesta', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="poll_settings[show_item][col_2]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_2'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_2'] ) ? ' checked ' : '' ).' name="poll_settings[show_item][col_2]" value="on" /></td>
										<td>'.__('Amount of votes received in this answer', 'wvp' ).'</td>
										<td class=""><input type="text" name="poll_settings[block_value][]" value="'.( $this->value['block_value'][1] && $this->value['block_value'][1] != '' ? $this->value['block_value'][1] : __('Cantidad de votos recibidos en esta respuesta', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="poll_settings[show_item][col_3]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_3'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_3'] ) ? ' checked ' : '' ).' name="poll_settings[show_item][col_3]" value="on" /></td>
										<td>'.__('Total shares in this answer', 'wvp' ).'</td>
										<td class=""><input type="text" name="poll_settings[block_value][]" value="'.( $this->value['block_value'][2] && $this->value['block_value'][2] != '' ? $this->value['block_value'][2] : __('Cantidad de acciones que suman los votos que participaron en esta respuesta', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="poll_settings[show_item][col_4]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_4'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_4'] ) ? ' checked ' : '' ).' name="poll_settings[show_item][col_4]" value="on" /></td>
										<td>'.__('%', 'wvp' ).'</td>
										<td class=""><input type="text" name="poll_settings[block_value][]" value="'.( $this->value['block_value'][3] && $this->value['block_value'][3] != '' ? $this->value['block_value'][3] : __('Porcentaje', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="poll_settings[show_item][col_5]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_5'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_5'] ) ? ' checked ' : '' ).' name="poll_settings[show_item][col_5]" value="on" /></td>
										<td>'.__('% of answers of the A+B shares over the quorum value that was present when the poll was closed', 'wvp' ).'</td>
										<td class=""><input type="text" name="poll_settings[block_value][]" value="'.( $this->value['block_value'][4] && $this->value['block_value'][4] != '' ? $this->value['block_value'][4] : __('Porcentaje', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="poll_settings[show_item][col_6]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_6'] == 'on' ? ' checked ' : '' ).'   name="poll_settings[show_item][col_6]" value="on" /></td>
										<td>'.__('Number of voters (Me + Number. of received proxies)', 'wvp' ).'</td>
										<td class=""><input type="text" name="poll_settings[block_value][]" value="'.( $this->value['block_value'][5] && $this->value['block_value'][5] != '' ? $this->value['block_value'][5] : __('Porcentaje', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="poll_settings[show_item][col_7]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_7'] == 'on' ? ' checked ' : '' ).'   name="poll_settings[show_item][col_7]" value="on" /></td>
										<td>'.__('% of answers of the A+B shares over the organization total shares', 'wvp' ).'</td>
										<td class=""><input type="text" name="poll_settings[block_value][]" value="'.( $this->value['block_value'][6] && $this->value['block_value'][6] != '' ? $this->value['block_value'][6] : __('Porcentaje', 'wvp' ) ).'" /></td>
									</tr>
								</tbody>
							</table> 
							
						  </div> 
					</div>';	
				break;

				case "quorum_config":

					$out .= '<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<div class="form-group">  
							<label class="control-label" for="'.$this->parameters['id'].'">'.$this->parameters['title'].'</label>  
							
							<table class="table">
								<thead>
									<tr>
										<th>'.__('Show?', 'wvp' ).'</th>
										<th>'.__('Column', 'wvp' ).'</th>
										<th class="">'.__('Column label - Write your text', 'wvp' ).'</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
										<input type="hidden" name="quorum_settings[show_item][col_1]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_1'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_1'] ) ? ' checked ' : '' ).' name="quorum_settings[show_item][col_1]" value="on" />
										
										
										</td>
										<td>'.__('Name', 'wvp' ).'</td>
										<td class="">
							 
										<input type="text" name="quorum_settings[block_value][]" value="'.( $this->value['block_value'][0] && $this->value['block_value'][0] != '' ? $this->value['block_value'][0] : __('Nombre', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="quorum_settings[show_item][col_2]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_2'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_2'] ) ? ' checked ' : '' ).' name="quorum_settings[show_item][col_2]" value="on" /></td>
										<td>'.__('Last Name', 'wvp' ).'</td>
										<td class=""><input type="text" name="quorum_settings[block_value][1]" value="'.( $this->value['block_value'][1] && $this->value['block_value'][1] != '' ? $this->value['block_value'][1] : __('Apellido', 'wvp' ) ).'" /></td>
									</tr>
									
									<tr>
										<td>
										<input type="hidden" name="quorum_settings[show_item][col_3]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_3'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_3'] ) ? ' checked ' : '' ).' name="quorum_settings[show_item][col_3]" value="on" /></td>
										<td>'.__('User shares (A)', 'wvp' ).'</td>
										<td class=""><input type="text" name="quorum_settings[block_value][2]" value="'.( $this->value['block_value'][2] && $this->value['block_value'][2] != '' ? $this->value['block_value'][2] : __('Votos del usuario', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="quorum_settings[show_item][col_4]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_4'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_4'] ) ? ' checked ' : '' ).' name="quorum_settings[show_item][col_4]" value="on" /></td>
										<td>'.__('Proxies received', 'wvp' ).'</td>
										<td class=""><input type="text" name="quorum_settings[block_value][3]" value="'.( $this->value['block_value'][3] && $this->value['block_value'][3] != '' ? $this->value['block_value'][3] : __('Poderes que ha recibido', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="quorum_settings[show_item][col_5]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_5'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_5'] ) ? ' checked ' : '' ).' name="quorum_settings[show_item][col_5]" value="on" /></td>
										<td>'.__('Total shares of proxies received (B)', 'wvp' ).'</td>
										<td class=""><input type="text" name="quorum_settings[block_value][4]" value="'.( $this->value['block_value'][4] && $this->value['block_value'][4] != '' ? $this->value['block_value'][4] : __('Votos propios + votos de Poderes', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="quorum_settings[show_item][col_9]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_9'] == 'on' ? ' checked ' : '' ).'   name="quorum_settings[show_item][col_9]" value="on" /></td>
										<td>'.__('Show NAME of users that assigned proxies', 'wvp' ).'</td>
										<td class=""><input type="text" name="quorum_settings[block_value][8]" value="'.( $this->value['block_value'][8] && $this->value['block_value'][8] != '' ? $this->value['block_value'][8] : __('Show NAME of users that assigned proxies', 'wvp' ) ).'" /></td>
									</tr>

									<tr>
										<td>
										<input type="hidden" name="quorum_settings[show_item][col_10]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_10'] == 'on' ? ' checked ' : '' ).'   name="quorum_settings[show_item][col_10]" value="on" /></td>
										<td>'.__('Show LAST NAMES of users that assigned proxies', 'wvp' ).'</td>
										<td class=""><input type="text" name="quorum_settings[block_value][]" value="'.( $this->value['block_value'][9] && $this->value['block_value'][9] != '' ? $this->value['block_value'][9] : __('Show LAST NAMES of users that assigned proxies', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="quorum_settings[show_item][col_6]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_6'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_6'] ) ? ' checked ' : '' ).' name="quorum_settings[show_item][col_6]" value="on" /></td>
										<td>'.__('Total A + B', 'wvp' ).'</td>
										<td class=""><input type="text" name="quorum_settings[block_value][5]" value="'.( $this->value['block_value'][5] && $this->value['block_value'][5] != '' ? $this->value['block_value'][5] : __('&nbsp;', 'wvp' ) ).'" /></td>
									</tr>
									
									<tr>
										<td>
										<input type="hidden" name="quorum_settings[show_item][col_7]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_7'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_7'] ) ? ' checked ' : '' ).' name="quorum_settings[show_item][col_7]" value="on" /></td>
										<td>'.__('Username', 'wvp' ).'</td>
										<td class=""><input type="text" name="quorum_settings[block_value][6]" value="'.( $this->value['block_value'][6] && $this->value['block_value'][6] != '' ? $this->value['block_value'][6] : __('Username', 'wvp' ) ).'" /></td>
									</tr>

									<tr>
										<td>
										<input type="hidden" name="quorum_settings[show_item][col_8]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_8'] == 'on' ? ' checked ' : '' ).'   name="quorum_settings[show_item][col_8]" value="on" /></td>
										<td>'.__('Poll category', 'wvp' ).'</td>
										<td class=""><input type="text" name="quorum_settings[block_value][7]" value="'.( $this->value['block_value'][7] && $this->value['block_value'][7] != '' ? $this->value['block_value'][7] : __('Poll category', 'wvp' ) ).'" /></td>
									</tr>

									<tr>
										<td>
										<input type="hidden" name="quorum_settings[show_item][col_11]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_11'] == 'on' ? ' checked ' : '' ).'   name="quorum_settings[show_item][col_11]" value="on" /></td>
										<td>'.__('Number of received proxies + 1', 'wvp' ).'</td>
										<td class=""><input type="text" name="quorum_settings[block_value][]" value="'.( $this->value['block_value'][10] && $this->value['block_value'][10] != '' ? $this->value['block_value'][10] : __('Number of received proxies + 1', 'wvp' ) ).'" /></td>
									</tr>

									
									
								</tbody>
							</table> 
							
						  </div> 
					</div>';	
				break;

				case "quorum_config_table":

					$out .= '<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<div class="form-group">  
							<label class="control-label" for="'.$this->parameters['id'].'">'.$this->parameters['title'].'</label>  
							
							<table class="table">
								<thead>
									<tr>
										<th>'.__('Show?', 'wvp' ).'</th>
										<th>'.__('Row', 'wvp' ).'</th>
										<th class="">'.__('Row label - Write your text', 'wvp' ).'</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
										<input type="hidden" name="quorum_settings_table[show_item][col_1]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_1'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_1'] ) ? ' checked ' : '' ).' name="quorum_settings_table[show_item][col_1]" value="on" />
										
										
										</td>
										<td>'.__('Total current users online at this moment:', 'wvp' ).'</td>
										<td class="">
							 
										<input type="text" name="quorum_settings_table[block_value][]" value="'.( $this->value['block_value'][0] && $this->value['block_value'][0] != '' ? $this->value['block_value'][0] : __('Total current users online at this moment:', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="quorum_settings_table[show_item][col_2]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_2'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_2'] ) ? ' checked ' : '' ).' name="quorum_settings_table[show_item][col_2]" value="on" /></td>
										<td>'.__('Total online shares at this moment:', 'wvp' ).'</td>
										<td class=""><input type="text" name="quorum_settings_table[block_value][]" value="'.( $this->value['block_value'][1] && $this->value['block_value'][1] != '' ? $this->value['block_value'][1] : __('Total online shares at this moment:', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="quorum_settings_table[show_item][col_3]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_3'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_3'] ) ? ' checked ' : '' ).' name="quorum_settings_table[show_item][col_3]" value="on" /></td>
										<td>'.__('% of shares out of the total for our organization, online at this moment:', 'wvp' ).'</td>
										<td class=""><input type="text" name="quorum_settings_table[block_value][]" value="'.( $this->value['block_value'][2] && $this->value['block_value'][2] != '' ? $this->value['block_value'][2] : __('% of shares out of the total for our organization, online at this moment:', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="quorum_settings_table[show_item][col_4]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_4'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_4'] ) ? ' checked ' : '' ).' name="quorum_settings_table[show_item][col_4]" value="on" /></td>
										<td>'.__('% of total current users online out of the total for our organization:', 'wvp' ).'</td>
										<td class=""><input type="text" name="quorum_settings_table[block_value][]" value="'.( $this->value['block_value'][3] && $this->value['block_value'][3] != '' ? $this->value['block_value'][3] : __('% sobre el total de votos de la organización que representan los usuarios autenticados en este momento', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="quorum_settings_table[show_item][col_5]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_5'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_5'] ) ? ' checked ' : '' ).' name="quorum_settings_table[show_item][col_5]" value="on" /></td>
										<td>'.__('Organization total shares :', 'wvp' ).'</td>
										<td class=""><input type="text" name="quorum_settings_table[block_value][]" value="'.( $this->value['block_value'][4] && $this->value['block_value'][4] != '' ? $this->value['block_value'][4] : __('Organization total shares :', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="quorum_settings_table[show_item][col_6]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_6'] == 'on' ? ' checked ' : '' ).' '.( !isset( $this->value['show_item']['col_6'] ) ? ' checked ' : '' ).' name="quorum_settings_table[show_item][col_6]" value="on" /></td>
										<td>'.__('Total online own shares + received shares in proxies at this moment:', 'wvp' ).'</td>
										<td class=""><input type="text" name="quorum_settings_table[block_value][]" value="'.( $this->value['block_value'][5] && $this->value['block_value'][5] != '' ? $this->value['block_value'][5] : __('Total online own shares + received shares in proxies at this moment: ', 'wvp' ) ).'" /></td>
									</tr>


									<tr>
										<td>
										<input type="hidden" name="quorum_settings_table[show_item][col_7]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_7'] == 'on' ? ' checked ' : '' ).'   name="quorum_settings_table[show_item][col_7]" value="on" /></td>
										<td>'.__('Show number of online users per user category:', 'wvp' ).'</td>
										<td class=""><input type="text" name="quorum_settings_table[block_value][]" value="'.( $this->value['block_value'][6] && $this->value['block_value'][6] != '' ? $this->value['block_value'][6] : __('Show number of online users per user category: ', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="quorum_settings_table[show_item][col_8]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_8'] == 'on' ? ' checked ' : '' ).'   name="quorum_settings_table[show_item][col_8]" value="on" /></td>
										<td>'.__('Show number of online total shares (Own + received from proxies) per user category', 'wvp' ).'</td>
										<td class=""><input type="text" name="quorum_settings_table[block_value][]" value="'.( $this->value['block_value'][7] && $this->value['block_value'][7] != '' ? $this->value['block_value'][7] : __('Show number of online total shares (Own + received from proxies) per user category', 'wvp' ) ).'" /></td>
									</tr>

									<tr>
										<td>
										<input type="hidden" name="quorum_settings_table[show_item][col_9]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_9'] == 'on' ? ' checked ' : '' ).'   name="quorum_settings_table[show_item][col_9]" value="on" /></td>
										<td>'.__('% of own shares + received shares in proxies out of the total for our organization, online at this moment', 'wvp' ).'</td>
										<td class=""><input type="text" name="quorum_settings_table[block_value][]" value="'.( $this->value['block_value'][8] && $this->value['block_value'][8] != '' ? $this->value['block_value'][8] : __('% of own shares + received shares in proxies out of the total for our organization, online at this moment', 'wvp' ) ).'" /></td>
									</tr>
									<tr>
										<td>
										<input type="hidden" name="quorum_settings_table[show_item][col_10]" value="off" />
										<input type="checkbox" '.( $this->value['show_item']['col_10'] == 'on' ? ' checked ' : '' ).'   name="quorum_settings_table[show_item][col_10]" value="on" /></td>
										<td>'.__('Number of online users + number of proxies received by them', 'wvp' ).'</td>
										<td class=""><input type="text" name="quorum_settings_table[block_value][]" value="'.( $this->value['block_value'][9] && $this->value['block_value'][9] != '' ? $this->value['block_value'][9] : __('Number of online users + number of proxies received by them', 'wvp' ) ).'" /></td>
									</tr>
									

									 
								</tbody>
							</table> 
							
						  </div> 
					</div>';	
				break;
				
				case "poll_variants":
					
					$poll_variants = (array) get_post_meta( $post->ID, 'poll_variants', true );
					$poll_variants_check = (array) get_post_meta( $post->ID, 'poll_variants_check', true );

			 
					if( count($poll_variants) == 0 || $poll_variants[0] == ''  ){
						$settings = get_option('wvp_variants_options');
						$settings = $settings['default_answer_variants'];
						
						$rows = explode("\n", $settings);
						$poll_variants = array_filter( $rows );
					}
			 
					$out .= '
					
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<h4>'.$this->parameters['title'].'</h4> 

						<div class="control_line text-right">
							<button type="button" class="btn btn-success add_variant">'.__('Add Variant', 'wvp' ).'</button>
						</div>

						<div class="vat_tab">
						<table class="table vote_table">
							<thead>
								<tr>
									<td>'.__('Variant', 'wvp').'</td>
									<td  class="is_multi">'.__('Choose ON to disable all other variants when user selects it', 'wvp').'</td>
									<td>'.__('Action', 'wvp').'</td>
								</tr>
							</thead>
							<tbody class="">';



							if( count($poll_variants) > 0 ){
								$index = 0;
								foreach( $poll_variants as $s_var ){
									$out .= '
									<tr>
										<td class="">
											<input type="text" name="poll_variants[]" class="form-control" value="'.htmlentities( stripslashes( $s_var ) ).'">
										</td>
										<td class="is_multi">
											<select name="poll_variants_check[]" class="form-control">
												<option value="off" '.( $poll_variants_check[$index] == 'off' ? ' selected ' : '' ).' >'.__('Off', 'wvp').'
												<option value="on" '.( $poll_variants_check[$index] == 'on' ? ' selected ' : '' ).' >'.__('On', 'wvp').'
											</select>
										</td>
										<td class="">
										<button type="button" class="btn btn-danger variant_line_remove">'.__('Remove', 'wvp' ).'</button>
										</td>
									</tr>
									';
									$index++;
								}
							}else{
								$out .= '
								<tr>
									<td class="">
									<input type="text" name="poll_variants[]" class="form-control">
									</td>
									<td class="">
									<button type="button" class="btn btn-danger variant_line_remove">'.__('Remove', 'wvp' ).'</button>
									</td>
								</tr>';
							}
							
							
							$out .= '
							</tbody>
						</table>
						</div>


						<div class="control_line text-right"  >
						<a href="#"  class="btn btn-danger  delete_all_results_fake">'.__('DELETE ALL CURRENT ANSWERS IN THIS POLL?', 'wvp' ).'</a>
						</div>
						<div class="control_line text-right delete_all_orig" style="display:none;">
						<div><b>'.__('Are you sure? This action is not reversible', 'wvp').'</b></div>
						<a href="'.admin_url('/post.php?post='.(int)$_GET['post'].'&action=edit&delete_answers=1').'" onClick="return confirm(\''.__('Are you sure? This action is not reversible', 'wvp').'\')" class="btn btn-danger  ">'.__('DELETE ALL CURRENT ANSWERS IN THIS POLL?', 'wvp' ).'</a>
						</div>

					</div>
						';
				break;

				
				case "reporting":
					global $wpdb;
					
					$out .= '
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">';

					$out .= '
					<h5>'.__('Poll results reports','wvp').'</h5>
					<ul>
						<li><a href="'.admin_url('edit.php?post_type=poll&page=wvp_dataexport_poll_results').'">'.__('Export all poll results', 'wvp').'</a></li>
						<li><a href="'.admin_url('edit.php?post_type=poll&page=wvp_dataexport_poll_results_csv').'">'.__('Export single CSV with results summary', 'wvp').'</a></li>
						<!-- <li><a href="'.admin_url('edit.php?post_type=poll&page=wvp_dataexport_poll_results_with_pdf').'">'.__('Export PDF with all results', 'wvp').'</a></li> -->
					</ul>


					<h5>'.__('Quorum reports','wvp').'</h5>
					<ul>
						<li><a href="'.admin_url('edit.php?post_type=poll&page=wvp_datadata_log').'">'.__('Online users in time', 'wvp').'</a></li>
						<!-- <li><a href="'.admin_url('edit.php?post_type=poll&page=wvp_dataquorum_users').'">'.__('Quorum users', 'wvp').'</a></li> -->
						<li><a href="'.admin_url('edit.php?post_type=poll&page=wvp_dataquorum_users2').'">'.__('Users that logged in at least once in a time range', 'wvp').'</a></li>
						<li><a href="'.admin_url('edit.php?post_type=poll&page=wvp_data_online_user_log').'">'.__('Online users log', 'wvp').'</a></li>
					</ul>

					<h5>'.__('Email Reports','wvp').'</h5>
					<ul>
						<li><a href="'.admin_url('edit.php?post_type=poll&page=wvp_dataemail_log').'">'.__('Sent emails report', 'wvp').'</a></li>
						<li><a href="'.admin_url('edit.php?post_type=poll&page=wvp_dataemail_open_report').'">'.__('Email open report', 'wvp').'</a></li>
					</ul>
					<hr>
					<a href="'.admin_url('edit.php?post_type=poll&page=wvp_data_reports&action=drop_database').'" onclick="return confirm(\''.__('Are you sure? This is not reversible', 'wvp').'\')">'.__('Clear Database','wvp').'</a>
					';

					$out .= '
					</div>';
				 
				break;

				case "proxy_admin":
					global $wpdb;
					
					$out .= '
			 
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">';
					$out .= '

						
					<form action="'.admin_url(  ).'" method="POST" id="assign_proxy" >
					<input type="hidden" name="_admin_assign_share"  value="1" />
					<div class="form-group">  
						<label>'.__('Select user that GIVES the proxy ', 'wvp').'</label>
						
						<select name="_user_from" id="_user_from" class="selectizer form-control1" required>';
						$all_users_that_caN_vote = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '1' " );
					
						$out .= '<option value="">'.'';

						foreach( $all_users_that_caN_vote as $s_user ){
							$user_data = get_user_by('ID', $s_user );
							$out .= '<option value="'.$s_user.'">'.$user_data->first_name.' - '.$user_data->last_name.' - '.$user_data->user_login.'</option>';
						}

						$out .= '
						</select>
					
					</div>
					<div class="form-group">  
						<label>'.__('Select user that RECEIVES the proxy  ', 'wvp').'</label>
						
						<select name="_user_to" id="_user_to" class="selectizer form-control1" required>';
						$all_users_that_caN_vote = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '1' " );

						$out .= '<option value="">'.'';
						foreach( $all_users_that_caN_vote as $s_user ){
							$user_data = get_user_by('ID', $s_user );
							$out .= '<option value="'.$s_user.'">'.$user_data->first_name.' - '.$user_data->last_name.' - '.$user_data->user_login.'</option>';
						}

						$out .= '
						</select>
					
					</div>
			 

					<button class="btn btn-success">'.__('Assign', 'wvp').'</button>
				</form>
				<br/><br/>

						<table class="table sortable_table">
							<thead>
								<tr>
									<th>'.__('#', 'wvp').'</th>
									<th>'.__('User that gave proxy', 'wvp').'</th>
									<th>'.__('User that received the proxy', 'wvp').'</th>
									<th>'.__('Given shares', 'wvp').'</th>
									<th>'.__('Date of action', 'wvp').'</th>
									<th>'.__('Proxy was given by user or admin?', 'wvp').'</th>
									<th>'.__('Attach documents', 'wvp').'</th>
									<th>'.__('Message', 'wvp').'</th>
									<th>'.__('Is external', 'wvp').'</th>
									<th>'.__('Actions', 'wvp').'</th>							 
								</tr>
							</thead>
							<tbody class="">';
							
							$counter = 0;
							$all_users_that_have_assigns = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'assign_date' AND meta_value != '1' " );
							foreach( $all_users_that_have_assigns as $s_user ){
								$counter++;

								$sender_data = get_user_by('ID', $s_user);
								$acceptor_data = get_user_by('ID', get_user_meta( $s_user, 'donate_recipient', true )  );
								$out .= '
								<tr>
									<th>'.$counter.'</th>
									<th>'.$sender_data->first_name.' - '.$sender_data->last_name.' - '.$sender_data->user_login.'</th>
									<th>'.$acceptor_data->first_name.' - '.$acceptor_data->last_name.' - '.$acceptor_data->user_login.'</th>
									<th>'.get_user_meta( $s_user, 'own_shares_old', true ).'</th>
									<th>'.date( 'Y/m/d H:i', get_user_meta( $s_user, 'assign_date', true ) ).'</th>
									<th>'.get_user_meta( $s_user, 'assign_type', true ) .'</th>
									<th>';
									
									$attached_files =  explode(',', get_user_meta( $s_user, 'sendproxy_files', true ) ); 
									$out_links = [];
									foreach( $attached_files as $s_file ){
										$out_links[] = '<a href="'.$s_file.'" target="_blank">'.basename( $s_file ).'</a>';
									}
									
									$out .= implode( ', ', $out_links ).'</th>
									<th>'.stripslashes( get_user_meta( $s_user, 'sendproxy_comment', true ) ) .'</th>
									<th>'.( get_user_meta( $s_user, 'is_external', true ) == '1' ? __('Yes', 'wvp') : __('No', 'wvp') ) .'</th>
									<th>
									

									<form action="'.admin_url().'" method="POST" >
										<input type="hidden" name="_admin_change_acceptor"  value="1" />

										<input type="hidden" name="_user_from"  value="'.$sender_data->ID.'" />
										<input type="hidden" name="_user_to"  value="'.$acceptor_data->ID.'" />


										<select name="_user_switch_to" class="selectizer " required>';
											$all_users_that_caN_vote = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '1' " );
										
											$out .= '<option value="">'.'';
			
											foreach( $all_users_that_caN_vote as $s_user ){
												$user_data = get_user_by('ID', $s_user );
												$out .= '<option value="'.$s_user.'">'.$user_data->first_name.' - '.$user_data->last_name.' - '.$user_data->user_login.'</option>';
											}
			
											$out .= '
											</select>

										<button class="btn btn-info">'.__('Change recipient','wvp').'</button>
									</form>
											<br/>

									<form action="'.admin_url().'" method="POST" >
										
										
									
										<input type="hidden" name="_user_from"  value="'.$sender_data->ID.'" />
										<input type="hidden" name="_user_to"  value="'.$acceptor_data->ID.'" />

										<input type="hidden" name="_admin_revoke_share"  value="1" />

									

										<button class="btn btn-danger">'.__('Remove Proxy','wvp').'</button>
									</form>
									</th>							 
								</tr>
								';
							}
					$out .= '
							</tbody>
						</table>

						

					';
					$out .= '
						<div class="row">
							<div class="col-12 text-right">
								<a href="'.admin_url( 'edit.php?post_type=poll&page=wvp_dataproxy_admin&action=export_proxy_data' ).'" class="btn btn-info">'.__('Export to CSV', 'wvp').'</a>
							</div>
						</div>

					</div>';
				 
				break;

				case "form_start":
					$out .= '<form method="POST" action="">'.wp_nonce_field( $this->setttings_prefix.'save_settings_action', $this->setttings_prefix.'save_settings_field', true, false  ); ;
				break;
				case "form_end":
					$out .= '</form><br/><br/>';
				break;
				case "voting_info":
					$out .= '
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
					
					<br/>
					<br/>

					<h4>'.$this->parameters['title'].'</h4>';
					//vote info
					global $wpdb;
					
					// get all votes
				$all_votes = $wpdb->get_results("SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = {$post->ID} AND meta_key LIKE '%user\\_%'");
				
				$poll_variants = (array) get_post_meta( $post->ID, 'poll_variants', true );

				$pols_count = count( $poll_variants );

				
				$all_results = [];
				$all_votes = [];
				$total_votes_count = 0;
				$totla_count = 0;
				/*
				for( $i=0; $i<$pols_count; $i++ ){
					//$all_votes = $wpdb->get_results("SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = {$post->ID} AND meta_key LIKE '%user_%' AND meta_value = {%i}");
					$votes_count = $wpdb->get_var("SELECT count(meta_value) FROM {$wpdb->prefix}postmeta WHERE post_id = {$post->ID} AND meta_key LIKE '%user_%' AND  meta_value = {$i}");
					 
					$all_results[$i] = $votes_count;
					$totla_count = $totla_count + $votes_count;
				}
 
				foreach( $all_votes as $s_vote ){

				}
				*/
				for( $i=0; $i<$pols_count; $i++ ){
					//$all_votes = $wpdb->get_results("SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = {$post->ID} AND meta_key LIKE '%user_%' AND meta_value = {%i}");
					$total_votes = $wpdb->get_col("SELECT meta_key FROM {$wpdb->prefix}postmeta WHERE post_id = {$post->ID} AND meta_key LIKE 'user\\_%' AND  meta_value = {$i} AND meta_id NOT IN ( SELECT meta_id FROM {$wpdb->prefix}postmeta WHERE post_id = {$post->ID} AND meta_key LIKE 'user_%_ip%' AND  meta_value = {$i}  )");
					//var_dump( "SELECT meta_key FROM {$wpdb->prefix}postmeta WHERE post_id = {$post_id} AND meta_key LIKE 'user_%' AND  meta_value = {$i} AND post_id NOT IN ( SELECT post_id FROM {$wpdb->prefix}postmeta WHERE post_id = {$post_id} AND meta_key LIKE 'user_%_ip%' AND  meta_value = {$i}  )" );
					
					$total_votes = array_unique( $total_votes );
					$all_votes[$i] = count( $total_votes );

					$total_votes_count = $total_votes_count + count( $total_votes );
				
					if( count( $total_votes ) > 0 )
					foreach( $total_votes as $s_vote ){
						$tmp = explode('_', $s_vote);
						$user_shares = get_own_shares( $tmp[1]  ); 
						$total_shares = get_total_shares( $tmp[1] );  
						$all_results[$i] = $all_results[$i] + $user_shares + $total_shares;
					}
					 
					//$all_results[$i] = $votes_count;
					$totla_count = $totla_count + $all_results[$i];
				}
					
			 
				// customize poll outptu
				$output_options = get_option('wvp_extra_options');
				$columns_data = $output_options['poll_settings']['show_item'];
				$labels_data = $output_options['poll_settings']['block_value'];


				$out .= '
				<table class="table text-center">
					<thead class="">
						<tr>';
						if( $columns_data['col_1'] == 'on' ){
							$out .= '<th class="">'.$labels_data[0].'</th>';
						}
						if( $columns_data['col_2'] == 'on' ){
							$out .= '<th class="">'.$labels_data[1].'</th>';
						}
						if( $columns_data['col_3'] == 'on' ){
							$out .= '<th class="">'.$labels_data[2].'</th>';
						}
						if( $columns_data['col_4'] == 'on' ){
							$out .= '<th class="">'.$labels_data[3].'</th>';
						}
						$out .= '
						</tr>
					</thead>
					<tbody class="">';
					
					

					if( count($poll_variants) > 0 ){
						$cnt = 0;

						
						foreach( $poll_variants as $s_var ){

							// count percentage 
					 
							if( !$totla_count || $totla_count  == 0 ){
								$percent = 0;
							}else{
								$percent = $all_results[$cnt]*100 / $totla_count;
							}
							
							$out .= '
							<tr>';
							if( $columns_data['col_1'] == 'on' ){
							$out .= '
								<td class=""><h6>'.$s_var.'</h6>
								<div class="progress">
								<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="'.$percent.'" style="width: '.$percent.'%"></div>
								</div>
								</td>';
							}
							if( $columns_data['col_2'] == 'on' ){
							$out .= '
								<td class="text-center">'.$all_votes[$cnt].'</td>';
							}
							if( $columns_data['col_3'] == 'on' ){
								$out .= '
								<td class="text-center">'.$all_results[$cnt].'</td>';
							}
							if( $columns_data['col_4'] == 'on' ){
								$out .= '
								<td class="text-center">'.number_format( $percent, 2 ).'</td>';
							}
								$out .= '
							</tr>';
							$cnt++;
						}
					}
				 
					$out .= '
					<tr>
						<td class="">'.__('Total', 'wvp').'</td>
						<td class="text-center">'.$total_votes_count.'</td>
						<td class="text-center">'.$totla_count.'</td>
						<td class="text-center">100%</td>
					</tr>';
				 
					$out .= '
					</tbody>
				</table>
				';
				if( $output_options['lite_mode'] == 'no' ){
					$out .= '	
					<br/>
					<br/>
					<h4>'.__('Voted Users', 'wvp').'</h4>
					<table class="table">
						<thead class="">
						<tr>
							<th class="">'.__('Username', 'wvp').'</th>
							<th class="">'.__('First name', 'wvp').'</th>
							<th class="">'.__('Last Name', 'wvp').'</th>
							<th class="">'.__('Own Shares (A)', 'wvp').'</th>
							<th class="">'.__('Proxys amount received by user', 'wvp').'</th>
							<th class="">'.__('Total shares of received proxys 
							(B)', 'wvp').'</th>
							<th class="">'.__('Total Shares (A + B)', 'wvp').'</th>
							<th class="">'.__('Answer given by user', 'wvp').'</th>
							<th class="">'.__('Date-time of answer', 'wvp').'</th>
							<th class="">'.__('User IP', 'wvp').'</th>
							<th class="">'.__('User Browser', 'wvp').'</th>
							<th class="">'.__('User Message', 'wvp').'</th>
							<th class="">'.__('Admin action', 'wvp').'</th>
						</tr>
						</thead>
						<tbody class="">';

						$all_users = get_users();
						$total_results = [];
					 
						foreach( $all_users as $s_user ){

						// show only can vote users
						$user_can_vote = get_user_meta( $s_user->ID, 'can_vote', true );
						//if( $user_can_vote != '1' ){ continue; }


						$val_a = get_own_shares( $s_user->ID  ); 
						$val_b = get_total_shares( $s_user->ID );

						$val_a_b = $val_a + $val_b;

						$user_answer =  get_post_meta( $post->ID, 'user_'.$s_user->ID, true );
						$poll_type =  get_post_meta( $post->ID, 'poll_type', true );
					 
						if( $poll_type == 'multi' ){
							$all_votes = get_post_meta( $post->ID, 'user_'.$s_user->ID );
							$out_votes = [];
							foreach( $all_votes as $s_vote ){
								$total_results[$s_vote] = (float)$total_results[$s_vote] + $val_a_b;
							}
						}else{
							$total_results[$user_answer] = (float)$total_results[$user_answer] + $val_a_b;
						}

						//prefind if user voted

						$user_vote = get_post_meta( $post->ID, 'user_'.$s_user->ID, true );

			
						if(  $user_vote != '' ){
							//$visible_class = ' ';
						}else{
							//$visible_class = 'd-none not-voted';
							continue;
						}
				 
						
						$out .= '
						<tr class="'.$visible_class.'">
							<td class="">'.$s_user->user_login.'</td>
							<td class="">'.$s_user->first_name.'</td>
							<td class="">'.$s_user->last_name.'</td>
							<td class="">'.$val_a.'</td>
							<td class="">'.get_proxys_amount( $s_user->ID ).'</td>
							<td class="">'.$val_b.'</td>
							<td class="">'.$val_a_b.'</td>';
				 
							// patch for multipol
							if( $poll_type == 'multi' ){
								$all_votes = get_post_meta( $post->ID, 'user_'.$s_user->ID );
								$out_votes = [];
								foreach( $all_votes as $s_vote ){
									$out_votes[] = $poll_variants[$s_vote];
								}
							 
								$out .= '
								<td class="">'.implode( ', ', $out_votes ) ;
							}else{
								$out .= '
								<td class="">'.( isset($poll_variants[$user_answer]) && $poll_variants[$user_answer] != ''  ? $poll_variants[$user_answer] : ' - ' ) ;
							}

							$out .= '
							<div class="edit_container">
								<select class="answer_variant" '.( $poll_type == 'multi' ? ' multiple ' : '' ).' data-url="'.admin_url('post.php?post='.$post->ID.'&action=edit&vote_user='.$s_user->ID ).'">';
								$cnt = 0;
								foreach( $poll_variants as $s_var ){
									$out .= '<option value="'.$cnt.'">'.$s_var;
									$cnt++;
								}
								$out .= '</select>
								<button type="button" class="btn btn-info save_result_block">'.__('Save', 'wvp').'</button>
							</div>
							';

							$out .= '</td>';
							$vote_date = date( 'Y/m/d H:i:s', (int)get_post_meta( $post->ID, 'uservotetime_'.$s_user->ID, true ) );


							$user_ip = $wpdb->get_var("SELECT meta_key FROM {$wpdb->postmeta} WHERE meta_key LIKE 'user\\_{$s_user->ID}\\_ip%'");
							$user_ip = explode("ip", $user_ip);
							$user_ip = $user_ip[1];

							$out .= '<td>'.$vote_date.'</td>
							<td>'.$user_ip.'</td>';

							$user_browser = get_post_meta( $post->ID, 'browser_'.$s_user->ID, true );
							$out .= '
							<td>'.$user_browser.'</td>';

							$message = get_post_meta( $post->ID, 'usermessage_'.$s_user->ID, true );
							$out .= '<td>'.$message.'</td>';
							$out .= '
							<td class=""> <a href="#" data-user="'.$s_user->ID.'" data-poll="'.$post->ID.'" class="edit_user_vote">'.__('Edit', 'wvp').'</a> / <a href="'.admin_url( 'post.php?post='.$post->ID.'&action=edit&delete_vote='.$s_user->ID ).'" class="delete_user_vote" data-user="'.$s_user->ID.'" data-poll="'.$post->ID.'" onclick="return confirm(\''.__('Are you sure? This is not reversible', 'wvp').'\')" >'.__('Delete', 'wvp').'</a> </td>
						</tr>';
						}
						$out .= '
						</tbody>
						<tfoot class="">
							<tr>
								<td colspan="12" class="text-right">
									<!-- <button type="button" class="btn btn-warning show_unvoted">'.__('Show Not Voted Users', 'wvp').'</button> -->

									<a href="'.admin_url( '/post.php?post='.$post->ID.'&action=edit&export=csv_voted' ).'" class="btn btn-info">'.__('Export to CSV', 'wvp').'</a>
								</td>
							</tr>

							<tr>
								<td colspan="1"><b>'.__('Total:').'</b></td>
								<td colspan="8">';
							 
								$out .= '<ul>';
								$cnt = 0;
								foreach( $poll_variants as $s_var ){
									$out .= '<li>'.$s_var.': '.(float)$total_results[$cnt].'</li>';
									$cnt++;
								}
								$out .= '</ul>';

								$out .= '</td>
							</tr>
						</tfoot>
					</table>';
					} // lite mode check

					// opening closing time
					$out .= '	
					<br/>
					<br/>
					<h4>'.__('Current quorum when poll was closed:', 'wvp').'</h4>
					<table class="table">
						<thead class="">
						<tr>
							<th class="">'.__('Closing time', 'wvp').'</th>
							<th class="">'.__('“total online shares a+b”  at closing times of the poll', 'wvp').'</th>
							<th class="">'.__('Action', 'wvp').'</th>
	 
						</tr>
						</thead>
						<tbody class="">';
						$all_times[] = (array)get_post_meta( $post->ID,  'vote_close_time', true );
							
						foreach( $all_times as $index =>  $s_time ){
							if( $s_time  == '' ){ continue; }
						
							$out .= '
							<tr>
								<td>'.date( 'Y/m/d H:i:s', (int)$s_time['time'] ).'</td>
								<td>'.$s_time['shares'].'</td>
								<td><button data-id="'.$post->ID.'" data-index="'.$index.'" type="button" class="btn btn-danger remove_poll_date">'.__('Delete', 'wvp').'</button></td>
							</tr>
							';
						}
					$out .= '
						</tbody>
					</table>
					';

					if( $output_options['lite_mode'] == 'no' ){
					// not voted output
					$out .= '	
					<br/>
					<br/>
					<h4>'.__('Not voted', 'wvp').'</h4>
					<table class="table">
						<thead class="">
						<tr>
							<th class="">'.__('Username', 'wvp').'</th>
							<th class="">'.__('First name', 'wvp').'</th>
							<th class="">'.__('Last Name', 'wvp').'</th>
							<th class="">'.__('Own Shares (A)', 'wvp').'</th>
							<th class="">'.__('Proxys amount received by user', 'wvp').'</th>
							<th class="">'.__('Total shares of received proxys 
							(B)', 'wvp').'</th>
							<th class="">'.__('Total Shares (A + B)', 'wvp').'</th>
							<th class="">'.__('Answer given by user', 'wvp').'</th>
							<th class="">'.__('Date-time of answer', 'wvp').'</th>
							<th class="">'.__('Admin action', 'wvp').'</th>
						</tr>
						</thead>
						<tbody class="">';

						$not_voted_cont = 0;
						$all_users = get_users();
						$total_results = [];
					 
						foreach( $all_users as $s_user ){

						// show only can vote users
						$user_can_vote = get_user_meta( $s_user->ID, 'can_vote', true );
						if( $user_can_vote != '1' ){ continue; }

				 

						$val_a = get_own_shares( $s_user->ID  ); 
						$val_b = get_total_shares( $s_user->ID );

						$val_a_b = $val_a + $val_b;

						$user_answer =  get_post_meta( $post->ID, 'user_'.$s_user->ID, true );
						$poll_type =  get_post_meta( $post->ID, 'poll_type', true );
					 
						if( $poll_type == 'multi' ){
							$all_votes = get_post_meta( $post->ID, 'user_'.$s_user->ID );
							$out_votes = [];
							foreach( $all_votes as $s_vote ){
								$total_results[$s_vote] = (float)$total_results[$s_vote] + $val_a_b;
							}

							
						}else{
							$total_results[$user_answer] = (float)$total_results[$user_answer] + $val_a_b;
						}

						//prefind if user voted

						$user_vote = get_post_meta( $post->ID, 'user_'.$s_user->ID, true );

			
						if(  $user_vote != '' ){
							//$visible_class = ' ';
							continue;
						}else{
							//$visible_class = 'd-none not-voted';
						}
						$not_voted_cont++;
						
						$out .= '
						<tr class="'.$visible_class.'">
							<td class="">'.$s_user->user_login.'</td>
							<td class="">'.$s_user->first_name.'</td>
							<td class="">'.$s_user->last_name.'</td>
							<td class="">'.$val_a.'</td>
							<td class="">'.get_proxys_amount( $s_user->ID ).'</td>
							<td class="">'.$val_b.'</td>
							<td class="">'.$val_a_b.'</td>';
				 
							// patch for multipol
							if( $poll_type == 'multi' ){
								$all_votes = get_post_meta( $post->ID, 'user_'.$s_user->ID );
								$out_votes = [];
								foreach( $all_votes as $s_vote ){
									$out_votes[] = $poll_variants[$s_vote];
		
								}
							 
								$out .= '
								<td class="">'.implode( ', ', $out_votes ) ;
							}else{
								$out .= '
								<td class="">'.( isset($poll_variants[$user_answer]) && $poll_variants[$user_answer] != ''  ? $poll_variants[$user_answer] : ' - ' ) ;
							}

							$out .= '
							<div class="edit_container">
								<select class="answer_variant" '.( $poll_type == 'multi' ? ' multiple ' : '' ).' data-url="'.admin_url('post.php?post='.$post->ID.'&action=edit&vote_user='.$s_user->ID ).'">';
								$cnt = 0;
								foreach( $poll_variants as $s_var ){
									$out .= '<option value="'.$cnt.'">'.$s_var;
									$cnt++;
								}
								$out .= '</select>
								<button type="button" class="btn btn-info save_result_block">'.__('Save', 'wvp').'</button>
							</div>
							';

							$out .= '</td>';
							$timestamp = (int)get_post_meta( $post->ID, 'uservotetime_'.$s_user->ID, true );
							if( $timestamp !== 0 ){
								$vote_date = date( 'Y/m/d H:i:s',  (int)$timestamp);
							}else{
								$vote_date = ' - ';
							}
							

							$out .= '  <td>'.$vote_date.'</td> 
							<td class=""> <a href="#" data-user="'.$s_user->ID.'" data-poll="'.$post->ID.'" class="edit_user_vote">'.__('Edit', 'wvp').'</a> / <a href="'.admin_url( 'post.php?post='.$post->ID.'&action=edit&delete_vote='.$s_user->ID ).'" class="delete_user_vote" data-user="'.$s_user->ID.'" data-poll="'.$post->ID.'" >'.__('Delete', 'wvp').'</a> </td>
						</tr>';
						}
						$out .= '
						</tbody>
						
						<tfoot class="">
							<tr>
								<td colspan="9" class="text-right">
									<!-- <button type="button" class="btn btn-warning show_unvoted">'.__('Show Not Voted Users', 'wvp').'</button> -->

									<a href="'.admin_url( '/post.php?post='.$post->ID.'&action=edit&export=csv_not_voted' ).'" class="btn btn-info">'.__('Export to CSV', 'wvp').'</a>
								</td>
							</tr>
						 
							<tr>
								<td colspan="3"><b>'.__('Total users that have not voted in this poll:', 'wvp').'</b></td>
								<td colspan="6">';
							 
								$out .= $not_voted_cont;

								$out .= '</td>
							</tr> 
						</tfoot> 
					</table>';
					} // hide non voted for lite mode
					$out .= '
					</div>
						';
				break;
				case "separator":
					$out .= '
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<div class="lead">'.$this->parameters['title'].'</div> 
					</div>
						';
				break;

				case "settings_break":
					$out .= '
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<div class="h3 mt-4 mb-2">'.$this->parameters['title'].'</div> 
						<p class=" ">'.$this->parameters['subtitle'].'</p> 
					</div>
						';
				break;
				case "export_poll_results":
					$out .= '
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<h4>'.__('Export and download in separate CSV files (one for each poll),  the poll results of all polls with at least 1 answer', 'wvp').'</h4>	
						<form method="GET" action="">

						<input type="hidden" name="export_type"  value="full_answers" />

						<div class="row">
					  
				 
								<div class="col-4">
								<label class="control-label" for="'.$this->parameters['id'].'">'.__('Do you want to include user answers?','wvp').'</label> 
									<!--
									<select name="include_answers" id="include_answers" class="form-control">
										<option value="yes">'.__('Yes','wvp').'</option>
										<option value="no">'.__('No','wvp').'</option>
									</select>
									-->

									<select name="include_field[]" id="include_field" class="form-control" multiple style="height:300px;">';

									$fields = array( 
										__('Username', 'wvp'), 
										__('First name', 'wvp'),   
										__('Last Name', 'wvp'),   
										__('Own Shares (A)', 'wvp'),   
										__('Proxys amount received by user', 'wvp'),   
										__('Total shares of received proxys (B)', 'wvp'),   
										__('Total Shares (A + B)', 'wvp'),   
										__('Answer given by user', 'wvp'),   
										__('Date-time of answer', 'wvp'),   
										__('User IP', 'wvp'),   
										__('User Browser', 'wvp'),   
										__('User Message', 'wvp'),   
										__('User Category', 'wvp'),   
									);
									$fields_eng = array( 
										 'Username', 
										 'First name', 
										 'Last Name',
										 'Own Shares (A)',
										 'Proxys amount received by user',
										 'Total shares of received proxys (B)',
										 'Total Shares (A + B)', 
										 'Answer given by user', 
										 'Date-time of answer', 
										 'User IP',
										 'User Browser', 
										 'User Message', 
										 'User Category', 
									);
									$cnt_innner = 0;
									foreach( $fields as $s_field ){
										$out .= '<option value="'.sanitize_title( $fields_eng[$cnt_innner] ).'" selected>'.$s_field.'</option>';
										$cnt_innner++;
									}
								 
									$out .= '
									</select>
								</div>

								<div class="col-4">
								<label class="control-label" for="'.$this->parameters['id'].'">'.__('Select poll category to export','wvp').'</label> 
									<select name="poll_category" id="poll_category" class="form-control">';
										$out .= '<option value="all">'.__('All','wvp').'</option>';
										$all_terms = get_terms(array(
											'taxonomy' => 'poll_category',
											'count' => true
										));
								
										foreach( $all_terms as $s_term ){
											$out .= '<option value="'.$s_term->term_id.'">'.$s_term->name.'</option>';
										}
										
										$out .= '
									</select>
								</div>


								<div class="col-4">
								<label class="control-label" for="'.$this->parameters['id'].'">&nbsp;</label> 
									<button class="btn btn-success form-control">'.__('Export','wvp').'</button>
								</div>
						
						</div>
						</form>
					</div>
						';
				break;
				case "export_poll_results_with_csv":
					$out .= '
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
				 
						<div class="row">
							<div class="col-12">
								<b>'.str_replace( '%link%', admin_url( 'edit.php?post_type=poll&page=wvp_extrasettings' ), __('This will allow you to create and download a CSV file with the results of all polls with at least one answer, including the COLUMNS and ROWS that you have selected at EXTRA-SETTINGS <a href="%link%">(Click here to modify them)</a>.', 'wvp') ).'</b>
							</div>
						</div>	
						<form method="GET" action="">
							<div class="row mt-4 mb-4">
								<div class="col-6">
									<a class="btn btn-success" href="'.get_option('home').'?action=save_csv_data_block">'.__('Create CSV file','wvp').'</a>
								</div>
							 
							</div>
						</form>
					</div>
						';
				break;
				case "export_poll_results_with_pdf":
					$out .= '
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
				 
						<div class="row">
							<div class="col-12">
								<b>'.str_replace( '%link%', admin_url( 'edit.php?post_type=poll&page=wvp_extrasettings' ), __('This will allow you to create and download a CSV file with the results of all polls with at least one answer, including the COLUMNS and ROWS that you have selected at EXTRA-SETTINGS <a href="%link%">(Click here to modify them)</a>.', 'wvp') ).'</b>
							</div>
						</div>	
						<form method="GET" action="">
							<div class="row mt-4 mb-4">
						 
								<div class="col-6">
									<a class="btn btn-success" href="'.get_option('home').'?action=save_pdf_data_block">'.__('Export PDF with all results','wvp').'</a>
								</div>
							</div>
						</form>
					</div>
						';
				break;
				case "quorum_users2":
				 
					global $wpdb;
					$table_name = 'online_users_log';
					$table_name =  $wpdb->prefix.$table_name;

					$out .= '
					</form>
					<style>
					#wpwrap{
						background:#fff;
					}
					</style>
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">';
						

					$out .= '
					<form action="" >
					<div class="row">
						<input name="post_type" value="poll" type="hidden" />
						<input name="page" value="wvp_dataquorum_users2" type="hidden" />
						<div class="col-4">
						<label class="control-label">'.__('Select start date / time:','wvp').'</label>
						<input type="text" autocomplete="off" class="form-control datepicker" name="user_start_date" value="'.sanitize_text_field( $_GET['user_start_date'] ).'" />
						</div>
						<div class="col-4">
						<label class="control-label">'.__('Select end date / time:','wvp').'</label>
						<input type="text" autocomplete="off" class="form-control datepicker" name="user_end_date" value="'.sanitize_text_field( $_GET['user_end_date'] ).'" />
						</div>
						<div class="col-4">
							<button class="btn btn-success mt-4">'.__('Submit','wvp').'</button>
						</div>
					
					</div>
					</form>
					';

					global $wpdb;
		 
					

					$out .= '
					<br/><br/>
						<table class="table">
							<thead class="">
								<tr>
									<th class="text-center">'.__('Name','wvp').'</th>
									<th class="text-center">'.__('Last Name','wvp').'</th>
									<th class="text-center">'.__('Username','wvp').'</th>
									<th class="text-center">'.__('Total shares A+B','wvp').'</th>
						 
								</tr>
							</thead>
							<tbody>';

							//$all_users_that_caN_vote = $wpdb->get_col("SELECT user_id   FROM {$table_name} WHERE meta_key = 'can_vote' AND meta_value = '1' " );
							

							if( isset( $_GET['user_start_date'] ) ){
								$start_date = strtotime( $_GET['user_start_date'] );
							}else{
								$start_date = date( 'Y/m/d 00:00' );
							}
							

							if( isset( $_GET['user_end_date'] ) ){
								$end_date = strtotime( $_GET['user_end_date'] );
							}else{
								$end_date = date( 'Y/m/d 00:00' );
							}
							

							$table_name = 'user_logins';
							$table_name =  $wpdb->prefix.$table_name;
							

							//$table_name = 'user_logins';
							//$table_name =  $wpdb->prefix.$table_name;
							$all_users_that_caN_vote = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '1' " );
							$all_login_users = $wpdb->get_col("SELECT DISTINCT user_id   FROM {$table_name} WHERE  date BETWEEN   '{$start_date}' AND '{$end_date}'  AND user_id IN (".implode( ',', $all_users_that_caN_vote ).") " );

							//$all_login_users = $wpdb->get_col("SELECT DISTINCT user   FROM {$table_name} WHERE  date BETWEEN   '{$start_date}' AND '{$end_date}'   " );
						
							$cnt = 1;
							foreach( $all_login_users as $s_user ){
								$userdata = get_user_by( 'ID', $s_user );
								$total_shares = get_own_shares( $s_user ) + get_total_shares( $s_user );

								$out .= '
								<tr>
									<td class="text-center">'.$cnt.'. '.$userdata->first_name.'</td>
									<td class="text-center">'.$userdata->last_name.'</td>
									<td class="text-center">'.$userdata->user_login.'</td>
									<td class="text-center">'.$total_shares.'</td>
								</tr>';
								$cnt++;
							}
								
					 
							
							$out .= '
							</tbody>
						</table>
						<div class="control_line text-right">
							<a href="'.admin_url( '?export_stats=true&user_start_date='.sanitize_text_field( $_GET['user_start_date'] ).'&user_end_date='.sanitize_text_field( $_GET['user_end_date'] ) ).'" class="btn btn-info"  >'.__('Export as CSV', 'wvp').'</a>
						</div>
					';

					$out .= '
					</div>
						';
				break;
				case "email_open_report":
				 
					global $wpdb;
					$table_name = 'email_view_log';
					$table_name =  $wpdb->prefix.$table_name;

					//error_reporting(E_ALL);
					//ini_set('display_errors', 'On');

					$out .= '
					</form>
					<style>
					#wpwrap{
						background:#fff;
					}
					</style>
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">';
						

					$out .= '
					<form action="" >
					<div class="row">
						<input name="post_type" value="poll" type="hidden" />
						<input name="page" value="wvp_dataemail_open_report" type="hidden" />

						<div class="col-10">
						<label class="control-label">'.__('Search:','wvp').'</label>
						<input type="text" autocomplete="off" class="form-control" name="search_term" value="'.sanitize_text_field( $_GET['search_term'] ).'" />
						</div>

						<div class="col-2">
							<button class="btn btn-success mt-4">'.__('Submit','wvp').'</button>
						</div>
					
					</div>
					</form>
					';

					global $wpdb;
		 
					

					$out .= '
					<br/><br/>
						<table class="table sortable_table">
							<thead class="">
								<tr>
									<th class="text-center">'.__('#','wvp').'</th>
									<th class="text-center">'.__('Username','wvp').'</th>
									<th class="text-center">'.__('Email','wvp').'</th>
									<th class="text-center">'.__('First name','wvp').'</th>
									<th class="text-center">'.__('Last Name','wvp').'</th>
									<th class="text-center">'.__('Email open date and time','wvp').'</th>
									<th class="text-center">'.__('IP','wvp').'</th>
									<th class="text-center">'.__('Email Subject','wvp').'</th>
						 
								</tr>
							</thead>
							<tbody>';

							$page_num = 200;

							if( $_GET['show'] == 'all' ){
								$post_line = '  ';
							}else{
								$post_line = ' LIMIT '.$page_num.' ';
							}
				
							// calculate offset
							if( $_GET['paged'] ){
								$paged = $_GET['paged'];
								$offset = ($paged-1)*$page_num;
								$offset_line =  ' OFFSET '.$offset.' ';
							}else{
								$paged = 1;
							}

							$extra_query = '';
							if( isset($_GET['search_term']) ){
								$search_query = "WHERE ( {$wpdb->prefix}users.user_login LIKE \"%".sanitize_text_field( $_GET['search_term'] )."%\" )
								OR ( {$wpdb->prefix}users.user_email LIKE \"%".sanitize_text_field( $_GET['search_term'] )."%\" )
								OR ( {$wpdb->prefix}users.user_nicename LIKE \"%".sanitize_text_field( $_GET['search_term'] )."%\" )
							
								" ;
							}

							$all_email_entries = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}email_view_log LEFT JOIN {$wpdb->prefix}users ON {$wpdb->prefix}email_view_log.user_id = {$wpdb->prefix}users.ID ".$search_query." ".$post_line.$offset_line );
						
							$cnt = 1;
							foreach( $all_email_entries as $s_row ){

						 
								$userdata = get_user_by( 'ID', $s_row->user_id );
								$email_subjects = get_option('email_sent_subjects');
				 
								if( !isset( $email_subjects[$s_row->subject] ) ){
									
									$email_subjects[$s_row->subject] = __('Not defined', 'wvp');
								}
								$out .= '
								<tr>
									<td class="text-center">'.$cnt.'</td>
									<td class="text-center">'.$userdata->user_login.'</td>
									<td class="text-center">'.$userdata->user_email.'</td>
									<td class="text-center">'.$userdata->first_name.'</td>
									<td class="text-center">'.$userdata->last_name.'</td>
									<td class="text-center" data-sort-value="'.$s_row->date.'">'.date( 'Y-m-d H:i', $s_row->date ).'</td>
									<td class="text-center">'. $s_row->ip.'</td>
									<td class="text-center">'.$email_subjects[$s_row->subject].'</td>
								</tr>';
								$cnt++;
							}
								
					 
							
							$out .= '
							</tbody>
						</table>';

						// pagination block
						$all_entries_count =   $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}email_view_log LEFT JOIN {$wpdb->prefix}users ON {$wpdb->prefix}email_view_log.user_id = {$wpdb->prefix}users.ID ".$search_query );

						$pages_amount = (int)( $all_entries_count / $page_num );
						if( $all_entries_count % $page_num != 0 ){
							$pages_amount = $pages_amount + 1;
						}
					
						$out .= '
							<div class="pagination-links">';
							for( $i=1; $i<=$pages_amount; $i++ ){
								if( $paged == $i ){
									$out .= '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">'.$i.'</span>';
								}else{
									$out .= '<a class=" button" href="'.admin_url('edit.php?post_type=poll&page=wvp_dataemail_open_report&paged='.$i).'"><span class="screen-reader-text">Last page</span><span aria-hidden="true">'.$i.'</span></a>';
								}
								
							}
						$out .= '</div>
						';

						$out .= '
						<div class="control_line text-right">
							<a href="'.admin_url( 'edit.php?post_type=poll&page=wvp_dataemail_open_report&export_log=true' ).'" class="btn btn-info"  >'.__('Export as CSV', 'wvp').'</a>
							<a href="'.admin_url( 'edit.php?post_type=poll&page=wvp_dataemail_open_report&action=drop_email_opened' ).'" class="btn btn-warning" onclick="return confirm(\''.__('Are you sure you want to delete all data?', 'wvp').'\')">'.__('Clear all records', 'wvp').'</a>
						</div>
					';

					$out .= '
					</div>
						';
				break;
				case "quorum_users":
				 
					$out .= '
					</form>
					<style>
					#wpwrap{
						background:#fff;
					}
					</style>
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">';
						

					$out .= '
					<form action="" >
					<div class="row">
						<input name="post_type" value="poll" type="hidden" />
						<input name="page" value="wvp_dataquorum_users" type="hidden" />
						<div class="col-4">
						<label class="control-label">'.__('Select start date / time:','wvp').'</label>
						<input type="text" autocomplete="off" class="form-control datepicker" name="user_start_date" value="'.sanitize_text_field( $_GET['user_start_date'] ).'" />
						</div>
						<div class="col-4">
						<label class="control-label">'.__('Select end date / time:','wvp').'</label>
						<input type="text" autocomplete="off" class="form-control datepicker" name="user_end_date" value="'.sanitize_text_field( $_GET['user_end_date'] ).'" />
						</div>
						<div class="col-4">
							<button class="btn btn-success">'.__('Submit','wvp').'</button>
						</div>
					
					</div>
					</form>
					';

					global $wpdb;
		 
					

					$out .= '
					<br/><br/>
						<table class="table">
							<thead class="">
								<tr>
									<th class="text-center">'.__('Name','wvp').'</th>
									<th class="text-center">'.__('Last Name','wvp').'</th>
									<th class="text-center">'.__('Username','wvp').'</th>
									<th class="text-center">'.__('Total shares A+B','wvp').'</th>
						 
								</tr>
							</thead>
							<tbody>';

							$all_users_that_caN_vote = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '1' " );
							

							if( isset( $_GET['user_start_date'] ) ){
								$start_date = strtotime( $_GET['user_start_date'] );
							}else{
								$start_date = date( 'Y/m/d 00:00' );
							}
							

							if( isset( $_GET['user_end_date'] ) ){
								$end_date = strtotime( $_GET['user_end_date'] );
							}else{
								$end_date = date( 'Y/m/d 00:00' );
							}
							


							

							$table_name = 'user_logins';
							$table_name =  $wpdb->prefix.$table_name;

							$all_login_users = $wpdb->get_col("SELECT DISTINCT user_id   FROM {$table_name} WHERE  date BETWEEN   '{$start_date}' AND '{$end_date}'  AND user_id IN (".implode( ',', $all_users_that_caN_vote ).") " );
						
							$cnt = 1;
							foreach( $all_login_users as $s_user ){
								$userdata = get_user_by( 'ID', $s_user );
								$total_shares = get_own_shares( $s_user ) + get_total_shares( $s_user );

								$out .= '
								<tr>
									<td class="text-center">'.$cnt.'. '.$userdata->first_name.'</td>
									<td class="text-center">'.$userdata->last_name.'</td>
									<td class="text-center">'.$userdata->user_login.'</td>
									<td class="text-center">'.$total_shares.'</td>
								</tr>';
								$cnt++;
							}
								
					 
							
							$out .= '
							</tbody>
						</table>
						<div class="control_line text-right">
							<a href="'.admin_url( '?export_stats=true&user_start_date='.sanitize_text_field( $_GET['user_start_date'] ).'&user_end_date='.sanitize_text_field( $_GET['user_end_date'] ) ).'" class="btn btn-info"  >'.__('Export as CSV', 'wvp').'</a>
 
						</div>
					';

					$out .= '
					</div>
						';
				break;
				case "data_log":
				 
					$out .= '
					<style>
					#wpwrap{
						background:#fff;
					}
					</style>
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">';
						
					global $wpdb;
					$table_name = 'online_log';
					$table_name =  $wpdb->prefix.$table_name;

					$out .= '
						<table class="table">
							<thead class="">
								<tr>
									<th class="">'.__('Date','wvp').'</th>
									<th class="">'.__('Time','wvp').'</th>
									<th class="">'.__('Total current users online at this moment','wvp').'</th>
									<th class="">'.__('Total online shares at this moment','wvp').'</th>
									<th class="">'.__('% of shares out of the total for our organization, online at this moment','wvp').'</th>
								</tr>
							</thead>
							<tbody>';
							$all_entries = $wpdb->get_results("SELECT  * FROM {$table_name}  group by date ORDER BY date DESC");
							if( count($all_entries)   > 0 )
							foreach( $all_entries as $s_entry ){
				  
								$out .= '
								<tr>
									<td class="">'.date( 'Y/m/d', strtotime( $s_entry->date ) ).'</td>
									<td class="">'.date( 'H:i:s', strtotime( $s_entry->date ) ).'</td>
									<td class="">'.$s_entry->total_users.'</td>
									<td class="">'.$s_entry->total_online_shares.'</td>
									<td class="">'.$s_entry->percent_of_shares.'</td>
								</tr>';
							}
							
							$out .= '
							</tbody>
						</table>
						<div class="control_line text-right">
							<a href="'.admin_url( 'edit.php?post_type=poll&page=wvp_datadata_log&export_log=true' ).'" class="btn btn-info"  >'.__('Export as CSV', 'wvp').'</a>

							<a href="'.admin_url( 'edit.php?post_type=poll&page=wvp_datadata_log&drop=all' ).'" class="btn btn-warning" onclick="return confirm(\''.__('Are you sure you want to delete all saved data for quorum?', 'wvp').'\')">'.__('Delete all records', 'wvp').'</a>
						</div>
					';

					$out .= '
					</div>
						';
				break;

				
				case "online_user_log":
				 
					$out .= '
					<style>
					#wpwrap{
						background:#fff;
					}
					</style>
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">';
						
					global $wpdb;
					$table_name = 'online_users_log';
					$table_name =  $wpdb->prefix.$table_name;

					$page_num = 20;

							if( $_GET['show'] == 'all' ){
								$post_line = '  ';
							}else{
								$post_line = ' LIMIT '.$page_num.' ';
							}
				
							// calculate offset
							if( $_GET['paged'] ){
								$paged = $_GET['paged'];
								$offset = ($paged-1)*$page_num;
								$offset_line =  ' OFFSET '.$offset.' ';
							}else{
								$paged = 1;
							}
				 
							

							$getting_all_dates = $wpdb->get_col("SELECT DISTINCT date FROM {$table_name} ORDER BY date DESC ".$post_line.$offset_line);
						
							foreach( $getting_all_dates as $single_date ){
								
								


								$out .= '
										<table class="table user_log_table">
											<thead class="">
												<tr>
													<th colspan="8">'.__('Online users at: ','wvp').date('Y-m-d H:i',  (int)$single_date ).'</th>
													
												</tr>
												<tr>
													<th class="text-center">'.__('#','wvp').'</th>
													<th class="text-center">'.__('Name','wvp').'</th>
													<th class="text-center">'.__('Last Name','wvp').'</th>
													<th class="text-center">'.__('Username','wvp').'</th>	
													
													<th class="text-center">'.__('Own Shares','wvp').'</th>
													<th class="text-center">'.__('Recieved Proxies','wvp').'</th>
													<th class="text-center">'.__('Shares in proxies','wvp').'</th>
													<th class="text-center">'.__('Total shares','wvp').'</th>
												</tr>
											</thead>
											<tbody>';

								$all_entries = $wpdb->get_results("SELECT DISTINCT * FROM {$table_name} WHERE date = {$single_date}  group by user");
								$cnt = 1;
								$global_total_shares = 0;
								if( count($all_entries)   > 0 )
									foreach( $all_entries as $s_entry ){
									
									 
											
											
								
											$userdata = get_user_by('ID', $s_entry->user);
										 
											$total_shares = get_own_shares( $s_entry->user ) + get_total_shares( $s_entry->user );
										 
											$global_total_shares = $global_total_shares + $total_shares;
											$out .= '
											<tr>
												<td class="text-center">'.$cnt.'</td>
												<td class="text-center">'.$userdata->first_name.'</td>
												<td class="text-center">'.$userdata->last_name.'</td>
												<td class="text-center">'.$userdata->user_login.'</td>

												<td class="text-center">'.get_own_shares( $s_entry->user ) .'</td>
												<td class="text-center">'.get_proxys_amount( $s_entry->user ) .'</td>
												<td class="text-center">'.get_total_shares( $s_entry->user ) .'</td>
												<td class="text-center">'.$total_shares.'</td>
											</tr>';
											$cnt++;


										
									}
									$out .= '
											</tbody>
											<tfoot>
												<tr>
													<td class="">&nbsp;</td>
													<td class="">&nbsp;</td>
													<td class="">&nbsp;</td>
													<td class="">&nbsp;</td>

													<td class="">&nbsp;</td>
													<td class="">&nbsp;</td>
													<td class="">&nbsp;</td>
													<td class="text-center">'.$global_total_shares.'</td>
												</tr>
											</tfoot>
										</table>
										';
							}

							###################

							
							
						// pagination block
						$all_entries_count =   $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} ORDER BY date DESC " );
						$pages_amount = (int)( $all_entries_count / $page_num );
						if( $all_entries_count % $page_num != 0 ){
							$pages_amount = $pages_amount + 1;
						}
					
						$out .= '
							<div class="pagination-links">';
							for( $i=1; $i<=$pages_amount; $i++ ){
								if( $paged == $i ){
									$out .= '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">'.$i.'</span>';
								}else{
									$out .= '<a class=" button" href="'.admin_url('edit.php?post_type=poll&page=wvp_data_online_user_log&paged='.$i).'"><span class="screen-reader-text">Last page</span><span aria-hidden="true">'.$i.'</span></a>';
								}
								
							}
						$out .= '</div>
						';

						$out .= '
							
						<div class="control_line text-right">
							
						<a href="'.admin_url( 'edit.php?post_type=poll&page=wvp_data_online_user_log&show=all' ).'" class="btn btn-success"  >'.__('Show all records on single page', 'wvp').'</a>
							
						<a href="'.admin_url( 'edit.php?post_type=poll&page=wvp_datadata_log&export_user_log=true' ).'" class="btn btn-info"  >'.__('Export as CSV', 'wvp').'</a>

							<a href="'.admin_url( 'edit.php?post_type=poll&page=wvp_datadata_log&drop_name_log=all' ).'" class="btn btn-warning" onclick="return confirm(\''.__('Are you sure? This is not reversible', 'wvp').'\')">'.__('Delete all records', 'wvp').'</a>
						</div>
					';

					$out .= '
					</div>
						';
				break;
				
				case "text":
						$out .= '
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).' '.( $this->parameters['topclass'] ? $this->parameters['topclass'] : 'col-12' ).'">
						<div class="form-group">  
							<label class="control-label" for="'.$this->parameters['id'].'">'.$this->parameters['title'].'</label>  
							
							  <input type="text"  class="form-control '.$this->parameters['class'].'"  name="'.$this->parameters['name'].'" id="'.$this->parameters['id'].'" placeholder="'.$this->parameters['placeholder'].'" value="'.( $this->value && $this->value != '' ? esc_html( stripslashes( $this->value ) ) : $this->parameters['default'] ).'"  '.( $this->parameters['disabled'] ? ' disabled ' : '' ).' >  
							  <p class="help-block">'.$this->parameters['sub_text'].'</p>  
							
						  </div> 
					</div>
						';
				break;
				case "total_current_shares":
					global $wpdb;

					$settings = get_option('wvp_options');
					$time_limit = current_time('timestamp') - (int)$settings['user_online_lifetime']*60;

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
				
					//$all_online_usrs = $wpdb->get_results("SELECT user_id as ID FROM {$wpdb->usermeta} WHERE meta_key = 'online_activity' AND meta_value > {$time_limit} AND user_id  IN ( ".implode( ",", $all_users_that_caN_vote )." )");

					$tmp_own_shares = 0;
					$tmp_recieved_proxy = 0;
					foreach( $all_users_that_caN_vote as $s_user ){
						
						$tmp_own_shares = $tmp_own_shares + get_own_shares( $s_user );

						$tmp_recieved_proxy = $tmp_recieved_proxy + get_proxys_amount( $s_user );
					}


					// all recieved shares PATCH
					$tmp_recieved_proxy = 0;
					$all_users_that_have_assigns = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'assign_date' AND meta_value != '1' " );
					foreach( $all_users_that_have_assigns as $s_user ){
						$tmp_recieved_proxy = $tmp_recieved_proxy + (float)get_user_meta( $s_user, 'own_shares_old', true );
					}

					$total_a_b = $tmp_own_shares + $tmp_recieved_proxy;
 
						$out .= '
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<div class="form-group">  
							<label class="control-label" for="'.$this->parameters['id'].'">'.$this->parameters['title'].'</label>  
							
							<table class="table">
								<tbody>
									<tr>
										<td><b>'.__('Total users with can vote=1:', 'wvp').'</b></td>
										<td>'.count($all_users_that_caN_vote).'</td>
									</tr>
									<tr>
										<td><b>'.__('Total own shares (A):', 'wvp').'</b></td>
										<td>'.$tmp_own_shares.'</td>
									</tr>
									<tr>
										<td><b>'.__('Total shares received in proxies (B):', 'wvp').'</b></td>
										<td>'.$tmp_recieved_proxy.'</td>
									</tr>
									<tr>
										<td><b>'.__('Total (A+B):', 'wvp').'</b></td>
										<td>'.$total_a_b.'</td>
									</tr>
								</tbody>
							</table>  
							
						  </div> 
					</div>
						';
				break;
				case "button":
						$out .= '
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<div class="form-group">  
							<label class="control-label" for="">&nbsp;</label>  
							
							  <a class="'.( $this->parameters['class'] ? $this->parameters['class'] : 'btn btn-success' ).'" href="'.$this->parameters['href'].'" id="'.$this->parameters['id'].'"  >'.$this->parameters['title'].'</a>  
							  
							
						</div> 
					</div>
						';
				break;
				case "logout_button":
						$out .= '
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<div class="form-group">  
							<label class="control-label" for="">&nbsp;</label>  
							
							  <a onClick="return confirm(\''.__('Are you sure? This action is not reversible', 'wvp').'\')" class="'.( $this->parameters['class'] ? $this->parameters['class'] : 'btn btn-success' ).'" href="'.$this->parameters['href'].'"   >'.$this->parameters['title'].'</a>  
							  
							
						</div> 
					</div>
						';
				break;
				case "select":
						$out .= '
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<div class="form-group">  
							<label class="control-label" for="'.$this->parameters['id'].'">'.$this->parameters['title'].'</label>  
							 
							  <select  style="'.$this->parameters['style'].'" class="form-control '.$this->parameters['class'].'" name="'.$this->parameters['name'].'" id="'.$this->parameters['id'].'">' ; 
							  if( count( $this->parameters['value'] ) > 0 )
							  foreach( $this->parameters['value'] as $k => $v ){
								  if( $this->value  ){
									$out .= '<option value="'.$k.'" '.( $this->value  == $k ? ' selected ' : ' ' ).' >'.$v.'</option> ';
								  }else{
									$out .= '<option value="'.$k.'" '.( $this->parameters['default']  == $k ? ' selected ' : ' ' ).' >'.$v.'</option> ';
								  }
								  
							  }
						$out .= '		
							  </select>  
							  <p class="help-block">'.$this->parameters['sub_text'].'</p> 
							</div>  
					</div>	 
						';
				break;
				case "checkbox":
						$out .= '
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<div class="form-check">  

							<input type="checkbox" class="'.$this->parameters['class'].' form-check-input" id="'.$this->parameters['id'].'" name="'.$this->parameters['name'].'" value="on" '.( $this->value == 'on' ? ' checked ' : '' ).'>
							<label class="form-check-label" for="exampleCheck1"><b>'.$this->parameters['title'].'</b></label>
 
						  </div>  
						  <p class="help-block">'.$this->parameters['sub_text'].'</p> 
					</div>
						';
				break;
				case "radio":
						$out .= '
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<div class="form-group">  
							<label class="control-label" for="'.$this->parameters['id'].'">'.$this->parameters['title'].'</label>';
								foreach( $this->parameters['value'] as $k => $v ){
									$out .= '
									<label class="radio">  
										<input  class="'.$this->parameters['class'].'" type="radio" name="'.$this->parameters['name'].'" id="'.$this->parameters['id'].'" value="'.$k.'" '.( $this->value == $k ? ' checked ' : '' ).' >&nbsp;  
										'.$v.'  
										<p class="help-block">'.$this->parameters['sub_text'].'</p> 
									  </label> ';
								}
							$out .= '
							
						  </div>  
					</div>
						';
				break;
				case "textarea":
					 
						$out .= '
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<div class="form-group">  
							<label class="control-label" for="'.$this->parameters['id'].'">'.$this->parameters['title'].'</label>  
						
							  <textarea style="'.$this->parameters['style'].'" class="form-control '.$this->parameters['class'].'" name="'.$this->parameters['name'].'" id="'.$this->parameters['id'].'" rows="'.$this->parameters['rows'].'">'.( $this->value && $this->value != '' ?  esc_html( stripslashes( $this->value ) ) : $this->parameters['default'] ).'</textarea>  
							  <p class="help-block">'.$this->parameters['sub_text'].'</p> 
						 
						  </div> 
					</div>
						';
				break;
				case "multiselect":
						$out .= '
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<div class="form-group">  
							<label class="control-label" for="'.$this->parameters['id'].'">'.$this->parameters['title'].'</label>  
							 
							  <select  multiple="multiple" style="'.$this->parameters['style'].'" class="form-control '.$this->parameters['class'].'" name="'.$this->parameters['name'].'[]" id="'.$this->parameters['id'].'">' ; 
							  foreach( $this->parameters['value'] as $k => $v ){
							 
								  if( !$this->value  || $this->value === null ){
									$this->value = [];
								  }
								  $out .= '<option value="'.$k.'" '.( in_array( $k, $this->value )   ? ' selected ' : ' ' ).' >'.$v.'</option> ';
							  }
						$out .= '		
							  </select>  
							  <p class="help-block">'.$this->parameters['sub_text'].'</p> 
							 
						  </div>  
					</div>
						';
				break;
				case "multiselect_email":
					$settings = get_option('wvp_email_options');

						$out .= '
					<style>
					.email_selector .user_pick_check{
						margin-right:10px;
						
					}
					.email_selector .content_bar{
						margin:10px 0px;;
					}
					.email_selector .content_bar{
						overflow-y:scroll;
						height:300px;
					}
					</style>
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<div class="form-group">  
							<label class="control-label" for="'.$this->parameters['id'].'">'.$this->parameters['title'].' <a class="select_all_users" href="#">'.__('Select All','wvp').'</a> &nbsp;&nbsp;&nbsp;&nbsp; <a class="unselect_all_users" href="#">'.__('Unselect All','wvp').'</a></label>  
							 
							  <div class="email_selector">
								 <div class="top_bar">
								 	<input class="user_filter form-control" placeholder="'.__('Search for users','wvp').'">
								 </div> 
								 <div class="content_bar">';
								 
								if( !$this->value || $this->value == '' ){
									$this->value = [];
								}

								$all_users = get_users();
								foreach( $all_users as $s_user ){
							 
								 
									$user_name = $s_user->user_login.' - '.$s_user->first_name.' - '.$s_user->last_name.' - '.$s_user->user_email;
									$out .= '
									<div class="single_user_row">
										<label>
											<input type="checkbox" '.( in_array( $s_user->ID, $this->value ) ? ' checked ' : '' ).' class="user_pick_check" name="recipients[]" data-username="'.$user_name.'" value="'.$s_user->ID.'" />'.$user_name.'
										</lable>
									</div>
									';
								}

								 $out .= '</div>
								 <div class="bottom_bar">
								 	<span class="user_count">'.( is_array( $this->value ) ? count( $this->value ) : '0' ).'</span> '.__('users selected','wvp').'
								 </div>
							  </div>
						  </div>  
					</div>
						';
				break;
				case "wide_editor":
					$out .= '
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<div class="form-group">  
							<label class="control-label" for="input01">'.$this->parameters['title'].'</label>
							
							<div class="form-control1">
							';  
				 
							ob_start();
							wp_editor(  stripslashes  ( $this->value ) , $this->parameters['name'] );
							$editor_contents = ob_get_clean();	
						 
							$out .= $editor_contents;  
						$out .= '
						
							</div>
							'.( $this->parameters['subtext'] ? '<p>'.$this->parameters['subtext'].'</p>' : '' ).'
						  </div> 
					</div>';	 
					 
				break;
				case "file":
						$out .= '
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<div class="form-group">  
							<label class="control-label" for="'.$this->parameters['id'].'">'.$this->parameters['title'].'</label>  
				 
							<input type="file" class="form-control-file '.$this->parameters['class'].'" name="'.$this->parameters['name'].''.( $this->parameters['multi'] ? '[]' : '' ).'" id="'.$this->parameters['id'].'" '.( $this->parameters['multi'] ? ' multiple ' : '' ).' >
							  
							  <p class="help-block">'.$this->parameters['subtext'].'</p> 
						 
						  </div>
					</div>
						';
				break;
				case "email_report":
						$out .= '
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">';
					//$report = get_option('email_sending_report');
					$processed_items = (int)get_post_meta($post->ID, 'email_full_processed_items_'.$post->ID, true);
					global $wpdb;

					//  getting users
					$settings = get_option('wvp_email_options');

					/**
					 * patch rebuilt from single sending to multiple templates v2.0.60
					 * restructurize settings to use user meta
					 */
					$settings['recipients'] = get_post_meta( $template_id, 'recipients', true);
					$settings['send_mail_to_all'] = get_post_meta( $template_id, 'send_mail_to_all', true);
					/** END */

					$recipients = $settings['recipients'];
					$user_list = [];
					if( $settings['send_mail_to_all'] == 'all'){	
						$user_list = $wpdb->get_col("SELECT ID   FROM {$wpdb->users} " ); /* WHERE meta_key = 'can_vote' AND meta_value = '1' */ 
					}elseif( $settings['send_mail_to_all'] == 'selected' ){
						$user_list = $recipients;
					}elseif( $settings['send_mail_to_all'] == 'can_vote' ){
						$user_list = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '1'" ); /* WHERE meta_key = 'can_vote' AND meta_value = '1' */ 
					}elseif( $settings['send_mail_to_all'] == 'cant_vote' ){
						$user_list = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '0'" );
					}

					if( $user_list === null ){
						$user_list = [];
					}

					$is_hide = get_post_meta($post->ID, 'hide_send_message_'.$post->ID, true);
		 

					if( $is_hide != '1' ){


						$text_string = 
						$out .= '<div class="alert alert-success">
						'.sprintf( 
							__('%d emails were sent in the last massive email that was sent. Click here to see the <a href=\'%s\'>email log</a>', 'wvp'), $processed_items, admin_url('edit.php?post_type=poll&page=wvp_dataemail_log') ).'
						
						<button type="button" class="close close_message_button" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
						
						</div>';

						if( $processed_items < count( $user_list ) && $processed_items != 0 ){
							$out .= '<div class="alert alert-warning no_finished_query">
							'.sprintf( 
								__('Your last massive email failed to send the message to all selected users.  Only %d out of %d were sent. Click <a href="#" class="send_full_emails" data-continue="1">here</a> to continue sending the message to the remaining users. Click <a href="#" class="drop_email_queue"  >here</a> to cancel and reset the sending queue', 'wvp'), $processed_items, count( $user_list ) ).'
							</div>';
						}
					}
					


					/*
					$result_text = '';
					$report = get_option('email_sending_report');
				
					if( count($report) > 0 ){
						foreach( $report as $user_id => $result ){
							if( substr_count(  $user_id, '@' ) > 0 ){
								if( $result ){
									$result_text .= '<li>'.$user_id.': <span class="badge badge-success">'.__('Sent','wvp').'</span></li>';
								}else{
									$result_text .= '<li>'.$user_id.': <span class="badge badge-danger">'.__('Error','wvp').'</span></li>';
								}
							}else{
								$userdata = get_user_by('ID', $user_id);
								if( $result ){
									$result_text .= '<li>'.$userdata->user_login.': <span class="badge badge-success">'.__('Sent','wvp').'</span></li>';
								}else{
									$result_text .= '<li>'.$userdata->user_login.': <span class="badge badge-danger">'.__('Error','wvp').'</span></li>';
								}
							}
							
							
							
						}
					}
					$out .= '<ul>'.$result_text.'</ul>';
					$out .= '
						<a class="btn btn-warning" href="'.admin_url('edit.php?post_type=poll&page=wvp_emailsend_email&droplog=1').'">'.__('Remove log', 'wvp').'</a>';
					*/
					$out .= '
					</div>
						';
						
				break;
				case "email_log":
					 
						$out .= '
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">';
					$current_log = get_option('big_email_log' );
					if( !$current_log ){
						$current_log = [];
					}
					
					$out .= '
						<table class="table">
						<thead>
							<tr>
		 
								<th colspan="5"  class="text-right">
								
								<a class="btn btn-danger" onClick="return confirm(\''.__('Are you sure? This action is not reversible', 'wvp').'\')" href="'.admin_url( 'edit.php?post_type=poll&page=wvp_dataemail_log&delete_email_log=1' ).'">'.__('Delete All Logs', 'wvp').'</a>
								
								</th>
							</tr>
							<tr>
								<th>'.__('Sending Date - Time', 'wvp').'</th>
								<th  class="text-center">'.__('Email was sent to this amount of users', 'wvp').'</th>
								<th  class="text-center">'.__('Users', 'wvp').'</th>
								<th  class="text-center">'.__('Actions', 'wvp').'</th>
							</tr>
						</thead>
						<tbody>';
						
						$date  = array_column( $current_log, 'date');
						array_multisort( $date, SORT_DESC, $current_log );
				 

						foreach( $current_log as $s_row ){
						 
							$out .= '
							<tr>
								<td>'.date( 'Y-m-d H:i', (int)$s_row['date'] ).'</td>
								<td class="text-center">'.$s_row['usercount'].'</td>
								<td  class="">
								<div class="user_log_list text-center">
								';
								
								$report = (array)$s_row['send_report'];
							 	
								

								$result_text = '';
								if( count($report) > 0 ){
									foreach( $report as $user_id => $result ){
										if( substr_count(  $user_id, '@' ) > 0 ){
											if( $result ){
												$result_text .= '<li>'.$user_id.': <span class="badge badge-success">'.__('Sent','wvp').'</span></li>';
											}else{
												$result_text .= '<li>'.$user_id.': <span class="badge badge-danger">'.__('Error','wvp').'</span></li>';
											}
										}else{
										 
											$userdata = get_user_by('ID', $user_id);
											if( $result ){
												$result_text .= '<li>'.$userdata->user_login.': <span class="badge badge-success">'.__('Sent','wvp').'</span></li>';
											}else{
												$result_text .= '<li>'.$userdata->user_login.': <span class="badge badge-danger">'.__('Error','wvp').'</span></li>';
											}
										}
									}
								}
								$out .= '<ul>'.$result_text.'</ul>';

								$out .= '
										</div>
									</td>
								<td>
								<a  class="btn btn-sm btn-info view_message" href="#" data-id="'.$s_row['date'].'">'.__('View', 'wvp' ).'</a>
								<a class="btn btn-sm btn-danger" href="'.admin_url('edit.php?post_type=poll&page=wvp_dataemail_log&remove_email_log='.$s_row['date']).'">'.__('Remove', 'wvp' ).'</a>
								<a class="btn btn-sm btn-warning" href="'.admin_url('edit.php?post_type=poll&page=wvp_emailsend_email&reuse_email_log='.$s_row['date'] ).'">'.__('Re-Use', 'wvp' ).'</a>
								<a class="btn btn-sm btn-light show_user_log" href="#">'.__('Show Report', 'wvp' ).'</a>
								</td>
							</tr>
							<tr id="mail_'.$s_row['date'].'" style="display:none;">
								<td colspan="4">'.nl2br( $s_row['body'] ).'</td>
							</tr>
							';
						}

						$out .= '</tbody>
						</table>
					';
					
					$out .= '
					</div>
						';
				break;
				case "mediafile_single":
					$attach_url = wp_get_attachment_url( $this->value );
					
					$out .= '
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<div class="form-group media_upload_block">  
						<label class="control-label" for="input01">'.$this->parameters['title'].'</label>  
						 
						  <input type="hidden" class="form-control input-xlarge mediafile_single item_id" name="'.$this->parameters['name'].'" id="'.$this->parameters['name'].'" value="'.$this->value.'"> 
						  
						
						  <input type="button" class="btn btn-success upload_file" data-single="1" value="'.$this->parameters['upload_text'].'" />
						  <div class="image_preview">'.( $attach_url ?  $attach_url  : '' ).'</div>
						</div> 
					</div>';	
					break;
					
					case "save":
				 
					$out .= '
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).'">
						<div class="form-actions">  
							<button type="submit" class="btn btn-primary" id="'.$this->parameters['id'].'">'.$this->parameters['title'].'</button>  
						</div> 
					</div>
					';	
					break;
					case "link":
				 
					$out .= '
					<div class="'.( $this->parameters['width'] ? $this->parameters['width'] : 'col-12' ).' '.$this->parameters['topclass'].' ">
						<div class="form-actions">  
							'.( $this->parameters['top_text'] ? '<p class="help-block">'.$this->parameters['top_text'].'</p> ' : '' ).'
							<a href="'.$this->parameters['href'].'" class="'.$this->parameters['class'].'">'.$this->parameters['title'].'</a>  
							<p class="help-block">'.$this->parameters['sub_text'].'</p> 
						</div> 
					</div>
					';	
					break;
					
					case "text_out":
				 
					$out .= '
					<div class="'.( $this->parameters['title'] ? $this->parameters['title'] : 'col-12' ).'">
						'.$this->parameters['class'].'
					</div>
					';	
					break;
					case "help_block":
				 
					$out .= '
					<div class="'.( $this->parameters['title'] ? $this->parameters['title'] : 'col-12' ).'">';
			 
					if( get_locale() == 'en_US' ){
						include_once( 'help_eng.php' );
					}else{
						include_once( 'help_esp.php' );
					}
						
					$out .= '
					</div>
					';	
					break;
			}
			$this->content = $out;
		 
		}
		public function  get_code(){
			return $this->content;
		}
	}
}
 
?>