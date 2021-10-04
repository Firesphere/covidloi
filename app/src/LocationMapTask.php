<?php

namespace Firesphere\Mini;

use SilverStripe\Dev\BuildTask;

class LocationMapTask extends BuildTask
{

    public function run($request)
    {
        $locations = Location::get();

        /** @var Location $location */
        foreach ($locations as $location) {
            if (!$location->MapID) {
                $location->getMapData();
                $location->write();
            }
        }
    }
}
