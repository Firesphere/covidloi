<?php

namespace Firesphere\Mini;

use SilverStripe\Core\Convert;
use SilverStripe\Core\Environment;
use SilverStripe\ORM\DataObject;

/**
 * Class \Firesphere\Mini\Suburb
 *
 * @property string $Name
 * @property string $Lat
 * @property string $Lng
 * @property int $CityID
 * @method City City()
 * @method DataList|Location[] Locations()
 */
class Suburb extends DataObject
{

    private static $table_name = 'Suburb';

    private static $db = [
        'Name' => 'Varchar(255)',
        'Lat'  => 'Varchar(15)',
        'Lng'  => 'Varchar(15)',
    ];

    private static $has_one = [
        'City' => City::class
    ];

    private static $has_many = [
        'Locations' => Location::class
    ];

    private static $casting = [
        'Spatial'    => 'Spatial',
        'getSpatial' => 'Spatial'
    ];

    public function getSpatial()
    {
        return sprintf('%s,%s', $this->Lat, $this->Lng);
    }

    /**
     * @param array $data
     * @param City $city
     * @return int|void
     * @throws \SilverStripe\ORM\ValidationException
     */
    public static function findOrCreate($data, $city)
    {
        foreach ($data as $value) {
            foreach ($value['types'] as $type) {
                if ($type === 'sublocality_level_1') {
                    $suburb = $value['long_name'];
                }
                if ($type === 'postal_code') {
                    $postal = $value['long_name'];
                }
            }
        }
        if (!isset($suburb) || is_array($suburb)) {
            return;
        }

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s,New+Zealand&key=%s';

        $result = file_get_contents(sprintf($url, Convert::raw2url(sprintf('%s,%s,%s', $suburb, $postal, $city->Name)),
            Environment::getEnv('MAPSKEY')));

        $result = json_decode($result);

        $exist = Suburb::get()->filter(['Name' => $suburb])->first();

        if (!$exist) {
            $exist = Suburb::create();
            $exist->Name = $suburb;
            $exist->CityID = $city->ID;
            if (count($result->results)) {
                $exist->Lat = $result->results[0]->geometry->location->lat;
                $exist->Lng = $result->results[0]->geometry->location->lng;
            }

            return $exist->write();
        }

        return $exist->ID;

    }
}
