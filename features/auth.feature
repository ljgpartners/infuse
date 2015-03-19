@auth
Feature: User accounts
    In order to give user accounts to manage content
    As an administrator
    I need authentication, user creation and reset password page

    Scenario: Successful authentication
        Given I have an account "super" "password"
        When I sign in
        Then I should be logged in