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
        <table class="header">
            <tr>
                <td class="menu">
                    <button><i class="fa fa-bars"></i><span class="sr-only">Menu</span></button>
                </td>
                <td class="title">
                    <h1>
                        <a href="/"><i class="fa fa-terminal"></i> Jeremy Harris</a> <small>Programming is art that fights back.</small>
                    </h1>
                </td>
                <td class="nav">
                    <nav>
                        <a href="/">Home</a>
                        <span class="divider">/</span>
                        <a href="/archive.html">Blog</a>
                        <span class="divider">/</span>
                        <a href="/contact.html">Contact</a>
                    </nav>
                </td>
            </tr>
        </table>
    </header>
    <section class="container">
        <?php echo $content; ?>
    </section>
</body>
</html>