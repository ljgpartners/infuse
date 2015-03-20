<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use PHPUnit_Framework_Assert as PHPUnit;
use Laracasts\Behat\Context\Migrator;
use Laracasts\Behat\Context\DatabaseTransactions;

/**
 * Defines application features from the specific context.
 */
class InfuseAuthContext extends MinkContext implements Context, SnippetAcceptingContext
{

    #use Migrator;
    use DatabaseTransactions;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        Artisan::call('migrate:reset',  array('--path' => "vendor/bpez/infuse/migrations"));
        Artisan::call('db:seed', array('--class' => "InfuseDatabaseSeeder"));
    }


    /**
     * @Given I have an account :arg1
     */
    public function iHaveAnAccount($arg1)
    {
        throw new PendingException();
    }

    /**
     * @When I sign in with :arg1 :arg2
     */
    public function iSignInWith($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @Then I should be on the dashboard
     */
    public function iShouldBeOnTheDashboard()
    {
        throw new PendingException();
    }

    /**
     * @When I fill :arg1 with :arg2
     */
    public function iFillWith($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @Given I signed in with :arg1 :arg2
     */
    public function iSignedInWith($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @Then I should be redirected to :arg1
     */
    public function iShouldBeRedirectedTo($arg1)
    {
        throw new PendingException();
    }
}
