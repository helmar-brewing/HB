<?php
ob_start();

/* ROOT SETTINGS */ require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');

/* FORCE HTTPS FOR THIS PAGE */ if($use_https === TRUE){if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == ""){header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);exit;}}

/* WHICH DATABASES DO WE NEED */
	$db2use = array(
		'db_auth' 	=> FALSE,
		'db_main'	=> FALSE
	);
//

/* GET KEYS TO SITE */ require($path_to_keys);

ob_end_flush();

include 'layout/header.php';

?>

		
			<!-- MAIN -->
			<div id="main" class="cf">
				
				<!-- page-content -->
				<div class="page-content">
					
					<!-- entry-content -->	
		        	<div class="entry-content cf">
                    <p><font color="red"><u>Please note that the website is still under deveopment. Certain links/pages may not display properly. Thank you for this understanding. </u></font></p>
		        		
		        		<!-- Dropcaps -->
						<h2 class="heading">The Story Behind Helmar and Charles</h2>
						<p class="dropcap">Perhaps you've enjoyed some of my other projects including cult favorites Helmar Big League Brew (see the back of every card, including this current offering). By the way, this Helmar beer won a Gold Medal at the 2005 World Beer Festival and has been the subject of quite a few magazine articles. We've also made Potato Chips featuring sports cards and the ongoing series of Helmar Famous Athletes trading cards that one often finds on eBay offered by other sellers. Check out other eBay sellers of Helmar Brewing products. I've been fortunate enough to be at the forefront of some of the hobby's most interesting products and trends.</p>	
						<p class="dropcap">I believe that quality original art (coupled with a well-known brand) has great potential growth within our hobby. Collectors are increasing less interested in the mass produced items and more appreciative of the innovative art and products of those who are devoted to the game for its own sake. I join those who think that this type of handmade, quality item will be of supreme interest to future collectors.</p>
						<!-- ENDS Dropcaps -->
						
												
					</div>
					<!-- ENDS entry-content -->
	
				</div><!-- ENDS page-content -->				
				
			</div>
			<!-- ENDS MAIN -->
			
			
			
			
			
			<?php include 'layout/footer.php';?>
		
		</div>
		<!-- ENDS WRAPPER -->
		
		<!-- JS -->
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
		<script>window.jQuery || document.write('<script src="js/jquery.js"><\/script>')</script>
		<script src="js/custom.js"></script>
		
		<!-- superfish -->
		<script  src="js/superfish-1.4.8/js/hoverIntent.js"></script>
		<script  src="js/superfish-1.4.8/js/superfish.js"></script>
		<script  src="js/superfish-1.4.8/js/supersubs.js"></script>
		<!-- ENDS superfish -->
		
		<script src="js/css3-mediaqueries.js"></script>
		
		<script src="js/nivoslider.js"></script>
		<script src="js/tabs.js"></script>
		
	
		<!-- ENDS JS -->
	</body>
</html>
