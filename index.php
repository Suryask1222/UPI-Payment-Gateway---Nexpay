<?php

require_once __DIR__ . '/config/bootstrap.php';

$router = require_once __DIR__ . '/routes/web.php';

$router->dispatch();