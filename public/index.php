<?php declare(strict_types=1);

require_once __DIR__ . '/../src/Calculator.php';

/**
 * Point d'entrée de l'API.
 *
 * Exemples :
 * /?operation=add&a=4&b=2
 * /?operation=divide&a=20&b=5
 *
 * Objectif pédagogique :
 * - lire les paramètres GET
 * - appeler la classe métier
 * - renvoyer une réponse JSON
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$calculator = new Calculator();

$operation = $_GET['operation'] ?? null;
$a = $_GET['a'] ?? null;
$b = $_GET['b'] ?? null;

/**
 * Vérification des paramètres obligatoires.
 */
if ($operation === null || $a === null || $b === null) {
    http_response_code(400);

    echo json_encode(
        ['error' => 'Paramètres attendus : operation, a, b'],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );
    exit;
}

/**
 * Conversion des valeurs en nombres.
 */
$a = (float) $a;
$b = (float) $b;

try {
    switch ($operation) {
        case 'add':
            $result = $calculator->add($a, $b);
            break;

        case 'subtract':
            $result = $calculator->subtract($a, $b);
            break;

        case 'multiply':
            $result = $calculator->multiply($a, $b);
            break;

        case 'divide':
            $result = $calculator->divide($a, $b);
            break;

        default:
            http_response_code(400);
            echo json_encode(
                ['error' => 'Opération inconnue. Utiliser : add, subtract, multiply, divide'],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );
            exit;
    }

    echo json_encode(
        [
            'operation' => $operation,
            'a' => $a,
            'b' => $b,
            'result' => $result
        ],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );
} catch (InvalidArgumentException $e) {
    http_response_code(400);

    echo json_encode(
        ['error' => $e->getMessage()],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );
}