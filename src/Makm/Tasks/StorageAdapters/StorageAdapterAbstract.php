<?php

namespace Makm\Tasks\StorageAdapters;

abstract class StorageAdapterAbstract implements StorageAdapterInterface
{
    /**
     * @var string
     */
    private $_prefix = 'makmTaskStorage';

    /**
     * @var array
     */
    protected $_options = array();

    /**
     * StorageAdapterAbstract constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if (!empty($options))
        {
            $this->configure($options);
        }
    }

    /**
     * Form prefix of key Group with part or name as Class Storage (Result|TaskItem)
     * 
     * @param string $groupName
     * @return string
     */
    protected function getKeyGroup($groupName): string
    {
        return $this->_prefix . '_' . $groupName;
    }

    /**
     * Full key $keyTask in $group
     * 
     * @param string $groupName
     * @param string $keyTask
     * @return string
     */
    protected function getKeyTask($groupName, $keyTask): string
    {
        return $this->getKeyGroup($groupName) . '_' . $keyTask;
    }

}
