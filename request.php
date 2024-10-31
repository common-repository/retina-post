<?php
define("PLUGIN_PATH", dirname(__FILE__) );
define("LINE_max_len", "75");
define("MAX_TOKEN_NO", "3");
define("MIN_TOKEN_NO", "2");


//*debug*/ $fh = fopen('debug.php', 'w') or die("can't open file");	

class Challenge {
	//private $custom_title_len, $custom_desc_len, $custom_link_len;

	public $font_size, $font_type;
	public $color, $BG_color;
	
	private $marks_generated, $symbol_list, $symbol_curr, $color_list, $color_curr;
	
	function __construct() {
		$this->font_size    = 11;
		$this->font_type  = PLUGIN_PATH.'/verdana.ttf';
				
		$this->color	= '#000000';
		$this->BG_color = '#FFFFFF';

		/// Random symbols
		$this->symbol_list = "~@#$&%+=werTyafgHzxcbm?<01234578";
		$this->symbol_list = str_split($this->symbol_list);
		shuffle (&$this->symbol_list );
		$this->symbol_curr = 0;
		/// end Random symbols

		/// Random colors
		$this->color_list		= array('#382D2C', '#806D7E', '#252BAD', '#2B65EC', '#F6358A', '#B03547', '#F433FF', '#B048B5', '#4EE2EC', '#3EA99F', '#AFC7C7', '#347235', '#667C26', '#87F717', '#FDD017', '#F62217', '#F88017');
		shuffle (&$this->color_list );
		$this->color_curr= 0;
		/// end Random colors
		
		$this->marks_generated = array();
	}

	/**
	 *  Get the first tokens (words) from a string 	
	 *	@param string  $string
	 *  @param integer $limit  the maximum number of letters;
	 *	return array of strings - the words.
	 */

	private function get_tokens($string, $limit)
	{
		$string = substr($string, 0, $limit+50); 	// limit the caracters, but take sufficient for the wrap to work 	
		
		$string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');  // decode so that &amp will be & and can be tested if it's an alfanum
																	 // encoded just when creating the HTML
		$string = str_replace("\n", " ", $string);		// replace \n with ' '
		$string = str_replace("\r", " ", $string);		// replace \r with ' '
		$string = str_replace("\t", " ", $string);		// replace \t with ' '
		//$string = str_replace("&nbsp", " ", $string);	// replace &nbsp; with ' '
					
		$string = wordwrap($string, $limit, "%$%");		// wrap will introduce the delimiter %$% after the last WORD that is inside the character interval 		
		$string_arr = explode("%$%", $string);			// make an array from the string sliced at the delimiter 
		$string = $string_arr[0];						// take first slice
		
		$string 	 = preg_replace( '/\s+/', ' ', trim( $string ) );  // remove duplicate spaces
		
		$words_array = explode(" ",$string);
		return $words_array;
	}

	/**
	 *  Choose random words (tokens) from a string given as an array of words
	 *	@param array of strings $token_array  the array of strings
	 *  @param string $rand_token_no  the desired number of tokens; sometimes it could return less !!!
	 *	return array - The position .
	 */

	private function random_tokens($token_array, $rand_token_no)
	{
		foreach ($token_array as $i => $word)
		{
			if (strlen(preg_replace('/[^A-Za-z0-9]/', '', $word ))>2)
				$good_tokens_index[]=$i;
		}	
		shuffle($good_tokens_index);
		
		$good_tokens_count = count($good_tokens_index);
		if ($good_tokens_count<$rand_token_no)
			$rand_token_no = $good_tokens_count;
		
		$rand_tokens = array($rand_token_no);
		for ($i=0; $i<$rand_token_no; $i++)
		{	
			$rand_tokens[$i] = $good_tokens_index[$i];
		}
		return $rand_tokens;
	}	

	/**
	 *  Markes one letter from the token, the token will be replace by an image
	 */
	private function mark_token($token)
	{
		$length = strlen ($token);

		//select a random character that is alfanum	
		$random_char_pos = mt_rand(0, $length-1); 	
		while (!ctype_alnum($solution_char=substr($token, $random_char_pos, 1)))
				$random_char_pos = mt_rand(0, $length-1); 

		$symbol_color = '#00AA00'; // de pus ceva mai avansat
		
		// Show a challenge with symbols
		//$highlight_color = '';
		//$symbol = $this->next_mark('symbol');
		
		$symbol = '';
		$highlight_color = $this->next_mark('color');
		
		$marked_token = '<img style="border:none;position:relative;top:4px" src="data:image/gif;base64,'.$this->gen_image($token, $random_char_pos, $symbol, $highlight_color, $this->color, $this->BG_color, $symbol_color).'"/>';

		return array($marked_token, $solution_char) ;
	}
	
	/**
	 *  Get the image data as inline data
	*/
	
	private function color_to_RGB ($color_str) 
	{
			preg_match("/^[#|]([a-f0-9]{2})?([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})$/i", $color_str, $matches);  
			return   array( 	'r' => hexdec($matches[2]),
							'g' => hexdec($matches[3]),
							'b' => hexdec($matches[4]),
							'a' => hexdec(!empty($matches[1])?$matches[1]:0));
	}
	
	private function gen_image($word, $mark_pos, $symbol, $highlight_color, $color, $BG_color, $symbol_color)
	{
		$font_type  = $this->font_type;
		$font_size 	= $this->font_size;
		$font_angle = 0;
		$letter_spaceing = 1;

		$bbox = imagettfbbox($font_size , $font_angle , $font_type, $word);
		$word_width  = max($bbox[0], $bbox[4]) - min($bbox[0], $bbox[4]) + strlen($word)*$letter_spaceing;
		$word_height = max($bbox[1], $bbox[5]) - min($bbox[1], $bbox[5]);

		$left = 1;
		$right= 0;
		$top = 2;
		$bottom = 4;

		$width 	= $left + $word_width  + $right;
		$height = $top 	+ $word_height + $bottom;

		$img = imagecreatetruecolor($width ,  $height);	// create image so the box will fit

		$BG_color = $this->color_to_RGB($BG_color);
		$BG_color = imagecolorallocatealpha($img, $BG_color['r'], $BG_color['g'], $BG_color['b'], $BG_color['a']);

		imagefilledrectangle($img, 0, 0, $width, $height, $BG_color);

		$color = $this->color_to_RGB($color);
		$color = imagecolorallocatealpha($img, $color['r'], $color['g'], $color['b'], $color['a']);

		$highlight_color = $this->color_to_RGB($highlight_color);
		$highlight_color = imagecolorallocatealpha($img, $highlight_color['r'], $highlight_color['g'], $highlight_color['b'], $highlight_color['a']);

		$char_pos = 0;
		$pos_x = $left;
		$array = str_split($word);

		foreach($array as $char) {
			$bbox = imagettfbbox($font_size , $font_angle, $font_type, $char);
			$char_width   = max($bbox[0], $bbox[4]) - min($bbox[0], $bbox[4]);

			$stuffing_left = 0;
			$stuffing_right = 0;
			imagettftext($img, $font_size , $font_angle , $pos_x+$stuffing_left, $top+$word_height, $color, $font_type, $char);
			
			if ( $char_pos == $mark_pos) //in_array($char_pos, $symbols) ) 
			{
				$highlight_to_left = 1;
				$highlight_to_right = 1;
				$mark_height = $top+$word_height + 1;	
				if ($char_width<5) $mark_height -= 1;	// for little words => underline is taller
				imagefilledrectangle($img, $pos_x - $highlight_to_left, $mark_height, $pos_x+$char_width+$highlight_to_right, $height, $highlight_color);
			}
				
			$pos_x += $stuffing_left+ $char_width + $letter_spaceing + $stuffing_right;
			$char_pos++;
		}
		
		ob_start();
		imagegif($img);
		$imageData = ob_get_contents();
		ob_end_clean();
		
		imagedestroy($img);
		return base64_encode( $imageData );
	}
	
	private function next_mark($type)
	{		
		$list = $type."_list";
		$curr = $type."_curr";
		$mark  = $this->{$list}[$this->$curr];
		$this->marks_generated[] = $mark;  
		$this->$curr++;
		return $mark;
	}
	
	public function get_marks()
	{
		return $this->marks_generated;	
	}
	
	/**
	 *  Generate HTML code containig a part of the challenge
	 *  The HTML contains the marked and unmarked words enclosed in a div
	 *  At the end a link to the article is added (like a "Read more")
	 *	@param string  $string the original content 
	 *	@param integer $html_len the maximum number of character
	 *  @param integer $tokens_no  the maximum number of marked words
	 *	@param string  $link (optional) Read More URL
	 *	return array of string  - The generated HTML and the expected code.
	 */
	public function generate_HTML($string, $html_len, $tokens_no, $link=null)
	{
		$tokens 	 = $this->get_tokens($string, $html_len);
		$rand_tokens = $this->random_tokens($tokens, $tokens_no);		
		$html = '';

		$expectedCode 	 ='';
		$marked_token_no = 0;
		$tokens_no		 = count($tokens);
		
		for ($i=0; $i<$tokens_no; $i++)
		{
			if (in_array($i, $rand_tokens)){
				list($marked_token, $marked_letters) = $this->mark_token($tokens[$i]);
				$expectedCode .= $marked_letters;
				$html .= $marked_token.' ';			
				$marked_token_no++;
			} 
			else{
				$html .= htmlentities( $tokens[$i], ENT_QUOTES, 'UTF-8').' ';
			}
		}
		//$html .= '<small>...</small>';
		if (!empty($link))
			$html .=' <a id="Retina_link" tabindex="-1" style="color:#00AA00;font-size:'.($this->font_size+5).'pt;font-weight:bold;text-decoration:none;" target="_blank" href="'.$link.'">&raquo;</a>';

		return array($html, $expectedCode);
	}
}

class RetinaPost {
	protected   $result;		// previous result
	protected 	$db_name;
	
	function __construct()
	{
		global $wpdb;
		$this->create_DB();
	}
		
	protected function create_DB()
	{
		global $wpdb;
		$this->db_name = $wpdb->prefix.'CAPTCHA_RetinaPost';

		if( $wpdb->get_var( "SHOW TABLES LIKE '$this->db_name' ") != $this->db_name )
		{
 			$sql = "CREATE TABLE IF NOT EXISTS $this->db_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				`challenge` varchar(33) NOT NULL,
				`timeOut` bigint(10) NOT NULL,
				`expectedCode` varchar(33) NOT NULL,
				PRIMARY KEY  (`id`),
				KEY `challenge` (`challenge`)
				);" or die ('Data Base error (create table)');
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql);
		}		
	}
	
	protected function save_DB( $challenge, $expectedCode )
	{
		global $wpdb;
		
		$timeOut = time() + 600;   // Expira dupa 10 min
		
		$wpdb->insert( $this->db_name, array( 'challenge' => $challenge, 'timeOut' => $timeOut, 'expectedCode' => $expectedCode ), array( '%s', '%d', '%s' ) );		
		$wpdb->query("DELETE FROM $this->db_name WHERE  ".time()." > timeOut LIMIT 100 ");	
	}	
	protected function get_DB( $challenge, $expectedCode )
	{
		global $wpdb;
		
		$query = $wpdb->prepare( "SELECT COUNT(*) AS count FROM " . $this->db_name . " WHERE challenge=%s  AND expectedCode=%s AND timeOut> %d ", $challenge, $expectedCode, time() );
		
		$result = $wpdb->get_row( $query );
		
		if( $result->count == 1 )
			return true;
		else
			return false;
	}

	public function get(array $params_arr)
	{	
		$text 		= $params_arr["text"]; 
		$link		= $params_arr["link"];	
		$message 	= $params_arr["message"];
		$tab_index 	= $params_arr["tab_index"];  			// the next tab index
		
		$text= trim(strip_tags($text));	
		
		//*debug*/ fwrite($fh, "<br/> text = $text");
		
		$link		= trim(strip_tags($link));
		$message 	= strip_tags($message); 	
		$message 	= (!empty($message) && strlen($message)<100)?$message:'Insert the colored letters';
		$tab_index = (!empty($tab_index) && $tab_index>=0 && $tab_index<=50)?$tab_index:'';
		
		$FIRST_LINE_text = !empty($text)? $text : "Retina Post engages users to read more articles and fights spam ";
		$LINE_link		 = !empty($text) ? $link : "";
				
		// Generare date de identificare
		$challenge = uniqid(mt_rand(0,99999)).$_SERVER['REMOTE_ADDR'];
				
		// Generare cod subliniat
		$expectedCode= '';
		$no_tokens= mt_rand( MIN_TOKEN_NO, MAX_TOKEN_NO);
		$Challenge = new Challenge( );
		
		list($FIRST_LINE_text,$expectedCode) = $Challenge->generate_HTML($FIRST_LINE_text, LINE_max_len, $no_tokens, $LINE_link);
		$HIGHLIGHT_colors = $Challenge->get_marks();
		$symbols ='';   // Show a challenge with symbols => //$symbols = $Challenge->get_marks();
		// end GENERARE COD SUBLINIAT

		// INSERARE IN BD A DATELOR FOLOSITE LA VERIFICARE
		$this->save_DB( $challenge, $expectedCode );		
		// end INSERARE IN BD A DATELOR FOLOSITE LA VERIFICARE
		
		// OUTPUT JAVASCRIPT CODE
		ob_start();
?>
<script type="text/javascript">
var z_index = 1000; 															

if (typeof window['Retina_inputFocus'] !== 'function') {
     function Retina_inputFocus( this_elem )													
     {				  																	
     	z_index = parseInt( z_index ) + 1;											
     	this_elem.style.zIndex = z_index;				
     	var color = this_elem.style.borderBottomColor;		
     	this_elem.style.borderColor = color;
		this_elem.select();
     }																				
}
if (typeof window['Retina_inputBlur'] !== 'function') {

     function Retina_inputBlur( this_elem )													
     {				  																	
     	this_elem.style.zIndex = z_index-1;			
     	var color = this_elem.style.borderBottomColor;		
     	this_elem.style.borderColor = "#c4c4c4";   	
     	this_elem.style.borderBottomColor = color;  	
     }																				
}
if (typeof window['Retina_keyUp'] !== 'function') {

     function Retina_keyUp(id )													
     {	
		var index  = parseInt(id);
		var char=document.getElementById("Retina_edit_"+index).value;		
		var response = document.getElementById("Retina_response");	
		var alfanum = /^[0-9a-zA-Z]+$/;
		
		if(char.match(alfanum))
		{
			<?php /* Add the new char to response */?>
			response.value = response.value.substr(0, parseInt(id)) + char + response.value.substr(parseInt(id)+char.length);   
		
			<?php /* Tab to next input */?>
			index = parseInt(index)+1;		
			if ( document.getElementById("Retina_no_ids").value> index )
				document.getElementById("Retina_edit_"+index).focus();	
		}
     }																				
}
if (typeof window['Retina_show_challenge'] !== 'function') {

     function Retina_show_challenge()						   									
     {
		document.getElementById("Retina_message").style.fontSize = <?php echo $Challenge->font_size-1; ?>+'pt';
		document.getElementById("Retina_text").style.display	 = 'block';
		document.getElementById("Retina_inputs").style.opacity	 = '1';
	 
		var retina_text = document.getElementById("Retina_text");
		var images 		= retina_text.getElementsByTagName("img");
		
		for(var i=0; images.length>i ; i++)
		{
			if ( 0 >= images[i].src.length ){	
				images[i].src = retina_img_src.shift();
			}
		}
		document.getElementById("Retina_show_challenge_button").style.display='none';
    }
}	

<?php 	
	///// AFISEAZA CHALLENGE HTML
	$CHALLENGE_html = $FIRST_LINE_text; 
	$CHALLENGE_html = str_replace('"','\"',$CHALLENGE_html);
?>		

var challange_body= " " +
"	<div id='Retina_table' style='border:none;margin:3px;padding:0px; min-width:50px; max-width:100%;z-index:1000;text-align:right;text-decoration:none;padding:5px; background-color:<?php echo $Challenge->BG_color;?>!important; border: 1px solid #FFFFFF;border-radius:10px;'> " +
"		<div id='Retina_message' style='display:block;width:auto;height:auto; position:relative;top:0px;left:0px;z-index:1000;font-size:<?php echo $Challenge->font_size; ?>pt;font-family:<?php echo $Challenge->font_type; ?>,Arial;text-align:center;font-style:normal; font-variant:normal; font-weight:normal; word-spacing:normal; letter-spacing:normal; text-decoration:none; text-transform:none; text-indent:0ex;color:#777;margin:0px;padding:0px;padding-bottom:5px;border:0px;'><?php echo $message;?></div>" +
"		<div  id='Retina_text'  style='width:auto;height:auto;position:relative;top:0px;left:0px; z-index:1000;color:<?php echo $Challenge->color; ?>!important; background-color:<?php echo $Challenge->BG_color; ?>!important; font-size:<? echo $Challenge->font_size; ?>pt!important;font-family:<?php echo $Challenge->font_type; ?>, Arial!important;font-style:normal;font-variant:normal;font-weight:normal;text-decoration:none;text-align:center;line-height:120%;margin:0px;padding:0px;border:0px;'>" +
"	 		<?php echo $CHALLENGE_html;	?>" +
"	 	</div>" +
"		<div id='Retina_inputs' style='display:block;width:auto;height:auto;z-index:1000;text-align:center;margin:0px;padding:0px;padding-top:5px;padding-bottom:2px;border:0px;'>";
<?php
	/////  AFISEAZA INPUTURILE
for ($i=0; $i<strlen($expectedCode); $i++)
{
	?>
	challange_body = challange_body + "	<input tabindex='<?php echo $tab_index?>' style='display:inline;z-index:1001; position:relative;top:0px;left:0px; width:12pt;height:15pt; color:<?php echo $Challenge->color;?>!important; background-color:<?php echo $Challenge->BG_color;?>!important; font-size:<?php echo $Challenge->font_size;?>;font-family:<?php echo $Challenge->font_type; ?>,Arial;text-align:center;font-style:normal; font-variant:normal; font-weight:normal; border: 1px solid #eee; text-align:center;text-decoration:none; border-radius: 4px; box-shadow: inset 0 1px 1px rgba(204,204,204,0.95); padding: 0px; text-indent:0px; margin:0 20px 0 20px; border-bottom-style:solid; border-bottom-width:5px; border-bottom-color:<?php echo $HIGHLIGHT_colors[$i];?>' id='Retina_edit_<?php echo $i; ?>' name='Retina_edit_<?php echo $i; ?>' onfocus='Retina_inputFocus(this)' onblur='Retina_inputBlur(this)' onkeyup='Retina_keyUp(\"<?php echo $i;?>\")' value='<?php /*echo $symbols[$i];*/ ?>'  autocomplete='off'/> ";
	<?php
}
	/////  AFISEAZA codul CHALLENGE
?>
challange_body = challange_body +
"		</div>" +
"		<a href='retinapost.com' style='font-size:10px'>RetinaPost.com</a>" +
"		<input type='hidden'  id='Retina_no_ids' name='Retina_response' value='<?php echo strlen($expectedCode); ?> '/>" +
"		<input type='hidden'  id='Retina_response' name='Retina_response' value='                          '  />	 " +
"		<input type='hidden'  id='Retina_challenge' name='Retina_challenge'  value='<?php echo $challenge; ?>'/>	 " +
"	</div> " ;


<?php /* Create container div if doesn't exists */ ?>
if (!document.getElementById('Retina_container'))
{  
	<?php /* Insert before element, if element doesn't exist append as child */ ?>
	<?php if (!empty($insert_before)){ ?>
	var container = document.createElement('div');
	container.setAttribute('id','Retina_container');
	container.setAttribute('style','display:block;position:relative;top:0px;left:0px;z-index:1000;');	
	
	if (document.getElementById('<?php echo $insert_before; ?>'))
		document.getElementById('<?php echo $insert_before; ?>').parentNode.insertBefore(container, document.getElementById('<?php echo $insert_before; ?>'));
	else
		document.body.appendChild(container);
	
	<?php /* Insert here */ ?>
	<?php } else {?>
	document.write("<div class='RetinaPost' id='Retina_container' style='display:block;position:relative;top:0px;left:0px;z-index:1000;'></div>");
	<?php }       ?>
}
document.getElementById('Retina_container').innerHTML = challange_body;

</script>
<?php
		$script = ob_get_contents();
		ob_end_clean();
		
		return $script;	
		//*debug*/ fclose($fh);
	}
	
	/**
	  * Checks the user answer
	  * @return true or false
	*/
	public function check()
	{
		$challenge = trim($_POST["Retina_challenge"]);
		$expectedCode = trim($_POST["Retina_response"]);
		
		if (empty($challenge) || empty($expectedCode)) 
		{
				$this->error = 'Empty challenge or response';
				return false;
		}	
		
		if ($this->get_DB( $challenge, $expectedCode )) {
			$this->error  = '';
			return true;
		}
		else {
			$this->error  = 'Wrong answer';		
			return false;
		}		
	}
	/**
	  * Gets the last error
	  * @return string - empty if no error
	*/
	public function get_error() {
		return $this->error;
	}
}
/*end RetinaPost class*/