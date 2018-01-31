<?php

namespace Data;

interface DatabaseWrapperInterface {
  public function read($sql, array $bindings = []);
  public function write($sql, array $bindings = []);
}
