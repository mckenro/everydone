<?php
  require_once __DIR__."/../vendor/autoload.php";
  require_once __DIR__."/../src/Group.php";
  require_once __DIR__."/../src/Task.php";
  require_once __DIR__."/../src/User.php";
  require_once __DIR__."/../src/Profile.php";

  use Symfony\Component\Debug\Debug;
  Debug::enable();
  use Symfony\Component\HttpFoundation\Request;
Request::enableHttpMethodParameterOverride();

  $app = new Silex\Application();
  $DB = new PDO('mysql:host=localhost:8889;dbname=appdata', 'root', 'root');
  $app['debug'] = true;

  $app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views'
  ));

  $app->get("/", function() use ($app) {
    return $app['twig']->render('index.html.twig', array('msg'=>''));
  });

  $app->get("/profile/{id}", function($id) use ($app) {
    $user = User::findUserbyId($id);
    $profile = Profile::getProfileUsingId($id);
    return $app['twig']->render('profile.html.twig', array('user_id' => $id, 'msg'=>'', 'user' => $user, 'profile' => $profile));
  });
  $app->post("/create_user", function() use ($app) {
    return $app['twig']->render('create_account.html.twig', array('msg'=>''));
  });
  $app->post("/create_account", function() use ($app) {
    $username = User::usernameArray();
    if (($_POST['password'] == $_POST['password1']) && (in_array($_POST['user_email'], $username) == 0))
    {
      $new_user = new User($_POST['user_email'], $_POST['password']);
      $new_user->save();
      return $app['twig']->render('create_profile.html.twig', array('user_id'=>$new_user->getId(), 'msg'=>''));
    } elseif (($_POST['password'] == $_POST['password1']) && (in_array($_POST['user_email'], $username) == 1)) {
      return $app['twig']->render('create_account.html.twig', array('msg'=>'That email is in use.'));
      return $app['twig']->render('profile.html.twig', array('user_id'=>$new_user->getId(), 'msg'=>''));
    } else {
      return $app['twig']->render('create_account.html.twig', array('msg'=>'Passwords need to match.'));
    }
  });

  $app->get("/viewprofile/{first_name}/{profile_id}/{id}", function($first_name, $profile_id, $id) use ($app) {
    $profile = Profile::findProfile($profile_id);
    $user = Profile::findUserbyProfileId($profile_id);
    $user_id = $user->getId();
    $groups = $user->getGroup();
    $me = User::findUserbyId($id);
    $friends = $me->findAllFriendsId();
    $friend = $me->findAllOtherFriendsId();
    foreach($friend as $afriend){
      array_push($friends, $afriend);
    };
    $inArray = in_array($profile_id, $friends);
    return $app['twig']->render('viewprofile.html.twig', array('profile'=>$profile,  'profile_id'=>$profile_id, 'user_id'=>$user_id,'friends'=> $inArray, 'groups' => $groups, 'id'=>$id));
  });
  $app->post("/viewprofile/{first_name}/{profile_id}/{id}", function($first_name, $profile_id, $id) use ($app) {
    $profile = Profile::findProfile($profile_id);
    $user = Profile::findUserbyProfileId($profile_id);
    $user_id = $user->getId();
    $groups = $user->getGroup();
    $friends = $me->findAllFriendsId();
    $friend = $me->findAllOtherFriendsId();
    foreach($friend as $afriend){
      array_push($friends, $afriend);
    };
    $inArray = in_array($profile_id, $friends);
    return $app['twig']->render('viewprofile.html.twig', array('profile'=>$profile,  'profile_id'=>$profile_id, 'user_id'=>$user_id,'friends' =>$inArray, 'groups' => $groups, 'id'=>$id));
  });
  $app->get("/homepage/{id}", function($id) use($app){
    $user = User::findUserbyId($id);
    $user_id = $user->getId();
    $groups = $user->getGroup();
    $group_requests = $user->findGroupRequest();
    $user_request = $user->findFriendRequest();
    $friends = $user->findAllFriends();
    $friend = $user->findAllOtherFriends();
    foreach($friend as $afriend){
      array_push($friends, $afriend);
    }
    return $app['twig']->render('homepage.html.twig', array('profile'=>Profile::getProfileUsingId($id), 'user'=>$user, 'groups'=>$groups,'user_id'=>$user_id, 'group_requests'=>$group_requests,'user_request'=>$user_request,"friends" => $friends));
  });

  $app->post("/homepage", function() use ($app) {
    if(isset($_POST['button'])){
      $new_profile = new Profile($_POST['first_name'], $_POST['last_name'], $_POST['profile_pic'], $_POST['bio']);
      $new_profile->save($new_profile->getFirstName(), $new_profile->getLastName(), $new_profile->getBio(), $new_profile->getPicture());
      $new_profile->saveUsertoJoinTable($_POST['user_id']);
      $user = User::findUserbyId($_POST['user_id']);
      $groups = $user->getGroup();
      $group_requests = $user->findGroupRequest();
      $user_request = $user->findFriendRequest();
      $friends = $user->findAllFriends();
      $friend = $user->findAllOtherFriends();
      foreach($friend as $afriend){
        array_push($friends, $afriend);
      }
      return $app['twig']->render('homepage.html.twig', array('profile'=>Profile::getProfileUsingId($_POST['user_id']), 'user'=>$user, 'groups'=>$groups,'user_id'=>$_POST['user_id'], 'group_requests'=>$group_requests,'user_request'=>$user_request,"friends" => $friends));
    } else {
        return $app['twig']->render('profile.html.twig', array('user_id'=>$_POST['user_id'], 'msg'=>''));
    }
  });
  $app->post("/login_user", function() use ($app) {
    $username = $_POST['username'];
    $password = $_POST['userpassword'];
    $user_id = User::login($username, $password);
    if ($user_id == null)
    {
      return $app['twig']->render('index.html.twig', array('msg'=>"Sorry, we could not find your account."));
    } else {
      $profile = Profile::getProfileUsingId($user_id);
      $user = User::findUserbyId($user_id);
      $groups = $user->getGroup();
      $group_requests = $user->findGroupRequest();
      $user_request = $user->findFriendRequest();
      $friends = $user->findAllFriends();
      $friend = $user->findAllOtherFriends();
      foreach($friend as $afriend){
        array_push($friends, $afriend);
      }
      return $app['twig']->render('homepage.html.twig', array('profile'=>$profile,'user'=>$user,'user_id'=>$user_id, 'groups'=>$groups, 'group_requests'=>$group_requests,'user_request'=>$user_request,"friends" => $friends));
    }
  });

  $app->post("/creategroup", function () use ($app) {
   if(($_POST['group'] != null) && (isset($_POST['privacy']))){
      $group = new Group($_POST['group'], $_POST['privacy']);
      $group->save();
      $group_id = $group->getId();
      $admin_id = $group->groupAdminId();
      $user = User::findUserbyId($_POST['user_id']);
      $user->addGroup($group_id);
      $group_requests = $user->findGroupRequest();
      $user_request = $user->findFriendRequest();
      $friends = $user->findAllFriends();
      $friend = $user->findAllOtherFriends();
      foreach($friend as $afriend){
        array_push($friends, $afriend);
      }
      $groups = $user->getGroup();
      return $app['twig']->render('homepage.html.twig', array('profile'=>Profile::getProfileUsingId($_POST['user_id']), 'user'=>User::findUserbyId($_POST['user_id']), 'user_id'=>$_POST['user_id'], 'groups'=>$groups, 'group_requests'=>$group_requests, 'user_request'=>$user_request, 'friends' => $friends));
    } else {
      $user = User::findUserbyId($_POST['user_id']);
      $group_requests = $user->findGroupRequest();
      $user_request = $user->findFriendRequest();
      $friends = $user->findAllFriends();
      $friend = $user->findAllOtherFriends();
      foreach($friend as $afriend){
        array_push($friends, $afriend);
      }
      $groups = $user->getGroup();
      return $app['twig']->render('homepage.html.twig', array('profile'=>Profile::getProfileUsingId($_POST['user_id']), 'user'=>User::findUserbyId($_POST['user_id']), 'user_id'=>$_POST['user_id'], 'groups'=>$groups, 'group_requests'=>$group_requests,'user_request'=>$user_request,"friends" => $friends));
    }
  });

  $app->get("/group/{id}", function ($id) use ($app) {
    $user = User::findUserbyId($id);
    $groups = Group::findGroupByUserId($id);
    $group_requests = $user->findGroupRequest();
    $user_request = $user->findFriendRequest();
    $friends = $user->findAllFriends();
    $friend = $user->findAllOtherFriends();
    foreach($friend as $afriend){
      array_push($friends, $afriend);
    }
    return $app['twig']->render('homepage.html.twig', array('groups'=>$groups, 'user_id'=>$id, 'user'=>$user, 'profile'=>Profile::getProfileUsingId($id), 'group_requests'=>$group_requests,'user_request'=>$user_request,"friends" => $friends));
  });

  $app->get("/groupinfo/{group_id}/{user_id}", function ($group_id, $user_id) use ($app) {
    $group = Group::find($group_id);
    $admin_id = $group->groupAdminId();
    $user = User::findUserbyId($user_id);
    $tasks = Task::getAllByGroupId($group_id);
    $assigned = Task::getAssignedTask($group_id);
    foreach($assigned as $assign){
      foreach($tasks as $key=>$value){
        if(($assign->getName()) == ($value->getName())){
          array_splice($tasks, $key, 1);
        }
      }
    }
    return $app['twig']->render('group.html.twig', array('group_id'=>$group->getId(), 'admin_id'=>$admin_id, 'user'=>$user, 'msg'=>'', 'tasks'=>$tasks, 'assignedtasks'=>$assigned , 'unassignedtasks'=>$tasks, 'user_id' => $user_id, 'groupname'=>$group->getGroupName()));
  });


  $app->post("/search/{id}", function($id) use($app){
      $user = User::findUserbyId($id);
      $user_id = $user->getId();
      $search = '%'.$_POST['searchName'].'%';
      $results = Profile::search($search);
      if($_POST['searchName'] != null){
        return $app['twig']->render('search_results.html.twig', array('profiles'=>$results, 'msg'=>'', 'user_id'=>$user_id));
      } else {
        return $app['twig']->render('search_results.html.twig', array('profiles'=>'', 'user_id'=>$id, 'msg'=>'No Match!', 'user'=>$user));
      }
  });

  $app->post("/sendinvite", function() use($app){
    if(!empty($_POST['user'])){
      $group = Group::find($_POST['group_id']);
      $user_name_array = User::usernameArray();
      if(in_array($_POST['user'], $user_name_array)){
        $user = User::findByUserName($_POST['user']);
        $user->saveGroupRequest($_POST['group_id'], $_POST['user_id']);
        $tasks = Task::getAllByGroupId($_POST['group_id']);
        $assigned = Task::getAssignedTask($_POST['group_id']);
        foreach($assigned as $assign){
          foreach($tasks as $key=>$value){
            if(($assign->getName()) == ($value->getName())){
              array_splice($tasks, $key, 1);
            }
          }
        }
        return $app['twig']->render('group.html.twig', array('group_id'=>$_POST['group_id'], 'admin_id'=>$_POST['admin_id'], 'user'=>User::findUserbyId($_POST['user_id']), 'msg'=>'Invitation has sent!', 'tasks'=>$tasks, 'assignedtasks'=>$assigned, 'unassignedtasks'=>$tasks, 'groupname'=>$group->getGroupName(), 'user_id'=>$_POST['user_id']));
      } else {
        $tasks = Task::getAllByGroupId($_POST['group_id']);
        $assigned = Task::getAssignedTask($_POST['group_id']);
        foreach($assigned as $assign){
          foreach($tasks as $key=>$value){
            if(($assign->getName()) == ($value->getName())){
              array_splice($tasks, $key, 1);
            }
          }
        }
        return $app['twig']->render('group.html.twig', array('group_id'=>$_POST['group_id'], 'admin_id'=>$_POST['admin_id'], 'user'=>User::findUserbyId($_POST['user_id']), 'msg'=>'User is not existed!', 'tasks'=>$tasks, 'assignedtasks'=>$assigned, 'unassignedtasks'=>$tasks, 'groupname'=>$group->getGroupName(), 'user_id'=>$_POST['user_id']));
      }
    }
  });

  $app->post("/groupaccept", function () use ($app) {
    $user = User::findUserbyId($_POST['user_id']);
    $user->addGroup($_POST['group_id']);
    $user->deleteGroupRequest($_POST['group_id'], $_POST['sender_id']);
    $group_requests = $user->findGroupRequest();
    $user_request = $user->findFriendRequest();
    $friends = $user->findAllFriends();
    $groups = $user->getGroup();
    $friend = $user->findAllOtherFriends();
    foreach($friend as $afriend){
      array_push($friends, $afriend);
    }
    return $app['twig']->render('homepage.html.twig', array('profile'=>Profile::getProfileUsingId($_POST['user_id']), 'user'=>User::findUserbyId($_POST['user_id']), 'user_id'=>$_POST['user_id'], 'groups'=>$groups, 'group_requests'=>$group_requests,'user_request'=>$user_request,"friends" => $friends));
  });

  $app->post("/grouprefuse", function () use ($app) {
    $user = User::findUserbyId($_POST['user_id']);
    $user->deleteGroupRequest($_POST['group_id'], $_POST['sender_id']);
    $group_requests = $user->findGroupRequest();
    $user_request = $user->findFriendRequest();
    $friends = $user->findAllFriends();
    $groups = $user->getGroup();
    $friend = $user->findAllOtherFriends();
    foreach($friend as $afriend){
      array_push($friends, $afriend);
    }
    return $app['twig']->render('homepage.html.twig', array('profile'=>Profile::getProfileUsingId($_POST['user_id']), 'user'=>User::findUserbyId($_POST['user_id']), 'user_id'=>$_POST['user_id'], 'groups'=>$groups, 'group_requests'=>$group_requests,'user_request'=>$user_request,"friends" => $friends));
  });

  $app->post("/createtask", function () use ($app) {
    if(isset($_POST['createtask'])){
      $group = Group::find($_POST['group_id']);
      $new_task = new Task($_POST['task'], $_POST['description']);
      $new_task->save();
      $new_task->addGroupToTask($_POST['group_id']);
      $tasks = Task::getAllByGroupId($_POST['group_id']);
      $assigned = Task::getAssignedTask($_POST['group_id']);
      foreach($assigned as $assign){
        foreach($tasks as $key=>$value){
          if(($assign->getName()) == ($value->getName())){
            array_splice($tasks, $key, 1);
          }
        }
      }
      return $app['twig']->render('group.html.twig', array('group_id'=>$_POST['group_id'], 'admin_id'=>$_POST['admin_id'], 'user'=>User::findUserbyId($_POST['user_id']), 'msg'=>'Task created successfully', 'tasks'=>$tasks, 'assignedtasks'=>$assigned, 'unassignedtasks'=>$tasks, 'groupname'=>$group->getGroupName(), 'user_id'=>$_POST['user_id']));
    }
  });

  $app->patch("/edit_homepage/{id}", function($id) use($app){
    $user = User::findUserbyId($id);
    $profile = Profile::getProfileUsingId($id);
    $profile_pic = $profile->getPicture();
    $new_profile = $profile->updateProfile($_POST['first_name'], $_POST['last_name'], $_POST['profile_pic'], $_POST['bio']);
    $profile = Profile::getProfileUsingId($id);
    $groups = $user->getGroup();
    $group_requests = $user->findGroupRequest();
    $user_request = $user->findFriendRequest();
    $friends = $user->findAllFriends();
    return $app['twig']->render('homepage.html.twig', array('profile'=>$profile, 'user_id'=>$id, 'groups'=>$groups, 'group_requests'=>$group_requests, 'user_request'=>$user_request, 'friends'=>$friends, 'user'=>$user));
  });

  $app->post("/task/{task_id}", function ($task_id) use ($app) {
    $task = Task::findTask($task_id);
    $assigned_user = $task->assignedUser();
    $user_id = $_POST['user_id'];
    $user = User::findUserbyId($user_id);
    return $app['twig']->render("task.html.twig", array('task'=>$task, 'assigned_user'=>$assigned_user,'user'=>$user, 'user_id'=>$user_id));
  });

  $app->post("/assignuser", function () use ($app) {
    if(isset($_POST['assign'])){
      $group = Group::find($_POST['group_id']);
      $user_name_in_group_array = $group->findAllUsersInTheGroup();
      if(in_array($_POST['username'], $user_name_in_group_array)){
        $admin_id = $group->groupAdminId();
        $user = User::findByUserName($_POST['username']);
        $user->addTask($_POST['task_id']);
        $task = Task::findTask($_POST['task_id']);
        $task->updateDue($_POST['duetime']);
        $tasks = Task::getAllByGroupId($_POST['group_id']);
        $assigned = Task::getAssignedTask($_POST['group_id']);
        foreach($assigned as $assign){
          foreach($tasks as $key=>$value){
            if(($assign->getName()) == ($value->getName())){
              array_splice($tasks, $key, 1);
            }
          }
        }
        return $app['twig']->render('group.html.twig', array('group_id'=>$group->getId(), 'admin_id'=>$admin_id, 'user'=>User::findUserbyId($_POST['user_id']), 'msg'=>'successfully assigned', 'tasks'=>$tasks, 'assignedtasks'=>$assigned, 'unassignedtasks'=>$tasks, 'groupname'=>$group->getGroupName(), 'user_id'=>$_POST['user_id']));
      } else {
        $admin_id = $group->groupAdminId();
        $tasks = Task::getAllByGroupId($_POST['group_id']);
        $assigned = Task::getAssignedTask($_POST['group_id']);
        foreach($assigned as $assign){
          foreach($tasks as $key=>$value){
            if(($assign->getName()) == ($value->getName())){
              array_splice($tasks, $key, 1);
            }
          }
        }
        return $app['twig']->render('group.html.twig', array('group_id'=>$group->getId(), 'admin_id'=>$admin_id, 'user'=>User::findUserbyId($_POST['user_id']), 'msg'=>'User is not in the group yet!', 'tasks'=>$tasks, 'assignedtasks'=>$assigned, 'unassignedtasks'=>$tasks, 'groupname'=>$group->getGroupName(), 'user_id'=>$_POST['user_id']));
      }
    }
  });
  $app->post('/sendFriendRequest', function() use ($app){
    $sender = User::findUserbyId($_POST['sender_id']);
    $receiver = User::findUserbyId($_POST['receiver_id']);
    $profile = Profile::getProfileUsingId($_POST['receiver_id']);
    $user = Profile::findUserbyProfileId($_POST['receiver_id']);
    $user_id = $receiver->getId();
    $groups = $receiver->getGroup();
    $id = ($_POST['sender_id']);
    $sender->saveFriendRequest($receiver->getId());
    $friends = $sender->findAllFriendsId();
    $friend = $sender->findAllOtherFriendsId();
    foreach($friend as $afriend){
      array_push($friends, $afriend);
    };
    $inArray = in_array($_POST['receiver_id'], $friends);
    return $app['twig']->render('viewprofile.html.twig', array('profile'=>$profile,  'profile_id'=>$_POST['receiver_id'],'friends'=> $inArray, 'user_id'=>$user_id, 'groups' => $groups, 'id'=>$id));
  });
  $app->post("/friendaccept", function () use ($app) {
    $user = User::findUserbyId($_POST['receiver_id']);
    $user->addfriend($_POST['sender_id']);
    $user->deleteFriendRequest($_POST['sender_id'], $_POST['receiver_id']);
    $group_requests = $user->findGroupRequest();
    $user_request = $user->findFriendRequest();
    $groups = $user->getGroup();
    $friends = $user->findAllFriends();
    $friend = $user->findAllOtherFriends();
    foreach($friend as $afriend){
      array_push($friends, $afriend);
    }
    return $app['twig']->render('homepage.html.twig', array('profile'=>Profile::getProfileUsingId($_POST['receiver_id']), 'user'=>$user, 'user_id'=>$_POST['receiver_id'], 'groups'=>$groups, 'group_requests'=>$group_requests,'user_request'=>$user_request,"friends" => $friends));
  });
  $app->post("/friendrefuse", function () use ($app) {
    $user = User::findUserbyId($_POST['receive_id']);
    $user->deleteFriendRequest($_POST['send_id'], $_POST['receive_id']);
    var_dump($_POST['receive_id']);
    $group_requests = $user->findGroupRequest();
    $user_request = $user->findFriendRequest();
    $groups = $user->getGroup();
    $friends = $user->findAllFriends();
    $friend = $user->findAllOtherFriends();
    foreach($friend as $afriend){
      array_push($friends, $afriend);
    }
    return $app['twig']->render('homepage.html.twig', array('profile'=>Profile::getProfileUsingId($_POST['receive_id']), 'user'=>$user, 'user_id'=>$_POST['receive_id'], 'groups'=>$groups, 'group_requests'=>$group_requests,'user_request'=>$user_request,"friends" => $friends));
  });

  $app->post("/deletetask", function () use ($app) {
    $group = Group::find($_POST['group_id']);
    $admin_id = $group->groupAdminId();
    $task = Task::findTask($_POST['task_id']);
    $task->delete();
    $tasks = Task::getAllByGroupId($_POST['group_id']);
    $assigned = Task::getAssignedTask($_POST['group_id']);
    foreach($assigned as $assign){
      foreach($tasks as $key=>$value){
        if(($assign->getName()) == ($value->getName())){
          array_splice($tasks, $key, 1);
        }
      }
    }
    return $app['twig']->render('group.html.twig', array('group_id'=>$group->getId(), 'admin_id'=>$admin_id, 'user'=>User::findUserbyId($_POST['user_id']), 'msg'=>'Delete successfully!', 'tasks'=>$tasks, 'assignedtasks'=>$assigned, 'unassignedtasks'=>$tasks, 'groupname'=>$group->getGroupName(), 'user_id'=>$_POST['user_id']));
  });

  return $app;
 ?>
