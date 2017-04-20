<?
session_start();
echo "<!-- running ID:".$_SESSION['id']." -->";
include("functions.php"); 

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
<span class="menu_right_start"><a class="no_color_link" href="gradehold.php">Theory Grade Hold </a></span>
<span class="menu_right_start"><a class="no_color_link" href="printgrades.php">Grades Summary </a></span>
</div>

<div id="main">

	<?  show_comments_user(); ?>
	
	<div class="seperator" id="add_grade" onclick="min_max_function($(this).attr('id'));">
		<center><span id="add_grade_img"><img src="images/min_button.png" class="seperator_img" /></span>
		<h2 class="seperator_h2">Προσθήκη ή Τροποποίηση βαθμού</h2></center>
	</div>
	<div class="function" id="add_grade_func">
		<div class="element_alone">
		<center>
			<div class="in_element_alone" >
				Μάθημα:<br />
				<select class="select_grade" onchange="get_labs(this.value)" id="select_class">
				  <? option_classes(); ?>
				</select><br /><br />
				Εργαστήριο:<br />
				<select class="select_grade_labs" id="select_lab" onchange="get_lab_details(this.value,<? echo $_SESSION['id']; ?>)" >
				  <option value="00">Επιλέξτε Μάθημα</option>
				</select><br /><br />
				Βαθμός: <br />
				<input type="text" size="3" class="input_grade" id="enter_new_grade" /><br/>
				<span class="example_text">(π.χ. 25)</span><br /><br />
				Παρατηρήσεις εργαστηριακής άσκησης<span class="timestamp_text" title="Προαιρετικό">*</span>: <br />
				<textarea maxlength="254" class="input_grade_comment" id="enter_new_comment"></textarea>
				<br />
				<span class="example_text" style="margin-left: 10px;">
					<i>Αφήστε κενό για διαγραφή παρατήρησης.</i>
				</span>
				<br /><br />
				<span class="checkbox_small_text">
					<input id="notify_grade_change" type="checkbox" name="notify_grade_change" value='1' checked> 
					Send notification for this grade modification via email.
				</span>
				<br /><br />
				<center>
				<input type="submit" name="enter_grade" value="OK" onclick="send_new_grade(<? echo $_SESSION['id']; ?>);update_showcase_grades();"  />
				</center>
			</div>
		</center>
		</div>
	</div>
	
	<div class="seperator" id="view_grades" onclick="min_max_function($(this).attr('id'));">
		<center><span id="view_grades_img"><img src="images/min_button.png" class="seperator_img" /></span>
		<h2 class="seperator_h2">Προβολή βαθμών</h2></center>
	</div>
	<div class="function" id="view_grades_func">
		<div class="element_list" id="view_grades_list">
			<? show_grades_user(-1); ?>
		</div>
		<input type='hidden' id='user_id_for_js' value='<? echo $_SESSION['id']; ?>'>
	</div>
	
	<div class="seperator" id="edit_grades" onclick="min_max_function($(this).attr('id'));">
		<center><span id="edit_grades_img"><img src="images/min_button.png" class="seperator_img" /></span>
		<h2 class="seperator_h2">Διαγραφή βαθμών</h2></center>
	</div>
	<div class="function" id="edit_grades_func">
		<div class="element_small_acp">
			<div class="in_element_acp">
			<a class="no_color_link_in_element_user" href="flushgrades.php">Απομάκρυνση όλων των βαθμολογιών (κλειδωμένων ή μη, που έχει βάλει ο διδάσκοντας ή ο φοιτητής) για ένα μάθημα.</a>
			</div>
		</div>
	</div>
	
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
	echo "<script>alert('Please log in first.');</script> ";
	header ("location: ../schedule/index.php");
}

?>
