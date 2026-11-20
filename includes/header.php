<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = $pageTitle ?? "University Complaint Management System";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($pageTitle) ?>
    </title>

    <link rel="stylesheet"
          href="/University_Complaint_Management_System/assets/css/style.css">
</head>

<body>

<?php include __DIR__ . "/navbar.php"; ?>

<main class="container">
