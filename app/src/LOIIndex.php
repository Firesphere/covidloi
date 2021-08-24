<?php

namespace Firesphere\Mini;

use Firesphere\SolrSearch\Indexes\BaseIndex;

class LOIIndex extends BaseIndex
{

    public function getIndexName()
    {
        return 'LOIIndex';
    }
}
