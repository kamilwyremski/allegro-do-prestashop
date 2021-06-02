<?php

define('URL', 'https://github.com');

define('ALLEGRO_CLIENT_API', 'test');

define('ALLEGRO_CLIENT_SECRET', 'test');

if(!empty($_GET['code'])){
	$authUrl = "https://allegro.pl/auth/oauth/token?grant_type=authorization_code&code=".$_GET['code']."&redirect_uri=".URL;
	
	$ch = curl_init($authUrl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		 "Authorization: Basic ".base64_encode(ALLEGRO_CLIENT_API.':'.ALLEGRO_CLIENT_SECRET),
		 "Accept: application/vnd.allegro.public.v1+json"
	]);

	$tokenResult = curl_exec($ch);
	curl_close($ch);

	$tokenResult = json_decode($tokenResult, true);
	
	print_r($tokenResult);

}else{
	echo('<a href="https://allegro.pl/auth/oauth/authorize?response_type=code&client_id='.ALLEGRO_CLIENT_API.'&redirect_uri='.URL.'">powiąż z allegro</a>');
}