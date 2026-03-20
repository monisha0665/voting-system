<?php
// Auto update election status based on current date and time
function update_election_statuses($pdo) {
    $now = date('Y-m-d H:i:s');
    $pdo->prepare("
        UPDATE elections
        SET status = 'active'
        WHERE status = 'upcoming'
        AND start_date <= ?
        AND end_date >= ?
    ")->execute([$now, $now]);

    $pdo->prepare("
        UPDATE elections
        SET status = 'closed'
        WHERE status = 'active'
        AND end_date < ?
    ")->execute([$now]);
}