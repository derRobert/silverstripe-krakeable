<?php

/**
 * Created by PhpStorm.
 * User: robert
 * Date: 09.06.17
 * Time: 16:56
 */
class Krakeable extends Extension
{


    public function onAfterUpload()
    {
        /** @var Image $file */
        $file = $this->owner;
        $krakenSvcConfig = Config::inst()->forClass('KrakenService');
        if (!in_array($file->getExtension(), $krakenSvcConfig->process_extensions)) {
            return;
        }
        if (!$krakenSvcConfig->enabled) {
            return;
        }
        if (!$krakenSvcConfig->process_on_upload) {
            return;
        }
        self::processImage($file);
    }

    public static function processImage(Image $image) {
        /** @var KrakenService $svc */
        $svc = singleton('KrakenService');
        $fullPath = $image->getFullPath();
        $response = $svc->optimizeImage($fullPath);
        if( is_array($response) && isset($response['success']) && $response['success'] == 1 ) {
            try {
                $newFile = file_get_contents($response['kraked_url']);
                file_put_contents($fullPath, $newFile);
            } catch( Exception $ex ) {
                user_error('Unable to write kraked image to file');
            }
        }
    }

}