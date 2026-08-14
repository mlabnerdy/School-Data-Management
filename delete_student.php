<?php

require_once __DIR__ . '/config.php';

require_login();

// Get ID
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    redirect('students.php');
}

// Get student
$stmt = $pdo->prepare("
    SELECT photo
    FROM students
    WHERE id = ?
");

$stmt->execute([$id]);

$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    redirect('students.php');
}

// Delete photo
if (!empty($student['photo'])) {
    delete_upload($student['photo']);
}

// Get documents
$stmt = $pdo->prepare("
    SELECT file_path
    FROM documents
    WHERE owner_type = ?
    AND owner_id = ?
");

$stmt->execute(['student', $id]);

$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Delete files
foreach ($documents as $document) {
    if (!empty($document['file_path'])) {
        delete_upload($document['file_path']);
    }
}

// Delete document records
$stmt = $pdo->prepare("
    DELETE FROM documents
    WHERE owner_type = ?
    AND owner_id = ?
");

$stmt->execute(['student', $id]);

// Delete student
$stmt = $pdo->prepare("
    DELETE FROM students
    WHERE id = ?
");

$stmt->execute([$id]);

redirect('students.php');