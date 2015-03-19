@auth
Feature: Infuse Authentication main
  In order to authenticate
  As an infuse system user
  I want to login into the system

  Scenario: Login with super user
    Given I have an account
    When I login with "super" "password
    Then I am on "/admin/dashboard"