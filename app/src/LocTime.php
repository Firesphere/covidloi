<?php

namespace Firesphere\Mini;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;

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

    public static function findOrCreate($data, $id)
    {
        $time = explode('-', $data['Times']);
        $locTime['Day'] = date('Y-m-d', strtotime($data['Day']));
        $locTime['StartTime'] = date('H:i:s', strtotime($time[0]));
        $locTime['EndTime'] = isset($time[1]) ? date('H:i:s', strtotime($time[1])) : null;
        $locTime['LocationID'] = $id;

        $find = LocTime::get()->filter($locTime);

        if (!$find->exists()) {
            LocTime::create($locTime)->write();
        }
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
        return
            sprintf("<h3>%s</h3><b>Date:</b> %s<br /><b>Time:</b> %s-%s<br /><b>What to do:</b><br />%s",
                htmlspecialchars_decode($this->Location()->Address),
                $this->dbObject('Day')->Nice(),
                $this->dbObject('StartTime')->Nice(),
                $this->dbObject('EndTime')->Nice(),
                htmlspecialchars_decode(trim($this->Location()->Help))
            );
    }
}
