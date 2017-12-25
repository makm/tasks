<?php

namespace Makm\Tasks\Actions;

/**
 * Class ActionAbstract
 * @package Makm\Tasks\Actions
 */
abstract class ActionAbstract
{
    /**
     * @var array
     */
    protected $vars = [];

    /*
     * variables names required for use
     */
    protected $requiredSerialize;
    protected $requiredUse;

    /**
     * ActionAbstract constructor.
     * @param array $variables
     * @throws \Exception
     */
    public function __construct(array $variables = [])
    {
        $this->addVar($variables);
    }

    /**
     * Bulk add variables
     *
     * @param array $arg
     * @return ActionAbstract
     * @throws \Exception
     */
    private function addVar($arg): ActionAbstract
    {
        foreach ($arg as $key => $value) {
            $this->setVar($key, $value);
        }

        return $this;
    }

    /**
     * AKA of __set
     *
     * @param string $name
     * @param mixed  $value
     * @throws \Exception
     */
    protected function setVar($name, $value): void
    {
        $this->__set($name, $value);
    }

    /**
     * AKA of __get
     *
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    protected function getVar($name)
    {
        return $this->__get($name);
    }

    /**
     * all vars
     *
     * @return array
     */
    protected function getVars()
    {
        return $this->vars;
    }

    /**
     * check params
     *
     * @param array $list
     * @return boolean
     */
    protected function _checkSetParams($list): bool
    {
        foreach ($list as $key) {
            if (!\array_key_exists($key, $this->vars)) {
                return false;
            }
        }
        return true;
    }

    /**
     * execute
     *
     * @return mixed
     */
    abstract protected function executeConstruction();

    /**
     * magic get
     *
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function __get($name)
    {
        if (!array_key_exists($name, $this->vars)) {
            throw new \RuntimeException("Variable: '{$name}' variable not set");
        }
        return $this->vars[$name];
    }

    /**
     * Set value with check
     * Блокируем любые попытки изменить переменные объекта , необходимые к сериализации
     *
     * @param string $name
     * @param string $value
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        //throw exception if need rewrite value from 'requiredSerialize' list
        if (isset($this->vars[$name]) && \in_array($name, $this->requiredSerialize, true)) {
            throw new \RuntimeException("Can't rewrite value ({$name}) required for Serialize");
        }
        $this->vars[$name] = $value;
    }

    /**
     * check params for Serialize
     *
     * @return boolean
     */
    public function completedParamsForSerialize(): bool
    {
        return $this->_checkSetParams($this->requiredSerialize);
    }

    /**
     * check params for Serialize
     *
     * @return boolean
     */
    public function completedParamsForExecute(): bool
    {
        return $this->_checkSetParams($this->requiredUse) AND $this->completedParamsForSerialize();
    }

    /**
     * Уничтожаем переменные, кроме тех что необходимы для сериализации
     *
     * @return array
     * @throws \Exception
     */
    public function __sleep()
    {
        if ($this->completedParamsForSerialize() == false) {
            throw new \RuntimeException("Can\'t serialize, required values not found");
        }

        //clear all not needed values
        foreach ($this->vars as $key => $value) {
            if (!\in_array($key, $this->requiredSerialize, true)) {
                unset($this->vars[$key]);
            }
        }

        return ['vars', 'requiredUse', 'requiredSerialize'];
    }

    /**
     * Execute action
     *
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function execute(array $params = [])
    {
        $this->addVar($params);

        if ($this->completedParamsForExecute() === false) {
            $listOfParamsGiven = \implode(',', array_keys($this->vars));
            throw new \RuntimeException("Can't execute, required values for use not found, only '{$listOfParamsGiven}' variable given");
        }

        return $this->executeConstruction();
    }
}
