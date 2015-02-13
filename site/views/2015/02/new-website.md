# New Website

It's been a very long time since I've posted anything new on my site, let alone
actually update it. The infrastructure service that was hosting my old site was
going through some changes which gave me a nice excuse to completely rebuild my
site.

I had very few requirements:

1. I wanted to write in markdown. I'm tired of shitty WYSIWYG editors.
2. Static is okay, I don't need a full-blown CMS
3. I wanted hosting to be cheap

I was looking at using Github pages to host and build using Jekyll, but I didn't
want to learn another system, and if I can avoid doing anything Ruby related I'm
generally happier. (No offense to Ruby people, I just rarely have luck setting
things up.)

A CMS is nice for many things, but I don't really care about tagging, I have
very little content, I'm the only contributor, and it adds a lot of overhead
I just don't need. It's also a bit harder to deploy.

So like any good programmer I decided to reinvent the wheel and write my own
static site generator and host it on AWS S3.

It turned out to be a fun little project. It was good to write some pure PHP
and JavaScript again.

Hopefully now that I can write in markdown (er, [CommonMark](http://commonmark.org/)),
I'll be more motivated to write about what I'm doing.

Full source for this project (and site) is available on Github:
[https://github.com/jeremyharris/someguyjeremy](https://github.com/jeremyharris/someguyjeremy)

I'm going to add some deploy code to it as well, so I don't have to leave my
precious console ever again.

P.S. I managed to create a little script to migrate my old posts into markdown,
but if you find anything wrong please tweet at my face or submit a PR.