<?php
// filepath: backend/config/eloquent-bootstrap.php

require_once __DIR__ . '/../../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

$config = require __DIR__ . '/eloquent.php';

$capsule = new Capsule;

$capsule->addConnection($config);

$capsule->setAsGlobal();

$capsule->bootEloquent();