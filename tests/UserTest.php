<?php
/**
* @backupGlobals disabled
* @backupStaticAttributes disabled
*/

$DB = new PDO('mysql:host=localhost:8889;dbname=appdata_test', "root", "root");
require_once "src/User.php";
require_once "src/Group.php";
require_once "src/Task.php";
class UserTest extends PHPUnit_Framework_TestCase
{
  protected function tearDown()
  {
    User::deleteAll();
    Task::deleteAll();
    Group::deleteAll();
  }
  function test_Save()
  {
    $newUser = new User ("sample@gmail.com", "password");
    $newUser->save();
    $result = User::getAll();
    $this->assertEquals($result, [$newUser]);
  }
  function test_deleteAll()
  {
    $newUser = new User ("sample@gmail.com","password");
    $newUser->save();
    User::deleteAll();
    $result = User::getAll();
    $this->assertEquals($result, []);
  }
  function test_getAll()
  {
    $newUser = new User ('sample@gmail.com', 'password');
    $newUser2 = new User ('guy@gmail.com', "admin");
    $newUser->save();
    $newUser2->save();
    $result = User::getAll();
    $this->assertEquals($result, [$newUser, $newUser2] );
  }
   function test_findUserbyId()
  {
    $newUser = new User ('sample@gmail.com', 'password');
    $newUser2 = new User ('samdfdle@gmail.com', 'pasdfdsword');
    $newUser->save();
    $newUser2->save();
    $test = $newUser->getId();
    $result = User::findUserbyId($test);
    $this->assertEquals($newUser, $result);
  }
  function test_findByUserName()
  {
    $newUser = new User ('sample@gmail.com', 'password');
    $newUser2 = new User ('samdfdle@gmail.com', 'pasdfdsword');
    $newUser->save();
    $newUser2->save();
    $test = $newUser->getUserName();
    $result = User::findByUserName($test);
    $this->assertEquals($newUser, $result);
  }
  function test_updateUserName()
  {
    $newUser = new User ('sample@gmail.com', 'password');
    $newUser->save();
    $newUser->updateUserName("john@gmail.com");
    $result = $newUser->getUserName();
    $this->assertEquals("john@gmail.com", $result);
  }
  function test_updateUserPassword()
  {
    $newUser = new User ('sample@gmail.com', 'password');
    $newUser->save();
    $newUser->updateUserPassword("admin");
    $result = $newUser->getPassword();
    $this->assertEquals("admin", $result);
  }
  function test_delete()
  {
    $newUser = new User ('sample@gmail.com', 'password');
    $newUser2 = new User ('samdfdle@gmail.com', 'pasdfdsword');
    $newUser->save();
    $newUser2->save();
    $newUser->delete();
    $result = User::getAll();
    $this->assertEquals($result, [$newUser2]);
  }
  function test_addTask()
  {
    $newUser = new User ("sample@gmail.com", "password");
    $newUser->save();
    $newTask = new Task("Clean", "All shelves in the kitchen", "5/11/17", "5/12/17");
    $newTask->save();
    $newUser->addTask($newTask);
    $result = $newUser->getTask();
    $this->assertEquals($result, [$newTask]);
  }
  function test_addGroup()
  {
    $newUser = new User ("sample@gmail.com", "password");
    $newUser->save();
    $newGroup2 = new Group("More Errands", 1);
    $newGroup2->save();
    $groupId = $newGroup2->getId();
    $newUser->addGroup($groupId);
    $result = $newUser->getGroup();
    $this->assertEquals($result, [$newGroup2]);
  }
  function test_addFriend()
  {
    $newUser = new User ('sample@gmail.com', 'password');
    $newUser2 = new User ('samdfdle@gmail.com', 'pasdfdsword');
    $profile = new Profile("max", "larson", "picture","I am cool");
    $profile->save($profile->getFirstName(), $profile->getLastName(), $profile->getPicture(), $profile->getBio());
    $newUser->save();
    $newUser2->save();
    $newUser2->joinUserProfile($profile->getId());
    $newUser->addFriend($newUser2);
    $result = $newUser->findAllFriends();
    $this->assertEquals([Profile::getProfileUsingId($newUser2->getId())], $result);
  }
}
?>
