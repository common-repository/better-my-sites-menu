<?php
/*
Plugin Name: Better My Sites Menu
Description: Basic adjustments to improve the My Sites menu for multiple-site admins.
Version: 1.0
Author: Kyle Maurer
Author URI: http://realbigmarketing.com/staff/kyle
*/

/*
Improve the My Sites drop down list.
Credit for the scroll script: http://wordpress.org/support/topic/adminbar-my-sites-dropdown-menu-needs-to-scroll?replies=4
More admin bar help here:
http://technerdia.com/1140_wordpress-admin-bar.html
*/
/*****Remove current My Sites menu******/
function ditch_current_my_sites() {
        global $wp_admin_bar;
	$wp_admin_bar->remove_menu('my-sites');
}

add_action('wp_before_admin_bar_render', 'ditch_current_my_sites');

/*****Make the My Sites list scroll if too many blogs****/
function custom_my_sites() {
$user_id = get_current_user_id();
$user_blogs = get_blogs_of_user( $user_id );
if (count($user_blogs) > 18) {
   echo '<style type="text/css">
           #wp-admin-bar-new-my-sites-list {
			max-height: 550px;
			overflow-x: visible;
			overflow-y: scroll;
		}
         </style>';
         }
}
add_action('admin_head', 'custom_my_sites');

/***Add new My Sites menu*****/
function new_admin_bar_my_sites_menu( $wp_admin_bar ) {
        global $wp_admin_bar;

	// Don't show for logged out users or single site mode.
	if ( ! is_user_logged_in() || ! is_multisite() )
		return;

	// Show only when the user has at least one site, or they're a super admin.
	if ( count( $wp_admin_bar->user->blogs ) < 1 && ! is_super_admin() )
		return;

	$wp_admin_bar->add_menu( array(
		'id'    => 'new-my-sites',
		'title' => __( 'My Sites' ),
		'href'  => admin_url( 'my-sites.php' ),
	) );

	if ( is_super_admin() ) {
		$wp_admin_bar->add_group( array(
			'parent' => 'new-my-sites',
			'id'     => 'new-my-sites-super-admin',
		) );

		$wp_admin_bar->add_menu( array(
			'parent' => 'new-my-sites-super-admin',
			'id'     => 'new-network-admin',
			'title'  => __('Network Admin'),
			'href'   => network_admin_url(),
		) );

		$wp_admin_bar->add_menu( array(
			'parent' => 'new-network-admin',
			'id'     => 'new-network-admin-d',
			'title'  => __( 'Dashboard' ),
			'href'   => network_admin_url(),
		) );
		$wp_admin_bar->add_menu( array(
			'parent' => 'new-network-admin',
			'id'     => 'new-network-admin-s',
			'title'  => __( 'Sites' ),
			'href'   => network_admin_url( 'sites.php' ),
		) );
		$wp_admin_bar->add_menu( array(
			'parent' => 'new-network-admin',
			'id'     => 'new-network-admin-u',
			'title'  => __( 'Users' ),
			'href'   => network_admin_url( 'users.php' ),
		) );
		$wp_admin_bar->add_menu( array(
			'parent' => 'new-network-admin',
			'id'     => 'new-network-admin-v',
			'title'  => __( 'Visit Network' ),
			'href'   => network_home_url(),
		) );
	}

	// Add site links
	$wp_admin_bar->add_group( array(
		'parent' => 'new-my-sites',
		'id'     => 'new-my-sites-list',
		'meta'   => array(
			'class' => is_super_admin() ? 'ab-sub-secondary' : '',
		),
	) );

	foreach ( (array) $wp_admin_bar->user->blogs as $blog ) {
		switch_to_blog( $blog->userblog_id );

		$blavatar = '<div class="blavatar"></div>';

		$blogname = empty( $blog->blogname ) ? $blog->domain : $blog->blogname;
		if (strlen($blogname) > 25) {
		$newblogname = substr($blogname,0,25) . "...";
		} else {
		$newblogname = $blogname;
		}
		$menu_id  = 'new-blog-' . $blog->userblog_id;

		$wp_admin_bar->add_menu( array(
			'parent'    => 'new-my-sites-list',
			'id'        => $menu_id,
			'title'     => $blavatar . $newblogname,
			'href'      => admin_url(),
		) );

		$wp_admin_bar->add_menu( array(
			'parent' => $menu_id,
			'id'     => $menu_id . '-d',
			'title'  => __( 'Dashboard' ),
			'href'   => admin_url(),
		) );

		if ( current_user_can( get_post_type_object( 'post' )->cap->create_posts ) ) {
			$wp_admin_bar->add_menu( array(
				'parent' => $menu_id,
				'id'     => $menu_id . '-n',
				'title'  => __( 'New Post' ),
				'href'   => admin_url( 'post-new.php' ),
			) );
		}

		if ( current_user_can( 'edit_posts' ) ) {
			$wp_admin_bar->add_menu( array(
				'parent' => $menu_id,
				'id'     => $menu_id . '-c',
				'title'  => __( 'Manage Comments' ),
				'href'   => admin_url( 'edit-comments.php' ),
			) );
		}

		$wp_admin_bar->add_menu( array(
			'parent' => $menu_id,
			'id'     => $menu_id . '-v',
			'title'  => __( 'Visit Site' ),
			'href'   => home_url( '/' ),
		) );

		restore_current_blog();
	}
	
}

add_action('admin_bar_menu', 'new_admin_bar_my_sites_menu',15);

?>