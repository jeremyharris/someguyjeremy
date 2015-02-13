<?php
namespace JeremyHarris\App\Test\TestCase;

use JeremyHarris\App\Application;

/**
 * Application test
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{

    /**
     * testSlugToTitle
     *
     * @return void
     */
    public function testSlugToTitle()
    {
        $result = Application::slugToTitle('my-slug-name');
        $expected = 'My Slug Name';
        $this->assertEquals($expected, $result);
    }

}
