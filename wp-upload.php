<?php
require_once('../wp_local/wp-config.php');

/**
 * Establish a connection to the WordPress MySQL database.
 *
 * @return mysqli The database connection.
 */
function connect_to_database()
{
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if ($mysqli->connect_error) {
        die("❌ Database connection failed: " . $mysqli->connect_error . "\n");
    }
    return $mysqli;
}

/**
 * Load and decode JSON user data from file.
 *
 * @param string $file_path Path to JSON file.
 * @return array|null Parsed user data.
 */
function get_data_from_json($file_path)
{
    return json_decode(file_get_contents($file_path), true);
}

/**
 * Validate the structure of the user data.
 *
 * @param array $U Parsed user data.
 * @return bool True if valid.
 */
function validate_data($U)
{
    return isset($U['D']['u'], $U['D']['n'], $U['D']['e'], $U['D']['f'], $U['D']['l']) &&
        is_array($U['A']) && count($U['A']) >= 1 &&
        is_array($U['P']) && count($U['P']) >= 1;
}

/**
 * Convert raw JSON structure into WordPress-compatible format.
 *
 * @param array $U Parsed user data.
 * @return array WP-compatible structure.
 */
function transform_to_wp_structure($U)
{
    $D = $U['D'];
    $billing = $U['A'][0];
    $shipping = $U['A'][1];

    return [
        'user_data' => [
            'user_login' => $D['u'],
            'user_email' => $D['e'],
            'user_nicename' => $D['n'],
            'user_registered' => date("Y-m-d H:i:s"),
            'display_name' => $D['f'] . ' ' . $D['l']
        ],
        'user_meta' => [
            'first_name' => $D['f'],
            'last_name' => $D['l'],
            'billing_phone' => $U['P'][0]['pj'],
            'billing_first_name' => $billing['fi'],
            'billing_last_name' => $billing['li'],
            'billing_company' => $billing['ci'],
            'billing_address_1' => $billing['a1i'],
            'billing_address_2' => $billing['a2i'],
            'billing_country' => 'BR',
            'billing_postcode' => '00000-000',
            'billing_state' => 'RJ',
            'billing_city' => 'Rio de Janeiro',
            'billing_email' => $D['e'],
            'shipping_first_name' => $shipping['fi'],
            'shipping_last_name' => $shipping['li'],
            'shipping_company' => $shipping['ci'],
            'shipping_address_1' => $shipping['a1i'],
            'shipping_address_2' => $shipping['a2i'],
            'shipping_country' => 'BR',
            'shipping_postcode' => '00000-000',
            'shipping_state' => 'RJ',
            'shipping_city' => 'Rio de Janeiro',
            'shipping_phone' => $U['P'][1]['pj'] ?? $U['P'][0]['pj']
        ]
    ];
}

/**
 * Inserts user data into WordPress users, usermeta, and WooCommerce lookup.
 *
 * @param mysqli $mysqli MySQL connection.
 * @param array $data Structured WP user data.
 */
function insert_data_into_wp($mysqli, $data)
{
    $user = $data['user_data'];

    $stmt = $mysqli->prepare("INSERT INTO wp_users (user_login, user_email, user_nicename, user_registered, display_name) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sssss', $user['user_login'], $user['user_email'], $user['user_nicename'], $user['user_registered'], $user['display_name']);
    $stmt->execute();
    $user_id = $mysqli->insert_id;

    foreach ($data['user_meta'] as $key => $value) {
        $stmt = $mysqli->prepare("INSERT INTO wp_usermeta (user_id, meta_key, meta_value) VALUES (?, ?, ?)");
        $stmt->bind_param('iss', $user_id, $key, $value);
        $stmt->execute();
    }

    $d = $data['user_data'];
    $m = $data['user_meta'];
    $stmt_wc = $mysqli->prepare("INSERT INTO wp_wc_customer_lookup (customer_id, user_id, username, first_name, last_name, email, date_last_active, date_registered, country, postcode, city, state) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_wc->bind_param('isssssssssss', $user_id, $user_id, $d['user_login'], $m['first_name'], $m['last_name'], $d['user_email'], $d['user_registered'], $d['user_registered'], $m['billing_country'], $m['billing_postcode'], $m['billing_city'], $m['billing_state']);
    $stmt_wc->execute();

    echo "✅ User inserted into WordPress: wp_users, wp_usermeta, and wp_wc_customer_lookup.\n";
}

// --- Script Entry Point ---
$file_path = 'user_data.json';
$data = get_data_from_json($file_path);

if (!validate_data($data)) {
    die("❌ JSON structure validation failed.\n");
}
echo "✅ JSON structure validation passed.\n";

$mysqli = connect_to_database();
insert_data_into_wp($mysqli, transform_to_wp_structure($data));
$mysqli->close();
