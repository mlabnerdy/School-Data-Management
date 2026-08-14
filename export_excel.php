<?php

require_once __DIR__ . '/config.php';

require_login();

$role = $_SESSION['role'] ?? '';

$type = $_POST['type'] ?? '';
$password = $_POST['password'] ?? '';

$isAdmin = strcasecmp($role, 'Administrator') === 0;
$isStaff = strcasecmp($role, 'Staff') === 0;
$isTeacher = strcasecmp($role, 'Teacher') === 0;


/* Check permission */

$allowed = false;

if ($type === 'students') {

    $allowed = $isTeacher || $isStaff || $isAdmin;

} elseif ($type === 'teachers') {

    $allowed = $isStaff || $isAdmin;

} elseif ($type === 'staff') {

    $allowed = $isAdmin;

}


if (!$allowed) {

    http_response_code(403);

    exit('You are not authorized to export this data.');

}


/* Check password */

if ($password === '') {

    exit('Password is required.');

}


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

    exit('Incorrect password. Export cancelled.');

}


/* Select table */

switch ($type) {

    case 'students':

        $table = 'students';
        $filename = 'students_export_' . date('Y-m-d_H-i-s') . '.csv';

        break;


    case 'teachers':

        $table = 'teachers';
        $filename = 'teachers_export_' . date('Y-m-d_H-i-s') . '.csv';

        break;


    case 'staff':

        $table = 'staff';
        $filename = 'staff_export_' . date('Y-m-d_H-i-s') . '.csv';

        break;


    default:

        exit('Invalid export type.');

}


/* Get columns */

$stmt = $pdo->query("
    SHOW COLUMNS FROM `$table`
");

$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);


if (!$columns) {

    exit('No columns found.');

}


/* Get data */

$stmt = $pdo->query("
    SELECT *
    FROM `$table`
    ORDER BY id ASC
");

$records = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* Download CSV */

header('Content-Type: text/csv; charset=utf-8');

header(
    'Content-Disposition: attachment; filename="' .
    $filename .
    '"'
);

header('Cache-Control: no-cache, must-revalidate');


$output = fopen('php://output', 'w');


/* UTF-8 BOM for Excel */

fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));


/* Header */

$headers = [];

foreach ($columns as $column) {

    $headers[] = ucwords(
        str_replace(
            '_',
            ' ',
            $column['Field']
        )
    );

}

fputcsv($output, $headers);


/* Data */

foreach ($records as $record) {

    $row = [];

    foreach ($columns as $column) {

        $field = $column['Field'];

        $value = $record[$field] ?? '';

        if ($value === null || trim((string)$value) === '') {

            $value = 'NA';

        }

        $row[] = $value;

    }

    fputcsv($output, $row);

}


fclose($output);

exit;