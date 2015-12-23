<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>someguyjeremy.com / <?php echo $title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/styles.css?_=<?php echo time(); ?>">
    <script src="/scripts.js?_=<?php echo time(); ?>"></script>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.4/styles/github.min.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.4/highlight.min.js"></script>
    <script>hljs.initHighlightingOnLoad();</script>
</head>
<body>
    <header>
        <h1>
            <a href="/"><i class="fa fa-terminal"></i> Jeremy Harris</a>
        </h1>
    </header>
    <nav>
        <a href="/">Home</a>
        <span class="divider">/</span>
        <a href="/archive.html">Blog</a>
        <span class="divider">/</span>
        <a href="/contact.html">Contact</a>
    </nav>
    <section class="container">
        <?php echo $content; ?>
    </section>
    <section class="container about">
        <img src="https://secure.gravatar.com/userimage/15404927/11e72399015e42ff118efb96ddcd7749?size=250" />
        <p>
            Jeremy Harris is a <span class="highlight">web developer</span> with over
            10 years of experience. He&apos;s coded in many languages and
            currently focuses on PHP, both agnostic and framework-based. When
            he isn&apos;t at the keyboard, you can find him walking
            <span class="highlight"><a href="https://twitter.com/riverthepuppy" target="_blank">@riverthepuppy</a></span>
            or <span class="highlight">brewing</span> beer. He only talks in the
            third person when peer pressure dictates he should, such as on his
            blog.
        </p>
    </section>
</body>
</html>