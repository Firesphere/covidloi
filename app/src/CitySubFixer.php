<?php

namespace Firesphere\Mini;

use SilverStripe\Core\Environment;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DataList;

class CitySubFixer extends BuildTask
{

    public function run($request)
    {
        /** @var DataList|City $cities */
        $cities = City::get();
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s+New+Zealand&key=%s';

        foreach ($cities as $city) {
            $data = file_get_contents(sprintf($url, $city->Name, Environment::getEnv('MAPSKEY')));

            $result = json_decode($data);

            if (count($result->results)) {
                $city->Lat = $result->results[0]->geometry->location->lat;
                $city->Lng = $result->results[0]->geometry->location->lng;
            }
            $city->write();
        }

    }
}
