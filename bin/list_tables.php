<?php
$db = new PDO('sqlite:../app.sqlite');
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
echo "Tables dans la base:\n";
foreach ($tables as $table) {
    echo "- $table\n";
}

if (in_array('apartments', $tables)) {
    echo "\nStructure de apartments:\n";
    $result = $db->query("PRAGMA table_info(apartments)");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("  %s: %s %s\n", $row['name'], $row['type'], $row['notnull'] ? 'NOT NULL' : '');
    }
}
