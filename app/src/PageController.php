<?php

namespace {

    use Firesphere\Mini\Location;
    use Firesphere\Mini\LocTime;
    use Firesphere\Mini\LOIIndex;
    use Firesphere\Mini\SearchForm;
    use Firesphere\SolrSearch\Queries\BaseQuery;
    use SilverStripe\CMS\Controllers\ContentController;
    use SilverStripe\Control\Director;
    use SilverStripe\Control\RSS\RSSFeed;
    use SilverStripe\Core\Environment;
    use SilverStripe\Forms\FieldList;
    use SilverStripe\Forms\FormAction;
    use SilverStripe\Forms\TextField;
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
        private static $allowed_actions = ['rss', 'json', 'search'];

        protected function init()
        {
            parent::init();
            Requirements::set_force_js_to_bottom(true);
            Requirements::javascript('themes/simple/dist/main.js');
            Requirements::css('themes/simple/dist/main.css');
            RSSFeed::linkToFeed('/home/rss', 'NZ LOI RSS Feed');

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
            if (Director::isLive()) {
                $matomoToken = Environment::getEnv('MATOMOTOKEN');
                $matomo = new MatomoTracker(16, 'https://piwik.casa-laguna.net');
                $matomo->setTokenAuth($matomoToken);
                $matomo->doTrackPageView('NZ LOI JSON Feed');
            }

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
            $vars = $request->allParams();
            $filter = [];
            $sort = 'Added DESC, Day DESC, StartTime DESC';
            $list = LocTime::get();
            if ($vars['ID'] == 'location') {
                $filter['Location.City.Name'] = ucfirst($request->param('OtherID'));
            } else {
                $list = $list->innerJoin('Location', 'Location.ID = LocTime.LocationID');
                $sort = 'Added DESC, Day DESC, StartTime DESC';
            }

            return $list->filter($filter)
                ->sort($sort);
        }

        public function SearchForm()
        {
            $fields = FieldList::create([
                $txt = TextField::create('query', '')
            ]);
            $actions = FieldList::create([
                FormAction::create('search', 'Go')
            ]);
            $txt->setAttribute('placeholder', 'Search');
            $form = SearchForm::create($this, __FUNCTION__, $fields, $actions);
            $url = '/home/search';
            $form->setFormAction($url);

            return $form;
        }

        public function search()
        {
            $query = $this->getRequest()->getVars();
            if (isset($query['query'])) {
                $this->Query = $query['query'];
                $this->dataRecord->Title = 'Search';
                $start = isset($query['start']) ? $query['start'] : 0;
                $baseQuery = new BaseQuery();
                $baseQuery->addTerm($query['query']);
                $baseQuery->setStart($start);
                $baseQuery->setFacetsMinCount(1);
                $index = new LOIIndex();
                $this->Results = $index->doSearch($baseQuery);
            }

            return $this;
        }

    }
}
