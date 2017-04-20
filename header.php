<?php 
if(!isset($_SESSION)) {
     session_start();
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html><head>
<title>iGrades</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!--[if IE]>
<link href="style_ie.css" rel="stylesheet" type="text/css" media="screen"/>
<![endif]-->
<!--[if !IE]> -->
<link href="style.css" rel="stylesheet" type="text/css" media="screen"/>
<!-- <![endif]-->
<link rel="stylesheet" href="jdpicker.css" type="text/css" media="screen" />
<script src="/jquery-latest.js"></script>
<script type ="text/javascript" src="jquery.jdpicker.js"></script>
<script type ="text/javascript" src="functions_js.js"></script>
<link type="text/css" href="../menu.css" rel="stylesheet" />
<script type="text/javascript" src="../jquery/menu.js"></script>
</head>
<body>
<div id="header">

	<div id="logo">
		<div id="logoimg">
		</div>
	</div>
	<div class="headershadow">
	</div>
	<div id="menu">

<?php require_once("../menu.php");?>
		
	<div id="decor">
	</div>
	<div class="header_user_welcome">Welcome <?php echo $_SESSION['username']."(".$_SESSION['id'].")";?>, you are logged in.</div>
	</div>
	
</div>
<div style="visibility:hidden">
 <a href="http://apycom.com/">Apycom jQuery Menus</a>
</div>
<div id="container">
