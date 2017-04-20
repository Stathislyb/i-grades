
/* Functions for user */


/* Show/Hide comments apo to menu twn comments */
function toggle_comment_show(comment) {
var total = $("#total_comments").val();
for(var i = 0; i <= total; i++){
	if(i==comment){
		$("#ann_"+i).removeClass().addClass('ann_show');
	}else{
		$("#ann_"+i).removeClass().addClass('ann_hidden');
	}
} 
}


/* Show next or previous 5 comments sto menu twn comments */
function prevornext_five_comments(multiplier) {
var pixels = multiplier*20;
$("#ann_list_ul").css('margin-top', '-'+pixels+'px'); 
}


/* Dinei ta labs afou fortosei i selida gia to proepilegmeno mathima */
$(document).ready( function() {
if ($('#select_class').length) {
	var value = $("#select_class").val();
	get_labs(value) ;
}
});


/* Dinei ta labs gia to mathima pou epilexthike */
function get_labs(selected_class) {
$.ajax({
type: "GET",
url: "functions.php",
data: "sel_class=" + selected_class,
dataType: "html",
success: function(html){       $("#select_lab").html(html);     }
}); 
}


/* Dinei ton vathmo kai sxolia gia to ergastirio pou epilexthike */
function get_lab_details(lab_id,user_id){
	class_val = $("#select_class").val();
	$.ajax({
		type: "GET",
		url: "functions.php",
		data: "lab_id=" + lab_id +"&user_id="+user_id+"&class_id="+class_val+"&get_lab_grade=1",
		dataType: "html",
		success: function(html){
			   $("#enter_new_grade").val(html);     
		}
	}); 
	
	$.ajax({
		type: "GET",
		url: "functions.php",
		data: "lab_id=" + lab_id +"&user_id="+user_id+"&class_id="+class_val+"&get_lab_comment=1",
		dataType: "html",
		success: function(html){
			   $("#enter_new_comment").val(html);     
		}
	}); 

}


/* Ananeonei tin provoli vathmon meta apo alages */
function update_showcase_grades(){
	$.ajax({
		type: "GET",
		url: "functions.php",
		data: "update_grades_showcase=1&class_id=-1",
		dataType: "html",
		success: function(html){    
			$("#view_grades_list").html(html);
		}
	});
	var user = $("#user_id_for_js").val();
	$.ajax({
		type: "GET",
		url: "functions.php",
		data: "user_id=" + user + "&create_user_classes_tabs=1",
		dataType: "html",
		success: function(html){ 
			$("#user_classes_tabs").html(html);
		}
	}); 
}


/* Stelnei tin isagogi vathmou */
function send_new_grade(user_id) {
var class_id = $("#select_class").val();
var lab_id = $("#select_lab").val();
var grade = $("#enter_new_grade").val();
var comment = $("#enter_new_comment").val();
var checkbox;
if($('#notify_grade_change').prop('checked')){
	checkbox=1;
}else{
	checkbox=0;
}

if(lab_id == -1){
	$("#notification").html('Παρακαλώ επιλέξτε εργαστήριο.');
	$("#pop_up").show().delay(5000).fadeOut();
}else{

	$.ajax({
	type: "GET",
	url: "functions.php",
	data: "class_id=" + class_id + "&lab_id=" + lab_id +"&user_id=" + user_id +"&grade=" + grade +"&comment=" + comment + "&checkbox=" + checkbox + "&enter_grade=1",
	dataType: "html",
	success: function(html){      
		if(html==1){
			$("#notification").html('Ο βαθμός καταχωρήθηκε με επιτυχία.');
		}else if(html==2){
			$("#notification").html('Ο βαθμός τροποποιήθηκε με επιτυχία.');
		}else if(html==0){
			$("#notification").html('Ο βαθμός δεν καταχωρήθηκε.<br/> Σιγουρευτείτε ότι δώσατε σωστά τον βαθμό.');
		}else if(html==31 || html==32){
			$("#notification").html('Το εργαστήριο αυτό είναι κλειδωμένο και δεν δέχεται νέους βαθμούς.');
		}else if(html==11 || html==12){
			$("#notification").html('Ο βαθμός δεν καταχωρήθηκε λόγο λάθους κατά την διαδικασία αποθήκευσης.');
		}else if(html==4){
			$("#notification").html('Ο συγκεκριμένος βαθμός έχει κλειδωθεί από τον διαχειριστή και δεν μπορείτε να τον τροποποιήσετε.');
		}else{
			$("#notification").html(html);
		}
		$("#pop_up").show().delay(5000).fadeOut();
	}
	});
	
}
}




function print_hold_listing()
{
var class_id = $("#select_class").val();

        $.ajax({
        type: "GET",
        url: "functions.php",
        data: "class_id=" + class_id +  "&print_hold=1",
        dataType: "html",
        success: function(html){
                if(html==1){
                        $("#notification").html('<h1>Ο βαθμός κρατήθηκε με επιτυχία.</h1>');
                }else if(html==2){
                        $("#notification").html('<h1>Ο βαθμός δεν είναι πια ΚΡΑΤΗΜΕΝΟΣ.</h1>');
                }else if(html==0){
                        $("#notification").html('<h1>Ο βαθμός δε μπόρεσε να γίνει HOLD.</h1><br/><h1>Συνέβηκε κάποιο σφάλμα.</h1>');
                }else{
                        $("#notification").html(html);
                }
                $("#pop_up").show().delay(10000).fadeOut();
        }
        });



}






/* HOLD GRADE */
function hold_grade(user_id) {
var class_id = $("#select_class").val();

        $.ajax({
        type: "GET",
        url: "functions.php",
        data: "class_id=" + class_id + "&user_id=" + user_id +  "&hold_grade=1",
        dataType: "html",
        success: function(html){
                if(html==1){
                        $("#notification").html('<h1>Ο βαθμός κρατήθηκε με επιτυχία.</h1>');
                }else if(html==2){
                        $("#notification").html('<h1>Ο βαθμός δεν είναι πια ΚΡΑΤΗΜΕΝΟΣ.</h1> ');
                }else if(html==3){
                        $("#notification").html('<h1>Το HOLD δεν επιτρέπεται αυτή την στιγμή.</h1>');
                }else if(html==0){
                        $("#notification").html('<h1>Ο βαθμός δε μπόρεσε να γίνει HOLD.</h1><br/><h1>Συνέβηκε κάποιο σφάλμα.</h1>');
                }else{
                        $("#notification").html(html);
                }
                $("#pop_up").show().delay(10000).fadeOut();
        }
        });

}

/* CHECK HOLD GRADE */
function check_hold_grade(user_id) {
var class_id = $("#select_class").val();

        $.ajax({
        type: "GET",
        url: "functions.php",
        data: "class_id=" + class_id + "&user_id=" + user_id +  "&check_hold_grade=1",
        dataType: "html",
        success: function(html){
                if(html==1){
                        $("#notification").html('<h1>Ο βαθμός για το μάθημα έχει σημειωθεί για κράτηση.</h1>');
                }else if(html==2){
                        $("#notification").html('<h1>Ο βαθμός δεν έχει σημειωθεί για κράτηση.</h1>');
                }else if(html==0){
                        $("#notification").html('<h1>Συνέβηκε κάποιο σφάλμα.</h1>');
                }else{
                        $("#notification").html(html);
                }
                $("#pop_up").show().delay(10000).fadeOut();
        }
        });


}



/* Stelnei to mathima pros ekatharisi vathmon tou user */
function send_flush_grades_user(user_id) {
var class_id = $("#class_flushing").val();
var class_name = $("#class_flushing option:selected").html();
$.ajax({
type: "GET",
url: "functions.php",
data: "class_id=" + class_id + "&user_id=" + user_id +"&flush_class_student=1",
dataType: "html",
success: function(html){    
	if(html==1){
		$("#notification").html('Οι βαθμοί σας διαγράφηκαν  με επιτυχία από το μάθημα ' + class_name);
	}else{
		$("#notification").html('Οι βαθμοί σας δεν μπόρεσαν να διαγραφούν από το μάθημα ' + class_name);
	}
	$("#pop_up").show().delay(5000).fadeOut();
}
}); 
}


/* Kanei minimize i maximize ta divs functions analoga me ton seperator pou patithike */
function min_max_function(seperator){ 
	if($("#"+seperator+"_func").attr("class")=="function"){
		$("#"+seperator+"_func").removeClass('function').addClass('function_hidden');
		$("#"+seperator+"_img").html('<img src="images/max_button.png" class="seperator_img" />');
	}else{
		$("#"+seperator+"_func").removeClass('function_hidden').addClass('function');
		$("#"+seperator+"_img").html('<img src="images/min_button.png" class="seperator_img" />');
	}
}


/* Functions gia admin */



/* Dinei ta labs gia to mathima pou epilexthike ston admin gia provoli vathmon*/
function get_labs_grades_show(selected_class) {
	$.ajax({
		type: "GET",
		url: "functions_admin.php",
		data: "selected_class=" + selected_class+"&sel_class_for_grade_showcase=1",
		dataType: "html",
		success: function(html){
			   $("#show_lab_select").html(html);     
		}
	}); 
}



/* Ajax gia provoli vathmon me vasi to mathima */
function show_grades_for_class(class_id) {
if(class_id != -1){
	$.ajax({
	type: "GET",
	url: "functions_admin.php",
	data: "class_id=" + class_id + "&total_only=0" + "&show_grades_class=1",
	dataType: "html",
	success: function(html){  
		$("#show_grades_admin").html(html);
		var grades_for_class_table = document.getElementById('grades_for_class_table');
		var rowLength = grades_for_class_table.rows.length;
		var max_grades=-1;
		for(var i=1; i<rowLength-1; i+=1){
			var row = grades_for_class_table.rows[i];	
			if(max_grades < row.cells[0].innerHTML){
				max_grades=row.cells[0].innerHTML;
			}
		}
		var limit_grades =30*max_grades / 100;
		for(var i=1; i<rowLength-1; i+=1){
			var row = grades_for_class_table.rows[i];
			
			if(i==1){
				if(limit_grades > (row.cells[0].innerHTML-1)){
					row.className = "";
					row.cells[row.cells.length - 4].className = "";
					row.cells[row.cells.length - 5].className = "";
				}
			}else if(i == (rowLength-2)){
				if(limit_grades > (row.cells[0].innerHTML+1)){
					row.className = "";
					row.cells[row.cells.length - 4].className = "";
					row.cells[row.cells.length - 5].className = "";
				}
			}else{
				if(limit_grades > row.cells[0].innerHTML){
					row.className = "";
					row.cells[row.cells.length - 4].className = "";
					row.cells[row.cells.length - 5].className = "";
				}
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



/* Ajax gia provoli vathmon me vasi to ergastirio */
function show_grades_for_lab(lab_id) {
	var class_id = $("#class_select").val();
	if(lab_id == -1){
		show_grades_for_class(class_id);
	}else if(lab_id == -2){
		$.ajax({
			type: "GET",
			url: "functions_admin.php",
			data: "class_id=" + class_id + "&total_only=1" + "&show_grades_class=1",
			dataType: "html",
			success: function(html){  
				$("#show_grades_admin").html(html);
			}
		});
	}else{
		$.ajax({
			type: "GET",
			url: "functions_admin.php",
			data: "lab_id=" + lab_id +"&class_id=" + class_id + "&show_grades_lab=1",
			dataType: "html",
			success: function(html){  
				$("#show_grades_admin").html(html);
			}
		}); 
	}
}


/* Ajax gia provoli vathmon me vasi ton foititi */
function show_grades_for_user() {
var user_aem = $("#get_aem_show_grades").val();
var show_all;
if( $('#show_disabled_classes_for_user').prop('checked') ){
	show_all=1;
}else{
	show_all=0;
}
$.ajax({
type: "GET",
url: "functions_admin.php",
data: "user_aem=" + user_aem + "&show_disabled_classes_user=" + show_all+ "&show_grades_user=1",
dataType: "html",
success: function(html){  
	$("#show_grades_admin").html(html);
}
}); 
}


/* Emfanise epiloges gia anazitisi vathmon */
function choose_class_show(){
	$("#choose_class").css("display","block");
	$("#choose_user").css("display","none");
}
function choose_user_show(){
	$("#choose_user").css("display","block");
	$("#choose_class").css("display","none");
}


/* Ajax gia dimiourgia mathimatos */
function add_new_class(class_name) {
var visible = $("#new_class_visible_select").val();
var max_lab = $("#add_new_class_maxlim_lab").val();
var max_theory = $("#add_new_class_maxlim_theory").val();
var min_lab = $("#add_new_class_minlim_lab").val();
var min_theory = $("#add_new_class_minlim_theory").val();
var min_total = $("#add_new_class_minlim_total").val();

$.ajax({
type: "GET",
url: "functions_admin.php",
data: "class_name=" + class_name + "&visible_class=" + visible + "&max_lab=" + max_lab + "&max_theory=" + max_theory + "&min_lab=" + min_lab + "&min_theory=" + min_theory + "&min_total=" + min_total + "&add_new_class=1",
dataType: "html",
success: function(html){  
	if(html==1){
		$("#notification").html('Το μάθημα ' + class_name + ' προστέθηκε με επιτυχία .');
	}else{
		$("#notification").html('Η πρόσθεση του μαθήματος ' + class_name + ' απέτυχε .');
	}
	$("#pop_up").show().delay(5000).fadeOut();
}
}); 
}


/* Allakse to class se input gia edit */
var timer_edit_class=[];
function edit_class_change(clicked_edit){
	var class_id_temp = clicked_edit.attr('id');
	var class_id = class_id_temp.replace('edit_class_','');
	var class_name = $("#class_edit_name"+class_id).val();
	var class_visible = $("#class_edit_visible"+class_id).val();
	var class_maxlab = $("#class_edit_maxlab"+class_id).val();
	var class_maxtheory = $("#class_edit_maxtheory"+class_id).val();
	var class_minlab = $("#class_edit_minlab"+class_id).val();
	var class_mintheory = $("#class_edit_mintheory"+class_id).val();
	var class_mintotal = $("#class_edit_mintotal"+class_id).val();
	
	var html_to_give_name = '<input type="text" id="input_class_edit_'+class_id+'" value="'+class_name+'" />';
	var html_to_give_visible;
	if(class_visible == 1){
		html_to_give_visible = '<select id="select_class_edit_visible_'+class_id+'"><option value="1" selected>Enabled</option><option value="0">Disabled</option></select>';
	}else{
		html_to_give_visible = '<select id="select_class_edit_visible_'+class_id+'"><option value="1">Enabled</option><option value="0" selected>Disabled</option></select>';
	}
	var html_to_give_maxlab = '<input size="1" type="text" id="input_class_edit_maxlab_'+class_id+'" value="'+class_maxlab+'" />';
	var html_to_give_maxtheory = '<input size="1" type="text" id="input_class_edit_maxtheory_'+class_id+'" value="'+class_maxtheory+'" />';
	var html_to_give_minlab = '<input size="1" type="text" id="input_class_edit_minlab_'+class_id+'" value="'+class_minlab+'" />';
	var html_to_give_mintheory = '<input size="1" type="text" id="input_class_edit_mintheory_'+class_id+'" value="'+class_mintheory+'" />';
	var html_to_give_mintotal = '<input size="1" type="text" id="input_class_edit_mintotal_'+class_id+'" value="'+class_mintotal+'" />';
	
	if(clicked_edit.val() == 'Edit'){
		$("#span_edit_class_"+class_id).html(html_to_give_name);
		$("#span_edit_class_visible_"+class_id).html(html_to_give_visible);
		$("#span_edit_class_maxlab_"+class_id).html(html_to_give_maxlab);
		$("#span_edit_class_maxtheory_"+class_id).html(html_to_give_maxtheory);
		$("#span_edit_class_minlab_"+class_id).html(html_to_give_minlab);
		$("#span_edit_class_mintheory_"+class_id).html(html_to_give_mintheory);
		$("#span_edit_class_mintotal_"+class_id).html(html_to_give_mintotal);
		timer_edit_class[class_id] = setTimeout(function() {
			$("#span_edit_class_"+class_id).html(class_name);
			if(class_visible==1){
				$("#span_edit_class_visible_"+class_id).html('Enabled');
			}else{
				$("#span_edit_class_visible_"+class_id).html('Disabled');
			}
			$("#span_edit_class_maxlab_"+class_id).html(class_maxlab);
			$("#span_edit_class_maxtheory_"+class_id).html(class_maxtheory);
			$("#span_edit_class_minlab_"+class_id).html(class_minlab);
			$("#span_edit_class_mintheory_"+class_id).html(class_mintheory);
			$("#span_edit_class_mintotal_"+class_id).html(class_mintotal);
			clicked_edit.val('Edit');
		}, 20000);
		clicked_edit.val('Done');
		
	}else{
		clearTimeout(timer_edit_class[class_id]);
		class_old_name=class_name;
		class_old_visible=class_visible;
		class_old_maxlab=class_maxlab;
		class_old_maxtheory=class_maxtheory;
		class_old_minlab=class_minlab;
		class_old_mintheory=class_mintheory;
		class_old_mintotal=class_mintotal;
		class_name = $("#input_class_edit_"+class_id).val();
		class_visible = $("#select_class_edit_visible_"+class_id).val();
		class_maxlab = $("#input_class_edit_maxlab_"+class_id).val();
		class_maxtheory = $("#input_class_edit_maxtheory_"+class_id).val();
		class_minlab = $("#input_class_edit_minlab_"+class_id).val();
		class_mintheory = $("#input_class_edit_mintheory_"+class_id).val();
		class_mintotal = $("#input_class_edit_mintotal_"+class_id).val();
		clicked_edit.val('Edit');
		send_class_edit(class_id,class_name,class_old_name,class_visible,class_old_visible,class_old_maxlab,class_maxlab,class_old_maxtheory,class_maxtheory,class_old_minlab,class_minlab,class_old_mintheory,class_mintheory,class_old_mintotal,class_mintotal);
	}
}


/* Ajax gia edit class */
function send_class_edit(class_id,class_name,class_old_name,class_visible,class_old_visible,class_old_maxlab,class_maxlab,class_old_maxtheory,class_maxtheory,class_old_minlab,class_minlab,class_old_mintheory,class_mintheory,class_old_mintotal,class_mintotal){
$.ajax({
type: "GET",
url: "functions_admin.php",
data: "class_id=" + class_id + "&class_name=" + class_name + "&class_visible=" + class_visible + "&class_maxlab=" + class_maxlab + "&class_maxtheory=" + class_maxtheory + "&class_minlab=" + class_minlab + "&class_mintheory=" + class_mintheory + "&class_mintotal=" + class_mintotal + "&send_class_edit=1",
dataType: "html",
success: function(html){ 
	if(html==1){
		$("#notification").html('Το μάθημα άλλαξε σε ' + class_name + ' επιτυχώς .');
		$("#span_edit_class_"+class_id).html(class_name);
		$("#class_edit_name"+class_id).val(class_name);
		$("#span_edit_class_maxlab_"+class_id).html(class_maxlab);
		$("#class_edit_maxlab"+class_id).val(class_maxlab);
		$("#span_edit_class_maxtheory_"+class_id).html(class_maxtheory);
		$("#class_edit_maxtheory"+class_id).val(class_maxtheory);
		$("#span_edit_class_minlab_"+class_id).html(class_minlab);
		$("#class_edit_minlab"+class_id).val(class_minlab);
		$("#span_edit_class_mintheory_"+class_id).html(class_mintheory);
		$("#class_edit_mintheory"+class_id).val(class_mintheory);
		$("#span_edit_class_mintotal_"+class_id).html(class_mintotal);
		$("#class_edit_mintotal"+class_id).val(class_mintotal);
		if(class_visible==1){
			$("#span_edit_class_visible_"+class_id).html('Enabled');
		}else{
			$("#span_edit_class_visible_"+class_id).html('Disabled');
		}
		$("#class_edit_visible"+class_id).val(class_visible);
	}else{
		$("#notification").html('Η αλλαγή του μαθήματος σε ' + class_name + ' απέτυχε .');
		$("#span_edit_class_"+class_id).html(class_old_name);
		$("#class_edit_name"+class_id).val(class_old_name);
		$("#span_edit_class_maxlab_"+class_id).html(class_old_maxlab);
		$("#class_edit_maxlab"+class_id).val(class_old_maxlab);
		$("#span_edit_class_maxtheory_"+class_id).html(class_old_maxtheory);
		$("#class_edit_maxtheory"+class_id).val(class_old_maxtheory);
		$("#span_edit_class_minlab_"+class_id).html(class_old_minlab);
		$("#class_edit_minlab"+class_id).val(class_old_minlab);
		$("#span_edit_class_mintheory_"+class_id).html(class_old_mintheory);
		$("#class_edit_mintheory"+class_id).val(class_old_mintheory);
		$("#span_edit_class_mintotal_"+class_id).html(class_old_mintotal);
		$("#class_edit_mintotal"+class_id).val(class_old_mintotal);
		if(class_old_visible==1){
			$("#span_edit_class_visible_"+class_id).html('Visible');
		}else{
			$("#span_edit_class_visible_"+class_id).html('Invisible');
		}
		$("#class_edit_visible"+class_id).val(class_old_visible);
	}
	$("#pop_up").show().delay(5000).fadeOut(); 
}
});  
}


/* Ajax gia diagrafi class kai update tis listas */
function send_class_removal(clicked_delete){
	var class_id_temp = clicked_delete.attr('id');
	var class_id = class_id_temp.replace('remove_class_','');
	var class_name = $("#class_edit_name"+class_id).val();
$.ajax({
type: "GET",
url: "functions_admin.php",
data: "class_id=" + class_id + "&send_class_delete=1",
dataType: "html",
success: function(html){  
	if(html==1){
		$("#notification").html('Το μάθημα ' + class_name + ' διαγράφηκε με επιτυχία .');
	}else{
		$("#notification").html('Η διαγραφηκή του μαθήματος ' + class_name + ' απέτυχε .');
	}
	$("#pop_up").show().delay(5000).fadeOut();
}
}); 
$.ajax({
type: "GET",
url: "functions_admin.php",
data: "give_classes_list=1",
dataType: "html",
success: function(html){  
	$("#list_classes_admin").html(html);
}
}); 
}


/* Ajax gia dimiourgia ergastiriou */
function add_new_lab(lab_name) {
var class_id = $("#class_select_new_lab").val();
var lock = $("#lab_select_lock").val();
var lock_date = $("#add_new_lab_date").val();
var lock_hour = $("#add_new_lab_hour").val();
var lock_minutes = $("#add_new_lab_minute").val();
var multiplier = $("#add_new_lab_mult").val();
var type = $("#lab_select_type").val();
var include_total = $("#lab_select_include_to_total").val();
var noerror=1;

if(lock_date=='' || lock_date==' '){
	data_to_send="lab_name=" + lab_name + "&class_id=" + class_id + "&lock=" + lock + "&multiplier=" + multiplier + "&type=" + type + "&include_total=" + include_total +"&lock_date=-1&lock_hour=-1&lock_minutes=-1&add_new_lab=1";
}else{
	if((lock_hour=='' || lock_hour==' ') || (lock_minutes=='' || lock_minutes==' ')){
		noerror=0;
		$("#notification").html('Έχει δοθεί ημερομηνία για κλείδωμα αλλά όχι ώρα ή λεπτά.');
		$("#pop_up").show().delay(5000).fadeOut();
	}else{
		data_to_send="lab_name=" + lab_name + "&class_id=" + class_id + "&lock=" + lock + "&lock_date=" + lock_date+ "&lock_hour=" + lock_hour+ "&lock_minutes=" + lock_minutes + "&multiplier=" + multiplier + "&type=" + type + "&include_total=" + include_total + "&add_new_lab=1";
	}
}

if(noerror==1){
	$.ajax({
	type: "GET",
	url: "functions_admin.php",
	data: data_to_send,
	dataType: "html",
	success: function(html){  
		if(html==1){
			$("#notification").html('Το εργαστήριο ' + lab_name + ' προστέθηκε με επιτυχία .');
		}else{
			$("#notification").html('Η πρόσθεση του εργαστηρίου ' + lab_name + ' απέτυχε .<br />');
		}
		$("#pop_up").show().delay(5000).fadeOut();
	}
	});
	$.ajax({
	type: "GET",
	url: "functions_admin.php",
	data: "show_admin_labs=1",
	dataType: "html",
	success: function(html){  
		$("#list_labs_admin").html(html);
	}
	}); 
	} 
}


/* Ajax gia diagrafi lab kai update tis listas */
function send_lab_removal(clicked_delete){
	var lab_id_temp = clicked_delete.attr('id');
	var lab_id = lab_id_temp.replace('remove_lab_','');
	var lab_name = $("#lab_edit_name"+lab_id).val();
$.ajax({
type: "GET",
url: "functions_admin.php",
data: "lab_id=" + lab_id + "&send_lab_delete=1",
dataType: "html",
success: function(html){  
	if(html==1){
		$("#notification").html('Το εργαστήριο ' + lab_name + ' διαγράφηκε με επιτυχία .');
	}else{
		$("#notification").html('Η διαγραφηκή του εργαστηρίου ' + lab_name + ' απέτυχε .');
	}
	$("#pop_up").show().delay(5000).fadeOut();
}
}); 
$.ajax({
type: "GET",
url: "functions_admin.php",
data: "show_admin_labs=1",
dataType: "html",
success: function(html){  
	$("#list_labs_admin").html(html);
}
}); 
}


/* Allakse to lab se input gia edit */
var timer_edit_labs=[];
function edit_lab_change(clicked_edit){
	var lab_id_temp = clicked_edit.attr('id');
	var lab_id = lab_id_temp.replace('edit_lab_','');
	var lab_name = $("#lab_edit_name"+lab_id).val();
	var lock = $("#lab_edit_lock"+lab_id).val();
	var lock_date = $("#lab_edit_date"+lab_id).val();
	var lock_hour = $("#lab_edit_hour"+lab_id).val();
	var lock_minutes = $("#lab_edit_minute"+lab_id).val();
	var multiplier = $("#lab_edit_mult"+lab_id).val();
	var type = $("#lab_edit_type"+lab_id).val();
	var inc_total = $("#lab_edit_inc_total"+lab_id).val();
	
	var html_to_give_name = '<input size="10" type="text" id="input_lab_edit_'+lab_id+'" value="'+lab_name+'" />';
	var html_to_give_lock ;
	if(lock==0){
		html_to_give_lock = '<select id="input_lab_edit_lock_'+lab_id+'"> <option value="0" selected>Ανοιχτό</option><option value="1">Κλειδωμένο</option></select>';
	}else{
		html_to_give_lock = '<select id="input_lab_edit_lock_'+lab_id+'"> <option value="0">Ανοιχτό</option><option value="1" selected>Κλειδωμένο</option></select>';
	}
	var html_to_give_lock_date = '<input size="10" type="text" id="input_lab_edit_date_'+lab_id+'" value="'+lock_date+'" />';
	var html_to_give_lock_hour = '<input size="1" type="text" id="input_lab_edit_hour_'+lab_id+'" value="'+lock_hour+'" />';
	var html_to_give_lock_minutes = '<input size="1" type="text" id="input_lab_edit_minutes_'+lab_id+'" value="'+lock_minutes+'" />';
	var html_to_give_multiplier = '<input size="1" type="text" id="input_lab_edit_mult_'+lab_id+'" value="'+multiplier+'" />';
	if(type==0){
		html_to_give_type = '<select id="input_lab_edit_type_'+lab_id+'"> <option value="0" selected>Εργαστήριο</option><option value="1">Θεωρία</option></select>';
	}else{
		html_to_give_type = '<select id="input_lab_edit_type_'+lab_id+'"> <option value="0">Εργαστήριο</option><option value="1" selected>Θεωρία</option></select>';
	}
	if(inc_total==0){
		html_to_give_inc_total = '<select id="input_lab_edit_inc_total_'+lab_id+'"> <option value="0" selected>Ναι</option><option value="1">Όχι</option></select>';
	}else{
		html_to_give_inc_total = '<select id="input_lab_edit_inc_total_'+lab_id+'"> <option value="0">Ναι</option><option value="1" selected>Όχι</option></select>';
	}

	if(clicked_edit.val() == 'Edit'){
		$("#span_edit_lab_"+lab_id).html(html_to_give_name);
		$("#span_edit_lab_lock_"+lab_id).html(html_to_give_lock);
		$("#span_edit_lab_date_"+lab_id).html(html_to_give_lock_date);
		$('#input_lab_edit_date_'+lab_id).jdPicker({date_format:'YYYY-mm-dd'});
		$("#span_edit_lab_hour_"+lab_id).html(html_to_give_lock_hour);
		$("#span_edit_lab_minutes_"+lab_id).html(html_to_give_lock_minutes);
		$("#span_edit_lab_mult_"+lab_id).html(html_to_give_multiplier);
		$("#span_edit_lab_type_"+lab_id).html(html_to_give_type);
		$("#span_edit_lab_inc_total_"+lab_id).html(html_to_give_inc_total);
		timer_edit_labs[lab_id] = setTimeout(function() {
			$("#span_edit_lab_"+lab_id).html(lab_name);
			if(lock==0){
				$("#span_edit_lab_lock_"+lab_id).html('Ανοιχτό');
			}else{
				$("#span_edit_lab_lock_"+lab_id).html('Κλειδωμένο');
			}
			if(type==0){
				$("#span_edit_lab_type_"+lab_id).html('Εργαστήριο');
			}else{
				$("#span_edit_lab_type_"+lab_id).html('Θεωρία');
			}
			if(inc_total==0){
				$("#span_edit_lab_inc_total_"+lab_id).html('Ναι');
			}else{
				$("#span_edit_lab_inc_total_"+lab_id).html('Όχι');
			}
			$("#span_edit_lab_date_"+lab_id).html(lock_date);
			$("#span_edit_lab_hour_"+lab_id).html(lock_hour);
			$("#span_edit_lab_minutes_"+lab_id).html(lock_minutes);
			$("#span_edit_lab_mult_"+lab_id).html(multiplier);
			clicked_edit.val('Edit');
		}, 20000);
		clicked_edit.val('Done');
		
	}else{
		clearTimeout(timer_edit_labs[lab_id]);
		lab_old_name=lab_name;
		lab_name = $("#input_lab_edit_"+lab_id).val();
		old_lock=lock;
		lock = $("#input_lab_edit_lock_"+lab_id).val();
		old_lock_date=lock_date;
		lock_date = $("#input_lab_edit_date_"+lab_id).val();
		old_lock_hour=lock_hour;
		lock_hour = $("#input_lab_edit_hour_"+lab_id).val();
		old_lock_minutes =lock_minutes;
		lock_minutes = $("#input_lab_edit_minutes_"+lab_id).val();
		old_multiplier =multiplier;
		multiplier = $("#input_lab_edit_mult_"+lab_id).val();
		old_type = type;
		type= $("#input_lab_edit_type_"+lab_id).val();
		old_inc_total=inc_total;
		inc_total=$("#input_lab_edit_inc_total_"+lab_id).val();
		clicked_edit.val('Edit');
		send_lab_edit(lab_id,lab_name,lab_old_name,lock,lock_date,lock_hour,lock_minutes,old_lock,old_lock_date,old_lock_hour,old_lock_minutes,old_multiplier,multiplier,inc_total,old_inc_total,type,old_type);
	}
}


/* Ajax gia edit lab */
function send_lab_edit(lab_id,lab_name,lab_old_name,lock,lock_date,lock_hour,lock_minutes,old_lock,old_lock_date,old_lock_hour,old_lock_minutes,old_multiplier,multiplier,inc_total,old_inc_total,type,old_type){
if(lock_date =='' || lock_date==' '){
	data_for_ajax = "lab_id=" + lab_id + "&lab_name=" + lab_name + "&lock=" + lock + "&multiplier=" + multiplier + "&inc_total=" + inc_total + "&type=" + type + "&lock_date=-1&send_lab_edit=1";
}else{
	data_for_ajax = "lab_id=" + lab_id + "&lab_name=" + lab_name + "&lock=" + lock + "&lock_date=" + lock_date + "&lock_hour=" + lock_hour + "&lock_minutes=" + lock_minutes + "&multiplier=" + multiplier + "&inc_total=" + inc_total + "&type=" + type + "&send_lab_edit=1";
}
$.ajax({
type: "GET",
url: "functions_admin.php",
data: data_for_ajax,
dataType: "html",
success: function(html){ 
	if(html==1){
		$("#notification").html('Το εργαστήριο  άλλαξε επιτυχώς .');
		$("#span_edit_lab_"+lab_id).html(lab_name);
		$("#lab_edit_name"+lab_id).val(lab_name);
		if(lock==0){
			$("#span_edit_lab_lock_"+lab_id).html('Ανοιχτό');
		}else{
			$("#span_edit_lab_lock_"+lab_id).html('Κλειδωμένο');
		}
		if(type==0){
			$("#span_edit_lab_type_"+lab_id).html('Εργαστήριο');
		}else{
			$("#span_edit_lab_type_"+lab_id).html('Θεωρία');
		}
		if(inc_total==0){
			$("#span_edit_lab_inc_total_"+lab_id).html('Ναι');
		}else{
			$("#span_edit_lab_inc_total_"+lab_id).html('Όχι');
		}
		$("#span_edit_lab_date_"+lab_id).html(lock_date);
		$("#span_edit_lab_hour_"+lab_id).html(lock_hour);
		$("#span_edit_lab_minutes_"+lab_id).html(lock_minutes);
		$("#span_edit_lab_mult_"+lab_id).html(multiplier);
		
		$("#lab_edit_lock"+lab_id).val(lock);
		$("#lab_edit_date"+lab_id).val(lock_date);
		$("#lab_edit_hour"+lab_id).val(lock_hour);
		$("#lab_edit_minute"+lab_id).val(lock_minutes);
		$("#lab_edit_mult"+lab_id).val(multiplier);
		$("#lab_edit_type"+lab_id).val(type);
		$("#lab_edit_inc_total"+lab_id).val(inc_total);
	}else{
		$("#notification").html('Η αλλαγή του εργαστηρίου απέτυχε .<br/>'+html);
		$("#span_edit_lab_"+lab_id).html(lab_old_name);
		$("#lab_edit_name"+lab_id).val(lab_old_name);
		if(old_lock==0){
			$("#span_edit_lab_lock_"+lab_id).html('Ανοιχτό');
		}else{
			$("#span_edit_lab_lock_"+lab_id).html('Κλειδωμένο');
		}
		if(old_type==0){
			$("#span_edit_lab_type_"+lab_id).html('Εργαστήριο');
		}else{
			$("#span_edit_lab_type_"+lab_id).html('Θεωρία');
		}
		if(old_inc_total==0){
			$("#span_edit_lab_inc_total_"+lab_id).html('Ναι');
		}else{
			$("#span_edit_lab_inc_total_"+lab_id).html('Όχι');
		}
		$("#span_edit_lab_date_"+lab_id).html(old_lock_date);
		$("#span_edit_lab_hour_"+lab_id).html(old_lock_hour);
		$("#span_edit_lab_minutes_"+lab_id).html(old_lock_minutes);
		$("#span_edit_lab_mult_"+lab_id).html(old_multiplier);
	}
	$("#pop_up").show().delay(5000).fadeOut(); 
}
});  
}


/* Epilogi vathmou gia epeksergasia h eisagogi */
function refresh_change_grade_admin(){
	var class_id = $("#select_class").val();
	var lab_id = $("#select_lab").val();
	var user_aem = $("#user__aem_grade_admin_edit").val();
	var all_here=1;
	
	if( lab_id == -1){
		all_here=0;
		$("#notification").html('Παρακαλώ επιλέξτε εργαστήριο .');
		$("#pop_up").show().delay(5000).fadeOut();
	}
	
	if( user_aem == '' || user_aem == ' '){
		all_here=0;
		$("#notification").html('Παρακαλώ δώστε ΑΕΜ .');
		$("#pop_up").show().delay(5000).fadeOut();
	}
	
	if(all_here==1){
		var called_id = 'user__aem_grade_admin_edit';
		find_user_by_aem(user_aem,called_id);
		
		$.ajax({
		type: "GET",
		url: "functions_admin.php",
		data: "lab_id=" + lab_id + "&class_id=" + class_id + "&user_aem=" + user_aem + "&select_grade_admin=1",
		dataType: "html",
		success: function(html){ 
			$("#edit_grades_admin_window").html(html);
		}
		}); 
	
	}
}


/* Stelnei tin isagogi/epeksergasia vathmou */
function send_new_grade_admin(user_id) {
var class_id = $("#select_class").val();
var lab_id = $("#select_lab").val();
var grade = $("#enter_new_grade").val();
var lock = $("#enter_new_grade_lock_option").val();
var checkbox;
if($('#notify_grade_change_admin').prop('checked')){
	checkbox=1;
}else{
	checkbox=0;
}

$.ajax({
type: "GET",
url: "functions_admin.php",
data: "class_id=" + class_id + "&lab_id=" + lab_id +"&user_id=" + user_id +"&grade=" + grade +"&lock=" + lock +"&checkbox="+checkbox+"&enter_change_grade=1",
dataType: "html",
success: function(html){      
	$("#notification").html(html);
	$("#pop_up").show().delay(10000).fadeOut();
}
});

}


/* Stelnei tin isagogi/epeksergasia public comment */
function send_public_com_admin(user_id) {
var class_id = $("#select_class").val();
var lab_id = $("#select_lab").val();
var public_com = $("#public_com_input").val();

$.ajax({
type: "GET",
url: "functions_admin.php",
data: "user_id=" + user_id +"&public_com=" + public_com +"&enter_change_public_com=1",
dataType: "html",
success: function(html){      
	if(html==1){
		$("#notification").html('Το δημόσιο σχόλιο καταχωρήθηκε με επιτυχία.');
	}else if(html==0){
		$("#notification").html('Το σχόλιο δεν καταχωρήθηκε.<br/> Υπήρξε κάποιο λάθος στην εισαγωγή στοιχείων.');
	}else if(html==11){
		$("#notification").html('Το δημόσιο σχόλιο διαγράφηκε με επιτυχία.');
	}else{
		$("#notification").html(html);
	}
	$("#pop_up").show().delay(5000).fadeOut();
}
});

}
/* Stelnei tin isagogi/epeksergasia private comment */
function send_private_com_admin(user_id) {
var class_id = $("#select_class").val();
var lab_id = $("#select_lab").val();
var private_com = $("#private_com_input").val();

$.ajax({
type: "GET",
url: "functions_admin.php",
data: "user_id=" + user_id +"&private_com=" + private_com +"&enter_change_private_com=1",
dataType: "html",
success: function(html){      
	if(html==1){
		$("#notification").html('Το ιδιωτικό σχόλιο καταχωρήθηκε με επιτυχία.');
	}else if(html==2){
		$("#notification").html('Πρέπει να υπάρχει βαθμός για να εισαχθέι κάποιο σχόλιο.');
	}else if(html==0){
		$("#notification").html('Το σχόλιο δεν καταχωρήθηκε.<br/> Υπήρξε κάποιο λάθος στην εισαγωγή στοιχείων.');
	}else if(html==11){
		$("#notification").html('Το ιδιωτικό σχόλιο διαγράφηκε με επιτυχία.');
	}else{
		$("#notification").html(html);
	}
	$("#pop_up").show().delay(5000).fadeOut();
}
});

}


/* Emfanizei ta comments stin provoli vathmon ana user */
function retrive_comments(user_id) {
var temp_html;


$.ajax({
type: "GET",
url: "functions_admin.php",
data: "user_id=" + user_id + "&retrive_public_com=1",
dataType: "html",
success: function(html){      
	if(html != '' && html != ' '){
		public_comment="Public Comment<br/>"+html+"<br /><br />";
	}else{
		public_comment="";
	}
}
});

$.ajax({
type: "GET",
url: "functions_admin.php",
data: "user_id=" + user_id + "&retrive_private_com=1",
dataType: "html",
success: function(html){      
	if(html != '' && html != ' '){
		private_comment="Private Comment<br/>"+html+"<br /><br />";
	}else{
		private_comment="";
	}
}
});
if(public_comment == '' && private_comment == ''){
	public_comment="There are no Comments for this grade.";
}
close_button='<input type="button" value="Close" onclick="$(\'#shadow_under_popup\').hide();$(\'#comments_container\').hide();" />';
$("#comments_container").html(public_comment + private_comment + close_button);
$("#shadow_under_popup").show();
$("#comments_container").show();
}


/* Stelnei to mathima pros ekatharisi vathmon olon ton user */
function flush_class_admin(class_id) {
var class_name = $("#class_flushing_admin option:selected").html();
$.ajax({
type: "GET",
url: "functions_admin.php",
data: "class_id=" + class_id + "&flush_class_all=1",
dataType: "html",
success: function(html){    
	if(html==1){
		$("#notification").html('Οι βαθμοί διαγράφηκαν  με επιτυχία από το μάθημα ' + class_name);
	}else{
		$("#notification").html('Οι βαθμοί δεν μπόρεσαν να διαγραφούν από το μάθημα ' + class_name);
	}
	$("#pop_up").show().delay(5000).fadeOut();
}
}); 
}


/* Stelnei ton user pros ekatharisi olon ton vathmon tou */
function flush_student_admin(user_aem) {
var user_name = $("#show_user_name_flush").html();
$.ajax({
type: "GET",
url: "functions_admin.php",
data: "user_aem=" + user_aem + "&flush_student_all=1",
dataType: "html",
success: function(html){    
	if(html==1){
		$("#notification").html('Οι βαθμοί διαγράφηκαν  με επιτυχία από τον φοιτητή ' + user_name);
	}else{
		$("#notification").html('Οι βαθμοί δεν μπόρεσαν να διαγραφούν από τον φοιτητή ' + user_name);
	}
	$("#pop_up").show().delay(5000).fadeOut();
}
}); 
}


/* Stelnei entoli gia egatharisi sxolion olon ton user */
function flush_all_comments_admin() {
	var type = $('#all_comments_to_flush_menu').val();
	$.ajax({
		type: "GET",
		url: "functions_admin.php",
		data: "type="+type+"&flush_all_comments=1",
		dataType: "html",
		success: function(html){   
			if(html==1){
				$("#notification").html('Τα σχόλια διαγράφηκαν με επιτυχία.');
			}else{
				$("#notification").html('Τα σχόλια δεν μπόρεσαν να διαγραφούν επιτυχώς.');
			}
			$("#pop_up").show().delay(5000).fadeOut();
		}
	}); 
}


/* Stelnei ton user pros ekatharisi olon ton private/public comments tou */
function flush_comments_for_student_admin(user_aem) {
var user_name = $("#show_user_name_flush_com").html();
var type = $("#comments_to_flush_menu").val();
$.ajax({
type: "GET",
url: "functions_admin.php",
data: "user_aem=" + user_aem + "&type=" + type +"&flush_student_all_comments=1",
dataType: "html",
success: function(html){   
	if(html==1){
		$("#notification").html('Τα σχόλια διαγράφηκαν  με επιτυχία από τον φοιτητή ' + user_name);
	}else{
		$("#notification").html('Τα σχόλια δεν μπόρεσαν να διαγραφούν από τον φοιτητή ' + user_name);
	}
	$("#pop_up").show().delay(5000).fadeOut();
}
}); 
}


/* vriskei ton user apo to aem tou */
function find_user_by_aem(user_aem,called_id) {
$.ajax({
type: "GET",
url: "functions_admin.php",
data: "user_aem=" + user_aem + "&find_user_by_aem=1",
dataType: "html",
success: function(html){ 
	if(called_id=='student_flushing_admin_com'){
		$("#show_user_name_flush_com").html(html);
	}
	if(called_id=='student_flushing_admin'){
		$("#show_user_name_flush").html(html);
	}
	if(called_id=='user__aem_grade_admin_edit'){
		$("#show_user_name_edit_grade").html("Επεξεργασία βαθμού για "+html);
	}
}
}); 
}


/* Misc functions */


/* dinei to jdpicker sta input me hmerominies */
$(document).ready(function(){
	$('#add_new_lab_date').jdPicker({date_format:'YYYY-mm-dd'});
});


/* Makes the tabs for different user classes in user view */
$(document).ready( function() {
if ($('#user_classes_tabs').length) {
	var user = $("#user_id_for_js").val();
	$.ajax({
		type: "GET",
		url: "functions.php",
		data: "user_id=" + user + "&create_user_classes_tabs=1",
		dataType: "html",
		success: function(html){ 
			$("#user_classes_tabs").html(html);
		}
	}); 
}
});


/* Changes user class tab in user view */
function change_class_tab(class_id) {
	var user = $("#user_id_for_js").val();
		$.ajax({
		type: "GET",
		url: "functions.php",
		data: "update_grades_showcase=1&class_id="+class_id,
		dataType: "html",
		success: function(html){    
			$(".selected_tab").removeClass("selected_tab");
			$("#tab_class_"+class_id).toggleClass( "selected_tab", 1000, "easeOutSine" );
			$("#user_shown_classes").html(html);
		}
	});
}
