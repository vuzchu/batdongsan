<?php
require_once __DIR__ . '/config/config.php';
logoutUser();
redirect(BASE_URL . '/index.php');
