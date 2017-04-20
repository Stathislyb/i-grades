
/* Ajax gia provoli vathmon me vasi to mathima */
function show_grades_for_class_mdasyg(class_id) {
	if(class_id != -1){
        $.ajax({
			type: "GET",
			url: "functions_user_with_admin_show.php",
			data: "class_id=" + class_id + "&show_grades_class_mdasyg=1",
			dataType: "html",
			success: function(html){
                $("#show_grades_admin").html(html);
				var grades_for_class_table = document.getElementById('grades_for_class_table');
				var rowLength = grades_for_class_table.rows.length;
				var max_grades=-1;
				for(var i=1; i<rowLength-8; i+=1){
					var row = grades_for_class_table.rows[i];	
					if(i==1){
						cells_grades= row.cells[0].innerHTML-1;
					}else if(i == (rowLength-2)){
						cells_grades= row.cells[0].innerHTML+1;
					}else{
						cells_grades= row.cells[0].innerHTML;
					}
					
					if(max_grades < cells_grades){
						max_grades=cells_grades;
					}
				}

				var limit_grades =30*max_grades / 100;
				for(var i=1; i<rowLength-8; i+=1){
					var row = grades_for_class_table.rows[i];
					if(i==1){
						cells_grades= row.cells[0].innerHTML-1;
					}else if(i == (rowLength-2)){
						cells_grades= row.cells[0].innerHTML+1;
					}else{
						cells_grades= row.cells[0].innerHTML;
					}
					
					if(limit_grades > cells_grades){
						row.className = "";
						row.cells[row.cells.length - 2].className = "";
						row.cells[row.cells.length - 3].className = "";
					}
				}
				var success_count = $("#grades_for_class_table .grades_pass_tr").length;
				var fail_count = $("#grades_for_class_table .grades_fail_tr").length;
				var total_grades = success_count + fail_count;
				if(total_grades==0){
					var success_rate = 0;
					var fail_rate = 0;
				}else{
					var success_rate = (100*success_count / total_grades).toFixed(2);
					var fail_rate = (100*fail_count / total_grades).toFixed(2);
				}
				$("#success_rate").html(success_count+" ( "+success_rate+"% )");
				$("#fail_rate").html(fail_count+" ( "+fail_rate+"% )");
			}
        });
	}
}


