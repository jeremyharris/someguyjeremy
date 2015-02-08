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

    /**
     * testRenderMarkdownView
     *
     * @return void
     */
    public function testRenderMarkdownView()
    {
        $view = new View(TEST_APP . DS . 'markdown.md');
        $result = $view->render();

        $h1 = '/<h1>(.+)<\/h1>/';
        $p = '/<p>(.+)<code>(.+)<\/code><\/p>/';
        $ul = '/<ul>(.*)<\/ul>/s';
        $li = '/<li>(.+)<\/li>/';

        $this->assertRegExp($h1, $result);
        $this->assertRegExp($p, $result);
        $this->assertRegExp($ul, $result);
        $this->assertRegExp($li, $result);
    }

    /**
     * testIsMarkdown
     *
     * @return void
     */
    public function testIsMarkdown()
    {
        $this->assertFalse($this->View->isMarkdown());

        $mdView = new View(TEST_APP . DS . 'markdown.md');
        $this->assertTrue($mdView->isMarkdown());
    }
}
