<?php

namespace Firesphere\Mini;

use SilverStripe\Admin\ModelAdmin;

/**
 * Class \Firesphere\Mini\LocationAdmin
 *
 */
class LocationAdmin extends ModelAdmin
{

    private static $url_segment = 'location';

    private static $menu_title = 'Locations';

    private static $managed_models = [
        Location::class,
        City::class,
        MoHCode::class,
    ];

}
