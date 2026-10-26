<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/functions.php';

require_post();
verify_csrf();
logout();
redirect('/admin/login.php');
