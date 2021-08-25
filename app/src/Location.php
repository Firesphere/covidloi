<?php

namespace Firesphere\Mini;

use SilverStripe\Control\Director;
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
 * @property int $SuburbID
 * @method City City()
 * @method Suburb Suburb()
 * @method DataList|LocTime[] Times()
 * @mixin GoogleSitemapExtension
 */
class Location extends DataObject
{

    private static $table_name = 'Location';

    private static $db = [
        'Name'        => 'Varchar(255)',
        'Address'     => 'Text',
        'Help'        => 'Text',
        'Added'       => 'Datetime',
        'Lat'         => 'Varchar(50)',
        'Lng'         => 'Varchar(50)',
        'LastUpdated' => 'Datetime'
    ];

    private static $has_one = [
        'City'   => City::class,
        'Suburb' => Suburb::class
    ];

    private static $has_many = [
        'Times' => LocTime::class,
    ];

    private static $indexes = [
        'Name'        => true,
        'Lat'         => true,
        'Lng'         => true,
        'LastUpdated' => true
    ];

    private static $summary_fields = [
        'Name',
        'Help',
        'Times.Count',
        'Added.Nice'
    ];

    private static $casting = [
        'getSpatial' => 'Spatial',
        'Spatial'    => 'Spatial'
    ];

    public static function findOrCreate($data)
    {
        $data = self::formatNameAddr($data);
        $filter = ['Address' => $data['Address']];
        if (strpos($data['Name'], 'Bus') === 0 ||
            strpos($data['Name'], 'Train') === 0 ||
            strpos($data['Name'], 'Public Bus') === 0 ||
            strpos($data['Name'], 'Flight') === 0
        ) {
            $filter = ['Name' => $data['Name']];
        }
        $existing = Location::get()->filter($filter)->first();

        if (!$existing) {

            $existing = Location::create($data);

            if (strpos($data['Name'], 'Bus') === false &&
                strpos($data['Name'], 'Train') === false &&
                strpos($data['Name'], 'Public Bus') === false &&
                strpos($data['Name'], 'Flight') === false
            ) {
                $result = self::getLatLng($data);

                if (isset($result['results'][0])) {
                    $city = City::findOrCreate($result['results'][0]['address_components']);
                    $existing->Lat = $result['results'][0]['geometry']['location']['lat'];
                    $existing->Lng = $result['results'][0]['geometry']['location']['lng'];
                    $existing->CityID = $city[0];
                    $existing->SuburbID = $city[1];
                }
            }
            $existing->write();
        }

        $id = $existing->ID;

        $new = LocTime::findOrCreate($data, $id);

        if ($new || !$existing->Help) {
            echo "new time or help " . $data['Name'];

            $lastUpdate = $existing->Times()->sort('Day DESC, StartTime DESC')->first();

            $existing->LastUpdated = strtotime($lastUpdate->Day . ' ' . $lastUpdate->StartTime);
            if (!$existing->Help) {
                $existing->Help = $data['Help'];
            }
            $existing->write();
        }
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
                $city = City::findOrCreate($result['results'][0]['address_components']);
                $existing = Location::create($data);
                $existing->CityID = $city[0];
                $existing->SuburbID = $city[1];
                $existing->write();
            }
            $id = $existing->ID;

            $new = LocTime::findOrCreate($data, $id);

            if ($new || !$existing->Help) {
                $lastUpdate = $existing->Times()->sort('Day DESC, StartTime DESC')->first();

                $existing->LastUpdated = strtotime($lastUpdate->Day . ' ' . $lastUpdate->StartTime);
                if (!$existing->Help) {
                    $existing->Help = $data['Help'];
                }
                $existing->write();
            }
        }
    }

    /**
     * @param array $existing
     * @return mixed
     */
    protected static function getLatLng($existing)
    {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s,New%%20Zealand&key=%s';

        $result = file_get_contents(sprintf($url, Convert::raw2url($existing['Address']),
            Environment::getEnv('MAPSKEY')));

        return json_decode($result, 1);
    }

    public function getDescription()
    {
        $return = sprintf('<b>%s</b><br />%s<br /><br />',
            $this->Name,
            $this->Address
        );
        foreach ($this->Times() as $time) {
            $return .= sprintf('%s: %s - %s<br />',
                $time->dbObject('Day')->Nice(),
                $time->dbObject('StartTime')->Nice(),
                $time->dbObject('EndTime')->Nice()
            );

        }
        $help = nl2br($this->Help);
        $help = str_replace(PHP_EOL, "", $help);
        $return .= "<br />$help";

        return $return;
    }

    public function getSpatial()
    {
        if (!$this->Lat || !$this->Lng) {
            $this->Lat = 0;
            $this->Lng = 0;
        }

        return sprintf('%s,%s', $this->Lat, $this->Lng);
    }


    public function Date()
    {
        return DBDatetime::create()->setValue($this->Times()->Last()->Day . ' ' . $this->Times()->Last()->StartTime);
    }

    public function Link()
    {
        return sprintf('/#%s', $this->ID);
    }

    public function AbsoluteLink()
    {
        return Director::absoluteURL($this->Link());
    }

}
