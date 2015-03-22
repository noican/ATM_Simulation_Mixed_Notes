<?php

function parse_this_file() {

	if (is_file('./config/inventory.xml')) {

		$reader = new XMLReader();
		$reader->open('./config/inventory.xml');

		// Set parser options - you must set this in order to use isValid method
		$reader->setParserProperty(XMLReader::VALIDATE, true);

		if ($reader->isValid()) {
		    $xmlfile = simplexml_load_file('./config/inventory.xml') or die("Error: Cannot create object");
		    return $xmlfile;
		} else {
		    return  "The XML is NOT valid";
		}

	} else {
		return "Config File not found!!!";
	} 
	
}


function show_inventory() {

	$existing_notes =  parse_this_file();
	if ($existing_notes == 'Config File not found!!!') {
		echo $existing_notes;
		exit;
	} else {
		echo  '</br>';
		foreach($existing_notes->children() as $bank_notes) {
		    echo "Notes: $". $bank_notes->amount . ", ";
		    echo "Remaining: ". $bank_notes->inventory . ", ";
		    echo "Total Amount: ".$bank_notes->amount*$bank_notes->inventory;
		    echo "</br>";
		} 
		echo 'End of initialization';
		echo '</br>';
		echo '</br>';
	}
}

function combination($amount_to_validate){

	$existing_notes =  parse_this_file();
	$combination = 0;
	$money_dispense =array();

	foreach($existing_notes->children() as $bank_notes) {
	    	$note_to_divide = intval($bank_notes->amount) ;		
		$present_inventory = intval($bank_notes->inventory) ;	

		if (($note_to_divide!=0) && ($present_inventory!=0)) {
		   	if ($amount_to_validate %$note_to_divide == 0 ) {
				$combinations++;
				$notes_to_dispense  = $note_to_divide;
				$no_of_notes_to_dispense = $amount_to_validate / $note_to_divide;
				$money_combination = array($notes_to_dispense,$no_of_notes_to_dispense);
				
				if ($no_of_notes_to_dispense<=$present_inventory) {
					array_push($money_dispense, $money_combination);
				}
			} 
		
		} 
		
	} 


	if ($combinations>=1) {
		return array ($combinations,$money_dispense, rand(0,$combinations-1));				
	} else {
		return array (0,$money_dispense, 0);
	}

}



function validate_notes_inventory($note_inventory,$num_to_check_inventory) {
	$existing_notes =  parse_this_file();	
	$result = false;
	foreach($existing_notes->children() as $bank_notes) {

		$num_inventory = intval($bank_notes->inventory) ;
	
		if (intval($bank_notes->amount) == $note_inventory ) {
			if (intval($bank_notes->inventory) >= $num_to_check_inventory) {
				$result = true;
			}
		}
	}
	return $result;
}


//////////////////////////////////////////
///// MIXED COMBINATION //////////////////
/////////////////////////////////////////

function mixed_combination($amount_to_withdraw) {
	$existing_notes =  parse_this_file();
	$combination = 0;
	$money_dispense =array();
	$money_combination =array();

	$x = $amount_to_withdraw;

	foreach($existing_notes->children() as $bank_notes) {

	    	$xmlfile_amount = intval($bank_notes->amount) ;		
		$xmlfile_inventory = intval($bank_notes->inventory) ;	

		if ($x>=$xmlfile_amount) {
			$a = $xmlfile_amount;
			$b = $x/$xmlfile_amount;
			$c = floor($b);

			if (validate_notes_inventory($a,$c)) {
				if ($c>=1) {		
					$x = $x - ($a * $c);
					array_push($money_combination,$a,$c);
				} 
			} 
			array_push($money_dispense,$money_combination,$x);
			$money_combination =array();
		}	
	
	} 


	$max_records = count($money_dispense)-1;

	//echo '<pre>',print_r($money_dispense);
	if ($money_dispense[$max_records] == 0) {
		foreach($money_dispense as $dispense) {
			if ($dispense[0]!=0 || $dispense[1]!=0) {
				dispense_cash($dispense[0],$dispense[1]);
			}
		}
	}

	return $money_dispense;

}



function find_combination($amount_to_withdraw) {
		//$_SESSION['combination_message']='';

		$result = '</br>';
		$combinations = combination($amount_to_withdraw);

		//echo '<pre>',print_r($combinations);
		//exit;
		$result = 0; 

		if ($combinations[0]>0) {
			$combination_to_use = intval($combinations[2]);
			
			$dispense_note = $combinations[1][$combination_to_use][0];
			$dispense_number = $combinations[1][$combination_to_use][1];
			if ($dispense_number >= 1) {
				$result = dispense_cash($dispense_note,$dispense_number);; 
			}

		}

		return $result;

}


function dispense_cash($notes_to_dispense,$num_notes_to_dispense){

	echo  'Note :'.$notes_to_dispense.'</br>No. of note/s :'.$num_notes_to_dispense.'</br>';
	$existing_notes =  parse_this_file();

	$doc = new DOMDocument('1.0', 'UTF-8');
	$doc->formatOutput = true;

	$money = $doc->createElement('money');
	$money = $doc->appendChild($money);

	foreach($existing_notes->children() as $bank_notes) {
		$notes = $doc->createElement('notes');
		$notes = $money->appendChild($notes);
		$amount = $notes->appendChild($doc->createElement('amount'));
		$amount_value = $amount->appendChild($doc->createTextNode(intval($bank_notes->amount))); 
		$inventory = $notes->appendChild($doc->createElement('inventory'));

		if (intval($bank_notes->amount) == $notes_to_dispense) {
			
			$inventory_value = $inventory->appendChild($doc->createTextNode(intval($bank_notes->inventory) -$num_notes_to_dispense )); 
		} else {
			$inventory_value = $inventory->appendChild($doc->createTextNode(intval($bank_notes->inventory))); 
		}
	}

	$doc->save('./config/inventory.xml');

	return 1;

}

function input_amount() {
	echo '<form method="POST">';
	echo 'Enter Amount to Withdraw : </br> <input type=text value=0 name="amount_withdrawn"></input>';
	echo '<input type="submit" name="submit" value="Withdraw"></input>';
	echo '</form>';
}

?>
