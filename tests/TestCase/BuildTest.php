<?php

namespace JeremyHarris\App\Test\TestSuite;

use JeremyHarris\App\Build;

class BuildTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test build path in tmp dir
     *
     * @var string
     */
    protected $testBuildPath = null;

    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $tempBuild = uniqid();
        $testBuildPath = TEST_BUILD . DS . $tempBuild;
        mkdir($testBuildPath);
        $this->testBuildPath = $testBuildPath;
        $this->Build = new Build(TEST_APP, $testBuildPath);
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Build);
        parent::tearDown();
    }

    /**
     * testBuildBadTargetException
     *
     * @expectedException \Exception
     * @return void
     */
    public function testBuildBadSiteException()
    {
        new Build('invalid site', TEST_BUILD);
    }

    /**
     * testBuildBadTargetException
     *
     * @expectedException \Exception
     * @return void
     */
    public function testBuildBadTargetException()
    {
        new Build(TEST_APP, 'invalid target');
    }

    /**
     * testBuild
     *
     * @return void
     */
    public function testBuild()
    {
        $this->Build->build();

        $this->assertTrue(file_exists($this->testBuildPath . DS . 'permanent'));
        $this->assertTrue(file_exists($this->testBuildPath . DS . 'permanent' . DS . 'empty'));
        $this->assertTrue(file_exists($this->testBuildPath . DS . 'robots.txt'));

        $this->assertTrue(file_exists($this->testBuildPath . DS . 'subdir'));
        $this->assertTrue(file_exists($this->testBuildPath . DS . 'subdir' . DS . 'article.html'));
        $this->assertTrue(file_exists($this->testBuildPath . DS . 'html.html'));
        $this->assertTrue(file_exists($this->testBuildPath . DS . 'markdown.html'));

        $this->assertTrue(file_exists($this->testBuildPath . DS . 'scripts.js'));
        $this->assertTrue(file_exists($this->testBuildPath . DS . 'styles.css'));

        $html = file_get_contents($this->testBuildPath . DS . 'html.html');
        $this->assertRegExp('/<html/', $html);

        $this->Build->useLayout('missing');
        $this->Build->build();

        $this->assertTrue(file_exists($this->testBuildPath . DS . 'subdir'));
        $this->assertTrue(file_exists($this->testBuildPath . DS . 'subdir' . DS . 'article.html'));
        $this->assertTrue(file_exists($this->testBuildPath . DS . 'html.html'));
        $this->assertTrue(file_exists($this->testBuildPath . DS . 'markdown.html'));
    }

    /**
     * testGetFileTree
     *
     * @return void
     */
    public function testGetFileTree()
    {
        $result = $this->Build->getFileTree(TEST_APP . DS . 'views');
        $result = array_flip($result);
        $someExpected = [
            'html.php',
            'markdown.md',
            'subdir' . DS . 'article.php',
        ];
        foreach ($someExpected as $expected) {
            $this->assertArrayHasKey($expected, $result);
        }
    }

    /**
     * testConcatFiles
     *
     * @return void
     */
    public function testConcatFiles()
    {
        $jsPath = 'assets' . DS . 'js';
        $files = $this->Build->getFileTree(TEST_APP . DS . $jsPath);
        array_walk($files, [$this->Build, 'prependDirectory'], $jsPath);
        $result = $this->Build->concatFiles($files);

        $this->assertRegExp('/alert/', $result);
        $this->assertRegExp('/\/\/ hi/', $result);
    }

    /**
     * testPrependDirectory
     *
     * @return void
     */
    public function testPrependDirectory()
    {
        $ds = DIRECTORY_SEPARATOR;
        $path = "{$ds}some{$ds}file.txt";
        $this->Build->prependDirectory($path, 0, "{$ds}directory{$ds}");
        $expected = "{$ds}directory{$ds}some{$ds}file.txt";
        $this->assertEquals($expected, $path);
    }
}
