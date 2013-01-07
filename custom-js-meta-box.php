<?php 

/*
Plugin Name: Custom JavaScript Meta Box
Plugin URI: 
Description: Abiliy to place custom JavaScript on individual pages and posts. Once enabled a custom text box will apear on page and post write panels and the custom JavaScript will be written at the bottom of the HTML document.
Author: CTLT Dev
Version: 1.0
Author URI: 

*/

Class Custom_JavaScript_Meta_Box {
	static $instance;
	
	function __construct() {
		self::$instance = $this;
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * init function.
	 * 
	 * @access public
	 * @return void
	 */
	function init(){
		// filters
		add_action( 'wp_footer',array( $this,'display_js'), 999 );
		
		// admin side
		/* Use the admin_menu action to define the custom boxes */
		add_action( 'admin_menu', array( $this, 'init_meta_box' ) );
	
		/* Use the save_post action to do something with the data entered */
		add_action('save_post',  array( $this, 'save_meta_data' ) );
		
		add_action( 'admin_print_styles-post-new.php', array( $this,'script_and_style') );
		add_action( 'admin_print_styles-post.php',array( $this,'script_and_style') );

	}

	
	/**
	 * display_js function.
	 * 
	 * @access public
	 * @return void
	 */
	function display_js() {
 		global $post;
 		
		if( is_single() || is_page() ):
		
	 		$custom_field = trim( get_post_meta( $post->ID, '_custom_js' , true ) );
			
	 		if( !empty(  $custom_field ) )
echo '<!-- JavaScript FROM META BOX -->
<script type="text/javascript">
//<![CDATA[ 
';
echo $custom_field;
echo '
//]]>
</script>
';
				
		endif;
	}
	
	
	
	// ADMIN SIDE
	/* Adds a custom section to the "advanced" Post and Page edit screens */
	function script_and_style(){
		global $post;
		
		if( !in_array($post->post_type, array('post','page') ) )
			return;
		// add javascript 
		wp_enqueue_script( 'codemirror',  plugins_url( 'custom-js-meta-box/js/codemirror.js' ), array( 'jquery' ) );
		wp_enqueue_script( 'codemirror-script-js', plugins_url( 'custom-js-meta-box/js/javascript.js' ), array( 'codemirror' ) );
		// add just the styles
		wp_enqueue_style( 'codemirror-style', plugins_url( 'custom-js-meta-box/css/codemirror.css' ) );
		
	}
	
	
	/**
	 * init_meta_box function.
	 * 
	 * @access public
	 * @return void
	 */
	function init_meta_box() {
			
		// on posts 
		add_meta_box( 'custom_js_meta_box', __( 'Custom JavaScript', 'custom-js-meta-box' ), array( $this, 'display_meta_box' ), 'post', 'advanced','low' );
		
		// on pages
		add_meta_box( 'custom_js_meta_box', __( 'Custom JavaScript', 'custom-js-meta-box' ), array( $this, 'display_meta_box' ), 'page', 'advanced','low' );
		
	}
	
	
	/**
	 * meta_box_display function.
	 * 
	 * @access public
	 * @return void
	 */
	function display_meta_box() {
		global $post;
		$custom_js = get_post_meta( $post->ID, '_custom_js', true );
   		
   		// Use nonce for verification
   		echo '<input type="hidden" name="custom_js_mate_box_noncename" id="custom_js_mate_box_noncename" value="' .wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
		echo __( 'The JavaScript will appear only on this ','custom-js-meta-box' ) . $post->post_type . __( ' and will be included at the END of the HTML','custom-js-meta-box' ); 
		// The actual fields for data entry
		?>
		<pre><code> &lt;script type="text/javascript"&gt;</code></pre>
		<textarea name="custom_js_meta_box"  id="custom-js-meta-box"><?php echo esc_textarea( $custom_js ); ?></textarea>
		<pre><code> &lt;/script&gt;</code></pre>
		<?php
		
	}
	
	
	/**
	 * save_meta_data function.
	 * 
	 * @access public
	 * @param mixed $post_id
	 * @return void
	 */
	function save_meta_data( $post_id ) {
		
	
		// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
		// to do anything
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			return $post_id;
		
		if ( !wp_verify_nonce( $_POST['custom_js_mate_box_noncename'], plugin_basename(__FILE__) ))
				return $post_id;
		
		// only update the data if it is a string	
		if( is_string( $_POST['custom_js_meta_box'] ) )
			add_post_meta( $post_id, '_custom_js', $_POST['custom_js_meta_box'], true) or update_post_meta( $post_id, '_custom_js', $_POST['custom_js_meta_box'] );
		
		return $post_id;

	}

}
new Custom_JavaScript_Meta_Box;