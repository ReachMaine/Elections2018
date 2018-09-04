<?php
/**
Plugin Name: EAElections
*/
define( 'EAE_PLUGIN', __FILE__ );
define( 'EAE_PLUGIN_DIR', untrailingslashit( dirname( EAE_PLUGIN ) ) );

add_action( 'wp_head', 'eae_header_scripts' );
function eae_header_scripts(){
  ?>
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  <?php
}

	require_once(EAE_PLUGIN_DIR.'/eai_election_results.php');
