<?php
/**
* @backupGlobals disabled
* @backupStaticAttributes disabled
*/

$DB = new PDO('mysql:host=localhost:8889;dbname=appdata_test', "root", "root");
require_once "src/Group.php";
require_once "src/Task.php";
require_once "src/User.php";

class GroupTest extends PHPUnit_Framework_TestCase
{
  protected function tearDown()
  {
    Group::deleteAll();
    Task::deleteAll();
    User::deleteAll();
  }
    function test_save()
    {
      $newGroup = new Group ("Errands", 1);
      $result = $newGroup->save();
      $this->assertTrue($result, "This group did not save.");
    }

    function test_deleteAll()
    {
      $newGroup = new Group ("Errands", 1);
      $newGroup->save();
      Group::deleteAll();
      $result = Group::getAll();
      $this->assertEquals([], $result);
    }

    function test_getAll()
    {
      $newGroup = new Group ("Errands", 1);
      $newGroup2 = new Group ("More Errands", 1);
      $newGroup->save();
      $newGroup2->save();
      $result = Group::getAll();
      $this->assertEquals([$newGroup, $newGroup2], $result);
    }

    function test_findId()
    {
      $newGroup = new Group ("Errands", 1);
      $newGroup2 = new Group ("More Errands", 1);
      $newGroup->save();
      $newGroup2->save();
      $result = $newGroup2->getId();
      $result2 = Group::find($result);
      $this->assertEquals($result2, $newGroup2);
    }
    function test_findByName()
    {
      $newGroup2 = new Group("More Errands", 1);
      $newGroup2->save();
      $result = $newGroup2->getGroupName();
      $result2 = Group::findByName($result);
      $this->assertEquals($newGroup2, $result2);
    }
    function test_updateGroupName()
    {
      $newGroup2 = new Group("More Errands", 1);
      $newGroup2->save();
      $new_group_name = "Even More Errands";
      $newGroup2->updateGroupName($new_group_name);
      $result = $newGroup2->getGroupName();
      $this->assertEquals($new_group_name, $result);
    }
    function test_delete()
    {
      $newGroup = new Group ("Errands", 1);
      $newGroup->save();
      $newGroup2 = new Group ("More Errands", 1);
      $newGroup2->save();
      $newGroup->delete();
      $result = Group::getAll();
      $this->assertEquals([$newGroup2], $result);
    }
    function test_addTask()
    {
      $newGroup = new Group ("Errands", 1);
      $newGroup->save();
      $test_task = new Task("shopping", "get groceries", "2017-04-10", "2017-06-10");
      $test_task->save();
      $newTask = new Task ("plan vacation", "list travel details", "2017-05-10", "2017-05-15");
      $newTask->save();
      $newGroup->addTaskToGroup($test_task);
      $newGroup->addTaskToGroup($newTask);
      $result = $newGroup->getTaskFromGroup();
      $this->assertEquals($result, [$test_task, $newTask]);

    }
  }






?>
