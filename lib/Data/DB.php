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

    // throw an exception on PDO error
    $this->pdo->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );

    // turn off MySQL number-quoting
    $this->pdo->setAttribute( \PDO::ATTR_EMULATE_PREPARES, false );
  }

  /**
   * INSERT or UPDATE
   * @return int number of rows affected
   */
  public function write( $sql, $values ) {
    $statement = $this->pdo->prepare( $sql );
    return $statement->execute( $values );
  }

  public function read( $sql, $values ) {
    try {
      $statement = $this->pdo->prepare( $sql );
      $statement->execute( $values );
      $results = $statement->fetchAll( \PDO::FETCH_ASSOC );

    } catch (\PDOException $e) {
      throw new DBException($e->getMessage());
    }

    return $results;
  }

  public function quote( $str ) {
    return $this->pdo->quote();
  }
}
