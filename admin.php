<?
if(!isset($_SESSION)) {
     session_start();
}
$database_already_here=1;
include_once("functions.php"); 
include_once("functions_admin.php"); 

if(isset($_SESSION['type'])){
 if($_SESSION['type']==1){
	include("header.php");
?>
<div class="pop_up" id="pop_up">
	<center><div id="notification"></div></center>
</div>
<div class="menu">
<span class="menu_left_finish"><a class="no_color_link" href="logout.php">Log out</a></span>
<span class="menu_middle"><a class="no_color_link" href="printgrades.php">Grades Summary</a></span>
<span class="menu_right_start"><a class="no_color_link" href="gradehold.php">Theory Grade Hold </a></span>
<span class="menu_right_start"><a class="no_color_link" href="user.php">View as User</a></span>
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
			<label><input type="radio" name="show_grades_option" value="user" onclick="choose_user_show();">Φοιτητή</label>
			<br />
			<div id="choose_class">
				Επιλέξτε Μάθημα:
				<select class="select_grade" id="class_select" onchange="get_labs_grades_show(this.value);show_grades_for_class($(this).val());">
					<option value='-1'>Παρακαλώ Επιλέξτε</option>
					<? option_classes(); ?>
				</select><br />
				Επιλέξτε Ε/Θ:
				<select class="select_grade" id="show_lab_select" onchange="show_grades_for_lab($(this).val());">
					<option value='-1'>Επιλέξτε Μάθημα</option>
				</select>
			</div>
			<div id="choose_user">
				Δώστε ΑΕΜ: 
				<input type="text" size="3" class="input_grade" id="get_aem_show_grades" onkeyup="setTimeout(show_grades_for_user, 1500);" /> <br />
				Εμφάνιση βαθμολογιών από disabled μαθήματα 
				<input id="show_disabled_classes_for_user" name="show_disabled_classes_for_user" type="checkbox" onclick="show_grades_for_user();" />
			</div>
			</center><br />
			<div id="show_grades_admin">
			</div>
			<div id="comments_container" ></div>
			<div id="shadow_under_popup" onclick="$('#shadow_under_popup').hide();$('#comments_container').hide();"></div>
	</div>
	
	<div class="seperator" id="admin_edit_grades" onclick="min_max_function($(this).attr('id'));">
		<center><span id="admin_edit_grades_img"><img src="images/max_button.png" class="seperator_img" /></span>
		<h2 class="seperator_h2">Προσθήκη / Επεξεργασία Βαθμολογίας</h2></center>
	</div>
	<div class="function_hidden" id="admin_edit_grades_func">
		<div class="element">
			<div class="in_element" >
			Μάθημα:<br />
			<select class="select_grade" onchange="get_labs(this.value)" id="select_class">
			  <? option_classes_enabled_only(); ?>
			</select>
			</div>
		</div>
		<div class="element">
			<div class="in_element">
			Ε/Θ:<br />
			<select class="select_grade_labs" id="select_lab" onchange="refresh_change_grade_admin();">
			  <option value="00">Επιλέξτε Μάθημα</option>
			</select>
			</div>
		</div>
		<div class="element">
			<div class="in_element">
				ΑΕΜ: 
				<input type="text" size="3" class="input_grade" id="user__aem_grade_admin_edit" onkeyup="setTimeout(refresh_change_grade_admin, 1500);" />
			</div>
		</div>
		<center><span class="show_user_name" id="show_user_name_edit_grade"></span></center>
		<div id="edit_grades_admin_window">
		</div>
		<br />
		<br />
		<div class="element_small_acp2">
			<div class="in_element_acp">
				<a class="no_color_link_in_element" href="flushgrades.php">Απομάκρυνση όλων των βαθμολογιών για<br> ένα μάθημα ή φοιτητή και σχολίων ανά <br />φοιτητή ή συνολικά</a>
			</div>
		</div>
	</div>
	
	<div class="seperator" id="admin_edit_classes" onclick="min_max_function($(this).attr('id'));">
		<center><span id="admin_edit_classes_img"><img src="images/max_button.png" class="seperator_img" /></span>
		<h2 class="seperator_h2">Προσθήκη / Επεξεργασία Μαθημάτων</h2></center>
	</div>
	<div class="function_hidden" id="admin_edit_classes_func">
		<div class="element_acp_new_class">
			<div class="in_element_acp">
				<center>
					Νέο μάθημα 
					<br />
					<table border='0' cellspacing='4px' cellpadding='4px'>
					<tr>
						<td>Όνομα μαθήματος :</td>
						<td><input type="text" name="add_new_class_input" id="add_new_class_input" /></td>
					</tr><tr>
						<td>Ενεργοποίηση μαθήματος:</td>
						<td><select class="select_grade" id="new_class_visible_select">
							<option value="1">Enabled</option>
							<option value="0">Disabled</option>
						</select></td>
					</tr><tr>
						<td>Μέγιστη βαθμολογία<br />εργαστηρίου :</td>
						<td><input type="text" size="1" name="add_new_class_maxlim_lab" id="add_new_class_maxlim_lab" /></td>
					</tr><tr>
						<td>Μέγιστη βαθμολογία<br />θεωρίας :</td>
						<td><input type="text" size="1" name="add_new_class_maxlim_theory" id="add_new_class_maxlim_theory" /></td>
					</tr><tr>
						<td>Βάση εργαστηρίου :</td>
						<td><input type="text" size="1" name="add_new_class_minlim_lab" id="add_new_class_minlim_lab" /></td>
					</tr><tr>
						<td>Βάση θεωρίας :</td>
						<td><input type="text" size="1" name="add_new_class_minlim_theory" id="add_new_class_minlim_theory" /></td>
					</tr><tr>
						<td>Βάση τελικής <br />βαθμολογίας :</td>
						<td><input type="text" size="1" name="add_new_class_minlim_total" id="add_new_class_minlim_total" /></td>
					</tr>
					</table><br />
					<input type="submit" name="add_new_class" value="OK" onclick="add_new_class($('#add_new_class_input').val());"/>
				</center><br />
			</div>
		</div>
		<div id="list_classes_admin">
			<? list_classes(); ?>
		</div>
	</div>
	
	<div class="seperator" id="admin_edit_labs" onclick="min_max_function($(this).attr('id'));">
		<center><span id="admin_edit_labs_img"><img src="images/max_button.png" class="seperator_img" /></span>
		<h2 class="seperator_h2">Προσθήκη / Επεξεργασία Ε/Θ</h2></center>
	</div>
	<div class="function_hidden" id="admin_edit_labs_func">
		<div class="element_acp_new_lab">
			<div class="in_element_acp">
				<center>
				Νέο Ε/Θ 
				<br />
				<table border='0' cellspacing='4px' cellpadding='4px'>
				<tr>
					<td>Μάθημα:</td>
					<td><select class="select_grade" id="class_select_new_lab">
						<? option_classes_enabled_only(); ?>
					</select></td>
				</tr><tr>
					<td>Όνομα:</td>
					<td><input type="text" name="add_new_lab_input" id="add_new_lab_input" /></td>
				</tr><tr>
					<td>Είδος βαθμολογίας:</td>
					<td><select class="select_lock" id="lab_select_type">
						<option value="0">Εργαστήριο</option>
						<option value="1">Θεωρία</option>
					</select></td>
				</tr><tr>
					<td>Περιλαμβάνεται <br/>στο σύνολο:</td>
					<td><select class="select_lock" id="lab_select_include_to_total">
						<option value="0">Ναι</option>
						<option value="1">Όχι</option>
					</select></td>
				</tr><tr>
					<td>Kλείδωμα Ε/Θ:</td>
					<td><select class="select_lock" id="lab_select_lock">
						<option value="0">Ανοιχτό</option>
						<option value="1">Κλειστό</option>
					</select></td>
				</tr><tr>
					<td>Συντελεστής:</td>
					<td><input type="text" size="8" name="add_new_lab_mult" id="add_new_lab_mult" /></td>
				</tr><tr>
					<td>Ημερομηνία:<br/>&nbsp;</td>
					<td><input type="text" name="add_new_lab_date" id="add_new_lab_date" /><br/>
					<span class="example_text">(π.χ. 2013-01-25)</span></td>
				</tr><tr>
					<td>Ώρα - Λεπτά:</td>
					<td><input type="text" size="1" name="add_new_lab_hour" id="add_new_lab_hour" />: <input type="text" size="1" name="add_new_lab_minute" id="add_new_lab_minute" /></td>
				</tr>
				</table>
				<input type="submit" name="add_new_lab" value="OK" onclick="add_new_lab($('#add_new_lab_input').val());"/>	
				</center>
			</div>
		</div>
		<br />
		<div id="list_labs_admin">
			<? show_labs_per_class(); ?>
		</div>
	</div>
	
</div>
<?
	include("footer.php");
 }else{
	echo "You don't have administration rights.";
 }
}else{
	echo "<script>alert('Please log in first.');</script> ";
	header ("location: ../schedule/index.php");
}

?>
