<?php

namespace Firesphere\Mini;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBDatetime;

class Importer extends BuildTask
{

    protected static $map = [
        'Name',
        'Address',
        'Day',
        'Times',
        'Help',
        'Added'
    ];

    /**
     * @param HTTPRequest $request
     */
    public function run($request)
    {
        $truncate = $request->getVar('truncate');
        if ($truncate) {
            DB::query('TRUNCATE TABLE `Location`');
            DB::query('TRUNCATE TABLE `LocTime`');
            DB::query('TRUNCATE TABLE `MoHCode`');
            DB::query('TRUNCATE TABLE `Suburb`');
            DB::query('TRUNCATE TABLE `City`');
        }
        $this->getAPI();
    }

    protected function getAPI()
    {
        $data = file_get_contents('https://api.integration.covid19.health.nz/locations/v1/current-locations-of-interest');
        $data = json_decode($data, true);
        $lastUpdated = DBDatetime::now();

        foreach ($data['items'] as $item) {

            $datatem = [
                'Name'          => $item['eventName'],
                'Address'       => $item['location']['address'],
                'City'          => $item['location']['city'],
                'Suburb'        => $item['location']['suburb'],
                'Help'          => $item['publicAdvice'],
                'Added'         => date('Y-m-d H:i:s', strtotime($item['publishedAt'])),
                'Lat'           => $item['location']['latitude'],
                'Lng'           => $item['location']['longitude'],
                'LastUpdated'   => $item['updatedAt'] ?? $lastUpdated,
                'startDateTime' => $item['startDateTime'],
                'endDateTime'   => $item['endDateTime']
            ];

            Location::findOrCreate($datatem);
        }
    }
}
