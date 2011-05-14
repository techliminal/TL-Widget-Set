<?php

/**
 * Plugin name: Techliminal Link Widget
 *
 * adapted from the general Widgets classes
 */

/**
 * JPages widget class
 *
 *
 */
class  JGI_Widget_Pages extends WP_Widget {

	function JGI_Widget_Pages() {
		$widget_ops = array('classname' => 'jgi_widget_pages', 'description' => __( 'Page Navigation Within a Site Area.  Lists children of current page, and more.') );
		$this->WP_Widget('jgi-pages', __('JPages'), $widget_ops);
	}

	// displays the appropriate page navigation for a given page
	function widget( $args, $instance ) { 


		extract( $args );


		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'In This Section' ) : $instance['title']);
		$sortby = empty( $instance['sortby'] ) ? 'menu_order' : $instance['sortby'];
		$exclude = empty( $instance['exclude'] ) ? '' : $instance['exclude'];
		$levels = empty($instance['levels']) ? 1 : $instance['levels'];
		$parent = empty($instance['parent']) ? 0 : intval($instance['parent']);
		$child_of = empty($instance['child_of']) ? 0 : intval($instance['child_of']);
		
		if (!is_page() && !is_home() && !$child_of){
			return;
		}
		
		global $wp_query;
		global $post;		
		
		$page_id = $post->ID;
		$page_ancestors = get_post_ancestors($page_id);
		
		if (isset($page_ancestors[0])){
			$page_parent = $page_ancestors[0];
		} else {
			$page_parent = 0;
		}
		
		if ($child_of == 0){
			$child_of = $page_id;
		}

		if ( $sortby == 'menu_order' )
			$sortby = 'menu_order, post_title';
		$out = wp_list_pages( apply_filters('widget_pages_args', array(
				'title_li' => "", 
				'echo' => 0, 
				'sort_column' => $sortby, 
				'exclude' => $exclude,
				'depth' => $levels,
				'child_of' => $child_of) ) );
				
		if ($page_parent != 0 && $parent == 1){
			$parent_post = get_post($page_parent);
			$parent_link = "<li class='parent'>parent: " . $page_parent . "</li>";
		} else {
			$parent_link = "";
		}
		
		$current = "<li class='current'>$post->title</li>";
		
		if (empty($out)){
			$out = wp_list_pages( apply_filters('widget_pages_args', array(
					'title_li' => "", 
					'echo' => 0, 
					'sort_column' => $sortby, 
					'exclude' => $exclude,
					'depth' => 1,
					'child_of' => $page_parent) ) );
				
		}
		echo $before_widget;
		if ( $title)
			echo $before_title . $title . $after_title;
		?>
		<ul>
			<?php echo $parent_link; echo $current; echo $out; ?>
		</ul>
		<?php
			echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		if ( in_array( $new_instance['sortby'], array( 'post_title', 'menu_order', 'ID' ) ) ) {
			$instance['sortby'] = $new_instance['sortby'];
		} else {
			$instance['sortby'] = 'menu_order';
		}

		$instance['exclude'] = strip_tags( $new_instance['exclude'] );
		$instance['levels'] = intval($new_instance['levels']);
		$instance['child_of'] = intval($new_instance['child_of']);
		if (isset($new_instance['parent'])){
			$instance['parent'] = true;
		}
	
		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'sortby' => 'post_title', 'title' => '', 'exclude' => '', 'parent' => false, 'levels'=>'') );
		$title = esc_attr( $instance['title'] );
		$exclude = esc_attr( $instance['exclude'] );
		$levels = esc_attr($instance['levels']);
		$child_of = esc_attr($instance['child_of']);
		
	?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p>
			<label for="<?php echo $this->get_field_id('sortby'); ?>"><?php _e( 'Sort by:' ); ?></label>
			<select name="<?php echo $this->get_field_name('sortby'); ?>" id="<?php echo $this->get_field_id('sortby'); ?>" class="widefat">
				<option value="post_title"<?php selected( $instance['sortby'], 'post_title' ); ?>><?php _e('Page title'); ?></option>
				<option value="menu_order"<?php selected( $instance['sortby'], 'menu_order' ); ?>><?php _e('Page order'); ?></option>
				<option value="ID"<?php selected( $instance['sortby'], 'ID' ); ?>><?php _e( 'Page ID' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('exclude'); ?>"><?php _e( 'Exclude:' ); ?></label> <input type="text" value="<?php echo $exclude; ?>" name="<?php echo $this->get_field_name('exclude'); ?>" id="<?php echo $this->get_field_id('exclude'); ?>" class="widefat" />
			<br />
			<small><?php _e( 'Page IDs, separated by commas.' ); ?></small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('child_of'); ?>"><?php _e( 'Show only children of this page:' ); ?></label> <input type="text" value="<?php echo $child_of; ?>" name="<?php echo $this->get_field_name('child_of'); ?>" id="<?php echo $this->get_field_id('child_of'); ?>" size="4" />
			<br />
			<small><?php _e( 'A single Page ID' ); ?></small>
		</p>
		<p> 
		<label for="<?php echo $this->get_field_id('levels'); ?>"><?php _e('Show # of Levels:');?></label><input type="text" value="<?php echo $levels; ?>" name="<?php echo $this->get_field_name('levels'); ?>" id="<?php echo $this->get_field_id('levels'); ?>" size=2 /><br/>
		<input class="checkbox" type="checkbox" <?php checked($instance['parent'], true) ?> id="<?php echo $this->get_field_id('parent'); ?>" name="<?php echo $this->get_field_name('parent'); ?>" />
		<label for="<?php echo $this->get_field_id('parent'); ?>"><?php _e('Show Parent Link?'); ?></label><br />
		</p>

<?php
	}

}

/**
 * JLinks widget class
 *
 * @since 2.8.0
 */
class JGI_Widget_Links extends WP_Widget {

	function JGI_Widget_Links() {
		$widget_ops = array('description' => __( "Link Categories" ) );
		$this->WP_Widget('jgi_links', __('JLinks'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args, EXTR_SKIP);

		$show_description = isset($instance['description']) ? $instance['description'] : false;
		$show_name = isset($instance['name']) ? $instance['name'] : false;
		$show_rating = isset($instance['rating']) ? $instance['rating'] : false;
		$show_images = isset($instance['images']) ? $instance['images'] : true;
		$category = isset($instance['category']) ? $instance['category'] : false;
		$sort_rating = isset($instance['sort'])? $instance['sort'] : false;
		$widget_class = isset($instance['widget_class']) ? $instance['widget_class'] : 'linkcat';
		//$widget_image = isset($instance['widget_image']) ? $instance['widget_image'] :  false;
    $widget_image = isset($instance['widget_image']) ? $instance['widget_image'] :  '';
		$title = isset($instance['title']) ? $instance['title'] : "All Links";
		
		if ($sort_rating) {
			$orderby = 'rating';
		} else {
			$orderby = 'name';
		}


		if ( is_admin() && !$category ) {
			// Display All Links widget as such in the widgets screen
			echo $before_widget . $before_title. __('All Links') . $after_title . $after_widget;
			return;
		}

		if (is_user_logged_in() && $category){ // add an 'edit' link to the widget header
			global $userdata;
			if ( ! isset($userdata) ) $userdata = get_currentuserinfo();
			if ($userdata->user_level > 4){ // they are editors or admins
				$after_title = $after_title . "<div class='landing_page_edit'><a class='post-edit-link' href='" . get_bloginfo('url') . "/wp-admin/link-manager.php?cat_id=$category'>Edit</a></div>";
			}
		}
		
		// Set the widget ID.
		$wid = !$category ? 'id="jlinks-0"' : 'id="jlinks-' . intval($category) . '"' ;
		$before_widget = preg_replace('/id="[^"]*"/', $wid, $before_widget);
		
		$class_string = 'class="' . $widget_class . ' widget widget_jgi_links"';
		$before_widget = preg_replace('/class="[^"]*"/', $class_string, $before_widget);
		
		$links_list = get_bookmarks(array('category' => $category, 
			'orderby' => $orderby, 'order' => 'ASC'));
		
		$links_html = "";
		
		if ($widget_image) {
			$links_html = "<img class='jlinks_widget_image' src='$widget_image' alt='$title'>";
		}
		
		foreach($links_list as $link){
			$description = "";
		
			$description .= $show_description ? "- $link->link_description" : "";
			$description .= $show_rating ? " - $link->link_rating" : "";
			
			$target = $link->link_target == "" ? "" : "target=$link->link_target"; 
			
			if ($link->link_url == 'http://none'){
				$link_out = "<li>$link->link_name $description</li>";
			} else {
				$link_out = '<li><a href="' . $link->link_url . '"'. " $target title='$link->link_name'> $link->link_name</a> $description </li>";
			}
			$links_html .= $link_out;
		}

		if ("" != $links_html){
			$links_html = "<ul>" . $links_html . "</ul>";
		}
		
		echo($before_widget . $before_title . $title . $after_title. $links_html . $after_widget);
		
		/*wp_list_bookmarks(apply_filters('widget_links_args', array(
			'title_before' => $before_title, 'title_after' => $after_title,
			'category_before' => $before_widget, 'category_after' => $after_widget,
			'show_images' => $show_images, 'show_description' => $show_description,
			'show_name' => $show_name, 'show_rating' => $show_rating,
			'category' => $category, 'class' => $widget_class . ' widget',
			'orderby' => $orderby, 'order' => 'ASC'
		)));*/
	}

	function update( $new_instance, $old_instance ) {
		$new_instance = (array) $new_instance;
		$instance = array( 'images' => 0, 'name' => 0, 'description' => 0, 'rating' => 0, 'sort'=> 0);
		foreach ( $instance as $field => $val ) {
			if ( isset($new_instance[$field]) )
				$instance[$field] = 1;
		}
		$instance['category'] = intval($new_instance['category']);
		if ($instance['category']){
			$title_cat = get_term($instance['category'], 'link_category');
			if ($title_cat){
				$instance['title'] = $title_cat->name;
			} else {
				$instance['title'] = $category;
		 	}
		 } else {
				$instance['title'] = "All Links";
		 }
		 
		$instance['widget_image'] = $new_instance['widget_image']; 
		$instance['widget_class'] = strip_tags($new_instance['widget_class']);
		
	
		return $instance;
	}

	function form( $instance ) {

		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'images' => true, 'name' => true, 'description' => false, 'rating' => false, 'category' => false, 'sort' => true, 'widget_class' => '', 'widget_image' => '' ) );
		$link_cats = get_terms( 'link_category');
		$widget_class = strip_tags($instance['widget_class']);
		$widget_image = $instance['widget_image'];
		$title = strip_tags($instance['title']);
		
?>
		<p>
		<label for="<?php echo $this->get_field_id('category'); ?>" class="screen-reader-text"><?php _e('Select Link Category'); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>">
		<option value=""><?php _e('All Links'); ?></option>
		<?php
		foreach ( $link_cats as $link_cat ) {
			echo '<option value="' . intval($link_cat->term_id) . '"'
				. ( $link_cat->term_id == $instance['category'] ? ' selected="selected"' : '' )
				. '>' . $link_cat->name . "</option>\n";
		}
		?>
		</select></p>
		<p>
		<input class="checkbox" type="checkbox" <?php checked($instance['images'], true) ?> id="<?php echo $this->get_field_id('images'); ?>" name="<?php echo $this->get_field_name('images'); ?>" />
		<label for="<?php echo $this->get_field_id('images'); ?>"><?php _e('Show Link Image'); ?></label><br />
		<input class="checkbox" type="checkbox" <?php checked($instance['name'], true) ?> id="<?php echo $this->get_field_id('name'); ?>" name="<?php echo $this->get_field_name('name'); ?>" />
		<label for="<?php echo $this->get_field_id('name'); ?>"><?php _e('Show Link Name'); ?></label><br />
		<input class="checkbox" type="checkbox" <?php checked($instance['description'], true) ?> id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>" />
		<label for="<?php echo $this->get_field_id('description'); ?>"><?php _e('Show Link Description'); ?></label><br />
		<input class="checkbox" type="checkbox" <?php checked($instance['rating'], true) ?> id="<?php echo $this->get_field_id('rating'); ?>" name="<?php echo $this->get_field_name('rating'); ?>" />
		<label for="<?php echo $this->get_field_id('rating'); ?>"><?php _e('Show Link Rating'); ?></label>
		<input class="checkbox" type="checkbox" <?php checked($instance['sort'], true) ?> id="<?php echo $this->get_field_id('sort'); ?>" name="<?php echo $this->get_field_name('sort'); ?>" />
		<label for="<?php echo $this->get_field_id('sort'); ?>"><?php _e('Sort by Rating?'); ?></label><br />
		</p>
		<p><label for="<?php echo $this->get_field_id('widget_class'); ?>"><?php _e('Give the widget a custom class (optional):'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('widget_class'); ?>" name="<?php echo $this->get_field_name('widget_class'); ?>" type="text" value="<?php echo $widget_class; ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('widget_image'); ?>"><?php _e('Widget Image URL (displayed above the links):'); ?></label>
		<input class="widefat" id="<php echo $this->get_fiel_id('widget_image'); ?>" name="<?php echo $this->get_field_name('widget_image'); ?>" type="text" value="<?php echo $widget_image; ?>" />
		<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="hidden" value="<?php echo $title; ?>" /></p>
<?php
	}
}



/**
 * Register all of the new widgets at the right time
 *
 * Calls 'widgets_init' action after all of the WordPress widgets have been
 * registered.
 *
 * 
 */

//add_action('widgets_init', create_function('', 'return register_widget("JGI_Widget_Pages");'));
add_action('widgets_init', create_function('', 'return register_widget("JGI_Widget_Links");'));

?>
