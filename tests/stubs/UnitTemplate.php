<?php

namespace Tests\Unit\BDD;

use PHPUnit\Framework\TestCase;

/**
 * ====================================================================
 * Unit Test: [Class or Method Name]
 * ====================================================================
 * 
 * Tests for: App\[NameSpace]\ClassName
 * Purpose: [What this class/method does and why it matters]
 * 
 * ====================================================================
 */
class ExampleUnitTest extends TestCase
{
    /**
     * ────────────────────────────────────────────────────────────────
     * Scenario: [Specific behavior being tested]
     * ────────────────────────────────────────────────────────────────
     * Given:  [Initial state]
     * When:   [Operation performed]
     * Then:   [Expected behavior]
     * ────────────────────────────────────────────────────────────────
     */
    public function test_should_return_expected_value_when_condition_met(): void
    {
        // ✓ ARRANGE - Setup dependencies and test input
        $input = 'test input';
        $expected = 'expected output';

        // ✓ ACT - Call the method being tested
        // Example: $result = new ClassName()->methodName($input);
        $result = $input;

        // ✓ ASSERT - Verify the result
        $this->assertEquals($expected, $result);
    }

    /**
     * Scenario: [Another specific behavior]
     */
    public function test_should_handle_edge_case(): void
    {
        // ARRANGE
        $edgeCase = null;

        // ACT
        // Code here

        // ASSERT
        $this->assertNull($edgeCase);
    }

    /**
     * Scenario: [Exception handling]
     */
    public function test_should_throw_exception_on_invalid_input(): void
    {
        $this->expectException(\Exception::class);
        
        // ACT - This should throw exception
        throw new \Exception('Test exception');
    }
}
