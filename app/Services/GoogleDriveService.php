<?php namespace App\Services;

use App\Cloud;
use App\User;
use Illuminate\Support\Facades\Config;
use Google_Service_Oauth2;
use Google_Client;

class GoogleDriveService extends CloudService {

    public function create($attributes)
    {
        return $this->refreshOrCreate($attributes);
    }

    protected function refreshOrCreate($attributes)
    {
        $access_token = $attributes['access_token'];

        $client = $this->getClient($attributes);
        $authService = new Google_Service_Oauth2($client);
        $uid = $authService->userinfo->get()->id;
        $user = User::findOrFail($attributes['user_id']);

        $googleDrives = $user->clouds->where('type', Cloud::GoogleDrive);
        foreach($googleDrives as $drive) {
            if($drive->uid === $uid) {
                $drive->access_token = $access_token;
                $drive->save();
                return $drive;
            }
        }

        return Cloud::create(array_merge($attributes, ['uid' => $uid, 'type' => Cloud::GoogleDrive]));
    }


    public function getClient($attributes)
    {
        $client = new Google_Client();
        $client->setClientSecret(Config::get('clouds.google_drive.secret'));
        $client->setClientId(Config::get('clouds.google_drive.id'));

        if($attributes instanceof Cloud) {
            $client->setAccessToken(json_encode([
                'access_token' => $attributes->access_token,
                'token_type' => $attributes->token_type,
                'expires_in' => $attributes->expires_in,
                'created' => $attributes->created
            ]));
        } else {
            $client->setAccessToken(json_encode([
                'access_token' => $attributes['access_token'],
                'token_type' => $attributes['token_type'],
                'expires_in' => $attributes['expires_in'],
                'created' => $attributes['created']
            ]));
        }


        return $client;
    }

    public function getContents($cloudId, $path)
    {
        $service = $this->getService($cloudId);

        $response = [];

        $files = $service->children->listChildren($path)->getItems();

        foreach($files as $file) {
            $_file = $service->files->get($file->getId());
            array_push($response, $_file);
        }
        return $response;
    }

    public function removeContent($cloudId, $path)
    {
        $service = $this->getService($cloudId);
        $response = $service->files->delete($path);

        return $response;
    }

    public function moveContent($cloudId, $path, $newPath)
    {

    }

    public function infoCloud($cloudId)
    {
        //$client = $this->getClient($cloudId);
    }

    public function removeCloud($cloudId)
    {
        $cloud = $this->getCloud($cloudId);
        //$client = $this->getClient($cloudId);

        //$client->revokeToken();
        $cloud->delete();
    }

    public function shareStart($cloudId, $path)
    {
        // TODO: Implement shareStart() method.
    }

    public function renameContent($cloudId, $fileId, $newTitle)
    {
        $service = $this->getService($cloudId);
        $file = $service->files->get($fileId);

        $file->setTitle($newTitle);

        try {
            $response = $service->files->update($fileId, $file) ? 'true' : 'false';
        }
        catch (\Google_Service_Exception $ex) {
            $response = $ex->getMessage();
        }

        return $response;
    }

    public function getService($cloudId)
    {
        $googleDrive = $this->getCloud($cloudId);
        $client = $this->getClient($googleDrive);
        return new \Google_Service_Drive($client);
    }
}
