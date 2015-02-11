<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>someguyjeremy.com</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles.css">
    <script src="scripts.js"></script>
</head>
<body>
    <header>
        <button><i class="fa fa-list"></i><span class="sr-only">Menu</span></button>
        <h1>Jeremy Harris <small>Another programmer.</small></h1>
    </header>
    <nav>
        <a href="about.html">About</a>
        <a href="archive.html">Blog</a>
    </nav>
    <section class="container">
        <?php echo $content; ?>
    </section>
</body>
</html>