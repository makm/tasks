<?php

namespace Makm\Tasks\TimesControl;

/**
 * @author makm
 */
class Croned implements TimesControlInterface
{

    /**
     * types
     */
    public const EACHTIME = 'eachvalue';
    public const CONCRETE = 'concrete';

    /**
     * units
     */
    public const DAYWEEK = 'W';
    public const MONTH = 'M';
    public const DAYMONTH = 'D';
    public const HOURS = 'h';
    public const MINUTES = 'm';
    public const SECONDS = 's';

    /**
     * @var array
     */
    private $_whenScheme = [];

    /**
     * @param $value
     * @param $unit
     * @param $type
     */
    private function when($value, $unit, $type)
    {
        $this->_whenScheme[$unit][$type] = $value;
    }

    /**
     * Internal checks each of units params as 'concrete' and as 'each'
     * @param string  $unitKey
     * @param array   $unitTypesValues
     * @param integer $timeNow
     * @return bool
     * @throws \RuntimeException
     */
    private function _checkUnit($unitKey, $unitTypesValues, $timeNow): bool
    {
        //map units for DateTime format
        $mapUnitsDateTime = [
            static::DAYWEEK  => 'M',
            static::MONTH    => 'n',
            static::DAYMONTH => 'j',
            static::HOURS    => 'G',
            static::MINUTES  => 'i',
            static::SECONDS  => 's',
        ];


        if (!isset($mapUnitsDateTime[$unitKey])) {
            throw new \RuntimeException("UnitKey {$unitKey} must be one of DAYWEEK,MONTH,DAYMONTH,HOURS,MINUTES,SECONDS const class value");
        }

        //set unit of current time of $unitKey
        $unitsNow = date($mapUnitsDateTime[$unitKey], $timeNow);

        //if CONCRETE is set, ignore EACH values
        if (isset($unitTypesValues[static::CONCRETE])) {
            return $unitsNow === (string)$unitTypesValues[static::CONCRETE];
        }
        if (isset($unitTypesValues[static::EACHTIME])) {
            return ($unitsNow % $unitTypesValues[static::EACHTIME]) === 0;
        }
    }

    /**
     * @param $options
     */
    public function setup($options): void
    {
        foreach ($options as $unit => $unitTypesValues) {
            foreach ($unitTypesValues as $type => $value) {
                $this->when($value, $unit, $type);
            }
        }
    }

    /**
     * set Concrete Time
     * @param integer $value
     * @param string  $unit
     * @return Croned
     */
    public function whenConcrete($value, $unit)
    {
        $this->when($value, $unit, static::CONCRETE);
        return $this;
    }

    /**
     * set Each Period
     * @param integer $value
     * @param string  $unit
     * @return Croned
     */
    public function whenEachTime($value, $unit)
    {
        $this->when($value, $unit, static::EACHTIME);
        return $this;
    }

    /**
     * Reset 'whenScheme'
     */
    public function resetScheme()
    {
        $this->_whenScheme = [];
    }

    public function getWhenScheme()
    {
        return $this->_whenScheme;
    }

    /**
     * @param integer $timeNow
     * @return boolean
     * @throws \Exception
     */
    public function isNow($timeNow): bool
    {
        //checks
        foreach ($this->_whenScheme as $unitKey => $unitTypesValues) {
            if (!$this->_checkUnit($unitKey, $unitTypesValues, $timeNow)) {
                return false;
            }
        }

        return true;
    }

}
