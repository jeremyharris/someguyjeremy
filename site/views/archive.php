<h1>Archive</h1>
<?php
use JeremyHarris\App\Application;

$directoryIterator = new \DirectoryIterator(__DIR__);
$iterator = new \IteratorIterator($directoryIterator);
$directories = new \RegexIterator($iterator, '/\d+$/');

$years = [];
foreach ($directories as $dir) {
    $years[] = $dir->getBasename();
}

$rows = array_chunk($years, 3);

foreach ($rows as $row) {

    echo '<div class="row archive">';

    foreach ($row as $year) {
        echo '<div class="year col col-md-4">';
        echo '<h2>' . $year . '</h2>';
        echo '<div class="body">';

        $directoryIterator = new \DirectoryIterator(__DIR__ . DIRECTORY_SEPARATOR . $year);
        $iterator = new \IteratorIterator($directoryIterator);
        $directories = new \RegexIterator($iterator, '/\d+$/');

        $months = array_fill(0, 12, null);
        foreach ($directories as $dir) {
            $monthInt = (int)$dir->getBasename();
            $months[$monthInt] = $dir->getBasename();
        }

        foreach ($months as $month) {
            if (empty($month)) {
                continue;
            }
            $directoryIterator = new \RecursiveDirectoryIterator(__DIR__ . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $month, \FilesystemIterator::SKIP_DOTS);
            echo '<div class="clearfix month">';
            echo '<h3>' . $month . '<span class="muted"> /</span></h3>';
            foreach ($directoryIterator as $file) {
                $filePieces = explode('.', $file->getBasename());
                $slug = $filePieces[0];
                $title = Application::slugToTitle($slug);
                $link = "<a href=\"/$year/$month/$slug.html\">$title</a>";
                echo $link;
            }
            echo '</div>';
        }

        echo '</div>';
        echo '</div>';
    }

    echo '</div>';

}


