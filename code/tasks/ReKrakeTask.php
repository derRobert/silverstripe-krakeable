<?php

/**
 * Created by PhpStorm.
 * User: robert
 * Date: 30.08.18
 * Time: 16:46
 */
class ReKrakeTask extends BuildTask
{

    protected $title = 'Re-Krake Images';

    public function run($request)
    {
        $cnt = 0;
        if (!Director::is_cli()) {
            die('cli only pls');
        }
        $cfg = singleton('KrakenService')->config();

        if ($list = $this->getList()) {
            foreach ($list as $image) {
                if ($image->canKrake()) {
                    $cnt++;
                    $res = Krakeable::processImage($image);
                    printf("Processing: {$image->Filename} :%s\n", ($res===true?'OK':$res));
                } else {
                    printf("Skipped: {$image->Filename}\n");
                }
            }
        }
        printf("-------------------------\nProcessed %s files\n", $cnt);
    }

    /**
     * @return DataList
     */
    protected function getList()
    {

        $list = DataList::create('Image');
        $this->extend('updateList', $list);
        return $list;

    }

}