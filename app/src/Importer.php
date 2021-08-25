<?php

namespace Firesphere\Mini;

use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBDatetime;

class Importer extends BuildTask
{

    protected static $map = [
        'Name',
        'Address',
        'Day',
        'Times',
        'Help',
        'Added'
    ];

    /**
     * @param \SilverStripe\Control\HTTPRequest $request
     * @throws \PHPHtmlParser\Exceptions\ChildNotFoundException
     * @throws \PHPHtmlParser\Exceptions\CircularException
     * @throws \PHPHtmlParser\Exceptions\ContentLengthException
     * @throws \PHPHtmlParser\Exceptions\LogicalException
     * @throws \PHPHtmlParser\Exceptions\NotLoadedException
     * @throws \PHPHtmlParser\Exceptions\StrictException
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function run($request)
    {
        $truncate = $request->getVar('truncate');
        if ($truncate) {
            DB::query('TRUNCATE TABLE `Location`');
            DB::query('TRUNCATE TABLE `LocTime`');
            DB::query('TRUNCATE TABLE `MoHCode`');
            DB::query('TRUNCATE TABLE `Suburb`');
            DB::query('TRUNCATE TABLE `City`');
        }
        $this->getMoHWebsite();

        $this->getGithub();
    }

    /**
     * @param $body
     * @return \SilverStripe\ORM\FieldType\DBDate|DBDatetime
     */
    protected function getLastUpdated($body)
    {
        $mohUpdate = $body->find('p.georgia-italic')->text;
        $mohUpdate = explode('updated:', $mohUpdate);
        $mohUpdate = str_replace("&nbsp;", " ", $mohUpdate[1]);
        $mohUpdate = explode(",", $mohUpdate);
        $date = date('Y-m-d', strtotime($mohUpdate[1]));
        $time = date('H:i:s', strtotime($mohUpdate[0]));
        $time = DBDatetime::create()->setValue($date . ' ' . $time);

        return $time;
    }

    /**
     * @throws \PHPHtmlParser\Exceptions\ChildNotFoundException
     * @throws \PHPHtmlParser\Exceptions\CircularException
     * @throws \PHPHtmlParser\Exceptions\ContentLengthException
     * @throws \PHPHtmlParser\Exceptions\LogicalException
     * @throws \PHPHtmlParser\Exceptions\NotLoadedException
     * @throws \PHPHtmlParser\Exceptions\StrictException
     */
    protected function getMoHWebsite()
    {
        $content = file_get_contents('https://www.health.govt.nz/our-work/diseases-and-conditions/covid-19-novel-coronavirus/covid-19-health-advice-public/contact-tracing-covid-19/covid-19-contact-tracing-locations-interest');
        $dom = new \PHPHtmlParser\Dom();
        $dom->loadStr($content);
        $body = $dom->find('div.main-container.container');
        $time = $this->getLastUpdated($body);
        $trs = $body->find('tr');
        foreach ($trs as $tr) {
            $tds = $tr->find('td');
            $data = [];
            $i = 0;
            foreach ($tds as $td) {
                $data[static::$map[$i]] = trim($td->innerText);
                $i++;
            }
            if (count($data)) {
                $data['Help'] = mb_convert_encoding($data['Help'], 'UTF-8');
                $data['Added'] = $time->getValue();
                Location::findOrCreate($data);
            }
        }
    }

    protected function getGithub(): void
    {
        $json = file_get_contents('https://raw.githubusercontent.com/minhealthnz/nz-covid-data/main/locations-of-interest/august-2021/locations-of-interest.geojson');

        $data = json_decode($json, 1);
        $mohCodes = MoHCode::get()->column('Code');
        foreach ($data['features'] as $location) {
            if (!in_array($location['properties']['id'], $mohCodes)) {
                $map = [
                    'Name'    => $location['properties']['Event'],
                    'Address' => $location['properties']['Location'],
                    'Day'     => date('Y-m-d', strtotime(str_replace('/', '-', $location['properties']['Start']))),
                    'Times'   => date('H:i:s',
                            strtotime(str_replace('/', '-', $location['properties']['Start']))) . '-' .
                        date('H:i:s', strtotime(str_replace('/', '-', $location['properties']['End']))),
                    'Help'    => $location['properties']['Advice'],
                    'Added'   => date('Y-m-d H:i:s')
                ];
                $mohCodes[] = $location['properties']['id'];
                MoHCode::create(['Code' => $location['properties']['id']])->write();
                Location::findOrCreateByLatLng($map);
            }
        }
    }
}
