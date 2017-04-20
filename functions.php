<?
include_once("../schedule/database.php"); 
if(!isset($_SESSION)) {
     session_start();
}


require_once("functions_admin.php");


function check_for_lock(){
	global $conn;
	$today = date("Y-m-d");
	$hour = date("H");
	$minutes = date("i");
	$lockit=0;
	
	$query_lab = "SELECT * FROM ilabs";
	$results_lab = mysql_query($query_lab,$conn);
	while($rows_lab = mysql_fetch_array($results_lab)){
		if(isset($rows_lab['lock_date']) && $rows_lab['lock']==0){
		
			$lockit=0;
			
			if($today > $rows_lab['lock_date']){
				$lockit=1;
			}elseif($today == $rows_lab['lock_date']){
				if($hour > $rows_lab['lock_hour']){
					$lockit=1;
				}elseif($hour == $rows_lab['lock_hour']){
					if($minutes > $rows_lab['lock_minutes']){
						$lockit=1;
					}
				}
			}
			
			if($lockit==1){
				$query = "UPDATE ilabs SET ilabs.lock='1' WHERE id='".$rows_lab['id']."' ";
				$results = mysql_query($query,$conn);
			}
			
		}
	}
}
check_for_lock();


function option_classes(){
	global $conn;
	
	$query = "SELECT * FROM iclasses ORDER BY id DESC ";
	$results = mysql_query($query,$conn);
	while ($rows = mysql_fetch_array($results)){
		if($rows['visible']==1 || $_SESSION['type']==1){
			echo "<option value='".$rows['id']."'>".$rows['name']."</option>";	
		}
	}
	
}



function option_classes_enabled_only(){
	global $conn;
	
	$query = "SELECT * FROM iclasses ORDER BY id ASC ";
	$results = mysql_query($query,$conn);
	while ($rows = mysql_fetch_array($results)){
		if($rows['visible']==1){
			echo "<option value='".$rows['id']."'>".$rows['name']."</option>";	
		}
	}
	
}



function select_labs($sel_class){
	global $conn;
	$query = "SELECT * FROM ilabs WHERE class_id='$sel_class' ORDER BY id ASC ";
	$results = mysql_query($query,$conn);
	echo "<option value='-1'>Παρακαλώ Επιλέξτε</option>";
	while ($rows = mysql_fetch_array($results)){
		if($rows['lock'] == 1){
			echo "<option class='locked_lab_select' value='".$rows['id']."'>".$rows['name']." (locked)</option>";	
		}else{
			echo "<option value='".$rows['id']."'>".$rows['name']."</option>";	
		}
	}
	
}

if(isset($_GET['sel_class'])){
	$sel_class=mysql_escape_string(filter_var($_GET['sel_class'], FILTER_SANITIZE_NUMBER_INT));
	select_labs($sel_class);
}



function return_grade_for_lab($user_id,$lab_id,$class_id){
	global $conn;
	$query = "SELECT * FROM igrades WHERE lab_id='$lab_id' AND user_id='$user_id' AND class_id='$class_id' ";
	$results = mysql_query($query,$conn);

	while ($rows = mysql_fetch_array($results)){
		echo $rows['grade'];
	}
	
}

if(isset($_GET['get_lab_grade'])){
	$user_id=mysql_escape_string(filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT));
	$lab_id=mysql_escape_string(filter_var($_GET['lab_id'], FILTER_SANITIZE_NUMBER_INT));
	$class_id=mysql_escape_string(filter_var($_GET['class_id'], FILTER_SANITIZE_NUMBER_INT));
	if($user_id==$_GET['user_id'] && $lab_id==$_GET['lab_id'] && $class_id==$_GET['class_id']){
		return_grade_for_lab($user_id,$lab_id,$class_id);
	}
}



function return_comment_for_lab($user_id,$lab_id,$class_id){
	global $conn;
	$query = "SELECT * FROM igrades WHERE lab_id='$lab_id' AND user_id='$user_id' AND class_id='$class_id' ";
	$results = mysql_query($query,$conn);
	while ($rows = mysql_fetch_array($results)){
		echo $rows['user_comment'];
	}
	
}

if(isset($_GET['get_lab_comment'])){
	$user_id=mysql_escape_string(filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT));
	$lab_id=mysql_escape_string(filter_var($_GET['lab_id'], FILTER_SANITIZE_NUMBER_INT));
	$class_id=mysql_escape_string(filter_var($_GET['class_id'], FILTER_SANITIZE_NUMBER_INT));
	if($user_id==$_GET['user_id'] && $lab_id==$_GET['lab_id'] && $class_id==$_GET['class_id']){
		return_comment_for_lab($user_id,$lab_id,$class_id);
	}
}



function hold_grade($class_id,$user_id,$aem)
{
	if(get_hold_status() == 1){
		global $conn;
		$query_hold="SELECT * FROM holdclass WHERE class_id='$class_id' and userid='$user_id' ";
		$results_lab = mysql_query($query_hold,$conn);


		//echo mysql_errno($conn).":".mysql_error($conn); 
		//var_dump($rows_lab);
		//var_dump($query_hold);
		//var_dump($results_lab);
		//var_dump($rows_lab);



		$rows_lab = mysql_fetch_array($results_lab);
		if ($rows_lab == FALSE) 
			{ $new=1;}
		else
			{ $new=0;}

		//var_dump($new);

		if ($new == 1 )
			{
			$query = "INSERT INTO holdclass (`class_id`,`userid`,`aem` ) VALUES ($class_id, $user_id,$aem) " ;
			$result =  mysql_query($query,$conn);
			if ( $result == TRUE ) 
				{ echo '1' ; }
			else
				{ 
				//var_dump($query);var_dump($result);
				echo '0' ; }
			}
		else
			{
			$query = "DELETE FROM `holdclass` WHERE `holdclass`.`class_id` = $class_id AND `holdclass`.`userid` = $user_id" ;
			if ( mysql_query($query,$conn) == TRUE )
				{ echo "2" ; }
			else
				{ echo "0" ; }
			}
	}else{
		echo "3";
	}
}


/* Return '1' if user wants hold value or '2' of false */
/* https://arch.icte.uowm.gr/igrades/functions.php?check_hold_grade=1&user_id=140&class_id=10 */

function check_hold_grade($class_id,$user_id)
{
global $conn;
$query_hold="SELECT * FROM holdclass WHERE class_id='$class_id' and userid='$user_id' ";
$results_lab = mysql_query($query_hold,$conn);
$rows_lab = mysql_fetch_array($results_lab);

//var_dump($results_lab);
//var_dump($query_hold);
if ($rows_lab  == FALSE)
{ echo "2";}
else
{ echo "1";}

}



/* DASYGENIS PRINT HOLDINGS */

function print_hold_grade($class_id)
{
global $conn;

$query_class = "SELECT name FROM iclasses WHERE id='".$class_id."' ";
$results_class = mysql_query($query_class,$conn);
$rows_class = mysql_fetch_array($results_class);
echo "<h3>Κρατήσεις ΑΕΜ για το μάθημα: $rows_class[name]</h3>";


$query_hold="SELECT * FROM holdclass WHERE class_id='$class_id' ";
$results = mysql_query($query_hold,$conn);

echo "<table>";

while ($row = mysql_fetch_array($results)){
      echo "<tr><td>$row[aem]</td></tr>";
	  }

echo "</table>";

}

function get_hold_status(){
	global $conn;
	$query = "SELECT * FROM professor_settings WHERE id='1' ";
	$results = mysql_query($query,$conn);
	$row = mysql_fetch_array($results);
	return $row['holdenable'];
}


function enter_new_grade($class_id,$lab_id,$user_id,$grade,$new,$comment){
	global $conn;
	
	$query_lab = "SELECT * FROM ilabs WHERE id='$lab_id' ";
	$results_lab = mysql_query($query_lab,$conn);
	$rows_lab = mysql_fetch_array($results_lab);

	$query_class="SELECT * FROM iclasses where id='$class_id'";
	$results_class = mysql_query($query_class,$conn);
	$rows_class = mysql_fetch_array($results_class);

	if ($new==1) { $old_grade==0; }
	else { 
			$query="select * from igrades WHERE class_id='".$class_id."' AND lab_id='".$lab_id."' AND user_id='".$user_id."' ";
			$result = mysql_query($query,$conn);
			$rows = mysql_fetch_array($result);
			$old_grade=$rows['grade'];
			}



	log_grade_change($rows_class['name'],$rows_lab['name'],$old_grade,$grade,$_SESSION['aem'],$_SESSION['lname'],$_SESSION['fname']);
	
	if($rows_lab['lock'] == 0){
		if($comment=='' || $comment==' '){
			$comment='';
		}
		if($new == 1){
			$query = "INSERT INTO igrades (lab_id, user_id, class_id, grade, user_comment) VALUES ($lab_id, $user_id, $class_id, $grade, '$comment') ";
			$result = mysql_query($query,$conn);
			if(!$result){
				echo "1";
			}
		}else{
			$query = "UPDATE igrades SET grade='".$grade."', user_comment='".$comment."' WHERE class_id='".$class_id."' AND lab_id='".$lab_id."' AND user_id='".$user_id."' ";
			$result = mysql_query($query,$conn);
			if(!$result){
				echo "1";
			}
		}
	}else{
		echo "3";
	}
	return;
}

function notification_mail_grade_change($user_id,$class_id,$lab_id,$grade){
	global $conn;
	
	$query_lab = "SELECT * FROM ilabs WHERE id='$lab_id' ";
	$results_lab = mysql_query($query_lab,$conn);
	$rows_lab = mysql_fetch_array($results_lab);
	$lab=$rows_lab['name'];
	if($rows_lab['lock'] == 0){
		$query_user = "SELECT * FROM users WHERE id='$user_id' ";
		$results_user = mysql_query($query_user,$conn);
		$rows_user = mysql_fetch_array($results_user);
		$aem=$rows_user['aem'];
		$fname=$rows_user['first_name'];
		$lname=$rows_user['last_name'];

		$query_class = "SELECT name FROM iclasses WHERE id='$class_id' ";
		$results_class = mysql_query($query_class,$conn);
		$rows_class = mysql_fetch_array($results_class);
		$class=$rows_class['name'];

		$staem= (string)$aem;
		$length= strlen($staem);
		if($length ==4){
		 $aemnew = $aem;
		}
		if($length ==3){
		 $aemnew = "0".$aem;
		}
		if($length ==2){
		 $aemnew = "00".$aem;
		}
		if($length ==1){
		 $aemnew = "000".$aem;
		}
		$date=date('l jS \of F Y h:i:s A');
		$crlf = chr(13) . chr(10);
		$to      = "st".$aemnew."@icte.uowm.gr ";
		$subject = "[IGrades] Grade Change Notification";
		$message = "Αυτοματοποιημένο μήνυμα i-grades".$crlf.$crlf.$crlf."Προς: [ ".$fname." ".$lname." ], AEM [".$aem."] ".$crlf."Σήμερα [ ".$date." ] τροποποιήθηκε η βαθμολογία ".$crlf."στο μάθημα [ ".$class." ] , εργαστήριο/θεωρία [ ".$lab." ] σε [ ".$grade." ],".$crlf."από τον [ ".$fname." ".$lname." ] . ".$crlf.$crlf."Μην απαντήσετε σε αυτό το email, γιατί δεν παρακολουθείται η συγκεκριμένη διεύθυνση.".$crlf.$crlf."Παρακαλώ διατηρήστε αυτό το email στο αρχείο σας έως το τέλος του εξαμήνου";
		$headers = 'From: noreply@spam.vlsi.gr'."\r\n".'Reply-To: noreply@spam.vlsi.gr'."\r\n".'Content-Type: text/plain; charset=UTF-8' . "\r\n" .'MIME-Version: 1.0' . "\r\n" .'Content-Transfer-Encoding: quoted-printable' . "\r\n" .'X-Mailer: PHP/'.phpversion();
		mail($to, $subject, $message, $headers);
	}
}

if ( isset($_GET['print_hold']) )
{
check_for_lock();
$class_id=mysql_escape_string(filter_var($_GET['class_id'], FILTER_SANITIZE_NUMBER_INT));
print_hold_grade($class_id);
}



if((isset($_GET['enter_grade']) && $_GET['enter_grade'] == 1) && $_SESSION['id']==$_GET['user_id']){
	
	check_for_lock();
	
	$class_id=mysql_escape_string(filter_var($_GET['class_id'], FILTER_SANITIZE_NUMBER_INT));
	$lab_id=mysql_escape_string(filter_var($_GET['lab_id'], FILTER_SANITIZE_NUMBER_INT));
	$user_id=mysql_escape_string(filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT));
	$comment=mysql_escape_string(filter_var($_GET['comment'], FILTER_SANITIZE_STRING));
	$checkbox=mysql_escape_string(filter_var($_GET['checkbox'], FILTER_SANITIZE_NUMBER_INT));

	if(preg_match("/^[-+]?([0-9]{1,3})?(\.[0-9]{1,2})?$/", $_GET['grade'])){
		$grade=$_GET['grade'];
	
		if($comment==$_GET['comment'] && $class_id==$_GET['class_id'] && $lab_id==$_GET['lab_id'] && $user_id==$_GET['user_id'] && $grade==$_GET['grade']){	
			
			$query_grades = "SELECT * FROM igrades WHERE user_id='".$user_id."' AND class_id='".$class_id."' AND lab_id='".$lab_id."' ";
			$results_grades = mysql_query($query_grades,$conn);
			$rows_grade = mysql_fetch_array($results_grades);
			if($rows_grade['lock_grade']==1){
				echo "4";
			}else{
				if(mysql_numrows($results_grades) == 0){
					$new=1;
					enter_new_grade($class_id,$lab_id,$user_id,$grade,$new,$comment);
					echo "1";
				}else{
					$new=0;
					enter_new_grade($class_id,$lab_id,$user_id,$grade,$new,$comment);
					echo "2";
				}

				if($checkbox==1){
					notification_mail_grade_change($user_id,$class_id,$lab_id,$grade);
				}
			}
		}else{
			echo "0";
		}
		
	}else{
		echo "Wrong grade form. Grade must be the type of NNN.NN .";
	}
}



/* DASYGENIS HOLD FUNCTION */
if  (isset($_GET['hold_grade']) && $_SESSION['id']==$_GET['user_id']){

check_for_lock();

$class_id=mysql_escape_string(filter_var($_GET['class_id'], FILTER_SANITIZE_NUMBER_INT));
$user_id=mysql_escape_string(filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT));



hold_grade($class_id,$user_id,$_SESSION['aem']);


}//end function hold




/* DASYGENIS CHECK HOLD FUNCTION */
if  (isset($_GET['check_hold_grade']) ){

check_for_lock();

$class_id=mysql_escape_string(filter_var($_GET['class_id'], FILTER_SANITIZE_NUMBER_INT));
$user_id=mysql_escape_string(filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT));


check_hold_grade($class_id,$user_id);


}//end function hold

















function flush_grades_user($class_id,$user_id){
	global $conn;
	
	$query_class = "SELECT * FROM iclasses WHERE id='".$class_id."'";
	$results_class = mysql_query($query_class,$conn);
	$row_class = mysql_fetch_array($results_class);
	
	if($row_class['visible']==1){
		$query = "DELETE FROM igrades WHERE user_id='".$user_id."' AND class_id='".$class_id."' ";
		$result = mysql_query($query,$conn);
		if(!$result){
			echo "1";
		}
	}
	return;
}

if((isset($_GET['flush_class_student']) && $_GET['flush_class_student'] == 1) && $_SESSION['id']==$_GET['user_id']){
	
	$class_id=mysql_escape_string(filter_var($_GET['class_id'], FILTER_SANITIZE_NUMBER_INT));
	$user_id=mysql_escape_string(filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT));

	if($class_id==$_GET['class_id'] && $user_id==$_GET['user_id']){	
		flush_grades_user($class_id,$user_id);
		echo "1";
	}else{
		echo "0";
	}
}



function show_grades_user($selected_class){
	global $conn;
	$create_tabs=1;
	$first_class=0;
	
	if($selected_class == -1){
		$query_class = "SELECT * FROM iclasses ORDER BY id ASC";
		$create_tabs=0;
	}else{
		$query_class = "SELECT * FROM iclasses WHERE id='".$selected_class."'";
	}
	
	$results_class = mysql_query($query_class,$conn);
	while ($rows_class = mysql_fetch_array($results_class)){
		
		$query_grades = "SELECT * FROM igrades WHERE user_id='".$_SESSION['id']."' AND class_id='".$rows_class['id']."' ORDER BY lab_id ASC ";
		$results_grades = mysql_query($query_grades,$conn);
		if(mysql_numrows($results_grades) > 0 && $first_class==0){
			$first_class=1;
			
			if($create_tabs==0){
				echo "<div id='user_classes_tabs'></div>";
			}
			echo "<div class='grades_seperation' id='user_shown_classes'>";
			echo "<div class='grades_showcase'>";
			$min_theory_msg = ($rows_class['min_theory'] != NULL ? $rows_class['min_theory'] : '5');
			$min_lab_msg = ($rows_class['min_lab'] != NULL ? $rows_class['min_lab'] : '5');
			$max_theory_msg = ($rows_class['max_theory'] != NULL ? $rows_class['max_theory'] : '5');
			$max_lab_msg = ($rows_class['max_lab'] != NULL ? $rows_class['max_lab'] : '5');
			$min_total_msg = ($rows_class['min_total'] != NULL ? $rows_class['min_total'] : '5');
			echo "<h4 class='grades_h4'>Μάθημα : ".$rows_class['name']." 
<br/><span class='normal_text'>{ Όρια προβιβασμού [ Θεωρίας: ".$min_theory_msg." , Εργαστηρίου: ".$min_lab_msg.", Μαθήματος: ".$min_total_msg." ] . Μέγιστες βαθμολογίες [ Θεωρίας: ".$max_theory_msg.", Εργαστηρίου: ".$max_lab_msg."] }</span></h4> <br/><table border='1' cellspacing='0' cellpadding='15px' class='user_grades_table'>";
			$show_headers==1;
			echo "Το ID σας στη βάση δεδομένων είναι:".$_SESSION['id']." με AEM: ".$_SESSION['aem']." και username: ".$_SESSION['username']." και ονοματεπώνυμο: ".$_SESSION['fname']." ".$_SESSION['lname']."<br/>";
			
			echo "<tr><td>Εργαστήριο </td><td>Scale<span title='Ο συντελεστής κανονικοποίησης ή αλλιώς, η βαρύτητα του εργαστηρίου.'>*</span></td><td>Βαθμός (unscaled)</td><td>Βαθμός (scaled)</td><td>Παρατηρήσεις σας</td></tr>";
			$total=0;
			$real_total=0;
			$total_theory=0;
			$total_labs=0;
			$found_lab_type=0;
			$found_theory_type=0;
			$lab_passed =0;
			$theory_passed =0;
			while ($rows_grades = mysql_fetch_array($results_grades)){
			
				$query_labs = "SELECT * FROM ilabs WHERE id='".$rows_grades['lab_id']."' ORDER BY id ASC ";
				$results_labs = mysql_query($query_labs,$conn);
				$rows_labs = mysql_fetch_array($results_labs);
				$real_grade_temp = $rows_grades['grade'] * $rows_labs['multiplier'];
				$real_grade = round($real_grade_temp,2);
				echo "<tr><td>".$rows_labs['name'];
				if($rows_labs['type']==0){
					echo "<span class='prefix_span_lab' title='Εργαστήριο'>{Ε}</span>";
				}else{
					echo "<span class='prefix_span_theory' title='Θεωρία'>{Θ}</span>";
				}
				if($rows_labs['include_total']!=0){
					echo "<span title='Δεν περιλαμβάνεται στο σύνολο'>*</span></td>";
				}
				echo "</td><td>".$rows_labs['multiplier']."</td><td>".$rows_grades['grade']."</td><td>".$real_grade."</td><td>".$rows_grades['user_comment']."</td></tr>";	
				
				if($rows_labs['include_total']==0){
					if($rows_labs['type']==0){
						$found_lab_type=1;
						$total_labs=$total_labs+$real_grade;
					}else{
						$found_theory_type=1;
						$total_theory=$total_theory+$real_grade;
					}
				}
			}
			
			// Apply limits for theory and lab totals
			if($rows_class['max_lab'] != NULL){
				if($total_labs > $rows_class['max_lab']){
					$total_labs= $rows_class['max_lab'];
				}
			}
			if($rows_class['max_theory'] != NULL){
				if($total_theory > $rows_class['max_theory']){
					$total_theory= $rows_class['max_theory'];
				}
			}
			
			// Hard limits 0 and 10 for theory and lab totals
			if($total_labs < 0 ){
				$total_labs= 0;
			}
			if($total_labs > 10 ){
				$total_labs = 10;
			}
			if($total_theory < 0){
				$total_theory= 0;
			}
			if($total_theory > 10){
				$total_theory= 10;
			}
			
			$real_total= $total_labs + $total_theory;
			// Hard limits 0 and 10 for general total
			if($real_total < 0 ){
				$real_total= 0;
			}
			if($real_total > 10 ){
				$real_total = 10;
			}
			
			if(isset($rows_class['min_total'])){
				if($real_total >= $rows_class['min_total'] && $real_total < 5){
					$real_total=5;
				}
			}
			
			// Limit to 4.9 if the student failed in theory and/or lab
			if($rows_class['min_lab'] != NULL){
				if($total_labs >= $rows_class['min_lab']){
					$lab_passed = 1;
				}
			}
			if($rows_class['min_theory'] != NULL){
				if($total_theory >= $rows_class['min_theory']){
					$theory_passed = 1;
				}
			}
			if($rows_class['min_lab']!=NULL && $rows_class['min_theory'] != NULL){
				if( ($lab_passed==0 || $theory_passed==0) && $real_total>=5){
					$real_total=4.9;
				}
			}
			
			
			echo "</table><table border='1' cellspacing='0' cellpadding='15px' class='user_grades_table'>";
			if($found_lab_type==1){
				echo "<tr><td>Σύνολο Εργαστηρίων(Scaled)</td><td>".$total_labs."</td></tr>";	
			}else{
				echo "<tr><td>Σύνολο Εργαστηρίων(Scaled)</td><td>-</td></tr>";
			}
			if($found_theory_type==1){
				echo "<tr><td>Σύνολο Θεωρίας(Scaled)</td><td>".$total_theory."</td></tr>";
			}else{
				echo "<tr><td>Σύνολο Θεωρίας(Scaled)</td><td>-</td></tr>";
			}
			echo "<tr><td>Σύνολο (scaled)</td><td>".$real_total."</td></tr></table>";
		
			echo "</div></div>";
			
		}
	}
	
}

if(isset($_GET['update_grades_showcase']) && $_GET['update_grades_showcase'] == 1){
	$class_id=mysql_escape_string(filter_var($_GET['class_id'], FILTER_SANITIZE_NUMBER_INT));
	if($class_id==$_GET['class_id']){	
		show_grades_user($class_id);
	}
}



function create_user_class_tabs($user_id){
	global $conn;
	$first_class=1;
	
	$query_class = "SELECT * FROM iclasses ORDER BY id ASC";
	$results_class = mysql_query($query_class,$conn);
	while ($rows_class = mysql_fetch_array($results_class)){
		
		$query_grades = "SELECT * FROM igrades WHERE user_id='".$user_id."' AND class_id='".$rows_class['id']."' ORDER BY lab_id ASC ";
		$results_grades = mysql_query($query_grades,$conn);
		if(mysql_numrows($results_grades) > 0 ){
			if($first_class==1){
				echo "<span class='class_tabs_user selected_tab' id='tab_class_".$rows_class['id']."' onclick='change_class_tab(".$rows_class['id'].")'>".$rows_class['name']."</span>";
				$first_class=0;
			}else{
				echo "<span class='class_tabs_user' id='tab_class_".$rows_class['id']."' onclick='change_class_tab(".$rows_class['id'].")'>".$rows_class['name']."</span>";
			}
		}
	}
	
}

if( isset($_GET['create_user_classes_tabs']) && $_GET['create_user_classes_tabs'] == 1 ){
	
	$user_id=mysql_escape_string(filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT));
	
	if($user_id==$_GET['user_id']){	
		create_user_class_tabs($user_id);
	}
}


function show_comments_user(){
	global $conn;
	
	$query_comments = "SELECT public_comment FROM users WHERE id='".$_SESSION['id']."'";
	$results_comments = mysql_query($query_comments,$conn);
	$row_comment = mysql_fetch_array($results_comments);
	if( !empty($row_comment['public_comment']) ){
		echo '<div id="announcments"><div id="header_ann"><center>Professor\'s Comment</center></div>';
		echo '<div id="content">'.$row_comment['public_comment'].'</div></div>';
	}

}

?>
