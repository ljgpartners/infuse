@auth
Feature: User accounts
    In order to give user accounts to manage content
    As an administrator
    I need authentication

    Scenario: Successful authentication
        Given I have an account "super"
        And I am on "/admin/login"
        When I sign in with "super" "password"
        Then I should be on the dashboard

    Scenario: Failed authentication
        Given I have an account "super"
        And I am on "/admin/login"
        When I fill "infuseU" with "super"
        And I fill "infuseP" with "wrong_password"
        And I press "go"
        Then I should see "User has entered the wrong password"

    Scenario: Signing user out
        Given I signed in with "super" "password"
        And I am on "/admin/dashboard"
        When I press "Sign out"
        Then I am on "/admin/login"

    Scenario: Guest can't access admin
        Given I am on "/admin/dashboard"
        Then I should be redirected to "/admin/login"
