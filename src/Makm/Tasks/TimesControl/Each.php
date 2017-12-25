<?php

namespace Makm\Tasks\TimesControl;

/**
 * @author makm
 */
class Each extends TimesControlAbstract implements TimesControlInterface
{
    //units
    public const HOURS = 'h';
    public const MINUTES = 'm';
    public const SECONDS = 's';

    /**
     * @var integer
     */
    private $_periodInSeconds;

    /**
     * In seconds Do
     * @param integer $value
     * @param string  $unit
     * @return integer
     * @throws \RuntimeException
     */
    private function inSeconds($value, $unit): ?int
    {
        switch ($unit) {
            case self::HOURS:
                return $value * 3600;
            case self::MINUTES:
                return $value * 60;
            case self::SECONDS:
                return $value;

            default:
                throw new \RuntimeException("Can't convert to seconds, unit {$unit} incorrect");
        }
    }

    /**
     * set Period as array('value', 'unit') or integer (is sec)
     * @param array $options
     * @throws \RuntimeException
     */
    public function setup($options): void
    {
        if (\is_int($options)) {
            $this->setPeriod($options, self::SECONDS);
            return;
        }
        if (!empty($options['value'])) {
            empty($options['unit']) AND $options['unit'] = self::SECONDS;
            $this->setPeriod($options['value'], $options['unit']);
        }
    }


    public function resetScheme(): void
    {
        $this->_periodInSeconds = null;
    }

    /**
     * @param        $value
     * @param string $unit
     * @throws \RuntimeException
     */
    public function setPeriod($value, $unit = self::SECONDS)
    {
        $this->_periodInSeconds = $this->inSeconds($value, $unit);
    }

    /**
     * @return integer|null
     */
    public function getPeriodInSeconds(): ?int
    {
        return $this->_periodInSeconds;
    }

    /**
     * @param $timeNow
     * @return bool
     */
    public function isNow($timeNow): bool
    {
        if ($this->_periodInSeconds === null) {
            return false;
        }

        if (($timeNow % $this->_periodInSeconds) === 0) {
            return true;
        }
        return false;
    }

}
