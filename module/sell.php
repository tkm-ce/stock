<?php
if(isset($_SESSION['user']))
{
	if($_REQUEST['o'] == 'sell')
	{
		$sql = "SELECT * FROM settings WHERE value = 'market'";
		$ref = query_database($sql);
		$row = mysql_fetch_assoc($ref);
		$status = $row['conf'];
		$symbol = null;
		$form = true;
		if(isset($_POST['sym']) && isset($_POST['amount']))
		{
			if($status != "closed")
			{
				$sym = mysql_real_escape_string(urldecode($_POST['sym']));
				$amount = mysql_real_escape_string($_POST['amount']);
				$amountCpy = $amount;
				$sql = "SELECT value FROM stockval WHERE symbol = '$sym'";
				$ref = query_database($sql);
				$row = mysql_fetch_assoc($ref);
				$rate = $row['value'];
				$cost = $amount * $row['value'];
				$sql = "SELECT liq_cash FROM user WHERE id = '" . $_SESSION['id'] . "'";
				$ref = query_database($sql);
				$row = mysql_fetch_assoc($ref);
				$brokerage = $cost * 0.003;
				$cost = $cost - 0.003 * $cost;
				$cost = $row['liq_cash'] + $cost;
				$sql = "SELECT * FROM stocks_bought WHERE id = '" . $_SESSION['id'] . "' AND symbol = '$sym'";
				
				$ref = query_database($sql);
				$current_amount = mysql_fetch_assoc($ref);
				$count = mysql_num_rows($ref);
				if($current_amount['amount'] >= $amount && $count != 0)
				{
					$amount = $current_amount['amount'] - $amount;
					$sql = "UPDATE user SET liq_cash = '$cost' WHERE id = '" . $_SESSION['id'] . "'";
					$ref = query_database($sql);
					
					$sql = "UPDATE stocks_bought SET amount = '$amount' WHERE id = '" . $_SESSION['id'] . "' and symbol = '$sym'";
					$ref = query_database($sql);
					
					$msg .= "Sold $amountCpy Units of $sym at $rate per Unit<br />Brokerage + tax = $brokerage<br />";
					
				}
				else
				{
					$msg .= "Unable to sell $sym";
					
				}
				$content .= $msg;
				$sql = "INSERT INTO notifications (id,notification,status) VALUES('" . $_SESSION['id'] . "','$msg','ur')";
				
				$ref = query_database($sql);
				
				

				$form = false;
			}
			else
			{
				$content .= "Market is closed";
				$form = false;
			}
		}	

		else if(isset($_GET['sym']))
		{
			$symbol = $_GET['sym'];
		}
		else 
		{
			$symbol = "Symbol";
		}
		if($form)
		{
	$content = $content . '<form class = "buySellAction" method = "post" action = "index.php">&nbsp;Symbol : <input type = "text" name = "sym" value = "' . $symbol . '"></input><br />Quantity : <input type = "text" name = "amount"></input><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Rate : <input class = "limitBuy" type = "textbox" name = "rate" value = "Current Rate"></input><br />Stop Loss : <input class = "stoploss" type = "textbox" name = "stop" value = "Stop Loss"></input><br /><input type = "hidden" name = "o" value = "sell"></input><select class = "buySellType" name = "t"><option value = "Current">Sell</option><option value = "limit">Limit</option></select><input type = "checkbox" name = "amo" value = "amo">AMO</input><br /><input type = "submit" /></form>';		
		}	
	}
	
}
		
