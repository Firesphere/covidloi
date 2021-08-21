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
 * @property int $CityID
 * @method City City()
 * @method DataList|LocTime[] Times()
 */
class Location extends DataObject
{

    private static $table_name = 'Location';

    private
    static $db = [
        'Name'        => 'Varchar(50)',
        'Address'     => 'Text',
        'Help'        => 'Text',
        'Added'       => 'Datetime',
        'Lat'         => 'Varchar(50)',
        'Lng'         => 'Varchar(50)',
        'LastUpdated' => 'Datetime'
    ];

    private static $has_one = [
        'City' => City::class
    ];

    private static $has_many = [
        'Times' => LocTime::class,
    ];

    private static $indexes = [
        'MoHCode' => true,
        'Name'    => true,
        'Lat'     => true,
        'Lng'     => true
    ];

    private static $summary_fields = [
        'Name',
        'Help',
        'Times.Count'
    ];

    public static function findOrCreate($data)
    {
        $data = self::formatNameAddr($data);
        $filter = ['Address' => $data['Address']];
        if (strpos('Bus', $data['Name']) === 0 ||
            strpos('Train', $data['Name']) === 0 ||
            strpos('Public Bus', $data['Name']) === 0
        ) {
            $filter = ['Name' => $data['Name']];
        }
        $existing = Location::get()->filter($filter)->first();

        if (!$existing) {

            $existing = Location::create($data);

            if (strpos('Bus', $data['Name']) === false &&
                strpos('Train', $data['Name']) === false &&
                strpos('Public Bus', $data['Name']) === false
            ) {
                $result = self::getLatLng($data);

                if (isset($result['results'][0])) {
                    $existing->Lat = $result['results'][0]['geometry']['location']['lat'];
                    $existing->Lng = $result['results'][0]['geometry']['location']['lng'];
                    $existing->CityID = City::findOrCreate($result['results'][0]['address_components']);
                }
            }
            $existing->write();
        }

        $id = $existing->ID;

        LocTime::findOrCreate($data, $id);

        $lastUpdate = $existing->Times()->sort('Day DESC, StartTime DESC')->first();

        $existing->LastUpdated = strtotime($lastUpdate->Day . ' ' . $lastUpdate->StartTime);
        $existing->write();
    }

    /**
     * @param $data
     * @return array
     */
    protected static function formatNameAddr($data): array
    {
        if (strpos($data['Name'], '&') !== false) {
            $name = trim(mb_convert_encoding($data['Name'], 'UTF-8', 'HTML-ENTITIES'));
        } else {
            $name = trim(mb_convert_encoding($data['Name'], 'UTF-8'));
        }
        if (strpos($data['Address'], '&') !== false) {
            $addr = mb_convert_encoding($data['Address'], 'UTF-8', 'HTML-ENTITIES');
        } else {
            $addr = mb_convert_encoding($data['Address'], 'UTF-8');
        }
        $data['Name'] = trim($name);
        $data['Address'] = trim($addr);

        return $data;
    }

    public static function findOrCreateByLatLng($data)
    {
        $data = self::formatNameAddr($data);
        $result = self::getLatLng($data);

        if (isset($result['results'][0])) {
            $data['Lat'] = $result['results'][0]['geometry']['location']['lat'];
            $data['Lng'] = $result['results'][0]['geometry']['location']['lng'];
            $existing = Location::get()
                ->filter(['Lat' => trim($data['Lat']), 'Lng' => trim($data['Lng'])])
                ->first();

            if (!$existing) {
                $existing = Location::create($data);
                $existing->CityID = City::findOrCreate($result['results'][0]['address_components']);
                $existing->write();
            }
            $id = $existing->ID;

            LocTime::findOrCreate($data, $id);

            $lastUpdate = $existing->Times()->sort('Day DESC, StartTime DESC')->first();

            $existing->LastUpdated = strtotime($lastUpdate->Day . ' ' . $lastUpdate->StartTime);
            $existing->write();
        }

        return $existing->ID;
    }

    /**
     * @param array $existing
     * @return mixed
     */
    protected static function getLatLng(Location $existing)
    {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s+New+Zealand&key=%s';

        $result = file_get_contents(sprintf($url, Convert::raw2url($existing['Address']),
            Environment::getEnv('MAPSKEY')));

        return json_decode($result, 1);
    }
}
