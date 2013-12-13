<?php

// pinterest webscrapper - PHP script
// Use casperjs data to webscrap original pins to folder
// then generate thumbnails for Wookmark plugin
// @author  Jean-François Lefebvre (hello@e-volution.be)
// @Date    13/12/2013
// @version 0.9

require_once('PinterestHelper.php');

$pinterestJson = __DIR__ . '/pinterest.json';

if (!file_exists($pinterestJson) || filesize($pinterestJson)===0) {
    echo 'pinterest.json is missing or empty.'.PHP_EOL;
    echo 'Please run casperjs pinterest-casper.js script before to call the webscraper.'.PHP_EOL;
    die();
}

$pinDataDir = __DIR__.'/pins/';

$pins_data = file_get_contents($pinterestJson);
$pins_data = json_decode($pins_data);

$pins = array();

foreach ($pins_data as $pin) {

    $pinName = $pin->description;
    $pinBoard = $pin->board;
    $pinThumbnail = $pin->pin_page;
    $pinPage = $pin->href;

    // retrieve informations about the pin and save the original pin image
	$pinInfo = PinterestHelper::getPinInfo($pinPage);
    if (!empty($pinInfo)) {
        $pinFullPath = $pinInfo["src"];
        $pinExplode = explode('/', $pinFullPath);
        $pinName = end($pinExplode);

        $pinFilename = $pinDataDir . $pinName;
        if (!file_exists($pinFilename)) file_put_contents($pinFilename, file_get_contents($pinFullPath));

        $baseURL = '/'; 
        $pinUrl = $baseURL . 'pins/' . $pinName;

        $pin = array();
        $pin['pin_url'] = $pinUrl;
        $pin['pin_page_url'] = $pinPage;
        $pin['image_name'] = $pinName;
        $pin['image_fullpath'] = $pinFilename;
        $pin['pinned'] = $pinInfo['pinned'];
        $pin['board'] = $pinBoard;
        $pin['description'] = $pinInfo['alt'];
        $pin['width'] = $pinInfo['width'];
        $pin['height'] = $pinInfo['height'];
        $pins[] = $pin;
    }
}

file_put_contents($pinterestJson, json_encode($pins));

// Generate the thumbnails
PinterestHelper::generateThumbnails('pins');

echo 'DONE !' . PHP_EOL;