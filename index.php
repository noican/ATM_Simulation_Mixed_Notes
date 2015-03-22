<?php

include ('include/functions.php');

echo 'Starting Inventory: ';
show_inventory();

if (isset($_POST['amount_withdrawn'])) {

	$amount_to_withdraw = intval($_POST['amount_withdrawn']);


	if (is_numeric($amount_to_withdraw) && $amount_to_withdraw>0) {
		
		if (find_combination($amount_to_withdraw)!=0) {

			echo 'Total amount dispensed : '. $amount_to_withdraw . '</br>';
			echo '</br></br></br>';
			echo 'Present Inventory: ';
			show_inventory();

		} else {
			echo 'Error : $'. $amount_to_withdraw .' is not divisible by existing notes.' ;
		}

		echo '</br><a href="index.php">Refresh</a>';

	} else {
		echo 'Invalid amount : '. $amount_to_withdraw ;
	}
	

} else {

	input_amount();
}

?>
