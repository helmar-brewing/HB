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
            
  
					
            
            <h2 class="heading">
					&nbsp; &nbsp; Here is some of our artwork!
				</h2>
                <p><font color="red"><u>Please note that the website is still under deveopment. Certain links/pages may not display properly. Thank you for this understanding. </u></font></p>
				
				<!-- work list -->
				<ul class="work-list cf" id="filter-container">
					
					<li>
						<a href="r319.php" class="thumb" >
							<img src="images/cardPics/R319-Helmar_10_Front.jpg" alt="R319-Helmar" />
							<div class="img-overlay">View Series</div>
						</a>
						<a href="r319.php"  class="title" > <center>R319-Helmar</center></a>
						
					</li>

					
                    
                    <li>
						<a href="e145.php" class="thumb" >
							<img src="images/cardPics/E145-Helmar_92_Front.jpg" alt="E145-Helmar" />
							<div class="img-overlay">View Series</div>
						</a>
						<a href="e145.php"  class="title" > <center>E145-Helmar</center></a>
						
					</li>
                    
                    <li>
						<a href="6up.php" class="thumb" >
							<img src="images/cardPics/Helmar_6_Up_Die-Cut_71_Front.jpg" alt="Helmar 6 Up Die-Cut" />
							<div class="img-overlay">View Series</div>
						</a>
						<a href="6up.php"  class="title" > <center>Helmar 6 Up Die-Cut</center></a>
						
					</li>
                    
                    <li>
						<a href="trolley.php" class="thumb" >
							<img src="images/cardPics/Helmar_Trolley_Card_13_Front.jpg" alt="Helmar Trolley Card"/>
							<div class="img-overlay">View Series</div>
						</a>
						<a href="trolley.php"  class="title" > <center>Helmar Trolley Card</center></a>
						
					</li>
                    
                    
                    
                    <li>
						<a href="r318.php" class="thumb" >
							<img src="images/cardPics/R318-Helmar_147_Front.jpg" alt="R318-Helmar" />
							<div class="img-overlay">View Series</div>
						</a>
						<a href="r318.php"  class="title" > <center>R318-Helmar</center></a>
						
					</li>
                    
                    
                    
                    
                    
                    <li>
						<a href="cabinet.php" class="thumb" >
							<img src="images/cardPics/Helmar_Cabinet_5_Front.jpg" alt="Helmar Cabinet"/>
							<div class="img-overlay">View Series</div>
						</a>
						<a href="cabinet.php"  class="title" > <center>Helmar Cabinet</center></a>
						
					</li>
                    
                    <li>
						<a href="t206.php" class="thumb" >
							<img src="images/cardPics/T206-Helmar_110_Front.jpg" alt="T206-Helmar"/>
							<div class="img-overlay">View Series</div>
						</a>
						<a href="t206.php"  class="title" > <center>T206-Helmar</center></a>
						
					</li>
                    
                    
                    
                    <li>
						<a href="h813.php" class="thumb" >
							<img src="images/cardPics/H813-4_Boston_Garter-Helmar_32_Front.jpg" alt="H813-4 Boston Garter-Helmar"/>
							<div class="img-overlay">View Series</div>
						</a>
						<a href="h813.php"  class="title" > <center>H813-4 Boston Garter-Helmar</center></a>
						
					</li>
                    
                    <li>
						<a href="r321.php" class="thumb" >
							<img src="images/cardPics/R321-Helmar_1_Front.jpg" alt="R321-Helmar"/>
							<div class="img-overlay">View Series</div>
						</a>
						<a href="r321.php"  class="title" > <center>R321-Helmar</center></a>
						
					</li>
                    
                    <li>
						<a href="imperial.php" class="thumb" >
							<img src="images/cardPics/Helmar_Imperial_Cabinet_23_Front.jpg" alt="Helmar Imperial Cabinet"/>
							<div class="img-overlay">View Series</div>
						</a>
						<a href="imperial.php"  class="title" > <center>Helmar Imperial Cabinet</center></a>
						
					</li>
                    
                    <li>
						<a href="l1.php" class="thumb" >
							<img src="images/cardPics/L1-Helmar_43_Front.jpg" alt="L1-Helmar"/>
							<div class="img-overlay">View Series</div>
						</a>
						<a href="l1.php"  class="title" > <center>L1-Helmar</center></a>
						
					</li>
                    
                    
                    
                    <li>
						<a href="t202.php" class="thumb" >
							<img src="images/cardPics/T202-Helmar_2_Front.jpg" alt="T202-Helmar"/>
							<div class="img-overlay">View Series</div>
						</a>
						<a href="t202.php"  class="title" > <center>T202-Helmar</center></a>
						
					</li>
                    
                    <li>
						<a href="t2.php" class="thumb" >
							<img src="images/cardPics/T202-Helmar_12_Front.jpg" alt="T2-Helmar"/>
							<div class="img-overlay">View Series</div>
						</a>
						<a href="t2.php"  class="title" > <center>T2-Helmar</center></a>
						
					</li>
                    
                    <li>
						<a href="ourguy.php" class="thumb" >
							<img src="images/cardPics/Our_Guy_28_Front.jpg" alt="Our Guy"/>
							<div class="img-overlay">View Series</div>
						</a>
						<a href="ourguy.php"  class="title" > <center>Our Guy</center></a>
						
					</li>
                    
                    <li>
						<a href="t3.php" class="thumb" >
							<img src="images/cardPics/T3-Helmar_2_Front.jpg" alt="T3-Helmar"/>
							<div class="img-overlay">View Series</div>
						</a>
						<a href="t3.php"  class="title" > <center>T3-Helmar</center></a>
						
					</li>
                    
                    <li>
						<a href="pharoh.php" class="thumb" >
							<img src="images/cardPics/Helmar_Pharohs_Choice_Cabinet_1_Front.jpg" alt="Helmar Pharoh's Choice Cabinet"/>
							<div class="img-overlay">View Series</div>
						</a>
						<a href="pharoh.php"  class="title" > <center>Helmar Pharoh's Choice Cabinet</center></a>
						
					</li>
                    
                    
                    
                    <li>
						<a href="l3.php" class="thumb" >
							<img src="images/cardPics/L3-Helmar_Cabinet_52_Front.jpg" alt="L3-Helmar Cabinet"/>
							<div class="img-overlay">View Series</div>
						</a>
						<a href="l3.php"  class="title" > <center>L3-Helmar Cabinet</center></a>
						
					</li>
                    
                    <li>
						<a href="polo.php" class="thumb" >
							<img src="images/cardPics/need.jpg" alt="Polo Grounds Heroes"/>
							<div class="img-overlay">View Series</div>
						</a>
						<a href="polo.php"  class="title" > <center>Polo Grounds Heroes</center></a>
						
					</li>
                    
                    <li>
						<a href="l2.php" class="thumb" >
							<img src="images/cardPics/need.jpg" alt="L2-Helmar"/>
							<div class="img-overlay">View Series</div>
						</a>
						<a href="l2.php"  class="title" > <center>L2-Helmar</center></a>
						
					</li>
                    					

				</ul>
				<!-- ENDS work list -->		
                

				
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