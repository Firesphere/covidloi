<?php

namespace Firesphere\Mini;

use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * Class \Firesphere\Mini\LocTime
 *
 * @property string $Day
 * @property string $StartTime
 * @property string $EndTime
 * @property int $LocationID
 * @method Location Location()
 */
class LocTime extends DataObject
{

    private static $table_name = 'LocTime';

    private static $db = [
        'Day'       => 'Date',
        'StartTime' => 'Time',
        'EndTime'   => 'Time'
    ];

    private static $has_one = [
        'Location' => Location::class
    ];

    private static $summary_fields = [
        'Day.Nice',
        'StartTime.Nice',
        'EndTime.Nice'
    ];

    private static $casting = [
        'LastUpdated' => 'Datetime',
    ];

    protected static $html = "<h3>%s</h3>
<b>Date:</b> %s<br />
<b>Time:</b> %s-%s<br />
<b>What to do:</b><br />%s";
    protected static $html_image = "<br /><figure id='map-%s'>
<img src='%s' alt='Map for %s' title='Map for %s' />
<figcaption>%s</figcaption>
</figure>";

    public static function findOrCreate($data, $id)
    {
        $locTime['Day'] = date('Y-m-d', strtotime($data['startDateTime']));
        $locTime['StartTime'] = date('H:i:s', strtotime($data['startDateTime']));
        $locTime['EndTime'] = isset($data['endDateTime']) ? date('H:i:s', strtotime($data['endDateTime'])) : null;
        $locTime['LocationID'] = $id;

        $find = LocTime::get()->filter($locTime);

        if (!$find->exists()) {
            return LocTime::create($locTime)->write();
        }

        return false;
    }


    public function Link()
    {
        return sprintf('/#%s', $this->Location()->ID);
    }

    public function Date()
    {
        return DBDatetime::create()->setValue($this->Location()->Added);
    }

    public function LastUpdated()
    {
        return DBDatetime::create()->setValue($this->Day . ' ' . $this->StartTime);
    }


    public function getName()
    {
        return $this->Location()->Name;
    }

    public function getDescription()
    {
        $location = $this->Location();

        $content = sprintf(
            static::$html,
            Convert::raw2att($location->Address),
            $this->dbObject('Day')->Nice(),
            $this->dbObject('StartTime')->Nice(),
            $this->dbObject('EndTime')->Nice(),
            trim($location->Help)
        );

        if ($location->MapID) {
            $content .= sprintf(
                static::$html_image,
                $this->ID,
                $location->Map()->AbsoluteLink(),
                Convert::raw2att($location->Name),
                Convert::raw2att($location->Name),
                sprintf(
                    '%s, %s %s',
                    Convert::raw2att($location->Name),
                    $location->Lat,
                    $location->Lng
                )
            );
        }

        return DBHTMLText::create()->setValue($content);
    }
}
