<?php
namespace JeremyHarris\App;

/**
 * Application
 */
class Application
{

    /**
     * Not-very-smart conversion of a slug to a title
     *
     * @param string $slug Slug
     * @return string
     */
    public static function slugToTitle($slug)
    {
        $words = explode('-', $slug);
        $ucWords = array_map('ucfirst', $words);
        return implode(' ', $ucWords);
    }

}