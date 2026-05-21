Oui — voici une proposition **très simple** d’API PHP “calculatrice” avec `Dockerfile`, `docker-compose.yml` et des tests unitaires PHPUnit, pensée pour un usage pédagogique en classe. L’approche la plus légère consiste à utiliser le serveur web intégré de PHP dans un conteneur Docker et à lancer les tests dans le même service avec `docker compose run`, ce qui reste simple à expliquer aux élèves. [github](https://github.com/sspangsberg/PHP-Calculator)

## Structure

Je te propose une architecture minimale avec une classe métier `Calculator`, un point d’entrée HTTP `public/index.php`, un fichier `composer.json`, un `Dockerfile`, un `docker-compose.yml` et un dossier `tests/` pour PHPUnit. Cette séparation est pédagogique, car elle montre clairement la différence entre la logique métier, l’interface HTTP et les tests. [stackoverflow](https://stackoverflow.com/questions/60284239/unable-to-run-phpunit-tests-on-api-rest-served-by-a-docker-with-php-fpm-and-ngin)

```txt
calculator-c/
├── src/
│   └── Calculator.php
├── public/
│   └── index.php
├── tests/
│   └── CalculatorTest.php
├── composer.json
├── phpunit.xml
├── Dockerfile
└── docker-compose.yml
```

## Fichiers

Le serveur PHP intégré peut être démarré dans Docker avec une commande du type `php -S 0.0.0.0:8000 -t public`, ce qui évite d’ajouter Apache ou Nginx et garde le projet très lisible pour des débutants. Docker recommande aussi de lancer les tests via `docker compose run --build --rm ... ./vendor/bin/phpunit`, ce qui est parfait pour montrer une boucle simple “coder → tester”. [gist.github](https://gist.github.com/yujiod/31af35417bffb80a08df)

### `src/Calculator.php`

```php
<?php

declare(strict_types=1);

/**
 * Classe Calculator
 *
 * Cette classe contient uniquement la logique métier.
 * C'est ici que l'on place les calculs.
 *
 * Intérêt pédagogique :
 * - séparer la logique du code HTTP
 * - rendre le code testable facilement
 */
class Calculator
{
    /**
     * Additionne deux nombres.
     */
    public function add(float $a, float $b): float
    {
        return $a + $b;
    }

    /**
     * Soustrait le second nombre du premier.
     */
    public function subtract(float $a, float $b): float
    {
        return $a - $b;
    }

    /**
     * Multiplie deux nombres.
     */
    public function multiply(float $a, float $b): float
    {
        return $a * $b;
    }

    /**
     * Divise le premier nombre par le second.
     *
     * On lève une exception si on tente une division par zéro.
     * Cela permet de montrer aux élèves la gestion d'erreur.
     */
    public function divide(float $a, float $b): float
    {
        if ($b == 0.0) {
            throw new InvalidArgumentException('Division par zéro impossible.');
        }

        return $a / $b;
    }
}
```

### `public/index.php`

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/Calculator.php';

/**
 * Petit point d'entrée HTTP pour l'API.
 *
 * Exemple d'appel :
 * /?operation=add&a=4&b=2
 *
 * Objectif pédagogique :
 * - lire des paramètres GET
 * - appeler une classe métier
 * - renvoyer du JSON
 */

header('Content-Type: application/json; charset=utf-8');

$calculator = new Calculator();

$operation = $_GET['operation'] ?? null;
$a = isset($_GET['a']) ? (float) $_GET['a'] : null;
$b = isset($_GET['b']) ? (float) $_GET['b'] : null;

if ($operation === null || $a === null || $b === null) {
    http_response_code(400);

    echo json_encode([
        'error' => 'Paramètres attendus : operation, a, b'
    ], JSON_PRETTY_PRINT);
    exit;
}

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
            echo json_encode([
                'error' => 'Opération inconnue. Utiliser : add, subtract, multiply, divide'
            ], JSON_PRETTY_PRINT);
            exit;
    }

    echo json_encode([
        'operation' => $operation,
        'a' => $a,
        'b' => $b,
        'result' => $result
    ], JSON_PRETTY_PRINT);

} catch (InvalidArgumentException $e) {
    http_response_code(400);

    echo json_encode([
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
```

### `tests/CalculatorTest.php`

```php
<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Calculator.php';

/**
 * Tests unitaires de la classe Calculator.
 *
 * Objectif pédagogique :
 * - montrer la structure AAA : Arrange / Act / Assert
 * - vérifier les cas simples
 * - vérifier aussi un cas d'erreur
 */
final class CalculatorTest extends TestCase
{
    public function testAdd(): void
    {
        // Arrange : on prépare l'objet et les données
        $calculator = new Calculator();

        // Act : on exécute la méthode à tester
        $result = $calculator->add(2, 3);

        // Assert : on vérifie le résultat attendu
        $this->assertEquals(5, $result);
    }

    public function testSubtract(): void
    {
        $calculator = new Calculator();
        $result = $calculator->subtract(10, 4);

        $this->assertEquals(6, $result);
    }

    public function testMultiply(): void
    {
        $calculator = new Calculator();
        $result = $calculator->multiply(6, 7);

        $this->assertEquals(42, $result);
    }

    public function testDivide(): void
    {
        $calculator = new Calculator();
        $result = $calculator->divide(20, 5);

        $this->assertEquals(4, $result);
    }

    public function testDivideByZeroThrowsException(): void
    {
        $calculator = new Calculator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Division par zéro impossible.');

        $calculator->divide(10, 0);
    }
}
```

### `composer.json`

```json
{
  "name": "demo/calculator-api",
  "description": "API PHP simple de calculatrice pour usage pedagogique",
  "type": "project",
  "require": {},
  "require-dev": {
    "phpunit/phpunit": "^10.5"
  },
  "autoload": {
    "classmap": [
      "src/"
    ]
  },
  "autoload-dev": {
    "classmap": [
      "tests/"
    ]
  },
  "scripts": {
    "test": "phpunit --testdox"
  }
}
```

### `phpunit.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Calculator Test Suite">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

### `Dockerfile`

Un Dockerfile simple pour PHP peut exposer un port, copier le projet, installer Composer puis démarrer le serveur intégré PHP. Cette logique reste cohérente avec les exemples simples de serveur PHP en conteneur et avec la documentation Docker sur les applications PHP testées dans un conteneur. [github](https://github.com/sspangsberg/PHP-Calculator)

```dockerfile
FROM php:8.2-cli

# Dossier de travail dans le conteneur
WORKDIR /app

# Installation de quelques outils utiles
RUN apt-get update && apt-get install -y git unzip zip \
    && rm -rf /var/lib/apt/lists/*

# Installation de Composer depuis l'image officielle
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copie des fichiers du projet
COPY . /app

# Installation des dépendances PHP, y compris PHPUnit
RUN composer install

# Le port utilisé par le serveur PHP intégré
EXPOSE 8000

# Lancement de l'API
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
```

### `docker-compose.yml`

```yaml
services:
  api:
    build: .
    container_name: php-calculator-api
    ports:
      - "8000:8000"
    volumes:
      - ./:/app
    working_dir: /app
```

## Commandes

Docker Compose permet de lancer les tests dans un conteneur avec une commande dédiée, et Docker documente explicitement ce mode de travail pour PHP et PHPUnit. Le flag `--testdox` est aussi souvent utilisé dans les exemples pédagogiques, car il rend la sortie plus lisible pour des apprenants. [stackoverflow](https://stackoverflow.com/questions/60284239/unable-to-run-phpunit-tests-on-api-rest-served-by-a-docker-with-php-fpm-and-ngin)

### Construire et lancer l’API

```bash
docker compose up --build
```

API disponible sur :

```txt
http://localhost:8000
```

### Tester dans le navigateur

```txt
http://localhost:8000/?operation=add&a=4&b=2
http://localhost:8000/?operation=subtract&a=10&b=3
http://localhost:8000/?operation=multiply&a=6&b=7
http://localhost:8000/?operation=divide&a=20&b=5
```

### Lancer les tests

```bash
docker compose run --build --rm api ./vendor/bin/phpunit --testdox
```

Exemple de sortie attendue :

```txt
Calculator
 ✔ Add
 ✔ Subtract
 ✔ Multiply
 ✔ Divide
 ✔ Divide by zero throws exception
```

## Explications élèves

Pour un cours, le plus important est d’expliquer que la classe `Calculator` contient la logique métier, alors que `index.php` ne fait que recevoir la requête HTTP, appeler la bonne méthode et renvoyer une réponse JSON. Cette séparation rend le code plus facile à tester, ce qui correspond au principe illustré dans les exemples PHPUnit classiques de calculatrice. [dev](https://dev.to/eelcoverbrugge/symfony-unit-testing-5ek5)

Tu peux faire travailler tes élèves sur ces tests progressifs :
- Tester l’addition.
- Tester la soustraction.
- Tester la multiplication.
- Tester la division.
- Tester l’erreur de division par zéro. [dev](https://dev.to/eelcoverbrugge/symfony-unit-testing-5ek5)

Tu peux aussi leur demander des évolutions simples :
- Ajouter l’opération `modulo`.
- Vérifier qu’une opération inconnue renvoie une erreur HTTP 400.
- Ajouter des tests avec des nombres décimaux.
- Refactoriser pour utiliser un autoload PSR-4 plus avancé plus tard, une fois la version simple comprise. [github](https://github.com/sspangsberg/PHP-Calculator)

## Variante cours

Pour une première séance, je te conseille de faire en 3 étapes :
1. Créer seulement `Calculator.php` et tester la classe avec PHPUnit.
2. Ajouter ensuite `index.php` pour transformer la classe en mini API HTTP.
3. Ajouter enfin Docker pour montrer la reproductibilité de l’environnement. [stackoverflow](https://stackoverflow.com/questions/60284239/unable-to-run-phpunit-tests-on-api-rest-served-by-a-docker-with-php-fpm-and-ngin)

Je peux maintenant te générer la **version complète prête à copier-coller**, avec tous les fichiers regroupés dans un seul bloc par fichier, ou te proposer une **version encore plus simple** sans Composer au début pour des débutants.