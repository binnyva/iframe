<?php
include('../common.php');

$tables = $sql->getCol("SHOW TABLES");
render();
