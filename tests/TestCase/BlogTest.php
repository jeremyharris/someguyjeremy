<?php
namespace JeremyHarris\App\Test\TestCase;

use JeremyHarris\App\Blog;

/**
 * Blog test
 */
class BlogTest extends \PHPUnit_Framework_TestCase
{

    /**
     * setUp
     *
     * @return void
     */
    public function setUp() {
        parent::setUp();
        $this->Blog = new Blog(TEST_APP . DS . 'views');
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown() {
        unset($this->Blog);
        parent::tearDown();
    }

    /**
     * testGetPosts
     *
     * @return void
     */
    public function testGetPosts()
    {
        $posts = $this->Blog->getPosts();

        $result = array_keys($posts);
        $expected = [
            '2012',
            '2013',
        ];
        $this->assertEquals($expected, $result);

        $result = array_keys($posts['2012']);
        $expected = [
            '01',
            '02',
        ];
        $this->assertEquals($expected, $result);

        foreach ($posts['2012']['01'] as $post) {
            $this->assertInstanceOf('\\JeremyHarris\\App\\Blog\\Post', $post);
        }
    }

    /**
     * testGetLatest
     *
     * @return void
     */
    public function testGetLatest()
    {
        $posts = $this->Blog->getPosts();

        $mayPosts = $posts['2013']['05'];

        touch($mayPosts[0]->source()->getRealpath());

        $result = $this->Blog->getLatest();
        $expected = $mayPosts[0];

        touch($mayPosts[1]->source()->getRealpath(), strtotime('tomorrow'));

        $result = $this->Blog->getLatest();
        $expected = $mayPosts[1];
    }

}