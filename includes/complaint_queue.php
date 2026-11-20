<?php

if (!isset($user, $conn)) {
    http_response_code(404);
    exit;
}

$query = $conn->query("SELECT c.id, c.title, c.status, c.priority, c.created_at, c.next_update,
        (c.next_update < CURRENT_DATE AND c.status IN ('pending','in_progress')) AS overdue,
        cat.category_name, staff.full_name AS staff_name"
    . COMPLAINT_FROM . " ORDER BY c.created_at DESC, c.id DESC");
$complaints = $query->fetchAll();
$pageTitle = "All complaints";
require __DIR__ . "/header.php";
?>
<section class="complaints-section">
    <div class="complaints-heading"><h1><?= $pageTitle ?></h1></div>
    <?php if (!$complaints): ?>
        <p class="empty-state">No complaints have been submitted.</p>
    <?php else: ?>
        <div class="complaints-table-wrapper">
            <table class="complaints-table">
                <thead><tr>
                    <th>Complaint</th><th>Category</th><th>Priority</th><th>Status</th>
                    <th>Assigned to</th>
                    <th>Next update</th><th>Submitted</th>
                </tr></thead>
                <tbody>
                <?php foreach ($complaints as $complaint): ?>
                    <tr>
                        <td><a href="../complaints/detail.php?id=<?= (int)$complaint["id"] ?>">
                            #<?= (int)$complaint["id"] ?> <?= htmlspecialchars($complaint["title"]) ?>
                        </a></td>
                        <td><?= htmlspecialchars($complaint["category_name"]) ?></td>
                        <td><?= htmlspecialchars(ucfirst($complaint["priority"])) ?></td>
                        <td><?= COMPLAINT_STATUSES[$complaint["status"]] ?></td>
                        <td><?= htmlspecialchars($complaint["staff_name"] ?? "Unassigned") ?></td>
                        <td><?= htmlspecialchars($complaint["next_update"] ?? "Unscheduled") ?> <?= $complaint["overdue"] ? "(Overdue)" : "" ?></td>
                        <td><?= htmlspecialchars($complaint["created_at"]) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . "/footer.php"; ?>
