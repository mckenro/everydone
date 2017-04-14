<?php
  class Group
  {
      private $group_name;
      private $public;
      private $id;

      function __construct($group_name, $public, $id=null)
      {
          $this->group_name = $group_name;
          $this->public = $public;
          $this->id = $id;
      }
      function getId()
      {
          return $this->id;
      }
      function setGroupName($new_group_name)
      {
          $this->group_name = $new_group_name;
      }
      function getGroupName()
      {
          return $this->group_name;
      }
      function setPublic($new_public)
      {
          $this->public = (int) $new_public;
      }
      function getPublic()
      {
          return $this->public;
      }
      function save()
      {
          $executed = $GLOBALS['DB']->exec("INSERT INTO task_forces (group_name, public) VALUES ('{$this->getGroupName()}', {$this->getPublic()});");
          if ($executed)
          {
              $this->id = $GLOBALS['DB']->lastInsertId();
              return true;
          } else {
              return false;
          }
      }
      static function getAll()
      {
          $groups = array();
          $returned_groups = $GLOBALS['DB']->query("SELECT * FROM task_forces;");
          foreach ($returned_groups as $group)
          {
              $id = $group['id'];
              $group_name = $group['group_name'];
              $public = $group['public'];
              $newgroup = new Group($group_name, $public, $id);
              array_push($groups, $newgroup);
          }
          return $groups;
      }
      static function deleteAll()
      {
          $executed = $GLOBALS['DB']->exec("DELETE FROM task_forces;");
          if (!$executed)
          {
              return false;
          } else {
              return true;
          }
      }

      static function find($id)
      {
          $executed = $GLOBALS['DB']->prepare("SELECT * FROM task_forces WHERE id = :id;");
          $executed->bindParam(':id', $id, PDO::PARAM_INT);
          $executed->execute();
          $result = $executed->fetch(PDO::FETCH_ASSOC);
          $group = new Group($result['group_name'], $result['public'], $result['id']);
          return $group;
      }

      static function findByName($name)
      {
          $executed = $GLOBALS['DB']->prepare("SELECT * FROM task_forces WHERE group_name = :name;");
          $executed->bindParam(':name', $name, PDO::PARAM_STR);
          $executed->execute();
          $result = $executed->fetch(PDO::FETCH_ASSOC);
          $group = new Group($result['group_name'], $result['public'], $result['id']);
          return $group;
      }
      function updateGroupName($new_group_name)
      {
          $executed = $GLOBALS['DB']->exec("UPDATE task_forces SET group_name = '{$new_group_name}' WHERE id = {$this->getId()};");
          if ($executed)
          {
            $this->setGroupName($new_group_name);
            return true;
          } else {
            return false;
          }
      }
      function delete()
      {
          $executed = $GLOBALS['DB']->exec("DELETE FROM task_forces WHERE id = {$this->getId()};");
          if (!$executed)
          {
          $executed = $GLOBALS['DB']->exec("DELETE FROM users_groups WHERE users_groups.group_id = {$this->getId()};");
          if (!$executed){
              return false;
          } else {
              return true;
          }
          }
      }
      function addUserToGroup($user_id)
      {
          $executed = $GLOBALS['DB']->exec("INSERT INTO users_groups (user_id, group_id) VALUES ({$user_id}, {$this->getId()});");
          if($executed){
            return true;
          } else {
            return false;
          }
      }
      function addTaskToGroup($task)
      {
        $executed = $GLOBALS['DB']->exec("INSERT INTO tasks_groups (task_id, group_id) VALUES ({$task->getId()}, {$this->getId()});");
        if($executed){
          return true;
        }else {
          return false;
        }
      }
      function getTaskFromGroup()
      {
        $returned_task = $GLOBALS['DB']->query("SELECT tasks.* FROM task_forces JOIN tasks_groups ON (task_forces.id = tasks_groups.group_id) JOIN tasks ON (tasks_groups.task_id = tasks.id) WHERE task_forces.id = {$this->getId()};");
        $tasks = array();
        foreach($returned_task as $task){
          $newTask = new Task($task['task_name'], $task['task_description'], $task['assign_time'], $task['due_time'], $task['id']);
          array_push($tasks, $newTask);
        }
        return $tasks;
      }

      function groupAdminId(){
        $executed = $GLOBALS['DB']->query("SELECT user_id FROM users_groups WHERE group_id = {$this->getId()} ORDER BY id LIMIT 1;");
        $result = $executed->fetch(PDO::FETCH_ASSOC);
        return $result['user_id'];
      }

      static function findGroupByUserId($id){
        $group_array = array();
        $executed = $GLOBALS['DB']->prepare("SELECT task_forces.* FROM task_forces JOIN users_groups ON (users_groups.group_id = task_forces.id) JOIN users ON (users.id = users_groups.user_id) WHERE users.id = :id;");
        $executed->bindParam(':id', $id, PDO::PARAM_INT);
        $executed->execute();
        $results = $executed->fetchAll(PDO::FETCH_ASSOC);
        foreach($results as $result){
          $group = new Group($result['group_name'], $result['public'], $result['id']);
          array_push($group_array, $group);
        }
        return $group_array;
      }

      function findAllUsersInTheGroup(){
        $user_name_in_group_array = array();
        $executed = $GLOBALS['DB']->query("SELECT users.* FROM users JOIN users_groups ON (users_groups.user_id = users.id) WHERE users_groups.group_id = {$this->getId()};");
        $results = $executed->fetchAll(PDO::FETCH_ASSOC);
        foreach($results as $result){
          array_push($user_name_in_group_array, $result['username']);
        }
        return $user_name_in_group_array;
      }

    }




?>
