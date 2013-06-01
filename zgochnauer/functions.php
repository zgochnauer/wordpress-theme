<?php
	// Make theme available for translation
	// Translations can be filed in the /languages/ directory
	load_theme_textdomain( 'hbd-theme', TEMPLATEPATH . '/languages' );
	
	add_theme_support( 'menus' );

	$locale = get_locale();
	$locale_file = TEMPLATEPATH . "/languages/$locale.php";
	if ( is_readable($locale_file) )
	    require_once($locale_file);

	// Get the page number
	function get_page_number() {
	    if ( get_query_var('paged') ) {
	        print ' | ' . __( 'Page ' , 'hbd-theme') . get_query_var('paged');
	    }
	} // end get_page_number

	// Custom callback to list comments in the hbd-theme style
	function custom_comments($comment, $args, $depth) {
	  $GLOBALS['comment'] = $comment;
	    $GLOBALS['comment_depth'] = $depth;
	  ?>
	    <li id="comment-<?php comment_ID() ?>" <?php comment_class() ?>>
	        <div class="comment-author vcard"><?php commenter_link() ?></div>
	        <div class="comment-meta"><?php printf(__('Posted %1$s at %2$s <span class="meta-sep">|</span> <a href="%3$s" title="Permalink to this comment">Permalink</a>', 'hbd-theme'),
	                    get_comment_date(),
	                    get_comment_time(),
	                    '#comment-' . get_comment_ID() );
	                    edit_comment_link(__('Edit', 'hbd-theme'), ' <span class="meta-sep">|</span> <span class="edit-link">', '</span>'); ?></div>
	  <?php if ($comment->comment_approved == '0') _e("\t\t\t\t\t<span class='unapproved'>Your comment is awaiting moderation.</span>\n", 'hbd-theme') ?>
	          <div class="comment-content">
	            <?php comment_text() ?>
	        </div>
	        <?php // echo the comment reply link
	            if($args['type'] == 'all' || get_comment_type() == 'comment') :
	                comment_reply_link(array_merge($args, array(
	                    'reply_text' => __('Reply','hbd-theme'),
	                    'login_text' => __('Log in to reply.','hbd-theme'),
	                    'depth' => $depth,
	                    'before' => '<div class="comment-reply-link">',
	                    'after' => '</div>'
	                )));
	            endif;
	        ?>
	<?php } // end custom_comments
	
	// Custom callback to list pings
	function custom_pings($comment, $args, $depth) {
	       $GLOBALS['comment'] = $comment;
	        ?>
	            <li id="comment-<?php comment_ID() ?>" <?php comment_class() ?>>
	                <div class="comment-author"><?php printf(__('By %1$s on %2$s at %3$s', 'hbd-theme'),
	                        get_comment_author_link(),
	                        get_comment_date(),
	                        get_comment_time() );
	                        edit_comment_link(__('Edit', 'hbd-theme'), ' <span class="meta-sep">|</span> <span class="edit-link">', '</span>'); ?></div>
	    <?php if ($comment->comment_approved == '0') _e('\t\t\t\t\t<span class="unapproved">Your trackback is awaiting moderation.</span>\n', 'hbd-theme') ?>
	            <div class="comment-content">
	                <?php comment_text() ?>
	            </div>
	<?php } // end custom_pings
	
	// Produces an avatar image with the hCard-compliant photo class
	function commenter_link() {
	    $commenter = get_comment_author_link();
	    if ( ereg( '<a[^>]* class=[^>]+>', $commenter ) ) {
	        $commenter = ereg_replace( '(<a[^>]* class=[\'"]?)', '\\1url ' , $commenter );
	    } else {
	        $commenter = ereg_replace( '(<a )/', '\\1class="url "' , $commenter );
	    }
	    $avatar_email = get_comment_author_email();
	    $avatar = str_replace( "class='avatar", "class='photo avatar", get_avatar( $avatar_email, 80 ) );
	    echo $avatar . ' <span class="fn n">' . $commenter . '</span>';
	} // end commenter_link
	
	// For category lists on category archives: Returns other categories except the current one (redundant)
	function cats_meow($glue) {
	    $current_cat = single_cat_title( '', false );
	    $separator = "\n";
	    $cats = explode( $separator, get_the_category_list($separator) );
	    foreach ( $cats as $i => $str ) {
	        if ( strstr( $str, ">$current_cat<" ) ) {
	            unset($cats[$i]);
	            break;
	        }
	    }
	    if ( empty($cats) )
	        return false;

	    return trim(join( $glue, $cats ));
	} // end cats_meow
	
	// For tag lists on tag archives: Returns other tags except the current one (redundant)
	function tag_ur_it($glue) {
	    $current_tag = single_tag_title( '', '',  false );
	    $separator = "\n";
	    $tags = explode( $separator, get_the_tag_list( "", "$separator", "" ) );
	    foreach ( $tags as $i => $str ) {
	        if ( strstr( $str, ">$current_tag<" ) ) {
	            unset($tags[$i]);
	            break;
	        }
	    }
	    if ( empty($tags) )
	        return false;

	    return trim(join( $glue, $tags ));
	} // end tag_ur_it
	
	// Register widgetized areas
	function theme_widgets_init() {
	    // Area 1
	    register_sidebar( array (
	    'name' => 'Primary Widget Area',
	    'id' => 'primary_widget_area',
	    'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
	    'after_widget' => "</li>",
	    'before_title' => '<h3 class="widget-title">',
	    'after_title' => '</h3>',
	  ) );

	    // Area 2
	    register_sidebar( array (
	    'name' => 'Secondary Widget Area',
	    'id' => 'secondary_widget_area',
	    'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
	    'after_widget' => "</li>",
	    'before_title' => '<h3 class="widget-title">',
	    'after_title' => '</h3>',
	  ) );
	} // end theme_widgets_init

	add_action( 'init', 'theme_widgets_init' );
	
	$preset_widgets = array (
	    'primary_widget_area'  => array( 'search', 'pages', 'categories', 'archives' ),
	    'secondary_widget_area'  => array( 'links', 'meta' )
	);
	if ( isset( $_GET['activated'] ) ) {
	    update_option( 'sidebars_widgets', $preset_widgets );
	}
	// update_option( 'sidebars_widgets', NULL );
	
	// Check for static widgets in widget-ready areas
	function is_sidebar_active( $index ){
	  global $wp_registered_sidebars;

	  $widgetcolums = wp_get_sidebars_widgets();

	  if ($widgetcolums[$index]) return true;

	    return false;
	} // end is_sidebar_active

	function new_excerpt_length($length) {
		return 40;
	}
	add_filter('excerpt_length', 'new_excerpt_length');
///////////////self photo widget //////////////////

add_action( 'widgets_init', 'register_my_widget' );  

function register_my_widget() {  
    register_widget( 'MY_Widget' );  
} 

class My_Widget extends WP_Widget { 

	function My_Widget() {  
		$widget_ops = array( 'classname' => 'portrait', 'description' => __('A widget that displays the authors name and Image ', 'portrait') );  
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'portrait-widget' );  
		$this->WP_Widget( 'portrait-widget', __('Portrait Widget', 'portrait'), $widget_ops, $control_ops );  
	} 

	function widget( $args, $instance ){  
		extract( $args ); 
			
		$title = apply_filters('widget_title', $instance['title'] );  
		$name = $instance['name']; 
		$path = $instance['path']; 
		$imagewh = $instance['imagewh'];  		
		$show_info = isset( $instance['show_info'] ) ? $instance['show_info'] : false;  
		  
		echo $before_widget;  
		  
		// Display the widget title   
		if ( $title )  
			echo $before_title . $title . $after_title;  
		  
		//Display the name 
		if ( $show_info )  
			printf( '
				<div style="width: 140px; height: 140px; margin: 0 auto; margin-top:30px;"> 
					<a href="" style = "">
						<div class = "myphoto" style="width: '.$imagewh.'px; height: '.$imagewh.'px; border-radius: '.($imagewh/2).'px;-webkit-border-radius: '.($imagewh/2).'px; -moz-border-radius: '.($imagewh/2).'px;background-image: url(\''.$path.'\') ;">
						</div>
					</a>
				</div>' 
			);
			
		if ( $name )  
			printf( '<p>' . __('<div style ="max-width:250px; margin: 0 auto;" class = "myselfwidget"> %1$s</div>', 'portrait') . '</p>', $name );  
		  
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {  
		$instance = $old_instance;  
	  
		//Strip tags from title and name to remove HTML  
		$instance['title'] = strip_tags( $new_instance['title'] );  
		$instance['name'] = strip_tags( $new_instance['name'] );
		$instance['path'] = strip_tags( $new_instance['path'] );
		$instance['imagewh'] = strip_tags( $new_instance['imagewh'] );    		
		$instance['show_info'] = $new_instance['show_info'];  
	  
		return $instance;  
	}  		

	function form($instance){
		//Set up some default widget settings.  
		$defaults = array( 'title' => __('Myself', 'portrait'), 'name' => __('Zachary Gochnauer', 'portrait'), 'show_info' => true );  
		$instance = wp_parse_args( (array) $instance, $defaults );?>
		<p>  
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'portrait'); ?></label>  
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />  
		</p>  
		<p>  
			<label for="<?php echo $this->get_field_id( 'name' ); ?>"><?php _e('Your Name and discription:', 'portrait'); ?></label>  
			<input  id="<?php echo $this->get_field_id( 'name' ); ?>" name="<?php echo $this->get_field_name( 'name' ); ?>" value="<?php echo $instance['name']; ?>" style="width:100%;" />  
		</p> 
		<p>  
			<label for="<?php echo $this->get_field_id( 'path' ); ?>"><?php _e('Your Image Location: (full path from wordpres root)', 'portrait'); ?></label>  
			<input id="<?php echo $this->get_field_id( 'path' ); ?>" name="<?php echo $this->get_field_name( 'path' ); ?>" value="<?php echo $instance['path']; ?>" style="width:100%;" />  
		</p> 
		<p>  
			<label for="<?php echo $this->get_field_id( 'imagewh' ); ?>"><?php _e('Your Image Width/Height (one number only numeric chars):', 'portrait'); ?></label>  
			<input id="<?php echo $this->get_field_id( 'imagewh' ); ?>" name="<?php echo $this->get_field_name( 'imagewh' ); ?>" value="<?php echo $instance['imagewh']; ?>" style="width:100%;" />  
		</p>  			
		<p>  
			<input class="checkbox" type="checkbox" <?php checked( $instance['show_info'], true ); ?> id="<?php echo $this->get_field_id( 'show_info' ); ?>" name="<?php echo $this->get_field_name( 'show_info' ); ?>" />   
			<label for="<?php echo $this->get_field_id( 'show_info' ); ?>"><?php _e('Display your image?', 'portrait'); ?></label>  
		</p>  
	<?php
	}
}
?>  