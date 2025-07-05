<?php
require_once('../oc_local/config.php');

/**
 * Establish a connection to the OpenCart MySQL database.
 *
 * @return mysqli The database connection.
 */
function connect_to_database()
{
    $mysqli = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    if ($mysqli->connect_error) {
        die("❌ Database connection failed: " . $mysqli->connect_error . "\n");
    }
    return $mysqli;
}

/**
 * Reads and decodes a JSON file into an associative array.
 *
 * @param string $file_path Path to the JSON file.
 * @return array|null Parsed data.
 */
function get_data_from_json($file_path)
{
    return json_decode(file_get_contents($file_path), true);
}

/**
 * Validates the structure of the user data array.
 *
 * @param array $U Parsed user data.
 * @return bool True if valid, false otherwise.
 */
function validate_data($U)
{
    return isset($U['D']['u'], $U['D']['n'], $U['D']['e'], $U['D']['f'], $U['D']['l']) &&
        is_array($U['A']) && count($U['A']) >= 2 &&
        is_array($U['P']) && count($U['P']) >= 1;
}

/**
 * Converts the original JSON data into OpenCart-compatible structure.
 *
 * @param array $U Parsed user data.
 * @return array Structured data.
 */
function transform_to_oc_structure($U)
{
    $D = $U['D'];
    $billing = $U['A'][0];
    $shipping = $U['A'][1];

    return [
        'general_info' => [
            'email' => $D['e'],
            'firstname' => $D['f'],
            'lastname' => $D['l'],
            'telephone' => $U['P'][0]['pj'],
            'password' => 'randompassword', // Replace with a secure hash in production
            'status' => 1,
            'customer_group_id' => 1
        ],
        'billing' => [
            'firstname' => $billing['fi'],
            'lastname' => $billing['li'],
            'company' => $billing['ci'],
            'address_1' => $billing['a1i'],
            'address_2' => $billing['a2i'],
            'city' => 'Rio de Janeiro',
            'postcode' => '00000-000',
            'country_id' => '30', // Brazil
            'zone_id' => '485'    // RJ
        ],
        'shipping' => [
            'firstname' => $shipping['fi'],
            'lastname' => $shipping['li'],
            'company' => $shipping['ci'],
            'address_1' => $shipping['a1i'],
            'address_2' => $shipping['a2i'],
            'city' => 'Rio de Janeiro',
            'postcode' => '00000-000',
            'country_id' => '30',
            'zone_id' => '485'
        ]
    ];
}

/**
 * Inserts customer and address data into OpenCart tables.
 *
 * @param mysqli $mysqli MySQL connection.
 * @param array $data OpenCart-structured data.
 */
function insert_data_into_oc($mysqli, $data)
{
    $c = $data['general_info'];

    $stmt = $mysqli->prepare("INSERT INTO " . DB_PREFIX . "customer (email, firstname, lastname, telephone, password, status, customer_group_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssssis', $c['email'], $c['firstname'], $c['lastname'], $c['telephone'], $c['password'], $c['status'], $c['customer_group_id']);
    $stmt->execute();
    $customer_id = $mysqli->insert_id;

    $addresses = [$data['billing'], $data['shipping']];
    foreach ($addresses as $a) {
        $stmt = $mysqli->prepare("INSERT INTO " . DB_PREFIX . "address (customer_id, firstname, lastname, company, address_1, address_2, city, postcode, country_id, zone_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('isssssssss', $customer_id, $a['firstname'], $a['lastname'], $a['company'], $a['address_1'], $a['address_2'], $a['city'], $a['postcode'], $a['country_id'], $a['zone_id']);
        $stmt->execute();
    }

    echo "✅ User inserted into OpenCart (customer + address).\n";
}

// --- Script Entry Point ---
$file_path = 'user_data.json';
$data = get_data_from_json($file_path);

if (!validate_data($data)) {
    die("❌ JSON structure validation failed.\n");
}
echo "✅ JSON structure validation passed.\n";

$mysqli = connect_to_database();
insert_data_into_oc($mysqli, transform_to_oc_structure($data));
$mysqli->close();
