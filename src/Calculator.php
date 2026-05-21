<?php declare(strict_types=1);

/**
 * Classe Calculator
 *
 * Cette classe contient la logique métier.
 * Elle ne connaît pas HTTP, JSON ou Docker.
 *
 * Idée pédagogique :
 * - une classe = une responsabilité
 * - le calcul est séparé de l'API
 * - on peut tester cette classe facilement avec PHPUnit
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
     * On lève une exception si b vaut 0.
     * Cela permet de montrer la gestion d'erreur.
     */
    public function divide(float $a, float $b): float
    {
        if ($b == 0.0) {
            throw new InvalidArgumentException('Division par zéro impossible.');
        }

        return $a / $b;
    }
}