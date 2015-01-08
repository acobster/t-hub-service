<?php

namespace Data;

class DB {
  protected static $singleton;

  public static function get() {
    if( ! self::$singleton ) {
      self::$singleton = new DB();
    }

    return self::$singleton;
  }

  protected function __construct() {
    $connectionString = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
    if( defined('DB_SOCKET') ) $connectionString .= ';unix_socket=' . DB_SOCKET;
    $this->pdo = new \PDO( $connectionString, DB_USER, DB_PASSWORD );
  }

  /**
   * INSERT or UPDATE
   * @return int number of rows affected
   */
  public function write( $sql ) {
    return $sql;
  }

  public function read( $sql ) {
    $query = $this->pdo->query( $sql );

    if( !$query ) {
      DBException::fromPDO( $this->pdo );
    }

    return $query->fetchAll( \PDO::FETCH_ASSOC );
  }

  public function quote( $str ) {
    return $this->pdo->quote();
  }
}