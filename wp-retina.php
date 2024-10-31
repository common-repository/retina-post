<?php
require_once('wp-plugin-retina.php');

define("CONTENT_max_len", "300");

if (!class_exists('wp_retina')) {
    class wp_retina extends wp_plugin_retina 
	{
        private $saved_error;
        private $options_default;
		private $RetinaPost;
		private $spam_detected, $spam_detected_option_name;

        function __construct($options_name) 
		{            
			$this->options_default= array(
			'call_to_action'=>'Type the colored letters',
			'incorrect_response_error'=>'Incorrect response', 
			'readmore_show' =>1, 
			'readmore_message'=>'Read more', 
			
			'show_in_comments'=>1,
			'bypass_for_registered_users'=>1,
			'minimum_bypass_level'=>'read',
			
			'type_c'=>'feed',
			'custom_text_c'=>'',
			'custom_link_c'=>'',
			'tab_index_c'=>'',
			
			'show_in_registration'=>1,
			'type_r'=>'feed',
			'custom_text_r'=>'',
			'custom_link_r'=>'',
			'tab_index_r'=>21
			);
			
			// get options
			$this->options_name = $options_name;            
            $this->options = $this->compute_options($options_name);
			
			// create RetinaPost object 
			require_once($this->path_to_plugin_directory() . '/request.php');
			$this->RetinaPost = new RetinaPost();
            
            // register the hooks
            $this->register_actions_filters();
        }
        
		//-------------------------------------------------------------------
		function compute_options($options_name)
		{			
            $options = $this->retrieve_options($options_name);			
			return $options;
		}
		
        function register_actions_filters() 
		{                        
			register_activation_hook($this->path_to_plugin_directory() . '/retina-post.php', array(&$this, 'activation_load_options')); 
			
			// only register the hooks if the user wants retina on the comments page
            if ($this->options['show_in_comments']) {
                add_action('comment_form', array(&$this, 'show_in_comments'));
				add_action('preprocess_comment', array(&$this, 'check_comment'), 0);
				add_action('comment_post_redirect', array(&$this, 'relative_redirect'), 0, 2);
				add_filter('comment_form_default_fields', array(&$this, 'fill_saved_default_fields') );
                add_filter('comment_form_field_comment', array(&$this, 'fill_saved_comment'));				
            }
            // only register the hooks if the user wants retina on the registration page
            if ($this->options['show_in_registration']) {				
				add_action('register_form', array(&$this, 'show_in_registration'));
				add_filter('registration_errors', array(&$this, 'check_registration'));
            }
            // administration (menus, pages, notifications, etc.)
            add_filter("plugin_action_links", array(&$this, 'show_settings_link'), 10, 2);
            add_action('admin_menu', array(&$this, 'add_settings_page'));
		
            // admin notices
            add_action('admin_notices', array(&$this, 'show_admin_notice'));
        }		
		//-------------------------------------------------------------------
        function activation_load_options() {
			$old_options 	  = $this->retrieve_options($this->options_name);
			
			if ($old_options) {
				$options = $this->options_default;     // pre-load with the default options
				foreach($old_options as $key => $val) // the old options will replace default options
					$options[$key] = $val;
			}           
            else {			
               $options= $this->options_default;			   
            }
			$this->update_options($this->options_name, $options);			          
        }
		
        //-------------------------------------------------------------------
			function retina_enabled() {
				return ($this->options['show_in_comments'] || $this->options['show_in_registration'] || $this->options['show_in_contact_form_7']);
			}
			function error_in_message_c(){		
				return ( $this->options['show_in_comments'] && $this->options['type_c']=='text' && empty($this->options['custom_text_c']));
			}
			function error_in_message_r(){		
				return ( $this->options['show_in_registration'] && $this->options['type_r']=='text' && empty($this->options['custom_text_r']));
			}
		
        function show_admin_notice() {			
            if ($this->retina_enabled() && 
			   ($this->error_in_message_c() || $this->error_in_message_r()) && 
			    FALSE===strpos($_SERVER['REQUEST_URI'], 'retina-post')) 
			{                
				$options_url = admin_url('options-general.php?page=retina-post/wp-retina.php');
				$message = sprintf(__('Configure Retina Post ' . ' <a href="%s" title="Retina Settings"> Go to settings</a>', 'retinapost'), $options_url);            
				echo '<div class="notice" style="color:#ffC050"><p><strong>'.$message . '</strong></p></div>';
            }
        }
        //-------------------------------------------------------------------
		function get_retina_html($retina_message, $were, $options=null ) {							
            if ($this->options['type_'.$were]=='feed')
			{
				global $post;  // this post
				$last_posts = get_posts(array( 'numberposts' =>6, 'exclude'=>$post->ID,  'post_status' => 'publish' ));
				
				//var_dump($last_posts);
				if ( sizeof($last_posts)>0 )
				{
					$rand_post  = mt_rand(0, sizeof($last_posts)-1);		
					$content = 	$last_posts[$rand_post]->post_title.' '.$last_posts[$rand_post]->post_content;
					$content = 	substr ( $content, 0, CONTENT_max_len);
					$link 	 = 	$last_posts[$rand_post]->guid;
				}
				else
				{
					$content = 	'';
					$link	 =  '';
				}
				
				return $this->RetinaPost->get(array("text"=>esc_attr($content), "link"=>$link, "message"=>$retina_message, "tab_index"=>$this->options['tab_index_'.$were] ));
			}				
			else 
				return $this->RetinaPost->get(array("text"=>esc_attr($this->options['custom_text_'.$were]), "link"=>$this->options['custom_link_'.$were], "message"=>$retina_message, "tab_index"=>$this->options['tab_index_'.$were] ));
        }
        //-------------------------------------------------------------------
        function show_in_registration($errors) {
            echo $this->get_retina_html($this->options['call_to_action'],'r');
        }
        
        function check_registration($errors) {
            // empty so throw the empty response error					
            if (empty($_POST['Retina_response']) || trim($_POST['Retina_response'])=='' || !$debug=$this->RetinaPost->check()) {
				if (is_multisite())
					$errors['errors']->add('generic', $this->options['incorrect_response_error']);
				else
					$errors->add('generic', $this->options['incorrect_response_error']);
            }
			return $errors;
        }
		//-------------------------------------------------------------------
        function show_in_comments() {
            global $user_ID;

            // set the minimum capability needed to skip the captcha if there is one
            if (isset($this->options['bypass_for_registered_users']) && $this->options['bypass_for_registered_users'] && $this->options['minimum_bypass_level'])
                $needed_capability = $this->options['minimum_bypass_level'];

            // skip the challenge if the minimum capability is met
            if ((isset($needed_capability) && $needed_capability && current_user_can($needed_capability)) || !$this->options['show_in_comments'])
                return;

            else {			
                // SHOW ERROR MESSAGE if there is an error
                if (!empty($_GET['retina_error']))
                    echo '<p class="none" style="color:#ff3333;">' .$this->options['incorrect_response_error'] . "</p>";

              	ob_start(); ?>				
				<div id="Retina_submit_area">
					<?php if ($this->options['readmore_show']) {	?>
					<div id="Retina_redirect" style="display:none">
						<input name="Retina_readmore_checkbox" type="checkbox" checked="checked" value="1" id="Retina_readmore_checkbox" />
						<label for="Retina_readmore_checkbox">
							<?php echo $this->options['readmore_message']; ?>
							<span style="color:#00AA00;font-weight:bold;text-decoration:none;">&raquo;</span>
						</label>
						<input type="hidden" name="Retina_redirect_input" id="Retina_redirect_input"/>
					</div>
					<?php }	?>
				</div>
				<script type="text/javascript">
					var submite_button = document.getElementById('submit');
					var retina_link = document.getElementById('Retina_link');
					var retina_redirect = document.getElementById('Retina_redirect');
					var retina_redirect_input = document.getElementById('Retina_redirect_input');
					
					submite_button.tabIndex = document.getElementById('Retina_edit_0').tabIndex;
					document.getElementById('Retina_submit_area').appendChild (submite_button);
					
					document.getElementById('commentform').onchange = Retina_show_challenge;
					
					<?php if ($this->options['readmore_show']) {	?>					
					if ( retina_link && retina_link.href!='')
					{
						retina_redirect_input.value = retina_link;
						
						if(typeof(window.getComputedStyle) != 'undefined'){ 
						
						var old_style	= window.getComputedStyle(submite_button, null);
						
						var margin_left	= parseInt(old_style.getPropertyValue("left"));
						margin_left  	= margin_left + parseInt(old_style.getPropertyValue("margin-left"));
						var margin_top	= parseInt(old_style.getPropertyValue("top"));
						margin_top  	= margin_top  + parseInt(old_style.getPropertyValue("margin-top"));
						
						retina_redirect.style.marginLeft= margin_left + 'px';
						retina_redirect.style.marginTop = margin_top  + 'px';
						submite_button.style.marginTop= '5px';
						}
						retina_redirect.style.display="block";
					}
					<?php }	?>
				</script>
				
				<?php $reorder_submit_button= ob_get_clean();
				
                echo $this->get_retina_html( $this->options['call_to_action'], 'c' ) . $reorder_submit_button;
           }
        } 
          
        function check_comment($comment_data) 
		{            
            if ($this->options['bypass_for_registered_users'] && $this->options['minimum_bypass_level'])
                $needed_capability = $this->options['minimum_bypass_level'];
            
            if ((isset($needed_capability) && current_user_can($needed_capability)) || !$this->options['show_in_comments'])
                return $comment_data;
            
			if ($comment_data['comment_type'] == '' || strtolower($comment_data['comment_type']) == 'comment' ) {
				if ($this->RetinaPost->check())
					return $comment_data;
					
				else {
					$post    = get_post( $comment_data["comment_post_ID"] );			
					$comment_data["user_ID"] = $post->post_author;
					$this->saved_error = $this->RetinaPost->get_error();
					add_filter('pre_comment_approved', create_function('$a', 'return \'trash\';'));				
					return $comment_data;
				}
			}		     
            return $comment_data;
        }
        
        function relative_redirect($location, $comment) {
            if ($this->saved_error != '') {
                // replace #comment- at the end of $location with #commentform                
                $location = substr($location, 0, strpos($location, '#')) .
                    ((strpos($location, "?") === false) ? "?" : "&") .
					http_build_query(array('retina_error'=>$this->saved_error, 'retina_comment_id'=> $comment->comment_ID)).
                    '#commentform';
            }
			else if ( $_POST["Retina_readmore_checkbox"] && !empty( $_POST["Retina_redirect_input"] )) 
				$location = $_POST["Retina_redirect_input"];
            return $location;
        }

		function fill_saved_default_fields( $fields ) {
			if (!empty($_GET['retina_comment_id'])){			
				$comment = get_comment($_GET['retina_comment_id']);
				
				$fields["author"] 	= str_replace( 'value=""', 'value="'.$comment->comment_author		.'"', 	$fields["author"] );
				$fields["email"] 	= str_replace( 'value=""', 'value="'.$comment->comment_author_email .'"', 	$fields["email"] );			
				$fields["url"] 		= str_replace( 'value=""', 'value="'.$comment->comment_author_url	.'"', 	$fields["url"] );			
			}
			return $fields;
		}			

		function fill_saved_comment( $field ) {	
			if (!empty($_GET['retina_comment_id'])){
				$comment = get_comment($_GET['retina_comment_id']);
				$field = str_replace( '</textarea>', $comment->comment_content.'</textarea>', $field );			
				wp_delete_comment($comment->comment_ID);
			}
			return $field;
		}
	
        //-------------------------------------------------------------------
        // add a settings link to the plugin in the plugin list
        function show_settings_link($links, $file){
			if (basename($file) == 'retina-post.php') {
               $settings_title = __('Settings for RetinaPost', 'retinapost');
               $settings = __('Settings', 'retinapost');
               $settings_link = '<a href="options-general.php?page=retina-post/wp-retina.php" title="' . $settings_title . '">' . $settings . '</a>';
               array_unshift($links, $settings_link);
            }            
            return $links;
        }
        
		//-------------------------------------------------------------------
        // add the settings page
        function add_settings_page() {  
			add_submenu_page( 'plugins.php', 'Engage user & fight spam by Retina Post', 'Engage & Anti-spam', 'manage_options', __FILE__, array(&$this, 'show_settings_page'));
			add_options_page('Engage user & fight spam', 'Engage & Anti-spam', 'manage_options', __FILE__, array(&$this, 'show_settings_page'));
        }
			function show_settings_page() {
				include($this->path_to_plugin_directory() . '/settings.php');
			}
        		
		//-------------------------------------------------------------------
		function build_dropdown($name, $keyvalue, $checked_value) {
            echo '<select name="' . $name . '" id="' . $name . '">'."\n";
            foreach ($keyvalue as $key => $value) {
                $checked = ($value == $checked_value) ? ' selected="selected" ' : '';                
                echo '\t <option value="' . $value . '"' . $checked . ">$key</option> \n";
                $checked = NULL;
            }            
            echo "</select> \n";
        }        
        function capabilities_dropdown() {
            $capabilities = array (
                __('all registered users', 'retinapost') => 'read',
                __('edit posts', 'retinapost') => 'edit_posts',
                __('publish posts', 'retinapost') => 'publish_posts',
                __('moderate comments', 'retinapost') => 'moderate_comments',
                __('activate plugins', 'retinapost') => 'activate_plugins'
            );            
            $this->build_dropdown('minimum_bypass_level', $capabilities, $this->options['minimum_bypass_level']);
        }
		private $capabilities = array ('read', 'edit_posts', 'publish_posts', 'moderate_comments', 'activate_plugins');
		
		function validate_dropdown($array, $key, $value) {
			// make sure that the capability that was supplied is a valid capability from the drop-down list
			if (in_array($value, $array))
				return $value;
			else // if not, load the old value
				return $this->options[$key];
		}   
    } // end class declaration
} // end of class exists clause
 
?>