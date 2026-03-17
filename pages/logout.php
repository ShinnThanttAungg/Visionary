<?php
session_start();
session_unset();
session_destroy();

header("Location: /visionary/index.php");
exit;