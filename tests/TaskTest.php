<?php
/**
* @backupGlobals disabled
* @backupStaticAttributes disabled
*/

$DB = new PDO('mysql:host=localhost:8889;dbname=appdata_test', "root", "root");
require_once "src/Task.php";
require_once "src/User.php";
require_once "src/Group.php";
class TaskTest extends PHPUnit_Framework_TestCase
{
  protected function tearDown()
  {
    Task::deleteAll();
    User::deleteAll();
    Group::deleteAll();
  }

    function test_save()
    {
      $newTask = new Task ("shopping", "get groceries");
      $result = $newTask->save();
      $this->assertTrue($result, "Fail");
    }

    function test_updateAll()
    {
      $test_task = new Task("shopping", "get groceries", "2017-04-10", "2017-06-10");
      $test_task->save();

      $test_task->updateAll("plan vacation", "list travel details", "2017-05-10", "2017-05-15");

      $this->assertEquals("plan vacation", $test_task->getName());
    }

    function test_deleteAll()
    {
      $newTask = new Task ("shopping", "get groceries", "2017-04-10", "2017-06-10");
      $newTask->save();
      Task::deleteAll();
      $result = Task::getAll();
      $this->assertEquals($result, []);
    }

    function test_getAll()
    {
      $newTask = new Task ("shopping", "get groceries");
      $newTask2 = new Task ("plan vacation", "list travel details");
      $newTask->save();
      $newTask2->save();
      $result = Task::getAll();
      $this->assertEquals($result, [$newTask, $newTask2]);
    }

    function test_delete()
    {
      $test_task = new Task("shopping", "get groceries");
      $test_task->save();

      $test_task2 = new Task("hiking", "get high");
      $test_task2->save();

      $test_user = new User("Mike", "password");
      $test_user->save();

      $test_task->addUser($test_user);

      $test_task->delete();

      $this->assertEquals([$test_task2], Task::getAll());
    }

    function test_addUser()
    {
      $test_task = new Task("shopping", "get groceries");
      $test_task->save();

      $test_user = new User("New Guy", "password");
      $test_user->save();

      $test_task->addUser($test_user);

      $this->assertEquals($test_task->getUsers(), [$test_user]);
    }

    function test_getUsers()
    {
      $test_task = new Task("shopping", "get groceries");
      $test_task->save();

      $test_user = new User("New Guy", "password");
      $test_user->save();

      $test_user2 = new User("Other Guy", "password");
      $test_user2->save();

      $test_task->addUser($test_user);
      $test_task->addUser($test_user2);

      $this->assertEquals($test_task->getUsers(), [$test_user, $test_user2]);
    }
    function test_addGroup()
    {
      $test_task = new Task("shopping", "get groceries");
      $test_task->save();
      $test_group = new Group("Errands", 1);
      $test_group->save();
      $test_task->addGroupToTask($test_group);
      $result = $test_task->getGroupFromTask();
      $this->assertEquals($result, $test_group);
    }
  }
?>
