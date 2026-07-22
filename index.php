<?php
session_start();
$success = $_SESSION['success'] ?? false;
$error = $_SESSION['error'] ?? false;
unset($_SESSION['success'], $_SESSION['error']);

// Generate time slots 9:00 AM to 9:00 PM (30-min intervals)
$time_slots = [];
$start_time = strtotime('09:00');
$end_time = strtotime('21:00');
while ($start_time <= $end_time) {
    $time_slots[] = date('h:i A', $start_time);
    $start_time = strtotime('+30 minutes', $start_time);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#6D3B47">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Reserve Your Experience | GlowSpa CRM</title>
    <meta name="description"
        content="Book your luxury spa session at GlowSpa — facials, massages, nail care, body wraps and more. Reserve your experience online in seconds.">
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

    <!-- Hero -->
    <div class="booking-hero">
        <span class="hero-symbol">✿</span>
        <h1>Reserve Your Experience</h1>
        <p>GlowSpa — Where Wellness Meets Luxury</p>
    </div>

    <!-- Booking Form -->
    <div class="booking-section">
        <div class="card">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    ✓ Your session has been reserved! We will confirm your booking shortly.
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error">
                    ✕ Please fill in all required fields before submitting.
                </div>
            <?php endif; ?>

            <form action="book.php" method="POST">

                <!-- Client Info -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="client_name">Full Name *</label>
                        <input type="text" id="client_name" name="client_name" class="form-control"
                            placeholder="Your full name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number *</label>
                        <input type="text" id="phone" name="phone" class="form-control" placeholder="+971 50 000 0000"
                            required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label" for="email">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="your@email.com"
                            required>
                    </div>
                </div>

                <!-- Service -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="service_category">Service Category *</label>
                        <select id="service_category" name="service_category" class="form-control" required>
                            <option value="">Select a Service</option>
                            <option value="Facial">Facial</option>
                            <option value="Massage">Massage</option>
                            <option value="Nail Care">Nail Care</option>
                            <option value="Waxing">Waxing</option>
                            <option value="Body Wrap">Body Wrap</option>
                            <option value="Hair Treatment">Hair Treatment</option>
                            <option value="Couple Spa">Couple Spa</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="therapist">Therapist Preference *</label>
                        <select id="therapist" name="therapist" class="form-control" required>
                            <option value="">Select Therapist</option>
                            <option value="Therapist 1">Therapist 1</option>
                            <option value="Therapist 2">Therapist 2</option>
                            <option value="Therapist 3">Therapist 3</option>
                            <option value="No Preference">No Preference</option>
                        </select>
                    </div>
                </div>

                <!-- Membership & Duration -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Membership Status *</label>
                        <div class="radio-group">
                            <div class="radio-option">
                                <input type="radio" id="ms_walkin" name="membership_status" value="Walk-in" checked>
                                <label for="ms_walkin">Walk-in</label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" id="ms_regular" name="membership_status" value="Regular">
                                <label for="ms_regular">Regular</label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" id="ms_vip" name="membership_status" value="VIP">
                                <label for="ms_vip">⭐ VIP</label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" id="ms_corporate" name="membership_status" value="Corporate">
                                <label for="ms_corporate">Corporate</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="duration">Session Duration *</label>
                        <select id="duration" name="duration" class="form-control" required>
                            <option value="">Select Duration</option>
                            <option value="30 min">30 min</option>
                            <option value="60 min">60 min</option>
                            <option value="90 min">90 min</option>
                            <option value="120 min">120 min</option>
                        </select>
                    </div>
                </div>

                <!-- Date & Time -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="appointment_date">Preferred Date *</label>
                        <input type="date" id="appointment_date" name="appointment_date" class="form-control" required
                            min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="appointment_time">Preferred Time *</label>
                        <select id="appointment_time" name="appointment_time" class="form-control" required>
                            <option value="">Select Time</option>
                            <?php foreach ($time_slots as $time): ?>
                                <option value="<?php echo htmlspecialchars($time); ?>">
                                    <?php echo htmlspecialchars($time); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Special Requests -->
                <div class="form-group">
                    <label class="form-label" for="special_requests">Special Requests (Optional)</label>
                    <textarea id="special_requests" name="special_requests" class="form-control"
                        placeholder="Allergies, sensitivities, preferred pressure, aromatherapy preferences…"></textarea>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" id="submit-booking" class="btn btn-primary"
                        style="width: 100%; padding: 1rem; font-size: 1rem; letter-spacing: 0.12em;">
                        ✿ &nbsp;Book My Session
                    </button>
                </div>

            </form>
        </div>

    </div>

    <style>
        /* Compact layout adjustments to fit viewport without vertical scroll */
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .booking-hero {
            padding: 1.5rem 1rem 1rem !important;
        }
        .booking-hero .hero-symbol {
            font-size: 1.8rem !important;
            margin-bottom: 0.25rem !important;
        }
        .booking-hero h1 {
            font-size: 1.8rem !important;
            margin-bottom: 0.25rem !important;
        }
        .booking-hero p {
            font-size: 0.85rem !important;
        }
        .booking-section {
            padding: 1rem 1.5rem !important;
            max-width: 720px !important;
        }
        .booking-section .card {
            padding: 1.25rem 1.5rem !important;
            margin-bottom: 0 !important;
        }
        .form-group {
            margin-bottom: 0.75rem !important;
        }
        .form-row {
            gap: 0.85rem !important;
        }
        .form-control {
            padding: 0.5rem 0.85rem !important;
            font-size: 0.88rem !important;
        }
        .form-label {
            font-size: 0.72rem !important;
            margin-bottom: 0.25rem !important;
        }
        .radio-option label {
            padding: 0.35rem 0.85rem !important;
            font-size: 0.78rem !important;
        }
        textarea.form-control {
            min-height: 50px !important;
            height: 50px !important;
        }
        .alert {
            padding: 0.6rem 1rem !important;
            margin-bottom: 0.85rem !important;
            font-size: 0.82rem !important;
        }
        #submit-booking {
            padding: 0.65rem !important;
            font-size: 0.88rem !important;
        }
        #submit-booking:hover {
            background: var(--rose-gold) !important;
        }
    </style>

</body>

</html>