<?php

/**
 * Class of Action for task
 * invoke function with some params
 */

namespace Makm\Tasks\Actions;

/**
 * Class ActionFunction
 * @package Makm\Tasks\Actions
 */
class ActionFunction extends ActionAbstract
{
    /**
     * @var \Closure
     */
    private $function;

    /**
     * Return full body of Closure object from file
     * see http://stackoverflow.com/questions/13983714/serialize-or-hash-a-closure-in-php
     *
     * @return array
     * @throws \RuntimeException
     * @throws \ReflectionException
     * @throws \LogicException
     */
    protected function getBodyOfFunction():array
    {
        $ref = new \ReflectionFunction($this->function);
        $file = new \SplFileObject($ref->getFileName());
        $file->seek($ref->getStartLine() - 1);
        $body = '';
        while ($file->key() < $ref->getEndLine())
        {
            $body .= $file->current();
            $file->next();
        }

        //preg_match('/function\s*\(\)/', $body, $m)
        return ['args' => null, 'use' => null, 'body' => null];
    }

    /**
     * set function for use
     * 
     * @param \Closure $function
     */
    public function setFunction(\Closure $function)
    {
        $this->function = $function;
    }

    /**
     * execute
     * 
     * @return mixed
     */
    protected function executeConstruction()
    {
        return \call_user_func_array($this->function, $this->params);
    }

}
