//On every keystroke in the search field
$("#ticket_search").keyup(function(){
	filter_tickets(this.value);
	
	if(this.value.length > 0){;
		$("#cancel_search").show();
	}else{
		$("#cancel_search").hide();
	}
});

//On every change with the checkboxes
$(".search_checkbox").change(function(){
	filter_tickets($("#ticket_search")[0].value);
});

//If a tag is clicked
$(".ticket_tag").click(function(){
	$("#ticket_search")[0].value = $.trim(this.innerHTML); //Update search field
	$("#search_tags").prop("checked", true); //Ensure tag checkbox is checked
	$("#cancel_search").show() //Show 'X' field
	filter_tickets($.trim(this.innerHTML)); //Update table
});

//Clear search field
$("#cancel_search").click(function(){
	$("#ticket_search")[0].value = ""; //Empty search field
	$("#cancel_search").hide(); //Hide 'X' field
	filter_tickets(); //Update table
});

//When a confimation checkbox is checked
$(".confirm_checkbox").change(function(){
	if($(this).is(":checked")){
		$("#confirm_" + this.value).attr("disabled", false);
	}else{
		$("#confirm_" + this.value).attr("disabled", true);
	}
});

/*
	Updates the ticket table based on search options

	@param string
*/
function filter_tickets(search_text){
	let total = 0;
	
	//For each ticket row
	$("#ticket_table tr:not(:first)").each(function(){
		//Set initial bool
		show_ticket = false;
		
		//Get array of each cell of the ticket row
		ticket_row = $(this).children("td");
		
		//For each field selected as per the checkboxes
		$(".search_checkbox").each(function(){			
			//Check if string exists for selected fields
			if($(this).is(":checked")){
				//If the string is found in a field
				if(new RegExp(search_text, "i").test(ticket_row[this.value].innerHTML)){
					show_ticket = true;
				}
			}
		});
		
		//Show/Hide accordingly
		if(show_ticket){
			$(this).show();
			total++;
		}else{
			$(this).hide();
		}
		
		if(total == 0){
			$('#ticket_table').hide();
			$('#no_records').show();
		}else{
			$('#ticket_table').show();
			$('#no_records').hide();
		}
	});
}