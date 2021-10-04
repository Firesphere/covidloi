<?php

namespace Firesphere\Mini;

use SilverStripe\Dev\BuildTask;

class LocationMapTask extends BuildTask
{

    public function run($request)
    {
        $locations = Location::get()->filter(['MapID' => 0]);

        /** @var Location $location */
        foreach ($locations as $location) {
            $location->getMapData();
            $location->write();
        }
    }
}
