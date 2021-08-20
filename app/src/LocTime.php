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

    public static function findOrCreate($data, $id)
    {
        $time = explode('-', $data[3]);
        $start = date('H:i:s', strtotime($time[0]));
        $end = isset($time[1]) ? date('H:i:s', strtotime($time[1])) : null;
        $day = date('Y-m-d', strtotime($data[2]));

        $find = LocTime::get()->filter([
            'Day'        => $day,
            'StartTime'  => $start,
            'EndTime'    => $end,
            'LocationID' => $id
        ]);

        if (!$find->exists()) {
            LocTime::create([
                'Day'        => $day,
                'StartTime'  => $start,
                'EndTime'    => $end,
                'LocationID' => $id
            ])->write();
        }
    }


    public function Link()
    {
        return sprintf('/#%s', $this->Location()->ID);
    }

    public function Date()
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
