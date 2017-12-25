<?php

namespace Makm\Tasks;

use Makm\Tasks\Actions\ActionAbstract;
use Makm\Tasks\TimesControl\TimesControlAbstract;

/**
 * Class or Task object
 *
 * @author makm
 */
class Task
{
    private $_createdTime;
    private $_ttl = false;

    /* time of Task create */
    private $_lastInvokeActionTime;

    /* TTL */
    private $_action;

    /* last invoked action Time time */
    private $_timesControl;

    /* Action object */
    private $_lastResult;

    /* TimesControl object */
    /**
     * @var array
     */
    public static $actionClassMap = [
        'method' => 'Actions\ActionMethod',
    ];


    /* last result data */
    /**
     * @var array
     */
    public static $timesControlClassMap = [
        'cron' => 'TimesControl\Cron',
        'each' => 'TimesControl\Each',
    ];

    /**
     * Task constructor.
     */
    public function __construct()
    {
        //set time of create 
        $this->_createdTime = new \DateTime();
    }

    /**
     * Return DateTime when Result created
     *
     * @return \Datetime
     */
    public function getCreatedTime(): \Datetime
    {
        return $this->_createdTime;
    }

    /**
     * Return DateTime when was last invoke action
     *
     * @return \Datetime
     */
    public function getLastInvokeActionTime(): \Datetime
    {
        return $this->_lastInvokeActionTime;
    }

    /**
     * Return Action object of Task
     *
     * @return ActionAbstract
     * @throws \Exception
     */
    public function getAction(): ActionAbstract
    {
        if ($this->_action === null) {
            throw new \RuntimeException('Action object is not configured');
        }
        return $this->_action;
    }

    /**
     * set Action
     * @param ActionAbstract $actionObject
     */
    public function setAction(ActionAbstract $actionObject)
    {
        $this->_action = $actionObject;
    }

    /**
     * Return TimesControl object of Task
     * @throws \RuntimeException
     */
    public function getTimesControl()
    {
        if ($this->_timesControl === null) {
            throw new \RuntimeException('TimesControl object is not configurated');
        }
        return $this->_timesControl;
    }

    /**
     * set TimesControl
     * @param TimesControlAbstract $timesControlObject
     */
    public function setTimesControl(TimesControlAbstract $timesControlObject): void
    {
        $this->_timesControl = $timesControlObject;
    }

    /**
     * Set what to do through fabric
     *
     * @param string $actionName
     * @param array  $params
     * @return \Makm\Tasks\Task
     */
    public function configAction($actionName, array $params = [])
    {
        $className = __NAMESPACE__ . '\\' . static::$actionClassMap[$actionName];
        $actionObject = new $className($params);
        $this->setAction($actionObject);
        return $this;
    }

    /**
     * Set when to do through fabric
     *
     * @param string $timesControlName
     * @param array  $params
     * @return \Makm\Tasks\Task
     */
    public function configTimesControl($timesControlName, array $params = [])
    {
        $className = __NAMESPACE__ . '\\' . static::$timesControlClassMap[$timesControlName];
        $timesControlObject = new $className($params);
        $this->setTimesControl($timesControlObject);
        return $this;
    }

    /**
     * Return TTL
     *
     * @return integer
     */
    public function getTTL(): int
    {
        return $this->_ttl;
    }

    /**
     * Set TTL
     *
     * @param integer $seconds
     * @return \Makm\Tasks\Task
     */
    public function setTTL($seconds): Task
    {
        $this->_ttl = (int)$seconds;
        return $this;
    }

    /**
     * return true if Task ttl is out
     *
     * @return boolean
     */
    public function ttlExpired($relativeTime): bool
    {
        /*
         * @todo: Check situation then $relativeTime < getCreatedTime()
         */
        if ($this->_ttl === 0) {
            return false;
        }
        return (($this->getCreatedTime()->getTimestamp() + $this->getTTL()) < $relativeTime);
    }

    /**
     * Return last result data ot Task
     *
     * @return mixed
     */
    public function getLastResult()
    {
        return $this->_lastResult;
    }

    /**
     * Set result data of Tast
     *
     * @return void
     */
    public function setLastResult($result)
    {
        $this->_lastResult = $result;
    }

    /**
     * If we can Execute actionObject, check actual time and execute current Action
     *
     * @param array $params
     * @param null  $relativeTime
     * @return boolean
     * @throws \Exception
     */
    public function donow($params = [], $relativeTime = null)
    {

        //set NOW if not setted
        if ($relativeTime === null) {
            $relativeTime = time();
        }

        //check if Task is Expired
        if ($this->ttlExpired($relativeTime)) {
            return false;
        }

        //check TimesControl
        if ($this->getTimesControl()->isNow($relativeTime)) {

            $fixInvokedTime = \DateTime::createFromFormat("U", $relativeTime);  //fix last invoke time

            $result = $this->getAction()->execute($params); //invoke!
            //fix for object after execute 
            $this->_lastInvokeActionTime = $fixInvokedTime;
            $this->setLastResult($result);
            return true;
        }
        return false;
    }

}
