<?php

namespace Firesphere\Mini;

use SilverStripe\Assets\Image;
use SilverStripe\Control\Director;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Environment;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\ValidationException;
use SilverStripe\View\Parsers\URLSegmentFilter;
use Wilr\GoogleSitemaps\Extensions\GoogleSitemapExtension;

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
 * @property string $ExposureType
 * @property int $CityID
 * @property int $SuburbID
 * @property int $MapID
 * @property int $MoHCodeID
 * @method City City()
 * @method Suburb Suburb()
 * @method Image Map()
 * @method MoHCode MoHCode()
 * @method DataList|LocTime[] Times()
 * @mixin GoogleSitemapExtension
 */
class Location extends DataObject
{

    private static $table_name = 'Location';

    private static $db = [
        'Name'         => 'Varchar(255)',
        'Address'      => 'Text',
        'Help'         => 'Text',
        'Added'        => 'Datetime',
        'Lat'          => 'Varchar(50)',
        'Lng'          => 'Varchar(50)',
        'LastUpdated'  => 'Datetime',
        'ExposureType' => 'Varchar(255)'
    ];

    private static $has_one = [
        'City'    => City::class,
        'Suburb'  => Suburb::class,
        'Map'     => Image::class,
        'MoHCode' => MoHCode::class
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
        'Address',
        'Times.Count',
        'Added.Nice',
        'ImagePreview'
    ];

    private static $owns = [
        'Map'
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
        $existing = self::get()->filter($filter)->first();
        $write = false;

        if (!$existing) {

            $existing = self::create($data);

            if (strpos($data['Name'], 'Bus') === false &&
                strpos($data['Name'], 'Train') === false &&
                strpos($data['Name'], 'Public Bus') === false &&
                strpos($data['Name'], 'Flight') === false
            ) {
                $city = City::findOrCreate($data);
                $existing->CityID = $city[0];
                $existing->SuburbID = $city[1];
            }

            $write = true;
        }


        if (!$existing->Lat) {
            $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s,New%%20Zealand&key=%s';

            $result = file_get_contents(sprintf($url,
                Convert::raw2url(sprintf('%s,%s', $data['Address'], $data['City'])),
                Environment::getEnv('MAPSKEY')));

            $result = json_decode($result);
            if (count($result->results)) {
                $existing->Lat = $result->results[0]->geometry->location->lat;
                $existing->Lng = $result->results[0]->geometry->location->lng;
                $write = true;
            }
        }

        if (!$existing->MoHCodeID) {
            $existing->MoHCodeID = $data['MoHCodeID'];
            $write = true;
        }

        if (!$existing->ExposureType) {
            $existing->ExposureType = $data['ExposureType'];
            $write = true;
        }

        $id = $existing->ID;
        if ($write) {
            $id = $existing->write();
        }
        $new = LocTime::findOrCreate($data, $id);

        if ($new || !$existing->LastUpdated) {

            $lastUpdate = $existing->Times()->sort('Day DESC, StartTime DESC')->first();

            $existing->LastUpdated = strtotime($lastUpdate->Day . ' ' . $lastUpdate->StartTime);

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

    public function onBeforeWrite()
    {
        $this->getMapData();
        parent::onBeforeWrite();
    }

    /**
     * @throws ValidationException
     */
    public function getMapData(): void
    {
        if ($this->Lat && $this->Lng && !$this->Map()->exists()) {
            $file = Image::create();
            $address = sprintf('%s, %s, New Zealand', $this->Address, $this->City()->Name);
            $url = "https://maps.googleapis.com/maps/api/staticmap?";
            $params = [
                'center'  => $address,
                'zoom'    => 13,
                'size'    => '600x300',
                'maptype' => 'roadmap',
                'markers' => sprintf('color:red|label:L|%s,%s', $this->Lat, $this->Lng),
                'key'     => Environment::getEnv('MAPSKEY')
            ];
            $fName = URLSegmentFilter::singleton()->filter(uniqid() . '-' . $this->Name) . '.png';
            $link = sprintf('%s%s', $url, http_build_query($params));
            $fContent = file_get_contents($link);
            $file->setFromString($fContent, $fName);
            $file->write();
            $file->publishSingle();
            $file->publishFile();

            $this->MapID = $file->ID;
        }
    }

    public function getRSSDescription()
    {
        return $this->getDescription() . sprintf("<br /><img src='%s' />", $this->Map()->getAbsoluteURL());

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

    public function AbsoluteLink()
    {
        return Director::absoluteURL($this->Link());
    }

    public function Link()
    {
        return sprintf('/#%s', $this->ID);
    }

    public function getImagePreview()
    {
        if ($this->Map()->exists()) {
            return $this->Map()->Fill(200, 100);
        }
    }
}
