<?php

namespace Firesphere\Mini;

use SilverStripe\Core\Convert;
use SilverStripe\Core\Environment;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;

/**
 * Class \Firesphere\Mini\Location
 *
 * @property string $MoHCode
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
        'MoHCode'     => 'Varchar(50)',
        'Name'        => 'Varchar(50)',
        'Address'     => 'Text',
        'Help'        => 'Text',
        'Added'       => 'Date',
        'Lat'         => 'Varchar(50)',
        'Lng'         => 'Varchar(50)',
        'LastUpdated' => 'Datetime'
    ];

    private static $has_one = [
        'City' => City::class
    ];

    private static $has_many = [
        'Times' => LocTime::class
    ];

    private static $indexes = [
        'MoHCode' => true,
        'Name'    => true,
        'Lat'     => true,
        'Lng'     => true
    ];

    public static function findOrCreate($data)
    {
        list($name, $addr) = self::formatNameAddr($data);
        $filter = ['Address' => $addr];
        if (strpos('Bus', $name) === 0 || strpos('Train', $name) === 0 || strpos('Public Bus', $name) === 0) {
            $filter = ['Name' => $name];
        }
        $existing = Location::get()->filter($filter)->first();

        if (!$existing) {

            $existing = Location::create([
                'Name'    => $name,
                'Address' => $addr,
                'Help'    => isset($data[5]) ? mb_convert_encoding($data[4], 'UTF-8') : '',
                'Added'   => strtotime(end($data)),
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

            $existing = Location::get()->byID($id);
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

        return array($name, $addr);
    }

    public static function findOrCreateByLatLng($data)
    {
        list($name, $addr) = self::formatNameAddr($data);
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s+New+Zealand&key=%s';

        $result = file_get_contents(sprintf($url, Convert::raw2url($addr),
            Environment::getEnv('MAPSKEY')));

        $result = json_decode($result, 1);

        if (isset($result['results'][0])) {
            $lat = $result['results'][0]['geometry']['location']['lat'];
            $lng = $result['results'][0]['geometry']['location']['lng'];
            $existing = Location::get()
                ->filter(['Lat' => trim($lat), 'Lng' => trim($lng)]);

            if (!$existing->exists()) {
                $existing = Location::create(
                    [
                        'Name'    => $name,
                        'Address' => $addr,
                        'Help'    => isset($data[5]) ? mb_convert_encoding($data[4], 'UTF-8') : '',
                        'Added'   => strtotime(end($data)),
                        'Lat'     => $lat,
                        'Lng'     => $lng
                    ]
                );
                $existing->CityID = City::findOrCreate($result['results'][0]['address_components']);
            } else {
                $existing = $existing->first();
            }

            $id = $existing->write();
            $existing = Location::get()->byID($id);


            $id = $existing->ID;

            LocTime::findOrCreate($data, $id);

            $lastUpdate = $existing->Times()->sort('Day DESC, StartTime DESC')->first();

            $existing->LastUpdated = strtotime($lastUpdate->Day . ' ' . $lastUpdate->StartTime);
            $existing->write();
        }
    }

    public function getDescription()
    {
        $return = "<b>$this->Name</b><br />$this->Address<br /><br />";
        foreach ($this->Times() as $time) {
            $return .= $time->dbObject('Day')->Nice() . ': ';
            $return .= $time->dbObject('StartTime')->Nice() . ' - ';
            $return .= $time->dbObject('EndTime')->Nice() . '<br />';
        }
        $help = nl2br($this->Help);
        $help = str_replace(PHP_EOL, "", $help);
        $return .= "<b>$help</b>";

        return $return;
    }
}
