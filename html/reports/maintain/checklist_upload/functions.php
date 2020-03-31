<?php
     if(isset($_POST["Import"])){
        
        $filename=$_FILES["file"]["tmp_name"];    
         if($_FILES["file"]["size"] > 0)
         {
            $file = fopen($filename, "r");
              while (($getData = fgetcsv($file, 10000, ",")) !== FALSE)
               {
                 $sql = "INSERT into test (test1,test2,num1) 
                       values ('".$getData[0]."','".$getData[1]."','".$getData[2]."')";
                       $result = mysqli_query($db_main, $sql);
            if(!isset($result))
            {
              echo "<script type=\"text/javascript\">
                  alert(\"Invalid File:Please Upload CSV File. ". $filename ."\" );
                  window.location = \"index.php\"
                  </script>";    
            }
            else {
                echo "<script type=\"text/javascript\">
                alert(\"CSV File has been successfully Imported.\");
                window.location = \"index.php\"
              </script>";
            }
               }
          
               fclose($file);  
         }
      }   
     ?>