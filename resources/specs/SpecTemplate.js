/**
 * ====================================================================
 * Feature: [Feature Name in Plain English]
 * ====================================================================
 * 
 * As a [actor/user type]
 * I want [the desired action/outcome]
 * So that [the business value/benefit]
 * 
 * ====================================================================
 */

describe('Feature: [Main Feature Name]', () => {
  /**
   * ────────────────────────────────────────────────────────────────
   * Scenario: [Clear, specific scenario description]
   * ────────────────────────────────────────────────────────────────
   * Given:  [Initial context/precondition]
   * When:   [User action/event]
   * Then:   [Expected outcome/assertion]
   * ────────────────────────────────────────────────────────────────
   */
  describe('Scenario: [Specific Behavior]', () => {
    test('should [expected behavior] when [condition]', () => {
      // ✓ ARRANGE - Setup test data and context
      const setup = {
        data: 'test data',
        status: 'active',
      };
      const expected = 'expected result';

      // ✓ ACT - Perform the action being tested
      // Example: const result = performAction(setup.data);
      const result = expected;

      // ✓ ASSERT - Verify the expected outcome
      expect(result).toBe(expected);
    });

    test('should handle another case when [different condition]', () => {
      // ARRANGE
      const input = 'different input';

      // ACT
      // Code here

      // ASSERT
      expect(input).toBeDefined();
    });
  });

  /**
   * ────────────────────────────────────────────────────────────────
   * Scenario: [Error handling scenario]
   * ────────────────────────────────────────────────────────────────
   */
  describe('Scenario: Error Handling', () => {
    test('should throw error when [error condition]', () => {
      // ARRANGE
      const invalidInput = null;

      // ACT & ASSERT
      expect(() => {
        // Code that should throw error
      }).toThrow();
    });

    test('should return error message on failure', () => {
      // ARRANGE
      const failureCase = false;

      // ACT
      const result = failureCase ? 'success' : 'error';

      // ASSERT
      expect(result).toBe('error');
    });
  });

  /**
   * ────────────────────────────────────────────────────────────────
   * Scenario: [Edge case scenario]
   * ────────────────────────────────────────────────────────────────
   */
  describe('Scenario: Edge Cases', () => {
    test('should handle empty input gracefully', () => {
      // ARRANGE
      const emptyInput = '';

      // ACT
      const result = emptyInput || 'default';

      // ASSERT
      expect(result).toBe('default');
    });

    test('should handle undefined values', () => {
      // ARRANGE
      const undefinedValue = undefined;

      // ACT & ASSERT
      expect(undefinedValue).toBeUndefined();
    });
  });

  // ────────────────────────────────────────────────────────────────
  // Setup & Teardown (Optional)
  // ────────────────────────────────────────────────────────────────

  beforeEach(() => {
    // Setup before each test
    // Initialize mocks, reset state, etc.
  });

  afterEach(() => {
    // Cleanup after each test
    // Reset mocks, clear state, etc.
  });
});
