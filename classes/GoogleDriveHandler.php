<?php
class GoogleDriveHandler
{
    public function __construct($rootPath, $imgHandler)
    {
        $this->rootPath = $rootPath;
        $this->imgHandler = $imgHandler;
    }

    public function getGoogleClient()
    {
        $client = new Google_Client();
        $client->setApplicationName('ImageCrawler');
        //$client->setScopes(Google_Service_Drive::DRIVE_METADATA_READONLY);
        $client->setScopes(Google_Service_Drive::DRIVE);
        $client->setAuthConfig($this->rootPath . '/credentials.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = $this->rootPath . '/token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }

    public function uploadToGoogleDrive($service, $file_name, $file_path, $folder_ids)
    {
        $fileMetadata = new Google_Service_Drive_DriveFile(array(
            'name' => $file_name,
            //Set the Parent Folder
            'parents' => $folder_ids // this is the folder id
        ));
        $file_mime = $this->imgHandler->getMimeType($file_path);
        if(is_null($file_mime)) return false;

        try{
            $newFile = $service->files->create(
                $fileMetadata,
                array(
                    'data' => file_get_contents($file_path),
                    'mimeType' => $file_mime,
                    'uploadType' => 'media'
                )
            );
            return true;
        } catch(Exception $e){
            echo 'An error ocurred : ' . $e->getMessage();
            return false;
        }
    }
}