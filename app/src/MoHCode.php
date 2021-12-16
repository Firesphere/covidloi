<?php

namespace Firesphere\Mini;

use SilverStripe\ORM\DataObject;

/**
 * Class \Firesphere\Mini\MoHCode
 *
 * @property string $Code
 * @method Location Location()
 */
class MoHCode extends DataObject
{

    private static $table_name = 'MoHCode';

    private static $db = [
        'Code' => 'Varchar(25)'
    ];

    private static $indexes = [
        'Code' => true
    ];

    private static $belongs_to = [
        'Location' => Location::class
    ];
}
