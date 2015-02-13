<?php
namespace JeremyHarris\App;

use JeremyHarris\App\Blog\Post;

/**
 * Blog
 *
 * Gets a collection of posts from a site source
 */
class Blog
{

    /**
     * Path to site source
     *
     * @var string
     */
    protected $site;

    /**
     * Array of posts by year and month
     *
     * @var array
     */
    protected $posts = [];

    /**
     * Constructor
     *
     * Iterates through directories, looking for blog posts and adding them to
     * this collection
     *
     * @param string $site Path to site source
     */
    public function __construct($site)
    {
        $this->site = $site;

        $directoryIterator = new \RecursiveDirectoryIterator($this->site, \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directoryIterator);

        foreach ($iterator as $file) {
            if (!$file->isDir() && preg_match('/[\d]{4}\/[\d]{2}\/(.+)$/', $file->getPathname())) {
                $this->addPost(new \SplFileObject($file));
            }
        }

    }

    /**
     * Gets posts
     *
     * @return array
     */
    public function getPosts()
    {
        ksort($this->posts);
        foreach ($this->posts as &$year) {
            ksort($year);
        }
        return $this->posts;
    }

    /**
     * Gets latest post (by mtime)
     *
     * @return Post
     */
    public function getLatest()
    {
        $reversed = array_reverse($this->posts);
        $latestYear = current($reversed);
        $reversedMonths = array_reverse($latestYear);
        $latestMonth = current($reversedMonths);

        usort(
            $latestMonth,
            function($post1, $post2) {
                return $post1->source()->getMTime() > $post2->source()->getMTime() ? -1 : 1;
            }
        );

        return $latestMonth[0];
    }

    /**
     * Adds a post
     *
     * @param \SplFileObject $file Post file object
     */
    protected function addPost(\SplFileObject $file)
    {
        $post = new Post($file);
        if (!array_key_exists($post->year(), $this->posts)) {
            $this->posts[$post->year()] = [];
        }
        if (!array_key_exists($post->month(), $this->posts[$post->year()])) {
            $this->posts[$post->year()][$post->month()] = [];
        }
        $this->posts[$post->year()][$post->month()][] = $post;
    }

}