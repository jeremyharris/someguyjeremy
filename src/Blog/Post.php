<?php
namespace JeremyHarris\App\Blog;

use JeremyHarris\App\Application;

/**
 * Post
 *
 * Expects posts to specifically be placed in SOME_DIR/YEAR/MONTH/post-file.ext
 */
class Post
{

    /**
     * Slug
     *
     * @var string
     */
    protected $slug;

    /**
     * Year
     *
     * @var string
     */
    protected $year;

    /**
     * Month
     *
     * @var string
     */
    protected $month;

    /**
     * Post file object
     *
     * @var \SplFileObject
     */
    protected $source;

    /**
     * Constructor
     *
     * @param \SplFileObject $post Post source file
     */
    public function __construct(\SplFileObject $post)
    {
        $this->source = $post;

        $paths = explode(DIRECTORY_SEPARATOR, $post->getPath());

        $this->month = $paths[count($paths) - 1];
        $this->year = $paths[count($paths) - 2];
        $this->slug = $post->getBasename('.' . $post->getExtension());
    }

    /**
     * Gets an HTML link to the post, where it will be built
     *
     * @return string
     */
    public function link()
    {
        return "<a href=\"/$this->year/$this->month/$this->slug.html\">" . $this->title() . "</a>";
    }

    /**
     * Gets post title based on slug
     *
     * @return string
     */
    public function title()
    {
        return Application::slugToTitle($this->slug);
    }

    /**
     * Gets slug
     *
     * @return string
     */
    public function slug()
    {
        return $this->slug;
    }

    /**
     * Gets post year
     *
     * @return string
     */
    public function year()
    {
        return $this->year;
    }

    /**
     * Gets post month
     *
     * @return string
     */
    public function month()
    {
        return $this->month;
    }

    /**
     * Gets post source file object
     *
     * @return \SplFileObject
     */
    public function source()
    {
        return $this->source;
    }

}
