<?php
/**
Plugin Name: EAElections
Description: zig's election plugin.
Author: zig, aka Linda Speight
Version: 1.18
**/
if (!defined('EAE_PLUGIN_NAME')) {
  define( 'EAE_PLUGIN_NAME', trim(dirname(plugin_basename(__FILE__)), '/') );
}
if (!defined('EAE_PLUGIN_DIR')){
  define('EAE_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . EAE_PLUGIN_NAME);
}
if (!defined('EAE_PLUGIN_URL')) {
  define('EAE_PLUGIN_URL', WP_PLUGIN_URL . '/' . EAE_PLUGIN_NAME);
}

add_action( 'wp_head', 'eae_header_scripts' );
function eae_header_scripts(){
  ?>
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  <?php
}

include_once(EAE_PLUGIN_DIR.'/eai_election_results.php');

function EAElections_get_tablename() {
  return "votes2018";
}

function EAElections_get_checkback_img() {
  return EAE_PLUGIN_URL.'/images/election_announcement_bar_tuesday.jpg';
  //return "//www.downeastmaine.com/elections2018/wp-content/uploads/sites/25/2018/09/election_announcement_bar_tuesday_2018.jpg";
}
