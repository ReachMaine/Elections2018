<?php
add_shortcode('electionresultstown', 'electionResults_Town');

/********** RESULTS BY TOWN ************************/
function electionResults_Town ($atts) {
/* shortcode to return all the results for a particular town */
    global $wpdb;
    $table = "votes2016";
    global $eai_elections_enable_results;
    $votes_preview = false;
	  $a = shortcode_atts( array(
        'town' => 'something',
        'primary' => false,
    ), $atts );
    $town = $a['town'];
    $primary = $a['primary'];
    $towns_not_reported = []; // empty array
    $htmlreturn = "<!-- Town Results shortcode -->";
    $unofficial_text = 'Voting from Hancock County ONLY.';
    if ($eai_elections_enable_results) {
        /* initializations */
        $htmlreturn .= '<div class="eai-results-wrapper"><div class="eai-town"><h4>Elections results for '.$town.".</h4>";
        $htmlreturn .= '<h6 class="eai-results-unofficial">'.$unofficial_text.'</h6>';
        $found_votes = false;
        // GET THE RACES for the given town
        $racesquery = 'SELECT  distinct `race`, r_d, r_g, r_r, r_u  FROM `'.$table.'` WHERE town="'. $town.'" ORDER BY raceorder';
        //*echo '<p>RacesQuery: '.$racesquery.'</p>'; // testing
        $racesresults = $wpdb->get_results($racesquery);
        //echo "<pre>";     var_dump($racesresults);      echo "</pre>"; // testing
        if ($racesresults) {
            foreach ($racesresults as $race) {
                if ($primary) {

                    switch ($race->party) {
                        case "R":
                             $total_voters = $race->r_r;
                            break;
                        case "D":
                            $total_voters = $race->r_d;
                            break;

                        case "G":
                            $total_voters = $race->r_g;
                            break;
                        case "U":
                            $total_voters = $race->r_u;
                            break;
                        default:
                             $total_voters = 1; // prevent divid by zero
                    }
                    //echo "<p>Primary for _".$indrace->party."_</p>";
                } else {
                     $total_voters = $race->r_d + $race->r_g + $race->r_r + $race->r_u;
                    //echo "<p>Election</p>";
                }

                //echo '<p> Race:'.$race->race.' Reg. voters:'.$total_voters.'</p>'; // testing
                $indracequery = 'SELECT DISTINCT candidate, votes, town, party, reported FROM '.$table.' WHERE town = "'.$town.'" AND race="'.$race->race.'"';
                //echo '<p>'.$indracequery.'</p>'; // testing
                $indraceresults = $wpdb->get_results($indracequery);
                //echo "<pre>";     var_dump($indraceresults);      echo "</pre>";
                if ($indraceresults) {
                    //$htmlreturn .= '<h4>'.$race->race.'</h4>';
                    $htmlreturn .= '<table class="eai-results eai-results-town"><tr class="eai-results-headerrow"><th class="eai-results-header">'.$race->race.'</th><th class="eai-result-votes">Votes</th></tr>';
                    $count_voted = 0;
                    $num_candidates = 0;
                    foreach ($indraceresults as $indrace) {
                        if ($indrace->reported <> '0') {
                            $found_votes = true;
                            $count_voted += $indrace->votes;
                        }
                        $num_candidates++;
                        // count number of candidates;
                    }
                    //echo "<p>found votes is: ".$found_votes."</p>";
                    foreach ($indraceresults as $indrace) {
                        if ($indrace->party) {
                            $party_string = ' (<span class="party-'.$indrace->party.'">'.$indrace->party.'</span>) ';
                        } else {
                            $party_string = '';
                        }
                        if ($indrace->reported) {
                            //$found_votes = true;
                            if (($count_voted > 0) && ($num_candidates > 1)) {
                                $htmlreturn .= '<tr><td>'.$indrace->candidate.$party_string.'</td><td class="eai-result-votes">'.number_format_i18n($indrace->votes).' ( '.round(($indrace->votes/$count_voted)*100).'% )</td></tr>';
                            } else {
                                $htmlreturn .= '<tr><td>'.$indrace->candidate.$party_string.'</td><td class="eai-result-votes">'.number_format_i18n($indrace->votes).'</td></tr>';
                            }

                            //$count_voted += $indrace->votes;
                        } else {
                            //$htmlreturn .= "{reported = ".$indrace->reported."}";
                            $htmlreturn .= '<tr><td>'.$indrace->candidate.$party_string.'</td><td>not yet available</td></tr>';
                        }
                    }
                    $htmlreturn .= '</table>';
                    //$htmlreturn .= "totalvoters:  $total_voters";
                    if ( ($count_voted > 0) && ($total_voters>0)  && false) {

                        //$htmlreturn .= '<p> Voter participation: '.number_format_i18n($count_voted).' of '.number_format_i18n($total_voters);
                        $voter_participation = round(($count_voted/$total_voters)*100);
                        if ($voter_participation > 100) {
                          $voter_participation = 100;
                        }
                        if ($voter_participation > 0 ) {
                          //$htmlreturn .= ' : '.$voter_participation.'%';
                        }
                        //$htmlreturn .= '</p>';

                    }
                }
            }
        } else { //
            $htmlreturn .="<p>No results yet.</p>";
        }
        $htmlreturn .= '<h6 class="eai-results-unofficial">'.$unofficial_text.'</h6>';
        $htmlreturn .="</div></div>"; // end of shortcode
        if (!$found_votes) {
            $htmlreturn = '<img src="http://www.reachdowneast.com/elections2016/wp-content/themes/election2016/images/election_announcement_bar_tuesday.png">';
        } else {
           // $htmlreturn .= "<p>found votes..</p>";
        }
  } // not enabled.
    return $htmlreturn;
} /* end of electionresultstown */
/********** END OF RESULTS BY TOWN *****************/
