<?php

function assignComplaint(PDO $conn, array $complaint, array $admin, $staffId): string
{
    $query = $conn->prepare("SELECT full_name FROM users WHERE id = ? AND role = 'staff' FOR UPDATE");
    $query->execute([$staffId]);
    $name = $query->fetchColumn();
    if ($name === false) {
        throw new DomainException("Select a staff member from the list.");
    }
    if ((int)$complaint["staff_id"] === $staffId) {
        throw new DomainException("This complaint is already assigned to that staff member.");
    }
    $insert = $conn->prepare("INSERT INTO complaint_assignments (complaint_id, staff_id, assigned_by)
        VALUES (?, ?, ?)");
    $insert->execute([$complaint["id"], $staffId, $admin["id"]]);
    return "Assigned to " . $name . ".";
}

function saveComplaintAssignment(PDO $conn, int $id, array $admin, array $form): void
{
    $conn->beginTransaction();
    try {
        // Both assignment and response writers lock the same complaint before reading its latest state.
        $lock = $conn->prepare("SELECT id FROM complaints WHERE id = ? FOR UPDATE");
        $lock->execute([$id]);
        $complaint = findComplaint($conn, $id);
        if (!$complaint) {
            throw new OutOfBoundsException();
        }
        if (($form["version"] ?? null) !== (string)$complaint["version"]) {
            throw new DomainException("This complaint has changed. Review its latest history before saving.");
        }

        $staffId = filter_var($form["staff_id"] ?? null, FILTER_VALIDATE_INT);
        $message = assignComplaint($conn, $complaint, $admin, $staffId);
        $status = $complaint["status"];

        $history = $conn->prepare("INSERT INTO complaint_updates
            (complaint_id, updated_by, message, old_status, new_status) VALUES (?, ?, ?, ?, ?)");
        $history->execute([$id, $admin["id"], $message, $status, $status]);
        $conn->commit();
    } catch (Throwable $error) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        throw $error;
    }
}
