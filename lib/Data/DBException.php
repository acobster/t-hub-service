<?php

namespace Data;

class DBException extends \Exception {
  public static function fromPDO( \PDO $pdo ) {
    $info = $pdo->errorInfo();
    $message = sprintf(
      "Database error: %s (%s:%d)", $info[2], $info[0], $info[1]
    );
    return new DBException( $message );
  }
}

?>