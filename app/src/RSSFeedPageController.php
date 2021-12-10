<?php

namespace Firesphere\Mini;

use PageController;

/**
 * Class \Firesphere\Mini\RSSFeedPageController
 *
 * @property RSSFeedPage dataRecord
 * @method RSSFeedPage data()
 * @mixin RSSFeedPage dataRecord
 */
class RSSFeedPageController extends PageController
{

    public function index()
    {
        return parent::rss($this->request);
    }
}
