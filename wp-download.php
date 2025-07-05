<?php
require_once('../wp_local/wp-config.php');

/**
 * Establishes a connection to the WordPress database.
 *
 * @return mysqli MySQLi connection object.
 */
function connect_to_database() {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if ($mysqli->connect_error) {
        die("❌ Failed to connect to the database: " . $mysqli->connect_error . "\n");
    }
    return $mysqli;
}

/**
 * Fetches user and meta information from the WordPress database.
 *
 * @param mysqli $mysqli MySQLi connection.
 * @param string $username The username (login) to search for.
 * @return void
 */
function fetch_user_data($mysqli, $username) {
    $user_result = $mysqli->query("SELECT ID, user_login, user_email, user_nicename FROM wp_users WHERE user_login = '{$mysqli->real_escape_string($username)}'");
    if (!$user_result || $user_result->num_rows === 0) {
        echo "⚠️ User '{$username}' not found.\n";
        return;
    }
    $user = $user_result->fetch_assoc();

    $meta_result = $mysqli->query("SELECT meta_key, meta_value FROM wp_usermeta WHERE user_id = {$user['ID']}");
    if (!$meta_result) {
        echo "⚠️ Failed to retrieve metadata for user ID {$user['ID']}.\n";
        return;
    }

    $D = [
        "u" => $user['user_login'],
        "n" => $user['user_nicename'],
        "e" => $user['user_email']
    ];

    $A = [];
    $P = [];

    // Convert metadata to associative array
    $temp = [];
    while ($row = $meta_result->fetch_assoc()) {
        $temp[$row['meta_key']] = $row['meta_value'];
    }

    $D["f"] = $temp['first_name'] ?? "";
    $D["l"] = $temp['last_name'] ?? "";

    if (isset($temp['billing_phone'])) {
        $P[] = ["pj" => $temp['billing_phone']];
    }
    if (isset($temp['shipping_phone'])) {
        $P[] = ["pj" => $temp['shipping_phone']];
    }

    $A[] = [
        "fi" => $temp['billing_first_name'] ?? '',
        "li" => $temp['billing_last_name'] ?? '',
        "ci" => $temp['billing_company'] ?? '',
        "a1i" => $temp['billing_address_1'] ?? '',
        "a2i" => $temp['billing_address_2'] ?? '',
        "ti" => 'billing'
    ];

    $A[] = [
        "fi" => $temp['shipping_first_name'] ?? '',
        "li" => $temp['shipping_last_name'] ?? '',
        "ci" => $temp['shipping_company'] ?? '',
        "a1i" => $temp['shipping_address_1'] ?? '',
        "a2i" => $temp['shipping_address_2'] ?? '',
        "ti" => 'shipping'
    ];

    $U = ["D" => $D, "A" => $A, "P" => $P];

    // Save the structured user data to a JSON file
    file_put_contents("user_data.json", json_encode($U, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "✅ User data successfully saved to 'user_data.json'.\n";
}

// --- ENTRY POINT ---

$mysqli = connect_to_database();

echo "Enter the WordPress username: ";
$username = trim(fgets(STDIN));

fetch_user_data($mysqli, $username);

$mysqli->close();
