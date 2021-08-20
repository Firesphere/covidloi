<?php

namespace Firesphere\Mini;

use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;

/**
 * Class \Firesphere\Mini\City
 *
 * @property string $Name
 * @method DataList|Location[] Locations()
 */
class City extends DataObject
{

    private static $table_name = 'City';

    private static $db = [
        'Name' => 'Varchar(255)'
    ];

    private static $has_many = [
        'Locations' => Location::class
    ];

    public static function findOrCreate($city)
    {
        print_r($city);
        foreach ($city as $value) {
            if ($value['types'][0] == 'administrative_area_level_1') {
                $city = $value['long_name'];
                break;
            }
        }
        if (is_array($city)) {
            return;
        }
        $exist = self::get()->filter(['Name' => $city])->first();

        if (!$exist) {
            $exist = self::create();
            $exist->Name = $city;
            return $exist->write();
        }

        return $exist->ID;
    }

}
