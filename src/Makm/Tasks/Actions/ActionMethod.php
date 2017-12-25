<?php

/**
 * Class of Action for task
 * execute construction is invoke method of some object with params
 */

namespace Makm\Tasks\Actions;

class ActionMethod extends ActionAbstract
{
    /**
     * ActionMethod constructor.
     * @param array $variables
     * @throws \Exception
     */
    public function __construct($variables = array())
    {
        parent::__construct($variables);
        $this->requiredSerialize = array('method', 'params');
        $this->requiredUse = array('object');
    }

    /**
     * Wrapper for better usebles: set Object name
     *
     * @param mixed $object
     * @return ActionMethod
     * @throws \Exception
     */
    public function setObject($object)
    {
        $this->setVar('object', $object);
        return $this;
    }

    /**
     * Wrapper for better usebles: set Method name
     *
     * @param string $method
     * @return ActionMethod
     * @throws \Exception
     */
    public function setMethod($method)
    {
        $this->setVar('method', $method);
        return $this;
    }

    /**
     * Wrapper for better usebles: set Params name
     *
     * @param string $params
     * @return ActionMethod
     * @throws \Exception
     */
    public function setParams($params)
    {
        $this->setVar('params', $params);
        return $this;
    }

    /**
     * execute
     * 
     * @return mixed
     */
    protected function executeConstruction()
    {
        return \call_user_func_array(array($this->object, $this->method), $this->params);
    }

}
