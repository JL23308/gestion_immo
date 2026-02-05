<?php
$db = new PDO('sqlite:../app.sqlite');
$db->exec('DROP TABLE IF EXISTS apartments');
$db->exec('DROP TABLE IF EXISTS leases');
echo "Tables supprimées avec succès\n";
