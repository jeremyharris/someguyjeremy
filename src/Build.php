<?php

namespace JeremyHarris\App;

use JeremyHarris\App\View;

/**
 * Simple build class
 *
 * Takes a layout like so:
 * ```
 * site
 *   |_ views
 *   |  |
 *   |  |_ about.php
 *   |  |_ contact.md
 *   |     |_ sub
 *   |        |_ article.php
 *   |_ assets
 *   | |_ css
 *   | |  |_ css1.css
 *   | |  |_ css2.css
 *   | |
 *   | |_ js
 *   |    |_ script1.js
 *   |    |_ script2.js
 *   |
 *   |_ webroot
 *   |  |_ robots.txt
 *   |  |_ fonts
 *   |     |_ font1.otf
 *   |
 *   |_ layout.php
 * ```
 * And builds it into a static site like so
 * ```
 * build
 *   |_ fonts
 *   |  |_ font1.otf
 *   |
 *   |_ sub
 *   |  |_ article.html
 *   |
 *   |_ styles.css
 *   |_ scripts.js
 *   |_ about.html
 *   |_ contact.html
 *   |_ robots.txt
 * ```
 *
 * All CSS files in `SITE_TARGET/assets/css` will be built into a single file.
 * Similarly, JS files are built into scripts.js. Everything in
 * `SITE_TARGET/webroot` is placed into the `BUILD_TARGET` root as-is, and all
 * view files in `SITE_TARGET/views` are rendered using `JeremyHarris\App\View`
 * using `SITE_TARGET/layout.php` as the wrapping view, and then placed into
 * `BUILD_TARGET` root.
 */
class Build {

    const VIEW_PATH = 'views';
    const ASSET_PATH = 'assets';
    const WEBROOT_PATH = 'webroot';

    /**
     * Site target
     *
     * @var string
     */
    protected $site = null;

    /**
     * Build target
     *
     * @var string
     */
    protected $build = null;

    /**
     * Constructor
     *
     * @param string $siteTarget  Site target
     * @param string $buildTarget Build target directory
     * @throws \Exception
     */
    public function __construct($siteTarget, $buildTarget)
    {
        if (!is_dir($buildTarget) || !is_writable($buildTarget))
        {
            throw new \Exception(sprintf('%s is not a directory that can be used to build', $buildTarget));
        }
        if (!is_dir($siteTarget))
        {
            throw new \Exception(sprintf('%s is not a valid site target', $siteTarget));
        }
        $this->site = $siteTarget;
        $this->build = $buildTarget;
    }

    /**
     * Builds the site
     *
     * @return void
     */
    public function build()
    {
        $webrootPath = self::WEBROOT_PATH . DIRECTORY_SEPARATOR;
        $webroot = $this->getFileTree($this->site . DIRECTORY_SEPARATOR . $webrootPath);
        foreach ($webroot as $file) {
            $contents = file_get_contents($this->site . DIRECTORY_SEPARATOR . $webrootPath . $file);
            $this->addFileToBuild($file, $contents);
        }
    }

    /**
     * Adds a file to the build
     *
     * @param string $filename Relative file path
     * @param string $contents File contents
     * @return void
     */
    public function addFileToBuild($filename, $contents)
    {
        $buildPath = $this->build . DIRECTORY_SEPARATOR . $filename;
        $directory = dirname($buildPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0775, true);
        }
        file_put_contents($buildPath, $contents);
    }

    public function renderView(View $view)
    {
        // wrap in layout
    }

    /**
     * Gets a relative list of files in `$directory`
     *
     * @param  string $directory Directory path
     * @return array
     */
    public function getFileTree($directory)
    {
        $directoryIterator = new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directoryIterator);
        $files = [];
        foreach ($iterator as $file) {
            $files[] = trim(str_replace($directory, '', $file->getPathname()), DIRECTORY_SEPARATOR);
        }
        return $files;
    }

    public function concatAssets()
    {
        // concat css and js
    }

}