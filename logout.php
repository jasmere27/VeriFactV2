<?php
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Redirect back to index with login form visible
header("Location: index.php?logout=1");
exit;
