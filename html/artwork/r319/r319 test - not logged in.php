<?php
ob_start();

/* ROOT SETTINGS */ require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');

/* FORCE HTTPS FOR THIS PAGE */ if($use_https === TRUE){if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == ""){header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);exit;}}

/* WHICH DATABASES DO WE NEED */
	$db2use = array(
		'db_auth' 	=> FALSE,
		'db_main'	=> TRUE
	);
//

/* GET KEYS TO SITE */ require($path_to_keys);

ob_end_flush();

include 'file:///C|/Users/admin/Desktop/Helmar Work/layout/header.php';

?>
			
            <!-- MAIN -->
			<div id="main" class="cf">
				
				<!-- page-content -->
				<div class="page-content">
					
					<!-- entry-content -->	
		        	<div class="entry-content cf">
                    
                    <h2 class="heading">
					Helmar Artwork >> R319-Helmar Series
				</h2>
                
				
                <p><center>
                <img src="file:///C|/Users/admin/Desktop/Helmar Work/images/cardPics/R319-Helmar_375_Front.jpg" alt="R319-Helmar" width=300/>
                <img src="file:///C|/Users/admin/Desktop/Helmar Work/images/cardPics/R319-Helmar_375_Back.jpg" alt="R319-Helmar" width=300/>
                </center></p>
		        		

<!-- edit series description here -->
<!-- edit series description here -->
<!-- edit series description here -->
   
		        		<h3  class="heading">R319-Helmar</h2>
		        		<p>The R-319 Helmar series has 180 subjects including many of your favorite players. All the original art was painted by our artists over a period of years, you won't find it elsewhere. Given the scope, the expense and the complexity for a small company or artist to put together a 385 card set of original and exceptional art, no one else may attempt something this ambitious for decades.  They are not available in full sets.</p>
                        
                        
                        
                        
                         <p>

<table>
   <!-- Table Header -->
  <!--  <thead>-->
	<tr align="center">
		<!--<th>Series</th>-->
		<th class="smallCol">Card Number</th>
		<th>Player</th>
		<th>Stance/Position</th>
		<th>Team</th>
		<th class="smallCol">Last Sold</th>
        <th class="smallCol">Average Sold</th>
        <th class="picCol">Picture?</th>
	</tr>
<!--    </thead>-->
    <!-- Table Header -->


<?php
	print '<a href="http://helmar.dev.stev.co/account/register/"><img src="http://helmar.dev.stev.co/img/checklist-sample.jpg"></a>';
?>  


</p>
                  

											
					</div>
					<!-- ENDS entry-content -->
	
				</div><!-- ENDS page-content -->				
				
			</div>
			<!-- ENDS MAIN -->
		
			
			
		<!-- JS -->
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
		<script>window.jQuery || document.write('<script src="js/jquery.js"><\/script>')</script>
		<script src="file:///C|/Users/admin/Desktop/Helmar Work/js/custom.js"></script>
		
		<!-- superfish -->
		<script  src="file:///C|/Users/admin/Desktop/Helmar Work/js/superfish-1.4.8/js/hoverIntent.js"></script>
		<script  src="file:///C|/Users/admin/Desktop/Helmar Work/js/superfish-1.4.8/js/superfish.js"></script>
		<script  src="file:///C|/Users/admin/Desktop/Helmar Work/js/superfish-1.4.8/js/supersubs.js"></script>
		<!-- ENDS superfish -->
		
		<script src="file:///C|/Users/admin/Desktop/Helmar Work/js/css3-mediaqueries.js"></script>
		
		<script src="file:///C|/Users/admin/Desktop/Helmar Work/js/nivoslider.js"></script>
		<script src="file:///C|/Users/admin/Desktop/Helmar Work/js/tabs.js"></script>
		
	
		<!-- ENDS JS -->			
			
			
<?php include 'file:///C|/Users/admin/Desktop/Helmar Work/layout/footer.php';?>