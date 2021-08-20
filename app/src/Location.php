<?php

namespace Firesphere\Mini;

use SilverStripe\Core\Convert;
use SilverStripe\Core\Environment;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;

/**
 * Class \Firesphere\Mini\Location
 *
 * @property string $Name
 * @property string $Address
 * @property string $Help
 * @property string $Added
 * @property string $Lat
 * @property string $Lng
 * @property string $LastUpdated
 * @method DataList|LocTime[] Times()
 */
class Location extends DataObject
{

    private static $table_name = 'Location';

    private
    static $db = [
        'Name'      => 'Varchar(255)',
        'Address'   => 'Text',
        'Help'      => 'Text',
        'Added'     => 'Date',
        'Lat'       => 'Varchar(255)',
        'Lng'       => 'Varchar(255)',
        'LastUpdated' => 'Datetime'
    ];

    private static $has_one = [
        'City' => City::class
    ];

    private static $has_many = [
        'Times' => LocTime::class
    ];

    public
    static function findOrCreate(
        $data
    ) {
        if (strpos($data[0], '&') !== false) {
            $name = trim(mb_convert_encoding($data[0], 'UTF-8', 'HTML-ENTITIES'));
        } else {
            $name = trim(mb_convert_encoding($data[0], 'UTF-8'));
        }
        if (strpos($data[1], '&') !== false) {
            $addr = mb_convert_encoding($data[1], 'UTF-8', 'HTML-ENTITIES');
        } else {
            $addr = mb_convert_encoding($data[1], 'UTF-8');
        }
        $name = trim($name);
        $addr = trim($addr);
        $existing = static::get()->filter(
            [
                'Name'      => $name,
            ]
        )->first();

        if (!$existing) {

            $existing = static::create([
                'Name'      => $name,
                'Address'   => $addr,
                'Help'      => isset($data[5]) ? mb_convert_encoding($data[4], 'UTF-8') : '',
                'Added'     => strtotime(end($data)),
            ]);

            if (strpos('Bus ', $name) !== 0) {
                $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s+New+Zealand&key=%s';

                $result = file_get_contents(sprintf($url, Convert::raw2url($existing->Address),
                    Environment::getEnv('MAPSKEY')));

                $result = json_decode($result, 1);

                if (isset($result['results'][0])) {
                    $existing->Lat = $result['results'][0]['geometry']['location']['lat'];
                    $existing->Lng = $result['results'][0]['geometry']['location']['lng'];
                    $existing->CityID = City::findOrCreate($result['results'][0]['address_components']);

                }
            }
            $id = $existing->write();

            $existing = static::get()->byID($id);
        }

        $id = $existing->ID;

        LocTime::findOrCreate($data, $id);


        $lastUpdate = $existing->Times()->sort('Day DESC, StartTime DESC')->first();

        $existing->LastUpdated = strtotime($lastUpdate->Day . ' ' . $lastUpdate->StartTime);
    }
}
