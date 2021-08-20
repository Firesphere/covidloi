<?php

namespace Firesphere\Mini;

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
            DB::query('TRUNCATE TABLE `MoHCode`');
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

        $json = file_get_contents('https://raw.githubusercontent.com/minhealthnz/nz-covid-data/main/locations-of-interest/august-2021/locations-of-interest.geojson');

        $data = json_decode($json, 1);
        $mohCodes = MoHCode::get()->column('Code');
        foreach ($data['features'] as $location) {
            if (!in_array($location['properties']['id'], $mohCodes)) {
                $map = [
                    $location['properties']['Event'],
                    $location['properties']['Location'],
                    date('Y-m-d', strtotime(str_replace('/', '-', $location['properties']['Start']))),
                    date('H:i:s', strtotime(str_replace('/', '-', $location['properties']['Start']))) . '-' .
                    date('H:i:s', strtotime(str_replace('/', '-', $location['properties']['End']))),
                    $location['properties']['Information'],
                    date('Y-m-d')
                ];
                $mohCodes[] = $location['properties']['id'];
                MoHCode::create(['Code' => $location['properties']['id']])->write();
                Location::findOrCreateByLatLng($map);
            }
        }
    }
}
