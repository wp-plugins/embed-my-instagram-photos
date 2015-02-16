<?php
/*
Plugin Name: Embed My Instagram Photos
Plugin URI: http://embedmyphotos.com
Description: Super simple and easy to use! Embed your Instagram photo gallery directly into your Wordpress site. No complex registration or access tokens. Any Instagram user can display their photos as a slideshow or in a grid and configure sizes, borders and much more.
Version: 1.0
Author: EmbedMyPhotos
Author URI: http://embedmyphotos.com
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


// --- display iframe containing widget settings ---
function emb_settings_page()
{
   // --- Check that the user is allowed to update options ---
   if (!current_user_can('manage_options')) 
   {
      wp_die('You do not have sufficient permissions to access this page.');
   }

   // --- If id_widget not present, then add it ---
   if (!$id_widget = get_option('emp_id_widget'))
   {
      $l_url = "http://embedmyphotos.com/widget_get_new_id.php";
      
      $curl_handle=curl_init();
      curl_setopt($curl_handle,CURLOPT_URL,$l_url);
      curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
      curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
      $l_buffer = curl_exec($curl_handle);
      curl_close($curl_handle);
      $l_id_hash = preg_replace("/\r?\n/",'',$l_buffer);
      
      add_option( 'emp_id_widget', $l_id_hash, '', 'yes' );
   }   

   // --- get id_widget ---
   $id_widget = get_option('emp_id_widget');
   
   // --- display settings ---
   echo "<iframe
           id=\"emb_frame\"
           scrolling=\"no\"
           seamless=\"seamless\"
         style=\"width:95%;
                   height:1150px;
                   overflow:hidden\" 
             src=\"http://embedmyphotos.com/widget-maker-wp/$id_widget\">
        </iframe>";
}


// --- add menu to WP Admin panel ---
function emp_instagram_menu() 
{
   add_menu_page(
   'Settings',
   'Instagram Feed',
   'manage_options',
   'emb-instagram-photos',
   'emb_settings_page',
   'dashicons-camera'
   );
}


// --- add access to our js functions ---
function emp_admin_scripts()
{
   wp_enqueue_script('emp-admin-script',
                      plugins_url('js/admin.js', __FILE__));
}


// --- [embed-my-photos] ---
function emb_shortcode( $atts )
{
   $l_id = get_option('emp_id_widget',false);
   $l_url = "http://embedmyphotos.com/widget_get_frame.php?id_hash=$l_id";
   
   $curl_handle=curl_init();
   curl_setopt($curl_handle,CURLOPT_URL,$l_url);
   curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
   curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
   $l_buffer = curl_exec($curl_handle);
   curl_close($curl_handle);
   
   if (empty($l_buffer)){
      return "Instagram Widget Unavailable...<p>";
   }
   else{
      return $l_buffer;
   }
}


// --- Add Settings link to plugins page ---
function emb_plugin_action_links( $links ) 
{
   $link = '<a href="'. get_admin_url(null, 'admin.php?page=emb-instagram-photos') .'">Settings</a>';
   array_unshift($links,$link);
   return $links;
}


// --- Add callback functions ---
add_action('admin_menu', 'emp_instagram_menu');
add_action('admin_enqueue_scripts', 'emp_admin_scripts');
add_shortcode('EmbedMyPhotos', 'emb_shortcode' );
add_shortcode('embedmyphotos', 'emb_shortcode' );
add_shortcode('embed-my-photos', 'emb_shortcode' );
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 
            'emb_plugin_action_links');


?>


