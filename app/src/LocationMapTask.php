<?php

namespace Firesphere\Mini;

use SilverStripe\Dev\BuildTask;

class LocationMapTask extends BuildTask
{

    protected $enabled = false;

    public function run($request)
    {
        $locations = Location::get();

        /** @var Location $location */
        foreach ($locations as $location) {
            if (!$location->Map()->exists()) {
                $location->getMapData();
                $location->write();
            }
        }
    }
}
