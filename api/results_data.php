<?php
require '../config/db.php';
header('Content-Type: application/json');

$election_id = intval($_GET['id'] ?? 0);
if (!$election_id) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT c.name, COUNT(v.id) AS votes
    FROM candidates c
    LEFT JOIN votes v ON c.id = v.candidate_id
                     AND v.election_id = ?
    WHERE c.election_id = ?
    GROUP BY c.id, c.name
    ORDER BY votes DESC
");
$stmt->execute([$election_id, $election_id]);

echo json_encode($stmt->fetchAll());