<?

if(isset($database_already_here) && $database_already_here=1){
}else{
	include("../schedule/database.php"); 
}
session_start();


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





/* Provoli vathmon ana mathima */
function show_grades_for_class_mdasyg($class_id){
	global $conn;
	$first_loop=1;
	$total=0;
	$found_lab_type=0;
	$found_theory_type=0;
	
	$query_class_labs = "SELECT ilabs.id, ilabs.name, ilabs.type AS lab_type, ilabs.include_total AS lab_include_total, iclasses.min_total, iclasses.name AS class_name, iclasses.min_theory,iclasses.min_lab,iclasses.max_theory,iclasses.max_lab FROM ilabs JOIN iclasses ON ilabs.class_id = iclasses.id WHERE ilabs.class_id=".$class_id." ORDER BY ilabs.id ASC; ";
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
			<br/><span class='normal_text'>{ Όρια προβιβασμού [ Θεωρίας: ".$min_theory_msg." , Εργαστηρίου: ".$min_lab_msg.", Μαθήματος: ".$min_total_msg." ] . Μέγιστες βαθμολογίες [ Θεωρίας: ".$max_theory_msg." , Εργαστηρίου: ".$max_lab_msg."] }</span></h4> </li>";

	echo "Αν υπάρχει <font color=red>*</font> σημαίνει ότι ο φοιτητής αιτήθηκε κράτηση βαθμού (HOLD GRADE)";
/* MINAS
// CSV export is disabled
			echo "<li><form action='csvdownload.php' method='post' target='_blank' ><input type='hidden' value=".$class_id." name='class_id'><input type='submit' value='Download CCSV' class='background_button'></form></li>";
			echo "<li><form action='classwebdownload.php' method='post' target='_blank' ><input type='hidden' value=".$class_id." name='class_id'><input type='submit' value='Classweb Export' class='background_button'></form></li><br/>";

*/
			$headers = "<li><table border='1px' cellspacing='1px' cellpadding='7px'>";
			$headers .= "<tr><td>Ονοματεπώνυμο</td><td>ΑΕΜ</td>";
		}
		if($rows_class_labs['lab_type']==0){
			$headers .= "<td><span class='prefix_span_lab' title='Εργαστήριο'>{Ε}</span>";
		}else{
			$headers .= "<td><span class='prefix_span_theory' title='Θεωρία'>{Θ}</span>";
		}
		$headers .= $rows_class_labs['name'];
		if($rows_class_labs['lab_include_total']==0){
			$headers .= "</td>";
		}else{
			$headers .= "<span title='Δεν περιλαμβάνεται στο σύνολο'>*</span></td>";
		}
		$labs_table[$i] = $rows_class_labs['id'] ;
		$i=$i+1;
	}
	
	$details="";
	$headers .= "<td>Σύνολο Εργαστηρίων(Scaled)</td>";
	$headers .= "<td>Σύνολο Θεωρίας(Scaled)</td>";
	$headers .= "<td>Σύνολο(Scaled)</td></tr>";
	echo $headers;
	
	$current_user_id = -1;
	$i=0;
	$total=0;
	$total_labs=0;
	$total_theory=0;

	$query_class = "SELECT * FROM iclasses WHERE id='".$class_id."' ";
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
	
	$query_grades = "SELECT ilabs.id AS lab_id, ilabs.multiplier, ilabs.name, ilabs.type AS lab_type, ilabs.include_total AS lab_include_total, users.id AS user_id, users.last_name, users.first_name, users.aem, igrades.id, igrades.grade, igrades.public_comment, igrades.private_comment FROM igrades JOIN ilabs ON igrades.lab_id = ilabs.id JOIN users ON igrades.user_id = users.id WHERE igrades.class_id=".$class_id." ORDER BY users.aem, igrades.lab_id ASC;";
	$results_grades = mysql_query($query_grades,$conn);
	if(mysql_numrows($results_grades) > 0){
		while ($rows_grades = mysql_fetch_array($results_grades)){
			//Αν θέλουμε μόνο για έναν φοιτητή:
			//if ( $rows_grades['aem']!=812 ) { continue; }
			if($current_user_id != $rows_grades['user_id']){
				
			 $extraholddot=grade_holded_star_note($rows_grades['user_id'],$class_id);                                                    

				//Αρχικά υπολογίζουμε τι τιμές για PASS/FAIL σε LAB/THEORY/LESSON
				if($first_loop==0){
					if($max_lab_total < $total_labs && $max_lab_total != -1){
						$total_labs=$max_lab_total;
					}
					if($max_theory_total < $total_theory && $max_theory_total != -1){
						$total_theory=$max_theory_total;
					}
					if($total_labs != 0 ||  $total_theory != 0){
						$total_labs = round($total_labs, 2);
						$total_theory = round($total_theory, 2);
						$total=$total_labs+$total_theory;
                        //ONLY FOR DEBUGGIN
						//echo $total_labs." ".$total_theory;
					}
					
					while($i <= $N){
						$details .= "<td> </td>";
						$i=$i+1;
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
					
					$details .= "<td>".$total."</td></tr>";	
					if($pass_lab == 1 && $pass_theory == 1 && $total >= $row_class['min_total']){
						echo "<tr class='grades_pass'>".$user_details.$details;
					}elseif(($pass_lab != -1 || $pass_theory != -1) && ($total < $row_class['min_total'] || ($pass_lab==0 || $pass_theory ==0) )){
						echo "<tr class='grades_fail'>".$user_details.$details;
					}else{
						echo "<tr>".$user_details.$details;
					}
				}else{
					$first_loop=0;
				}

				//Αφού έχουμε υπολογίσει τις τιμές THEORY/LAB/LESSON, μπορούμε για κάθε φοιτητή να εκτυπώσουμε κατάλληλα τα στοιχεία του
				
				$details = "";
				//$user_details= "<td>".$rows_grades['last_name']." ".$rows_grades['first_name']."</td><td>".$rows_grades['aem']."</td>";
				//Κόβουμε το όνομα να μη φαίνεται....
				$user_details= "<td>". mb_substr($rows_grades['last_name'], 0, 1, 'utf-8').". ".mb_substr($rows_grades['first_name'], 0, 1, 'utf-8').$extraholddot."</td><td>".$rows_grades['aem']."</td>";
				
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


				//Λαμβάνουμε μια γραμμή rows_grades και είτε εκτυπώνουμε τα στοιχεία που έχει (τα βαθμολογικά) είτε κενό....Για κάθε φοιτητή, αυτή
				//εδώ η γραμμή γίνεται για κάθε εργαστήριο...
			
			while( ($labs_table[$i] != $rows_grades['lab_id']) && ($i < $N)){   // prosperase ta ergastiria xoris vathous gia ton xristi
				$details .= "<td> </td>";
				$i=$i+1;
			}
			//var_dump($labs_table);echo '<br/><br/>';var_dump($rows_grades);;
			
			$temp_real_grade=$rows_grades['grade']*$rows_grades['multiplier'];
			$real_grade=round($temp_real_grade, 2);
			if(isset($rows_grades['public_comment']) || isset($rows_grades['private_comment'])){
				$details .= "<td><center><a title='comments' href='javascript:void(0);' onclick='retrive_comments(".$rows_grades['id'].");' >".$rows_grades['grade']."</a><br/>(".$real_grade.")</center></td>";
			}else{
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
		while($i <= $N){
			$details .= "<td> </td>";
			$i=$i+1;
		}
		
		if($max_lab_total < $total_labs && $max_lab_total != -1){
			$total_labs=$max_lab_total;
		}
		if($max_theory_total < $total_theory && $max_theory_total != -1){
			$total_theory=$max_theory_total;
		}
		if($total_labs != 0 ||  $total_theory != 0){
			$total_labs = round($total_labs, 2);
			$total_theory = round($total_theory, 2);
			$total=$total_labs+$total_theory;
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
		
		$details .= "<td>".$total."</td></tr>";	
		if($pass_lab == 1 && $pass_theory == 1 && $total >= $row_class['min_total']){
			echo "<tr class='grades_pass'>".$user_details.$details;
		}elseif(($pass_lab != -1 || $pass_theory != -1) && ($total < $row_class['min_total'] || ($pass_lab==0 || $pass_theory ==0) )){
			echo "<tr class='grades_fail'>".$user_details.$details;
		}else{
			echo "<tr>".$user_details.$details;
		}
		
		
	}//Τέλος, αν υπάρχουν βαθμοί...
	else{
		echo "<tr><td>Δεν υπάρχουν βαθμοί σε  αυτό το μάθημα.</td></tr>";
	}
	
	
	$sum_multiplier = "<tr><td>Συντελεστής : </td><td class='no_border_td'></td>";
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
		
		$query_grades_sum = "SELECT * FROM igrades WHERE lab_id='".$rows_labs_sum['id']."' ";
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
	echo "<tr><td class='no_border_td'> <b>Σύνοψη Ε/Θ</b> </td></tr>".$sum_multiplier."</tr>".$sum_no_grades."</tr>".$sum_average_grade."</tr>".$sum_max_grade."</tr>".$sum_min_grade."</tr>" ;
	
	echo "</table></li>";
	echo "</ul></center></div>";
}
if(isset($_GET['show_grades_class_mdasyg']) && $_GET['show_grades_class_mdasyg']==1){
	$class_id=mysql_escape_string(filter_var($_GET['class_id'], FILTER_SANITIZE_NUMBER_INT));
	if($class_id == $_GET['class_id']){
		show_grades_for_class_mdasyg($class_id);
	}
}



?>
