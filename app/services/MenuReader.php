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

    public function __construct()
    {

    }

    public function readCurrent()
    {
        $httpClient = new Client([
            'base_uri' => 'http://menza.jcu.cz',
            RequestOptions::CONNECT_TIMEOUT => 7,
            RequestOptions::TIMEOUT => 10,
        ]);

        $result = $httpClient->get('Studentska.html');
        // dump($result);
        if ($result->getStatusCode() !== 200) {
            return false;
        }

        $html = $result->getBody()->getContents();


        $DOM = new \DOMDocument();
        //$DOM->loadHTML('<?xml encoding="windows-1250">'.utf8_encode($html));
        $DOM->loadHTML($html);

        //get all H1
        $items = $DOM->getElementsByTagName('tr');

        $first = true;
        $date = false;
        $data = [];
        foreach ($items as $tr) {
            if ($first) {
                $first = false;
                continue;
            }
            /** @var $tr \DOMElement */

            $colCount = 0;
            $skip = false;
            foreach ($tr->getElementsByTagName('td') as $td) {
                /** @var $td \DOMElement */

                $str = $td->textContent; //iconv('windows-1250', 'utf-8', $td->textContent);
                switch ($colCount) {
                    case 0:
                        // chack date
                        if (preg_match('~^([0-9]{1,2}\.){2}[0-9]{4}$~', $str)) {
                            // dump($str.' 00:00:00');
                            $date = DateTime::createFromFormat('j.n.Y H:i:s', $str . ' 00:00:00');
                            // dump($date);
                        }
                        break;
                    case 1:
                        $str = self::fixEncoding($str);
                        // dump($str);
                        // dump(mb_convert_encoding(trim($td->textContent), 'utf-8', 'cp1250'));
                        if (preg_match('~^(Specialita) [0-9]~', $str)) {
                            $type = $str;
                        } else {
                            $skip = true;
                        }

                        break;
                    case 3:
                        $name = $str;
                        break;
                }
                // dump($td->textContent);
                $colCount++;
            }
            if ($skip) {
                continue;
            }

            // dump(iconv('cp1250', 'UTF-8', $name));
            //     dump(self::fixEncoding($name));

            $data[] = [$date, $type, self::fixEncoding($name)];
            //dump('date: ' . $date->format('d.m.Y') . ' ' . $type . ' ' . self::fixEncoding($name));
        }

        return $data;
    }

    protected static function fixEncoding($str)
    {
        // $encoding = mb_detect_encoding($str);
        //if($encoding != 'UTF-8'){
        //  $str = iconv('cp1250', 'UTF-8', $str);
        $str = mb_convert_encoding($str, 'utf-8', 'cp1250');
        // }

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
            //  "ě" => 'Č',
        ];

        return str_replace(array_keys($replace), array_values($replace), $str);
    }

}