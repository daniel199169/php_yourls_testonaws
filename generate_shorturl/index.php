<?php
$number = $_GET["number"];

$user_yourls = "yourls";
            $pass_yourls = "StrongPassword";
            $server_yourls = "localhost";
            $db_yourls = "yourls";
            $conn_yourls = mysqli_connect($server_yourls, $user_yourls, $pass_yourls, $db_yourls);
            if($conn_yourls){
                
                $query_count = "SELECT * FROM yourls_url";
                $result_count = mysqli_query($conn_yourls, $query_count);
                $db_row_count = mysqli_num_rows($result_count);
               
               
            }else{
                echo "connection failed";
               
            }
            
$number = $number + $db_row_count;
$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
$no = 1;
for($i = 0; $i < strlen($characters) ; $i++){
   for($j = 0; $j < strlen($characters) ; $j++){
      for($k = 0; $k < strlen($characters) ; $k++){
         for($l = 0; $l < strlen($characters) ; $l++){
            for($m = 0; $m < strlen($characters) ; $m++){
               if($no > $number) break 5;
               if($no > $db_row_count){
                  $shorturl = $characters[$i].$characters[$j].$characters[$k].$characters[$l].$characters[$m];
                  
                   
                    $timestamp = date('Y-m-d H:i:s');
                    
                   
                    
                    $query = "INSERT INTO yourls_url (keyword, url, title, timestamp, ip, clicks) VALUES ('$shorturl','','', '$timestamp', '', 0)";
                    mysqli_query($conn_yourls, $query);
                    echo $shorturl;
                    echo "<br>";
               }
               $no++;
            }
         }
      }
   } 
}


?>