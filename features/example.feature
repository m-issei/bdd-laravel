# ====================================================================
# Feature: [Feature Name in Plain English]
# ====================================================================
#
# As a [actor/user type]
# I want [the desired action/outcome]
# So that [the business value/benefit]
#
# ====================================================================

Feature: Example Feature
  Feature descriptions should be user-focused and describe the business value

  Background:
    # Common setup for all scenarios in this feature
    Given the system is initialized
    And test data is available

  # ────────────────────────────────────────────────────────────────
  # Scenario 1: Happy Path / Main Flow
  # ────────────────────────────────────────────────────────────────
  Scenario: User can perform main action successfully
    Given a precondition exists
    When the user performs an action
    Then the expected result should occur
    And additional verification

  # ────────────────────────────────────────────────────────────────
  # Scenario 2: Alternative Flow
  # ────────────────────────────────────────────────────────────────
  Scenario: User can perform alternative action
    Given different initial conditions
    When the user chooses alternative path
    Then alternative outcome happens
    And system behaves correctly

  # ────────────────────────────────────────────────────────────────
  # Scenario 3: Error Case / Sad Path
  # ────────────────────────────────────────────────────────────────
  Scenario: System handles error gracefully
    Given invalid input is provided
    When user attempts to proceed
    Then error is caught and handled
    And user receives helpful error message

  # ────────────────────────────────────────────────────────────────
  # Scenario 4: Edge Case
  # ────────────────────────────────────────────────────────────────
  Scenario: System handles boundary conditions
    Given edge case condition exists
    When boundary value is used
    Then system responds appropriately
    And no unexpected behavior occurs

  # ────────────────────────────────────────────────────────────────
  # Scenario 5: Using Examples / Scenario Outline
  # ────────────────────────────────────────────────────────────────
  Scenario Outline: Testing multiple data sets
    Given I have "<input>" as input
    When I process the input
    Then I should get "<output>" as result

    Examples:
      | input   | output  |
      | valid   | success |
      | invalid | failure |
      | empty   | error   |
