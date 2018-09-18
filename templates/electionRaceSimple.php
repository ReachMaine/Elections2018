<?php
/*
Template name: Election Test Race Simple
*/
$have_race = false;
$updated = false;
global $wpdb;
$table = "votes2016";

if ( 'GET' == $_SERVER['REQUEST_METHOD'] && !empty( $_GET['action'] ) && $_GET['action'] == 'updaterace' ) {

    $have_race = true;
    $inrace = $_GET['race_ddl'];
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

			<form method="get" id="race_form" action="<?php the_permalink(); ?>">
				<?php /*build the race select from DB. */
				$racequery = 'SELECT  distinct `race` FROM `'. $table.'` WHERE 1 ORDER BY raceorder';
    			$raceresult = $wpdb->get_results($racequery);
    			//echo "<pre>"; var_dump($raceresult); echo "</pre>";
    			if ($raceresult) {
    				$ddl_out = '<select name="race_ddl" class="amw" >';
    				$ddl_out .= '<option value="">Select Race</option>';
    				foreach ($raceresult as $race) {
    					//echo "<pre>"; var_dump($race); echo "</pre>";
    					$thisrace = $race->race;
    					$ddl_out .= '<option value="'.$thisrace.'" ';
    					if ($inrace == $thisrace) {
    						$ddl_out .= "selected";
    					}
    					$ddl_out .=  ' >'.$thisrace.'</option>';
    				}
    				echo $ddl_out;
    			} else {
    				echo "<p>no race</p>";
    			}
    			?>


				<input name="updaterace" type="submit" id="updaterace" class="submit button" value="GO" />
			    <input name="action" type="hidden" id="action" value="updaterace" />
			</form>
			<form method="post" id="clearing_form" action="<?php the_permalink(); ?>" class="eai-election-inputs-clear">
				<input name="clearform" type="submit" id="clearform" class="submit button" value="Clear" />
				<input name="action" type="hidden" id="action" value="clearform" />
			</form>
			<?php /* */
				if ($have_race) {
					$shortcode_out = '[electionresultsimple race = "'.$inrace.'"]';
					echo do_shortcode($shortcode_out);
				} else {
					echo "<p>No Race selected yet.</p>";
				}
			?>





	</div><!-- .page-inner -->
</div><!-- end #content large-9 left -->

<div class="large-3 columns left">
<?php get_sidebar(); ?>
</div><!-- end sidebar -->

</div><!-- end row -->
</div><!-- end page-right-sidebar container -->


<?php get_footer(); ?>
