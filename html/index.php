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

include 'layout/header.php';

?>

			<!-- MAIN -->
			<div id="main" class="cf">
			
            	<!-- page-content -->
				<div class="page-content">
					
					<!-- entry-content -->	
		        	<div class="entry-content cf">

				
				<!-- featured -->
				<h2 class="heading">Welcome!</h2>
				<p class="feature cf">
				Welcome to the homepage of Helmar Brewing Company! Helmar Brewing Co. produces fine, hand-made art cards, mainly of sports and history subjects. <a href="http://stores.ebay.com/Helmar-Brewing-Art-and-History/">Please visit our eBay store!</a>
					
				</p>
                <p><font color="red"><u>Please note that the website is still under deveopment. Certain links/pages may not display properly. Thank you for this understanding. </u></font></p>
				<!-- ENDS featured -->
                <h2 class="heading">
<?php
	$auction_end_date = db1($db_main, "SELECT endDateString FROM activeAuctions LIMIT 1");
	if($auction_end_date != FALSE){
		print '
					&nbsp; &nbsp; See our current auctions ending on '.$auction_end_date.'!
		';
	}
?>  	
				</h2>
				
				<p style="line-height:155px;"> <!-- line height added as a quick hacky fix to put spacing between rows of cards -->
                
<?php
	
	$R_auctions = $db_main->query("SELECT * FROM activeAuctions");
	if($R_auctions !== FALSE){
		$R_auctions->data_seek(0);
		while($auction = $R_auctions->fetch_assoc()){
			print'
					<a href="http://www.ebay.com/itm/'.$auction['auctionID'].'" class="thumb" target="new"><img src="'.$auction['imgURL'].'" style="max-height:150px;" /></a>
			';
		}
		$R_auctions->free();
	}else{
		print 'Could not get list of auctions.';
	}
	
	
	
	$db_main->close();   // We no longer need the connection to the database on this page so we have to close it.
	
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
			
			
<?php include 'layout/footer.php';?>