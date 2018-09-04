<?php

add_shortcode('electionresultsimple', 'electionResults_RaceSimple');


function electionResults_RaceSimple ($atts) {
    /* short code function to display Summary of election results by Race.  Ex:  Governor's race */
    global $wpdb;
    $table = "votes2016";
    global $eai_elections_enable_results;
    $a = shortcode_atts( array(
        'race' => '',
        'link' => '',
        'unvoted' => false,  // by default, dont show the unvoted
        'primary' => false,
        'title' => "yes",
        'partial' => "no", // partial result of race (i.e. state races, dont have ALL results)
    ), $atts );

    $primary = $a['primary'];
    if ($a['title'] == "yes") {
       $show_title = true;
    } else {
        $show_title = false;
    }
    if ($a['unvoted'] ) {
        $show_unvoted = true;
    } else {
        $show_unvoted = false;
    }
    if ($a['partial'] != "no") {
      $show_partial_text = true;
    } else {
      $show_partial_text = false;
    }
    // initializations
    $unofficial_text = '<h6 class="eai-results-unofficial">';
    if ($show_partial_text) {
        $unofficial_text .= " Voting from Hancock County ONLY.";
    }
    $unofficial_text .= '</h6>';
    $race = $a['race'];
    $link = $a['link'];
    $count_precinct_reporting = 0;
    $count_precincts = 0;
    $count_voted = 0;
    $total_voters = 0;
    $found_votes = false;
    $count_unreported = 0;
    $count_unreported_d = 0;
    $count_unreported_r = 0;
    $count_unreported_g = 0;
    $count_unreported_u = 0;
    $pct_unreported_d = 0;
    $pct_unreported_r = 0;
    $pct_unreported_g = 0;
    $pct_unreported_u = 0;
    $jsreturn = "";
    $towns_reporting = []; // empty array.
    if ($eai_elections_enable_results) {
     /* get the candidates in the race */
      $candquery = 'SELECT  distinct `candidate`, party, raceorder FROM `'. $table.'` WHERE race="'. $race.'"';
      //echo "<p>Canidate Query: ".$candquery."</p>";
      $candresult = $wpdb->get_results($candquery);
      //echo "<pre>"; var_dump($candresult);echo "</pre>";

      if ($candresult) {

          $racequery = 'SELECT distinct base.precinct, reported, r_d, r_g, r_r, r_u ';
          $c=0;
          $sums = array();
          foreach ($candresult as $cand) {
              $raceorder = $cand->raceorder;
              $c++;
              $ctabname = (string)$c;
              $ctabname = 'c'.$ctabname;
              $candidate_name = $cand->candidate;
              $sums[$candidate_name] = 0;
              //echo "ctabname = ". $ctabname;
              //echo "<p>".$candidate_name."</p>";
              $racequery .= ', (select votes FROM `'.$table.'` '. $ctabname.' WHERE '.$ctabname.'.race="'.$race.'" AND '.$ctabname.'.candidate = "'.$candidate_name .'" and '.$ctabname.'.precinct = base.precinct) `'.$candidate_name.'` ';
          }
          $num_candidates = $c;
          $racequery .= ' FROM `'.$table.'` base ';
          $racequery .= ' WHERE base.race="'.$race.'"';
          $racequery .= ' ORDER BY reported DESC, base.precinct';

          //echo "<p>-- Race Query -- <br>"; echo $racequery;  echo "</br></hr></p>";;// for testing

          $raceresults = $wpdb->get_results($racequery);
          // echo "<pre>"; var_dump($raceresults); echo "</pre>";  // for testing

              /* loop thought calc the sums & totals */
          foreach ($raceresults as $raceresult) {
              //$htmlreturn .= "<tr>";
              //$htmlreturn .= "<td>".$raceresult->precinct."</td>";
              if ($raceresult->reported == 1) {
                  $found_votes = true;
                  $count_precinct_reporting++;
                  for ($i=0; $i< $num_candidates; $i++) {
                      $candidate_name = $candresult[$i]->candidate;
                      $race_amount = $raceresult->$candidate_name;
                      $sums[$candidate_name ] = $sums[$candidate_name] + $race_amount;
                      $count_voted += $race_amount;
                  }
              } else {
                  $all_towns_reported = false;
                  //$count_unreported += $raceresult->registeredvoters;
                  $count_unreported_d += $raceresult->r_d;
                  $count_unreported_r += $raceresult->r_r;
                  $count_unreported_g += $raceresult->r_g;
                  $count_unreported_u += $raceresult->r_u;
              }

              if ($primary) { // only count in party for primary
                   switch ($raceresult->party) {
                      case "R":
                           $registeredvoters = $raceresult->r_r;
                          break;
                      case "D":
                          $registeredvoters = $raceresult->r_d;
                          break;

                      case "G":
                          $registeredvoters = $raceresult->r_g;
                          break;
                      case "U":
                          $registeredvoters = $raceresult->r_u;
                          break;
                      default:
                          $registeredvoters = 0;
                  }

              } else {
                   $registeredvoters = $raceresult->r_d + $raceresult->r_r + $raceresult->r_g + $raceresult->r_u;
              }
              $total_voters += $registeredvoters;
              $count_precincts++;
          } // end for each result
          // more calcs once all counted.
          if ($found_votes) {
              if ($count_unreported > 0) {
                  $pct_unreported_r = round(($count_unreported_r / $count_unreported)*100,1);
                  $pct_unreported_d = round(($count_unreported_d / $count_unreported)*100, 1);
                  $pct_unreported_g = round(($count_unreported_g / $count_unreported)*100, 1);
                  $pct_unreported_u = round(($count_unreported_u / $count_unreported)*100, 1);
              }
              // build the data for the pie chart.
              $str_piedata = "[['Candidate', 'Votes']";
              $str_colors = "";
              for ($i=0; $i< $num_candidates; $i++) {
                 // if ($i > 0 ) { $str_piedata .= ","; }
                  $candidate_name = $candresult[$i]->candidate;

                  $str_piedata .= ",['". $candresult[$i]->candidate."', ".$sums[$candidate_name]."]";
                  switch ($candidate_name) {
                   case 'Yes':
                          $str_colors .= ",'#4B874F'"; // a nice green
                          break;
                      case 'No':
                          $str_colors .= ",'grey'";
                          break;
                  }
                  switch ($candresult[$i]->party) {
                      case 'R':
                          $str_colors .= ",'#D33'";
                          break;
                      case 'D':
                          $str_colors .= ",'#1E73BE'"; // a nice blue
                          break;
                      case 'G':
                          $str_colors .= ",'green'";
                          break;
                      case 'U':
                          $str_colors .= ",'purple'";
                          break;
                      case 'I':
                          $str_colors .= ",'grey'";
                          break;

                  }

              }
              if ($str_colors <> "") {
                  $str_colors = ',colors :['.substr($str_colors,1).']';
              }
              $str_piedata .= "]";
          }

          /* ********** start building html ************ */
          $htmlreturn = "<!-- simple shortcode -->";
          $htmlreturn .= '<div class="eai-resultsimple-wrapper">';


          $htmlreturn .= '<div class="eai-racesimple"><h4>';
          $title = $race;
          if ($link) {
              $htmlreturn .= '<a href="'.$link.'">'.$title.'</a>';
          } else {
                $htmlreturn .= $title;
          }
          $htmlreturn .= '</h4>';

          /* display the results */
          if ($raceresults) {
              // first some of the totals & counts
              $htmlreturn .= '<ul class="eai-results-sum">';
              for ($i=0; $i< $num_candidates; $i++) {
                  $candidate_name = $candresult[$i]->candidate;
                  if ($candresult[$i]->party) {
                          $party_string = ' (<span class="party-'.$candresult[$i]->party.'">'.$candresult[$i]->party.'</span>) ';
                      } else {
                          $party_string = '';
                  }
                  $htmlreturn .= "<li>". $candidate_name.$party_string;
                  if ($found_votes) {
                      $htmlreturn .= ': '.number_format_i18n($sums[$candidate_name]);
                  }
                  if (($count_voted > 0) && ($all_towns_reported)) {
                      $htmlreturn .= ' - '.round(($sums[$candidate_name]/$count_voted)*100).'%';
                  }
                  $htmlreturn .='</li>';
              }
              $htmlreturn .= '</ul>';
              if ($found_votes) {
                  if (!$all_towns_reported && ($count_precincts > 1)) {
                      $htmlreturn .= "<p>".$count_precinct_reporting.' of '.$count_precincts.' Towns reported.</p>';
                  }
                  //$htmlreturn .= "<p>".$count_precinct_reporting.' of '.$count_precincts.' Precincts reporting.'; // : '.round(($count_precinct_reporting/$count_precincts)*100).'%</p>';
                  //$htmlreturn .= '<p>'.number_format_i18n($count_voted).' of '.number_format_i18n($total_voters).' voters. Participation: '.round(($count_voted/$total_voters)*100).'%</p>';

                   /* $htmlreturn .= '<script>var piedata'.$raceorder.' = google.visualization.arrayToDataTable('.$str_piedata.');</script>';
                  $htmlreturn .= '<p>Total unreported:'.number_format_i18n($count_unreported);
                  $htmlreturn .= ' d:'.number_format_i18n($count_unreported_d).'('.$pct_unreported_d.'%), ';
                  $htmlreturn .= ' r:'.number_format_i18n($count_unreported_r).'('.$pct_unreported_r.'%), ';
                  $htmlreturn .= ' u:'.number_format_i18n($count_unreported_u).'('.$pct_unreported_u.'%), ';
                  $htmlreturn .= ' g:'.number_format_i18n($count_unreported_g).'('.$pct_unreported_g.'%) ';
                  $htmlreturn .= '</p>';  */
                   if ($link) {
                      $htmlreturn .= '<span> <a href="'.$link.'"> More details >>> </a></span>';
                  }
                  $htmlreturn .= $unofficial_text;


              } else {
                   $htmlreturn .= '<p class="eai-checkback">Polls close at 8 p.m. Check back then for results as they come in.</p>';
              }

          } else {
              $htmlreturn .= "<p>No results</p>";
          }

      } else {
          $htmlreturn .= "<p>No Candidates for ".$race.".</p>";
      }
      $htmlreturn .="</div></div>"; // end of wrapper & identifying div.
    } // end of $eai_elections_enable_results
    return $htmlreturn;
}
