<?php

namespace Firesphere\Mini;

/**
 * Class \Firesphere\Mini\RSSFeedPageController
 *
 * @property RSSFeedPage dataRecord
 * @method RSSFeedPage data()
 * @mixin RSSFeedPage
 */
class RSSFeedPageController extends \PageController
{

    public function index()
    {
        return parent::rss($this->request);
    }
}
