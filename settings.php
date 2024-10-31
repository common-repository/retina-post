<?php
if (defined('RETINA_CREATED') === false)
	die('no direct access');
	
if (isset($_POST['blog_options_form_submited'])) {
	array_walk_recursive($_POST, create_function('&$val', '$val = stripslashes($val); $val = trim($val);'));
	
	$options= array(
	'call_to_action'	=>$_POST['call_to_action'],
	'incorrect_response_error'=>$_POST['incorrect_response_error'], 
	'readmore_show' 	=>(bool) $_POST['readmore_show'], 
	'readmore_message'	=>$_POST['readmore_message'], 
	
	'show_in_comments'	=>(bool) $_POST['show_in_comments'],
	'bypass_for_registered_users'=>(bool) $_POST['bypass_for_registered_users'],
	'minimum_bypass_level'=> $this->validate_dropdown($this->capabilities, 'minimum_bypass_level', $_POST['minimum_bypass_level']),
	
	'type_c'			=>($_POST['type_c'])=='feed'?'feed':'text',
	'custom_text_c'	=>$_POST['custom_text_c'],
	'custom_link_c'		=>(strpos($_POST['custom_link_c'], 'http')=== false)? 'http://'.$_POST['custom_link_c'] : $_POST['custom_link_c'],
	'tab_index_c'		=>$_POST['tab_index_c'],
	
	'show_in_registration'=>(bool) $_POST['show_in_registration'],
	'type_r'			=>($_POST['type_r'])=='feed'?'feed':'text',
	'custom_text_r'	=>$_POST['custom_text_r'],
	'custom_link_r'		=>(strpos($_POST['custom_link_r'], 'http')=== false)? 'http://'.$_POST['custom_link_r'] : $_POST['custom_link_r'],
	'tab_index_r'		=>$_POST['tab_index_r']
	);
	
    $this->update_options($this->options_name, $options);
}

/* Recompute options */
$this->options = $this->compute_options($this->options_name);

define("custom_text_LEN", "100");	
define("CUSTOM_LINK_LEN", "250");

?>
<style type="text/css">
.retina_wrap{
	padding:25px;
}
h2{
	font-size:1.2em;
}
h3{
	color:#77BB00;
	font-size:1.1em;
}
h4{
	color:#F2B212;
	font-size:1em;
}
.retina_caption{
	display:inline-block;
	min-width:165px;	
	padding:15px 0px 10px 10px;
}
.retina_caption_small{
	display:inline-block;
	min-width:150px;	
	padding:5px 0px 5px 10px;
}
.retina_header{
	display:inline-block;
	width:215px;	
	padding:5px 0px 25px 10px;
	font-size:1.2em
}
.retina_warning{	
	color:#ee7700;
}
.inline-block{
	display:inline-block;	
}
.right{
	margin-left:50px;
	padding-left:50px;
	border-left: 2px dotted #999999;
}
.hr-1{
	margin:10px;margin-left:200px; text-align: left; width: 25%;
}
.hr-2{
	margin:25px;margin-left:100px; text-align:left; width:25%;
}
.tweet_me{
	margin-left: 30px;
    vertical-align: middle;
	width:100px;
}
.support_buttons{
	display:inline-block;
	margin-left: 30px;
	vertical-align: middle;
}
.support_buttons a{
	display:block;
	width:100px;
	font-size: 14px;
	text-align: center;
	text-decoration:none;
	color: #050505;
	margin-top:3px;
	padding: 3px 10px;
	background: -moz-linear-gradient(top, #e7ffa6 0%,#cee342);
	background: -webkit-gradient( linear, left top, left bottom, from(#e7ffa6), to(#cee342));
	border-radius: 14px;
	border: 1px solid #804600;
	text-shadow: 0px -1px 0px rgba(000,000,000,0.2), 0px 1px 0px rgba(255,255,255,0.4);
}
.read_more_button {
	font-size: 11px;
	text-decoration:none;
	color: #000000;
	padding: 5px 6px;
	background: -moz-linear-gradient( top, #f2f2f2 0%,#e3e3e3 50%,#d1d1d1);
	background: -webkit-gradient(linear, left top, left bottom,from(#f2f2f2),color-stop(0.50, #e3e3e3),	to(#d1d1d1));
	border-radius: 7px;
	border: 1px solid #919191;
	text-shadow: 0px 1px 0px rgba(255,255,255,1),0px 1px 0px rgba(255,255,255,1);
}

</style>
<script type="text/javascript">
	function set_visible(id, visible)
	{ document.getElementById( id ).style.display = visible; }
	function set_value(id, value)
	{ document.getElementById( id ).value = value; }
	function get_value(id)
	{ return document.getElementById( id ).value; }
</script>

<div class="retina_wrap">
   <h2><?php _e('Engage user & Anti spam by Retina Post', 'retina-post'); ?></h2>
   
   <h3 class="inline-block">
	<?php _e('Keep it on to stop spam and invite users to read more.', 'retina-post'); ?>
   </h3>
   <a href="http://twitter.com/home?status=Great%20WordPress%20plugin%3A%20Engage%20user%20and%20Anti%20spam%20Retina%20Post%20http%3A%2F%2Fretinapost.com%20%23engage%20%23wordpress%20%23plugin" target="_blank"><img class="tweet_me" src="<?php echo WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); ?>tweet_me.png" alt="Tweet me" border="0" /></a>
   <div class="support_buttons">
	<a href="http://wordpress.org/tags/retina-post?forum_id=10" target="_blank"><?php _e('Support Forum', 'retina-post'); ?></a>
	<a href="http://retinapost.com/contact/" target="_blank"><?php _e('Contact me', 'retina-post'); ?></a>
   </div>

<form id="blog_options_form" method="post" action="">
   <input type="hidden" name="blog_options_form_submited" value="true" />
	<h2><?php _e('Translate in your language', 'retina-post'); ?></h2>

	<div class="inline-block">
		<div>
			<div class="retina_caption_small"><?php _e('Type the colored letters', 'retina-post'); ?></div>
			<input type="text" name="call_to_action" size="25" value="<?php echo $this->options['call_to_action']; ?>" />
		</div>		
		<div>
			<div class="retina_caption_small"><?php _e('Incorrect response', 'retina-post'); ?></div>
			<input type="text" name="incorrect_response_error" size="25" value="<?php echo $this->options['incorrect_response_error']; ?>" />
		</div>			
		<div>
			<div class="retina_caption_small"><?php _e('Read more', 'retina-post'); ?></div>
			<input type="text"  name="readmore_message" size="25" value="<?php echo $this->options['readmore_message']; ?>" />
		</div>			
	</div>

	<p id="keys_submit" class="submit"><input type="submit" class="button-primary" title="<?php _e('Save RetinaPost options') ?>" value="<?php _e('Save Options') ?> &raquo;" /></p>
    <hr size="2" style="margin:25px; text-align:left; width:75%;"/>

	<h2><?php _e('Advance Setup', 'retina-post'); ?></h2>
	
	<!-- COMMENT SETTINGS -->

	<div>
		<div class="retina_header"><label for="show_in_comments"><?php _e('Enable for comments form', 'retina-post'); ?></label></div>
        <input type="checkbox" name="show_in_comments" value="1" <?php checked(1, $this->options['show_in_comments']); ?> id="show_in_comments"/>    
		&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;
		<label for="readmore_show"><?php _e('Show ', 'retina-post'); ?><b><?php _e('Read More', 'retina-post'); ?></b></label>
		<input type="checkbox" name="readmore_show" value="1" <?php checked(1, $this->options['readmore_show']); ?> id="readmore_show"/>
	</div>
	
    <div class="retina_caption"><?php _e('Advanced Options', 'retina-post'); ?> </div><input type="button"id="settings_visible_c" style="display:none" value="<?php _e('Show/Hide Options', 'retina-post'); ?>"/>
	
	<div id="settings_c">	
    <div>
		<div class="retina_caption"><?php _e('Target', 'retina-post'); ?></div>
        <input type="checkbox" name="bypass_for_registered_users" value="1" <?php checked( 1, $this->options['bypass_for_registered_users'] ); ?> id="bypass_for_registered_users" />
        <label for="bypass_for_registered_users"><?php _e('Hide for Registered Users who can', 'retina-post'); ?></label>
               <?php $this->capabilities_dropdown(); ?>
	</div>
	
	<div>
		<div class="retina_caption">
			<b><?php _e('In comment form promote ', 'retina-post'); ?></b>
		</div>
		<div id="buttons_c" style="display:none">
			<input type="button" id="button_feed_c" value="<?php _e(' your Blog Posts ', 'retina-post'); ?>" />
			&nbsp;&nbsp;<?php _e('-or-', 'retina-post'); ?>&nbsp;&nbsp;
			<input type="button" id="button_text_c"  value="<?php _e('   a  Message   ', 'retina-post'); ?>" /> 		
		</div>
		<input type="text" name="type_c" id="type_c" title="Type 'feed' or 'text'" value="<?php echo $this->options['type_c']; ?>" />
	</div>
	<br/>
	
	<div id="settings_text_c">
		<div id="settings_custom_text_c">
			<?php /*show error*/ if ( $this->error_in_message_c() && empty($this->options['custom_text_c']))   { ?>
								<div class="retina_warning"><?php _e('Provide a simple text', 'retina-post'); ?></div><?php } ?>	
			<div class="retina_caption"><?php _e('Message', 'retina-post'); ?></div>
			<input type="text" name="custom_text_c" 		size="60" maxlength="<?php echo custom_text_LEN; ?>" value="<?php echo $this->options['custom_text_c']; ?>" />
		</div>		 
		
		<div id="settings_text_link_c">
			<div class="retina_caption"><?php _e('Link', 'retina-post'); ?></div>
			<input type="text" name="custom_link_c" 		size="60" maxlength="<?php echo CUSTOM_LINK_LEN; ?>"  value="<?php echo $this->options['custom_link_c']; ?>" />
		</div>
	</div> <!-- end settings_text_c -->
	<div>
		<hr size="1" class="hr-1" />
	</div>
	
	<div id="comment_tab_index">	
		<div class="retina_caption"><b><?php _e('Tab index', 'retina-post'); ?></b></div>
		<input type="text" name="tab_index_c" size="2" maxlength="2" value="<?php echo $this->options['tab_index_c']; ?>" /> 
	</div>	
 
	<div style="display:none">
		<hr size="1" class="hr-1"/>
    </div>
	
	<p class="submit"><input type="submit" class="button-primary" title="<?php _e('Save RetinaPost options') ?>" value="<?php _e('Save Options') ?> &raquo;" /></p>
	</div>
    <hr size="2" class="hr-2"/>
	
	<!-- REGISTER SETTINGS -->

	<div>
		<div class="retina_header"><label for="show_in_registration"><?php _e('Enable for registration form', 'retina-post'); ?></label></div>
        <input type="checkbox" name="show_in_registration" value="1" <?php checked(1, $this->options['show_in_registration']); ?> id="show_in_registration" />        
	</div>		
	  
    <div class="retina_caption"><?php _e('Advanced Options', 'retina-post'); ?></div><input type="button"id="settings_visible_r" style="display:none" value="<?php _e('Show/Hide Options', 'retina-post'); ?>"/>
	<div id="settings_r">
	
	<div>
		<div class="retina_caption">
			<b><?php _e('In registration form promote ', 'retina-post'); ?></b>
		</div>
		<div id="buttons_r" style="display:none">
			<input type="button" id="button_feed_r" value="<?php _e(' your Blog Posts ', 'retina-post'); ?>" />
			&nbsp;&nbsp;<?php _e('-or-', 'retina-post'); ?>&nbsp;&nbsp;
			<input type="button" id="button_text_r"  value="<?php _e('   a  Message   ', 'retina-post'); ?>" /> 
		</div>
		<input type="text" name="type_r" id="type_r" title="Type 'feed' or 'text'" value="<?php echo $this->options['type_r']; ?>"/>
	</div>
	<br/>
	 
	<div id="settings_text_r">
		<div id="settings_custom_text_r">
			<?php /*show error*/ if ( $this->error_in_message_r() && empty($this->options['custom_text_r'])) { ?>
								<div class="retina_warning"><?php _e('Provide a simple text', 'retina-post'); ?></div><?php } ?>
			<div class="retina_caption"><?php _e('Message', 'retina-post'); ?></div>
			<input type="text" name="custom_text_r" 		size="60" maxlength="<?php echo custom_text_LEN; ?>" value="<?php echo $this->options['custom_text_r']; ?>" />	<small><?php echo custom_text_LEN; _e(' max characters','retina-post');?> </small>
		</div>		 
		<div id="settings_text_link_r">
			<div class="retina_caption"><?php _e('Link', 'retina-post'); ?></div>
			<input type="text" name="custom_link_r" 		size="60" maxlength="<?php echo CUSTOM_LINK_LEN; ?>"  value="<?php echo $this->options['custom_link_r']; ?>" />		<small><?php echo CUSTOM_LINK_LEN; _e(' max characters','retina-post');?></small>
		</div>
	</div> <!-- end settings_text_r -->
	<div>
		<hr size="1" class="hr-1" />
	</div>
	
	<div id="register_tab_index">	
		<div class="retina_caption"><b><?php _e('Tab index', 'retina-post'); ?></b></div>
		<input type="text" name="tab_index_r" size="2" maxlength="2" value="<?php echo $this->options['tab_index_r']; ?>" /> 
	</div>

   	<p class="submit"><input type="submit" class="button-primary" title="<?php _e('Save RetinaPost options') ?>" value="<?php _e('Save Options') ?> &raquo;" /></p>
	</div>
    <hr size="2" class="hr-2"/>
   
	<p class="submit"><input type="submit" class="button-primary" title="<?php _e('Save RetinaPost options') ?>" value="<?php _e('Save Options') ?> &raquo;" /></p>
   </form>
   
    <script type="text/javascript">
		set_visible('settings_visible_c',	'inline-block');
		set_visible('settings_visible_r',	'inline-block');
						
		<?php if (!$this->error_in_message_c() ){?>
			set_visible('settings_c',	'none');
		<?php } if (!$this->error_in_message_r() ){?>
			set_visible('settings_r',	'none');
		<?php } ?>
				
		set_visible('settings_text_c',	'none');
		set_visible('buttons_c',		'inline-block');
		set_visible('type_c',			'none');
		
		set_visible('settings_text_r',	'none');
		set_visible('buttons_r',		'inline-block');
		set_visible('type_r',			'none');		  

		jQuery(function($){	
			$('#settings_visible_c').click(function() {	
				$('#settings_c').toggle("slow");		
			return false;
			});
			$('#settings_visible_r').click(function() {	
				$('#settings_r').toggle("slow");		
			return false;
			}); 
			
			// Show comment feed
			$('#button_feed_c').click(function() {	
				$('#settings_text_c').hide();
				document.getElementById('button_feed_c').style.border='solid 2px #50C000'; 
				document.getElementById('button_text_c').style.border='';
				document.getElementById('type_c').value='feed'; 	
			return false;
			}); 
			
			// Show comment text
			$('#button_text_c').click(function() {
				$('#settings_text_c').show("slow");	
				document.getElementById('button_text_c').style.border='solid 2px #50C000'; 
				document.getElementById('button_feed_c').style.border='';
				document.getElementById('type_c').value='text';
			return false;
			}); 
			
			// Show register feed
			$('#button_feed_r').click(function() {
				$('#settings_text_r').hide();
				document.getElementById('button_feed_r').style.border='solid 2px #50C000'; 
				document.getElementById('button_text_r').style.border='';
				document.getElementById('type_r').value='feed';				
			return false;
			}); 
			
			// Show register text
			$('#button_text_r').click(function() {
				$('#settings_text_r').show("slow");	
				document.getElementById('button_text_r').style.border='solid 2px #50C000'; 
				document.getElementById('button_feed_r').style.border='';
				document.getElementById('type_r').value='text';				
			return false;
			});
			
			<?php if($this->options['type_c']=='feed') { ?>			
				$('#button_feed_c').click();
			<?php } else if ($this->options['type_c']=='text') { ?>			
				$('#button_text_c').click();
			<?php } ?>
			
			
			<?php if($this->options['type_r']=='feed') { ?>
				$('#button_feed_r').click();
			<?php } else if ($this->options['type_r']=='text') { ?>
				$('#button_text_r').click();
			<?php } ?>
		});		
   </script>
</div>