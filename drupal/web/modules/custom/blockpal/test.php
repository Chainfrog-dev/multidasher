<?php
    $directory = '/var/www/.multichain/new2/';

    if ($fh = fopen($directory.'multichain.conf', 'r')) {
    	echo('loaded multichain.conf');
	    while (!feof($fh)) {
	        $line = fgets($fh);
			if (strpos($line, 'rpcuser=') !== false) {
				$user = preg_replace('/\s+/', '', str_replace('rpcuser=','',$line));
			}
			if (strpos($line, 'rpcpassword=') !== false) {
				$password = preg_replace('/\s+/', '', str_replace('rpcpassword=','',$line));
			}
	    }
	    fclose($fh);
	}

    if ($fh2 = fopen($directory.'params.dat', 'r')) {
    	echo('loaded params.dat');
	    while (!feof($fh2)) {
	        $line = fgets($fh2);
	        echo($line);
			if (strpos($line, 'default-rpc-port =') !== false) {
				$port = substr(str_replace('default-rpc-port = ','',$line), 0, 4);
		        $url='http://localhost:'.$port;
			}
	    }
	    fclose($fh2);
	}		
	$payload=json_encode(array(
		'id' => time(),
		'method' => 'getinfo',
		'params' => [],
	));
	
	$ch=curl_init($url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERPWD, $user.':'.$password);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: '.strlen($payload)
	));
	
	$response=curl_exec($ch);
	$result=json_decode($response, true);
	print_r($result);
?>

