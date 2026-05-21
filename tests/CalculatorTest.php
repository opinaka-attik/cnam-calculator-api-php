<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Calculator.php';

/**
 * Tests unitaires de la classe Calculator.
 *
 * Structure d'un test :
 * - Arrange : préparation
 * - Act : action
 * - Assert : vérification
 */
final class CalculatorTest extends TestCase
{
    public function testAdd(): void
    {
        $calculator = new Calculator();
        $result = $calculator->add(2, 3);

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