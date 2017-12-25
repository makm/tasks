<?php

namespace Makm\Tasks\StorageAdapters;

use \Makm\Tasks\Task;

class RedisAdapter extends StorageAdapterAbstract
{

    /**
     * @var \Redis
     */
    private $_redis;

    /**
     * @var array
     */
    protected $_defautOptions = array('host' => '127.0.0.1', 'port' => 6379);

    public function configure($options = array())
    {
        //
        $this->_options = array_merge($this->_defautOptions, $options);

        $redis = new \Redis();
        $resultConnection = $redis->pconnect($this->_options['host'],
                $this->_options['port']);

        if ($resultConnection === false)
        {
            throw new \Exception("Can't connect");
        }

        //@todo: setup others params
        $this->setRedis($redis);
    }

    /**
     * Get Redis object
     *
     * @param \Redis $redis
     */
    public function setRedis(\Redis $redis)
    {
        $this->_redis = $redis;
    }

    /**
     * return current Redis object
     *
     * @return \Redis
     */
    protected function getRedis()
    {
        return $this->_redis;
    }

    /**
     * @param $obj
     * @return string
     */
    protected function serialize($obj)
    {
        return \serialize($obj);
    }

    /**
     * @param $string
     * @return mixed
     */
    protected function unserialize($string)
    {
        return \unserialize($string);
    }

    /**
     * set one task in $groupName with $key
     *
     * @param      $groupName
     * @param      $keyTask
     * @param Task $task
     * @return boolean
     */
    public function set($groupName, $keyTask, Task $task)
    {
        //block list of group
        $this->getRedis()->watch($this->getKeyGroup($groupName));

        $tasksInGroupString = $this->getRedis()->get($this->getKeyGroup($groupName));
        $tasksInGroup = \unserialize($tasksInGroupString);

        //if empty list
        if (empty($tasksInGroup))
        {
            $tasksInGroup = array();
        }

        //uif not inserted early
        if (!\in_array($keyTask, $tasksInGroup, true))
        {
            //add new in group list
            $tasksInGroup[] = $keyTask;
        }

        //insert and update list
        return $this->getRedis()->multi()
                ->set($this->getKeyGroup($groupName), \serialize($tasksInGroup))
                ->set($this->getKeyTask($groupName, $keyTask),
                        $this->serialize($task)) //task
                ->exec();
    }

    /**
     * Returned task in $group with $key
     *
     * @param string $groupName
     * @param string $keyTask
     * @return Task
     */
    public function get($groupName, $keyTask)
    {
        $taskString = $this->getRedis()
                ->get($this->getKeyTask($groupName, $keyTask));
        //
        if (empty($taskString))
        {
            return null;
        }
        return $this->unserialize($taskString);
    }

    /**
     * Delete task in $group with $key
     *
     * @param string $groupName
     * @param string $keyTask
     * @return $int
     * @throws \Exception
     */
    public function del($groupName, $keyTask)
    {
        //block list of group
        $this->getRedis()->watch($this->getKeyGroup($groupName));

        //
        $tasksInGroupString = $this->getRedis()->get($this->getKeyGroup($groupName));
        $tasksInGroup = $this->unserialize($tasksInGroupString);

        //unset in list
        $pos = \array_search($keyTask, $tasksInGroup, true);
        if ($pos === false)
        {
            throw new \RuntimeException("Can't find  record of Task: {$keyTask} in groupList:{$groupName}");
        }
        unset($tasksInGroup[$pos]);



        $result = $this->getRedis()->multi()
                ->set($this->getKeyGroup($groupName),
                        $this->serialize($tasksInGroup))
                ->delete($this->getKeyTask($groupName, $keyTask))
                ->exec();

        if ($result === false)
        {
            return false;
        }

        return true;
    }

    /**
     * Returned array list of task in $group
     *
     * @param string $groupName
     * @return array
     * @throws \Exception
     */
    public function getInGroup($groupName): array
    {
        $list = array();
        //get list of taskKeys from storage
        $tasksInGroupString = $this->getRedis()->get($this->getKeyGroup($groupName));
        $tasksInGroup = $this->unserialize($tasksInGroupString);

        //if nothing
        if (empty($tasksInGroup))
        {
            return array();
        }
        //fill list 
        foreach ((array) $tasksInGroup as $keyTask)
        {
            $list[$keyTask] = $this->get($groupName, $keyTask);
        }

        //check correctness counts    
        if (\count($tasksInGroup) !== \count($list))
        {
            throw new \RuntimeException('Indexes of Tasks in group ' . count($tasksInGroup) . ', but ' . count($list) . ' found');
        }

        return $list;
    }

}
