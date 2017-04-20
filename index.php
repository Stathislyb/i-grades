<?
session_start();
header("Cache-Control: max-age=259200"); //3days (60s x 60m x 24h x 3d)
if(isset($_SESSION['username']) && isset($_SESSION['password']) && $_SESSION['active']==1){
 if($_SESSION['type']==1){
	include("admin.php"); 
 }else{
	include("user.php"); 
 }
}else{
	header ("location: ../schedule/index.php?entry=igrades");
}

?>
