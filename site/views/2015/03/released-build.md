# Released jeremyharris/build

I pushed an early release for my static site generator today. It's a fairly
simple generator that I used to create this site, but it has a few cool features
that make publishing content a bit more fun than it used to be for me.

> Check it out on Github: [jeremyharris/build][1]

When I set out to switch my site from an unnecessarily heavy CMS to a simple
static site, I wanted a generator that was written in PHP that would give me the
flexibility of writing markdown or PHP. I couldn't find anything out there at
the time, so I wrote my own (because why not).

I'm fairly happy with the result, as it does what I want and is a fairly small
codebase.

I've wrapped up deployment as well (that is, uploading files to AWS S3), which
you can check out here: [jeremyharris/someguyjeremy][2]. I used [CLImate][3] for
the CLI tool, as it recently released no-nosense argument parsing. Since `Build::build()`
is smart enough to only build new files, the whole site isn't deployed each time
I deploy to S3 (saving me only some pennies because my site's not really very
big...).

## Improvements

There are still some improvements I'd like to make. A notable one, for example,
is that while it doesn't rebuild unmodified files, it's not smart enough to
detect a layout change and rebuild the views. Also, if you create a PHP page
(such as a Blog archive) page, that page will not be rebuilt if you add a post.
For now, I just force build when I make these sorts of changes, but making them
automatically rebuild those sorts of pages will be an addition I'll be working on.

```php
$build = new \JeremyHarris\Build\Build('/path/to/site_target', '/path/to/build_target');
// force rebuild
$build->build(true);
```

Or, in the case of my little CLI tool: `$ php build.php -s site/ -b build/ -f`

I'd also like to add support for multiple layouts, althought I don't personally
have a need for it at the moment.

I'll probably discover more changes and improvements to make down the road as I
use it more.

## Straight PHP

It's been a long while since I coded straight PHP, and with the standards set
today and the advancements in the PHP language itself, it's really a pleasant
experience.

## League Packages

I've used a couple of [The PHP League's][4] packages for this project. I have to
say, CLImate was a fun one to use. CommonMark not as much. I struggled with the
lack of documentation in trying to add a custom parser. I suppose I'll jump back
in and try again at some point. It'll be nice to add some more parsers so I can
shortcut things like icons, and point all external links to a new tab.

I've used other League packages in other projects, and they are definitely worth
checking out if you haven't yet.

[1]: https://github.com/jeremyharris/build
[2]: https://github.com/jeremyharris/someguyjeremy
[3]: https://github.com/thephpleague/climate
[4]: http://thephpleague.com/
