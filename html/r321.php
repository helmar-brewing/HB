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

?>


<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" > <!--<![endif]-->

	<head>
		<meta charset="utf-8">
		<title>Helmar Baseball Art Card Company</title>
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width">
		
		<!-- CSS -->
		<link href='http://fonts.googleapis.com/css?family=Arvo:400' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" href="css/style.css">
		<link rel="stylesheet" href="css/responsive.css">
        <link rel="stylesheet" href="css/helmar.css">
		
		<script src="js/modernizr.js"></script>
        
        <!-- light box, thank you http://lokeshdhakar.com/projects/lightbox2/ -->
        <link href="css/lightbox.css" rel="stylesheet" />
        <script src="js/jquery-1.10.2.min.js"></script>
		<script src="js/lightbox-2.6.min.js"></script>
        

	</head>
	<body>
	
		
		<!-- WRAPPER -->
		<div class="wrapper">
		
			<?php include 'header.php';?>
			
            <!-- MAIN -->
			<div id="main" class="cf">
				
				<!-- page-content -->
				<div class="page-content">
					
					<!-- entry-content -->	
		        	<div class="entry-content cf">
                    
                    <h2 class="heading">
				Helmar Artwork >> R321 Series </h2>
                
				
                <p><center>
                <img src="images/cardPics/R321-Helmar_3_Front.jpg" alt="R321-Helmar" width=300/>
                <img src="images/cardPics/R321-Helmar_3_Back.jpg" alt="R321-Helmar" width=300/>
                </center></p>
		        		

<!-- edit series description here -->
<!-- edit series description here -->
<!-- edit series description here -->
   
		        		<h3  class="heading">R321-Helmar</h2>
		        		<p></p>
                        
                        
                        
                        
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
   </table>

<div id="wrapper">
	<table>

		<!-- Table Body -->
    	<tbody>

<?php
	$i = 0;
	$R_cards = $db_main->query("SELECT * FROM cardList WHERE series = 'R321-Helmar'");
	if($R_cards !== FALSE){
		$R_cards->data_seek(0);
		while($card = $R_cards->fetch_assoc()){
			print'
				<tr>
					<td align="center" class="smallCol">'.$card['cardnum'].'</td>
					<td>'.$card['player'].'</td>
					<td>'.$card['description'].'</td>
					<td>'.$card['team'].'</td>
			';
			if($card['averagesold'] == 0){
				print'
					<td class="smallCol"></td>
					<td class="smallCol"></td>
				';
			}else{
				print'
					<td class="smallCol">'.$card['lastsold'].'</td>
					<td class="smallCol" align="right">'.$card['averagesold'].'</td>
				';
			}
			// old section for retired, currently using for front/back pic
			//print '<td align="center" class="smallCol">'.$row['retired'].'</td>';
			
			// for one row, check if picture exists
			print'
					<td align="center" class="picCol">
			';
			
			
			// define the pictures
			$frontpic = 'images/cardPics/'.$card['series'].'_'.$card['cardnum'].'_Front.jpg';
			$frontthumb = 'images/cardPics/thumb/'.$card['series'].'_'.$card['cardnum'].'_Front_small.jpg';
			$backpic  = 'images/cardPics/'.$card['series'].'_'.$card['cardnum'].'_Back.jpg';
			$backthumb  = 'images/cardPics/thumb/'.$card['series'].'_'.$card['cardnum'].'_Back_small.jpg';
			
			//check if either pic exists
			if( file_exists($frontpic) || file_exists($backpic) ){
			
				// print the front pic if exists
				if(file_exists($frontpic)){
				/*	print'
						<a href="http://www.helmarbrewing.com/'.$frontpic.'" data-lightbox="'.$card['series'].'_'.$card['cardnum'].'" ><font size="5">F</font></a>
					';*/
					print'
						<a href="http://www.helmarbrewing.com/'.$frontpic.'" data-lightbox="'.$card['series'].'_'.$card['cardnum'].'" ><img src="http://www.helmarbrewing.com/'.$frontthumb.'"></a>
					';
				}
				
				// insert space
				if( file_exists($frontpic) && file_exists($backpic) ){
					print'&nbsp;&nbsp;';
				}
				
				// print the back pic if exists
				if(file_exists($backpic)){
					/*print'
						<a href="http://www.helmarbrewing.com/'.$backpic.'" data-lightbox="'.$card['series'].'_'.$card['cardnum'].'" ><font size="5">B</font></a>
					';*/
					print'
						<a href="http://www.helmarbrewing.com/'.$backpic.'" data-lightbox="'.$card['series'].'_'.$card['cardnum'].'" ><img src="http://www.helmarbrewing.com/'.$backthumb.'"></a>
					';
					
				}
				
			// neither pic exists print message instead
			}else{
				print'
						<i>no picture</i>
				';
			}
			
			print'
					</td>
				</tr>
			';
			$i++;
			$updated = $card['updatedate'];
		}
		$R_cards->free();
	}else{
		print'
			<tr><td colspan="7">could not get list of cards</td></tr>
		';
	}

	print'
		</tbody>
		<!-- Table Body -->
		</table>
		</div>
		
		<p>
			Card list last updated: '.$updated.'<br/>
			Number of Records: '.$i.'
		</p>
	';

	$db_main->close();   // We no longer need the connection to the database on this page so we have to close it.
	
?>  


</p>
                  

											
					</div>
					<!-- ENDS entry-content -->
	
				</div><!-- ENDS page-content -->				
				
			</div>
			<!-- ENDS MAIN -->
		
			
			
			
			
			
			<?php include 'footer.php';?>
		
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
