<?php
namespace JeremyHarris\App;

class Application
{

    public static function slugToTitle($slug)
    {
        $words = explode('-', $slug);
        $ucWords = array_map('ucfirst', $words);
        return implode(' ', $ucWords);
    }

}