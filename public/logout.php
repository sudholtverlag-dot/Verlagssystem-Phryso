<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

$_SESSION = [];
session_destroy();
redirect('/index.php');
