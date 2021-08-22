<?php

namespace Firesphere\Mini;

use SilverStripe\Core\Convert;
use SilverStripe\Core\Environment;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;

/**
 * Class \Firesphere\Mini\City
 *
 * @property string $Name
 * @property string $Lat
 * @property string $Lng
 * @method DataList|Location[] Locations()
 */
class City extends DataObject
{

    private static $table_name = 'City';

    private static $db = [
        'Name' => 'Varchar(255)',
        'Lat' => 'Varchar(15)',
        'Lng' => 'Varchar(15)'
    ];

    private static $has_many = [
        'Locations' => Location::class
    ];

    private static $casting = [
        'Spatial' => 'Spatial',
        'getSpatial' => 'Spatial'
    ];

    protected static $map = [
        'AUK' => 'Auckland',
        'AKL' => 'Auckland',
        'WLG' => 'Wellington'
    ];

    public function getSpatial()
    {
        if (!$this->Lat || !$this->Lng) {
            $this->Lat = $this->Lng = 0;
        }
        return sprintf('%s,%s', $this->Lat, $this->Lng);
    }

    public static function findOrCreate($data)
    {
        foreach ($data as $value) {
            if ($value['types'][0] == 'administrative_area_level_1') {
                $city = $value['long_name'];
                break;
            }
        }
        if (!isset($city) || is_array($city)) {
            return;
        }
        if (array_key_exists(strtoupper($city), static::$map)) {
            $city = static::$map[strtoupper($city)];
        }

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s+New+Zealand&key=%s';

        $result = file_get_contents(sprintf($url, Convert::raw2url($city),
            Environment::getEnv('MAPSKEY')));

        $result = json_decode($result);

        /** @var City $exist */
        $exist = City::get()->filter(['Name' => $city])->first();

        if (!$exist) {
            $exist = City::create();
            $exist->Name = $city;
            if (count($result->results)) {
                $exist->Lat = $result->results[0]->geometry->location->lat;
                $exist->Lng = $result->results[0]->geometry->location->lng;
            }
            $exist->write();
        }

        $subID = Suburb::findOrCreate($data, $exist);

        return [$exist->ID, $subID];
    }

}
