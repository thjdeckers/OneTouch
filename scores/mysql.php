<?php

/* Utility functions */

function KeysAndValues( $array) {

  $keys = array();
  $values = array();
  
  foreach ( $array as $key => $value) {
    $keys[] = "`".$key."`";
    $values[] = $value;
  }
  
  return array( "keys" => $keys, "values" => $values);

}

/* CMysql : MySQL connection class */

class CMysql {

  var $errormsg = "";
  var $log = "";

  var $hostname = "";
  var $username = "";
  var $password = "";
  var $database = "";
  var $main_table = "";
  var $main_form = "";
  var $actual_table = "";  
  var $coll_id = "";  

  var $connection = 0;

  function connect( $in_hostname, $in_username, $in_password)
  {
    
    if ($this->connection) {
      $this->errormsg = "Already connected. Disconnect first.";
      return false;
    }

    $this->hostname = $in_hostname;
    $this->username = $in_username;
    $this->password = $in_password;

    $this->connection = mysql_connect( $this->hostname, $this->username, $this->password);
    if (!$this->connection)
    {
      $this->errormsg = "Failed to connect to server '$this->hostname'.<br>".mysql_error();
      return false;
    }

    return true;

  }

  function usedb( $in_database)
  {

    if (!$this->connection) {
      $this->errormsg = "Not connected to a MySQL server.";
      return false;
    }

    if (!mysql_select_db( $in_database, $this->connection))
    {
      $this->errormsg = "Failed to connect to database '$in_database'.<br>".mysql_error();
      return false;
    }

    $this->database = $in_database;

    return true;

  }

  function disconnect()
  {

    if (!$this->connection)
    {
      $this->errormsg = "Not connected to server.<br>";
      return false;
    }

    if (!mysql_close( $this->connection))
    {
      $this->errormsg = "Failed to close connection to server.<br>".mysql_error();
      return false;
    }
    
    return true;
    
  }
  
  function set_coll_id( $coll_id )
  {
      $this->coll_id = $coll_id;
  }

  function clr_coll_id( $coll_id )
  {
      $this->coll_id = 'undefined';
  }

  function get_coll_id()
  {
      return $this->coll_id;
  }

  function set_main_table( $table )
  {
      $this->main_table = $table;
  }

  function get_main_table()
  {
      return $this->main_table;
  }

  function set_main_form( $form )
  {
      $this->main_form = $form;
  }

  function get_main_form()
  {
      return $this->main_form;
  }

  function set_coll_cns( $cns )
  {
      $this->coll_cns = $cns;
  }

  function get_coll_cns()
  {
      return $this->coll_cns;
  }

  function query( $query )
  {

    if (!$this->connection) {
      $this->errormsg = "Not connected to server.<br>";
      return false;
    }
    
    $result = mysql_query( $query, $this->connection);
    if ( !$result) {
      echo "ERROR: In MySQL query: '".$query."'<br>";
      echo mysql_error()."<br>";
    }
    return $result;
      
  }
  

}

function execute ( $query) {

  global $mysql;

  $result = $mysql->query( $query);
  
  if ( $result) {
    echo "<p class=greencode>".str_replace( Chr(13), "<br />", $query)."</p>";
  } else {
    echo "<p class=redcode>".mysql_error()."<br>";
    echo str_replace( Chr(13), "<br />", $query)."</p>";
  }
  
  return $result;
  
}

?>
