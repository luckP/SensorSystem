<?php

  class Entity{
    function __construct(){}

    protected function init_connection(){
      // $this->link = mysqli_init();
      $link = mysqli_connect( host, user, password, db );
      if(!$link)
        die("Connection error: " . mysqli_connect_error());
      return $link;
    }

    protected function close_connection($link){
      mysqli_close ( $link );
    }

    public function insert(){}

    public function update(){}

    public function delete(){}

    public static function select(){}

    public static function select_by_cols($colmns){}

  }

  // ----------------------------------USER------------------------------------

  class User extends Entity{
    private $id;
    private $email;
    private $password;
    private $status;

    function __construct($e, $p, $s){
      $this->email = $e;
      $this->password = $p;
      $this->status = $s;
    }

    function insert(){
      if(!$this->id){
        $link = parent::init_connection();
        $query = "INSERT INTO user (email, password, status) VALUES ('$this->email' , '$this->password', '$this->status');";
        mysqli_query($link, $query);
        $this->id = mysqli_insert_id($link);
        parent::close_connection($link);
      }
      else{
        $this->update();
      }
    }

    public function update(){
      if($this->id){
        $link = parent::init_connection();
        $query = "UPDATE user SET email = '$this->email', password = '$this->password', status= '$this->status' WHERE ( `id` = $this->id );";
        mysqli_query($link, $query);
        parent::close_connection($link);
      }
      else {
        $this->insert();
      }
    }

    public function delete(){
      if($this->id){
        $link = parent::init_connection();
        $query = "DELETE FROM user WHERE (id = $this->id); ";
        mysqli_query($link, $query);
        parent::close_connection($link);

        $this->id = 0;
        $this->email = '';
        $this->password = '';
      }
    }

    public static function select(){
      $entity = new Entity();
      $link = $entity->init_connection();
      $query = "SELECT * FROM user";
      mysqli_query($link, $query);
      $result = $link->query($query);
      $users = array();

      if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
              // echo "id: " . $row["id"];
              $user = new User($row['email'], $row['password'], $row['status']);
              $user->setId($row['id']);
              array_push($users, $user);
          }
      }

      $entity->close_connection($link);
      return $users;
    }

    public function selectByEmailPassword(){
      $link = parent::init_connection();
      $query = "SELECT * FROM user where email = '$this->email' && password = '$this->password'";
      mysqli_query($link, $query);
      $result = $link->query($query);

      if ($result->num_rows == 1) {
          while($row = $result->fetch_assoc()) {
              // echo "id: " . $row["id"];
              $this->setId($row['id']);
              $this->setStatus($row['status']);
          }
      }

      parent::close_connection($link);
    }

    public function select_by_user_id(){
      $link = parent::init_connection();
      $query = "SELECT * FROM user where id = $this->id";
      mysqli_query($link, $query);
      $result = $link->query($query);
      if ($result->num_rows == 1) {
          while($row = $result->fetch_assoc()) {
              // echo "id: " . $row["id"];
              // $this->setId($row['id']);
              $this->setEmail($row['email']);
              $this->setPassword($row['password']);
              $this->setStatus($row['status']);
          }
      }
      else{
        $this->setId(0);
      }

      parent::close_connection($link);
    }

    public static function select_by_cols($colmns){}

    public function toString(){
      return "id: $this->id, email: $this->email, password: $this->password, status: $this->status";
    }

    public function toJson(){
      return "{'id': $this->id, 'email': '$this->email', 'status': '$this->status'}";
    }

    // GETs and SETs
    public function getId(){
      return $this->id;
    }

    public function setId($id){
      $this->id = $id;
    }

    public function getEmail(){
      return $this->email;
    }

    public function setEmail($email){
      $this->email = $email;
    }

    public function getPassword(){
      return $this->password;
    }

    public function setPassword($password){
      $this->password = $password;
    }

    public function getStatus(){
      return $this->status;
    }

    public function setStatus($status){
      $this->status = $status;
    }
  }

  // -----------------------------AuthTokens-----------------------------------

  class AuthTokens extends Entity{
    private $id = 0;
    private $auth_token;
    private $date_creation;
    private $date_expiry;
    private $user_id;

    function __construct($user_id){
      $this->date_creation = date('Y-m-d H:i:s');
      $this->date_expiry = date('Y-m-d H:i:s', strtotime(' +1 day'));
      $this->user_id = $user_id;

      $str = $this->user_id . $this->date_creation;
      $this->auth_token  = md5 ($str);
    }

    function insert(){
      if(!$this->id){
        $link = parent::init_connection();
        $query = "INSERT INTO auth_tokens (auth_token, date_creation, date_expiry, user_id) VALUES ('$this->auth_token', '$this->date_creation', '$this->date_expiry', '$this->user_id');";
        mysqli_query($link, $query);
        $this->id = mysqli_insert_id($link);
        parent::close_connection($link);
      }
    }

    public function update(){
      if($this->id){
        $link = parent::init_connection();
        $query = "UPDATE auth_tokens SET auth_token = '$this->auth_token', date_creation = '$this->date_creation', date_expiry = '$this->date_expiry', user_id = '$this->user_id' WHERE ( `id` = $this->id );";
        mysqli_query($link, $query);
        parent::close_connection($link);
      }
      else {
        $this->insert();
      }
    }

    public function delete(){
      if($this->id){
        $link = parent::init_connection();
        $query = "DELETE FROM auth_tokens WHERE (id = $this->id);";
        mysqli_query($link, $query);
        // $error = empty(mysqli_error($link));
        parent::close_connection($link);

        return True;
      }

      return Frue;
    }

    public static function select(){
      $entity = new Entity();
      $link = $entity->init_connection();
      $query = "SELECT * FROM auth_tokens";
      mysqli_query($link, $query);
      $result = $link->query($query);
      $auth_tokens = array();

      if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
              $auth_token = new AuthTokens($row['user_id']);
              $auth_token->setId($row['id']);
              $auth_token->setDateCreation($row['date_creation']);
              $auth_token->setDateExpiry($row['date_expiry']);
              $auth_token->setAuthToken($row['auth_token']);
              array_push($auth_tokens, $auth_token);
          }
      }

      $entity->close_connection($link);
      return $auth_tokens;
    }

    public static function select_by_cols($colmns){}

      public function select_by_user_id(){
        $link = parent::init_connection();
        $query = "SELECT * FROM auth_tokens WHERE user_id = $this->user_id";
        mysqli_query($link, $query);
        $result = $link->query($query);

        if ($result->num_rows == 1) {
            while($row = $result->fetch_assoc()) {
                $this->setId($row['id']);
                $this->setDateCreation($row['date_creation']);
                $this->setDateExpiry($row['date_expiry']);
                $this->setAuthToken($row['auth_token']);
            }
          parent::close_connection($link);
      }
      else {
        $this->setId(0);
        $this->delete();
      }
    }

    public function select_by_token(){
      $link = parent::init_connection();
      $query = "SELECT * FROM auth_tokens WHERE auth_token = '$this->auth_token'";
      mysqli_query($link, $query);
      $result = $link->query($query);

      if ($result->num_rows == 1) {
          while($row = $result->fetch_assoc()) {
              $this->setId($row['id']);
              $this->setDateCreation($row['date_creation']);
              $this->setDateExpiry($row['date_expiry']);
              $this->setAuthToken($row['auth_token']);
              $this->setUserId($row['user_id']);
          }
        parent::close_connection($link);
    }

    else{
      $this->setId(0);
    }
  }

    public function checkValidToken(){
      if($this->getDateExpiry()<date('Y-m-d H:i:s')){
        $this->delete();

        $this->setDateCreation(date('Y-m-d H:i:s'));
        $this->setDateExpiry(date('Y-m-d H:i:s', strtotime(' +1 day')));

        $str = $this->user_id . $this->date_creation;
        $this->auth_token  = md5 ($str);

        $this->insert();
      }
      else{
        $this->setDateExpiry(date('Y-m-d H:i:s', strtotime(' +1 day')));
        $this->update();
      }
    }

    public function getUserByToken(){
      $link = parent::init_connection();

      $user = new User('', '', '');
      $user->setId($this->getUserId());
      $user->select_by_user_id();

      if($user->getId()!=0){
        return $user;
      }
      // echo '->';
      return False;

      // $query = "SELECT * FROM user WHERE id = '$this->getUserId()'";
      // mysqli_query($link, $query);
      // $result = $link->query($query);
      //
      // if ($result->num_rows == 1) {
      //     while($row = $result->fetch_assoc()) {
      //       $user = new User($row['email'], $row['password'], $row['status']);
      //       $user->setId($row['id']);
      //       return $user;
      //     }
      //   parent::close_connection($link);
      // }

      return False;
    }

    public function toString(){
      return "id: $this->id, auth_token: $this->auth_token, date_creation: $this->date_creation, date_expiry: $this->date_expiry, user_id: $this->user_id";
    }

    public function toJson(){
      return "{'id': '$this->id', 'auth_token': '$this->auth_token', 'date_creation': '$this->date_creation', 'date_expiry': '$this->date_expiry', 'user_id': '$this->user_id'}";

    }

    // GETs and SETs

    public function getId(){
      return $this->id;
    }

    public function setId($id){
      $this->id = $id;
    }

    public function getAuthToken(){
      return $this->auth_token;
    }

    public function setAuthToken($auth_token){
      $this->auth_token = $auth_token;
    }

    public function getDateCreation(){
      return $this->date_creation;
    }

    public function setDateCreation($date_creation){
      $this->date_creation = $date_creation;
    }

    public function getDateExpiry(){
      return $this->date_expiry;
    }

    public function setDateExpiry($date_expiry){
      $this->date_expiry = $date_expiry;
    }

    public function getUserId(){
      return $this->user_id;
    }

    public function setUserId($user_id){
      $this->user_id = $user_id;
    }
  }

  // -----------------------------Company--------------------------------------

  class Company extends Entity{
    private $id;
    private $name;

    function __construct($name){
      $this->name = $name;
    }

    function insert(){
      if(!$this->id){
        $link = parent::init_connection();
        $query = "INSERT INTO company (name) VALUES ('$this->name');";
        mysqli_query($link, $query);
        $this->id = mysqli_insert_id($link);
        parent::close_connection($link);
      }
      else{
        $this->update();
      }
    }

    public function update(){
      if($this->id){
        $link = parent::init_connection();
        $query = "UPDATE company SET name = '$this->name' WHERE ( `id` = $this->id );";
        mysqli_query($link, $query);
        parent::close_connection($link);
      }
      else {
        $this->insert();
      }
    }

    public function delete(){
      if($this->id){
        $link = parent::init_connection();
        $query = "DELETE FROM company WHERE (id = $this->id); ";
        mysqli_query($link, $query);
        parent::close_connection($link);

        $this->id = 0;
        $this->email = '';
        $this->password = '';
      }
    }

    public static function select(){
      $entity = new Entity();
      $link = $entity->init_connection();
      $query = "SELECT * FROM company";
      mysqli_query($link, $query);
      $result = $link->query($query);
      $companies = array();

      if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
              $company = new Company($row['name']);
              $company->setId($row['id']);
          }
      }

      $entity->close_connection($link);
      return $companies;
    }

    public static function select_by_cols($colmns){

    }

    public function toString(){
      return "id: $this->id, name: $this->name";
    }

    public function toJson(){
      return "{'id': '$this->id', 'name': '$this->name'}";

    }

    // GETs and SETs

    public function getId(){
      return $this->id;
    }

    public function setId($id){
      $this->id = $id;
    }

    public function getName(){
      return $this->name;
    }

    public function setName($name){
      $this->name = $name;
    }
  }

  // -----------------------------Sensor---------------------------------------

  class Sensor extends Entity{
    private $id;
    private $name;
    private $raw_min_val;
    private $raw_max_val;
    private $min_val;
    private $max_val;
    private $measure_units;
    private $company_id;

    function __construct($name, $raw_min_val, $raw_max_val, $min_val, $max_val, $measure_units, $company_id){
      $this->name = $name;
      $this->raw_min_val = $raw_min_val;
      $this->raw_max_val = $raw_max_val;
      $this->min_val = $min_val;
      $this->max_val = $max_val;
      $this->measure_units = $measure_units;
      $this->company_id = $company_id;
    }

    function insert(){
      if(!$this->id){

        $link = parent::init_connection();
        $query = "INSERT INTO sensor (name, raw_min_val, raw_max_val, min_val, max_val, measure_units, company_id) VALUES ( '$this->name', $this->raw_min_val, $this->raw_max_val, $this->min_val, $this->max_val, '$this->measure_units', $this->company_id);";
        mysqli_query($link, $query);
        $this->id = mysqli_insert_id($link);
        parent::close_connection($link);
      }
      else{
        $this->update();
      }
    }

    public function update(){
      if($this->id){
        $link = parent::init_connection();
        $query = "UPDATE sensor SET  name = $this->name, raw_min_val = $this->raw_min_val, raw_max_val = $this->raw_max_val, min_val = $this->min_val, max_val = $this->max_val, measure_units = $this->measure_units, company_id = $this->company_id WHERE ( `id` = $this->id );";
        mysqli_query($link, $query);
        parent::close_connection($link);
      }
      else {
        $this->insert();
      }
    }

    public function delete(){
      if($this->id){
        $link = parent::init_connection();
        $query = "DELETE FROM sensor WHERE (id = $this->id); ";
        mysqli_query($link, $query);
        parent::close_connection($link);

        $this->id = 0;
        $this->email = '';
        $this->password = '';
      }
    }

    public static function select(){
      $entity = new Entity();
      $link = $entity->init_connection();
      $query = "SELECT * FROM sensor";
      mysqli_query($link, $query);
      $result = $link->query($query);
      $companies = array();

      if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
              $sensor = new Sensor($row['name'], $row['raw_min_val'], $row['raw_max_val'], $row['min_val'], $row['max_val'], $row['measure_units'], $row['company_id']);
              $sensor->setId($row['id']);
          }
      }

      $entity->close_connection($link);
      return $companies;
    }

    public static function select_by_cols($colmns){

    }

    public function toString(){
      return "id: $this->id, name: $this->name, raw_min_val: $this->raw_min_val, raw_max_val: $this->raw_max_val, min_val: $this->min_val, max_val: $this->max_val, measure_units: $this->measure_units, company_id: $this->company_id ";
    }

    public function toJson(){
      return "{'id': '$this->id', 'name': '$this->name', 'raw_min_val': '$this->raw_min_val', 'raw_max_val': '$this->raw_max_val', 'min_val': '$this->min_val', 'max_val': '$this->max_val', 'measure_units': '$this->measure_units', 'company_id': '$this->company_id'}";
    }

    // GETs and SETs

    public function getId(){
      return $this->id;
    }

    public function setId($id){
      $this->id = $id;
    }

    public function getName(){
      return $this->name;
    }

    public function setName($name){
      $this->name = $name;
    }

    public function getRawMinVal(){
      return $this->raw_min_val;
    }

    public function setRawMinVal($raw_min_val){
      $this->raw_min_val = $raw_min_val;
    }

    public function getRawMaxVal(){
      return $this->raw_max_val;
    }

    public function setRawMaxVal($raw_max_val){
      $this->raw_max_val = $raw_max_val;
    }

    public function getMinVal(){
      return $this->min_val;
    }

    public function setMinVal($min_val){
      $this->min_val = $min_val;
    }

    public function getMaxVal(){
      return $this->max_val;
    }

    public function setMaxVal($max_val){
      $this->max_val = $max_val;
    }

    public function getMeasureUnits(){
      return $this->measure_units;
    }

    public function setMeasureUnits($measure_units){
      $this->measure_units = $measure_units;
    }

    public function getCompanyId(){
      return $this->company_id;
    }

    public function setCompanyId($company_id){
      $this->company_id = $company_id;
    }
  }

  // -----------------------------Sensor_val-----------------------------------

  class SensorVal extends Entity{
    private $id;
    private $sensor_id;
    private $val;

    function __construct($sensor_id, $val){
      $this->sensor_id = $sensor_id;
      $this->val = $val;
    }

    function insert(){
      if(!$this->id){
        $link = parent::init_connection();
        $query = "INSERT INTO sensor_val (sensor_id, val) VALUES ($this->sensor_id, $this->val);";
        mysqli_query($link, $query);
        $this->id = mysqli_insert_id($link);
        parent::close_connection($link);
      }
      else{
        $this->update();
      }
    }

    public function update(){
      if($this->id){
        $link = parent::init_connection();
        $query = "UPDATE sensor_val SET sensor_id = $this->sensor_id, val = $this->val WHERE ( id = $this->id );";
        mysqli_query($link, $query);
        parent::close_connection($link);
      }
      else {
        $this->insert();
      }
    }

    public function delete(){
      if($this->id){
        $link = parent::init_connection();
        $query = "DELETE FROM sensor_val WHERE (id = $this->id); ";
        mysqli_query($link, $query);
        parent::close_connection($link);

        $this->id = 0;
        $this->email = '';
        $this->password = '';
      }
    }

    public static function select(){
      $entity = new Entity();
      $link = $entity->init_connection();
      $query = "SELECT * FROM sensor_val";
      mysqli_query($link, $query);
      $result = $link->query($query);
      $companies = array();

      if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
              $company = new SensorVal($row['sensor_id'], $row['val']);
              $company->setId($row['id']);
          }
      }

      $entity->close_connection($link);
      return $companies;
    }

    public static function select_by_cols($colmns){

    }

    public function toString(){
      return "id: $this->id, sensor_id: $this->sensor_id, val: $this->val";
    }

    public function toJson(){
      return "{'id': '$this->id', 'sensor_id': '$this->sensor_id', 'val': '$this->val'}";

    }

    // GETs and SETs

    public function getId(){
      return $this->id;
    }

    public function setId($id){
      $this->id = $id;
    }

    public function getSensorId(){
      return $this->sensor_id;
    }

    public function setSensorId($sensor_id){
      $this->sensor_id = $sensor_id;
    }

    public function getVal(){
      return $this->val;
    }

    public function setVal($val){
      $this->val = $val;
    }
  }

  // -----------------------------User_company---------------------------------

  class UserCompany extends Entity{
    private $id;
    private $user_id;
    private $company_id;
    private $level;

    function __construct($user_id, $company_id, $level){
      $this->user_id = $user_id;
      $this->company_id = $company_id;
      $this->level = $level;
    }

    function insert(){
      if(!$this->id){
        $link = parent::init_connection();
        $query = "INSERT INTO user_company (user_id, company_id, level) VALUES ($this->user_id, $this->company_id, $this->level);";
        mysqli_query($link, $query);
        $this->id = mysqli_insert_id($link);
        parent::close_connection($link);
      }
      else{
        $this->update();
      }
    }

    public function update(){
      if($this->id){
        $link = parent::init_connection();
        $query = "UPDATE user_company SET user_id = $this->user_id, company_id = $this->company_id, level = $this->level WHERE ( id = $this->id );";
        mysqli_query($link, $query);
        parent::close_connection($link);
      }
      else {
        $this->insert();
      }
    }

    public function delete(){
      if($this->id){
        $link = parent::init_connection();
        $query = "DELETE FROM user_company WHERE (id = $this->id); ";
        mysqli_query($link, $query);
        parent::close_connection($link);

        $this->id = 0;
        $this->email = '';
        $this->password = '';
      }
    }

    public static function select(){
      $entity = new Entity();
      $link = $entity->init_connection();
      $query = "SELECT * FROM user_company";
      mysqli_query($link, $query);
      $result = $link->query($query);
      $companies = array();

      if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
              $company = new SensorVal($row['user_id'], $row['company_id'], $row['level']);
              $company->setId($row['id']);
          }
      }

      $entity->close_connection($link);
      return $companies;
    }

    public static function select_by_cols($colmns){

    }

    public function toString(){
      return "id: $this->id, user_id: $this->user_id, company_id: $this->company_id, level: '$this->level";
    }

    public function toJson(){
      return "{ 'id': '$this->id', 'user_id': '$this->user_id', 'company_id': '$this->company_id', 'level: '$this->level }";
    }

    // GETs and SETs

    public function getId(){
      return $this->id;
    }

    public function setId($id){
      $this->id = $id;
    }

    public function getUserId(){
      return $this->user_id;
    }

    public function setUserId($user_id){
      $this->user_id = $user_id;
    }

    public function getCompanyId(){
      return $this->company_id;
    }

    public function setCompanyId($company_id){
      $this->company_id = $company_id;
    }

    public function getLevel(){
      return $this->level;
    }

    public function setLevel($level){
      $this->level = $level;
    }
  }
 ?>
