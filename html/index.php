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

/* GET COOKIE */ if(isset($_COOKIE[$gv_login_cookie_name])){$cookie = $_COOKIE[$gv_login_cookie_name];}

ob_end_flush();

?>







<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" > <!--<![endif]-->
	<head>
		<meta charset="utf-8">
		<title>Helmar Baseball Art Card Company</title>
		<meta name="description" content=\">
		<meta name="viewport" content="width=device-width">
		
		<!-- CSS -->
		<link href='http://fonts.googleapis.com/css?family=Arvo:400' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" href="css/style.css">
		<link rel="stylesheet" href="css/responsive.css">
		
		<script src="js/modernizr.js"></script>
        
	</head>
	<body>
	
		
		<!-- WRAPPER -->
		<div class="wrapper">
		
			<!-- HEADER -->
			<header class="cf">
				<div id="logo"><a href="index.php"><img src="img/helmartitle2.png" alt="Helmar Baseball Card Art Company" /></a></div>
				
				<!-- social-bar -->
				<ul id="social-bar" class="cf">
					<li class="facebook"><a href="https://www.facebook.com/pages/Helmar-Baseball-Art-Card-Company/666710940030121"  title="Facebook" target="new"></a></li>
					<li class="ebay"><a href="http://stores.ebay.com/Helmar-Brewing-Art-and-History/"  title="Ebay" target="new"></a></li>
					

				</ul>
				<!-- ENDS social-bar -->
				
			</header>
			<!-- ENDS HEADER -->
			
			<!-- NAV -->
			<nav class="cf">
				<ul id="nav" class="sf-menu">
					<li class="current-menu-item"><a href="index.php">Home</a></li>
					<li><a href="artwork.php">Helmar Artwork</a></li>
					<li><a href="about.php">Helmar & Charles</a></li>
					<li><a href="contact.php">Stay in Touch</a></li>
                    <li class="important"><a href="http://helmarblog.wordpress.com" target="new">helmarblog</a></li>
					<li class="important"><a href="http://stores.ebay.com/Helmar-Brewing-Art-and-History/" target="new">OUR STORE!</a></li>
					
				</ul>
			</nav>
			<!-- ENDS NAV -->
			
			
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
				
				<p style="line-height:305px;"> <!-- line height added as a quick hacky fix to put spacing between rows of cards -->
                
<?php
	
	$R_auctions = $db_main->query("SELECT * FROM activeAuctions");
	if($R_auctions !== FALSE){
		$R_auctions->data_seek(0);
		while($auction = $R_auctions->fetch_assoc()){
			print'
					<a href="http://www.ebay.com/itm/'.$auction['auctionID'].'" class="thumb" target="new"><img src="'.$auction['imgURL'].'" style="max-height:300px;" /></a>
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
			
			
			
			
			
			<!-- FOOTER -->
			<div class="footer-divider"></div>
			<footer class="cf">
				Madison Theme by <a href="http://www.luiszuno.com" >luiszuno.com</a> 
			</footer>
			<!-- ENDS FOOTER -->
		
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
