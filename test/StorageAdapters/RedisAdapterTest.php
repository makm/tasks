<?php

namespace Makm\Tasks\StorageAdapters;

use PHPUnit\Framework\TestCase;
use Makm\Tasks\Task;

class RedisAdapterTestClass extends RedisAdapter
{

    public $redisMock;

    protected function getRedis()
    {
        return $this->redisMock;
    }

}

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2014-08-25 at 17:51:37.
 */
class RedisAdapterTest extends TestCase
{

    /**
     * @var RedisAdapterTestClass
     */
    protected $object;

    protected $redisMock;

    private $testTaskMock;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {

        if (!class_exists(\Redis::class)) {
            $this->markTestSkipped(
                'The Redis extension is not available.'
            );
        }

        $this->object = new RedisAdapterTestClass;

        $this->object->redisMock = $this->createMock(\Redis::class,
            ['watch', 'get', 'set', 'multi', 'exec', 'delete']);

        $this->testTaskMock = $this->getMockClass(Task::class);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    /**
     * @covers Makm\Tasks\StorageAdapters\RedisAbstract::set
     */
    public function testSet()
    {
        $taskListString = \serialize(['task0', 'task1', 'task2', 'task3']);
        $this->object->redisMock->expects($this->at(0))->method('watch')->will($this->returnSelf()); //block list
        $this->object->redisMock->expects($this->at(1))->method('get')->will($this->returnValue($taskListString)); //get list
        $this->object->redisMock->expects($this->at(2))->method('multi')->will($this->returnSelf()); // start atomar block
        $this->object->redisMock->expects($this->at(3))->method('set')->will($this->returnSelf()); // new list
        $this->object->redisMock->expects($this->at(4))->method('set')->will($this->returnSelf()); // new taskData with set TTL
        $this->object->redisMock->expects($this->at(5))->method('exec')->will($this->returnValue(true)); // exec atomar
        $result = $this->object->set('anygroup', 'anykey', $this->testTaskMock);
        $this->assertInternalType('boolean', $result);
        $this->assertTrue($result);
    }

    /**
     * @covers Makm\Tasks\StorageAdapters\RedisAbstract::get
     * @todo   Implement testGet().
     */
    public function testGet()
    {
        $serialazedTask = \serialize($this->testTaskMock);
        $this->object->redisMock->expects($this->at(0))->method('get')->will($this->returnValue($serialazedTask));
        $task = $this->object->get('anygroup', 'anykey');
        $this->assertInstanceOf('\Makm\Tasks\Task', $task);

        //nothig
        $this->object->redisMock->expects($this->at(0))->method('get')->will($this->returnValue(false));
        $result = $this->object->get('anygroup', 'anykey');
        $this->assertNull($result);
    }

    /**
     * @covers Makm\Tasks\StorageAdapters\RedisAbstract::del
     * @todo   Implement testDel().
     */
    public function testDel()
    {
        $taskListString = \serialize(['task0', 'task1', 'task2', 'task3']);
        $this->object->redisMock->expects($this->at(0))->method('watch')->will($this->returnSelf()); //block list
        $this->object->redisMock->expects($this->at(1))->method('get')->will($this->returnValue($taskListString)); //get list
        $this->object->redisMock->expects($this->at(2))->method('multi')->will($this->returnSelf()); // start atomar block
        $this->object->redisMock->expects($this->at(3))->method('set')->will($this->returnSelf()); // new list
        $this->object->redisMock->expects($this->at(4))->method('delete')->will($this->returnSelf()); // newt new taskData
        $this->object->redisMock->expects($this->at(5))->method('exec'); // exec atomar
        $result = $this->object->del('anygroup', 'task1');
        $this->assertInternalType('boolean', $result);
    }

    /**
     * @covers Makm\Tasks\StorageAdapters\RedisAbstract::getInGroup
     * @todo   Implement testGetInGroup().
     */
    public function testGetInGroup()
    {

        $this->object->redisMock->expects($this->exactly(3))
            ->method('get')
            ->with($this->isType('string'))
            ->will($this->onConsecutiveCalls(
                \serialize(['task0', 'task1']), //is list of keys in group
                \serialize($this->testTaskMock),
                \serialize($this->testTaskMock)) //unserilized tasks
            )
        ;

        $result = $this->object->getInGroup('anygroup', 'anykey');

        //assert 
        $this->assertEquals([
            'task0' => $this->testTaskMock,
            'task1' => $this->testTaskMock,
        ], $result);
    }

    /**
     * @covers Makm\Tasks\StorageAdapters\RedisAbstract::getInGroup
     * @todo   Implement testGetInGroupNotExisted().
     */
    public function testGetInGroupNotExisted()
    {

        $this->object->redisMock->expects($this->once())
            ->method('get')
            ->with($this->isType('string'))
            ->will($this->returnValue(false))
        ; //is list is not found


        $result = $this->object->getInGroup('anygroup', 'nofoundgroup');

        //assert 
        $this->assertEquals([], $result);
    }

}