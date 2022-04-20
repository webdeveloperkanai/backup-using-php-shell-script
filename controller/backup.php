<?php 

require "config.php"; 
$ch = curl_init();
  curl_setopt($ch, CURLOPT_URL,"?backup=true&path=$path&dbuser=$dbuser&dbhost=$dbhost&dbpass=$dbpass&dbname=$dbname");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
  $response = curl_exec($ch);
  $result = json_decode($response,1);
  curl_close($ch); // Close the connection

foreach ($result as $rs) {
    if($rs!="Success") { echo "<a href='".$rs."' target='_blank' > Download </a> <br> ";}
}
?>