<?php

namespace Makm\Tasks;

class TaskManager
{

    private $storage = null;

    /**
     * configure Storages through factories
     *
     * @param string $nameStorageAdapter
     * @param array  $optionsStorageAdapter
     * @throws \Exception
     */
    public function configurateStorage($nameStorageAdapter,
            $optionsStorageAdapter)
    {
        $storage = new Storage();
        $storage->configureAdapter($nameStorageAdapter, $optionsStorageAdapter);
        $this->storage = $storage;
    }

    /**
     * get Storage object
     *
     * @return Storage
     * @throws \Exception
     */
    public function getStorage()
    {
        if ($this->storage === null)
        {
            throw new \RuntimeException('Result Storage obj not configurated');
        }
        return $this->storage;
    }

    /**
     * Create new Task
     * 
     * @return \Makm\Tasks\Task
     */
    public function createTask(): Task
    {
        return new Task();
    }

}
