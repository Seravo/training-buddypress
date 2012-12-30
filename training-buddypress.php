<?php
  /*
    Plugin Name: Training Buddypress
    Plugin URI: http://seravo.fi
    Description: Experimental training plugin for Buddypress and Events Manager
    Version: 1.0
    Author: Tomi Toivio
    Author URI: http://seravo.fi
    License: GPL2
 */
  /*  Copyright 2012 Tomi Toivio (email: tomi@seravo.fi)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/* Requires the Events manager plugin and Buddypress */

/* Add meta fields to trainings posts  */ 
function tm_add_metafields_trainings($result, $EM_Event) {
	 if (has_term( 'koulutukset', 'event-categories')) {
			add_post_meta($EM_Event->post_id, 'audience', 'General audience', true);
			add_post_meta($EM_Event->post_id, 'prerequisites', 'No prerequisite knowledge', true);
			add_post_meta($EM_Event->post_id, 'test', 'Does not prepare for a test', true);
			add_post_meta($EM_Event->post_id, 'certification', 'Does not prepare for a certification', true);
			add_post_meta($EM_Event->post_id, 'price', 'Free', true);
			add_post_meta($EM_Event->post_id, 'equipment', 'Equipment is not needed', true);
			add_post_meta($EM_Event->post_id, 'testprepare', 'Does not prepare for a test', true);
			add_post_meta($EM_Event->post_id, 'moreinfo', 'No additional information', true);			
			}
}
add_filter('em_event_save', 'tm_add_metafields_trainings',10,2);

/* Additional info for training posts */ 

function tm_trainings_post_author($content){
	if (has_term( 'koulutukset', 'event-categories')) {
	   	global $post;
		global $bp;
	   	$tm_group_id = get_post_meta($post->ID, '_group_id', true);
		$groupinfo = groups_get_group( array( 'group_id' => $tm_group_id ) );
		$content .= '<h2>Company</h2><p><a href="' . get_site_url() . '/user-groups/' . $groupinfo->slug .  '">' . $groupinfo->name . '</a></p>';
	     	$tags = get_the_terms($EM_Event->post_id, EM_TAXONOMY_TAG);
		if( is_array($tags) && count($tags) > 0 ){
			$content .= '<h2>Training tags</h2>';
			$tags_list = array();
			foreach($tags as $tag){
			$link = get_term_link($tag->slug, EM_TAXONOMY_TAG);
			if ( is_wp_error($link) ) $link = '';
			$tags_list[] = '<a href="'. $link .'">'. $tag->name .'</a>';
		}
		$content .= '<p>' . implode(', ', $tags_list) . '</p>';
		}
  		$content .= "<h2>Additional information</h2>";
  	  	$content .= "<p>Is a test: " . get_post_meta($post->ID, 'test', true);
  	  	$content .= "</p><p>Certification: " . get_post_meta($post->ID, 'certification', true);
  	  	$content .= "</p><p>Price: " . get_post_meta($post->ID, 'price', true);
  	  	$content .= "</p><p>Equipment: " . get_post_meta($post->ID, 'equipment', true);
  	  	$content .= "</p><p>Prepares for a test: " . get_post_meta($post->ID, 'testprepare', true);
  	  	$content .= "</p><p>More info: " . get_post_meta($post->ID, 'moreinfo', true);
  	  	$content .= "</p><p>Prerequisites: " . get_post_meta($post->ID, 'prerequisites', true);
		$content .= "</p>";
	}
	return $content;
}
add_filter('em_event_output','tm_trainings_post_author');

/* Add shortcode for event tags */ 
function tm_trainings_tags( $atts ){
 	$tm_trainings_tags = get_terms('event-tags','hide-empty=0&orderby=id');
	$sep = '';
	echo '<h2>Training tags</h2><p>';
	foreach ( $tm_trainings_tags as $tm_trainings_tags ) {
			if( ++$count > 60 ) break;  
			echo $sep . '<a href="' . get_term_link($tm_trainings_tags) . '">' . $tm_trainings_tags->name . '</a>';
			$sep = ', '; 
		}
	echo '</p>';
}
add_shortcode( 'tm_trainings_tags', 'tm_trainings_tags' );

/* Add stuff to Buddypress groups test */

function bp_group_meta_init() {
function custom_field($meta_key) {
	 return groups_get_groupmeta(bp_get_group_id(), $meta_key) ;
}
function group_header_fields_markup() {
	 global $bp, $wpdb;?>
	 <h2>Company information</h2><label for="companyurl">Company URL</label>
	 <input id="companyurl" type="text" name="companyurl" value="<?php echo custom_field('companyurl'); ?>" />
	 <br>
	 <label for="companyphone">Company phone</label>
	 <input id="companyphone" type="text" name="companyphone" value="<?php echo custom_field('companyphone'); ?>" /> 
	 <br>
	 <label for="companyemail">Company email</label>
	 <input id="companyemail" type="text" name="companyemail" value="<?php echo custom_field('companyemail'); ?>" /> 
	 <br>
	 <label for="companyaddress">Company address</label>
	 <input id="companyaddress" type="text" name="companyaddress" value="<?php echo custom_field('companyaddress'); ?>" /> 
	 <br>
	 <label for="companyservices">Company services</label>
	 <input id="companyservices" type="text" name="companyservices" value="<?php echo custom_field('companyservices'); ?>" /> 
	 <br>
	 <?php }
function group_header_fields_save( $group_id ) {
	 global $bp, $wpdb;
	 $plain_fields = array(
	 'companyurl',
	 'companyphone',
	 'companyemail',
	 'companyaddress',
	 'companyservices'
	 );
	 foreach( $plain_fields as $field ) {
	 $key = $field;
	 if ( isset( $_POST[$key] ) ) {
	    $value = $_POST[$key];
	    groups_update_groupmeta( $group_id, $field, $value );
	    			     }
	    }
}
add_filter( 'groups_custom_group_fields_editable', 'group_header_fields_markup' );
add_action( 'groups_group_details_edited', 'group_header_fields_save' );
add_action( 'groups_created_group',  'group_header_fields_save' );
 
// Show the custom field in the group header
function show_field_in_header( ) {
	 echo '<div><h3>Information</h3>';
	 echo '<p>Website: <a href="'; 
	 echo custom_field('companyurl'); 
	 echo '">'; 
	 echo custom_field('companyurl'); 
	 echo '</a></p>';
	 echo '<p>Phone: ' . custom_field('companyphone') . '</p>'; 
	 echo '<p>Email: ' . custom_field('companyemail') . '</p>'; 
	 echo '<p>Address: ' . custom_field('companyaddress') . '</p>'; 
	 echo '<p>Services: ' . custom_field('companyservices') . '</p></div>'; 
	 }
add_action('bp_group_header_meta' , 'show_field_in_header') ;
}
add_action( 'bp_include', 'bp_group_meta_init' );
?>
