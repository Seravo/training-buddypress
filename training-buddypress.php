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

/* Training event location widget */ 

class TmTrainingWidget extends WP_Widget {

	function TmTrainingWidget() {
		// Instantiate the parent object
		parent::__construct( false, 'Koulutuksien sijainnit' );
	}

	function widget( $args, $instance ) {
		// Widget output
		echo '<div id="text-6" class="widget widget_text well nav nav-list"><h4 class="widgettitle nav-header">Koulutuksien sijainnit</h4><div class="textwidget">'; 
		Echo EM_Locations::output(array("full" => 1, "long_events" => 1, "category" => 364)); 
		echo '</div></div>';
	}

	function update( $new_instance, $old_instance ) {
		// Save widget options
	}

	function form( $instance ) {
		// Output admin widget options form
	}
}

function tm_register_trainings_widgets() {
	register_widget( 'TmTrainingWidget' );
}
add_action( 'widgets_init', 'tm_register_trainings_widgets' );

/* Add meta fields to trainings posts  */ 
function tm_add_metafields_trainings($result, $EM_Event) {
	 if (has_term( 'koulutukset', 'event-categories')) {
			add_post_meta($EM_Event->post_id, 'audience', 'Suunnattu kaikille', true);
			add_post_meta($EM_Event->post_id, 'prerequisites', 'Ei ennakkovaatimuksia', true);
			add_post_meta($EM_Event->post_id, 'test', 'Ei ole koe', true);
			add_post_meta($EM_Event->post_id, 'certification', 'Ei valmenna sertifikaattiin', true);
			add_post_meta($EM_Event->post_id, 'price', 'Ilmainen', true);
			add_post_meta($EM_Event->post_id, 'equipment', 'Ei laitevaatimuksia', true);
			add_post_meta($EM_Event->post_id, 'testprepare', 'Ei valmenna kokeeseen', true);
			add_post_meta($EM_Event->post_id, 'moreinfo', 'Ei lisätietoja', true);
			add_post_meta($EM_Event->post_id, 'language', 'Suomeksi', true);
			}
}
add_filter('em_event_save', 'tm_add_metafields_trainings',10,2);

/* Additional info for training posts */ 

function tm_trainings_post_author($content){
	if (has_term( 'koulutukset', 'event-categories')) {
	   	global $post;
		global $bp;
		global $EM_Event;
	   	$tm_group_id = get_post_meta($post->ID, '_group_id', true);
		$groupinfo = groups_get_group( array( 'group_id' => $tm_group_id ) );
		$content .= '<h2>Koulutuksien järjestäjät</h2><p><a href="' . get_site_url() . '/user-groups/' . $groupinfo->slug .  '">' . $groupinfo->name . '</a></p>';
	     	$tags = get_the_terms($EM_Event->post_id, EM_TAXONOMY_TAG);
		if( is_array($tags) && count($tags) > 0 ){
			$content .= '<h2>Koulutuksien tunnisteet</h2>';
			$tags_list = array();
			foreach($tags as $tag){
			$link = get_term_link($tag->slug, EM_TAXONOMY_TAG);
			if ( is_wp_error($link) ) $link = '';
			$tags_list[] = '<a href="'. $link .'">'. $tag->name .'</a>';
		}
		$content .= '<p>' . implode(', ', $tags_list) . '</p>';
		}
  		$content .= "<h2>Lisätietoa</h2>";
  	  	$content .= "<p>On koe: " . get_post_meta($post->ID, 'test', true);
  	  	$content .= "</p><p>Valmistaa sertifikaattiin: " . get_post_meta($post->ID, 'certification', true);
  	  	$content .= "</p><p>Hinta: " . get_post_meta($post->ID, 'price', true);
  	  	$content .= "</p><p>Varusteet: " . get_post_meta($post->ID, 'equipment', true);
  	  	$content .= "</p><p>Valmistaa kokeeseen: " . get_post_meta($post->ID, 'testprepare', true);
  	  	$content .= "</p><p>Lisätietoa: " . get_post_meta($post->ID, 'moreinfo', true);
  	  	$content .= "</p><p>Ennakkovaatimukset: " . get_post_meta($post->ID, 'prerequisites', true);
  	  	$content .= "</p><p>Koulutuksen kieli: " . get_post_meta($post->ID, 'language', true);
		$content .= "</p>";
		/* Add trainings list, remove events list */
		$content .= '<div class="row-fluid more-from-cat-list"><div class="span12"><div class="homepagebox"><h3><a href="/koulutukset/">Tulevat koulutukset</a></h3><a class="morelink" href="/koulutukset/">Lisää &rarr;</a>';
		$content .= '<style type="text/css"> row-fluid, .more-from-cat-list { display: none !important; } </style>';
	}
	return $content;
}
add_filter('em_event_output','tm_trainings_post_author');

/* Add shortcode for event tags */ 
function tm_trainings_tags( $atts ){
 	$tm_trainings_tags = get_terms('event-tags','hide-empty=0&orderby=id');
	$sep = '';
	echo '<h2>Koulutuksien tunnisteet</h2><p>';
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
	 <h2>Tietoja</h2><label for="companyurl">Verkkosivu</label>
	 <input id="companyurl" type="text" name="companyurl" value="<?php echo custom_field('companyurl'); ?>" />
	 <br>
	 <label for="companyphone">Puhelin</label>
	 <input id="companyphone" type="text" name="companyphone" value="<?php echo custom_field('companyphone'); ?>" /> 
	 <br>
	 <label for="companyemail">Sähköposti</label>
	 <input id="companyemail" type="text" name="companyemail" value="<?php echo custom_field('companyemail'); ?>" /> 
	 <br>
	 <label for="companyaddress">Osoite</label>
	 <input id="companyaddress" type="text" name="companyaddress" value="<?php echo custom_field('companyaddress'); ?>" /> 
	 <br>
	 <label for="companyservices">Tarjotut palvelut</label>
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
 
//Admin Files
if( is_admin() ){

    function aaa_em_submenu () {
	    $plugin_page = add_submenu_page('edit.php?post_type='.EM_POST_TYPE_EVENT, 'Tapahtumat', 'Vain tapahtumat', 'edit_events', 'edit.php?post_type='.EM_POST_TYPE_EVENT.'&event-categories=59');
    }
    add_action('admin_menu','aaa_em_submenu', -1);
    
    function aaa_em_submenu2 () {
	    $plugin_page = add_submenu_page('edit.php?post_type='.EM_POST_TYPE_EVENT, 'Koulutukset', 'Vain koulutukset', 'edit_events', 'edit.php?post_type='.EM_POST_TYPE_EVENT.'&event-categories=364');

    }
    add_action('admin_menu','aaa_em_submenu2', 0.001);
}

function my_em_text_rewrites($translation, $orig) {
	$translation = str_replace('Tapahtumat','Tapahtumat ja koulutukset', $translation);
	return $translation;
}
add_action ( 'gettext', 'my_em_text_rewrites', 1, 2 );


?>
