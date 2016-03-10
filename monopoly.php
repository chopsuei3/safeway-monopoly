<html>

<head>
</head>

<body>

<form method="post" action="monopoly.php" enctype="multipart/form-data">
Safeway Username: <input type="text" name="username"><br>
Safeway Password: <input type="text" name="password"><br>
Select File: <input type="file" name="file" id="file" />
<input type="submit" name="submit" />
</form>

<?php

// Check if form was submitted
if(isset($_POST["submit"]))
{

	// Check if form contains username and password for login
    if (isset($_POST['username']) && isset($_POST['password'])) {

    	//Prepare POST data from form entry
	    $username = urlencode($_POST["username"]);
		$password = $_POST["password"];
		$postdata = "redirect=&username=" . $username . "&password=" . $password; 

		$auth_url = "https://www.playmonopoly.us/authentication/login";

		$cURL = curl_init();
		curl_setopt($cURL, CURLOPT_URL, $auth_url);
		curl_setopt ($cURL, CURLOPT_SSL_VERIFYPEER, FALSE); 
		curl_setopt($cURL, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($cURL, CURLOPT_COOKIEJAR, 'cookie.txt');
		curl_setopt($cURL, CURLOPT_COOKIEFILE, 'temp.txt');

		// Might need to modify based on your OS
		//curl_setopt($cURL, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt');
		curl_setopt ($cURL, CURLOPT_POSTFIELDS, $postdata); 
		curl_setopt ($cURL, CURLOPT_POST, TRUE); 
		curl_setopt($cURL, CURLOPT_FOLLOWLOCATION, FALSE);
		curl_setopt($cURL, CURLOPT_HTTPHEADER, 
			array(
				'Host: www.playmonopolycodes.us',
				'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:44.0) Gecko/20100101 Firefox/44.0',
				'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
				'Accept-Language: en-US,en;q=0.5',
				'Accept-Encoding: gzip, deflate, br',
				'Referer: https://www.playmonopoly.us/authentication/login',
				'Connection: keep-alive'
				));

		$loginresult = curl_exec($cURL);
		$logininfo = curl_getinfo($cURL);
		##curl_close($cURL);
		if($logininfo['http_code'] == 200) {
			echo "<p>Login successful!</p>";
		}
		else
		{
			die("<p>Login UNsuccessful!</p>");
		}


		// Check if CSV file was uploaded without an error
	    if (isset($_FILES['file']) && $_FILES['error'] == 0) {

			echo "<p>" . $_FILES['file']['name'] . " uploaded</p>";

		    $codeCount = 0;
		    $codeArray = array();

		    $handle = fopen($_FILES['file']['tmp_name'], "r");
		    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				//Load codes into array for CURL later
				$codeArray[$codeCount] = $data['0'];

				//Count records
				$codeCount++;
			}

		    fclose($handle);

		    echo "<p>There are " . $codeCount . " codes in the file you uploaded";

			// HTML formatting
		    echo "<table border=\"1\">";
		    echo "<tr><th>Code</th><th>URL</th><th>HTTP Response</th><th>Prize</th><th>Code from JSON</th><th>Error Message</th><tr>";

			// Loop through array and submit each code as a POST using CURL
		    for($i=0;$i<$codeCount;$i++)
		    {

				$entry_url = "https://www.playmonopolycodes.us/service/game-code?code=" . $codeArray[$i];

				curl_setopt($cURL, CURLOPT_URL, $entry_url);
				curl_setopt ($cURL, CURLOPT_SSL_VERIFYPEER, FALSE); 
			//	curl_setopt ($cURL, CURLOPT_POSTFIELDS, $postdata); 
				curl_setopt ($cURL, CURLOPT_POST, TRUE); 
				curl_setopt($cURL, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($cURL, CURLOPT_COOKIEJAR, 'cookie.txt');
				curl_setopt($cURL, CURLOPT_COOKIEFILE, 'temp.txt');
				curl_setopt($cURL, CURLOPT_FOLLOWLOCATION, FALSE);
				curl_setopt($cURL, CURLOPT_HTTPHEADER, 
					array(
						'Host: www.playmonopolycodes.us',
						'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:44.0) Gecko/20100101 Firefox/44.0',
						'Accept: */*',
						'Accept-Language: en-US,en;q=0.5',
						'Accept-Encoding: gzip, deflate, br',
						'Referer: https://www.playmonopolycodes.us/game/enter-codes',
						'Connection: keep-alive'
						));

				$code_result_raw = curl_exec($cURL);

				// The response is gzip encoded so you have to use gzdecode on the response and then decode the JSON string to an array
				$code_result = json_decode(gzdecode($code_result_raw), TRUE);

				$entryinfo = curl_getinfo($cURL);
				$entry_http_code = $entryinfo['http_code'];

				// Set prize field to string as PHP FALSE does not output anything
				if($code_result['prize'])
				{
					$code_result_prize = "Winner!";
				}
				else
				{
					$code_result_prize = "Loser";
				}

				// Output each code and the results to the table
				echo "<tr><td>" . $codeArray[$i] . "</td><td>" . $entry_url . "</td><td>" . $entry_http_code . "</td><td>" . $code_result_prize . "</td><td>" . $code_result['code'] . "</td><td>" . $code_result['error'] . "</td></tr>";
			}
	
		echo "</table>";
		curl_close($cURL);
		}
	}
}
elseif($_FILES['error'] != 0)
{
	die("<p>upload error or no username or password entered</p>");
}

?>

</body>

</html>