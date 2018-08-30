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
        $krakenSvcConfig = Config::inst()->forClass('KrakenService');
        /** @var Image $file */
        $file = $this->owner;
        if( $this->canKrake() && $krakenSvcConfig->process_on_upload ) {
            self::processImage($file);
        }
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
                return true;
            } catch( Exception $ex ) {
                return 'Unable to write kraked image to file';
            }
        } else {
            return $response['error'];
        }
    }

    public function canKrake() {
        /** @var Image $file */
        $file = $this->owner;
        $krakenSvcConfig = Config::inst()->forClass('KrakenService');
        if (!in_array($file->getExtension(), $krakenSvcConfig->process_extensions)) {
            return false;
        }
        if (!$krakenSvcConfig->enabled) {
            return false;
        }
        $can = true;
        $this->owner->extend('updateCanKrake', $can);
        return $can;
    }

}