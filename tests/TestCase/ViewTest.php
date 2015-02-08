<?php
namespace JeremyHarris\App\Test\TestCase;

use JeremyHarris\App\View;

/**
 * View test
 */
class ViewTest extends \PHPUnit_Framework_TestCase
{

    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->View = new View(TEST_APP . DS . 'html.php');
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->View);
        parent::tearDown();
    }

    /**
     * testInvalidViewFile
     *
     * @expectedException \Exception
     */
    public function testInvalidViewFile()
    {
        new View('invalid file name');
    }

    /**
     * testSetAndGet
     *
     * @return void
     */
    public function testSetAndGet()
    {
        $this->View->set('var', 'test');
        $result = $this->View->get('var');
        $expected = 'test';
        $this->assertEquals($expected, $result);
    }

    /**
     * testGetException
     *
     * @expectedException \OutOfBoundsException
     */
    public function testGetException()
    {
        $this->View->get('missing');
    }

    /**
     * testRender
     *
     * @return void
     */
    public function testRender()
    {
        $this->View->set('test', 'Span!');
        $result = $this->View->render();
        $expected = '<span>Span!</span>';
        $this->assertEquals($expected, $result);
    }

}
