<?php

define('TOKEN', 'nasz token');

$category_id = 4029;

function getCategories($category_id){
	
	$ch = curl_init("https://api.allegro.pl/sale/categories?parent.id=".$category_id);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		 "Authorization: Bearer ".TOKEN,
		 "Accept: application/vnd.allegro.public.v1+json"
	]);

	$result = curl_exec($ch);
	
	curl_close($ch);

	return json_decode($result, true)['categories'];
}

$lines = [];

function addCategories($categories, $parent_id){
	global $lines;
	foreach($categories as $category){
		$lines[] = [$category['id'], $category['name'], $parent_id, 1];
		addCategories(getCategories($category['id']), $category['id']);
	}
}

addCategories(getCategories($category_id), $category_id);

header('Content-Disposition: attachment; filename="kategorie.csv";');

$f = fopen("kategorie.csv", "w");
foreach ($lines as $line) {
    fputcsv($f, $line, ';');
}