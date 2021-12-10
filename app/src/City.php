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
 * @method DataList|Suburb[] Suburbs()
 */
class City extends DataObject
{

    protected static $map = [
        'AUK' => 'Auckland',
        'AKL' => 'Auckland',
        'WLG' => 'Wellington'
    ];
    private static $table_name = 'City';
    private static $db = [
        'Name' => 'Varchar(255)',
        'Lat'  => 'Varchar(15)',
        'Lng'  => 'Varchar(15)'
    ];
    private static $has_many = [
        'Locations' => Location::class,
        'Suburbs'   => Suburb::class
    ];
    private static $casting = [
        'Spatial'    => 'Spatial',
        'getSpatial' => 'Spatial'
    ];

    public static function findOrCreate($data)
    {
        $city = $data['City'];
        $exist = self::get()->filter(['Name' => $city])->first();

        $write = false;
        if (!$exist) {
            $exist = self::create();
            $exist->Name = $city;
            $write = true;
        }

        if (!$exist->Lat) {
            $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s+New+Zealand&key=%s';

            $result = file_get_contents(sprintf($url, Convert::raw2url($city),
                Environment::getEnv('MAPSKEY')));

            $result = json_decode($result);
            if (count($result->results)) {
                $exist->Lat = $result->results[0]->geometry->location->lat;
                $exist->Lng = $result->results[0]->geometry->location->lng;
                $write = true;
            }
        }

        if ($write) {
            $exist->write();
        }

        $subID = Suburb::findOrCreate($data, $exist);

        return [$exist->ID, $subID];
    }

    public function getSpatial()
    {
        if (!$this->Lat || !$this->Lng) {
            $this->Lat = $this->Lng = 0;
        }

        return sprintf('%s,%s', $this->Lat, $this->Lng);
    }

}
