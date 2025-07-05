<?php
require_once('../oc_local/config.php');

/**
 * Connects to the OpenCart database.
 *
 * @return mysqli MySQLi connection object.
 */
function connect_to_database()
{
    $mysqli = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

    if ($mysqli->connect_error) {
        die("❌ Failed to connect to the database: " . $mysqli->connect_error . "\n");
    }

    return $mysqli;
}

/**
 * Fetches customer basic data by email.
 *
 * @param mysqli $mysqli MySQLi connection object.
 * @param string $email Customer's email address.
 * @return mysqli_result Query result object.
 */
function get_customer_data($mysqli, $email)
{
    $stmt = $mysqli->prepare("
        SELECT customer_id, firstname, lastname, email, telephone 
        FROM " . DB_PREFIX . "customer 
        WHERE email = ?
    ");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Fetches address records for the given customer.
 *
 * @param mysqli $mysqli MySQLi connection object.
 * @param int $customer_id Customer ID.
 * @return mysqli_result Query result object.
 */
function get_addresses($mysqli, $customer_id)
{
    $query = "
    SELECT a.firstname, a.lastname, a.company, a.address_1, a.address_2, a.city, a.postcode,
           c.iso_code_2 AS country_code, z.code AS zone_code
    FROM " . DB_PREFIX . "address a
    LEFT JOIN " . DB_PREFIX . "country c ON a.country_id = c.country_id
    LEFT JOIN " . DB_PREFIX . "zone z ON a.zone_id = z.zone_id
    WHERE a.customer_id = $customer_id
    ";
    return $mysqli->query($query);
}

/**
 * Converts OpenCart data into the PODS JSON format.
 *
 * @param array $customer_data Customer info from the DB.
 * @param mysqli_result $addresses Result set of addresses.
 * @return array Structured array with keys D, A, and P.
 */
function structure_data($customer_data, $addresses)
{
    // General Info (D)
    $D = [
        "u" => $customer_data["email"],
        "n" => $customer_data["firstname"] . ' ' . $customer_data["lastname"],
        "e" => $customer_data["email"],
        "f" => $customer_data["firstname"],
        "l" => $customer_data["lastname"],
    ];

    $A = [];
    $P = [];

    // Addresses (A)
    if ($addresses->num_rows > 0) {
        $addresses_arr = [];
        while ($row = $addresses->fetch_assoc()) {
            $addresses_arr[] = $row;
        }

        // First address → billing
        $billing = $addresses_arr[0];
        $A[] = [
            "fi" => $billing['firstname'],
            "li" => $billing['lastname'],
            "ci" => $billing['company'],
            "a1i" => $billing['address_1'],
            "a2i" => $billing['address_2'],
            "ti" => "billing"
        ];

        // Second address → shipping (or fallback to billing)
        $shipping = $addresses_arr[1] ?? $billing;
        $A[] = [
            "fi" => $shipping['firstname'],
            "li" => $shipping['lastname'],
            "ci" => $shipping['company'],
            "a1i" => $shipping['address_1'],
            "a2i" => $shipping['address_2'],
            "ti" => "shipping"
        ];
    } else {
        // Fallback for missing addresses
        $A[] = ["fi" => "", "li" => "", "ci" => "", "a1i" => "", "a2i" => "", "ti" => "billing"];
        $A[] = ["fi" => "", "li" => "", "ci" => "", "a1i" => "", "a2i" => "", "ti" => "shipping"];
    }

    // Phone numbers (P) — only main one for now
    $P[] = ["pj" => !empty($customer_data['telephone']) ? $customer_data['telephone'] : ""];

    return ["D" => $D, "A" => $A, "P" => $P];
}

/**
 * Saves structured data into a JSON file.
 *
 * @param array $data Structured PODS format.
 * @return void
 */
function save_to_json_file($data)
{
    $json_data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $file_path = 'user_data.json';
    file_put_contents($file_path, $json_data);
    echo "✅ Data successfully saved to '$file_path'.\n";
}

// --- EXECUTION ---

$mysqli = connect_to_database();

echo "🔎 Enter customer email (username is not used in open cart): ";
$email = trim(fgets(STDIN));

$customer_data = get_customer_data($mysqli, $email);

if ($customer_data->num_rows > 0) {
    $row = $customer_data->fetch_assoc();
    $customer_id = $row['customer_id'];

    $addresses = get_addresses($mysqli, $customer_id);
    $structured_data = structure_data($row, $addresses);

    save_to_json_file($structured_data);
} else {
    echo "❌ No customer found with the email '$email'.\n";
}

$mysqli->close();
