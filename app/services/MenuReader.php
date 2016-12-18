<?php
/**
 * Created by IntelliJ IDEA.
 * User: Pavel
 * Date: 17.12.2016
 * Time: 18:27
 */

namespace App\Services;


use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Nette\Utils\DateTime;

class MenuReader
{
    public function readCurrent()
    {
        // setup guzzle client
        $httpClient = new Client([
            'base_uri' => 'http://menza.jcu.cz',
            RequestOptions::CONNECT_TIMEOUT => 7,
            RequestOptions::TIMEOUT => 10,
        ]);

        // create request
        $result = $httpClient->get('Studentska.html');

        if ($result->getStatusCode() !== 200) {
            return false;
        }

        // rpocess html as dom object
        $html = $result->getBody()->getContents();
        $DOM = new \DOMDocument();
        $DOM->loadHTML($html);

        $first = true; // first row indicator
        $date = false; // current date in row
        $data = [];

        $items = $DOM->getElementsByTagName('tr');
        foreach ($items as $tr) {
            /** @var $tr \DOMElement */

            if ($first) {
                $first = false;
                continue;
            }

            $colCount = 0;
            $skip = false;
            foreach ($tr->getElementsByTagName('td') as $td) {
                /** @var $td \DOMElement */

                $str = $td->textContent;
                switch ($colCount) {
                    case 0:
                        // fisr col contains date
                        if (preg_match('~^([0-9]{1,2}\.){2}[0-9]{4}$~', $str)) {
                            $date = DateTime::createFromFormat('j.n.Y H:i:s', $str . ' 00:00:00');
                        }
                        break;
                    case 1:
                        // type of meal
                        $str = self::fixEncoding($str);
                        if (preg_match('~^(Specialita) [0-9]~', $str)) {
                            $type = $str;
                        } else {
                            $skip = true;
                        }
                        break;
                    case 3:
                        // name of a meal
                        $name = $str;
                        break;
                }
                $colCount++;
            }

            // skip non interested meals
            if ($skip) {
                continue;
            }

            $data[] = [$date, $type, self::fixEncoding($name)];
        }

        return $data;
    }

    /**
     * Fix some encoding problems with data
     * @param $str
     * @return mixed
     */
    protected static function fixEncoding($str)
    {
        $str = mb_convert_encoding($str, 'utf-8', 'cp1250');

        $replace = [
            'ì' => 'ě',
            'è' => 'č',
            "Ã" => 'í',
            "ø" => 'ř',
            "é" => 'á',
            "\xc3\xa8" => 'ě',
            "\xc4\x9b" => 'ě',
            "\xc4\x8d" => 'č',
            "\xc3\xa1" => 'é'
        ];

        return str_replace(array_keys($replace), array_values($replace), $str);
    }

}