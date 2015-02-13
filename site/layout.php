<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>someguyjeremy.com / <?php echo $title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/styles.css">
    <script src="/scripts.js"></script>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.4/styles/github.min.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.4/highlight.min.js"></script>
    <script>hljs.initHighlightingOnLoad();</script>
</head>
<body>
    <header>
        <button><i class="fa fa-bars"></i><span class="sr-only">Menu</span></button>
        <h1><a href="/"><i class="fa fa-terminal"></i> Jeremy Harris</a> <small>Programming is art that fights back.</small></h1>
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
</body>
</html>