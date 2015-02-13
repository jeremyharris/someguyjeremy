<?php
use JeremyHarris\App\Blog;
?>
<h1>Hi</h1>

<p>
    You look like a hoopy frood. My name is <span class="highlight">Jeremy</span>,
    I am a <span class="highlight">programmer</span> living in the
    middle of nowhere, coding mostly in <span class="highlight">PHP</span>. I
    occasionally <a href="/archive.html">write posts</a> on web development.
</p>

<h2>Latest Post</h2>

<p class="latest">
    <?php
    $Blog = new Blog(__DIR__);
    $latest = $Blog->getLatest();
    ?>
    <span class="year"><?php echo $latest->year(); ?></span>
    /
    <span class="month"><?php echo $latest->month(); ?></span>
    <?php echo $latest->link(); ?>
</p>
