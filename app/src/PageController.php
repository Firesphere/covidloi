<?php

namespace {

    use Firesphere\Mini\Location;
    use Firesphere\Mini\LocTime;
    use SilverStripe\CMS\Controllers\ContentController;
    use SilverStripe\Control\Director;
    use SilverStripe\Control\RSS\RSSFeed;

    /**
 * Class \PageController
 *
 * @property Page dataRecord
 * @method Page data()
 */
class PageController extends ContentController
    {
        /**
         * An array of actions that can be accessed via a request. Each array element should be an action name, and the
         * permissions or conditions required to allow the user to access it.
         *
         * <code>
         * [
         *     'action', // anyone can access this action
         *     'action' => true, // same as above
         *     'action' => 'ADMIN', // you must have ADMIN permissions to access this action
         *     'action' => '->checkAction' // you can only access this action if $this->checkAction() returns true
         * ];
         * </code>
         *
         * @var array
         */
        private static $allowed_actions = ['rss'];

        protected function init()
        {
            parent::init();

            $tmp = Location::get()->exclude([
                'Address:PartialMatch' => 'Bus'
            ])
                ->column('Name');
            $tmp = array_unique($tmp);
            $this->Locations = Location::get()->filter(['Name' => $tmp]);
            // You can include any CSS or JS required by your project here.
            // See: https://docs.silverstripe.org/en/developer_guides/templates/requirements/
        }

        public function rss($request)
        {
            $data = LocTime::get()->sort('Day DESC, StartTime DESC');
            $feed = new RSSFeed(
                $data,
                Director::absoluteBaseURL(),
                'Locations of Interest',
                null,
                'getName',
                'getDescription'
            );

            return $feed->outputToBrowser();
        }
    }
}
