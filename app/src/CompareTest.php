<?php

namespace Firesphere\Mini;

use ElliotSawyer\NZStreets\Address;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DataList;

class CompareTest extends BuildTask
{

    public function run($request)
    {
        /** @var DataList|Location $orig */
        $orig = Location::get();

        foreach ($orig as $location) {
            print_r($location->Address . PHP_EOL);
//            Address::get()->filter()
        }
    }
}
