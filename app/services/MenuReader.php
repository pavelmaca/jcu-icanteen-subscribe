<?php
/**
 * Created by IntelliJ IDEA.
 * User: Pavel
 * Date: 17.12.2016
 * Time: 18:27
 */

namespace App\Services;


use App\Model\Meal;
use App\Model\MealType;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Nette\SmartObject;
use Nette\Utils\DateTime;

class MenuReader
{
    use SmartObject;

    /**
     * @return Meal[]
     */
    public function getCurrentMealList()
    {
        $html = $this->readPage('Studentska.html');
        if (!$html) {
            return [];
        }

        return $this->parseHtml($html);
    }

    protected function readPage($pageName)
    {
        // setup guzzle client
        $httpClient = new Client([
            'base_uri' => 'http://menza.jcu.cz',
            RequestOptions::CONNECT_TIMEOUT => 7,
            RequestOptions::TIMEOUT => 10,
        ]);

        // create request
        $result = $httpClient->get($pageName);

        if ($result->getStatusCode() !== 200) {
            return false;
        }

        return $result->getBody()->getContents();
    }

    public function parseHtml($html)
    {
        // fix encoding to UTF-8
        $fixedHtml = iconv('WINDOWS-1250', 'UTF-8', $html);

        // split to lines
        $lines = preg_split('~\n\r\n~', $fixedHtml);

        $meals = [];
        $currentDate = null;
        foreach ($lines as $line) {
            if (!preg_match('~<TR><TD>(?P<col1>[^<]+)<\/TD><TD>(?P<col2>[^<]+)<\/TD><TD>(?P<col3>[^<]+)<\/TD><TD>(?P<col4>[^<]+)<\/TD><\/TR>~', $line, $cols)) {
                continue;
            }

            if($cols['col4'] == '&nbsp;') {
                continue;
            }

            if (preg_match('~(?P<date>[0-9]{1,2}\.[0-9]{1,2}.[0-9]{4})~', $cols['col1'], $dateResult)) {
                $currentDate = DateTime::createFromFormat('j.n.Y H:i:s', $dateResult['date'] . ' 00:00:00');
            }

            if ($currentDate == null) {
                continue;
            }
            
            $meals[] = new Meal($cols['col4'], $currentDate, MealType::getFromString($cols['col2']));
        }

        return $meals;
    }
}