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
		        		
						<!--<h2  class="heading">Stay in touch using this form!</h2>-->
						<h2  class="heading">Coming soon!</h2>
                        <p><font color="red"><u>Please note that the website is still under deveopment. Certain links/pages may not display properly. Thank you for this understanding. </u></font></p>
						
						<!-- form 
						<form id="contactForm" action="#" method="post">
							<fieldset>
																
								<p>
									<label for="name" >Name</label>
									<input name="name"  id="name" type="text" class="form-poshytip" title="Enter your full name" />
								</p>
								
								<p>
									<label for="email" >Email</label>
									<input name="email"  id="email" type="text" class="form-poshytip" title="Enter your email address" />
								</p>
								
								<p>
									<label for="web">Website</label>
									<input name="web"  id="web" type="text" class="form-poshytip" title="Enter your website" />
								</p>
								
								<p>
									<label for="comments">Message</label>
									<textarea  name="comments"  id="comments" rows="5" cols="20" class="form-poshytip" title="Enter your comments"></textarea>
								</p>
								
								<!-- send mail configuration 
								<input type="hidden" value="ENTER SUBJECT HERE" name="subject" id="subject" />
								<input type="hidden" value="send-mail.php" name="sendMailUrl" id="sendMailUrl" />
								<!-- ENDS send mail configuration 
								
								<p><input type="button" value="Send" name="submit" id="submit" /> <span id="error" class="warning">Message</span></p>
							</fieldset>
							
						</form>
						<p id="sent-form-msg" class="success">Form data sent. Thanks for your comments.</p>
						<!-- ENDS form -->
                        
																
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
		
		<!-- gmaps -->
		<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
		<script src="js/gmaps.js"></script>
		<!-- ENDS gmaps -->
		
		<!-- superfish -->
		<script  src="js/superfish-1.4.8/js/hoverIntent.js"></script>
		<script  src="js/superfish-1.4.8/js/superfish.js"></script>
		<script  src="js/superfish-1.4.8/js/supersubs.js"></script>
		<!-- ENDS superfish -->
		
		<script src="js/css3-mediaqueries.js"></script>
		
		<script src="js/nivoslider.js"></script>
		<script src="js/tabs.js"></script>
		<script src="js/form-validation.js"></script>
		
		
		<script type="text/javascript">
			var map;
			$(document).ready(function(){
				map = new GMaps({
					div: '#map',
					lat: -12.043333,
					lng: -77.028333
				});
				map.addMarker({
				  lat: -12.043333,
				  lng: -77.028333
				});
		    });
		  </script>
	
		<!-- ENDS JS -->
	</body>
</html>
