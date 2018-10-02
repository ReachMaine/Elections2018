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
// enqueue the style sheets
function eae_enqueue_styles()
{
      wp_enqueue_style( 'eae_styles', plugins_url( 'eai_results.css', __FILE__ ) );
}
add_action('wp_enqueue_scripts', 'eae_enqueue_styles' );
/* election results short codes */
/* shortcode [electionresultsrace ] */
require_once(EAE_PLUGIN_DIR.'/shortcodes/eai_election_result_racebars.php');
/* shortcode [electionresultspie] */
require_once(EAE_PLUGIN_DIR.'/shortcodes/eai_election_results_simplepie.php');
/* shortcode [electionresultsimple ] - best for uncontested races */
require_once(EAE_PLUGIN_DIR.'/shortcodes/eai_election_results_simple.php');
/* shortcode [electionresultstown ] */
require_once(EAE_PLUGIN_DIR.'/shortcodes/eai_election_results_town.php');

/**** functions *****/
function EAElections_enabled() {
  $eai_elections_enable_results = true;
  return $eai_elections_enable_results;
}
function EAElections_get_tablename() {
  return "votes2018";
}

function EAElections_get_checkback_img() {
  return EAE_PLUGIN_URL.'/images/election_announcement_bar_tuesday.jpg';
}
