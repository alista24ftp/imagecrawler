<?php
ini_set('max_execution_time', '1800');
ini_set('post_max_size', '0');
ini_set('memory_limit', '-1');

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/autoload.php';

if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}

// STEP 1: Crawl images and titles from website, and download them to local folder
$rootpath = __DIR__;
$imgHandler = new ImageHandler();
$crawler = new UberEatsCrawler(
    'https://www.ubereats.com/ca/vancouver/food-delivery/hon-sushi/XAAB10yNTL6wz9qbi2gXfA/e8db9ac7-3349-4e00-915c-b7d048eb5080', 
    new PatternUtil(), 
    $imgHandler
);
$images = $crawler->getAllImgs($rootpath);

// STEP 2: Upload images to Google Drive
$googleDriveHandler = new GoogleDriveHandler($rootpath, $imgHandler);
$google_client = $googleDriveHandler->getGoogleClient();
$drive_service = new Google_Service_Drive($google_client);
$dest_folders = [
    '1rMEoweo9t9_fAeKlc7M_19uMWn2UvL9S'
];
$res = ['success'=>[], 'failed'=>[]];
foreach($images as $img_name => $img_path){
    $uploadRes = $googleDriveHandler->uploadToGoogleDrive(
        $drive_service, 
        $img_name, 
        $img_path, 
        $dest_folders
    );
    if($uploadRes){
        $res['success'][] = $img_name;
    }else{
        $res['failed'][] = $img_name;
    }
}
// print which images were successfully uploaded to Drive and which ones failed
var_dump($res);
