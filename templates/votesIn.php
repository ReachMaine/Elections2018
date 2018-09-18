<?php
/*
Template name: Voting2016 Input
*/
$have_town = false;
$updated = false;
global $wpdb;
$table = "votes2016";

if ( 'GET' == $_SERVER['REQUEST_METHOD'] && !empty( $_GET['action'] ) && $_GET['action'] == 'updatetown' ) {

    $have_town = true;
    $intown = $_GET['town_ddl'];
}
if ( 'GET' == $_SERVER['REQUEST_METHOD'] && !empty( $_GET['action'] ) && $_GET['action'] == 'updatevotes' ) {
    $have_town = false;
    $updated = $_GET;
    $toupdate_arr = $_GET;
    $ids_to_update = $_GET["id"];
    $reporteds_to_update = $_GET["reported"];
    $votes_to_update = $_GET["votes"];
    $updatequery = "";
    $i = 0;
    // build the query
 	foreach($ids_to_update as $toupid){
 		if ($votes_to_update[$i] != "" && $reporteds_to_update[$i] == 'true') {
 			$updatequery = 'UPDATE '.$table." SET `votes` = ".$votes_to_update[$i].", `reported` = 1 WHERE `electionrecordid` = ".$toupid.';';
			$updateresults = $wpdb->get_results($updatequery);
 		} else {
 		}
 		$i = $i + 1;
 	}

 	/*if ($updatequery) {
 		$updateresults = $wpdb->get_results($updatequery);
 	}*/

}

get_header(); ?>

<?php if( has_excerpt() ) { ?>
<div class="page-header">
	<?php the_excerpt(); ?>
</div>
<?php } ?>

<div  class="page-wrapper page-left-sidebar">
<div class="row">

<div id="content" class="large-9 right columns" role="main">
	<div class="page-inner">
		<?php while ( have_posts() ) : the_post(); ?>
					<?php get_template_part( 'content', 'page' ); ?>
		<?php endwhile; // end of the loop. ?>
			<?php //if ($have_town) { echo "<p>have town = ".$intown."</p>"; }
			      if ($updated) {echo "<p>updated.".$updated."</p>";
			       //echo"Updated:<pre>";var_dump($updated);echo"</pre>";
			       //echo"Reported:<pre>";var_dump($reporteds_to_update);echo"</pre>";
			      //echo"sql:<pre>";var_dump($updatequery);echo"</pre>";
			       //echo"results:<pre>";var_dump($updateresults);echo"</pre>";

			  	  } ?>
			<form method="get" id="town_form" action="<?php the_permalink(); ?>">
				<?php /*
					<select name="town_ddl" class="amw" >
					<option value="">Select Town</option>
					<option value="Amherst">Amherst</option>
					<option value="Aurora">Aurora</option>
					<option value="Bar Harbor">Bar Harbor</option>
					<option value="Blue Hill">Blue Hill</option>
					<option value="Brooklin">Brooklin</option>
					<option value="Bucksport">Bucksport</option>
					<option value="Castine">Castine</option>
					<option value="Cranberry Isles">Cranberry Islands</option>
					<option value="Dedham">Dedham</option>
					<option value="Deer Isle">Deer Isle</option>
					<option value="Eastbrook">Eastbrook</option>
					<option value="Ellsworth">Ellsworth</option>
					<option value="Franklin">Franklin</option>
					<option value="Frenchboro">Frenchboro</option>
					<option value="Gouldsboro">Gouldsboro</option>
					<option value="Great Pond">Great Pond</option>
					<option value="Hancock">Hancock</option>
					<option value="Lamoine">Lamoine</option>
					<option value="Mount Desert">Mount Desert</option>
					<option value="Orland">Orland</option>
					<option value="Osborn">Osborn</option>
					<option value="Otis">Otis</option>
					<option value="Penobscot">Penobscot</option>
					<option value="Sedgwick">Sedgwick</option>
					<option value="Sorrento">Sorrento</option>
					<option value="Southwest Harbor">Southwest Harbor</option>
					<option value="Stonington">Stonington</option>
					<option value="Sullivan">Sullivan</option>
					<option value="Surry">Surry</option>
					<option value="Swans Island">Swan’s Island</option>
					<option value="Tremont">Tremont</option>
					<option value="Trenton">Trenton</option>
					<option value="Verona Island">Verona Island</option>
					<option value="Waltham">Waltham</option>
					<option value="Winter Harbor" >Winter Harbor</option>
				</select> */ ?>
				<?php /*build the town select from DB. */
				$townquery = 'SELECT  distinct `town` FROM `'. $table.'` WHERE 1';
    			$townresult = $wpdb->get_results($townquery);
    			//echo "<pre>"; var_dump($townresult); echo "</pre>";
    			if ($townresult) {
    				$ddl_out = '<select name="town_ddl" class="amw" >';
    				$ddl_out .= '<option value="">Select Town</option>';
    				foreach ($townresult as $town) {
    					//echo "<pre>"; var_dump($town); echo "</pre>";
    					$thistown = $town->town;
    					$ddl_out .= '<option value="'.$thistown.'" ';
    					if ($intown == $thistown) {
    						$ddl_out .= "selected";
    					}
    					$ddl_out .=  ' >'.$thistown.'</option>';
    				}
    				echo $ddl_out;
    			} else {
    				echo "<p>no towns</p>";
    			}
    			?>


				<input name="updatetown" type="submit" id="updatetown" class="submit button" value="GO" />
			    <input name="action" type="hidden" id="action" value="updatetown" />
			</form>
			<form method="post" id="clearing_form" action="<?php the_permalink(); ?>" class="eai-election-inputs-clear">
				<input name="clearform" type="submit" id="clearform" class="submit button" value="Clear" />
				<input name="action" type="hidden" id="action" value="clearform" />
			</form>
			<form method="get" id="voting_form" action="<?php the_permalink(); ?>" class="eai-election-inputs-form">
				<?php /* */
					if ($intown) {
						echo "<h3>".$intown."</h3>";
						$racesquery = 'SELECT  distinct `race` FROM `'.$table.'` WHERE town="'. $intown.'" ORDER BY raceorder';
					    //*echo '<p>RacesQuery: '.$racesquery.'</p>'; // testing
					    $racesresults = $wpdb->get_results($racesquery);
					    //echo "<pre>"; var_dump($racesresults); echo "</pre>";// testing
					    $htmlout = "";
					    if ($racesresults) {
	        				foreach ($racesresults as $race) {
					            //echo '<p> Race:'.$race->race.' Reg. voters:'.$race->registeredvoters.'</p>'; // testing
					            $indracequery = 'SELECT DISTINCT electionrecordid, candidate, party, votes, town, reported FROM '.$table.' WHERE town = "'.$intown.'" AND race="'.$race->race.'"';
					            //echo '<p>'.$indracequery.'</p>'; // testing
					            $indraceresults = $wpdb->get_results($indracequery);
								//echo "<pre>";var_dump($indraceresults); echo "</pre>";// testing
					            if ($indraceresults) {

					            	//$htmlout .= '<p>'.$race->race.'</p>';
					            	$htmlout .= "<fieldset>";
					            	$htmlout .= '<legend>'.$race->race.'.</legend>';
					            	$htmlout .= '<dl class="eai-election-inputs-list" >';
					            	foreach ($indraceresults as $indrace) {
					            		$electionrecordid = $indrace->electionrecordid;
					            		//$htmlout .= '<span>'.$electionrecordid.'</span>';
					            		$htmlout .= '<input type="hidden" name="id[]"  value="'.$electionrecordid.'">';
					            		$htmlout .= '<dt class="eai-election-inputs-item-cand">'.$indrace->candidate;
                            //$htmlout .='-'.$indrace->reported;
                          if ($indrace->party) {
                            $htmlout .= ' ('.$indrace->party.')';
                          }
                          $htmlout .= '</dt>';
					            		if ($indrace->reported) {
					            			//$htmlout .= '<dd><input name="reported[]" type="checkbox" label="reported"></dd>';
					            			//$htmlout .= "reported ";
					            			$htmlout .= '<dd><input type="text" name="votes[]" label="'.$indrace->candidate.'" value="'.$indrace->votes.'"></dd>';
					            		} else {
					            			//$htmlout .= "nope ";
					            			//$htmlout .= '<dd><input name="reported[]" type="checkbox" label="reported" checked="checked"></dd>';
					            			$htmlout .= '<dd><input type="text" name="votes[]" label="'.$indrace->candidate.'" ></dd>';
					            		}
					            		$htmlout .= '<input type = "hidden" name="reported[]"  value="true" >';
					            		$htmlout .= "<br>";


					                   // $htmlout .= '</br>';
					                } /*end for individual race */
					                $htmlout .= "</dl>";
					                 $htmlout .= "</fieldset>";
					            }  else {
		        					echo "<p>no ind. results </p>";
		        					$htmlout = "<p>no ind. results </p> ";
		        				}
	        				} /* end for raceresults */
	        			} else { $htmlout =  "<p>No results</p>";}
	        		}
        			echo $htmlout;
				?>
				<input name="updatevotes" type="submit" id="updatevotes" class="submit button" value="Save" />
				<input name="action" type="hidden" id="action" value="updatevotes" />
			</form>




	</div><!-- .page-inner -->
</div><!-- end #content large-9 left -->

<div class="large-3 columns left">
<?php get_sidebar(); ?>
</div><!-- end sidebar -->

</div><!-- end row -->
</div><!-- end page-right-sidebar container -->


<?php get_footer(); ?>
