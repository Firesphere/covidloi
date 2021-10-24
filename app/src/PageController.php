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
    use SilverStripe\Control\RSS\RSSFeed;
    use SilverStripe\Forms\DropdownField;
    use SilverStripe\Forms\FieldList;
    use SilverStripe\Forms\FormAction;
    use SilverStripe\Forms\TextField;
    use SilverStripe\ORM\DataList;
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
        private static $allowed_actions = ['rss', 'search'];

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

        /**
         * @param $request
         * @return DBHTMLText
         */
        public function rss($request)
        {
            $data = $this->getDataItems($request);
            $feed = RSSFeed::create(
                $data,
                Director::absoluteBaseURL(),
                'Locations of Interest',
                null,
                'getName',
                'getDescription',
                $data->max('LastEdited')
            );

            return $feed->outputToBrowser();
        }

        /**
         * @param $request
         * @return DataList
         */
        protected function getDataItems($request): DataList
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

    }
}
