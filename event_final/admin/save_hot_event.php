<?php
require_once('../includes/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the hot events from POST
    $hot_events = isset($_POST['hot_events']) ? $_POST['hot_events'] : [];

    try {
        // Begin a transaction
        $pdo->beginTransaction();

        // Clear existing hot events first
        $pdo->exec("DELETE FROM hot_events");

        // Insert new hot events if any
        if (!empty($hot_events)) {
            $stmt = $pdo->prepare("INSERT INTO hot_events (event_id) VALUES (:event_id)");
            foreach ($hot_events as $event_id) {
                $stmt->execute(['event_id' => $event_id]);
            }
        }

        // Update the is_hot field in the events table
        // Set all is_hot to 0 initially
        $pdo->exec("UPDATE events SET is_hot = 0");

        // Set is_hot to 1 for the selected hot events
        if (!empty($hot_events)) {
            $placeholders = implode(',', array_fill(0, count($hot_events), '?'));
            $stmt = $pdo->prepare("UPDATE events SET is_hot = 1 WHERE id IN ($placeholders)");
            $stmt->execute($hot_events);
        }

        // Commit the transaction
        $pdo->commit();

        // Redirect to avoid form resubmission
        header("Location: index.php"); // Replace with your redirect URL
        exit();
    } catch (PDOException $e) {
        // Rollback the transaction in case of error
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}
