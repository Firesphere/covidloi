<?php

namespace {

    use Firesphere\Mini\Location;
    use Firesphere\Mini\LocTime;
    use SilverStripe\CMS\Controllers\ContentController;
    use SilverStripe\Control\Director;
    use SilverStripe\Control\RSS\RSSFeed;
    use SilverStripe\View\Requirements;

    /**
 * Class \PageController
 *
 * @property Page dataRecord
 * @method Page data()
 */
    class PageController extends ContentController
    {

        protected $Locations;
        protected $Pages;
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
        private static $allowed_actions = ['rss', 'json'];

        protected function init()
        {
            parent::init();
            Requirements::set_force_js_to_bottom(true);
            Requirements::javascript('themes/simple/dist/main.js');
            Requirements::css('themes/simple/dist/main.css');

            $this->Pages = Page::get()->filter(['ShowInMenus' => true]);
            $this->Locations = Location::get()
                ->exclude(['Lat:GreaterThan' => 0])
                ->exclude(['Lat' => null]);
        }

        public function rss($request)
        {
            $data = $this->getDataItems($request);
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

        public function json($request)
        {
            $this->Locations = Location::get();

            $this->getResponse()->addHeader('Content-Type', 'application/json');

            return $this->renderWith('Json');

        }

        /**
         * @param $request
         * @return \SilverStripe\ORM\DataList
         */
        protected function getDataItems($request): \SilverStripe\ORM\DataList
        {
            $vars = $request->getVars();
            $filter = [];
            $sort = 'Added DESC, Day DESC, StartTime DESC';
            if (count($vars)) {
                if (isset($vars['location'])) {
                    $filter['Location.City.Name'] = ucfirst($request->getVar('location'));
                }
                if (isset($vars['date'])) {
                    $filter['Day'] = date('2021-m-d', strtotime($vars['date']));
                }
            }

            $data = LocTime::get()
                ->innerJoin('Location', 'Location.ID = LocTime.LocationID')
                ->filter($filter)
                ->sort($sort);

            return $data;
        }
    }
}
