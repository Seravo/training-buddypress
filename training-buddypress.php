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

/* Change admin bar for subscribers */

function tm_admin_bar_render() {
    global $wp_admin_bar;
    global $user_ID;
    global $current_user;
    $user_roles = $current_user->roles;
    $user_role = array_shift($user_roles);
    if ($user_role == "subscriber") {
    $wp_admin_bar->remove_menu('site-name');
    $wp_admin_bar->remove_menu('new-event-recurring', 'new-content');
    $wp_admin_bar->remove_menu('new-location', 'new-content');
    $wp_admin_bar->remove_menu('new-event', 'new-content');
    $wp_admin_bar->add_menu( array(
        'parent' => 'new-content',
        'id' => 'training',
        'title' => __('Koulutus'),
        'href' => admin_url( '/koulutus-uusi/')
    ) );
        $wp_admin_bar->add_menu( array(
        'parent' => 'new-content',
        'id' => 'event',
        'title' => __('Tapahtuma'),
        'href' => admin_url('/kalenteri/lisaa-tapahtuma/')
    ) );
    }
        if ($user_role == "administrator") {
    $wp_admin_bar->remove_menu('new-event', 'new-content');
        $wp_admin_bar->add_menu( array(
        'parent' => 'new-content',
        'id' => 'training',
        'title' => __('Koulutus'),
        'href' => admin_url( '/koulutus-uusi/')
    ) );
        $wp_admin_bar->add_menu( array(
        'parent' => 'new-content',
        'id' => 'event',
        'title' => __('Tapahtuma'),
        'href' => admin_url('/kalenteri/lisaa-tapahtuma/')
    ) );
    }
}
add_action( 'wp_before_admin_bar_render', 'tm_admin_bar_render' );

/* Make Subscriber users see only their own Events in Admin */ 

function tm_posts_for_trainings_author($query) {
global $user_ID;
global $current_user;
$user_roles = $current_user->roles;
$user_role = array_shift($user_roles);
if($query->is_admin) {
	if ($user_role == "subscriber") {
			global $user_ID;
			$query->set('author', $user_ID);
		echo '<style type="text/css">
		.subsubsub { display: none !important; }
		</style>';
}
}
	return $query;	
}
add_filter('pre_get_posts', 'tm_posts_for_trainings_author');

/* Training company widget */

class TmTrainingCompanyWidget extends WP_Widget {

	function TmTrainingCompanyWidget() {
		$this->_construct();
	}

	function __construct() {
		$widget_ops = array( 'description' => __( 'Lista koulutusyhtiöistä', 'buddypress' ) );
		parent::__construct( false, __( 'Koulutusyhtiöt', 'buddypress' ), $widget_ops );

		if ( is_active_widget( false, false, $this->id_base ) && !is_admin() && !is_network_admin() ) {
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				wp_enqueue_script( 'groups_widget_groups_list-js', BP_PLUGIN_URL . 'bp-groups/js/widget-groups.dev.js', array( 'jquery' ), bp_get_version() );
			} else {
				wp_enqueue_script( 'groups_widget_groups_list-js', BP_PLUGIN_URL . 'bp-groups/js/widget-groups.js', array( 'jquery' ),     bp_get_version() );
			}
		}
	}

	function widget( $args, $instance ) {
		$user_id = apply_filters( 'bp_group_widget_user_id', '0' );

		extract( $args );

		if ( empty( $instance['group_default'] ) )
			$instance['group_default'] = 'popular';

		if ( empty( $instance['title'] ) )
			$instance['title'] = __( 'Koulutusten tarjoajat', 'buddypress' );

		echo $before_widget;

		$title = $instance['link_title'] ? '<a href="' . trailingslashit( bp_get_root_domain() . '/' . bp_get_groups_root_slug() ) . '">' . $instance['title'] . '</a>' : $instance['title'];

		echo $before_title
		   . $title
		   . $after_title; ?>

		<?php if ( bp_has_groups( 'user_id=' . $user_id . '&type=' . $instance['group_default'] . '&max=' . $instance['max_groups'] ) ) : ?>
		   		<div class="item-options" id="groups-list-options">
				<a href="<?php echo site_url( bp_get_groups_root_slug() ); ?>" id="newest-groups"<?php if ( $instance['group_default'] == 'newest' ) : ?> class="selected"<?php endif; ?>><?php _e("Newest", 'buddypress') ?></a> |
				<a href="<?php echo site_url( bp_get_groups_root_slug() ); ?>" id="recently-active-groups"<?php if ( $instance['group_default'] == 'active' ) : ?> class="selected"<?php endif; ?>><?php _e("Active", 'buddypress') ?></a> |
				<a href="<?php echo site_url( bp_get_groups_root_slug() ); ?>" id="popular-groups" <?php if ( $instance['group_default'] == 'popular' ) : ?> class="selected"<?php endif; ?>><?php _e("Popular", 'buddypress') ?></a>
			</div>

			<ul id="groups-list" class="item-list" style="list-style-type: none;">
				<?php while ( bp_groups() ) : bp_the_group(); ?>
					<li>
						<div class="item-avatar">
							<a href="<?php bp_group_permalink() ?>" title="<?php bp_group_name() ?>"><?php bp_group_avatar_thumb() ?></a>
						</div>

						<div class="item">
							<div class="item-title"><a href="<?php bp_group_permalink() ?>" title="<?php bp_group_name() ?>"><?php bp_group_name() ?></a></div>
							<div class="item-meta"><span class="activity">Koulutusten järjestäjä</span>
								<!--<span class="activity">-->
								<?php /*
									if ( 'newest' == $instance['group_default'] )
										printf( __( 'created %s', 'buddypress' ), bp_get_group_date_created() );
									if ( 'active' == $instance['group_default'] )
										printf( __( 'active %s', 'buddypress' ), bp_get_group_last_active() );
									else if ( 'popular' == $instance['group_default'] )
										bp_group_member_count();
								*/
								?>
								<!--</span>-->
							</div>
						</div>
					</li>

				<?php endwhile; ?>
			</ul>
			<?php wp_nonce_field( 'groups_widget_groups_list', '_wpnonce-groups' ); ?>
			<input type="hidden" name="groups_widget_max" id="groups_widget_max" value="<?php echo esc_attr( $instance['max_groups'] ); ?>" />

		<?php else: ?>

			<div class="widget-error">
				<?php _e('There are no groups to display.', 'buddypress') ?>
			</div>

		<?php endif; ?>

		<?php echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']         = strip_tags( $new_instance['title'] );
		$instance['max_groups']    = strip_tags( $new_instance['max_groups'] );
		$instance['group_default'] = strip_tags( $new_instance['group_default'] );
		$instance['link_title']    = (bool)$new_instance['link_title'];

		return $instance;
	}

	function form( $instance ) {
		$defaults = array(
			'title'         => __( 'Groups', 'buddypress' ),
			'max_groups'    => 5,
			'group_default' => 'active',
			'link_title'    => false
		);
		$instance = wp_parse_args( (array) $instance, $defaults );

		$title 	       = strip_tags( $instance['title'] );
		$max_groups    = strip_tags( $instance['max_groups'] );
		$group_default = strip_tags( $instance['group_default'] );
		$link_title    = (bool)$instance['link_title'];
		?>

		<p><label for="bp-groups-widget-title"><?php _e('Title:', 'buddypress'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 100%" /></label></p>
		
		<p><label for="<?php echo $this->get_field_name('link_title') ?>"><input type="checkbox" name="<?php echo $this->get_field_name('link_title') ?>" value="1" <?php checked( $link_title ) ?> /> <?php _e( 'Link widget title to Groups directory', 'buddypress' ) ?></label></p>

		<p><label for="bp-groups-widget-groups-max"><?php _e('Max groups to show:', 'buddypress'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_groups' ); ?>" name="<?php echo $this->get_field_name( 'max_groups' ); ?>" type="text" value="<?php echo esc_attr( $max_groups ); ?>" style="width: 30%" /></label></p>

		<p>
			<label for="bp-groups-widget-groups-default"><?php _e('Default groups to show:', 'buddypress'); ?>
			<select name="<?php echo $this->get_field_name( 'group_default' ); ?>">
				<option value="newest" <?php if ( $group_default == 'newest' ) : ?>selected="selected"<?php endif; ?>><?php _e( 'Newest', 'buddypress' ) ?></option>
				<option value="active" <?php if ( $group_default == 'active' ) : ?>selected="selected"<?php endif; ?>><?php _e( 'Active', 'buddypress' ) ?></option>
				<option value="popular"  <?php if ( $group_default == 'popular' ) : ?>selected="selected"<?php endif; ?>><?php _e( 'Popular', 'buddypress' ) ?></option>
			</select>
			</label>
		</p>
	<?php
	}
}

function tm_groups_ajax_widget_groups_list() {

	check_ajax_referer('tm_groups_widget_groups_list');

	switch ( $_POST['filter'] ) {
		case 'newest-groups':
			$type = 'newest';
		break;
		case 'recently-active-groups':
			$type = 'active';
		break;
		case 'popular-groups':
			$type = 'popular';
		break;
	}

	if ( bp_has_groups( 'type=' . $type . '&per_page=' . $_POST['max_groups'] . '&max=' . $_POST['max_groups'] ) ) : ?>
		<?php echo "0[[SPLIT]]"; ?>

		<ul id="groups-list" class="item-list">
			<?php while ( bp_groups() ) : bp_the_group(); ?>
				<li>
					<div class="item-avatar">
						<a href="<?php bp_group_permalink() ?>"><?php bp_group_avatar_thumb() ?></a>
					</div>

					<div class="item">
						<div class="item-title"><a href="<?php bp_group_permalink() ?>" title="<?php bp_group_name() ?>"><?php bp_group_name() ?></a></div>
						<div class="item-meta">
							<span class="activity">
								<?php
								if ( 'newest-groups' == $_POST['filter'] ) {
									printf( __( 'created %s', 'buddypress' ), bp_get_group_date_created() );
								} else if ( 'recently-active-groups' == $_POST['filter'] ) {
									printf( __( 'active %s', 'buddypress' ), bp_get_group_last_active() );
								} else if ( 'popular-groups' == $_POST['filter'] ) {
									bp_group_member_count();
								}
								?>
							</span>
						</div>
					</div>
				</li>

			<?php endwhile; ?>
		</ul>
		<?php wp_nonce_field( 'tm_groups_widget_groups_list', '_wpnonce-groups' ); ?>
		<input type="hidden" name="tm_groups_widget_max" id="groups_widget_max" value="<?php echo esc_attr( $_POST['max_groups'] ); ?>" />

	<?php else: ?>

		<?php echo "-1[[SPLIT]]<li>" . __("No groups matched the current filter.", 'buddypress'); ?>

	<?php endif;

}
add_action( 'wp_ajax_widget_groups_list', 'tm_groups_ajax_widget_groups_list' );
add_action( 'wp_ajax_nopriv_widget_groups_list', 'tm_groups_ajax_widget_groups_list' );

	
/* Training event location widget */ 

class TmTrainingWidget extends WP_Widget {

	function TmTrainingWidget() {
		// Instantiate the parent object
		parent::__construct( false, 'Koulutusten sijainnit' );
	}

	function widget( $args, $instance ) {
		// Widget output
		echo '<div id="text-6" class="widget widget_text well nav nav-list"><h4 class="widgettitle nav-header">Koulutuksien sijainnit</h4><div class="textwidget">'; 
		echo EM_Locations::output(array("full" => 1, "long_events" => 1, "category" => 364)); 
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
	register_widget( 'TmTrainingTagWidget');
	register_widget( 'TmEventTagWidget');
	register_widget( 'TmTrainingCompanyWidget' );
}
add_action( 'widgets_init', 'tm_register_trainings_widgets' );

/* Hide attributes and tags from events */
/* Events and Trainings have separate tags, does this make sense any more? */
/*
function tm_hide_att_meta_boxes() {
	global $EM_Event;
	if (is_admin()) { 
	if(!has_term(364, 'event-categories', $EM_Event->post_id)) {
		add_action( 'add_meta_boxes', 'tm_trainings_add_hint_box' );
    		remove_meta_box('tagsdiv-event-tags', 'event', 'side'); 
		echo '<style type="text/css">
		#em-event-group { display: none !important; }
		</style>'; 
		echo '<style type="text/css">
                #em-event-attributes { display: none !important; }
                </style>';
				} 
}
add_action( 'admin_menu', 'tm_hide_att_meta_boxex' );
*/
function tm_trainings_add_hint_box (){
	add_meta_box( 
        'tm_trainings_hint_box',
        __( 'Koulutuksien lisäämisestä', 'tm_trainings' ),
        'tm_trainings_hint_box',
        'event',
        'side',
	'high'
    );
}

function tm_trainings_hint_box ($post) {
	echo '<p>HUOMAA: Laita koulutukset kategoriaan Koulutus ja tallenna. Tämän jälkeen voit liittää koulutusyhtiön ja kirjoittaa lisätietoja.</p>';
}
}
add_action( 'admin_menu', 'tm_trainings_add_hint_box' );


/* Additional info for training posts */ 

function tm_trainings_post_author($content){
	if (is_single() && has_term( 'koulutukset', 'event-categories')) {
	   	global $post;
		global $bp;
		global $EM_Event;
	   	$tm_group_id = get_post_meta($post->ID, '_group_id', true);
		$groupinfo = groups_get_group( array( 'group_id' => $tm_group_id ) );
		$content .= '<h2>Järjestäjä</h2><p><a href="' . get_site_url() . '/koulutusyhtiot/' . $groupinfo->slug .  '">' . $groupinfo->name . '</a></p>';
	     	$tags = get_the_terms($EM_Event->post_id, EM_TAXONOMY_TAG);
		if( is_array($tags) && count($tags) > 0 ){
			$content .= '<h2>Tunnisteet</h2>';
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

class TmTrainingTagWidget extends WP_Widget {

	function TmTrainingTagWidget() {
		// Instantiate the parent object
		parent::__construct( false, 'Koulutusten avainsanat' );
	}
		function widget( $args, $instance ) {
		// Widget output
		echo '<div id="text-6" class="widget widget_text well nav nav-list"><h4 class="widgettitle nav-header">Koulutuksien avainsanat</h4><div class="textwidget">'; 
  		$tm_trainings_tags = get_terms('event-tags',  array( 'hide_empty' => 1, 'orderby' => 'count' ));
		$sep = '';
		foreach ( $tm_trainings_tags as $tm_trainings_tags ) {
		$tagcount= EM_Events::get(array('scope'=>'future','category'=>364,'tag'=>$tm_trainings_tags->term_id));  
		if (count($tagcount) > 0) {
			echo $sep . '<a href="' . get_term_link($tm_trainings_tags) . '">' . $tm_trainings_tags->name . '</a>';
			$sep = ', '; 
			}

		}
	echo '</p>';		
	echo '</div></div>';
	}

	function update( $new_instance, $old_instance ) {
		// Save widget options
	}

	function form( $instance ) {
		// Output admin widget options form
	}
	
}

class TmEventTagWidget extends WP_Widget {

	function TmEventTagWidget() {
		// Instantiate the parent object
		parent::__construct( false, 'Tapahtumien avainsanat' );
		}
		function widget( $args, $instance ) {
		// Widget output
		echo '<div id="text-6" class="widget widget_text well nav nav-list"><h4 class="widgettitle nav-header">Tapahtumien avainsanat</h4><div class="textwidget">'; 
  		$tm_trainings_tags = get_terms('event-tags',  array( 'hide_empty' => 1, 'orderby' => 'count' ));
		$sep = '';
		foreach ( $tm_trainings_tags as $tm_trainings_tags ) {
		$tagcount= EM_Events::get(array('scope'=>'future','category'=>59,'tag'=>$tm_trainings_tags->term_id));  
		if (count($tagcount) > 0) {
			echo $sep . '<a href="' . get_term_link($tm_trainings_tags) . '">' . $tm_trainings_tags->name . '</a>';
			$sep = ', '; 
			}

		}
	echo '</p>';		
	echo '</div></div>';
	}

	function update( $new_instance, $old_instance ) {
		// Save widget options
	}

	function form( $instance ) {
		// Output admin widget options form
	}
}


/* Add shortcode for trainings tags */ 
function tm_trainings_tags( $atts ){  	
  	$tm_trainings_tags = get_terms('event-tags',  array( 'hide_empty' => 1, 'orderby' => 'count' ));
	$sep = '';
	foreach ( $tm_trainings_tags as $tm_trainings_tags ) {
		$tagcount= EM_Events::get(array('scope'=>'future','category'=>364,'tag'=>$tm_trainings_tags->term_id));  
		if (count($tagcount) > 0) {
			echo $sep . '<a href="' . get_term_link($tm_trainings_tags) . '">' . $tm_trainings_tags->name . '</a>';
			$sep = ', '; 
			}

		}
	echo '</p>';
}
add_shortcode( 'tm_trainings_tags', 'tm_trainings_tags' );

/* Add shortcode for event tags */ 
function tm_events_tags( $atts ){  	
  	$tm_trainings_tags = get_terms('event-tags',  array( 'hide_empty' => 1, 'orderby' => 'count' ));
	$sep = '';
	foreach ( $tm_trainings_tags as $tm_trainings_tags ) {
		$tagcount= EM_Events::get(array('scope'=>'future','category'=>59,'tag'=>$tm_trainings_tags->term_id));  
		if (count($tagcount) > 0) {
			echo $sep . '<a href="' . get_term_link($tm_trainings_tags) . '">' . $tm_trainings_tags->name . '</a>';
			$sep = ', '; 
			}

		}
	echo '</p>';
}
add_shortcode( 'tm_events_tags', 'tm_events_tags' );



/* Add stuff to Buddypress groups test */

function bp_group_meta_init() {
function custom_field($meta_key) {
	 return groups_get_groupmeta(bp_get_group_id(), $meta_key);
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
}
add_action( 'bp_include', 'bp_group_meta_init' );

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

    function aaa_em_submenu3 () {
            $plugin_page = add_submenu_page('edit.php?post_type='.EM_POST_TYPE_EVENT, 'Tapahtumat', 'Uusi tapahtuma', 'edit_events', '../kalenteri/lisaa-tapahtuma/');

    }
    add_action('admin_menu','aaa_em_submenu3', 0.002);

    function aaa_em_submenu4 () {
            $plugin_page = add_submenu_page('edit.php?post_type='.EM_POST_TYPE_EVENT, 'Koulutukset', 'Uusi koulutus', 'edit_events', '../koulutus-uusi/');

    }
    add_action('admin_menu','aaa_em_submenu4', 0.003);

}







function my_em_text_rewrites($translation, $orig) {
	$translation = str_replace('Tapahtumat','Tapahtumat ja koulutukset', $translation);
	$translation = str_replace('certification','Sertifikaatti', $translation);
	return $translation;
}
add_action ( 'gettext', 'my_em_text_rewrites', 1, 2 );

?>
