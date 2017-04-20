<?
session_start();
include("header.php");
?>
<div class="pop_up" id="pop_up">
	<center><div id="notification"></div></center>
</div>
<div class="menu">
<span class="menu_left_finish"><a class="no_color_link" href="logout.php">Log out</a></span>
<span class="menu_right_start"><a class="no_color_link" href="index.php">Back</a></span>
</div>
<div id="main">
<?


if(isset($_SESSION['type'])){
 if($_SESSION['type']>=0){
 include("functions.php"); 
	?>
	<div class="seperator" id="edit_grades" onclick="min_max_function($(this).attr('id'));">
		<center><span id="edit_grades_img"><img src="images/min_button.png" class="seperator_img" /></span>
		<h2 class="seperator_h2">Απομάκρυνση βαθμών</h2></center>
	</div>
	<div class="function" id="edit_grades_func">
		<div class="element_small">
			<div class="in_element">
			Flush Your Grades : 
			<select class="select_grade" id="class_flushing">
				<? option_classes(); ?>
			</select>
			<input type="submit" name="flush_class_student" value="OK" onclick="send_flush_grades_user(<? echo $_SESSION['id']; ?>);"/>
			</div>
		</div>
	</div>
	<?
 }
}

if(isset($_SESSION['type'])){
 if($_SESSION['type'] == 1){
 $database_already_here=1;
 include("functions_admin.php"); 
	?>
	<div class="seperator" id="admin_edit_grades" onclick="min_max_function($(this).attr('id'));">
		<center><span id="admin_edit_grades_img"><img src="images/max_button.png" class="seperator_img" /></span>
		<h2 class="seperator_h2">Απομάκρυνση βαθμών Διαχειριστή</h2></center>
	</div>
	<div class="function" id="admin_edit_grades_func">
		<div class="element_small">
			<div class="in_element">
			Flush Class Grades : 
			<select class="select_grade" id="class_flushing_admin">
				<? option_classes(); ?>
			</select>
			<input type="submit" name="flush_class" value="OK" onclick="flush_class_admin($('#class_flushing_admin').val());"/>
			</div>
		</div>
		<div class="element_small_acp2">
			<div class="in_element_acp">
			Flush Student AEM : 
			<input type="text" size="3" class="input_grade" id="student_flushing_admin" onkeyup="find_user_by_aem($(this).val(),$(this).attr('id'));" />
			<input type="submit" name="flush_student" value="OK" onclick="flush_student_admin($('#student_flushing_admin').val());"/><br />
			<span class="show_user_name" id="show_user_name_flush"></span>
			</div>
		</div>
	</div>
	<div class="seperator" id="admin_flush_comments" onclick="min_max_function($(this).attr('id'));">
		<center><span id="admin_flush_comments_img"><img src="images/max_button.png" class="seperator_img" /></span>
		<h2 class="seperator_h2">Απομάκρυνση Σχολίων Διαχειριστή</h2></center>
	</div>
	<div class="function" id="admin_flush_comments_func">
		<div class="element_small">
			<div class="in_element">
			Flush All Comments : 
			<select class="select_grade" id="all_comments_to_flush_menu">
				<option value="0">All</option>
				<option value="2">Public</option>
				<option value="1">Private</option>
			</select>
			<input type="submit" name="flush_all_comments" value="OK" onclick="flush_all_comments_admin();"/>
			</div>
		</div>
		<div class="element_small_acp3">
			<div class="in_element_acp2">
			Flush Comments for Student : 
			<input type="text" size="3" class="input_grade" id="student_flushing_admin_com" value="AEM" onkeyup="find_user_by_aem($(this).val(),$(this).attr('id'));" />
			<br/>
			<select class="cenctered_dropdown_acp3_small" id="comments_to_flush_menu">
				<option value="0">All</option>
				<option value="2">Public</option>
				<option value="1">Private</option>
			</select>
			<input type="submit" name="flush_student_comments" value="OK" onclick="flush_comments_for_student_admin($('#student_flushing_admin_com').val());"/><br />
			<span class="show_user_name" id="show_user_name_flush_com"></span>
			</div>
		</div>
	</div>
	<?
 }
}
echo "</div>";
include("footer.php");
?>