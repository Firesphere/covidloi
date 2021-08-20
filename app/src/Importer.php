<?php

namespace Firesphere\Mini;

use SilverStripe\Control\Director;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DB;

class Importer extends BuildTask
{

    public function run($request)
    {
        $truncate = $request->getVar('truncate');
        if ($truncate) {
            DB::query('TRUNCATE TABLE `Location`');
            DB::query('TRUNCATE TABLE `LocTime`');
            DB::query('TRUNCATE TABLE `City`');
        }
        $content = file_get_contents('https://www.health.govt.nz/our-work/diseases-and-conditions/covid-19-novel-coronavirus/covid-19-health-advice-public/contact-tracing-covid-19/covid-19-contact-tracing-locations-interest');
        $dom = new \PHPHtmlParser\Dom();
        $dom->loadStr($content);
        $body = $dom->find('div.main-container.container');
        $trs = $body->find('tr');
        foreach ($trs as $tr) {
            $tds = $tr->find('td');
            $data = [];
            $i = 0;
            foreach ($tds as $td) {
                $data[$i] = $td->text;
                $i++;
            }
            if (count($data)) {
                Location::findOrCreate($data);
            }
        }
    }
}
