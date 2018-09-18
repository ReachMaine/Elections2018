<?php
/* election results short codes */
add_shortcode('electionresultsrace', 'electionResults_Race2');
add_shortcode('electionresultsrace2', 'electionResults_Race2');
/********** RESULTS BY RACE ************************/
function electionResults_Race2($atts) {
    /* short code function to display election results by Race.  Ex:  Governor's race */
  global $wpdb;
  $table = EAElections_get_tablename();

   $a = shortcode_atts( array(
      'race' => '',
      'unvoted' => false,  // by default, dont show the unvoted
      'primary' => false,
      'title' => "yes",
      'charttype' => "pie",
      'partial' => "no",
  ), $atts );

  $votes_preview = false;
  $htmlreturn = "<!-- Race Shortcode -->";
  $jsreturn = "";
  if (EAElections_enabled()) {
    // initializations
    $primary = $a['primary'];
    $race = $a['race'];
    if ($a['title'] == "yes") {
       $show_title = true;
    } else {
        $show_title = false;
    }
    $charttype = $a["charttype"];
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

    if ($show_partial_text) {
      $unofficial_text = '<h6 class="eai-results-unofficial">';
      $unofficial_text .= " Votes cast in Hancock County ONLY";
      $unofficial_text .= '.</h6>';
    }

    $count_precinct_reporting = 0;
    $count_precincts = 0;
    $count_voted = 0;
    $total_voters = 0;
    $found_votes = false;
    $all_reported = false;
    $all_towns_reported = true;
    $towns_not_reported = array();
    $jsreturn = "";
    $count_unreported = 0;
    $count_unreported_d = 0;
    $count_unreported_r = 0;
    $count_unreported_g = 0;
    $count_unreported_u = 0;
    $arry_names = array();
    $arry_votes = array();
    $arry_gdata = array();
    $show_pie2 = true;
    /* get the candidates in the race */
    $candquery = 'SELECT  distinct `candidate`,party, raceorder FROM `'. $table.'` WHERE race="'. $race.'"';
    $candresult = $wpdb->get_results($candquery);
    //echo "<pre>"; var_dump($candresult);echo "</pre>";

    if ($candresult) {

        $racequery = 'SELECT distinct base.precinct, reported, r_d, r_g, r_r, r_u '; // cant have party in here (unless primary)
        $c=0;
        $sums = array();
        $str_piedata2 = "";
        $str_piedata2 .= "[['Town' "; // start of data for pie2
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
            $str_piedata2 .= ", '".$candidate_name."'";
        }
        $str_piedata2 .= "]";
        $num_candidates = $c;
        $racequery .= ' FROM `'.$table.'` base ';
        $racequery .= ' WHERE base.race="'.$race.'"';
        $racequery .= ' ORDER BY reported DESC, base.precinct';

        //echo "-- Race Query -- <br>"; echo $racequery;  echo "</br></hr>";;// for testing

        $raceresults = $wpdb->get_results($racequery);
        //echo "<pre>"; var_dump($raceresults); echo "</pre>";  // for testing

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
        }
        // more calcs once all counted.
    //$found_votes = true;
        if ($found_votes) {
            if ($count_unreported > 0) {
                $pct_unreported_r = round(($count_unreported_r / $count_unreported)*100,1);
                $pct_unreported_d = round(($count_unreported_d / $count_unreported)*100, 1);
                $pct_unreported_g = round(($count_unreported_g / $count_unreported)*100, 1);
                $pct_unreported_u = round(($count_unreported_u / $count_unreported)*100, 1);
            } else {
                $all_reported = true;
            }

            // build the data for the chart(s)
            $str_voterdata = "[['Unreported', 'Voters']";
            $str_voterdata .= ",['Republican',".$count_unreported_r."]";
            $str_voterdata .= ",['Democrat',".$count_unreported_d."]";
            $str_voterdata .= ",['Green',".$count_unreported_g."]";
            $str_voterdata .= ",['Independent',".$count_unreported_u."]";
            $str_voterdata .= "]";
            $str_votercolors = "colors:['#D33', '#1E73BE', '#4B874F', 'grey']";
            $str_piedata = "[['Candidate', 'Votes'";
            switch($charttype) {
              case "bar":
                $str_piedata .= ",{role: 'style'}";
              break;
            }
            $str_piedata .= "]"; // end of column headings.
            $str_colors = "";
            for ($i=0; $i< $num_candidates; $i++) {
               // if ($i > 0 ) { $str_piedata .= ","; }
                $candidate_name = $candresult[$i]->candidate;
                $candidtate_name_title = $candresult[$i]->candidate;
                if ($candresult[$i]->party) {
                  $candidtate_name_title .= "(".$candresult[$i]->party.")";
                }
                $str_piedata .= ",['".$candidtate_name_title."', ".$sums[$candidate_name];

                switch ($candidate_name) {
                 case 'Yes':
                        $str_colors .= ",'#4B874F'"; // a nice green
                        $cand_color = '#4B874F';
                        break;
                    case 'No':
                        $str_colors .= ",'grey'";
                        $cand_color = 'grey';
                        break;
                }
                switch ($candresult[$i]->party) {
                    case 'R':
                        $str_colors .= ",'#D33'";
                        $cand_color = "#D33";
                        break;
                    case 'D':
                        $str_colors .= ",'#1E73BE'"; // a nice blue
                        $cand_color = '#1E73BE';
                        break;
                    case 'G':
                        $str_colors .= ",'green'";
                        $cand_color = 'green';
                        break;
                    case 'U':
                        $str_colors .= ",'purple'";
                        $cand_color = 'purple';
                        break;
                    case 'Y':
                         $str_colors .= ",'#4B874F'"; // a nice green
                         $cand_color .= "#4B874F";
                         break;
                    case 'N':
                         $str_colors .= ",'grey'";
                         $cand_color = 'grey';
                         break;
                    case 'I':
                    case 'L':
                        $str_colors .= ",'grey'";
                        $cand_color = 'grey';
                        break;
                }
                switch ($charttype) {
                  case "bar":
                    $str_piedata .= ",'color: ".$cand_color.";'";
                }
                $str_piedata .= "]"; // end of candidate row
            } // end for
            if ($str_colors <> "") {
                $str_colors = ',colors :['.substr($str_colors,1).']';
            }
            $str_piedata .= "]"; // end of piedata
        } // found votes
//$htmlreturn .= "IN SHORTCODE with type: ".$charttype;

        /* ********** build the display **************/

        $htmlreturn .= '<div class="eai-resultsrace-wrapper">';

        //$htmlreturn .= '<h4 class="eia-race-title" >'.$race.'</h4>';
        $htmlreturn .= "<!--open wrapper-->";
//$found_votes = false;
        if ($raceresults) {
            $htmlreturn .= '<div class="eai-racesum">';
            if ($show_title) {
                $htmlreturn .= '<h2 style="text-align: center;">'.$race."</h2>";
            }
            // in racesum: 1st the piechart
            if ($found_votes) {

                $htmlreturn .= '<div class="eai-race-vote-pie">';
                if ($num_candidates > 1) {
                  $htmlreturn.= '<h5>Hancock County Vote Summary</h5>';
                  $htmlreturn .= '<div id="racedisplay'.$raceorder.'" class ="eai-race-grx"></div>';
                }
                $htmlreturn .= '</div>';
            }

            // in racesum: 2nd  - display precincts reporting
            if ($found_votes ) {

                $htmlreturn .= '<div class="eai-precincts-reporting" >';
                if ($count_precinct_reporting < $count_precincts ) {
                  $htmlreturn .= '<h3 class="eai-precincts-precent">'.round(($count_precinct_reporting/$count_precincts)*100).'%</h3><h3 class="eai-precincts-title">Towns</br>reporting</h3>';
                  $htmlreturn .= '<p class="eai-precincts-subtitle">'.$count_precinct_reporting.' of '.$count_precincts.'</p>';
                } else {
                  // all precints have reported.
                  //$htmlreturn .= '<p class="eai-precincts-subtitle">All towns reported</p>';
                }

                //$htmlreturn .= '<p>'.number_format_i18n($count_voted).' of '.number_format_i18n($total_voters).' voters. Participation: '.round(($count_voted/$total_voters)*100).'%</p>';
                $htmlreturn .="</div>";
            }
             // in racesum: 3nd  - display voter participation
            if ($found_votes & $all_towns_reported ) {
                $voter_participation = round(($count_voted/$total_voters)*100);
                if ($voter_participation = 0) {
                  //$htmlreturn .= '<div class="eai-voter-partcip" >';
                  //$htmlreturn .= '<h3 class="eai-voter-precent">'.$htmlreturn.'%</h3><h3 class="eai-voter-title">Voter</br>Participation</h3>';
                  //$htmlreturn .= "<p>".$count_precinct_reporting.' of '.$count_precincts.' Precincts reporting:</p>';
                  //$htmlreturn .= '<p>'.number_format_i18n($count_voted).' of '.number_format_i18n($total_voters).' voters. Participation: '.round(($count_voted/$total_voters)*100).'%</p>';
                  /* $count_voted = 0;
                  $total_voters = 0; */
                  //$htmlreturn .= "(counted = ".$count_voted." / total_voters = ".$total_voters.")";
                  //$htmlreturn .="</div>";
                }

            }


            // in racesum: 4th - piechart of remaining voters affiliates
            if ($found_votes && $show_unvoted) {
                $htmlreturn .= '<div class="eai-unvoted"><h5>Profile of unreported precincts</h3>';
                $htmlreturn .= '<div id="eai-unvoted-affl" class="eai-voter-grx"></div>';
                $htmlreturn .= '</div>';
            }
            $htmlreturn .= '</div>'; // end of race-ssum
            $htmlreturn .=" <!-- end  of race sum -->";
            if ($found_votes)    {
                $htmlreturn .= $unofficial_text;
                // spot for chart2
                //$htmlreturn .= "<p>count of precincts = ".$count_precincts."</p>";
                if (($num_candidates > 1) && ($count_precincts > 1)) {

                  $htmlreturn .= '<div class="eai-race-vote-pie2" style="margin-bottom: 20px;">';
                  $htmlreturn.= '<h5>Results by Town</h5>';
                  $htmlreturn .= '<div id="racedisplay2-'.$raceorder.'" class ="eai-race-grx2"></div>';
                  $htmlreturn .= '</div>';

                } else {
                    $show_pie2 = false;
                }



                // now the table of all the results
                $htmlreturn .= '<table class="eai-results-race-details">';
                // put totals at top of table as well as bottom
                $htmlreturn .= '<tr class="eai-results-totalrow"><td>Totals</td>';
                for ($i=0; $i< $num_candidates; $i++) {
                    $candidate_name = $candresult[$i]->candidate;
                    $htmlreturn .= '<td class="eia-result-totals">'.number_format_i18n($sums[$candidate_name])."</td>"; // $sumresult->
                }
                $htmlreturn .= "</tr>";

                $htmlreturn .= '<tr class="eai-results-headerrow"><th>Town</th>';
                foreach ($candresult as $cand) {
                   if ($cand->party) {
                            //$party_string = ' ('.$cand->party.') ';
                            $party_string = ' (<span class="party-'.$cand->party.'">'.$cand->party.'</span>) ';
                        } else {
                            $party_string = '';
                    }
                   $htmlreturn .= '<th class="eai-result-votes">'.$cand->candidate.$party_string.'</th>';
                }
                $htmlreturn .= "</tr>";

                //

                foreach ($raceresults as $raceresult) {
                    if ($raceresult->reported) {
                      $htmlreturn .= "<tr>";
                      $htmlreturn .= "<td>".$raceresult->precinct.'</td>';
                      $str_piepiece  = ",['".$raceresult->precinct."'";
                      $town_reported_here = true;
                      for ($i=0; $i< $num_candidates; $i++) {
                          $candidate_name = $candresult[$i]->candidate;
                          if ($raceresult->reported) {
                              $race_amount = $raceresult->$candidate_name; // name of column is candidates name.
                              $race_amount_str = number_format_i18n($race_amount);
                              $str_piepiece .= ",".$race_amount;
                          } else {
                              $race_amount_str = 'Not yet reported.';
                              $town_reported_here = false;
                          }

                          //$sums[$candidate_name ] = $sums[$candidate_name] + $race_amount;
                          $htmlreturn .= '<td class="eai-result-votes">'.$race_amount_str."</td>";
                      }
                      if ($town_reported_here) {
                        $str_piedata2 .= $str_piepiece."]";
                      }
                      $htmlreturn .= "</tr>";
                    }  else { // not reported.
                      $towns_not_reported[] = $raceresult->precinct;
                    }
                } // end for
                  $str_piedata2 .= "]"; // end of pie2 data chart.

                // put the sums at the bottom of the table

                $htmlreturn .= '<tr class="eai-results-totalrow"><td>Totals</td>';
                for ($i=0; $i< $num_candidates; $i++) {
                    $candidate_name = $candresult[$i]->candidate;
                    $htmlreturn .= '<td class="eia-result-totals">'.number_format_i18n($sums[$candidate_name])."</td>"; // $sumresult->
                }
                $htmlreturn .= "</tr>";
                $htmlreturn .= "<tr><th>Town</th>";
                foreach ($candresult as $cand) {
                    if ($cand->party) {
                            $party_string = ' (<span class="party-'.$cand->party.'">'.$cand->party.'</span>) ';
                        } else {
                            $party_string = '';
                    }
                   $htmlreturn .= '<th class="eai-result-votes">'.htmlspecialchars_decode($cand->candidate).$party_string.'</th>';
                }
                $htmlreturn .= "</tr>";
                $htmlreturn .="</table>";
                if (!empty($towns_not_reported)) {
                  $htmlreturn .= "<p>Not yet reported:<br> ". implode(", ",$towns_not_reported).".</p>";
                }
                /* $htmlreturn .= '<p>Total unreported:'.number_format_i18n($count_unreported);
                $htmlreturn .= ' d:'.number_format_i18n($count_unreported_d).', ';
                $htmlreturn .= ' r:'.number_format_i18n($count_unreported_r).', ';
                $htmlreturn .= ' u:'.number_format_i18n($count_unreported_u).', ';
                $htmlreturn .= ' g:'.number_format_i18n($count_unreported_g);
                $htmlreturn .= '</p>'; */
                $htmlreturn .= $unofficial_text;

                /* now for the javascript to build the graphics */
                //$raceorder = "";

                if ($charttype && ($charttype != "none"))  {
                  if (  $num_candidates > 1) {
                    switch ($charttype) {
                      case 'bar':
                          $chart_areaoption =  ",chartArea:{'width': '50%','height': '90%'}";
                          break;
                      case 'pie':
                          $chart_areaoption =  ",chartArea:{'width': '90%','height': '90%'}";
                          break;
                    }

                    //$chart_options = "{title:'".$race."'".$str_colors.$chart_areaoption."}"; // ,chartArea:{'width':'50%', height:'50%'}
                    $voter_options = "{title:'Profile of unreturned precincts',".$str_votercolors.$chart_areaoption."}";
                    $jsreturn = "<script>";
                    $jsreturn .= "google.charts.load('current', {'packages':['corechart','bar']});";
                    $jsreturn .= "google.charts.setOnLoadCallback(drawChart);";
                    $jsreturn .= "function drawChart(){";
                    $jsreturn .= 'var data = google.visualization.arrayToDataTable('.$str_piedata.');';
                    switch ($charttype) {
                        case "pie" :
                          $chart_options = "{title:'".$race."'".$str_colors.$chart_areaoption."}"; // ,chartArea:{'width':'50%', height:'50%'}
                          $jsreturn .= "var chart = new google.visualization.PieChart(document.getElementById('racedisplay".$raceorder."'));";
                          break;
                        case "bar":
                          $jsreturn .= "data.sort([{column: 1, desc: true  }]);";
                          $chart_options = "{title:'".$race."'";
                        //  $chart_options .= $str_colors.$chart_areaoption;
                          $chart_options .= $chart_areaoption;
                          //$chart_options .= ", vAxis:{ title:'Candidate' }";
                          $chart_options .= ", legend: 'none'";
                          $chart_options .= "}";

                          $jsreturn .= "var chart = new google.visualization.BarChart(document.getElementById('racedisplay".$raceorder."'));";
                          break;
                    }
                    $chart2_options =  "{title:'".$race."'";
                    $chart2_options .= $str_colors;
                    $chart2_options .= ",chartArea:{'width': '60%','height': '90%'}";
                    $chart2_options .= ',height: '.$count_precincts*$num_candidates*12;
                    $chart2_options .= "}";

                          //$htmlreturn .= "<p>PieData</p><pre>".$str_piedata."</pre>";
                          //$htmlreturn .= "<pre>Chart options:".$chart_options."</pre>";
                    $jsreturn .= "var options = ".$chart_options.";";
                    $jsreturn .= "chart.draw(data,options);";
                    if ($show_pie2) {
                        $jsreturn .= 'var data2 = google.visualization.arrayToDataTable('.$str_piedata2.');';
                        $jsreturn .= "var options2 = ".$chart2_options.";";
                        $jsreturn .= "var chart2 = new google.visualization.BarChart(document.getElementById('racedisplay2-".$raceorder."'));";
                        $jsreturn .= "chart2.draw(data2,options2);";
                            //$htmlreturn .= "<p>Piedata2</p><pre>".$str_piedata2."</pre>";
                            //$htmlreturn .= "<pre>Chart options:".$chart2_options."</pre>";
                    }
                    $jsreturn .="} </script>";
                  } // more than one Candidate
                } // end chartype
            } else {
                // no votes yet.
                // $htmlreturn .= '<p class="eai-checkback">Polls close at 8 p.m. Check back then for results as they come in.</p>';
                $htmlreturn .= '<img src="'.EAElections_get_checkback_img().'">';
            }
        } else {
            $htmlreturn .= "<p>No results.</p>";
            //var_dump($raceresults);
            //echo $racequery;
        }

    } else {
        $htmlreturn .= "<p>No Candidates for ".$race."</p>";
    }
    $htmlreturn .="</div>"; // end of wrapper & ident div
    $htmlreturn .= "<!-- end of wrapper race -->";
  } // end enabled.
  else {$htmlreturn = "<!-- nada -->";$jsreturn = ""; }
    return $htmlreturn.$jsreturn;
}
/********** END OF RESULTS BY RACE2 *****************/

?>
