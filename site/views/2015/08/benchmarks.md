# Benchmarks

Everyone of us sees lots of benchmarks come through our feeds, claiming
this framework or library is faster than this other one. My personal
favorite are "hello world" benchmarks, which try to show you which thing
is fastest using the minimal overhead. I recently [saw one][1] that
showed how my personal framework of choice, CakePHP, is faster at Hello
World than the very popular Laravel framework.

Benchmarks are useful, in my opinion, to gauge the difference in speed
between a framework at version X and Y, or to compare differences in
speed between your application at point X and Y. Comparing frameworks
seems silly to me, because there are always ways to use the framework
to make it faster.

This post is aimed to make that point.

In a recent little conversation with @dereuromark, I mentioned I could
make the "Hello World" benchmark mentioned in the blog post above even
faster by skipping the conroller layer entirely. This is just one of
those cases where a benchmark, especially one that simply spits out a
HTTP response, is a silly way to gauge the speed of frameworks. I can
also skew the results because I know the framework pretty well.

[So I did.][2]

## Setup

Let me preface this by saying that **this is not a professionally done
benchmark**. I didn't see the point in doing that, since the overall
point of this project was not to see how fast Cake can be, but I can
tweak it to make even "Hello World" benchmarks faster.

Let me also say that, like many benchmarks, the real world use of an
application configured this way is unlikely.

I first created a CakePHP app using `composer create-project cakephp/app`.
I kept all the intial setup from the installed project. I used the same
`HelloWorldController` that the blog post did. Then I created my faster
method, a dispatch filter that returns the response.

My computer is a POS, specifically:

- 2010 Macbook Pro (2.4 GHz Intel Core i5)
- 8 GB RAM

These numbers are probably pointless anyway.

To benchmark, I did some basic Apache Benchmarks:

`ab -c 5 -n 1000 -k http://bench.local/hello_world`

The paths tested were:

- `/hello_world`: A hello world sent from a controller
- `/hello_dispatch`: A hello world sent from a dispatch filter

## Results

The results showed my version, `/hello_dispatch`, getting 30 more
requests per second. There. Faster.

### `/hello_world`

```
Time taken for tests:   3.953 seconds
Complete requests:      1000
Failed requests:        0
Keep-Alive requests:    993
Total transferred:      265685 bytes
HTML transferred:       12000 bytes
Requests per second:    252.97 [#/sec] (mean)
Time per request:       19.765 [ms] (mean)
Time per request:       3.953 [ms] (mean, across all concurrent
requests)
Transfer rate:          65.64 [Kbytes/sec] received
```

### `/hello_dispatch`

```
Time taken for tests:   3.562 seconds
Complete requests:      1000
Failed requests:        0
Keep-Alive requests:    993
Total transferred:      265685 bytes
HTML transferred:       12000 bytes
Requests per second:    280.74 [#/sec] (mean)
Time per request:       17.810 [ms] (mean)
Time per request:       3.562 [ms] (mean, across all concurrent
requests)
Transfer rate:          72.84 [Kbytes/sec] received
```

## Even Faster

While I kept the basic settings, I could continue to make micro
optimizations to make it appear like Cake is even *faster*. But would
those applications actually be useful? Probably not, just as a large
scale application without some sort of templating wouldn't be very
useful.

## Conclusion

I hope this exercise shines a little light on all of these benchmarks
and how they can always be twisted and skewed. I'll continue using
CakePHP to build amazing applications quickly, when it's appropriate to
use a framework. Benchmarks will continue to pass by your desk, but take
them with a grain of salt. The Great Framework Wars will also continue,
but choosing something you enjoy working with is what I would suggest.
Hell, people still complain about PHP being slow, but like anything, it
can be sped up.

It's utlimately the up to the knowledge of the developer to increase
speed where possible. There are a lot of moving parts in web
applications, each with their own set of optimizations.

If you're making a choice on what framework to build your application
on, or whether even to use a framework, do a little research. Program a
simple application yourself. There are more factors that should go into
your decision than what some dude like me on some random website like
mine *said*.

[1]: http://blog.a-way-out.net/blog/2015/03/27/php-framework-benchmark/
[2]: https://github.com/jeremyharris/cakephp-dumb-benchmark
