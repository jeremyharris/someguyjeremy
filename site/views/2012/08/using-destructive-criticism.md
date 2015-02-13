# Using Destructive Criticism

Growing up, I was never really criticized for my work. I was never really praised, either. Sure, I was criticized for other things, like any other kid, but never for the work I did (save the necessary *corrections *to what was broken). During the past few years, though, I've had an inordinate amount of both thrown my way. This is mainly because of working at a job with more visibility than my last, but also because I've stepped into the OSS universe which is made up of people on the internet, and we all know how they are.

### Some Backstory

Here at [**ROCK**HARBOR](http://www.rockharbor.org), we have a member management web app called CORE. The original iteration was created before my time here. This was a big step moving away from managing a congregation of thousands using spreadsheets spread across personal computers and file servers, to a single one stop shop. It even had a user interface that non-staff could use to sign up for events! This was brilliant, how could anyone hate moving towards this?

Well, they did. Bigtime. I was originally brought on for parttime bug maintenance. It turned into a fulltime job. During my first year or so, I gathered pretty quickly that most staffers didn't like using the software. The code, running on CakePHP 1.1, wasn't in a state where it could be upgraded. With this in mind, we decided a complete rewrite was in order. I spent the better part of my time here rewriting the beast into something that I *knew *staff would love. The code was solid, the tests were in order, the design was purdy. Then it came time to launch.

After launching, we encountered the usual bumps and bruises. The app suffered from slowness (play data !== real, actual data) and some crashing here and there. The next few weeks I did nothing but fix bugs, sift through emails, and take calls from unhappy customers. I began to get overwhelmed with the aura of unpleasant feelings over the new CORE. I took it personally. I wrote this thing, from *nothing*to *something*. I did it right - it had test cases, I tested it as much as I could (though I would have really liked some more usability tests). People were legitimately angry. (I should point out that the entire staff was extremely supportive, even in their frustrations. I know that some of them still brood over it, but they never take it out on me. I've never met a group of people who were unanimously able to appreciate something they didn't understand.)

### Anger Leads To Hate

A big problem with this whole situation was that people hated CORE to begin with. They were already judging the new one, not fully understanding what was behind it. I actually had one person call me and **tell me**to revert to the previous version!I'm generally a nice guy, so my responses to the nonstop flowing emails and bug reports were polite. Inside, though, the software became my bane. I started to become angry and hate my own work. I started to believe that maybe I didn't know what I was doing at all, which, admittedly, I kind of don't and kind of do. This is programming, after all.

After the turmultuous weeks passed, I gathered myself and decided that I could use the criticism as a motivator rather than letting it crush my spirits.
### A Different Way Of Thinking

I started looking at the negative critique differently. In the open source world, when someone reports a bug I see it as a good thing. If the bug is reported, that means it's no longer invisibly affecting apps. You can *fix it*. Fixing bugs is a great feeling. Committing them into the repo and seeing appreciation from users is a great feeling.

I decided to take the criticism as a simple observation instead. This took away some of the sting, and let me approach the problem without pesky emotions getting in the way. I constantly reminded myself that I was doing this for the users.
> I hate CORE! It's way too slow to be useful!

My first reaction to something like this used to be to brush it off.
> I'm just one guy, I don't have a ton of experience building massive web apps, I'm not a performance tuner, premature optimization is the root of all evil, etc. Yeah it's slower than we'd like, but it's still usable. Also you probably still harbor negative feelings leftover from the first version.

Instead of responding like this, I reread the issue. I cut out the hatred, removed the exaggerations, and here's what it began to look like:

> CORE is slow.

Now this I can work with. It's slow. Okay, let's optimize some queries, create better indexes, add some caching, compress some assets. Bingo, much faster than before. Wow, *much *faster. This is awesome! I wonder if I can make it even faster...

### The Emotional Gain

If I responded using my original way of thinking, I still would have sped up the app. But I wouldn't have been happy about it. I would have thought "fine, it's fast now. Happy?" There are two problems with this. First, I'm not excited about the improvement I made. This is huge, because staying excited about programming is an important key to growing and become better at it. I also would have left it at that, instead of continually trying to improve it. Second, I'm bitter towards the user. No good can come from this. If you hate your users, why are you writing an app for users?

Using my new method, though, I now become excited about speeding it up and keep working on improving the speed. During this process, I learn new methods and improve my skills in the ones I already knew. I've become better. And what about the user? Well, they're going to be stoked that it's fast now!

### Becoming Better

Criticism can be a great way to self-improve. It's important not to look at destructive criticism as destructive. It's sometimes hard to separate out the negativity, but if you look at it with [cold, logical](http://www.imdb.com/title/tt0584452/quotes?qt=qt0318507) eyes, it becomes a problem to solve. Programmers love solving problems. Solving difficult problems is even better, because you have to research how to fix it. Research leads to learning. Learning leads to becoming better.

Now, not all critcism can be turned into a problem to solve. Some people are just hateful, cruel, and ready to attack. To that, I say, haters gonna hate.

![haters gonna hate](https://gimmebar-assets.s3.amazonaws.com/4e92a2019e2b1.gif)