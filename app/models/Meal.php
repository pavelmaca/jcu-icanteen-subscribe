<?php

namespace App\Model;
/**
 * Created by IntelliJ IDEA.
 * User: Assassik
 * Date: 17.02.2017
 * Time: 16:44
 */
class Meal
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var MealType
     */
    private $type;

    /**
     * Meal constructor.
     * @param string $name
     * @param \DateTime $date
     * @param MealType $type
     */
    public function __construct(string $name, \DateTime $date, int $type)
    {
        $this->name = $name;
        $this->date = $date;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return MealType
     */
    public function getType()
    {
        return $this->type;
    }


}

abstract class MealType
{
    const UNKNOWN = -1;
    const SNIDANE = 0;
    const OBED = 1;
    const VECERE = 2;
    const DIETA = 3;
    const POLEVKA = 4;
    const SPECIALITA = 5;


    public static function getFromString(string $name): int
    {
        switch ($name) {
            case "Specialita 1":
                return self::SPECIALITA;
            case "Polévka 1":
            case "Polévka 2":
                return self::POLEVKA;
            case "Dieta 1":
            case "Dieta 2":
            case "Dieta 3":
                return self::DIETA;
            case "Oběd 1":
            case "Oběd 2":
            case "Oběd 3":
            case "Oběd 4":
            case "Oběd 5":
            case "Oběd 6":
                return self::OBED;
            case "Snídaně 1":
            case "Snídaně 2":
                return self::SNIDANE;
            case "Večeře 1":
                return self::VECERE;
            default:
                return self::UNKNOWN;
        }
    }
}