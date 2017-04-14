<?php
  class Task
{
    private $task_name;
    private $task_description;
    private $assign_time;
    private $due_time;
    private $id;

    function __construct($task_name, $task_description, $assign_time=null, $due_time=null, $id=null)
      {
        $this->task_name = $task_name;
        $this->task_description = $task_description;
        $this->assign_time = $assign_time;
        $this->due_time = $due_time;
        $this->id = $id;
      }
      function getName()
      {
        return $this->task_name;
      }

      function setName($new_name)
      {
         $this->task_name = $new_name;
      }

      function getDescription()
      {
        return $this->task_description;
      }

      function setDescription($new_description)
      {
         $this->task_description = $new_description;
      }

      function getAssignTime()
      {
        return $this->assign_time;
      }

      function setAssignTime($new_assign_time)
      {
         $this->assign_time = $new_assign_time;
      }

      function getDueTime()
      {
        return $this->due_time;
      }

      function setDueTime($new_due_time)
      {
         $this->due_time = $new_due_time;
      }

      function getId()
      {
        return $this->id;
      }

      function save()
      {
          $executed = $GLOBALS['DB']->exec("INSERT INTO tasks (task_name, task_description, assign_time) VALUES ('{$this->getName()}', '{$this->getDescription()}', CURDATE());");
          if($executed){
            $this->id = $GLOBALS['DB']->lastInsertId();
            return true;
          }else{
            return false;
          }
      }

      function updateAll($new_name, $new_description, $new_assign_time, $new_due_time)
      {
        $executed = $GLOBALS['DB']->prepare("UPDATE tasks SET task_name = :task_name, task_description = :task_description, assign_time = :assign_time, due_time = :due_time WHERE id={$this->getId()}");
        $executed->bindParam(':task_name', $new_name, PDO::PARAM_STR);
        $executed->bindParam(':task_description', $new_description, PDO::PARAM_STR);
        $executed->bindParam(':assign_time', $new_assign_time, PDO::PARAM_STR);
        $executed->bindParam(':due_time', $new_due_time, PDO::PARAM_STR);
        $executed->execute();
        if ($executed){
          $this->setName($new_name);
          $this->setDescription($new_description);
          $this->setAssignTime($new_assign_time);
          $this->setDueTime($new_due_time);
          return true;
        } else {
          return false;
        }
      }

      static function getAllByGroupId($group_id)
      {
        $tasks = array();
        $returned_tasks = $GLOBALS['DB']->prepare("SELECT tasks.* FROM tasks JOIN tasks_groups ON (tasks_groups.task_id = tasks.id) JOIN task_forces ON (tasks_groups.group_id = task_forces.id) WHERE task_forces.id = :group_id;");
        $returned_tasks->bindParam(':group_id', $group_id, PDO::PARAM_INT);
        $returned_tasks->execute();
        $results = $returned_tasks->fetchAll(PDO::FETCH_ASSOC);
        foreach($results as $result)
        {
          $newTask = new Task($result['task_name'], $result['task_description'],  $result['assign_time'], $result['due_time'], $result['id']);
          array_push($tasks, $newTask);
        }
        return $tasks;
      }

      function addUser($user)
      {
        $executed = $GLOBALS['DB']->exec("INSERT INTO users_tasks (user_id, task_id) VALUES ({$user->getId()}, {$this->getId()});");
        if ($executed) {
          return true;
        } else {
          return false;
        }
      }

      function getUsers()
      {
        $returned_users = $GLOBALS['DB']->query("SELECT users.* FROM tasks JOIN users_tasks ON (tasks.id = users_tasks.task_id) JOIN users ON (users_tasks.user_id = users.id) WHERE tasks.id = {$this->getId()};");

        $users = array();
        foreach ($returned_users as $user) {
          $id = $user['id'];
          $user_name = $user['username'];
          $password = $user['password'];
          $new_user = new User($user_name, $password, $id);
          array_push($users, $new_user);
        }
        return $users;
      }

      static function deleteAll()
      {
        $deleteAll = $GLOBALS['DB']->exec("DELETE FROM tasks;");
        if ($deleteAll)
        {
          return true;
        }else {
          return false;
        }
      }

      function delete()
      {
        $executed = $GLOBALS['DB']->exec("DELETE FROM tasks WHERE id = {$this->getId()};");
        $executed = $GLOBALS['DB']->exec("DELETE FROM tasks_groups WHERE task_id = {$this->getId()};");
        $executed = $GLOBALS['DB']->exec("DELETE FROM users_tasks WHERE task_id = {$this->getId()};");
        if (!$executed) {
          return false;
        } else {
          return true;
        }
      }
      function addGroupToTask($group_id)
      {
        $executed = $GLOBALS['DB']->prepare("INSERT INTO tasks_groups (task_id, group_id) VALUES ({$this->getId()}, :group_id);");
        $executed->bindParam(':group_id', $group_id, PDO::PARAM_INT);
        $executed->execute();
        if ($executed){
          return true;
        }else {
          return false;
        }
      }
      function getGroupFromTask()
      {
        $returned_groups = $GLOBALS['DB']->query("SELECT task_forces.* FROM tasks JOIN tasks_groups ON (tasks.id = tasks_groups.task_id) JOIN task_forces ON (tasks_groups.group_id = task_forces.id) WHERE tasks.id = {$this->getId()};");
        foreach($returned_groups as $group){
          $newGroup = new Group($group['group_name'], $group['public'], $group['id']);
          return $newGroup;
        }
      }

      static function findTask($id){
        $executed = $GLOBALS['DB']->prepare("SELECT * FROM tasks WHERE id = :id;");
        $executed->bindParam(':id', $id, PDO::PARAM_INT);
        $executed->execute();
        $result = $executed->fetch(PDO::FETCH_ASSOC);
        $new_task = new Task($result['task_name'], $result['task_description'], $result['assign_time'], $result['due_time'], $result['id']);
        return $new_task;
      }

      function updateDue($due_time){
        $executed = $GLOBALS['DB']->prepare("UPDATE tasks SET due_time = :due_time WHERE id = {$this->getId()};");
        $executed->bindParam(':due_time', $due_time, PDO::PARAM_STR);
        $executed->execute();
        if($executed){
          return true;
        } else {
          return false;
        }
      }

      static function getAssignedTask($group_id){
        $assigned_task_array = array();
        $executed = $GLOBALS['DB']->prepare("SELECT tasks.* FROM tasks JOIN users_tasks ON (users_tasks.task_id = tasks.id) JOIN tasks_groups ON (tasks_groups.task_id = users_tasks.task_id) WHERE tasks_groups.group_id = :group_id;");
        $executed->bindParam(':group_id', $group_id, PDO::PARAM_INT);
        $executed->execute();
        $results = $executed->fetchAll(PDO::FETCH_ASSOC);
        foreach($results as $result){
          $task = new Task($result['task_name'], $result['task_description'], $result['assign_time'], $result['due_time'], $result['id']);
          array_push($assigned_task_array, $task);
        }
        return $assigned_task_array;
      }

      function assignedUser(){
        $executed = $GLOBALS['DB']->query("SELECT CONCAT(first_name,' ',last_name) AS assigned_user FROM profiles JOIN users_profiles ON (users_profiles.profile_id = profiles.id) JOIN users ON (users.id = users_profiles.user_id) JOIN users_tasks ON (users_tasks.user_id = users.id) JOIN tasks ON (users_tasks.task_id = tasks.id) WHERE users_tasks.task_id = {$this->getId()};");
        $username = $executed->fetch(PDO::FETCH_ASSOC);
        return $username['assigned_user'];
      }

    }

 ?>
