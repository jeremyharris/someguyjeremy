<h1>Archive</h1>
<?php
use JeremyHarris\App\Blog;

$Blog = new Blog(__DIR__);

$years = array_reverse($Blog->getPosts(), true);
$cols = array_chunk($years, 3, true);
foreach ($cols as $col) {

    echo '<div class="row archive">';

    foreach ($col as $year => $months) {
        echo '<div class="year col col-md-4">';
        echo '<h2>' . $year . '</h2>';
        echo '<div class="body">';

        foreach ($months as $month => $posts) {
            echo '<div class="clearfix month">';
            echo '<h3>' . $month . '<span class="muted"> /</span></h3>';
            foreach ($posts as $post) {
                echo $post->link();
            }
            echo '</div>';
        }

        echo '</div>';
        echo '</div>';
    }

    echo '</div>';

}


