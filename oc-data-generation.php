<?php
require_once 'vendor/autoload.php';

$faker = Faker\Factory::create('pt_BR');

/**
 * Generates a Brazilian cellphone number in international format.
 *
 * @param Faker\Generator $faker Instance of Faker.
 * @return string Cellphone number with country code.
 */
function generatePhoneNumber($faker) {
    return '+55' . preg_replace('/\D/', '', $faker->cellphoneNumber);
}

// --- PERSONAL DATA (D) ---
$D = [
    "u" => $faker->userName,
    "n" => $faker->userName,
    "e" => strtolower($faker->safeEmail),
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
$U = [
    "D" => $D,
    "A" => $A,
    "P" => $P
];

// --- SAVE TO JSON FILE ---
file_put_contents('user_data.json', json_encode($U, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "✅ Fake OpenCart user data generated and saved to 'user_data.json'.\n";
