<?php
require_once 'vendor/autoload.php';

$faker = Faker\Factory::create();

/**
 * Generates a Brazilian-style cellphone number in international format.
 *
 * @param Faker\Generator $faker Instance of Faker.
 * @return string Cellphone number.
 */
function generatePhoneNumber($faker) {
    return "+55" . $faker->numerify('##') . $faker->numerify('########');
}

// --- PERSONAL DATA (D) ---
$D = [
    "u" => $faker->userName,
    "n" => $faker->userName,
    "e" => $faker->email,
    "f" => $faker->firstName,
    "l" => $faker->lastName
];

// --- ADDRESSES (A) ---
$A = [
    [
        "fi" => $faker->firstName,
        "li" => $faker->lastName,
        "ci" => $faker->company,
        "a1i" => $faker->streetAddress,
        "a2i" => $faker->secondaryAddress,
        "ti" => "billing"
    ],
    [
        "fi" => $faker->firstName,
        "li" => $faker->lastName,
        "ci" => $faker->company,
        "a1i" => $faker->streetAddress,
        "a2i" => $faker->secondaryAddress,
        "ti" => "shipping"
    ]
];

// --- PHONES (P) ---
$P = [
    ["pj" => generatePhoneNumber($faker)],
    ["pj" => generatePhoneNumber($faker)]
];

// --- FINAL USER OBJECT (U) ---
$U = ["D" => $D, "A" => $A, "P" => $P];

// --- SAVE TO JSON FILE ---
file_put_contents('user_data.json', json_encode($U, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "✅ Fake WordPress user data generated and saved to 'user_data.json'.\n";
