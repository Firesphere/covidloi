<?php

namespace {

    use Firesphere\Mini\City;
    use Firesphere\Mini\Location;
    use Firesphere\Mini\LocTime;
    use Firesphere\Mini\LOIIndex;
    use Firesphere\Mini\SearchForm;
    use Firesphere\SolrSearch\Queries\BaseQuery;
    use SilverStripe\CMS\Controllers\ContentController;
    use SilverStripe\Control\Director;
    use SilverStripe\Control\HTTPRequest;
    use SilverStripe\Control\RSS\RSSFeed;
    use SilverStripe\Core\Environment;
    use SilverStripe\Forms\DropdownField;
    use SilverStripe\Forms\FieldList;
    use SilverStripe\Forms\FormAction;
    use SilverStripe\Forms\TextField;
    use SilverStripe\ORM\DataList;
    use SilverStripe\ORM\FieldType\DBDatetime;
    use SilverStripe\ORM\FieldType\DBHTMLText;
    use SilverStripe\View\Requirements;

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
        private static $allowed_actions = ['rss', 'search'];
        protected $Locations;
        protected $Pages;
        protected $matomo;

        /**
         * @param $request
         * @return DBHTMLText
         * @throws Exception
         */
        public function rss($request)
        {
            $this->matomo->doTrackPageView('RSS Feed');
            $data = $this->getDataItems($request);
            $feed = RSSFeed::create(
                $data,
                Director::absoluteBaseURL(),
                'Locations of Interest',
                'Locations of Interest regarding the COVID-19 spread in New Zealand.',
                'getName',
                'getDescription',
                null,
                DBDatetime::create()->setValue($data->max('LastEdited'))->Rfc822()
            );

            $this->matomo->doBulkTrack();

            return $feed->outputToBrowser();
        }

        /**
         * @param HTTPRequest $request
         * @return DataList
         */
        protected function getDataItems($request): DataList
        {
            $vars = $request->allParams();
            $gets = $request->getVars();
            $filter = [];
            $sort = 'Added DESC, Day DESC, StartTime DESC';
            $list = LocTime::get();
            if ($vars['ID'] == 'location') {
                $filter['Location.City.Name'] = ucfirst($vars['OtherID']);
                $this->matomo->doTrackEvent('RSS', 'location', $vars['OtherID'] ?? 'none');
            } else {
                $list = $list->innerJoin('Location', 'Location.ID = LocTime.LocationID');
            }

            if (isset($gets['days'])) {
                if (!isset($filter['Location.City.Name'])) {
                    $sort = 'Location.Added DESC, Day DESC, StartTime DESC';
                }
                $filter['Location.Added:GreaterThan'] = date('Y-m-d 00:00:00',
                    strtotime('-' . (int)$gets['days'] . ' days'));
                $this->matomo->doTrackEvent('RSS', 'range', 'days', (int)$gets['days']);
            }

            if (isset($gets['sort']) && $gets['sort'] == 'days') {
                $sort = 'Day DESC, StartTime DESC';
                $this->matomo->doTrackEvent('RSS', 'sort', 'days');
            }

            $list = $list->filter($filter)
                ->sort($sort);

            $limit = 250;
            if (isset($gets['limit'])) {
                $limit = (int)$gets['limit'];
                $this->matomo->doTrackEvent('RSS', 'range', 'limit', (int)$gets['limit']);
            }

            return $list->limit($limit);
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

        public function AdvancedSearchForm()
        {
            $fields = FieldList::create([
                $txt = TextField::create('query', 'Query'),
                $city = DropdownField::create('City', 'City', City::get())
            ]);
            $city->setEmptyString('-- Select a city --');
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
                $start = $query['start'] ?? 0;
                $baseQuery = new BaseQuery();
                $baseQuery->addTerm($query['query']);
                if (isset($query['City'])) {
                    $baseQuery->addFilter('Location.City.ID', $query['City']);
                }
                $baseQuery->setStart($start);
                $baseQuery->setFacetsMinCount(1);
                $index = new LOIIndex();
                $this->Results = $index->doSearch($baseQuery);
            }

            return $this;
        }

        protected function init()
        {
            parent::init();
            if ($this->getURLParams()['Action'] !== 'rss') {
                Requirements::set_force_js_to_bottom(true);
                Requirements::javascript('themes/simple/dist/main.js');
                Requirements::css('themes/simple/dist/main.css');
                RSSFeed::linkToFeed('/home/rss', 'NZ LOI RSS Feed');

                $this->Pages = Page::get()->filter(['ShowInMenus' => true]);
                $this->Locations = Location::get()
                    ->exclude(['Lat:GreaterThan' => 0])
                    ->exclude(['Lat' => null]);
            } else {
                $this->getMatomo($this->getRequest());
            }
        }

        /**
         * @param HTTPRequest $request
         */
        protected function getMatomo(HTTPRequest $request)
        {
            $this->matomo = new MatomoTracker(16, 'https://piwik.casa-laguna.net');
            $this->matomo->doBulkRequests = true;
            $this->matomo->setTokenAuth(Environment::getEnv('MATOMOTOKEN'));
            $this->matomo->disableSendImageResponse();
            $this->matomo->setUserAgent($request->getHeader('user-agent'));
            $this->matomo->setIp($request->getVar('X-Forwarded-For') ?? $request->getIP());
            $this->matomo->setBrowserLanguage($request->getHeader('accept-language'));
        }

    }
}
