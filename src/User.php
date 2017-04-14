<?php
  class User
{
    private $username;
    private $password;
    private $id;


    function __construct($username, $password, $id=null)
      {
        $this->username =$username;
        $this->password =$password;
        $this->id = $id;
      }
      function getUserName()
      {
        return $this->username;
      }
      function setUserName($new_username)
      {
         $this->username = $new_username;
      }
      function getPassword()
      {
        return $this->password;
      }
      function setPassword($new_password)
      {
         $this->password = $new_password;
      }
      function getId()
      {
        return $this->id;
      }
      function save()
      {
        $executed = $GLOBALS['DB']->exec("INSERT INTO users (username, password) VALUES ('{$this->getUserName()}', '{$this->getPassword()}'); ");
          if($executed){
            $this->id = $GLOBALS['DB']->lastInsertId();
            return true;
          }else{
            return false;
          }
      }
      static function getAll()
      {
        $users = array();
        $returned_users = $GLOBALS['DB']->query('SELECT * FROM users;');
        foreach($returned_users as $user){
          $newUser = new User($user['username'], $user["password"],  $user["id"]);
          array_push($users, $newUser);
        }
          return $users;
      }
      static function deleteAll()
      {
        $deleteAll = $GLOBALS['DB']->exec("DELETE FROM users;");
        if ($deleteAll)
        {
          return true;
        }else {
          return false;
        }
      }
    static function findUserbyId($id)
      {
        $returned_user= $GLOBALS['DB']->prepare("SELECT * FROM users WHERE id=:id;");
        $returned_user->bindParam(':id', $id, PDO::PARAM_STR);
        $returned_user->execute();
        foreach($returned_user as $user){
        $newUser = new User($user['username'], $user['password'], $user['id']);
        return $newUser;
      }
      }
      static function findByUserName($search_name)
      {
        $returned_user = $GLOBALS['DB']->prepare("SELECT * FROM users WHERE username = :name");
        $returned_user->bindParam(':name', $search_name, PDO::PARAM_STR);
        $returned_user->execute();
        foreach($returned_user as $user){
          $name = $user['username'];
          if($name == $search_name){
            $newUser = new User($user['username'], $user['password'], $user['id']);
            return $newUser;
          }
        }
      }
      function updateUserName($new_name)
      {
        $executed = $GLOBALS['DB']->exec("UPDATE users SET username = '{$new_name}' WHERE id = {$this->getId()};");
        if($executed){
          $this->setUserName($new_name);
          return true;
        }else{
          return false;
        }
      }
      function updateUserPassword($new_pass)
      {
        $executed = $GLOBALS['DB']->exec("UPDATE users SET password = '{$new_pass}' WHERE id = {$this->getId()};");
        if($executed){
          $this->setPassword($new_pass);
          return true;
        }else{
          return false;
        }
      }
      function delete()
      {
        $executed = $GLOBALS['DB']->exec("DELETE FROM users WHERE id = {$this->getId()};");
        if(!$executed){
          return false;
        }
        $executed = $GLOBALS['DB']->exec("DELETE FROM users_groups WHERE user_id = {$this->getId()};");
        if(!$executed){
          return false;
        }
        $executed = $GLOBALS["DB"]->exec("DELETE FROM users_profiles WHERE user_id = {$this->getId()};");
        if(!$executed){
          return false;
        }
        $executed = $GLOBALS['DB']->exec("DELETE FROM users_tasks WHERE user_id = {$this->getId()};");
        if (!$executed){
          return false;
        }else{
          return true;
        }
      }

      function addTask($task_id)
      {
        $executed = $GLOBALS['DB']->prepare("INSERT INTO users_tasks (user_id, task_id) VALUES ({$this->getId()}, :task_id);");
        $executed->bindParam(':task_id', $task_id, PDO::PARAM_INT);
        $executed->execute();
        if($executed){
          return true;
        }else{
          return false;
        }
      }
      function getTask()
      {
        $returned_task = $GLOBALS['DB']->query("SELECT tasks.* FROM users JOIN users_tasks ON (users_tasks.user_id = users.id) JOIN tasks ON (tasks.id = users_tasks.task_id) WHERE users.id = {$this->getId()};");
        $all_task = array();
        foreach($returned_task as $task) {
            $each_task = new Task($task['task_name'], $task['task_description'], $task['assign_time'], $task['due_time'],$task['id']);
            array_push($all_task, $each_task);
          }
        return $all_task;
      }

    function addGroup($group_id)
    {
      $executed = $GLOBALS['DB']->exec("INSERT INTO users_groups (user_id, group_id) VALUES ({$this->getId()}, $group_id);");
      if($executed){
        return true;
      }else{
        return false;
      }
    }

    function getGroup(){
      $returned_groups = $GLOBALS['DB']->query("SELECT task_forces.* FROM users JOIN users_groups ON (users_groups.user_id = users.id) JOIN task_forces ON (task_forces.id = users_groups.group_id) WHERE users.id = {$this->getId()};");
      $all_groups = array();
        foreach($returned_groups as $group){
          $each_group = new Group($group['group_name'], $group['public'], $group['id']);
          array_push($all_groups, $each_group);
        }
      return $all_groups;
    }
    function getTasksinGroup(){
      $returned_tasks = $GLOBALS['DB']->query("SELECT tasks.* FROM tasks JOIN users_tasks ON (tasks.id = users_tasks.task_id) JOIN users ON (users_tasks.user_id = users.id) WHERE users.id = {$this->getId()};");
      $all_tasks = array();
        foreach($returned_tasks as $task){
          $each_task = new Task($task['task_name'], $task['task_description'], $task['assign_time'], $task['due_time'], $task['id']);
          array_push($all_tasks, $each_task);
        }
      return $all_tasks;
    }

      function joinUserProfile($profile_id)
      {
          $executed = $GLOBALS['DB']->exec("INSERT INTO users_profiles (user_id, profile_id) VALUES ({$this->getId()}, $profile_id);");
          if ($executed)
          {
            return true;
          } else {
            return false;
          }
      }
      static function usernameArray()
      {
        $usernameArray = array();
        $executed = $GLOBALS['DB']->query("SELECT * FROM users;");
        $results = $executed->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $result){
          array_push($usernameArray, $result['username']);
        }
        return $usernameArray;
      }
      static function userpasswordArray()
      {
        $userpasswordArray = array();
        $executed = $GLOBALS['DB']->query("SELECT * FROM users;");
        $results = $executed->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $result){
          array_push($userpasswordArray, $result['password']);
        }
        return $userpasswordArray;
      }

      static function login($username, $password)
      {
        $check = $GLOBALS['DB']->prepare("SELECT * FROM users WHERE username = :username AND password = :password;");
        $check->bindParam(':username', $username, PDO::PARAM_STR);
        $check->bindParam(':password', $password, PDO::PARAM_STR);
        $check->execute();
        $result = $check->fetch(PDO::FETCH_ASSOC);
        $user = new User($result['username'], $result['password'], $result['id']);
        return $result['id'];
      }

      function saveGroupRequest($group_id, $sender_id){
        $executed = $GLOBALS['DB']->prepare("INSERT INTO group_requests (group_id, user_id, sender_id) VALUES (:group_id, {$this->getId()}, :sender_id);");
        $executed->bindParam(':group_id', $group_id, PDO::PARAM_INT);
        $executed->bindParam(':sender_id', $sender_id, PDO::PARAM_INT);
        $executed->execute();
        if($executed){
          return true;
        } else {
          return false;
        }
      }

      function findGroupRequest(){
        $executed = $GLOBALS['DB']->query("SELECT users.id AS user_id, task_forces.id AS group_id, username, group_name, sender_id FROM users JOIN group_requests ON (group_requests.user_id = users.id) JOIN task_forces ON (group_requests.group_id = task_forces.id) WHERE users.id = {$this->getId()};");
        $result = $executed->fetchAll(PDO::FETCH_ASSOC);
        return $result;
      }

    function deleteGroupRequest($group_id, $sender_id){
      $executed = $GLOBALS['DB']->prepare("DELETE FROM group_requests WHERE user_id = {$this->getId()} AND group_id = :group_id AND sender_id = :sender_id;");
      $executed->bindParam(':group_id', $group_id, PDO::PARAM_INT);
      $executed->bindParam(':sender_id', $sender_id, PDO::PARAM_INT);
      $executed->execute();
      if(!$executed){
        return false;
      } else {
        return true;
      }
    }
    function addFriend($friend)
    {
        $executed = $GLOBALS['DB']->exec("INSERT INTO friends (friend_one, friend_two) VALUES ({$this->getId()}, {$friend});");
        if($executed){
          return true;
        }else{
          return false;
        }
    }
    function findAllFriends()
    {
      $executed = $GLOBALS['DB']->query("SELECT friends.friend_two, users.username, profiles.* FROM friends JOIN users ON(users.id = friends.friend_two) JOIN users_profiles ON (users_profiles.user_id = friends.friend_two) JOIN profiles ON (users_profiles.profile_id = profiles.id) WHERE friends.friend_one = {$this->getId()};");
      $friends = array();
      $returned_friends = $executed->fetchAll(PDO::FETCH_ASSOC);
      foreach($returned_friends as $profile){
        $newProfile = new Profile ($profile['first_name'], $profile['last_name'], $profile['picture'], $profile['bio'],$profile['id'],$profile['join_date']);
        array_push($friends, $newProfile);
      }
      return $friends;
    }
    function findAllOtherFriends()
    {
      $executed = $GLOBALS['DB']->query("SELECT friends.friend_one, users.username, profiles.* FROM friends JOIN users ON(users.id = friends.friend_one) JOIN users_profiles ON (users_profiles.user_id = friends.friend_one) JOIN profiles ON (users_profiles.profile_id = profiles.id) WHERE friends.friend_two = {$this->getId()};");
      $friends = array();
      $returned_friends = $executed->fetchAll(PDO::FETCH_ASSOC);
      foreach($returned_friends as $profile){
        $newProfile = new Profile ($profile['first_name'], $profile['last_name'], $profile['picture'], $profile['bio'],$profile['id'],$profile['join_date']);
        array_push($friends, $newProfile);
      }
      return $friends;
    }
    function saveFriendRequest($receiver_id){
      $executed = $GLOBALS['DB']->exec("INSERT INTO friend_request (sender_id, receiver_id) VALUES ({$this->getId()}, $receiver_id);");
      if($executed){
        return true;
      } else {
        return false;
      }
    }

    function findFriendRequest(){
      $executed = $GLOBALS['DB']->query("SELECT * FROM friend_request WHERE (friend_request.receiver_id = {$this->getId()});");
      $returned_request = $executed->fetchAll(PDO::FETCH_ASSOC);
      $allrequest= array();
      foreach($returned_request as $request){
        $newRequest = Profile::getProfileUsingId($request['sender_id']);
        array_push($allrequest, $newRequest);
      }
      return $allrequest;
    }

  function deleteFriendRequest($sender_id, $receiver_id){
    $executed = $GLOBALS['DB']->prepare("DELETE FROM friend_request WHERE receiver_id = {$this->getId()} AND sender_id = :sender_id AND receiver_id = :receiver_id;");
    $executed->bindParam(':sender_id', $sender_id, PDO::PARAM_INT);
    $executed->bindParam(':receiver_id', $receiver_id, PDO::PARAM_INT);
    $executed->execute();
    if(!$executed){
      return false;
    } else {
      return true;
    }
  }
  function findAllFriendsId()
  {
    $executed = $GLOBALS['DB']->query("SELECT friends.friend_two, users.username, profiles.* FROM friends JOIN users ON(users.id = friends.friend_two) JOIN users_profiles ON (users_profiles.user_id = friends.friend_two) JOIN profiles ON (users_profiles.profile_id = profiles.id) WHERE friends.friend_one = {$this->getId()};");
    $friends = array();
    $returned_friends = $executed->fetchAll(PDO::FETCH_ASSOC);
    foreach($returned_friends as $profile){
      $newProfile = $profile['id'];
      array_push($friends, $newProfile);
    }
    return $friends;
  }
  function findAllOtherFriendsId()
  {
    $executed = $GLOBALS['DB']->query("SELECT friends.friend_one, users.username, profiles.* FROM friends JOIN users ON(users.id = friends.friend_one) JOIN users_profiles ON (users_profiles.user_id = friends.friend_one) JOIN profiles ON (users_profiles.profile_id = profiles.id) WHERE friends.friend_two = {$this->getId()};");
    $friends = array();
    $returned_friends = $executed->fetchAll(PDO::FETCH_ASSOC);
    foreach($returned_friends as $profile){
      $newProfile = $profile['id'];
      array_push($friends, $newProfile);
    }
    return $friends;
  }
  }





 ?>
