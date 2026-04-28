<?php
// Admin candidates - manage candidates per election
session_start();
require '../config/db.php';
require '../includes/auth_guard.php';
require_admin();

$election_id = intval($_GET['election_id'] ?? 0);
$type        = $_GET['type'] ?? 'results';

if ($type === 'results') {
    // Export vote results
    $stmt = $pdo->prepare("
        SELECT c.name AS candidate_name,
               COUNT(v.id) AS total_votes,
               ROUND(COUNT(v.id) * 100.0 /
               NULLIF((SELECT COUNT(*) FROM votes
                       WHERE election_id = ?), 0), 2)
               AS percentage
        FROM candidates c
        LEFT JOIN votes v ON c.id = v.candidate_id
        WHERE c.election_id = ?
        GROUP BY c.id, c.name
        ORDER BY total_votes DESC
    ");
    $stmt->execute([$election_id, $election_id]);
    $rows = $stmt->fetchAll();

    // Get election title
    $estmt = $pdo->prepare("SELECT title FROM elections WHERE id = ?");
    $estmt->execute([$election_id]);
    $election = $estmt->fetch();

    // Set headers for CSV download
    $filename = 'results_' . str_replace(' ', '_', $election['title'])
                . '_' . date('Y-m-d') . '.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // CSV Headers
    fputcsv($output, [
        'Election: ' . $election['title'],
        'Exported: ' . date('M d Y h:i A')
    ]);
    fputcsv($output, []);
    fputcsv($output, ['Candidate Name', 'Total Votes', 'Percentage %']);

    foreach ($rows as $row) {
        fputcsv($output, [
            $row['candidate_name'],
            $row['total_votes'],
            $row['percentage'] . '%'
        ]);
    }

    // Add total row
    $total = array_sum(array_column($rows, 'total_votes'));
    fputcsv($output, []);
    fputcsv($output, ['TOTAL VOTES', $total, '100%']);

    fclose($output);
    exit;

} elseif ($type === 'voters') {
    // Export voter list with IP
    $stmt = $pdo->prepare("
        SELECT u.name AS voter_name,
               u.email,
               c.name AS candidate_voted,
               v.voted_at,
               v.ip_address
        FROM votes v
        JOIN users u ON v.user_id = u.id
        JOIN candidates c ON v.candidate_id = c.id
        WHERE v.election_id = ?
        ORDER BY v.voted_at ASC
    ");
    $stmt->execute([$election_id]);
    $rows = $stmt->fetchAll();

    $estmt = $pdo->prepare("SELECT title FROM elections WHERE id = ?");
    $estmt->execute([$election_id]);
    $election = $estmt->fetch();

    $filename = 'voters_' . str_replace(' ', '_', $election['title'])
                . '_' . date('Y-m-d') . '.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    fputcsv($output, [
        'Election: ' . $election['title'],
        'Exported: ' . date('M d Y h:i A')
    ]);
    fputcsv($output, []);
    fputcsv($output, [
        'Voter Name', 'Email',
        'Candidate Voted', 'Voted At', 'IP Address'
    ]);

    foreach ($rows as $row) {
        fputcsv($output, [
            $row['voter_name'],
            $row['email'],
            $row['candidate_voted'],
            date('M d Y h:i A', strtotime($row['voted_at'])),
            $row['ip_address'] ?? 'Unknown'
        ]);
    }

    fclose($output);
    exit;

} elseif ($type === 'all_votes') {
    // Export all votes across all elections
    $rows = $pdo->query("
        SELECT e.title AS election,
               u.name AS voter,
               u.email,
               c.name AS candidate,
               v.voted_at,
               v.ip_address
        FROM votes v
        JOIN users u ON v.user_id = u.id
        JOIN elections e ON v.election_id = e.id
        JOIN candidates c ON v.candidate_id = c.id
        ORDER BY v.voted_at DESC
    ")->fetchAll();

    $filename = 'all_votes_' . date('Y-m-d') . '.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['All Votes Export - ' . date('M d Y h:i A')]);
    fputcsv($output, []);
    fputcsv($output, [
        'Election', 'Voter Name', 'Email',
        'Candidate', 'Voted At', 'IP Address'
    ]);

    foreach ($rows as $row) {
        fputcsv($output, [
            $row['election'],
            $row['voter'],
            $row['email'],
            $row['candidate'],
            date('M d Y h:i A', strtotime($row['voted_at'])),
            $row['ip_address'] ?? 'Unknown'
        ]);
    }

    fclose($output);
    exit;
}

header("Location: dashboard.php");
exit;