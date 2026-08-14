<?php

require_once __DIR__ . '/config.php';

require_login();

$role = $_SESSION['role'] ?? '';

$isAdmin = strcasecmp($role, 'Administrator') === 0;

if (!$isAdmin) {

    http_response_code(403);

    exit('Administrator access required.');

}


$password = $_POST['password'] ?? '';

if ($password === '') {

    exit('Password is required.');

}


/* Verify password */

$stmt = $pdo->prepare("
    SELECT password
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([
    $_SESSION['user_id']
]);

$user = $stmt->fetch();


if (
    !$user ||
    !password_verify($password, $user['password'])
) {

    exit('Incorrect password. Backup cancelled.');

}


/* Get tables */

$tables = $pdo
    ->query("SHOW TABLES")
    ->fetchAll(PDO::FETCH_COLUMN);


/* Start SQL */

$sql = '';

$sql .= "-- School Data Management System Backup\n";

$sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

$sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";


foreach ($tables as $table) {


    /* Table structure */

    $createStmt = $pdo->query(
        "SHOW CREATE TABLE `$table`"
    );

    $create = $createStmt->fetch(PDO::FETCH_ASSOC);


    $createSql = $create['Create Table'] ?? '';


    $sql .= "-- ----------------------------------------\n";

    $sql .= "-- Table: `$table`\n";

    $sql .= "-- ----------------------------------------\n\n";


    $sql .= "DROP TABLE IF EXISTS `$table`;\n\n";

    $sql .= $createSql . ";\n\n";


    /* Table data */

    $rows = $pdo
        ->query("SELECT * FROM `$table`")
        ->fetchAll(PDO::FETCH_ASSOC);


    if (!empty($rows)) {


        foreach ($rows as $row) {

            $fields = [];

            $values = [];


            foreach ($row as $field => $value) {

                $fields[] = "`" . str_replace(
                    "`",
                    "``",
                    $field
                ) . "`";


                if ($value === null) {

                    $values[] = "NULL";

                } else {

                    $values[] = $pdo->quote($value);

                }

            }


            $sql .= "INSERT INTO `$table` (";

            $sql .= implode(', ', $fields);

            $sql .= ") VALUES (";

            $sql .= implode(', ', $values);

            $sql .= ");\n";

        }


        $sql .= "\n";

    }

}


$sql .= "SET FOREIGN_KEY_CHECKS=1;\n";


/* Download */

$filename =
    'school_data_backup_' .
    date('Y-m-d_H-i-s') .
    '.sql';


header(
    'Content-Type: application/sql'
);

header(
    'Content-Disposition: attachment; filename="' .
    $filename .
    '"'
);

header('Content-Length: ' . strlen($sql));

header('Cache-Control: no-cache, must-revalidate');

echo $sql;

exit;