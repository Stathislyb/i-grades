<?
include("../schedule/database.php"); 
session_start();

/* Dimiourgia kai download tou csv file */
function show_grades_for_class($class_id){
	global $conn;
	$file = fopen("/tmp/csvreport.csv", "w");
	$first_loop=1;
	$total=0;
	$headers = array();
	
	
	$query_class = "SELECT * FROM iclasses WHERE id='".$class_id."' ";
	$results_class = mysql_query($query_class,$conn);
	$rows_class = mysql_fetch_array($results_class);
	
	$class[] = $rows_class['name'];
	fputcsv($file, $class); 
	
	$headers[] = "Ονοματεπώνυμο";
	$headers[] = "ΑΕΜ";
	
	$query_labs_header = "SELECT * FROM ilabs WHERE class_id='".$rows_class['id']."' ORDER BY id ASC ";
	$results_labs_header = mysql_query($query_labs_header,$conn);
		
	while ($rows_labs_header = mysql_fetch_array($results_labs_header)){
		$headers[] = $rows_labs_header['name'];
	}
	$headers[] = "Σύνολο(Scaled)";
	fputcsv($file, $headers); 
	
	$query_user = "SELECT * FROM users ORDER BY aem ASC";
	$results_user = mysql_query($query_user,$conn);
	while ($rows_user = mysql_fetch_array($results_user)){
	
		$query_grades_count = "SELECT * FROM igrades WHERE class_id='".$rows_class['id']."' AND user_id='".$rows_user['id']."' ";
		$results_grades_count = mysql_query($query_grades_count,$conn);
		if(mysql_numrows($results_grades_count) >0){
			$details= array();	
				
			$details[] = $rows_user['last_name']." ".$rows_user['first_name'];
			$details[] = $rows_user['aem'];
			
			$query_labs = "SELECT * FROM ilabs WHERE class_id='".$rows_class['id']."' ORDER BY id ASC ";
			$results_labs = mysql_query($query_labs,$conn);
			$total=0;
			$total_labs=0;
			$total_theory=0;
			while ($rows_labs = mysql_fetch_array($results_labs)){
			
				$query_grades = "SELECT * FROM igrades WHERE user_id='".$rows_user['id']."' AND lab_id='".$rows_labs['id']."' ";
				$results_grades = mysql_query($query_grades,$conn);
				$rows_grades = mysql_fetch_array($results_grades);
				
				if(isset($rows_grades['grade'])){
					$temp_real_grade=$rows_grades['grade']*$rows_labs['multiplier'];
					$real_grade=round($temp_real_grade, 2);
					$details[] = $rows_grades['grade'];
					//$real_grade;
					
					if($rows_labs['type']==0){
						$total_labs=$total_labs+$real_grade;
					}else{
						$total_theory=$total_theory+$real_grade;
					}
					
				}else{
					$details[] ='';
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
			
			$details[] = $total;	
			fputcsv($file, $details); 
			unset($details);
		}
	}

	fclose($file);
	
}
if( isset($_POST['class_id']) ){
	$class_id=mysql_escape_string(filter_var($_POST['class_id'], FILTER_SANITIZE_NUMBER_INT));
	if($class_id == $_POST['class_id']){
		show_grades_for_class($class_id);
		
		$query_class = "SELECT * FROM iclasses WHERE id='".$class_id."' ";
		$results_class = mysql_query($query_class,$conn);
		$rows_class = mysql_fetch_array($results_class);
		
		$class_name = str_replace(' ', '',$rows_class['name']);
	}
	
	$date = "_".date('d')."_".date('m')."_".date('Y');
	
	header('Content-Type: application/csv');
	header('Content-Disposition: attachment; filename=Βαθμολογία_'.$class_name.$date.'.csv');
	header('Pragma: no-cache');
	readfile("/tmp/csvreport.csv");
	
	$file = fopen("/tmp/csvreport.csv", "w");
	$empty = array();
	fputcsv($file, $empty); 
	fclose($file);

}
?>