<?php
	//Current redirects to error are the same; can show different page if required per error type
	
	// Initialize the session
	session_start();
	
	//If there is no session
	if(!isset($_SESSION["loggedin"])){
		header("location: index.php");
		exit();
	}
	
	// Include main functions
	require_once("include/funcs/sql_funcs.php");
	
	//Connect to database
	sql_connect();
	
	$_POST['ticket_id'] = (int)$_POST['ticket_id'];
	
	//Ensure ticket exists
	try{
		$ticket = new Ticket($_POST['ticket_id'], $_SESSION['username']);
	}catch(Exception $e){
		header("location: view_all_tickets.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//Ensure the user has permissions to update the fields
	$account = new Account($_SESSION['username']);
	
	//Developers
	if($account->is_dev() || $account->is_admin() && $account->id == $ticket->get_assigned_to()){
		//Set to pending for review
		if(isset($_POST['request_ticket_review'])){
			$ticket->update_status('pending');
		}
	}
	
	//Reviewers
	if($account->is_rev() || $account->is_admin()){
		//Reviewer takes on a bug ticket and assigns it to self
		if(isset($_POST['review_ticket'])){
			$ticket->assign_rev($_POST['account_id']);
		}
		
		//Ticket is approved as resolved
		if(isset($_POST['approve_ticket']) && $account->id == $ticket->get_reviewed_by()->id){
			$ticket->update_status('resolved');
		}
		
		//Ticket is rejected as resolved
		if(isset($_POST['reject_ticket']) && $account->id == $ticket->get_reviewed_by()->id){
			$ticket->update_status('assigned');
			$ticket->clear_rev();
		}
	}
	
	//Triagers
	if($account->is_tri() || $account->is_admin()){	
		//If setting bug as duplicate
		if(isset($_POST['set_as_duplicate'])){
			$ticket->update_dup((int)$_POST['dup_id']);
		}
		
		//If clearing bug as duplicate
		if(isset($_POST['clear_duplicate'])){
			$ticket->clear_dup();
		}
		
		//If assigning developer, but no developer was chosen
		if(isset($_POST['assign_dev']) && !empty($_POST['developer'])){
			if($_POST['developer'] == "1"){
				//If clearing the assigned developer
				$ticket->clear_dev();
				$ticket->update_status('unassigned');
			}else{
				//Asssign developer
				$ticket->assign_dev($_POST['developer']);			
				
				//Update the status and redirect
				$ticket->update_status('assigned');
			}
		}
		
		//If setting as invalid
		if(isset($_POST['set_invalid'])){
			$ticket->clear_dev();
			$ticket->clear_rev();
			$ticket->update_status('invalid');
		}
	}
	
	header("location: view_ticket_info.php?t={$_POST['ticket_id']}");
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
