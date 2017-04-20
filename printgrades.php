<?
session_start();
$database_already_here=1;
include("functions.php"); 
//include("functions_admin.php"); 
echo '<script type="text/javascript" src="functions_user_with_admin_show.js"> </script>';

if(isset($_SESSION['type'])){
	if($_SESSION['active']==1){
	include("header.php");
?>
<div class="pop_up" id="pop_up">
	<center><div id="notification"></div></center>
</div>
<div class="menu">
<span class="menu_left_finish"><a class="no_color_link" href="logout.php">Log out</a></span>
<span class="menu_right_start"><a class="no_color_link" href="index.php">Back</a></spa>
</div>

<div id="main">

	<div class="seperator" id="admin_view_grades" onclick="min_max_function($(this).attr('id'));">
		<center><span id="admin_view_grades_img"><img src="images/min_button.png" class="seperator_img" /></span>
		<h2 class="seperator_h2">Προβολή βαθμολογιών</h2></center>
	</div>
	<div class="function" id="admin_view_grades_func">
			<center>
			Προβολή βαθμών για 
			<label><input type="radio" name="show_grades_option" value="class" onclick="choose_class_show();">Μάθημα</label>
			<br />
			<div id="choose_class">
				Επιλέξτε Μάθημα :
				<select class="select_grade" id="class_select" onchange="get_labs_grades_show(this.value);show_grades_for_class_mdasyg($(this).val());">
					<option value='-1'>Παρακαλώ Επιλέξτε</option>
					<? option_classes(); ?>
				</select><br />
			</div>
			</center><br />
			<div id="show_grades_admin">
			</div>
			<div id="comments_container" ></div>
			<div id="shadow_under_popup" onclick="$('#shadow_under_popup').hide();$('#comments_container').hide();"></div>
	</div>
	
</div>
<?
		include("footer.php");
	}else{
		echo '<html><head><title>PrintGrades - Summary Page of Student Grades at ICTE/UOWM of Dasygenis courses</title></head><body>';
		echo "<script>alert('Please acitvate your account first.');</script> ";
		echo 'Summary Page of Student Grades of Dasygenis courses is only available to authorized students<br />';
		echo 'You are beeing redirected in 5 secs to login page';
		echo '<meta http-equiv="refresh" content="5; url=../schedule/index.php">';
		echo '</body></html>';
	}
}else{
echo '<html><head><title>PrintGrades - Summary Page of Student Grades at ICTE/UOWM of Dasygenis courses</title></head><body>';
	echo "<script>alert('Please log in first.');</script> ";
echo 'Summary Page of Student Grades of Dasygenis courses is only available to authorized students<br />';
echo 'You are beeing redirected in 5 secs to login page';
echo '<meta http-equiv="refresh" content="5; url=../schedule/index.php">';
echo '</body></html>';
}

?>
