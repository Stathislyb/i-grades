<?php


function log_grade_change($lesson,$session,$old_grade,$new_grade,$aem,$lname,$fname){
$dddate=getdate();
$datestr="$dddate[weekday] $dddate[month] $dddate[mday] $dddate[hours]:$dddate[minutes] $dddate[year]";
$datastring="GRADECHANGE $datestr,Μάθημα:[$lesson],Session:[$session],Παλαιός Βαθμός:[$old_grade],Νέος Βαθμός:[$new_grade],AEM:[$aem],Ονοματεπώνυμο:[$fname $lname],BY==> ($_SESSION[username],$_SESSION[aem],$_SESSION[lname],$_SESSION[fname],$_SERVER[REMOTE_ADDR])".PHP_EOL;
file_put_contents("/tmp/igrades.logfile.txt", $datastring,FILE_APPEND | LOCK_EX );
}//end_function log grade change


if(isset($database_already_here) && $database_already_here=1){
}else{
	include("../schedule/database.php"); 
}
if(!isset($_SESSION)) {
     session_start();
}


/* Kanei redirect xristes xoris dikeomata admin */
// DASYGENIS, CHANGED---> just die...
if($_SESSION['type'] != 1){
return;
//header ("location: ../schedule/index.php");
}




/* Dinei ta lab options gia provoli vathmon ana ergastirio */
function select_labs_for_grade_show_admin($sel_class){
	global $conn;
	$query = "SELECT * FROM ilabs WHERE class_id='".$sel_class."' ORDER BY id ASC ";
	$results = mysql_query($query,$conn);
	echo "<option value='-1'>Όλα τα Ε/Θ.</option>";
	echo "<option value='-2'>[Σύνολο] Όλα τα Ε/Θ.</option>";
	while ($rows = mysql_fetch_array($results)){
		$count=0;
		$query_grades = "SELECT * FROM igrades WHERE lab_id='".$rows['id']."' ";
		$results_grades = mysql_query($query_grades,$conn);
		while($rows_grades = mysql_fetch_array($results_grades)){
			if(isset($rows_grades['user_comment']) && $rows_grades['user_comment']!='' && $rows_grades['user_comment']!=' '){
				$count=$count+1;
			}
		}
		
		if($count > 0){
			echo "<option value='".$rows['id']."'>".$rows['name']." (".$count.")</option>";
		}else{
			echo "<option value='".$rows['id']."'>".$rows['name']."</option>";	
		}
	}
	
}

if(isset($_GET['sel_class_for_grade_showcase'])){
	$sel_class=mysql_escape_string(filter_var($_GET['selected_class'], FILTER_SANITIZE_NUMBER_INT));
	select_labs_for_grade_show_admin($sel_class);
}


/* Add star note to inform that the grade is holded  */
function grade_holded_star_note($userid,$classid){
	global $conn;
	$query_hold="SELECT * FROM holdclass WHERE class_id='$classid' and userid='$userid' ";
	$results_lab = mysql_query($query_hold,$conn);
	$rows_lab = mysql_fetch_array($results_lab);
	//var_dump($rows_lab);
	//var_dump($query_hold);
	if ($rows_lab  == FALSE)
	{ $string="";}
	else
	{ 
        $string = " <span class='bright_red_font' title='Έχει ζητηθεί κράτηση βαθμολογίας για το συγκεριμένο φοιτητή'>*</span>";
        return $string;
	}
}


/* Add star note to inform that the column is not included in the total grade */
function not_included_in_total_star_note($string){
	$string .= " <span class='bright_red_font' title='Δε συμμετέχει στη διαμόρφωση του τελικού βαθμού γιατί έχει αντικατασταθεί από άλλη βαθμολογία.'>*</span>";
	return $string;
}

/* Provoli vathmon ana fititi */
function show_grades_for_student($user_aem, $show_disabled_classes_user){
	global $conn;
	
	$query_user = "SELECT * FROM users WHERE aem='$user_aem' ";
	$results_user = mysql_query($query_user,$conn);
	$rows_user = mysql_fetch_array($results_user);
	
	if(mysql_numrows($results_user) == 0){
		echo "<center>Δεν υπάρχει εγγεγραμμένος φοιτητής με ΑΕΜ ".$user_aem."</center>";
	}else{
		$query_class = "SELECT * FROM iclasses order by id desc";
		$results_class = mysql_query($query_class,$conn);
		echo "<div class='grades_admin_cell'><center><ul class='grades_admin_cell_ul'>";


		while ($rows_class = mysql_fetch_array($results_class)){
			$extraholddot=grade_holded_star_note($rows_user['id'],$rows_class['id']);
			if($show_disabled_classes_user==1 || $rows_class['visible']==1){
				$has_grades=0;
				$details="";
				
				$min_theory_msg = ($rows_class['min_theory'] != NULL ? $rows_class['min_theory'] : '5');
				$min_lab_msg = ($rows_class['min_lab'] != NULL ? $rows_class['min_lab'] : '5');
				$max_theory_msg = ($rows_class['max_theory'] != NULL ? $rows_class['max_theory'] : '5');
				$max_lab_msg = ($rows_class['max_lab'] != NULL ? $rows_class['max_lab'] : '5');
				$min_total_msg = ($rows_class['min_total'] != NULL ? $rows_class['min_total'] : '5');
				$headers = "<li><h4>Μάθημα: ".$rows_class['name']." (id:".$rows_class['id'].") 
	<br/><span class='normal_text'> { Όρια προβιβασμού [ Θεωρίας: ".$min_theory_msg." , Εργαστηρίου: ".$min_lab_msg.", Μαθήματος: ".$min_total_msg." ] . Μέγιστες βαθμολογίες [ Θεωρίας: ".$max_theory_msg." , Εργαστηρίου: ".$max_lab_msg."] }</span></h4> </li>";
				$headers .= "<li><table border='1px' cellspacing='1px' cellpadding='7x'>";
				$headers .= "<tr><td>Ονοματεπώνυμο</td><td>ΑΕΜ</td>";
				$details .= "<td>".$rows_user['last_name']." ".$rows_user['first_name']." ".$extraholddot."</td><td>".$rows_user['aem']."</td>";
				
				$query_labs = "SELECT * FROM ilabs WHERE class_id='".$rows_class['id']."' ORDER BY id ASC ";
				$results_labs = mysql_query($query_labs,$conn);
				$total=0;
				$total_labs=0;
				$total_theory=0;
				$found_lab_type=0;
				$found_theory_type=0;

				
				while ($rows_labs = mysql_fetch_array($results_labs)){
				
					$query_grades = "SELECT * FROM igrades WHERE user_id='".$rows_user['id']."' AND lab_id='".$rows_labs['id']."' AND class_id='".$rows_class['id']."' ";
					$results_grades = mysql_query($query_grades,$conn);
					$rows_grades = mysql_fetch_array($results_grades);
					

					if($rows_labs['type']==0){
						$headers .= "<td><span class='prefix_span_lab' title='Εργαστήριο'>{Ε}</span>";
					}else{
						$headers .= "<td><span class='prefix_span_theory' title='Θεωρία'>{Θ}</span>";
					}
					$headers .= $rows_labs['name'];
					if($rows_labs['include_total']!=0){
						$headers = not_included_in_total_star_note($headers);
					}
					$headers .= "</td>";
					if(isset($rows_grades['grade'])){
						$has_grades = $has_grades+1;
						$temp_real_grade=$rows_grades['grade']*$rows_labs['multiplier'];
						$real_grade=round($temp_real_grade, 2);
						$details .= "<td><center>".$rows_grades['grade']." (".$real_grade.")<br/><span class='timestamp_text'>(".$rows_grades['update_time'].")</span></center></td>";
						
						if($rows_labs['include_total']==0){
							if($rows_labs['type']==0){
								$found_lab_type=1;
								$total_labs=$total_labs+$real_grade;
							}else{
								$found_theory_type=1;
								$total_theory=$total_theory+$real_grade;
							}
							$total = $total + $real_grade;
						}
					}else{
						$details .= "<td> </td>";
					}
					
				}		
				
				//soft limits based on class
				if($rows_class['max_lab'] == NULL){
					$max_lab_total= -1;
				}else{
					$max_lab_total= $rows_class['max_lab'];
				}
				if($rows_class['max_theory'] == NULL){
					$max_theory_total= -1;
				}else{
					$max_theory_total= $rows_class['max_theory'];
				}
				$max_total = $max_lab_total + $max_theory_total;
				
				if($max_lab_total < $total_labs && $max_lab_total != -1){
					$total_labs=$max_lab_total;
				}
				if($max_theory_total < $total_theory && $max_theory_total != -1){
					$total_theory=$max_theory_total;
				}
				//hard limits 0 and 10 in case the class has no limits
				if($total_labs > 10) $total_labs=10;  
				if($total_labs < 0) $total_labs=0;  
				if($total_theory > 10) $total_theory=10;  
				if($total_theory < 0) $total_theory=0;  

				if($total_labs != 0 ||  $total_theory != 0){
					$total_labs = round($total_labs, 2);
					$total_theory = round($total_theory, 2);
					$total=$total_labs+$total_theory;
				}
				//hard limits 0 and 10 in case the class has no limits
				if($total > 10) $total=10;  
				if($total < 0) $total=0; 
				
				if(isset($rows_class['min_total'])){
					if($total >= $rows_class['min_total'] && $total < 5){
						$total=5;
					}
				}
				
				$headers .= "<td>Σύνολο Εργαστηρίων(Scaled)</td><td>Σύνολο Θεωρίας(Scaled)</td><td>Σύνολο(Scaled)</td></tr>";
				if($found_lab_type==1){
					if($rows_class['min_lab'] == NULL){
						$details .= "<td>".$total_labs."</td>";
						$pass_lab=-1;
					}else{
						if($total_labs < $rows_class['min_lab']){
							$details .= "<td class='grades_fail'>".$total_labs."</td>";
							$pass_lab=0;
						}else{
							$details .= "<td class='grades_pass'>".$total_labs."</td>";
							$pass_lab=1;
						}
					}
				}else{
					$details .= "<td>-</td>";
				}
				if($found_theory_type==1){
					if($rows_class['min_theory'] == NULL){
						$details .= "<td>".$total_theory."</td>";
						$pass_theory=-1;
					}else{
						if($total_theory < $rows_class['min_theory']){
							$details .= "<td class='grades_fail'>".$total_theory."</td>";
							$pass_theory=0;
						}else{
							$details .= "<td class='grades_pass'>".$total_theory."</td>";
							$pass_theory=1;
						}
					}
				}else{
					$details .= "<td>-</td>";
				}
				$details .= "<td>".$total."</td></tr>";	
				if($pass_lab == 1 && $pass_theory == 1 && $total >= $rows_class['min_total']){
					$details = "<tr class='grades_pass'>".$details;
				}elseif(($pass_lab != -1 || $pass_theory != -1) && ($total < $rows_class['min_total'] || ($pass_lab==0 || $pass_theory ==0) )){
					$details = "<tr class='grades_fail'>".$details;
				}else{
					$details = "<tr>".$details;
				}
				if($has_grades > 0){
					echo $headers.$details."</table></li>";
				}
				
			}
		}
		echo "</ul></center></div>";
	}
}
if(isset($_GET['show_grades_user']) && $_GET['show_grades_user']==1){
	$user_aem=mysql_escape_string(filter_var($_GET['user_aem'], FILTER_SANITIZE_NUMBER_INT));
	$show_disabled_classes_user=mysql_escape_string(filter_var($_GET['show_disabled_classes_user'], FILTER_SANITIZE_NUMBER_INT));
	if($user_aem == $_GET['user_aem'] && $show_disabled_classes_user == $_GET['show_disabled_classes_user']){
		show_grades_for_student($user_aem, $show_disabled_classes_user);
	}
	
}


/* Emfanisi public comments stin provoli vathmon ana foititi */
function show_public_comments_in_grades_per_user($user_id){
	global $conn;

	$query = "SELECT public_comment FROM users WHERE id='".$user_id."' ";
	$result = mysql_query($query,$conn);
	$row = mysql_fetch_array($result);
	
	echo $row['public_comment'];
}
/* Emfanisi private comments stin provoli vathmon ana foititi */
function show_private_comments_in_grades_per_user($user_id){
	global $conn;

	$query = "SELECT private_comment FROM users WHERE id='".$user_id."' ";
	$result = mysql_query($query,$conn);
	$row = mysql_fetch_array($result);
	
	echo $row['private_comment'];
}
if(isset($_GET['retrive_public_com']) && $_GET['retrive_public_com']==1){
	$user_id=mysql_escape_string(filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT));
	if($user_id == $_GET['user_id']){
		show_public_comments_in_grades_per_user($user_id);
	}
}
if(isset($_GET['retrive_private_com']) && $_GET['retrive_private_com']==1){
	$user_id=mysql_escape_string(filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT));
	if($user_id == $_GET['user_id']){
		show_private_comments_in_grades_per_user($user_id);
	}
}

/* Provoli vathmon ana mathima */
function show_grades_for_class($class_id,$total_only){
	global $conn;
	$first_loop=1;
	$total=0;
	$class_number_of_labs_total_only=0;
	$class_number_of_labs_total_only_lab=0;
	$class_number_of_labs_total_only_theory=0;
	$sum_average_grade_total_only = 0;
	$sum_max_grade_total_only= -999;
	$sum_min_grade_total_only= 999;
	$sum_average_grade_total_only_lab = 0;
	$sum_max_grade_total_only_lab= -999;
	$sum_min_grade_total_only_lab= 999;
	$sum_average_grade_total_only_theory = 0;
	$sum_max_grade_total_only_theory= -999;
	$sum_min_grade_total_only_theory= 999;
	$found_lab_type=0;
	$found_theory_type=0;
	$num_of_grades=0;
	$headers='';
	
	$query_class_labs = "SELECT ilabs.id, ilabs.name, ilabs.type AS lab_type, ilabs.include_total AS lab_include_total, iclasses.min_total, iclasses.name AS class_name, iclasses.min_theory,iclasses.min_lab,iclasses.max_theory,iclasses.max_lab FROM ilabs JOIN iclasses ON ilabs.class_id = iclasses.id WHERE ilabs.class_id=".$class_id." ORDER BY ilabs.id ASC; ";
//DASYGENIS DEBUG
//echo $query_class_labs;
	$results_class_labs = mysql_query($query_class_labs,$conn);
	$N = mysql_numrows($results_class_labs) -1 ;
	$labs_table=array();
	$i=0;
	while ($rows_class_labs = mysql_fetch_array($results_class_labs)){
		if($i==0){
			echo "<div class='grades_admin_cell'><center><ul class='grades_admin_cell_ul'>";		
			
			$min_theory_msg = ($rows_class_labs['min_theory'] != NULL ? $rows_class_labs['min_theory'] : '5');
			$min_lab_msg = ($rows_class_labs['min_lab'] != NULL ? $rows_class_labs['min_lab'] : '5');
			$max_theory_msg = ($rows_class_labs['max_theory'] != NULL ? $rows_class_labs['max_theory'] : '5');
			$max_lab_msg = ($rows_class_labs['max_lab'] != NULL ? $rows_class_labs['max_lab'] : '5');
			$min_total_msg = ($rows_class_labs['min_total'] != NULL ? $rows_class_labs['min_total'] : '5');
			echo "<li><h4 class='grades_h4'>Μάθημα : ".$rows_class_labs['class_name']." 
			<br/><span class='normal_text'>{ Όρια προβιβασμού [ Θεωρίας: ".$min_theory_msg." , Εργαστηρίου: ".$min_lab_msg.", Μαθήματος: ".$min_total_msg."] . Μέγιστες βαθμολογίες [ Θεωρίας: ".$max_theory_msg." , Εργαστηρίου: ".$max_lab_msg."] }</span></h4> </li>";
			echo "<li><form action='csvdownload.php' method='post' target='_blank' ><input type='hidden' value=".$class_id." name='class_id'><input type='submit' value='Download CSV' class='background_button'></form></li>";
			echo "<li><form action='classwebdownload.php' method='post' target='_blank' ><input type='hidden' value=".$class_id." name='class_id'><input type='hidden' value='no' name='hold'><input type='submit' value='Classweb Export' class='background_button'></form></li>";
			echo "<li><form action='classwebdownload.php' method='post' target='_blank' ><input type='hidden' value=".$class_id." name='class_id'><input type='hidden' value='yes' name='hold'><input type='submit' value='Classweb Export (HOLD)' class='background_button'></form></li><br/>";
			$headers = "<li><table border='1px' cellspacing='1px' cellpadding='7px' id='grades_for_class_table'>";
			$headers .= "<tr><td>Ονοματεπώνυμο</td><td>ΑΕΜ</td>";
		}
		if($total_only==0){
			if($rows_class_labs['lab_type']==0){
				$headers .= "<td><span class='prefix_span_lab' title='Εργαστήριο'>{Ε}</span>";
			}else{
				$headers .= "<td><span class='prefix_span_theory' title='Θεωρία'>{Θ}</span>";
			}
			$headers .= $rows_class_labs['name'];
			if($rows_class_labs['lab_include_total']!=0){
				$headers = not_included_in_total_star_note($headers);
			}
			$headers .= "</td>";
			$labs_table[$i] = $rows_class_labs['id'] ;
		}
		$i=$i+1;
	}//END for every registered class
	
	$details="";
	$headers .= "<td>Σύνολο Εργαστηρίων(Scaled)</td>";
	$headers .= "<td>Σύνολο Θεωρίας(Scaled)</td>";
	$headers .= "<td>Σύνολο(Scaled)</td>";
	$headers .= "<td>Ονοματεπώνυμο</td><td>ΑΕΜ</td></tr>";
	echo $headers;
	
	$current_user_id = -1;
	$i=0;
	$total=0;
	$total_labs=0;
	$total_theory=0;
	
	$query_class = "SELECT * FROM iclasses WHERE id='".$class_id."' ";
//DASYGENIS DEBUG
//echo $query_class;
	$results_class = mysql_query($query_class,$conn);
	$row_class = mysql_fetch_array($results_class);
	if($row_class['max_lab'] == NULL){
		$max_lab_total= -1;
	}else{
		$max_lab_total= $row_class['max_lab'];
	}
	if($row_class['max_theory'] == NULL){
		$max_theory_total= -1;
	}else{
		$max_theory_total= $row_class['max_theory'];
	}
	$max_total = $max_lab_total + $max_theory_total;

	$query_grades = "SELECT ilabs.id AS lab_id, ilabs.multiplier, ilabs.name, ilabs.type AS lab_type, ilabs.include_total AS lab_include_total, users.id AS user_id, users.last_name, users.first_name, users.aem, igrades.id, igrades.grade FROM igrades JOIN ilabs ON igrades.lab_id = ilabs.id JOIN users ON igrades.user_id = users.id WHERE igrades.class_id=".$class_id." ORDER BY users.aem, igrades.lab_id ASC;";
	$results_grades = mysql_query($query_grades,$conn);
//echo $query_grades;
//DASYGENIS DEBUG
//echo mysql_numrows($results_grades);
	if(mysql_numrows($results_grades) > 0){
		while ($rows_grades = mysql_fetch_array($results_grades)){
			$num_of_grades++;
			if($current_user_id != $rows_grades['user_id']){
//Dasygenis. Print all User IDs that have grades
//echo $current_user_id." ";
//var_dump($rows_grades);



$extraholddot=grade_holded_star_note($rows_grades['user_id'],$class_id);

				
				if($first_loop==0){
					
					if($max_lab_total < $total_labs && $max_lab_total != -1){
						$total_labs=$max_lab_total;
					}
					if($max_theory_total < $total_theory && $max_theory_total != -1){
						$total_theory=$max_theory_total;
					}
					//hard limits 0 and 10 in case the class has no limits
					if($total_labs > 10) $total_labs=10;  
					if($total_labs < 0) $total_labs=0;  
					if($total_theory > 10) $total_theory=10;  
					if($total_theory < 0) $total_theory=0;  
					
					if($total_labs != 0 ||  $total_theory != 0){
						$total_labs = round($total_labs, 2);
						$total_theory = round($total_theory, 2);
						$total=$total_labs+$total_theory;
					} 
					
					if($total_only==0){
						while($i <= $N){
							$details .= "<td> </td>";
							$i=$i+1;
						}
					}
					if($found_lab_type==1){
						if($row_class['min_lab'] == NULL){
							$details .= "<td>".$total_labs."</td>";
							$pass_lab=-1;
						}else{
							if($total_labs < $row_class['min_lab']){
								$details .= "<td class='grades_fail'>".$total_labs."</td>";
								$pass_lab=0;
							}else{
								$details .= "<td class='grades_pass'>".$total_labs."</td>";
								$pass_lab=1;
							}
						}
					}else{
						$details .= "<td>-</td>";
					}
					if($found_theory_type==1){
						if($row_class['min_theory'] == NULL){
							$details .= "<td>".$total_theory."</td>";
							$pass_theory=-1;
						}else{
							if($total_theory < $row_class['min_theory']){
								$details .= "<td class='grades_fail'>".$total_theory."</td>";
								$pass_theory=0;
							}else{
								$details .= "<td class='grades_pass'>".$total_theory."</td>";
								$pass_theory=1;
							}
						}
					}else{
						$details .= "<td>-</td>";
					}
					
					//hard limits 0 and 10 in case the class has no limits
					if($total > 10) $total=10;  
					if($total < 0) $total=0;
					
					if(isset($row_class['min_total'])){
						if($total >= $row_class['min_total'] && $total < 5){
							$total=5;
						}
					}
					
					if( ($pass_lab==0 || $pass_theory==0) && $total>=5){
						$total=4.9;
					}
					
					//DASYGENIS print of name/aem after the total column
					$details .= "<td>".$total."</td>".$user_details."</tr>";	
					if($pass_lab == 1 && $pass_theory == 1 && $total >= $row_class['min_total']){
						echo "<tr class='grades_pass_tr'><td class='hidden'>".$num_of_grades."</td>".$user_details.$details;
					}elseif(($pass_lab != -1 || $pass_theory != -1) && ($total < $row_class['min_total'] || ($pass_lab==0 || $pass_theory ==0) )){
						echo "<tr class='grades_fail_tr'><td class='hidden'>".$num_of_grades."</td>".$user_details.$details;
					}else{
						echo "<tr>".$user_details.$details;
					}
					$num_of_grades=0;
					
					if($found_lab_type==1 && $rows_grades['lab_include_total']==0){
						$class_number_of_labs_total_only_lab=$class_number_of_labs_total_only_lab+1;
					}
					if($found_theory_type==1){
						$class_number_of_labs_total_only_theory=$class_number_of_labs_total_only_theory+1;
					}
					$class_number_of_labs_total_only = $class_number_of_labs_total_only+1;
					
					if($total_only==1 && $rows_grades['lab_include_total']==0){
						$sum_average_grade_total_only = $sum_average_grade_total_only+$total;  //general total
						if(($found_lab_type==1 || $found_theory_type==1) && $sum_max_grade_total_only < $total){
							$sum_max_grade_total_only = $total;
						}
						if(($found_lab_type==1 || $found_theory_type==1) && $sum_min_grade_total_only>$total){
							$sum_min_grade_total_only = $total;
						}
						$sum_average_grade_total_only_lab = $sum_average_grade_total_only_lab+$total_labs; //labs total
						if($found_lab_type==1 && $sum_max_grade_total_only_lab < $total_labs){
							$sum_max_grade_total_only_lab = $total_labs;
						}
						if($found_lab_type==1 && $sum_min_grade_total_only_lab > $total_labs){
							$sum_min_grade_total_only_lab = $total_labs;
						}
						$sum_average_grade_total_only_theory = $sum_average_grade_total_only_theory+$total_theory; //theory total
						if($found_theory_type==1 && $sum_max_grade_total_only_theory < $total_theory){
							$sum_max_grade_total_only_theory = $total_theory;
						}
						if($found_theory_type==1 && $sum_min_grade_total_only_theory > $total_theory){
							$sum_min_grade_total_only_theory = $total_theory;
						}
					}
					
				}else{
					$first_loop=0;
				}
				
				$details = "";
				$user_details= "<td>".$rows_grades['last_name']." ".$rows_grades['first_name']."</td><td>".$rows_grades['aem']." ".$extraholddot."</td>";
				
				$current_user_id = $rows_grades['user_id'];
				$i=0;
				$total_theory=0;
				$total_labs=0;
				$total=0;
				$found_lab_type=0;
				$found_theory_type=0;
				$pass_theory=-1;
				$pass_lab=-1;
				
			}
			if($total_only==0){
				while( ($labs_table[$i] != $rows_grades['lab_id']) && ($i < $N) ){   // prosperase ta ergastiria xoris vathous gia ton xristi
					$details .= "<td> </td>";
					$i=$i+1;
				}
			}
			
			$temp_real_grade=$rows_grades['grade']*$rows_grades['multiplier'];
			$real_grade=round($temp_real_grade, 2);
			if($total_only==0){
				$details .= "<td><center>".$rows_grades['grade']."<br/>(".$real_grade.")</center></td>";
			}
			if($rows_grades['lab_include_total']==0){
				if($rows_grades['lab_type']==0){
					$found_lab_type=1;
					$total_labs=$total_labs+$real_grade;
				}else{
					$found_theory_type=1;
					$total_theory=$total_theory+$real_grade;
				}
				$total = $total + $real_grade;  
			}
			$i=$i+1;
			
		}
		if($total_only==0){
			while($i <= $N){
				$details .= "<td> </td>";
				$i=$i+1;
			}
		}
		
		if($max_lab_total < $total_labs && $max_lab_total != -1){
			$total_labs=$max_lab_total;
		}
		if($max_theory_total < $total_theory && $max_theory_total != -1){
			$total_theory=$max_theory_total;
		}
		//hard limits 0 and 10 in case the class has no limits
		if($total_labs > 10) $total_labs=10;  
		if($total_labs < 0) $total_labs=0;  
		if($total_theory > 10) $total_theory=10;  
		if($total_theory < 0) $total_theory=0;  

		if($total_labs != 0 ||  $total_theory != 0){
			$total_labs = round($total_labs, 2);
			$total_theory = round($total_theory, 2);
			$total=$total_labs+$total_theory;
		}
		//hard limits 0 and 10 in case the class has no limits 
		if($total > 10) $total=10;  
		if($total < 0) $total=0; 
		
		if(isset($row_class['min_total'])){
			if($total >= $row_class['min_total'] && $total < 5){
				$total=5;
			}
		}
		
		if($found_lab_type==1){
			if($row_class['min_lab'] == NULL){
				$details .= "<td>".$total_labs."</td>";
				$pass_lab=-1;
			}else{
				if($total_labs < $row_class['min_lab']){
					$details .= "<td class='grades_fail'>".$total_labs."</td>";
					$pass_lab=0;
				}else{
					$details .= "<td class='grades_pass'>".$total_labs."</td>";
					$pass_lab=1;
				}
			}
		}else{
			$details .= "<td>-</td>";
		}
		if($found_theory_type==1){
			if($row_class['min_theory'] == NULL){
				$details .= "<td>".$total_theory."</td>";
				$pass_theory=-1;
			}else{
				if($total_theory < $row_class['min_theory']){
					$details .= "<td class='grades_fail'>".$total_theory."</td>";
					$pass_theory=0;
				}else{
					$details .= "<td class='grades_pass'>".$total_theory."</td>";
					$pass_theory=1;
				}
			}
		}else{
			$details .= "<td>-</td>";
		}
		
		//Dasygenis, we have to put it here, otherwise the last user_detail name is ommited!
		$details .= "<td>".$total."</td>".$user_details."</tr>";	
		if($pass_lab == 1 && $pass_theory == 1 && $total >= $row_class['min_total']){
			echo "<tr class='grades_pass_tr'><td class='hidden'>".$num_of_grades."</td>".$user_details.$details;
		}elseif(($pass_lab != -1 || $pass_theory != -1) && ($total < $row_class['min_total'] || ($pass_lab==0 || $pass_theory ==0) )){
			echo "<tr class='grades_fail_tr'><td class='hidden'>".$num_of_grades."</td>".$user_details.$details;
		}else{
			echo "<tr>".$user_details.$details;
		}
		$num_of_grades=0;
		
		if($found_lab_type==1){
			$class_number_of_labs_total_only_lab=$class_number_of_labs_total_only_lab+1;
		}
		if($found_theory_type==1){
			$class_number_of_labs_total_only_theory=$class_number_of_labs_total_only_theory+1;
		}
		$class_number_of_labs_total_only = $class_number_of_labs_total_only+1;
		
		if($total_only==1 && $rows_grades['lab_include_total']==0){
			$sum_average_grade_total_only = $sum_average_grade_total_only+$total;  //general total
			if(($found_lab_type==1 || $found_theory_type==1) && $sum_max_grade_total_only < $total){
				$sum_max_grade_total_only = $total;
			}
			if(($found_lab_type==1 || $found_theory_type==1) && $sum_min_grade_total_only>$total){
				$sum_min_grade_total_only = $total;
			}
			$sum_average_grade_total_only_lab = $sum_average_grade_total_only_lab+$total_labs; //labs total
			if($found_lab_type==1 && $sum_max_grade_total_only_lab < $total_labs){
				$sum_max_grade_total_only_lab = $total_labs;
			}
			if($found_lab_type==1 && $sum_min_grade_total_only_lab > $total_labs){
				$sum_min_grade_total_only_lab = $total_labs;
			}
			$sum_average_grade_total_only_theory = $sum_average_grade_total_only_theory+$total_theory; //theory total
			if($found_theory_type==1 && $sum_max_grade_total_only_theory < $total_theory){
				$sum_max_grade_total_only_theory = $total_theory;
			}
			if($found_theory_type==1 && $sum_min_grade_total_only_theory > $total_theory){
				$sum_min_grade_total_only_theory = $total_theory;
			}
		}
	}else{
		echo "<tr><td>Δεν υπάρχουν βαθμοί σε  αυτό το μάθημα.</td></tr>";
	}

	
	

	//Dasygenis Change. Due to very big listing, we want the front row to be repeated here.
	$sum_multiplier = $headers."<tr><td>Συντελεστής : </td><td class='no_border_td'></td>";
	$sum_no_grades = "<tr><td>Αριθμός βαθμών : </td><td class='no_border_td'></td>";
	$sum_average_grade = "<tr><td>Μέσος όρος : </td><td class='no_border_td'></td>";
	$sum_max_grade = "<tr><td>Μέγιστη βαθμολογία : </td ><td class='no_border_td'></td>";
	$sum_min_grade = "<tr><td>Ελάχιστη βαθμολογία : </td><td class='no_border_td'></td>";
	
	$query_labs_sum = "SELECT * FROM ilabs WHERE class_id='".$class_id."' ORDER BY id ASC ";
	$results_labs_sum = mysql_query($query_labs_sum,$conn);
	while ($rows_labs_sum = mysql_fetch_array($results_labs_sum)){
		
		$lab_number_of_grades=0;
		$lab_max_grade=-999;
		$lab_min_grade=999;
		$lab_average_grade=0;
		$sum_multiplier .= "<td>".$rows_labs_sum['multiplier']."</td>";
		
		$query_grades_sum = "SELECT * FROM igrades WHERE lab_id='".$rows_labs_sum['id']."' AND class_id='".$class_id."' ";
		$results_grades_sum = mysql_query($query_grades_sum,$conn);
		while ($rows_grades_sum = mysql_fetch_array($results_grades_sum)){
			$lab_number_of_grades = $lab_number_of_grades+1 ;
			$lab_average_grade = $lab_average_grade + $rows_grades_sum['grade'];
			if($lab_max_grade < $rows_grades_sum['grade']){
				$lab_max_grade = $rows_grades_sum['grade'];
			}
			if($lab_min_grade > $rows_grades_sum['grade']){
				$lab_min_grade = $rows_grades_sum['grade'];
			}
		}
		if($lab_number_of_grades >0 ){
			$lab_average_grade = $lab_average_grade / $lab_number_of_grades;
			$lab_average_grade = round($lab_average_grade, 2);
		}
		
		$sum_no_grades .= "<td>".$lab_number_of_grades."</td>";
		if($lab_number_of_grades>0){
			$sum_average_grade .= "<td>".$lab_average_grade."</td>";
			$sum_max_grade .= "<td>".$lab_max_grade."</td>";
			$sum_min_grade .= "<td>".$lab_min_grade."</td>";
			
		}else{
			$sum_average_grade .= "<td>-</td>";
			$sum_max_grade .= "<td>-</td>";
			$sum_min_grade .= "<td>-</td>";
		}
	}
	if($total_only==0){
		echo "<tr><td class='no_border_td'> <b>Σύνοψη Ε/Θ</b> </td></tr>".$sum_multiplier."</tr>".$sum_no_grades."</tr>".$sum_average_grade."</tr>".$sum_max_grade."</tr>".$sum_min_grade."</tr>" ;
		echo "<tr><td>Αριθμός επιτυχιών : </td><td class='no_border_td'></td><td id='success_rate'></td></tr>";
		echo "<tr><td>Αριθμός αποτυχιών : </td><td class='no_border_td'></td><td id='fail_rate'></td></tr>";
	}else{
		echo "<tr><td class='no_border_td'> <b>Σύνοψη Μαθήματος</b> </td></tr>";
		echo "<tr><td>Μέσος όρος : ";
		if($class_number_of_labs_total_only_lab > 0){
			$sum_average_grade_total_only_lab = $sum_average_grade_total_only_lab / $class_number_of_labs_total_only_lab;
			$sum_average_grade_total_only_lab = round($sum_average_grade_total_only_lab, 2);
			echo "<td class='no_border_td'></td><td>".$sum_average_grade_total_only_lab."</td>";
		}else{
			echo "</td><td class='no_border_td'></td><td>-</td>";
		}
		if($class_number_of_labs_total_only_theory > 0){
			$sum_average_grade_total_only_theory = $sum_average_grade_total_only_theory / $class_number_of_labs_total_only_theory;
			$sum_average_grade_total_only_theory = round($sum_average_grade_total_only_theory, 2);
			echo "<td>".$sum_average_grade_total_only_theory."</td>";
		}else{
			echo "<td>-</td>";
		}
		if($class_number_of_labs_total_only > 0){
			$sum_average_grade_total_only = $sum_average_grade_total_only / $class_number_of_labs_total_only;
			$sum_average_grade_total_only = round($sum_average_grade_total_only, 2);
			echo "<td>".$sum_average_grade_total_only."</td></tr>";
		}else{
			echo "<td>-</td></tr>";
		}
		echo "<tr><td>Μέγιστη βαθμολογία : ";
		if($sum_max_grade_total_only_lab == -999){
			echo "</td><td class='no_border_td'></td><td>-</td>";
		}else{
			echo "</td><td class='no_border_td'></td><td>".$sum_max_grade_total_only_lab."</td>";
		}
		if($sum_max_grade_total_only_theory == -999){
			echo "<td>-</td>";
		}else{
			echo "<td>".$sum_max_grade_total_only_theory."</td>";
		}
		if($sum_max_grade_total_only == -999){
			echo "<td>-</td></tr>";
		}else{
			echo "<td>".$sum_max_grade_total_only."</td></tr>";
		}
		echo "<tr><td>Ελάχιστη βαθμολογία : ";
		if($sum_min_grade_total_only_lab == 999){
			echo "</td><td class='no_border_td'></td><td>-</td>";
		}else{
			echo "</td><td class='no_border_td'></td><td>".$sum_min_grade_total_only_lab."</td>";
		}
		if($sum_min_grade_total_only_theory == 999){
			echo "<td>-</td>";
		}else{
			echo "<td>".$sum_min_grade_total_only_theory."</td>";
		}
		if($sum_min_grade_total_only == 999){
			echo "<td>-</td></tr>";
		}else{
			echo "<td>".$sum_min_grade_total_only."</td></tr>";
		}
	}
	echo "</table></li>";
	echo "</ul></center></div>";
}
if(isset($_GET['show_grades_class']) && $_GET['show_grades_class']==1){
	$class_id=mysql_escape_string(filter_var($_GET['class_id'], FILTER_SANITIZE_NUMBER_INT));
	$total_only=mysql_escape_string(filter_var($_GET['total_only'], FILTER_SANITIZE_NUMBER_INT));
	if($class_id == $_GET['class_id'] && $total_only==$_GET['total_only']){
		show_grades_for_class($class_id,$total_only);
	}
}



/* Provoli vathmon ana ergastirio */
function show_grades_for_lab($lab_id,$class_id){
	global $conn;
	$first_loop=1;
	$total=0;
	
	$query_lab = "SELECT * FROM ilabs WHERE id='".$lab_id."' ";
	$results_lab = mysql_query($query_lab,$conn);
	$rows_lab = mysql_fetch_array($results_lab);
	
	$query_class = "SELECT * FROM iclasses WHERE id='".$class_id."' ";
	$results_class = mysql_query($query_class,$conn);
	$rows_class = mysql_fetch_array($results_class);
	
	echo "<div class='grades_admin_cell'><center><h4>Μάθημα: ".$rows_class['name'];	
	$min_theory_msg = ($rows_class['min_theory'] != NULL ? $rows_class['min_theory'] : '5');
	$min_lab_msg = ($rows_class['min_lab'] != NULL ? $rows_class['min_lab'] : '5');
	$max_theory_msg = ($rows_class['max_theory'] != NULL ? $rows_class['max_theory'] : '5');
	$max_lab_msg = ($rows_class['max_lab'] != NULL ? $rows_class['max_lab'] : '5');
	$min_total_msg = ($rows_class['min_total'] != NULL ? $rows_class['min_total'] : '5');
	echo "<br/><span class='normal_text'>{ Όρια προβιβασμού [ Θεωρίας: ".$min_theory_msg." , Εργαστηρίου: ".$min_lab_msg.", Μαθήματος: ".$min_total_msg." ] . Μέγιστες βαθμολογίες [ Θεωρίας: ".$max_theory_msg." , Εργαστηρίου: ".$max_lab_msg."] }</span></h4>";
	if($rows_lab['type']==0){
		echo "<br /><b>Ε/Θ: <span class='prefix_span_lab' title='Εργαστήριο'>{Ε}</span> ".$rows_lab['name']."</b><br />";
	}else{
		echo "<br /><b>Ε/Θ: <td><span class='prefix_span_theory' title='Θεωρία'>{Θ}</span>".$rows_lab['name']."</b><br />";
	}
	
	$query_grades = "SELECT * FROM igrades WHERE lab_id='".$rows_lab['id']."' AND class_id='".$class_id."'";
	$results_grades = mysql_query($query_grades,$conn);
	if(mysql_numrows($results_grades) == 0){
		echo "Δεν υπάρχουν βαθμοί σε  αυτό το Ε/Θ.</center></div>";
		return ;
	}else{
	
		$lab_number_of_grades=0;
		$lab_max_grade=-999;
		$lab_min_grade=999;
		$lab_average_grade=0;
		
		echo "<table border='1px' cellspacing='1px' cellpadding='7px'>" ;
		echo "<tr> <td>Ονοματεπώνυμο</td> <td>ΑΕΜ</td> <td>Βαθμός</td> <td>Παρατηρήσεις</td> </tr>";
		
		while($rows_grades = mysql_fetch_array($results_grades)){
		
			$lab_number_of_grades = $lab_number_of_grades+1 ;
			$lab_average_grade = $lab_average_grade + $rows_grades['grade'];
			if($lab_max_grade < $rows_grades['grade']){
				$lab_max_grade = $rows_grades['grade'];
			}
			if($lab_min_grade > $rows_grades['grade']){
				$lab_min_grade = $rows_grades['grade'];
			}
		
			echo "<tr>";
			
			$query_user = "SELECT * FROM users WHERE id='".$rows_grades['user_id']."' ";
			$results_user = mysql_query($query_user,$conn);
			$rows_user = mysql_fetch_array($results_user);
			
			echo "<td>".$rows_user['last_name']." ".$rows_user['first_name']."</td><td>".$rows_user['aem']."</td>";
						
			$temp_real_grade=$rows_grades['grade']*$rows_lab['multiplier'];
			$real_grade=round($temp_real_grade, 2);
			
			echo "<td><center>".$rows_grades['grade']."<br/>(".$real_grade.")</center></td>";

			echo "<td>".$rows_grades['user_comment']."</td>";

			echo "</tr>";
		}
	}
	echo "</table>";
	
	if($lab_number_of_grades >0 ){
		$lab_average_grade = $lab_average_grade / $lab_number_of_grades;
		$lab_average_grade = round($lab_average_grade, 2);
	}
	
	echo "<br /><b>Σύνοψη Ε/Θ</b>";
	echo "<table border='0px' cellspacing='2px' cellpadding='3px'>" ;
	echo "<tr><td>Συντελεστής : </td><td>".$rows_lab['multiplier']."</td></tr>";
	echo "<tr><td>Αριθμός βαθμών : </td><td>".$lab_number_of_grades."</td></tr>";
	echo "<tr><td>Μέσος όρος : </td><td>".$lab_average_grade."</td></tr>";
	echo "<tr><td>Μέγιστη βαθμολογία : </td><td>".$lab_max_grade."</td></tr>";
	echo "<tr><td>Ελάχιστη βαθμολογία : </td><td>".$lab_min_grade."</td></tr>";
	echo "</table></center></div>";
}
if(isset($_GET['show_grades_lab']) && $_GET['show_grades_lab']==1){
	$lab_id=mysql_escape_string(filter_var($_GET['lab_id'], FILTER_SANITIZE_NUMBER_INT));
	$class_id=mysql_escape_string(filter_var($_GET['class_id'], FILTER_SANITIZE_NUMBER_INT));
	if($class_id == $_GET['class_id'] && $lab_id == $_GET['lab_id']){
		show_grades_for_lab($lab_id,$class_id);
	}
}


/* Prosthiki mathimatos */
function add_new_class($class_name,$visible_class,$max_lab,$max_theory,$min_lab,$min_theory,$min_total){
	global $conn;
	
	if($max_lab==0){ 
		$max_lab=""; 
		$max_lab_sql="";
	}else{
		$max_lab=",".$max_lab; 
		$max_lab_sql=", max_lab";
	}
	if($max_theory==0){ 
		$max_theory=""; 
		$max_theory_sql="";
	}else{
		$max_theory=",".$max_theory; 
		$max_theory_sql=", max_theory";
	}
	if($min_lab==0){ 
		$min_lab=""; 
		$min_lab_sql="";
	}else{
		$min_lab=",".$min_lab; 
		$min_lab_sql=", min_lab";
	}
	if($min_theory==0){ 
		$min_theory=""; 
		$min_theory_sql="";
	}else{
		$min_theory=",".$min_theory; 
		$min_theory_sql=", min_theory";
	}
	if($min_total==0){ 
		$min_total=""; 
		$min_total_sql="";
	}else{
		$min_total=",".$min_total; 
		$min_total_sql=", min_total";
	}

	$query = "INSERT INTO iclasses (name, id, visible".$max_lab_sql.$max_theory_sql.$min_lab_sql.$min_theory_sql.$min_total_sql.") VALUES ('$class_name', NULL, '$visible_class'".$max_lab.$max_theory.$min_lab.$min_theory.$min_total.") ";
	return mysql_query($query,$conn);
}

if(isset($_GET['add_new_class']) && $_GET['add_new_class']==1){
	$visible_class=mysql_escape_string(filter_var($_GET['visible_class'], FILTER_SANITIZE_NUMBER_INT));
	$class_name=mysql_escape_string(filter_var($_GET['class_name'], FILTER_SANITIZE_STRING));
	$max_lab=mysql_escape_string(filter_var($_GET['max_lab'], FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION ));
	$max_theory=mysql_escape_string(filter_var($_GET['max_theory'], FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION ));
	$min_lab=mysql_escape_string(filter_var($_GET['min_lab'], FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION ));
	$min_theory=mysql_escape_string(filter_var($_GET['min_theory'], FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION ));
	$min_total=mysql_escape_string(filter_var($_GET['min_total'], FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION ));

	if($visible_class == $_GET['visible_class'] && $class_name == $_GET['class_name']  && $class_name != '' && $class_name != ' ' && $max_lab == $_GET['max_lab'] && $max_theory == $_GET['max_theory'] && $min_lab == $_GET['min_lab'] && $min_theory == $_GET['min_theory'] && $min_total == $_GET['min_total']){
		add_new_class($class_name,$visible_class,$max_lab,$max_theory,$min_lab,$min_theory,$min_total);
		echo "1";
	}else{
		echo "0";
	}
}


/* Lista mathimaton admin */
function list_classes(){
	global $conn;

	$query = "SELECT * FROM iclasses";
	$results = mysql_query($query,$conn);
	echo "<center><table border='0' cellspacing='5px' cellpadding='5px'>";
	echo "<tr><td>Μάθημα</td> <td>Ενεργοποίηση</td> <td>Μέγιστη βαθμολογία<br />εργαστηρίου</td> <td>Μέγιστη βαθμολογία<br />θεωρίας</td> <td>Βάση εργαστηρίου</td> <td>Βάση θεωρίας</td> <td>Βάση τελικής <br />βαθμολογίας</td></tr> ";
	while ($rows = mysql_fetch_array($results)){
		echo "<tr><td><span id='span_edit_class_".$rows['id']."'>".$rows['name']."</span> ";
		if($rows['visible']==1){
			echo "</td><td><span id='span_edit_class_visible_".$rows['id']."'>Enabled</span> ";
		}else{
			echo "</td><td><span id='span_edit_class_visible_".$rows['id']."'>Disabled</span> ";
		}
		echo "</td><td><span id='span_edit_class_maxlab_".$rows['id']."'>".$rows['max_lab']."</span> ";
		echo "</td><td><span id='span_edit_class_maxtheory_".$rows['id']."'>".$rows['max_theory']."</span> ";
		echo "</td><td><span id='span_edit_class_minlab_".$rows['id']."'>".$rows['min_lab']."</span> ";
		echo "</td><td><span id='span_edit_class_mintheory_".$rows['id']."'>".$rows['min_theory']."</span> ";
		echo "</td><td><span id='span_edit_class_mintotal_".$rows['id']."'>".$rows['min_total']."</span> ";
		
		echo "<input type='hidden' value='".$rows['name']."' id='class_edit_name".$rows['id']."' >";
		echo "<input type='hidden' value='".$rows['id']."' id='class_edit_id".$rows['id']."'>";
		echo "<input type='hidden' value='".$rows['visible']."' id='class_edit_visible".$rows['id']."'>";
		echo "<input type='hidden' value='".$rows['max_lab']."' id='class_edit_maxlab".$rows['id']."'>";
		echo "<input type='hidden' value='".$rows['max_theory']."' id='class_edit_maxtheory".$rows['id']."'>";
		echo "<input type='hidden' value='".$rows['min_lab']."' id='class_edit_minlab".$rows['id']."'>";
		echo "<input type='hidden' value='".$rows['min_theory']."' id='class_edit_mintheory".$rows['id']."'>";
		echo "<input type='hidden' value='".$rows['min_total']."' id='class_edit_mintotal".$rows['id']."'>";
		echo "</td><td><input type='submit' name='edit_class_".$rows['id']."' value='Edit' id='edit_class_".$rows['id']."' onclick='edit_class_change($(this));' /></td>";
		echo "</td><td><input type='submit' name='remove_class_".$rows['id']."' value='Delete' id='remove_class_".$rows['id']."' onclick='send_class_removal($(this));' /></td>";
		echo "</tr>";
	}
	echo "</table></center>";
	
}
if(isset($_GET['give_classes_list']) && $_GET['give_classes_list']==1){
	list_classes($class_name);
}


/* Diagrafi mathimatos kai oti sundeete me auto */
function remove_class($class_id){
	global $conn;
	$query_grades = "DELETE FROM igrades WHERE class_id='".$class_id."' ";
	mysql_query($query_grades,$conn);
	$query_labs = "DELETE FROM ilabs WHERE class_id='".$class_id."' ";
	mysql_query($query_labs,$conn);
	$query_class = "DELETE FROM iclasses WHERE id='".$class_id."' ";
	mysql_query($query_class,$conn);
	return;
}

if((isset($_GET['send_class_delete']) && $_GET['send_class_delete'] == 1) && $_SESSION['type']==1){
	
	$class_id=mysql_escape_string(filter_var($_GET['class_id'], FILTER_SANITIZE_NUMBER_INT));

	if($class_id==$_GET['class_id']){	
		remove_class($class_id);
		echo "1";
	}else{
		echo "0";
	}
}


/* epeksergasia mathimatos */
function edit_class($class_id, $class_name, $class_visible,$max_lab,$max_theory,$min_lab,$min_theory,$min_total){
	global $conn;
	
	if($max_lab==0){ 
		$max_lab=""; 
	}else{
		$max_lab=", max_lab='".$max_lab."'"; 
	}
	if($max_theory==0){ 
		$max_theory=""; 
	}else{
		$max_theory=", max_theory='".$max_theory."'"; 
	}
	if($min_lab==0){ 
		$min_lab=""; 
	}else{
		$min_lab=", min_lab='".$min_lab."'"; 
	}
	if($min_theory==0){ 
		$min_theory=""; 
	}else{
		$min_theory=", min_theory='".$min_theory."'"; 
	}
	if($min_total==0){ 
		$min_total="";
	}else{
		$min_total=", min_total='".$min_total."'";
	}
	
	$query_class = "UPDATE iclasses SET name='".$class_name."', visible='".$class_visible."'".$max_lab.$max_theory.$min_lab.$min_theory.$min_total." WHERE id='".$class_id."' ";
	mysql_query($query_class,$conn);
	return;
}

if((isset($_GET['send_class_edit']) && $_GET['send_class_edit'] == 1) && $_SESSION['type']==1){
	$class_visible=mysql_escape_string(filter_var($_GET['class_visible'], FILTER_SANITIZE_NUMBER_INT));
	$class_id=mysql_escape_string(filter_var($_GET['class_id'], FILTER_SANITIZE_NUMBER_INT));
	$class_name=mysql_escape_string(filter_var($_GET['class_name'],  FILTER_SANITIZE_STRING));
	$max_lab=mysql_escape_string(filter_var($_GET['class_maxlab'], FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION ));
	$max_theory=mysql_escape_string(filter_var($_GET['class_maxtheory'], FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION ));
	$min_lab=mysql_escape_string(filter_var($_GET['class_minlab'], FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION ));
	$min_theory=mysql_escape_string(filter_var($_GET['class_mintheory'], FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION ));
	$min_total=mysql_escape_string(filter_var($_GET['class_mintotal'], FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION ));
	
	if($class_id==$_GET['class_id'] && $class_visible==$_GET['class_visible'] && $class_name==$_GET['class_name']  && $class_name != '' && $class_name != ' ' && $max_lab == $_GET['class_maxlab'] && $max_theory == $_GET['class_maxtheory'] && $min_lab == $_GET['class_minlab'] && $min_theory == $_GET['class_mintheory'] && $min_total == $_GET['class_mintotal']){	
		edit_class($class_id, $class_name, $class_visible,$max_lab,$max_theory,$min_lab,$min_theory,$min_total);
		echo "1";
	}else{
		echo "0";
	}
}


/* Prosthiki ergastiriou */
function add_new_lab($lab_name, $class_id, $lock, $multiplier, $type, $include_total){
	global $conn;
	$query = "INSERT INTO ilabs (name, class_id, ilabs.lock, multiplier, type, include_total) VALUES ('$lab_name', '$class_id', '$lock', '$multiplier', '$type', '$include_total') ";
	return mysql_query($query,$conn);
}
/* Prosthiki ergastiriou me imerominia gia klidoma*/
function add_new_lab_lockable($lab_name, $class_id, $lock, $lock_date, $lock_hour, $lock_minutes, $multiplier, $type, $include_total){
	global $conn;
	$query = "INSERT INTO ilabs (name, class_id, ilabs.lock, lock_date, lock_hour, lock_minutes, multiplier, type, include_total) VALUES ('$lab_name', '$class_id', '$lock', '$lock_date', '$lock_hour', '$lock_minutes', '$multiplier', '$type', '$include_total') ";
	return mysql_query($query,$conn);
}

if(isset($_GET['add_new_lab']) && $_GET['add_new_lab']==1){
	$class_id=mysql_escape_string(filter_var($_GET['class_id'], FILTER_SANITIZE_NUMBER_INT));
	$lab_name=mysql_escape_string(filter_var($_GET['lab_name'], FILTER_SANITIZE_STRING));
	$lock=mysql_escape_string(filter_var($_GET['lock'], FILTER_SANITIZE_NUMBER_INT));
	$multiplier=mysql_escape_string(filter_var($_GET['multiplier'], FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION));
	$type=mysql_escape_string(filter_var($_GET['type'], FILTER_SANITIZE_NUMBER_INT));
	$include_total=mysql_escape_string(filter_var($_GET['include_total'], FILTER_SANITIZE_NUMBER_INT));

	if($_GET['lock_date'] != '-1'){
		if(preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$/", $_GET['lock_date'])){
			
			$lock_date=$_GET['lock_date'];
			$lock_hour=mysql_escape_string(filter_var($_GET['lock_hour'], FILTER_SANITIZE_NUMBER_INT));
			$lock_minutes=mysql_escape_string(filter_var($_GET['lock_minutes'], FILTER_SANITIZE_NUMBER_INT));
			
			if($type==$_GET['type'] && $include_total==$_GET['include_total'] && $lock == $_GET['lock'] && $lab_name == $_GET['lab_name'] && $lab_name != '' && $lab_name != ' ' && $class_id == $_GET['class_id']  && $lock_date == $_GET['lock_date'] && $lock_hour == $_GET['lock_hour'] && $lock_minutes == $_GET['lock_minutes']&& $multiplier == $_GET['multiplier']){
				add_new_lab_lockable($lab_name, $class_id, $lock, $lock_date, $lock_hour, $lock_minutes, $multiplier, $type, $include_total);
				echo "1";
			}else{
				echo "0";
			
			}
		}
	}else{
	
		if($type==$_GET['type'] && $include_total==$_GET['include_total'] && $lock == $_GET['lock'] &&$lab_name == $_GET['lab_name'] && $lab_name != '' && $lab_name != ' ' && $class_id == $_GET['class_id'] && $multiplier == $_GET['multiplier']){
			add_new_lab($lab_name, $class_id, $lock, $multiplier, $type, $include_total);
			echo "1";
		}else{
			echo "0";
		}
		
	}
}


/* Provoli ergastirion ana mathima */
function show_labs_per_class(){
	global $conn;
	
	$query_class = "SELECT * FROM iclasses";
	$results_class = mysql_query($query_class,$conn);
	
	echo "<center><ul class='grades_admin_cell_ul'>";
	
	while ($rows_class = mysql_fetch_array($results_class)){
		if($rows_class['visible']==1){
			echo "<li><h4>Μάθημα: ".$rows_class['name']."</h4>";
			echo "<table border='0' cellspacing='5px' cellpadding='5px'>";
			echo "<tr><td>Όνομα Ε/Θ</td> <td>Είδος</td> <td>Κλειδωμένο / Ανοιχτό</td> <td>Συντελεστής</td> <td>Περιλαμβάνεται <br/>στο σύνολο</td> <td>Ημερομηνία Κλείδωσης  </td> <td>Ώρα / Λεπτά Κλείδωσης </td></tr> ";
			$query_labs = "SELECT * FROM ilabs WHERE class_id='".$rows_class['id']."' ORDER BY id ASC ";
			$results_labs = mysql_query($query_labs,$conn);
			while ($rows_labs = mysql_fetch_array($results_labs)){
				echo "<tr><td><span id='span_edit_lab_".$rows_labs['id']."'>".$rows_labs['name']."</span></td> ";	
				if($rows_labs['type']==0){
					echo "<td><span id='span_edit_lab_type_".$rows_labs['id']."'>Εργαστήριο</span></td> ";
				}else{
					echo "<td><span id='span_edit_lab_type_".$rows_labs['id']."'>Θεωρία</span></td> ";
				}
				if($rows_labs['lock']==0){
					echo "<td><span id='span_edit_lab_lock_".$rows_labs['id']."'>Ανοιχτό</span></td> ";
				}else{
					echo "<td><span id='span_edit_lab_lock_".$rows_labs['id']."'>Κλειδωμένο</span></td> ";
				}
				echo "<td><span id='span_edit_lab_mult_".$rows_labs['id']."'>".$rows_labs['multiplier']."</span></td> ";
				if($rows_labs['include_total']==0){
					echo "<td><span id='span_edit_lab_inc_total_".$rows_labs['id']."'>Ναι</span></td> ";
				}else{
					echo "<td><span id='span_edit_lab_inc_total_".$rows_labs['id']."'>Όχι</span></td> ";
				}
				echo "<td><span id='span_edit_lab_date_".$rows_labs['id']."'>".$rows_labs['lock_date']."</span></td> ";
				echo "<td><span id='span_edit_lab_hour_".$rows_labs['id']."'>".$rows_labs['lock_hour']."</span> ";
				if(isset($rows_labs['lock_minutes'])){
					echo " : ";
				}
				echo "<span id='span_edit_lab_minutes_".$rows_labs['id']."'>".$rows_labs['lock_minutes']."</span> ";
				
				echo "<input type='hidden' value='".$rows_labs['name']."' id='lab_edit_name".$rows_labs['id']."' >";
				echo "<input type='hidden' value='".$rows_labs['type']."' id='lab_edit_type".$rows_labs['id']."' >";
				echo "<input type='hidden' value='".$rows_labs['lock']."' id='lab_edit_lock".$rows_labs['id']."' >";
				echo "<input type='hidden' value='".$rows_labs['lock_date']."' id='lab_edit_date".$rows_labs['id']."' >";
				echo "<input type='hidden' value='".$rows_labs['lock_hour']."' id='lab_edit_hour".$rows_labs['id']."' >";
				echo "<input type='hidden' value='".$rows_labs['lock_minutes']."' id='lab_edit_minute".$rows_labs['id']."' >";
				echo "<input type='hidden' value='".$rows_labs['multiplier']."' id='lab_edit_mult".$rows_labs['id']."' >";
				echo "<input type='hidden' value='".$rows_labs['include_total']."' id='lab_edit_inc_total".$rows_labs['id']."' >";
				echo "<input type='hidden' value='".$rows_labs['id']."' id='lab_edit_id".$rows_labs['id']."'>";
				echo "</td><td><input type='submit' name='edit_lab_".$rows_labs['id']."' value='Edit' id='edit_lab_".$rows_labs['id']."' onclick='edit_lab_change($(this));' /></td>";
				echo "</td><td><input type='submit' name='remove_lab_".$rows_labs['id']."' value='Delete' id='remove_lab_".$rows_labs['id']."' onclick='send_lab_removal($(this));' /></td>";
				echo "</tr>";		
			}
			echo "</table></li>";
		}
	}

	echo "</ul></center>";
}
if(isset($_GET['show_admin_labs']) && $_GET['show_admin_labs']==1){
	show_labs_per_class($class_id);
} 


/* Diagrafi mathimatos kai oti sundeete me auto */
function remove_lab($lab_id){
	global $conn;
	$query_grades = "DELETE FROM igrades WHERE lab_id='".$lab_id."' ";
	mysql_query($query_grades,$conn);
	$query_labs = "DELETE FROM ilabs WHERE id='".$lab_id."' ";
	mysql_query($query_labs,$conn);
	return;
}

if((isset($_GET['send_lab_delete']) && $_GET['send_lab_delete'] == 1) && $_SESSION['type']==1){
	
	$lab_id=mysql_escape_string(filter_var($_GET['lab_id'], FILTER_SANITIZE_NUMBER_INT));

	if($lab_id==$_GET['lab_id']){	
		remove_lab($lab_id);
		echo "1";
	}else{
		echo "0";
	}
}


/* epeksergasia ergastiriou */
function edit_lab($lab_name, $lab_id, $lock, $multiplier, $type, $inc_total){
	global $conn;
	$query_lab = "UPDATE ilabs SET name='".$lab_name."', multiplier='".$multiplier."', type='".$type."', include_total='".$inc_total."', ilabs.lock='".$lock."' WHERE id='".$lab_id."' ";
	mysql_query($query_lab,$conn);
	return;
}
/* epeksergasia ergastiriou me lock date*/
function edit_lab_lockable($lab_name, $lab_id, $lock, $lock_date, $lock_hour, $lock_minutes, $multiplier, $type, $inc_total){
	global $conn;
	$query_lab = "UPDATE ilabs SET name='".$lab_name."', multiplier='".$multiplier."', type='".$type."', include_total='".$inc_total."', ilabs.lock=".$lock.", lock_date='".$lock_date."', lock_hour=".$lock_hour.", lock_minutes=".$lock_minutes." WHERE id='".$lab_id."' ";
	mysql_query($query_lab,$conn);
	return;
}

if((isset($_GET['send_lab_edit']) && $_GET['send_lab_edit'] == 1) && $_SESSION['type']==1){
	$lab_id=mysql_escape_string(filter_var($_GET['lab_id'], FILTER_SANITIZE_NUMBER_INT));
	$lab_name=mysql_escape_string(filter_var($_GET['lab_name'],  FILTER_SANITIZE_STRING));
	$lock=mysql_escape_string(filter_var($_GET['lock'], FILTER_SANITIZE_NUMBER_INT));
	$multiplier=mysql_escape_string(filter_var($_GET['multiplier'], FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION));
	$type=mysql_escape_string(filter_var($_GET['type'], FILTER_SANITIZE_NUMBER_INT));
	$inc_total=mysql_escape_string(filter_var($_GET['inc_total'], FILTER_SANITIZE_NUMBER_INT));
	
	if($_GET['lock_date'] != '-1'){
		if(preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$/", $_GET['lock_date'])){
		
			$lock_date=$_GET['lock_date'];
			$lock_hour=mysql_escape_string(filter_var($_GET['lock_hour'], FILTER_SANITIZE_NUMBER_INT));
			$lock_minutes=mysql_escape_string(filter_var($_GET['lock_minutes'], FILTER_SANITIZE_NUMBER_INT));
			
			if($type==$_GET['type'] && $inc_total==$_GET['inc_total'] && $lock == $_GET['lock'] && $lab_name == $_GET['lab_name'] && $lab_name != '' && $lab_name != ' ' && $lab_id == $_GET['lab_id']  && $lock_date == $_GET['lock_date'] && $lock_hour == $_GET['lock_hour'] && $lock_minutes == $_GET['lock_minutes'] && $multiplier == $_GET['multiplier']){
				edit_lab_lockable($lab_name, $lab_id, $lock, $lock_date, $lock_hour, $lock_minutes, $multiplier, $type, $inc_total);
				echo "1";
			}else{
				echo "0";
			}
		}
	}else{
	
		if($type==$_GET['type'] && $inc_total==$_GET['inc_total'] && $lock == $_GET['lock'] &&$lab_name == $_GET['lab_name'] && $lab_name != '' && $lab_name != ' ' && $lab_id == $_GET['lab_id'] && $multiplier == $_GET['multiplier']){
			edit_lab($lab_name, $lab_id, $lock, $multiplier, $type, $inc_total);
			echo "1";
		}else{
			echo "0";
		}
		
	}
}


/* emfanisi epeksergasias vathmou */
function show_grade_for_edit($lab_id, $class_id, $user_aem){
	global $conn;
	$query_user = "SELECT * FROM users WHERE aem='".$user_aem."' ";
	$results_user = mysql_query($query_user,$conn);
	$rows_user = mysql_fetch_array($results_user);
	
	$query_grade = "SELECT * FROM igrades WHERE class_id='".$class_id."' AND lab_id='".$lab_id."' AND user_id='".$rows_user['id']."' ";
	$results_grade = mysql_query($query_grade,$conn);
	$rows_grade = mysql_fetch_array($results_grade);
	
	echo "<br /><div class='element_edit_grade_admin'>";
	echo "<div id='edit_public_comment_area'><div class='edit_com_header'>Public Comment</div><div class='edit_com_container'><textarea id='public_com_input'>";
	if(isset($rows_user['public_comment'])){
		echo $rows_user['public_comment'];
	}
	echo "</textarea><br/><input type='button' value='Save Comment' onclick='send_public_com_admin(".$rows_user['id'].");' /></div></div>";
	echo "<div id='edit_private_comment_area'><div class='edit_com_header'>Private Comment</div><div class='edit_com_container'><textarea id='private_com_input'>";
	if(isset($rows_user['private_comment'])){
		echo $rows_user['private_comment'];
	}
	echo "</textarea><br/><input type='button' value='Save Comment' onclick='send_private_com_admin(".$rows_user['id'].");' /></div></div>";
	echo "<div onclick=\"$('#edit_public_comment_area').toggle();\" class='public_comment_edit_button' id='public_com_link' title='Public Comment'></div>";
	echo "<div onclick=\"$('#edit_private_comment_area').toggle();\" class='private_comment_edit_button' id='private_com_link' title='Private Comment'></div>";
	echo "<div class='in_element_less_top'>Βαθμός : <br />";
	
	if(mysql_numrows($results_grade) == 0){
		echo "<input type='text' size='3' class='input_grade' id='enter_new_grade' value='N/A ' /> ";
	}else{
		echo "<input type='text' size='3' class='input_grade' id='enter_new_grade' value='".$rows_grade['grade']."' /> ";
	}
	echo "<span class='example_text_admin'>(π.χ. 25)</span><br />";
	echo "<span class='example_text'>Αφήστε κενό για διαγραφή βαθμού.</span><br/>";
	
	if($rows_grade['lock_grade']==0){
		echo "<select class='input_grade' id='enter_new_grade_lock_option'><option value='0'>Unlocked</option><option value='1'>Locked</option><select><br />";
	}else{
		echo "<select class='input_grade' id='enter_new_grade_lock_option'><option value='1'>Locked</option><option value='0'>Unlocked</option><select><br />";
	}
	echo "<br/><span class='checkbox_small_text'><input id='notify_grade_change_admin' type='checkbox' name='notify_grade_change_admin' value='1' checked>Notify student via email</span><br/><br/>";
	echo "<input type='submit' class='input_grade' name='enter_grade' value='OK' onclick='send_new_grade_admin(".$rows_user['id'].");'  />";
	echo "</div></div>";
	
	return;
}

if((isset($_GET['select_grade_admin']) && $_GET['select_grade_admin'] == 1) && $_SESSION['type']==1){
	$lab_id=mysql_escape_string(filter_var($_GET['lab_id'], FILTER_SANITIZE_NUMBER_INT));
	$user_aem=mysql_escape_string(filter_var($_GET['user_aem'], FILTER_SANITIZE_NUMBER_INT));
	$class_id=mysql_escape_string(filter_var($_GET['class_id'], FILTER_SANITIZE_NUMBER_INT));
	
	if($lab_id==$_GET['lab_id'] && $class_id==$_GET['class_id']  && $user_aem==$_GET['user_aem']){	
		show_grade_for_edit($lab_id, $class_id, $user_aem);
	}
	
}


/* epeksergasia vathmou */
function enter_new_grade_admin($class_id,$lab_id,$user_id,$grade,$action,$lock){
	global $conn;
	if($action==1){
		$query = "INSERT INTO igrades (lab_id, user_id, class_id, grade, lock_grade) VALUES ($lab_id, $user_id, $class_id, $grade, $lock) ";
	}elseif($action==3){
		$query = "DELETE FROM igrades WHERE lab_id='".$lab_id."' AND user_id='".$user_id."' AND class_id='".$class_id."' ";
	}else{
		$query = "UPDATE igrades SET grade='".$grade."', lock_grade='".$lock."' WHERE class_id='".$class_id."' AND lab_id='".$lab_id."' AND user_id='".$user_id."' ";
	}
	$result = mysql_query($query,$conn);
	if(!$result){
		echo "Ο βαθμός δεν καταχωρήθηκε λόγο λάθους κατά την διαδικασία αποθήκευσης.";
	}
	return;
}
/* epeksergasia public comment */
function update_public_com_admin($user_id,$public_com){
	global $conn;
	$query_comment=" SELECT public_comment FROM users WHERE id='".$user_id."' ";
	$results_comment = mysql_query($query_comment,$conn);
	$rows_comment = mysql_fetch_array($results_comment);
	
	if($rows_comment['public_comment'] != $public_com){
		if( $public_com != '' && $public_com != ' '){
			$query = "UPDATE users SET public_comment='".$public_com."' WHERE id='".$user_id."' ";
		}else{
			$query = "UPDATE users SET public_comment=NULL WHERE id='".$user_id."' ";
			echo "1";
		}
		mysql_query($query,$conn);
	}
	return;
}
/* epeksergasia private comment */
function update_private_com_admin($user_id,$private_com){
	global $conn;
	$query_comment=" SELECT private_comment FROM users WHERE id='".$user_id."' ";
	$results_comment = mysql_query($query_comment,$conn);
	$rows_comment = mysql_fetch_array($results_comment);
	
	if($rows_comment['private_comment'] != $private_com){
		if( $private_com != '' && $private_com != ' '){
			$query = "UPDATE users SET private_comment='".$private_com."' WHERE id='".$user_id."' ";
		}else{
			$query = "UPDATE users SET private_comment=NULL WHERE id='".$user_id."' ";
			echo "1";
		}
		mysql_query($query,$conn);
	}
	return;
}
/* apostoli e-mail enimerosis */
function notification_mail_grade_change_admin($user_id,$class_id,$lab_id,$grade,$old_grade){
	global $conn;
	
	$query_user = "SELECT * FROM users WHERE id='$user_id' ";
	$results_user = mysql_query($query_user,$conn);
	$rows_user = mysql_fetch_array($results_user);
	$aem=$rows_user['aem'];
	$lname=$rows_user['last_name'];
	$fname=$rows_user['first_name'];

	$query_class = "SELECT name FROM iclasses WHERE id='$class_id' ";
	$results_class = mysql_query($query_class,$conn);
	$rows_class = mysql_fetch_array($results_class);
	$class=$rows_class['name'];

	$query_lab = "SELECT name FROM ilabs WHERE id='$lab_id' ";
	$results_lab = mysql_query($query_lab,$conn);
	$rows_lab = mysql_fetch_array($results_lab);
	$lab=$rows_lab['name'];

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
	$message = "Αυτοματοποιημένο μήνυμα i-grades ".$crlf.$crlf.$crlf."Προς: [ ".$fname." ".$lname." ], AEM [".$aem."] ".$crlf."Σήμερα [ ".$date." ] τροποποιήθηκε η βαθμολογία ".$crlf."στο μάθημα [ ".$class." ] , εργαστήριο/θεωρία [ ".$lab." ] σε [ ".$grade." ] (προηγούμενη βαθμολογία: ".$old_grade."),".$crlf."από τον [ Διαχειριστή/Διδάσκοντα ] . ".$crlf.$crlf."Μην απαντήσετε σε αυτό το email, γιατί δεν παρακολουθείται η συγκεκριμένη διεύθυνση.".$crlf.$crlf."Παρακαλώ διατηρήστε αυτό το email στο αρχείο σας έως το τέλος του εξαμήνου";
	$headers = 'From: noreply@spam.vlsi.gr'."\r\n".'Reply-To: noreply@spam.vlsi.gr'."\r\n".'Content-Type: text/plain; charset=UTF-8' . "\r\n" .'MIME-Version: 1.0' . "\r\n" .'Content-Transfer-Encoding: quoted-printable' . "\r\n" .'X-Mailer: PHP/'.phpversion();
	mail($to, $subject, $message, $headers);
}

if((isset($_GET['enter_change_grade']) && $_GET['enter_change_grade'] == 1) && $_SESSION['type']==1){
	global $conn;
	
	$class_id=mysql_escape_string(filter_var($_GET['class_id'], FILTER_SANITIZE_NUMBER_INT));
	$lab_id=mysql_escape_string(filter_var($_GET['lab_id'], FILTER_SANITIZE_NUMBER_INT));
	$user_id=mysql_escape_string(filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT));
	$lock=mysql_escape_string(filter_var($_GET['lock'], FILTER_SANITIZE_NUMBER_INT));
	$checkbox=mysql_escape_string(filter_var($_GET['checkbox'], FILTER_SANITIZE_NUMBER_INT));
	
	
	$_GET['grade'] = preg_replace('/\s+/', '', $_GET['grade']); // trim spaces
	$temp_grade = rtrim($_GET['grade']); // empty() can't get the return of a function directly so I use this var instead.
										// note for rtrim, it ignores zeros(0)
	
	$query_user=" SELECT aem,first_name,last_name FROM users WHERE id='".$user_id."'";
	$result_user = mysql_query($query_user,$conn);
	$row_user = mysql_fetch_array($result_user);
	//$query_class=" SELECT name FROM iclasses WHERE id='".$class_id."'";
	//DASYGENIS include also session
	$query_class=" select iclasses.name,ilabs.name as session  from iclasses left join ilabs on iclasses.id=ilabs.class_id where ilabs.id='".$lab_id."'";
	$result_class = mysql_query($query_class,$conn);
	$row_class = mysql_fetch_array($result_class);

//include_once("http://arch.icte.uowm.gr/post.php.inc");savemsg(json_encode($query_class));
//include_once("http://arch.icte.uowm.gr/post.php.inc");savemsg(json_encode($row_class));
		
	if(preg_match("/^[-+]?([0-9]{1,3})?(\.[0-9]{1,2})?$/", $_GET['grade']) || empty($temp_grade) ){
		$grade=$_GET['grade'];
	}else{
		echo "Wrong grade form. Grade must be the type of NNN.NN .";
	}
	if($_GET['lock']==$lock && $class_id==$_GET['class_id'] && $lab_id==$_GET['lab_id'] && $user_id==$_GET['user_id'] && $grade==$_GET['grade']){	
		
		$query_grades = "SELECT * FROM igrades WHERE user_id='".$user_id."' AND class_id='".$class_id."' AND lab_id='".$lab_id."' ";
		$results_grades = mysql_query($query_grades,$conn);
		//include_once("http://arch.icte.uowm.gr/post.php.inc");savemsg(json_encode($query_grades));
		if(mysql_numrows($results_grades) == 0){//αρχη - αν δεν υπάρχει βαθμολογία για το μάθημα
			if(!empty($temp_grade) || $temp_grade=='0'){
				$action=1;
				enter_new_grade_admin($class_id,$lab_id,$user_id,$grade,$action,$lock);
				echo "Ο βαθμός ".$temp_grade." προστέθηκε στο φοιτητή ".$row_user['last_name']." ".$row_user['first_name']." με ΑΕΜ ".$row_user['aem']." με επιτυχία για το μάθημα ".$row_class['name'].".";
			}
			if($checkbox==1){
				notification_mail_grade_change_admin($user_id,$class_id,$lab_id,$grade,"0");
			}


		log_grade_change($row_class['name'],$row_class['session'],"0",$temp_grade,$row_user['aem'],$row_user['last_name'],$row_user['first_name']);


		}//τέλος - αν δεν υπάρχει βαθμολογία για το μάθημα
		else{//αρχή - υπάρχει βαθμολογία
			$row_grades=mysql_fetch_array($results_grades);



			if(!empty($temp_grade) || $temp_grade=='0'){
				$action=2;
				enter_new_grade_admin($class_id,$lab_id,$user_id,$grade,$action,$lock);
				echo "Ο βαθμός ".$temp_grade." τροποποιήθηκε στο φοιτητή ".$row_user['last_name']." ".$row_user['first_name']." με ΑΕΜ ".$row_user['aem']." με επιτυχία για το μάθημα ".$row_class['name'].".";
			}else{
				$action=3;
				enter_new_grade_admin($class_id,$lab_id,$user_id,$grade,$action,$lock);
				echo "Ο βαθμός ".$temp_grade." διαγράφηκε στο φοιτητή ".$row_user['last_name']." ".$row_user['first_name']." με ΑΕΜ ".$row_user['aem']." με επιτυχία για το μάθημα ".$row_class['name'].".";
			
			}
			if($checkbox==1){
				notification_mail_grade_change_admin($user_id,$class_id,$lab_id,$grade,$row_grades['grade']);
			}

		//include_once("http://arch.icte.uowm.gr/post.php.inc");savemsg(json_encode($results_grades));
		log_grade_change($row_class['name'],$row_class['session'],$row_grades['grade'],$temp_grade,$row_user['aem'],$row_user['last_name'],$row_user['first_name']);
		}//τέλος - δεν υπάρχει βαθμολογία
		
	}else{
		echo "Ο βαθμός δεν καταχωρήθηκε.<br/> Σιγουρευτείτε ότι δώσατε σωστά τον βαθμό.";
	}
}//end - enter change grade



if((isset($_GET['enter_change_public_com']) && $_GET['enter_change_public_com'] == 1) && $_SESSION['type']==1){
	
	$user_id=mysql_escape_string(filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT));
	$public_com = $_GET['public_com'];
	
	if($user_id==$_GET['user_id']){	
		update_public_com_admin($user_id,$public_com);
		echo "1";

	}else{
		echo "0";
	}
} // end change public comment



if((isset($_GET['enter_change_private_com']) && $_GET['enter_change_private_com'] == 1) && $_SESSION['type']==1){
	
	$user_id=mysql_escape_string(filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT));
	$private_com = $_GET['private_com'];
	
	if($user_id==$_GET['user_id']){	
		update_private_com_admin($user_id,$private_com);
		echo "1";
	}else{
		echo "0";
	}
} // start change public comment


/* Flush vathmon gia mathima */
function flush_grades_class_admin($class_id){
	global $conn;
	
	$query = "DELETE FROM igrades WHERE class_id='".$class_id."' ";
	return mysql_query($query,$conn);
}

if((isset($_GET['flush_class_all']) && $_GET['flush_class_all'] == 1) && $_SESSION['type']==1){
	
	$class_id=mysql_escape_string(filter_var($_GET['class_id'], FILTER_SANITIZE_NUMBER_INT));

	if($class_id==$_GET['class_id']){	
		flush_grades_class_admin($class_id);
		echo "1";
	}else{
		echo "0";
	}
}


/* Flush vathmon gia user */
function flush_grades_user_admin($user_aem){
	global $conn;
	
	$query_user = "SELECT * FROM users WHERE aem='".$user_aem."' ";
	$results_user = mysql_query($query_user,$conn);
	$rows_user = mysql_fetch_array($results_user);
	
	$query = "DELETE FROM igrades WHERE user_id='".$rows_user['id']."' ";
	return mysql_query($query,$conn);
}

if((isset($_GET['flush_student_all']) && $_GET['flush_student_all'] == 1) && $_SESSION['type']==1){
	
	$user_aem=mysql_escape_string(filter_var($_GET['user_aem'], FILTER_SANITIZE_NUMBER_INT));

	if($user_aem==$_GET['user_aem']){	
		flush_grades_user_admin($user_aem);
		echo "1";
	}else{
		echo "0";
	}
}



/* Flush all comments */
function flush_all_comments_admin($type){
	global $conn;

	if($type==0){
		$query = "UPDATE users SET private_comment=NULL, public_comment=NULL ";
	}elseif($type==1){
		$query = "UPDATE users SET private_comment=NULL ";
	}elseif($type==2){
		$query = "UPDATE users SET public_comment=NULL ";
	}
	return mysql_query($query,$conn);
}

if((isset($_GET['flush_all_comments']) && $_GET['flush_all_comments'] == 1) && $_SESSION['type']==1){
	$type=mysql_escape_string(filter_var($_GET['type'], FILTER_SANITIZE_NUMBER_INT));
	if($type==$_GET['type']){
		flush_all_comments_admin($type);
	}
	echo "1";
}



/* Flush comments gia user */
function flush_comments_user_admin($user_aem,$type){
	global $conn;
	
	if($type==0){
		$query = "UPDATE users SET private_comment=NULL, public_comment=NULL WHERE aem='".$user_aem."' ";
	}elseif($type==1){
		$query = "UPDATE users SET private_comment=NULL WHERE aem='".$user_aem."' ";
	}elseif($type==2){
		$query = "UPDATE users SET public_comment=NULL WHERE aem='".$user_aem."' ";
	}
	
	return mysql_query($query,$conn);
}

if((isset($_GET['flush_student_all_comments']) && $_GET['flush_student_all_comments'] == 1) && $_SESSION['type']==1){
	
	$user_aem=mysql_escape_string(filter_var($_GET['user_aem'], FILTER_SANITIZE_NUMBER_INT));
	$type=mysql_escape_string(filter_var($_GET['type'], FILTER_SANITIZE_NUMBER_INT));

	if($user_aem==$_GET['user_aem'] && $type==$_GET['type']){	
		flush_comments_user_admin($user_aem,$type);
		echo "1";
	}else{
		echo "0";
	}
}



/* vriskei ton user apo to aem tou */
function find_user_by_aem($user_aem){
	global $conn;
	
	$query_user = "SELECT * FROM users WHERE aem='".$user_aem."' ";
	$results_user = mysql_query($query_user,$conn);
	$rows_user = mysql_fetch_array($results_user);
	
	echo $rows_user['last_name']." ".$rows_user['first_name'];
}

if((isset($_GET['find_user_by_aem']) && $_GET['find_user_by_aem'] == 1) && $_SESSION['type']==1){
	
	$user_aem=mysql_escape_string(filter_var($_GET['user_aem'], FILTER_SANITIZE_NUMBER_INT));

	if($user_aem==$_GET['user_aem']){	
		find_user_by_aem($user_aem);
	}
}



/* Energopoih / Apenergopoihei to HOLD gia ton etoumeno kathigiti */
function toggle_holdgrades($prof_id){
	global $conn;
	
	$query_user = "SELECT * FROM professor_settings WHERE professor_id='".$prof_id."' ";
	$results_user = mysql_query($query_user,$conn);
	$row = mysql_fetch_array($results_user);
	$new_holdenable = ($row['holdenable']==1)?0:1;
	
	$query_user = "UPDATE professor_settings SET holdenable = '".$new_holdenable."' WHERE professor_id = '".$prof_id."' ";
	$results_user = mysql_query($query_user,$conn);
	
}

if(isset($_POST['hold_grade_toggle'])){
	toggle_holdgrades($_SESSION['id']);
}
?>
