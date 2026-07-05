<?php
session_start();
require_once __DIR__ . '/includes/storage.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_name       = trim($_POST['client_name']       ?? '');
    $phone             = trim($_POST['phone']             ?? '');
    $email             = trim($_POST['email']             ?? '');
    $service_category  = trim($_POST['service_category']  ?? '');
    $therapist         = trim($_POST['therapist']         ?? '');
    $membership_status = trim($_POST['membership_status'] ?? '');
    $duration          = trim($_POST['duration']          ?? '');
    $appointment_date  = trim($_POST['appointment_date']  ?? '');
    $appointment_time  = trim($_POST['appointment_time']  ?? '');
    $special_requests  = trim($_POST['special_requests']  ?? '');

    // Basic validation — required fields
    if (
        empty($client_name) ||
        empty($phone) ||
        empty($email) ||
        empty($service_category) ||
        empty($therapist) ||
        empty($membership_status) ||
        empty($duration) ||
        empty($appointment_date) ||
        empty($appointment_time)
    ) {
        $_SESSION['error'] = true;
        header('Location: index.php');
        exit;
    }

    $appointment = [
        'client_name'       => htmlspecialchars($client_name),
        'phone'             => htmlspecialchars($phone),
        'email'             => htmlspecialchars($email),
        'service_category'  => htmlspecialchars($service_category),
        'therapist'         => htmlspecialchars($therapist),
        'membership_status' => htmlspecialchars($membership_status),
        'duration'          => htmlspecialchars($duration),
        'appointment_date'  => htmlspecialchars($appointment_date),
        'appointment_time'  => htmlspecialchars($appointment_time),
        'special_requests'  => htmlspecialchars($special_requests),
        'internal_notes'    => '',
    ];

    add_appointment($appointment);
    $_SESSION['success'] = true;
    header('Location: index.php');
    exit;
}

// Redirect if accessed directly without POST
header('Location: index.php');
exit;
?>
