/**
 * ====================================================================
 * Feature: Form Validation and Submission
 * ====================================================================
 * 
 * As a user
 * I want form validation to provide clear feedback
 * So that I can correct my input before submission
 * 
 * ====================================================================
 */

describe('Feature: Form Validation', () => {
  /**
   * ────────────────────────────────────────────────────────────────
   * Scenario: Form validates email format
   * ────────────────────────────────────────────────────────────────
   * Given:  A registration form is loaded
   * When:   I enter invalid email format
   * Then:   Error message is shown
   * ────────────────────────────────────────────────────────────────
   */
  describe('Scenario: Email Validation', () => {
    test('should reject invalid email format', () => {
      // ARRANGE
      const validator = {
        validateEmail: (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email),
      };
      const invalidEmail = 'notanemail';

      // ACT
      const isValid = validator.validateEmail(invalidEmail);

      // ASSERT
      expect(isValid).toBe(false);
    });

    test('should accept valid email format', () => {
      // ARRANGE
      const validator = {
        validateEmail: (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email),
      };
      const validEmail = 'user@example.com';

      // ACT
      const isValid = validator.validateEmail(validEmail);

      // ASSERT
      expect(isValid).toBe(true);
    });
  });

  /**
   * ────────────────────────────────────────────────────────────────
   * Scenario: Form validates password requirements
   * ────────────────────────────────────────────────────────────────
   */
  describe('Scenario: Password Validation', () => {
    test('should require minimum 8 characters', () => {
      // ARRANGE
      const validator = {
        validatePassword: (pwd) => pwd.length >= 8,
      };
      const shortPassword = 'short';

      // ACT
      const isValid = validator.validatePassword(shortPassword);

      // ASSERT
      expect(isValid).toBe(false);
    });

    test('should accept password with 8+ characters', () => {
      // ARRANGE
      const validator = {
        validatePassword: (pwd) => pwd.length >= 8,
      };
      const validPassword = 'LongPassword123!';

      // ACT
      const isValid = validator.validatePassword(validPassword);

      // ASSERT
      expect(isValid).toBe(true);
    });

    test('should require uppercase letter', () => {
      // ARRANGE
      const validator = {
        validatePassword: (pwd) => /[A-Z]/.test(pwd) && pwd.length >= 8,
      };
      const noUppercase = 'lowercase123';

      // ACT
      const isValid = validator.validatePassword(noUppercase);

      // ASSERT
      expect(isValid).toBe(false);
    });
  });

  /**
   * ────────────────────────────────────────────────────────────────
   * Scenario: Form shows all validation errors
   * ────────────────────────────────────────────────────────────────
   */
  describe('Scenario: Multiple Validation Errors', () => {
    test('should show all errors when multiple fields invalid', () => {
      // ARRANGE
      const validator = {
        validate: (formData) => {
          const errors = [];
          if (!formData.name) errors.push('Name is required');
          if (!formData.email.includes('@')) errors.push('Invalid email');
          if (formData.password.length < 8) errors.push('Password too short');
          return errors;
        },
      };
      const formData = {
        name: '',
        email: 'invalid',
        password: 'short',
      };

      // ACT
      const errors = validator.validate(formData);

      // ASSERT
      expect(errors).toHaveLength(3);
      expect(errors).toContain('Name is required');
      expect(errors).toContain('Invalid email');
      expect(errors).toContain('Password too short');
    });
  });

  // ────────────────────────────────────────────────────────────────
  // Setup & Teardown
  // ────────────────────────────────────────────────────────────────

  beforeEach(() => {
    // Reset form state before each test
    document.body.innerHTML = '<form id="testForm"></form>';
  });

  afterEach(() => {
    // Cleanup after each test
    document.body.innerHTML = '';
  });
});
