# Why I Hate WordPress

I use a lot of different technologies. I'm not an Apply guy, but I own a MacBook Pro. I'm not a PC guy, but I've used one for most my life and still do for work. I have an Android phone, and a Zune music player. I didn't go to school for programming and my programming is mostly limited to web technologies. I've programmed in BASIC, ASP, ActionScript, JavaScript, PHP, and more. I've used frameworks, and coded from scratch. I say this to show you that I tend not to be a fanboy of any one thing, or a hater of any one thing. I believe there is a right tool for every job.

Yet I am confident in saying that I absolutely loath WordPress.

It's really unfortunate that I'm stuck using it in certain cases. It's even more unfortunate that it has such a high market share and that people are still flocking to it as if it is a good solution for their CMS. Non-programmers see a pretty backend and are astonished at its great and awe-inspiring magnificence. WordPress "programmers" look at it and see the myriad of filters, the nice looking documentation, and plethora of (mostly useless - I'll get to this later) plugins and are captivated by the sheer power of the applications they can charge way too much for. I look at it and weep uncontrollably. On the inside, of course. As a man, I don't cry.

A while ago, I received a site that I was told to maintain. I've worked with bad code before, as well all have (after all, we all write better code than the next person just like we're all better drivers than everyone else). This was an affront to even half decent WordPress sites. For anonymity, let's call the programming firm that delivered this atrocity "HardCoderz."

Here's a quick breakdown of the site:

1. 26 Plugins - It had plugins for things as simple as a breadcrumb. Not only that, but only two (count it,*two*) were written by HardCoderz. To make things worse, much of the functionality of the site relied on these plugins. If the original author didn't update, it was now up to me to maintain their crap.
2. Parent Theme - The site is a child theme of yet another author's parent theme. Not only that, but the site doesn't actually use much of the parent theme's functionality.
3. HARDCODE ALL THE THINGS - I'm pretty sure this was HardCoderz' mantra.
4. Inconsistent - We're all inconsistent, but I mean, come on. Spacing? HTML as strings or after closed PHP tags? Namespace functions or not? Use brackets or not?
5. Debugging - This is a really obscure term in programming. If you haven't heard of it, it's where you check to make sure your program works, and clear out the kinks. HardCoderz didn't know about this rare, new aspect to programming. I'm not talking unit testing here, that's for super elite programmers. I'm just talking turning `WP_DEBUG` on. I turned it on and it crippled the site.
6. No version control - Sweeeeeeeeeeet.

I'll be using this site as an example during this series. It's important to note, though, that I don't hate WordPress because of this site. This site is just another example of the culture that WordPress has cultivated, I feel.


### WordPress <3's absolutes

HardCoderz delivered the site to a development server we had. They told us to create all of the content on the development site and that we could move it to the production server fairly easily. After creating all of the content, they sent me instructions for moving the site. I wish I was lying, but here are the instructions:

> Open the SQL file, and do a search and replace for http://dev.example.com and replace with http://example.com

I saw this and thought it was odd that WordPress would store absolute URIs in the database. What if you wanted to move the site. Or what if you wanted to work on it locally instead of pushing (read: FTPing) every change to the production server? WordPress doesn't care. It stores everything in the database as a hard and fast, never-gonna-change, value. I'm not talking links in content, which make sense to store as fully qualified links, I'm talking everything from the site url to the home page link to the domains in a multisite install.

Since this was my first real experience with WordPress I just ran with it. Upon actually running their complex migration scripts, it killed portions of the site. Why?

### Serialized data


WordPress likes to store serialized data. Like, a lot of it. Just visit `/wp-admin/options.php` for a sampling. When you edit serialized data, it breaks it (unless you get real serious and edit the keys that dictate the string lengths, etc.). The particular part of the site that broke were widgets, since they and their options are serialized.

### Culture

Remember when I said that turning on `WP_DEBUG` killed the site? Well, I first chalked it up to being HardCoderz' fault. After looking through the numerous errors, I realized it didn't stop with them. Plugins were throwing errors left and right.

Now, did this have anything to do with WordPress itself? Well, in a way. WordPress has developed a culture of non-programmers who think they are programmers. This isn't all that bad, if you teach them good practices. But WordPress encourages [unreadable code](http://codex.wordpress.org/WordPress_Coding_Standards)(see Brace Style, Space Usage, and Yoda Conditions for some), in my opinion. These non-programmers actually tout themselves as programmers and it sells. Then they deliver a barely cohesive piece of dribble that is unmaintainable and is 80% not their own.

I browsed through all of the plugins and found zero tests. I tried to find repos to see if they had tests, but to no avail.

One could argue that PHP itself has created a culture of lazy, non-testing programmers as well. But WordPress has made it all too easy to use their code.

### Testing

The WordPress core has tests, yes. But they don't provide an easy way to test your code. This makes it hard for developers to write tests for their code. For one, you have to replace your`wp-config.php`file. If you forget to, and run your tests, say bye bye to your data. Mocking actions and filters looks to be a huge pain. The WordPress test library doesn't even have any tests that test a theme with existing actions and filters! What happened to lead by example?

Oh, and have you looked at their test library. It's pretty lacking. Something like 40 cases for the hundreds of files that make up the WordPress core. And they also tend to[push changes without really testing anyway](http://kevinjohngallagher.com/2012/01/wordpress-has-left-the-building/).

What's more is that the test cases are stored outside of your theme or plugin, and mixed with the core tests.

### Permissions

WordPress [actually suggests](http://codex.wordpress.org/Hardening_WordPress) that your `/wp-content` folder should be "completely writable by all users (owner/user, group, and public)."

That terrible suggestion aside, there's a fun little quirk I encountered when I was testing safer permissions. When WordPress uploads a file, it does something different (read: stupid). Try to follow this: it checks the user id of the file that checks permissions and compares it with the user id of folder you are uploading to. It does this in`/wp-admin/includes/file.php` in the `get_filesystem_method()` function.

### Debugging

As far as I can tell, there is no native debugging in the WordPress core. This explains why HardCoderz included this exhaustive debugging functionality in their`functions.php`file. Disclaimer: the following is actual code.


```php
function pring_r( $input ) {
  echo _pring_r( $input );
}
function _pring_r( $input ) {
  return "".print_r( $input, true )."";
}

    function echo_r( $input ) {
  echo _echo_r( $input );
}
function _echo_r( $input ) {
  return "".$input."";
}
```


With no debug functionality and a community of non-programmers creating plugins, this is a pretty standard find for me. I wrote my own debug function, which was easy enough - stack traces, who'd have thought! Later, I finally found the[Debug Bar](http://wordpress.org/extend/plugins/debug-bar/stats/)plugin, which looks[pretty familiar](https://github.com/cakephp/debug_kit).

### Globals

WordPress is the king of globals. There are entire files dedicated to creating global functions. I've unwittingly killed the site before by simply assigning a variable and overwriting a global. What's weird about this is that a lot of the WordPress core is made up of classes, but there is a 4,500 line file dedicated to making it "convenient" for users by creating a huge number of global functions that access these classes. This means that every time a new function is added to the core, a global function to call it is needed.

### Theming

While theming has improved, there's still a lot to be desired. I'm a fan of the[MVC](http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller)architecture, because it works pretty dang well. I'm not saying that it's the only way, but at least a layout-view structure would be nice. I don't like having to include the header and footer in each new template. WordPress finally got on the ball and included "partial template" loading with`get_template_part()`, which is nice.

When you create a child theme, however, the child's`functions.php`file is loaded*before*the parent theme's. This seems backwards. How is the child supposed to be inheriting the parent? It turns out, child themes aren't really children at all. They just have access to the parent theme's functions and templates. Oh yeah, the templates are loaded in the opposite way - parent first, then child.
### To Echo or Not to Echo

So many, many of the WordPress functions auto-echo your content. Things like `the_content()` and `the_permalink()` just spit it out. Those examples specifically have partner functions that don't echo, such as `get_the_content()`. Some even have a key you can include in the arguments to prevent echoing, instead! It's so consistent it makes me want to be a better person.

For those special functions that don't have a partner`get_whatever()`function, well, I guess you'll just have to swallow the output buffer on your own.

### Switching Blogs

The site I'm using as an example here is a multi-site, that is, it has many sites within the same network. This allows me to pull content from other sites. I recently rewrote all of the sites, save the main one. During this rewrite I wrote a function that aggregated posts from all of the different blogs if they were "pushed" to other sites. This allowed us to create content on one site and share it with another.

Included in WordPress is a function for switching blogs, which basically just switches the tables it checks. Since the tables are inconsistently named (i.e., the first site stores posts in `wp_posts` and the next in `wp_2_posts`) I decided to use it. For some reason, tags, categories and permalinks were still referencing the original site. After looking into it I found that WordPress doesn't fully switch. It turns out that there's a [two year old ticket](http://core.trac.wordpress.org/ticket/12040) explaining why this is so.

### Plugins

Plugins are great. I use them in all of my apps. The quality of the plugin is hugely important to me as a developer, especially if my data is relying on it. The last thing I want is a rogue plugin to open a security hole or delete my content. In my endeavors to build a better WordPress site, I've spent many an hour on Google trying to find answers to what should be fairly common questions. I would say that about 90% of the time, the answer is "Just use this plugin." If I followed that rule, I would probably have about 100 plugins each running 1 or 2 lines of code. The way WordPress bootstraps the plugins causes this to slow the site to a crawl.

Without tests, it's hard for me to trust a plugin. In my redesign process, I included 2 plugins - neither of which will cause the site to fail if they don't exist. They simply enhance. Everything else has been built into the template.

When I read through some of the code for the plugins that HardCoderz chose, it makes me a sad panda. I guess this goes back to the culture of "programmers" that WordPress has created.

### Database Clutter

WordPress clutters the database in a few different ways. The first is by inserting empty posts. Each time you click "Add New" on a post type, WordPress actually creates a database record whether you save it or not. Go ahead, click "Add New" under Posts a hundred times. You now have a hundred more empty rows in your database.

Revisions are another place where WordPress drops the ball. Revisions are cool, but they are endless as well. This means that over a few years of content editing, you could end up with thousands of revisions that you will never, ever use.

### The End of a Rant

The secret is out (that is, if you don't follow me on Twitter): I unabashedly hate WordPress. I think it's all for good reason. From a developer's perspective, it's absolutely awful. This isn't even a complete list of everything I've found that has caused me hours of frustration, and I'm sure it won't end.

I'll leave you with this snippet of an email I sent to my supervisor after spending an hour trying to figure out why WordPress was deciding to delete the custom templates we used on certain pages, and use the default instead:

> To fully appreciate how much a pain in the ass WordPress is, you need to be awarded the pleasure of reading its source code. By pleasure, I mean the same kind of pleasure that comes from sticking scorching metal tubing into your eye socket and pouring salt, lemon juice, and finally iodine - so not to get infected later - down it.

