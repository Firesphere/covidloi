<?php

namespace Firesphere\Mini;

use SilverStripe\Core\Convert;
use SilverStripe\Core\Environment;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationException;

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

    /**
     * @param array $data
     * @param City $city
     * @return int|void
     * @throws ValidationException
     */
    public static function findOrCreate($data, $city)
    {
        if (!$data['Suburb']) {
            return 0;
        }
        $suburb = $data['Suburb'];
        $exist = Suburb::get()->filter(['Name' => $suburb])->first();

        $write = false;
        if (!$exist) {
            $exist = Suburb::create();
            $exist->Name = $suburb;
            $exist->CityID = $city->ID;
            $write = true;
        }

        if (!$exist->Lat) {
            $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s,New%%20Zealand&key=%s';

            $result = file_get_contents(sprintf($url, Convert::raw2url(sprintf('%s,%s', $suburb, $city->Name)),
                Environment::getEnv('MAPSKEY')));

            $result = json_decode($result);
            if (count($result->results)) {
                $exist->Lat = $result->results[0]->geometry->location->lat;
                $exist->Lng = $result->results[0]->geometry->location->lng;
                $write = true;
            }
        }

        if ($write) {
            return $exist->write();
        }

        return $exist->ID;

    }

    public function getSpatial()
    {
        if (!$this->Lat || !$this->Lng) {
            $this->Lat = $this->Lng = 0;
        }

        return sprintf('%s,%s', $this->Lat, $this->Lng);
    }
}
