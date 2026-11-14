<?php
require 'includes/config.php';
redirect(is_logged_in() ? 'dashboard.php' : 'login.php');
