<?php

namespace Makm\Tasks;

use Makm\Tasks\StorageAdapters\StorageAdapterInterface;

/**
 * Class Storage
 * @package Makm\Tasks
 */
class Storage
{
    /**
     * @var StorageAdapterInterface
     */
    private $_storageAdapter;

    /**
     * Factory method to create StorageAdapter
     *
     * @param string $name
     * @param array  $options
     * @throws \Exception
     */
    public function configureAdapter($name, $options): void
    {
        $classAdapterName = __NAMESPACE__ . '\\StorageAdapters\\' . $name . 'Adapter';
        if (!class_exists($classAdapterName))
        {
            throw new \RuntimeException("{$classAdapterName} not exist");
        }
        $this->setStorageAdapter(new $classAdapterName($options));
    }

    /**
     * Set StorageAdapter
     * 
     * @param StorageAdapters\StorageAdapterInterface $storageAdapter
     */
    public function setStorageAdapter(StorageAdapters\StorageAdapterInterface $storageAdapter): void
    {
        $this->_storageAdapter = $storageAdapter;
    }

    /**
     * Return current StorageAdapter
     * 
     * @return StorageAdapters\StorageAdapterInterface
     */
    protected function getStorageAdapter()
    {
        return $this->_storageAdapter;
    }

    /**
     * Set one task in $groupName with $key
     * 
     * @param string $groupName
     * @param string $keyTask
     * @param \Makm\Tasks\Task $task
     * @return boolean
     */
    public function set($groupName, $keyTask, Task $task): bool
    {
        return $this->getStorageAdapter()->set($groupName, $keyTask, $task);
    }

    /**
     * Returned task in $groupName with $keyTask
     * 
     * @param string $groupName
     * @param string $keyTask
     * @return \Makm\Tasks\Task
     */
    public function get($groupName, $keyTask)
    {
        $task = $this->getStorageAdapter()->get($groupName, $keyTask);

        //when not found
        if (empty($task))
        {
            return null;
        }

        return $task;
    }

    /**
     * Delete task in $groupName with $keyTask in Storage through Adapter
     * 
     * @param string $groupName
     * @param string $keyTask
     * @return boolean
     */
    public function del($groupName, $keyTask)
    {
        return $this->getStorageAdapter()->del($groupName, $keyTask);
    }

    /**
     * Returned array list of task in $groupName from StorageAdapter.
     * 
     * @param string $groupName
     * @return array
     */
    public function getInGroup($groupName)
    {
        return $this->getStorageAdapter()->getInGroup($groupName);
    }

}
