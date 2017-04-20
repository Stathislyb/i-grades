<?
include("../schedule/database.php"); 
session_start();

/* Dimiourgia kai download tou csv file */
function show_grades_for_class($class_id, $hold){
	global $conn;
	$file = fopen("/tmp/classweb_report.txt", "w");
	
	
	$query_class = "SELECT * FROM iclasses WHERE id='".$class_id."' ";
	$results_class = mysql_query($query_class,$conn);
	$rows_class = mysql_fetch_array($results_class);
	
	$class[] = $rows_class['name'];
	$header= "ΑΕΜ\t";
	$header.= "Ονοματεπώνυμο\t";
	$header.= "Πατρώνυμο\tΠερ.Εγγραφής\tΕξαμ.Δηλ.\tΚατάσταση\tΣύν. Απουσιών\tΚατάσταση δήλωσης\tΒαθμός\r\n";
	fwrite($file,$header);
	
	$total=0;
	$total_labs=0;
	$total_theory=0;
	$lab_passed =0;
	$theory_passed =0;
	$first_loop=1;
	$current_user_id=-3;
	$skip_hold=0;

	$query_grades = "SELECT ilabs.id AS lab_id, ilabs.multiplier, ilabs.name, ilabs.type AS lab_type, ilabs.include_total AS lab_include_total, users.id AS user_id, users.last_name, users.first_name, users.aem, igrades.id, igrades.grade, igrades.public_comment, igrades.private_comment FROM igrades JOIN ilabs ON igrades.lab_id = ilabs.id JOIN users ON igrades.user_id = users.id WHERE igrades.class_id=".$class_id." ORDER BY users.aem, igrades.lab_id ASC;";

	$results_grades = mysql_query($query_grades,$conn);
	if(mysql_numrows($results_grades) > 0){
		while ($rows_grades = mysql_fetch_array($results_grades)){
			if($hold=="yes" && ( $current_user_id != $rows_grades['user_id'] ) ){
				$query_hold = "SELECT COUNT(*) FROM holdclass WHERE class_id='".$class_id."' AND userid='".$rows_grades['user_id']."'";
				$results_hold = mysql_query($query_hold,$conn);
				$result_hold = mysql_fetch_array($results_hold);
				if($result_hold['COUNT(*)'] > 0){
					$skip_hold=1;
				}else{
					$skip_hold=0;
				}
			}
			if($skip_hold==0){
				if($current_user_id != $rows_grades['user_id']){
					
					if($first_loop==0){
						
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
						
						
						$total= $total_labs + $total_theory;			
						// Hard limits 0 and 10 for general total
						if($total < 0 ){
							$total= 0;
						}
						if($total > 10 ){
							$total = 10;
						}
						
						if(isset($rows_class['min_total'])){
							if($total >= $rows_class['min_total'] && $total < 5){
								$total=5;
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
							if( ($lab_passed==0 || $theory_passed==0) && $total>=5){
								$total=4.9;
							}
						}
						
						//Ceil total at one demical
						//  Ceil doesn't leave demicals so we multiply by 10 before and divide by 10 after
						//  the ceiling to keep one demical
						$total = ( ceil($total*10) ) / 10;
						
						//Replace . with , for demicals
						$total_str = str_replace('.', ',', $total);
						
						// Write in file if the student has a total more than 3
						$student_line .= "\t\t\t\t\t\t".$total_str."\r\n";
						if($total >= 3 ){
							fwrite($file,$student_line);
						}

						$total=0;
						$total_labs=0;
						$total_theory=0;
						$lab_passed =0;
						$theory_passed =0;
					}else{
						$first_loop=0;
					}
					$student_line = $rows_grades['aem']."\t\t";
					$current_user_id = $rows_grades['user_id'];
				}
				
				if(isset($rows_grades['grade'])){
					$temp_real_grade=$rows_grades['grade']*$rows_grades['multiplier'];
					
					$real_grade=round($temp_real_grade, 2);
					//$real_grade;

					if($rows_grades['lab_type']==0){
						$total_labs=$total_labs+$real_grade;
					}else{
						$total_theory=$total_theory+$real_grade;
					}
					
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
		
		
		$total= $total_labs + $total_theory;			
		// Hard limits 0 and 10 for general total
		if($total < 0 ){
			$total= 0;
		}
		if($total > 10 ){
			$total = 10;
		}
		
		if(isset($rows_class['min_total'])){
			if($total >= $rows_class['min_total'] && $total < 5){
				$total=5;
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
			if( ($lab_passed==0 || $theory_passed==0) && $total>=5){
				$total=4.9;
			}
		}
		
		//Ceil total at one demical
		//  Ceil doesn't leave demicals so we multiply by 10 before and divide by 10 after
		//  the ceiling to keep one demical
		$total = ( ceil($total*10) ) / 10;
		
		//Replace . with , for demicals
		$total_str = str_replace('.', ',', $total);
		
		// Write in file if the student has a total more than 3
		$student_line .= "\t\t\t\t\t\t".$total_str."\r\n";
		if($total >= 3 ){
			fwrite($file,$student_line);
		}

		$total=0;
		$total_labs=0;
		$total_theory=0;
		$lab_passed =0;
		$theory_passed =0;
	}
		
	fclose($file);
	
}

if( isset($_POST['class_id']) ){
	$class_id=mysql_escape_string(filter_var($_POST['class_id'], FILTER_SANITIZE_NUMBER_INT));
	$hold=mysql_escape_string(filter_var($_POST['hold'], FILTER_SANITIZE_STRING));
	if($class_id == $_POST['class_id'] && $hold==$_POST['hold']){
		show_grades_for_class($class_id, $hold);
		
		$query_class = "SELECT * FROM iclasses WHERE id='".$class_id."' ";
		$results_class = mysql_query($query_class,$conn);
		$rows_class = mysql_fetch_array($results_class);
		
		$class_name = str_replace(' ', '',$rows_class['name']);
	}
	
	$date = "_".date('d')."_".date('m')."_".date('Y');
	
	header('Content-Disposition: attachment; filename=Βαθμολογία_'.$class_name.$date.'.txt');
	header('Content-Type: text/plain');
	header('Pragma: no-cache');
	readfile("/tmp/classweb_report.txt");
	
	$file = fopen("/tmp/classweb_report.txt", "w");
	$empty = array();
	fputcsv($file, $empty); 
	fclose($file);

}
?>