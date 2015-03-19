<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use PHPUnit_Framework_Assert as PHPUnit;

/**
 * Defines application features from the specific context.
 */
class InfuseAuthContext extends MinkContext implements Context, SnippetAcceptingContext
{

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /**
     * @static
     * @beforeSuite
     */
    public static function setUpDb()
    {
        Artisan::call('migrate');
    }

    /**
     * @Then I should be able to do something with Laravel
     */
    public function iShouldBeAbleToDoSomethingWithLaravel()
    {
        $environmentFileName = app()->environmentFile();
        $environmentName = env('APP_ENV');
        PHPUnit::assertEquals('.env.behat', $environmentFileName);
        PHPUnit::assertEquals('behat', $environmentName);
    }

    public function iLoginWith($username, $password)
    {
        $this->visit('admin/posts/create');
        $this->fillField('infuseU', $username);
        $this->fillField('infuseP', $password);
        $this->pressButton('go');
        $this->printCurrentUrl();

    }
}
