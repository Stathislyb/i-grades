<?
if(!isset($_SESSION)) {
     session_start();
}
include_once("functions.php"); 
if($_SESSION['type'] == 1){
	include_once("functions_admin.php"); 
}
if(isset($_SESSION['type'])){
 if($_SESSION['type']>=0){
	if($_SESSION['active']==1){
		include("header.php");
?>
<div class="pop_up" id="pop_up">
	<center><div id="notification"></div></center>
</div>
<div class="menu">
<span class="menu_left_finish"><a class="no_color_link" href="logout.php">Log out</a></span>
<?
if($_SESSION['type']==1){
	echo "<span class='menu_middle'><a class='no_color_link' href='index.php'>Back to Admin</a></span>";
}
?>
<span class="menu_right_start"><a class="no_color_link" href="printgrades.php">Grades Summary </a></span>
</div>

<div id="main">

	
	<div class="seperator" id="add_grade" onclick="min_max_function($(this).attr('id'));">
		<center><span id="add_grade_img"><img src="images/min_button.png" class="seperator_img" /></span>
		<h2 class="seperator_h2">Κράτηση ή Απομάκρυνση Κράτησης Βαθμολογίας</h2></center>
	</div>
	<div class="function" id="hold_grade_func">
		<div class="element_alone">
		<center>
			<div class="in_element_alone" >
				Μάθημα:<br />
				<select class="select_grade" onchange="get_labs(this.value)" id="select_class">
				  <? option_classes(); ?>
				</select><br /><br />

				<center>
				<!-- Dasygenis: Uncomment one of these blocks -->
				<!-- <input type="submit" name="hold_grade" value="HOLD/UNHOLD" onclick="hold_grade(<? echo $_SESSION['id']; ?>)"  />  -->
				<input type="submit" name="hold_grade" <?php echo (get_hold_status() == 0)?"disabled='disabled'":""; ?> value="HOLD/UNHOLD" onclick="hold_grade(<? echo $_SESSION['id']; ?>)"  />


				<input type="submit" name="check_hold_grade" value="check" onclick="check_hold_grade(<? echo $_SESSION['id']; ?>)"  />
				<input type="submit" name="full_listing" value="list" onclick="print_hold_listing()"/>
				</center>
				<ul>
				<li>Πατήστε μια φορά το HOLD/UNHOLD για να κρατηθεί ο βαθμός (δείτε το μήνυμα).</li>
				<li>Πατήστε ξανά για να μην κρατηθεί ο βαθμός, αν έχει ήδη κρατηθεί (δείτε το μήνυμα).</li>
				<li>Πατήστε list για να δείτε αν το ΑΕΜ σας έχει σημειωθεί για κράτηση της βαθμολογίας.</li>
				</ul>
			</div>
		</center>
		</div>
	</div>
	<?php 
	if($_SESSION['type'] == 1){
	?>
	<div class="function" id="hold_grade_options">
		<div class="element_alone">
		<center>
			<div class="in_element_alone" >
				Επιλογές Διαχειριστή:<br /><br/>
				<center>
				Η δυνατότητα HOLD είναι <u><?php echo (get_hold_status() == 0)?"Απενεργοποιημένη":"Ενεργοποιημένη"; ?></u> <br/><br/>
				<form method="POST" action="">
					<input type="submit" name="hold_grade_toggle" value="<?php echo (get_hold_status() == 0)?"Ενεργοποίηση":"Απενεργοποίηση"; ?>" />
				</form>
				</center>
			</div>
		</center>
		</div>
	</div>
	<?php } ?>
</div>

<?
		include("footer.php");
	}else{
		echo "You don't have user rights. Please activate your account.";
	}
 }else{
	echo "You don't have user rights.";
 }
}else{
	header ("location: ../schedule/index.php");
}

?>
