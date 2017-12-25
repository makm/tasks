<?php

namespace Makm\Tasks\StorageAdapters;

use Makm\Tasks\Task;

interface StorageAdapterInterface
{
    public function configure($options);

    public function set($groupName, $keyTask, Task $task);

    public function get($groupName, $keyTask);

    public function del($groupName, $keyTask);

    public function getInGroup($groupName);
}
