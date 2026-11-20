<?php

require_once __DIR__ . "/../includes/complaints.php";
require_once __DIR__ . "/../includes/complaint_changes.php";
$user = requireAdmin($conn);
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    complaintNotFound();
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? null;
    if (!is_string($token) || empty($_SESSION["csrf_token"])
        || !hash_equals($_SESSION["csrf_token"], $token)) {
        http_response_code(403);
        exit("Unable to accept this submission.");
    }
    try {
        saveComplaintAssignment($conn, $id, $user, $_POST);
        $_SESSION["saved_complaint"] = $id;
        header("Location: detail.php?id=" . $id, true, 303);
        exit;
    } catch (OutOfBoundsException $exception) {
        complaintNotFound();
    } catch (DomainException $exception) {
        $error = $exception->getMessage();
    } catch (PDOException $exception) {
        error_log($exception->getMessage());
        $error = "The change could not be saved. Please try again.";
    }
}

$complaint = findComplaint($conn, $id);
if (!$complaint) {
    complaintNotFound();
}
$historyQuery = $conn->prepare("SELECT u.created_at, u.message, u.old_status, u.new_status, author.full_name
    FROM complaint_updates u JOIN users author ON author.id = u.updated_by
    WHERE u.complaint_id = ? ORDER BY u.created_at, u.id");
$historyQuery->execute([$id]);
$history = $historyQuery->fetchAll();
$filesQuery = $conn->prepare("SELECT id, file_name FROM complaint_attachments WHERE complaint_id = ? ORDER BY id");
$filesQuery->execute([$id]);
$files = $filesQuery->fetchAll();
$services = ["Academic" => "Academic Affairs", "Facilities" => "Facilities", "Accommodation" => "Housing",
    "IT Services" => "IT Services", "Administration" => "Administration"];
$suggestion = $services[$complaint["category_name"]] ?? "Student Services";
$staff = $conn->query("SELECT id, full_name, email, department FROM users
    WHERE role = 'staff' ORDER BY full_name, email")->fetchAll();
$saved = ($_SESSION["saved_complaint"] ?? null) === $id;
unset($_SESSION["saved_complaint"]);
$_SESSION["csrf_token"] ??= bin2hex(random_bytes(32));
$pageTitle = "Complaint #" . $id;
require __DIR__ . "/../includes/header.php";
?>
<article class="complaint-detail">
    <a href="../admin/dashboard.php">&larr; Back to complaints</a>
    <header>
        <p>Complaint #<?= $id ?></p>
        <h1><?= htmlspecialchars($complaint["title"]) ?></h1>
        <span class="status-badge status-<?= htmlspecialchars($complaint["status"]) ?>"><?= COMPLAINT_STATUSES[$complaint["status"]] ?></span>
    </header>
    <?php if ($saved): ?><p class="message success-message" role="status">Change saved.</p><?php endif; ?>
    <?php if ($error): ?><p class="message error-message" role="alert"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <section class="info-card">
        <h2>Complaint details</h2>
        <p><?= nl2br(htmlspecialchars($complaint["description"])) ?></p>
        <p><strong>Submitted by</strong> <?= htmlspecialchars($complaint["student_name"]) ?></p>
        <p><strong>Category</strong> <?= htmlspecialchars($complaint["category_name"]) ?></p>
        <p><strong>Priority</strong> <?= htmlspecialchars(ucfirst($complaint["priority"])) ?></p>
        <p><strong>Assigned to</strong> <?= htmlspecialchars($complaint["staff_name"] ?? "Unassigned") ?></p>
        <p><strong>Next update</strong> <?= htmlspecialchars($complaint["next_update"] ?? "No date scheduled") ?></p>
        <?php if ($files): ?>
            <h3>Supporting files</h3>
            <ul>
            <?php foreach ($files as $file): ?>
                <li><a href="attachment.php?id=<?= $id ?>&amp;file=<?= (int)$file["id"] ?>"><?= htmlspecialchars($file["file_name"]) ?></a></li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="info-card">
        <h2>Complaint history</h2>
        <ol class="complaint-history">
            <li><strong>Complaint submitted</strong><br><?= htmlspecialchars($complaint["created_at"]) ?></li>
            <?php foreach ($history as $entry): ?>
                <li>
                    <strong><?= htmlspecialchars($entry["full_name"]) ?></strong> &middot; <?= htmlspecialchars($entry["created_at"]) ?>
                    <?php if ($entry["new_status"] && $entry["new_status"] !== $entry["old_status"]): ?>
                        <div>Status changed to <?= htmlspecialchars(COMPLAINT_STATUSES[$entry["new_status"]] ?? $entry["new_status"]) ?></div>
                    <?php endif; ?>
                    <div><?= nl2br(htmlspecialchars($entry["message"])) ?></div>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>

    <section class="info-card">
        <h2>Assign complaint</h2>
        <p>Suggested service: <?= htmlspecialchars($suggestion) ?></p>
        <?php if (!$staff): ?>
            <p>A staff account is needed before you can assign this complaint.</p>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">
                <input type="hidden" name="version" value="<?= (int)$complaint["version"] ?>">
                <div class="form-group">
                    <label for="staff_id">Staff member</label>
                    <select name="staff_id" id="staff_id" required>
                        <option value="">Select staff member</option>
                        <?php foreach ($staff as $member): ?>
                            <option value="<?= (int)$member["id"] ?>" <?= $member["id"] == $complaint["staff_id"] ? "selected" : "" ?>>
                                <?= htmlspecialchars($member["full_name"] . " (" . ($member["department"] ?: $member["email"]) . ")") ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn btn-primary" type="submit">Save assignment</button>
            </form>
        <?php endif; ?>
    </section>
</article>
<?php require __DIR__ . "/../includes/footer.php"; ?>
