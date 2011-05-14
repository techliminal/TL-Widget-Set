<?php

/**
 * Widget name: Techliminal Link Widget
 *
 * adapted from the general Widgets classes
 */


/**
 * TLLinks widget class
 *
 * @since 2.8.0
 */
class TL_Widget_Links extends WP_Widget {

	function TL_Widget_Links() {
		$widget_ops = array('description' => __( "Link Categories" ) );
		$this->WP_Widget('tl_links', __('TLLinks'), $widget_ops);
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
		$wid = !$category ? 'id="tllinks-0"' : 'id="tllinks-' . intval($category) . '"' ;
		$before_widget = preg_replace('/id="[^"]*"/', $wid, $before_widget);
		
		$class_string = 'class="' . $widget_class . ' widget widget_tl_links"';
		$before_widget = preg_replace('/class="[^"]*"/', $class_string, $before_widget);
		
		$links_list = get_bookmarks(array('category' => $category, 
			'orderby' => $orderby, 'order' => 'ASC'));
		
		$links_html = "";
		
		if ($widget_image) {
			$links_html = "<img class='tllinks_widget_image' src='$widget_image' alt='$title'>";
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

add_action('widgets_init', create_function('', 'return register_widget("TL_Widget_Links");'));

?>
