<?php
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
	
	$account = new Account($_SESSION['username']);
	
	try{
		$ticket = new Ticket((int)$_GET['t'], $_SESSION['username']);
	}catch(Exception $e){
		header("location: view_all_tickets.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>View Ticket #<?= (int)$_GET['t'] ?></title>
		<link rel='stylesheet' href='include/css/main.css'>
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<table id='ticket_details' class='basic_table' style='width:60%;'>
			<tr>
				<td colspan='2'>
					Viewing ticket #<?= (int)$_GET['t'] ?>
				</td>
			</tr>
			<tr>
				<td style='width:30%;'>
					Title:
				</td>
				<td style='width:70%;'>
					<?= $ticket->get_title() ?>
				</td>
			</tr>
			<tr>
				<td>
					Description:
				</td>
				<td>
					<?= $ticket->get_description() ?>
				</td>
			</tr>
			<tr>
				<td>
					Tags:
				</td>
				<td>
					<?php
						if(!empty($ticket->get_tags())){
							$tag_array = explode(",", $ticket->get_tags());
							
							foreach($tag_array as $tag){
					?>
								<span class='ticket_tag' style='cursor:auto;'>
										<?= $tag ?>
								</span>	
					<?php
							}
						}
					?>
				</td>
			</tr>
			<tr>
				<td>
					Created by:
				</td>
				<td>
					<a href='view_account_info.php?a=<?= $ticket->get_created_by()->id ?>'>
						<?= $ticket->get_created_by()->get_full_name() ?>
					</a>
				</td>
			</tr>
			<tr>
				<td>
					Created on:
				</td>
				<td>
					<?= date('d-M-Y', strtotime($ticket->get_created_date())) ?>
				</td>
			</tr>
			<tr>
				<td>
					Current Status:
				</td>
				<td class='<?= $ticket->get_status() ?>_ticket'>
					<?php
						echo ucfirst($ticket->get_status());
						
						if(strtolower($ticket->get_status()) == "assigned" && ($account->is_dev() || $account->is_admin()) && $account->id == $ticket->get_assigned_to()->id){
					?>
							<form action='ticket_assign.php' method='POST' style='display:inline-block;'>
								<input type='hidden' name='account_id' value='<?= $account->id ?>' />
								<input type='hidden' name='ticket_id' value='<?= $ticket->ticket_id ?>' />
								<input type='submit' name='request_ticket_review' value='Set for Review' />
							</form>
					<?php
						}
					?>
				</td>
			</tr>
			<tr>
				<td>
					Duplicate of:
				</td>
				<td>
					<?php
						try{
							//There is a duplicate
							$dup_ticket = new Ticket($ticket->get_duplicate_of());
						
							echo "<a href='view_ticket_info.php?t={$dup_ticket->ticket_id}'>#{$dup_ticket->ticket_id} - {$dup_ticket->get_title()}</a>";
							
							//Allow traigers to update the field
							if($account->is_tri() || $account->is_admin()){
					?>
								<br />
								<br />
								<form action='ticket_assign.php' method='POST'>
									Duplicate ID: <input type='text' name='dup_id' value='<?= $dup_ticket->ticket_id ?>' style='width:60px;;'/>
									<input type='hidden' name='ticket_id' value='<?= $ticket->ticket_id ?>' />
									<input type='submit' name='set_as_duplicate' value='Set as duplicate' />
									<input type='submit' name='clear_duplicate' value='Clear duplicate' />
								</form>
					<?php
							}
						}catch(Exception $e){
							//There is no duplicate
							if($account->is_tri() || $account->is_admin()){
								//Allow triagers to set duplicate
					?>
								<form action='ticket_assign.php' method='POST'>
									Duplicte ID: <input type='number' min='1' name='dup_id' style='width:60px;' />
									<input type='hidden' name='ticket_id' value='<?= $ticket->ticket_id ?>' />
									<input type='submit' name='set_as_duplicate' value='Set as duplicate' />
								</form>
					<?php
							}else{
								//Show N/A for other users
								echo "N/A";
							}
						}
					?>
				</td>
			</tr>
			<?php
				if(!$account->is_norm()){
			?>
					<tr>
						<td>
							Assigned to:
						</td>
						<td>
							<?= (is_object($ticket->get_assigned_to()) ? "<a href='view_account_info.php?a={$ticket->get_assigned_to()->id}'>{$ticket->get_assigned_to()->get_full_name()}</a>" : "") ?>
						</td>
					</tr>
					<tr>
						<td>
							Reviewed by:
						</td>
						<td>
							<?php 
								if(is_object($ticket->get_reviewed_by())){
							?>
									<a href='view_account_info.php?a=<?= $ticket->get_reviewed_by()->id ?>'><?= $ticket->get_reviewed_by()->get_full_name() ?></a>
							<?php
									if(strtolower($ticket->get_status()) == "pending" && ($account->is_rev() || $account->is_admin()) && $account->id == $ticket->get_reviewed_by()->id){
							?>
										<form action='ticket_assign.php' method='POST' style='display:inline-block;'>
											<input type='hidden' name='account_id' value='<?= $account->id ?>' />
											<input type='hidden' name='ticket_id' value='<?= $ticket->ticket_id ?>' />
											<input type='submit' name='approve_ticket' value='Approve' />
											<input type='submit' name='reject_ticket' value='Reject' />
										</form>
							<?php
									}
								}else{
									if(strtolower($ticket->get_status()) == "pending" && ($account->is_rev() || $account->is_admin())){
							?>
										<form action='ticket_assign.php' method='POST'>
											<input type='hidden' name='account_id' value='<?= $account->id ?>' />
											<input type='hidden' name='ticket_id' value='<?= $ticket->ticket_id ?>' />
											<input type='submit' name='review_ticket' value='Review Ticket' />
										</form>
							<?php
									}
								}
							?>
						</td>
					</tr>
			<?php
				}

				if($account->is_tri() || $account->is_admin()){
					//Check if the ticket is resolved/closed/invalid
					if(strtolower($ticket->get_status()) != 'assigned' && strtolower($ticket->get_status()) != 'unassigned'){
						$disableSelection = "disabled";
					}else{
						$disableSelection = "";
					}
			?>
					<tr>
						<td>
							Assign to:
						</td>
						<td>
							<form action='ticket_assign.php' method='POST' style='display:inline-block;'>
								<select name='developer' <?php echo $disableSelection ?>>
									<option value='0'>Assign developer</option>
									<option value='1'>Clear developer</option>
							<?php
								$query = db_query("SELECT * FROM `accounts` WHERE `account_type` = 'developer' ORDER BY `experience`;");
								
 								while($row = mysqli_fetch_assoc($query)){
 							?>
									<option value='<?= $row['id'] ?>'><?= "{$row['first_name']} {$row['last_name']} ({$row['experience']} Exp)" ?></option>
							<?php
								}
							?>
								</select>
								<input type='checkbox' class='confirm_checkbox' name='ticket_id' value='<?= $ticket->ticket_id ?>' <?= $disableSelection ?>/>
								<input type='submit' name='assign_dev' id='confirm_<?= $ticket->ticket_id ?>' value='Assign' disabled/>
							</form>
							<form action='ticket_assign.php' method='POST' style='display:inline-block;'>
								<input type='hidden' name='account_id' value='<?= $account->id ?>' />
								<input type='hidden' name='ticket_id' value='<?= $ticket->ticket_id ?>' />
								<input type='submit' name='set_invalid' value='Set as invalid' onClick="return confirm('Confirm setting the ticket as invalid?')" <?= $disableSelection ?>/>
							</form>
						</td>
					</tr>
			<?php
				}
			?>
		</table>
		<br />
		<form action='ticket_comment.php' method='POST'>
			<table id='make_comment' class='basic_table' style='width:60%;'>
				<tr>
					<td>
						Make a comment
					</td>
				</tr>
				<tr>
					<td>
						<textarea name='comment' placeholder='Comment here' rows='8' style='width:97%;' required></textarea>
					</td>
				</tr>
				<tr>
					<td>
						<input type='hidden' name='bug_id' value='<?= (int)$_GET['t'] ?>'>
						<input type='submit' name='submit' value='Comment on Bug Ticket'>
					</td>
				</tr>
			</table>
		</form>
		<br />
		<?php
			$all_comments = get_comments((int)$_GET['t']);
			
			foreach($all_comments as $this_comment){
				$poster = new Account($this_comment['account_id']);
		?>
				<table class='basic_table' style='width:60%;'>
					<tr>
						<td>
							<span style='float:left'>From: <a href='view_account_info.php?a=<?= $this_comment['account_id'] ?>'><?= $poster->get_full_name() ?></a></span> <span style='float:right'><?= date('d-M-Y', strtotime($this_comment['created_date'])) ?></span>
						</td>
					</tr>
					<tr>
						<td>
							<?= $this_comment['comment'] ?>
						</td>
					</tr>
				</table>
				<br />
		<?php
			}
		?>
		<a href='view_all_tickets.php'>Back to main page</a>
	</body>
</html>
<script src='include/js/jquery-light-v3.5.1.js'></script>
<script src='include/js/global.js'></script>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
