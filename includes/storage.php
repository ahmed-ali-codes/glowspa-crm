<?php
/**
 * GlowSpa CRM — Flat-file JSON storage for bookings.
 * No database required — works on any PHP hosting.
 */

define('DATA_DIR', __DIR__ . '/../data');
define('APPOINTMENTS_FILE', DATA_DIR . '/appointments.json');
define('META_FILE', DATA_DIR . '/meta.json');

$therapists = ['Therapist 1', 'Therapist 2', 'Therapist 3', 'No Preference'];
$service_categories = ['Facial', 'Massage', 'Nail Care', 'Waxing', 'Body Wrap', 'Hair Treatment', 'Couple Spa', 'Other'];
$durations = ['30 min', '60 min', '90 min', '120 min'];
$membership_statuses = ['Walk-in', 'Regular', 'VIP', 'Corporate'];

// Auto-create data directory and protect it
if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

// Protect data directory from direct web access
$htaccess_path = DATA_DIR . '/.htaccess';
if (!file_exists($htaccess_path)) {
    file_put_contents($htaccess_path, "Deny from all\n");
}

// Initialize appointments file if it doesn't exist
if (!file_exists(APPOINTMENTS_FILE)) {
    file_put_contents(APPOINTMENTS_FILE, json_encode([], JSON_PRETTY_PRINT));
}

// Initialize meta file (stores next ID)
if (!file_exists(META_FILE)) {
    file_put_contents(META_FILE, json_encode(['next_id' => 1], JSON_PRETTY_PRINT));
}

/**
 * Get the next auto-increment ID
 */
function get_next_id() {
    $meta = json_decode(file_get_contents(META_FILE), true);
    $id = $meta['next_id'];
    $meta['next_id'] = $id + 1;
    file_put_contents(META_FILE, json_encode($meta, JSON_PRETTY_PRINT), LOCK_EX);
    return $id;
}

/**
 * Normalize a booking to ensure all GlowSpa fields are present
 */
function normalize_appointment($appt) {
    // Client info
    if (!isset($appt['client_name']))       $appt['client_name']       = 'Unknown Client';
    if (!isset($appt['phone']))             $appt['phone']             = '';
    if (!isset($appt['email']))             $appt['email']             = '';

    // GlowSpa-specific fields
    if (!isset($appt['service_category']))  $appt['service_category']  = 'Other';
    if (!isset($appt['therapist']))         $appt['therapist']         = 'No Preference';
    if (!isset($appt['membership_status'])) $appt['membership_status'] = 'Walk-in';
    if (!isset($appt['duration']))          $appt['duration']          = '60 min';
    if (!isset($appt['special_requests']))  $appt['special_requests']  = '';

    // Scheduling
    if (!isset($appt['appointment_date'])) $appt['appointment_date']   = '';
    if (!isset($appt['appointment_time'])) $appt['appointment_time']   = '';
    if (!isset($appt['status']))           $appt['status']             = 'Pending';
    if (!isset($appt['internal_notes']))   $appt['internal_notes']     = '';
    if (!isset($appt['created_at']))       $appt['created_at']         = '';

    // Strip legacy SparkClean fields if any
    unset($appt['property_address'], $appt['property_type'], $appt['rooms_count'],
          $appt['cleaning_type'], $appt['team_assigned'], $appt['access_notes']);

    return $appt;
}

/**
 * Get all bookings, optionally filtered
 */
function get_appointments(
    $filter_date = null,
    $filter_service_category = null,
    $filter_therapist = null,
    $filter_membership = null,
    $filter_status = null
) {
    $data = json_decode(file_get_contents(APPOINTMENTS_FILE), true);
    if (!is_array($data)) {
        $data = [];
    }

    $result = [];
    foreach ($data as $appt) {
        $appt = normalize_appointment($appt);
        if ($filter_date             && $appt['appointment_date']  !== $filter_date)             continue;
        if ($filter_service_category && $appt['service_category']  !== $filter_service_category) continue;
        if ($filter_therapist        && $appt['therapist']         !== $filter_therapist)        continue;
        if ($filter_membership       && $appt['membership_status'] !== $filter_membership)       continue;
        if ($filter_status           && $appt['status']            !== $filter_status)           continue;
        $result[] = $appt;
    }

    // Sort by date ASC, then time ASC
    usort($result, function ($a, $b) {
        $cmp = strcmp($a['appointment_date'], $b['appointment_date']);
        if ($cmp !== 0) return $cmp;
        return strcmp($a['appointment_time'] ?? '', $b['appointment_time'] ?? '');
    });

    return $result;
}

/**
 * Get a single booking by ID
 */
function get_appointment($id) {
    $data = json_decode(file_get_contents(APPOINTMENTS_FILE), true);
    if (!is_array($data)) return null;

    foreach ($data as $appt) {
        if ((int)$appt['id'] === (int)$id) {
            return normalize_appointment($appt);
        }
    }
    return null;
}

/**
 * Add a new booking
 */
function add_appointment($appointment) {
    $data = json_decode(file_get_contents(APPOINTMENTS_FILE), true);
    if (!is_array($data)) {
        $data = [];
    }

    $appointment['id']         = get_next_id();
    $appointment['status']     = 'Pending';
    $appointment['created_at'] = date('Y-m-d H:i:s');
    $appointment = normalize_appointment($appointment);

    $data[] = $appointment;
    file_put_contents(APPOINTMENTS_FILE, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
    return $appointment['id'];
}

/**
 * Update booking status
 */
function update_appointment_status($id, $status) {
    $allowed = ['Pending', 'Confirmed', 'In Progress', 'Completed', 'Cancelled'];
    if (!in_array($status, $allowed)) return false;

    $data = json_decode(file_get_contents(APPOINTMENTS_FILE), true);
    if (!is_array($data)) return false;

    $updated = false;
    foreach ($data as &$appt) {
        if ((int)$appt['id'] === (int)$id) {
            $appt['status'] = $status;
            $updated = true;
            break;
        }
    }

    if ($updated) {
        file_put_contents(APPOINTMENTS_FILE, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
    }
    return $updated;
}

/**
 * Update internal notes
 */
function update_appointment_notes($id, $notes) {
    $data = json_decode(file_get_contents(APPOINTMENTS_FILE), true);
    if (!is_array($data)) return false;

    $updated = false;
    foreach ($data as &$appt) {
        if ((int)$appt['id'] === (int)$id) {
            $appt['internal_notes'] = $notes;
            $updated = true;
            break;
        }
    }

    if ($updated) {
        file_put_contents(APPOINTMENTS_FILE, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
    }
    return $updated;
}

/**
 * Delete a booking by ID
 */
function delete_appointment($id) {
    $data = json_decode(file_get_contents(APPOINTMENTS_FILE), true);
    if (!is_array($data)) return false;

    $data = array_values(array_filter($data, function ($appt) use ($id) {
        return (int)$appt['id'] !== (int)$id;
    }));

    file_put_contents(APPOINTMENTS_FILE, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
    return true;
}

/**
 * Count bookings by criteria
 */
function count_appointments(
    $date = null,
    $status = null,
    $exclude_status = null,
    $is_current_month = false,
    $membership = null
) {
    $data = json_decode(file_get_contents(APPOINTMENTS_FILE), true);
    if (!is_array($data)) return 0;

    $count = 0;
    $current_month = date('Y-m');

    foreach ($data as $appt) {
        $appt = normalize_appointment($appt);
        if ($date           && $appt['appointment_date']  !== $date)           continue;
        if ($status         && $appt['status']            !== $status)         continue;
        if ($exclude_status && $appt['status']            === $exclude_status) continue;
        if ($membership     && $appt['membership_status'] !== $membership)     continue;
        if ($is_current_month) {
            if (substr($appt['appointment_date'], 0, 7) !== $current_month) continue;
        }
        $count++;
    }
    return $count;
}

/**
 * Count bookings by service category (for chart)
 */
function count_by_service_category() {
    $data = json_decode(file_get_contents(APPOINTMENTS_FILE), true);
    if (!is_array($data)) return [];

    $counts = [];
    foreach ($data as $appt) {
        $appt = normalize_appointment($appt);
        $cat = $appt['service_category'];
        $counts[$cat] = ($counts[$cat] ?? 0) + 1;
    }
    arsort($counts);
    return $counts;
}

/**
 * Count bookings per therapist for today (Therapist Schedule)
 */
function count_by_therapist_today() {
    global $therapists;
    $data = json_decode(file_get_contents(APPOINTMENTS_FILE), true);
    if (!is_array($data)) return array_fill_keys($therapists, 0);

    $today = date('Y-m-d');
    $counts = array_fill_keys($therapists, 0);

    foreach ($data as $appt) {
        $appt = normalize_appointment($appt);
        if ($appt['appointment_date'] === $today) {
            $t = $appt['therapist'];
            if (isset($counts[$t])) {
                $counts[$t]++;
            }
        }
    }
    return $counts;
}
?>
