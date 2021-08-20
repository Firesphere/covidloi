<?php

namespace Firesphere\Mini;

use SilverStripe\ORM\DataObject;

/**
 * Class \Firesphere\Mini\MoHCode
 *
 * @property string $Code
 */
class MoHCode extends DataObject
{

    private static $table_name = 'MoHCode';

    private static $db = [
        'Code' => 'Varchar(25)'
    ];
}
