<?php

define('TOKEN', 'nasz token');

$parametersAllegroArray = [];
function getParametersAllegro(int $categoryId){
	global $parametersAllegroArray;
	if(!isset($parametersAllegroArray[$categoryId])){
		$parametersAllegroArray[$categoryId] = getParametersFromAllegro($categoryId);
	}
	return $parametersAllegroArray[$categoryId];
}

function getParametersFromAllegro(int $categoryId){
	$getOffersUrl = "https://api.allegro.pl/sale/categories/".$categoryId."/parameters";

	$ch = curl_init($getOffersUrl);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		 "Authorization: Bearer ".TOKEN,
		 "Accept: application/vnd.allegro.public.v1+json"
	]);

	$parametersResult = curl_exec($ch);
	$resultCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	if ($parametersResult === false || $resultCode !== 200) {
		return false;
	}

	$parameters = json_decode($parametersResult);

	return $parameters;
}
	
$f = fopen("produkty.csv", "w");

function getOffers(int $offset = 0, int $limit = 100){
	
	$getOffersUrl = "https://api.allegro.pl/sale/offers?publication.status=ACTIVATING&publication.status=ACTIVE&sellingMode.format=BUY_NOW&limit=".$limit."&offset=".$offset;

	$ch = curl_init($getOffersUrl);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
				 "Authorization: Bearer ".TOKEN,
				 "Accept: application/vnd.allegro.public.v1+json"
	]);

	$offersResult = curl_exec($ch);
	$resultCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	if ($offersResult === false || $resultCode !== 200) {
		return false;
	}

	$offers = json_decode($offersResult);

	return $offers;
}

public static function getOffer(int $id){
		$getOfferUrl = "https://api.allegro.pl/sale/offers/".$id;

		$ch = curl_init($getOfferUrl);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
					 "Authorization: Bearer ".TOKEN,
					 "Accept: application/vnd.allegro.public.v1+json"
		]);

		$productResult = curl_exec($ch);
		$resultCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($productResult === false || $resultCode !== 200) {
			return false;
		}

		$product = json_decode($productResult);

		return $product;
	}
	
$offers = getOffers();

$lines = [];

foreach($offers->offers as $offer){
	$product = allegro::getOffer($offer->id);
	
	$description = '';
	foreach($product->description->sections as $section){
		foreach($section->items as $item){
			if($item->type == 'TEXT'){
				$description .= $item->content;
			}elseif($item->type == 'IMAGE'){
				$description .= '<img src="'.$item->url.'">';
			}
		}
	}
	
	$images = '';
	foreach($product->images as $image){
		$images .= $image->url.',';
	}
	$images = trim($images,',');
	
	$parametry = '';
	
	$marka = '';
	
	$parametersAllegro = getParametersAllegro($product->category->id);
	
	foreach($product->parameters as $parameter){
		if($parameter->id != 11323){ // omijam stan
			foreach($parametersAllegro->parameters as $parameterAllegro){
				if($parameter->id == $parameterAllegro->id){
					if($parameter->id == 127415){ // producent części
						foreach($parameterAllegro->dictionary as $dictionary){
							if($dictionary->id == $parameter->valuesIds[0]){
								$marka = $dictionary->value;
							}
						}
					}else{
						if(isset($parameterAllegro->dictionary)){
							foreach($parameterAllegro->dictionary as $dictionary){
								if($dictionary->id == $parameter->valuesIds[0]){
									$parametry .= $parameterAllegro->name.':'.$dictionary->value.'::,';
								}
							}
						}else{
							$parametry .= $parameterAllegro->name.':'.$parameter->values[0].'::,';
						}
					}
				}
			}
		}
	}
	
	$parametry = trim($parametry,',');
	
	if(isset($product->sellingMode->netPrice->amount)){
		$price = $product->sellingMode->netPrice->amount;
	}else{
		$price = round($product->sellingMode->price->amount/1.23, 2);
	}
	
	$name = trim(trim($product->name,'#'));
	$name = str_replace("=","-",$name);
	$name = str_replace("<","-",$name);
	$name = str_replace(">","-",$name);
	$name = str_replace(";","-",$name);
	$lines[] = [
		'', //$product->id,
		$offer->publication->status=='ACTIVE' ? 1 : 0,
		$name,
		$product->category->id.',2',
		$price,
		1,
		'',
		1,
		0,
		0,
		'',
		'',
		'',
		'',
		'', // Dostawca
		$marka,
		'',
		'',
		'',
		'',
		'',
		'',
		'',
		'',
		'',
		'',
		$offer->stock->available,
		'',
		'',
		'',
		'',
		0,
		'',
		'',
		'',
		$description,
		'',
		'',
		'',
		'',
		'',
		'',
		'',
		'',
		'',
		'',
		1,
		$images,
		'',
		1,
		$parametry, // parametry
		'',
		'new'
	];
}

foreach ($lines as $line) {
	fputcsv($f, $line, ';');
}
