<?php // cone example
$db = new PDO('pgsql:host=localhost;port=5432;dbname='.preg_replace("/[^a-z]/","",$sEmpre), 'myuser', 'mypassword');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
