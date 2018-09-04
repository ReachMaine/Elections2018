<?php
/* election results short codes */
  $eai_elections_enable_results = true;
/* shortcode [electionresultsrace ] */
require_once(EAE_PLUGIN_DIR.'/shortcodes/eai_election_result_racebars.php');

/* shortcode [electionresultspie] */
require_once(EAE_PLUGIN_DIR.'/shortcodes/eai_election_results_simplepie.php');

/* shortcode [electionresultsimple ] - best for uncontested races */
require_once(EAE_PLUGIN_DIR.'/shortcodes/eai_election_results_simple.php');

/* shortcode [electionresultstown ] */
require_once(EAE_PLUGIN_DIR.'/shortcodes/eai_election_results_town.php');

?>
