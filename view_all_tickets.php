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
	$all_tickets = get_tickets($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>View All Tickets</title>
		<link rel='stylesheet' href='include/css/main.css'>
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
	</head>
	<body>
		
		<?php include("include/templates/header.php"); ?>
		Search: <input type='text' name='ticket_search' id='ticket_search' placeholder='Search for ticket' /> <span id='cancel_search'>X</span>
		<input type='checkbox' id='search_id' class='search_checkbox' value=0 checked/> ID
		<input type='checkbox' id='search_title' class='search_checkbox' value=1 /> Title
		<input type='checkbox' id='search_tags' class='search_checkbox' value=2 /> Tags
		<input type='checkbox' id='search_status' class='search_checkbox' value=3 /> Status
		
		<?php
			//Additional table columns based on account type
			if(!$account->is_norm()){
		?>
				<input type='checkbox' id='search_developer' class='search_checkbox' value=4 /> Developer
				<input type='checkbox' id='search_reviewer' class='search_checkbox' value=5 /> Reviewer
		<?php
			}
		?>
		<br />
		<br />
		<table id='ticket_table' class='basic_table'>
			<tr>
				<td style='text-align:center;'>
					ID
				</td>
				<td style='text-align:center;'>
					Title
				</td>
				<td style='text-align:center;'>
					Tags
				</td>
				<td style='text-align:center;'>
					Status
				</td>
				<?php
					//Additional table columns based on account type
					if(!$account->is_norm()){
				?>
						<td style='text-align:center;'>
							Assigned
						</td>
						<td style='text-align:center;'>
							Reviewer
						</td>
				<?php
					}
					
					if($account->is_tri() || $account->is_admin()){
				?>
						<td style='text-align:center;'>
							Assign
						</td>
				<?php
					}
				?>
			</tr>
			
			<?php
				//For each ticket
				foreach($all_tickets as $ticket){
			?>
				<tr>
					<td onclick="location.href='view_ticket_info.php?t=<?= $ticket->ticket_id ?>'" class='<?= $ticket->get_status() ?>_ticket' style='text-align:center; cursor:pointer;'>
						<?php printf('%05d', $ticket->ticket_id); ?>
					</td>
					<td onclick="location.href='view_ticket_info.php?t=<?= $ticket->ticket_id ?>'" class='<?= $ticket->get_status() ?>_ticket' style='text-align:left; cursor:pointer;'>
						<?= $ticket->get_title() ?>
					</td>
					<td class='<?= $ticket->get_status() ?>_ticket' style='text-align:left;'>
						<?php
							if(!empty($ticket->get_tags())){
								$tag_array = explode(",", $ticket->get_tags());
								
								foreach($tag_array as $tag){
						?>
									<span class='ticket_tag'>
											<?= $tag ?>
									</span>	
						<?php
								}
							}
						?>
					</td>
					<td   class='<?= $ticket->get_status() ?>_ticket' style='text-align:center;'>
						<?= $ticket->get_status() ?>
					</td>
					<?php
						//Additional table columns based on account type
						if(!$account->is_norm()){
					?>
							<td <?= (is_object($ticket->get_assigned_to()) ? "onclick=\"location.href='view_account_info.php?a={$ticket->get_assigned_to()->id}'\"" : "") ?> class='<?= $ticket->get_status() ?>_ticket' style='text-align:center; <?= (is_object($ticket->get_assigned_to()) ? "cursor:pointer;" : "") ?>'>
								<?= (is_object($ticket->get_assigned_to()) ? $ticket->get_assigned_to()->get_full_name() : "") ?>
							</td>
							<td <?= (is_object($ticket->get_reviewed_by()) ? "onclick=\"location.href='view_account_info.php?a={$ticket->get_reviewed_by()->id}'\"" : "") ?> class='<?= $ticket->get_status() ?>_ticket' style='text-align:center; <?= (is_object($ticket->get_assigned_to()) ? "cursor:pointer;" : "") ?>'>
								<?= (is_object($ticket->get_reviewed_by()) ? $ticket->get_reviewed_by()->get_full_name() : "") ?>
							</td>
					<?php
						}
						// Only assigned and unassigned status can update developers
						$disableSelection = "";

						if($account->is_tri() || $account->is_admin()){
							if(!(strtolower($ticket->get_status()) == 'assigned') && !(strtolower($ticket->get_status()) == 'unassigned')) {
								$disableSelection = "disabled";
							}
							
					?>
							<td style='text-align:center;'>
								<form action='ticket_assign.php' method='POST'>
									<select name='developer' <?php echo $disableSelection ?>>
										<option value='0'>Assign developer</option>
										<option value='1'>Clear developer</option>
										<?php
											$query = db_query("SELECT * FROM `accounts` WHERE `account_type` = 'developer' ORDER BY `experience`;");
											
											while($row = mysqli_fetch_assoc($query)){
										?>
												<option value='<?= $row['id'] ?>'><?= $row['first_name'] ." ". $row['last_name'] ." (". $row['experience'] ." Exp)" ?></option>
										<?php
											}
										?>
									</select>
									<input type='checkbox' <?= $disableSelection ?> class='confirm_checkbox' name='ticket_id' value='<?php echo $ticket->ticket_id ?>' />
									<input type='submit' name='assign_dev' id='confirm_<?= $ticket->ticket_id ?>' value='Assign' disabled/>
								</form>
							</td>
					<?php
						}
					?>
				</tr>
			<?php
				}
			?>
		</table>
	</body>
</html>
<script src='include/js/jquery-light-v3.5.1.js'></script>
<script src='include/js/global.js'></script>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
