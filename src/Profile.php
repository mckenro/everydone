<?php
  class Profile
  {
      private $first_name;
      private $last_name;
      private $picture;
      private $bio;
      private $id;
      private $date;

      function __construct($first_name, $last_name, $picture, $bio, $id=null, $date=null)
      {
          $this->first_name = $first_name;
          $this->last_name = $last_name;
          $this->picture = $picture;
          $this->bio = $bio;
          $this->id = $id;
          $this->date = $date;
      }
      function getDate()
      {
        return $this->date;
      }
      function getId()
      {
          return $this->id;
      }
      function setFirstName($new_first_name)
      {
          $this->first_name = $new_first_name;
      }
      function getFirstName()
      {
          return $this->first_name;
      }
      function setLastName($new_last_name)
      {
          $this->last_name = $new_last_name;
      }
      function getLastName()
      {
          return $this->last_name;
      }
      function setPicture($new_picture)
      {
          $this->picture = $new_picture;
      }
      function getPicture()
      {
          return $this->picture;
      }
      function setBio($new_bio)
      {
          $this->bio = $new_bio;
      }
      function getBio()
      {
          return $this->bio;
      }
      function save($first_name, $last_name, $bio, $pic)
      {
          $executed = $GLOBALS['DB']->prepare("INSERT INTO profiles (first_name, last_name, bio, picture, join_date) VALUES (:first_name, :last_name, :bio, :pic, NOW());");
          $executed->bindParam(':first_name', $first_name, PDO::PARAM_STR);
          $executed->bindParam(':last_name', $last_name, PDO::PARAM_STR);
          $executed->bindParam(':bio', $bio, PDO::PARAM_STR);
          $executed->bindParam(':pic', $pic, PDO::PARAM_STR);
          $executed->execute();
          if ($executed)
          {
              $this->id = $GLOBALS['DB']->lastInsertId();
              return true;
          } else {
              return false;
          }
      }

      static function deleteAll()
      {
          $executed = $GLOBALS['DB']->exec("DELETE FROM profiles;");
          if (!$executed)
          {
              return false;
          } else {
              return true;
          }
      }

      static function findProfile($id)
      {
          $executed = $GLOBALS['DB']->prepare("SELECT * FROM profiles WHERE id = :id;");
          $executed->bindParam(':id', $id, PDO::PARAM_INT);
          $executed->execute();
          $result = $executed->fetch(PDO::FETCH_ASSOC);
          $profile = new Profile ($result['first_name'], $result['last_name'], $result['picture'], $result['bio'], $result['id'], $result['join_date']);
          return $profile;
      }

      static function findByName($first_name)
      {
          $returned_profiles = array();
          $executed = $GLOBALS['DB']->prepare("SELECT * FROM profiles WHERE first_name = :name;");
          $executed->bindParam(':name', $first_name, PDO::PARAM_STR);
          $executed->execute();
          $results = $executed->fetchAll(PDO::FETCH_ASSOC);
          foreach ($results as $result)
          {
              $profile = new Profile ($result['first_name'], $result['last_name'], $result['picture'], $result['bio'], $result['id'], $result['join_date']);
              array_push($returned_profiles, $profile);
          }
          return $returned_profiles;
      }
      function updateProfile($new_first_name, $new_last_name, $new_pic, $new_bio)
      {
          $executed = $GLOBALS['DB']->prepare("UPDATE profiles SET first_name = :first_name, last_name = :last_name, picture = :picture, bio = :bio WHERE id = {$this->getId()};");
          $executed->bindParam(':first_name', $new_first_name, PDO::PARAM_STR);
          $executed->bindParam(':last_name', $new_last_name, PDO::PARAM_STR);
          $executed->bindParam(':picture', $new_pic, PDO::PARAM_STR);
          $executed->bindParam(':bio', $new_bio, PDO::PARAM_STR);
          $executed->execute();
          if ($executed)
          {
            return true;
          } else {
            return false;
          }
      }
      function delete()
      {
          $executed = $GLOBALS['DB']->exec("DELETE FROM profiles WHERE id = {$this->getId()};");
          if (!$executed)
          {
          $executed = $GLOBALS['DB']->exec("DELETE FROM users_profiles WHERE profile_id = {$this->getId()};");
          if (!$executed){
              return false;
          } else {
              return true;
          }
          }
      }
      static function getProfileUsingId($user_id)
      {
        $executed = $GLOBALS['DB']->prepare("SELECT profiles.* FROM profiles JOIN users_profiles ON (users_profiles.profile_id = profiles.id) JOIN users ON (users_profiles.user_id = users.id) WHERE users.id = :id;");
        $executed->bindParam(':id', $user_id, PDO::PARAM_INT);
        $executed->execute();
        $result = $executed->fetch(PDO::FETCH_ASSOC);
        $profile = new Profile($result['first_name'], $result['last_name'], $result['picture'], $result['bio'], $result['id'], $result['join_date']);
        return $profile;
      }
      function saveUsertoJoinTable($id)
      {
        $executed = $GLOBALS['DB']->prepare("INSERT INTO users_profiles (user_id, profile_id) VALUES (:id, {$this->getId()});");
        $executed->bindParam(':id', $id, PDO::PARAM_INT);
        $executed->execute();
        if ($executed) {
          return true;
        } else {
          return false;
        }
      }
      static function findProfilebyLastName($last_name)
      {
          $returned_profiles = array();
          $executed = $GLOBALS['DB']->prepare("SELECT * FROM profiles WHERE last_name = :name;");
          $executed->bindParam(':name', $last_name, PDO::PARAM_STR);
          $executed->execute();
          $results = $executed->fetchAll(PDO::FETCH_ASSOC);
          foreach ($results as $result)
          {
              $profile = new Profile ($result['first_name'], $result['last_name'], $result['picture'], $result['bio'], $result['id'], $result['join_date']);
              array_push($returned_profiles, $profile);
          }
          return $returned_profiles;
      }
      static function findByFullName($first_name, $last_name)
      {
          $returned_profiles = array();
          $executed = $GLOBALS['DB']->prepare("SELECT * FROM profiles WHERE first_name = :name AND last_name = :lastname;");
          $executed->bindParam(':name', $first_name, PDO::PARAM_STR);
          $executed->bindParam(':lastname', $last_name, PDO::PARAM_STR);
          $executed->execute();
          $results = $executed->fetchAll(PDO::FETCH_ASSOC);
          foreach ($results as $result)
          {
              $profile = new Profile ($result['first_name'], $result['last_name'], $result['picture'], $result['bio'], $result['id'], $result['join_date']);
              array_push($returned_profiles, $profile);
          }
          return $returned_profiles;
      }


    static function search($search){
      $returned_array = array();
      $executed = $GLOBALS['DB']->prepare("SELECT * FROM profiles WHERE first_name LIKE :search OR last_name LIKE :search;");
      $executed->bindParam(':search', $search, PDO::PARAM_STR);
      $executed->execute();
      $results = $executed->fetchAll(PDO::FETCH_ASSOC);
      foreach($results as $result){
        $new_profile = new Profile($result['first_name'], $result['last_name'], $result['picture'], $result['bio'], $result['id'], $result['join_date']);
        array_push($returned_array, $new_profile);
      }
      return $returned_array;
    }
    static function findUserbyProfileId($profile_id)
    {
      $executed = $GLOBALS['DB']->query("SELECT users.* FROM users JOIN users_profiles ON (users_profiles.user_id = users.id) JOIN profiles ON (users_profiles.profile_id = profiles.id) WHERE profiles.id = $profile_id;");
      $result = $executed->fetch(PDO::FETCH_ASSOC);
      $user = new User($result['username'], $result['password'], $result['id']);
      return $user;
    }
     function findUserbyProfileIdNotStatic($profile_id)
    {
      $executed = $GLOBALS['DB']->query("SELECT users.* FROM users JOIN users_profiles ON (users_profiles.user_id = users.id) JOIN profiles ON (users_profiles.profile_id = profiles.id) WHERE profiles.id = $profile_id;");
      $result = $executed->fetch(PDO::FETCH_ASSOC);
      $user = new User($result['username'], $result['password'], $result['id']);
      return $user;
    }

  }
?>
