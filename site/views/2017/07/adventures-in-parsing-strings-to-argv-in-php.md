# Adventures in Parsing Strings to `argv` in PHP

I was recently working on a [new feature][1] for CakePHP when I stumbled into an
interesting problem.

The feature is a new test case class
that allows easy testing of shells. One of my original goals when writing this
was to allow developers to pass the same command string they would type into
the CLI much in the same way Cake's `IntegrationTestCase` lets you pass the
same string you would type into a browser. In the end, I wanted it to look
like this:

```php
$this->exec('my_shell command_name positional-arg --option=value');
```

At first glance the problem seems simple. Turns out, it is **not**.

## First Attempts

My first attempts at this were simple. In fact, they were downright silly. I'd
split the string by spaces. After all, that's how the above example
looks. Then I remembered that spaces are certainly allowed in `argv` pieces,
such as:

```
echo 'this should be a single arg'
```

## RegExp to the Rescue!

If anything could tackle this challenge, surely it would be the supernatural magic of
`/Regular Expressions/`. They're powerful and there's a multitude of existing
solutions out there already. I dug around Stack Overflow and tested a couple of
the better looking answers. Some of them parsed simple strings like the above
fantastically.

Wait, how about parsing something like `--sarcasm='air "quotes"'`. Quotes in quotes
broke most of the solutions. My adventure then took me to [regex101][2], my go-to
RegExp testing tool. It has a library with a plethora of community provided
regular expressions.

> Would you say I have a plethora of [command line parsing regexps]?
>
>   &mdash; <cite>El Guapo</cite>


No, [not really][3].

Now, I'm not even close to a regular expression master. I have not traveled into
nature to pontificate upon positive lookbehinds. But I took it as a challenge and
an opportunity to learn more, so I dove in and gave it a shot. I got really
close (I regret not saving the expression anywhere). A few groups and some
clever backreferences got me close, until I tried even more complex but
perfectly valid command strings.

```
command --one=two --three="four" 'can I have a "little" more'
```

The above parses into the following `$argv` array in PHP, which my best expression
was just shy of doing:

```php
[
    'command',
    '--one=two',
    '--three=four',
    'can I have a "little" more',
]
```

## Giving Up

After admitting to myself that I couldn't hack it as a Certified Regular Expression
Engineer, I gave up. I thought, *how about just return exactly what PHP gets*?
Surely, that's a foolproof idea. I'd send the command to a little shell that
just returned the exact `$argv` array.

```php
#!/usr/bin/php -q
<?php
echo json_encode($argv);
```

Done! `shell_exec` that sucka and decode the JSON and I'd have exactly what I
needed. This little shell made it into the initial PR. The code review came back:

> What about windows? Will this work there?

My "brilliant" idea disregarded a significant portion of our users (usually not
something that slips my mind). Not to mention it was slow - I had to call an
external shell for each test case. Yuk.

## Asking the Right Questions

Instead of asking "how do I parse a command string into argv", I should have been
asking "how does PHP parse a command string into argv?"

/me smacks self in head

PHP is written in C and as far as I can tell<sup>1</sup> it gets its global `$argv`
variable directly from C itself. So, I looked at the C implementation of how it
parses the command line string. I was expecting some sort of clever wizardry.

It looks like this:

```c
while (*input != EOS)
{
  if (ISSPACE (*input) && !squote && !dquote && !bsquote)
{
  break;
}
  else
{
  if (bsquote)
    {
      bsquote = 0;
      *arg++ = *input;
    }
  else if (*input == '\\')
    {
      bsquote = 1;
    }
  else if (squote)
    {
      if (*input == '\'')
    {
      squote = 0;
    }
      else
    {
      *arg++ = *input;
    }
    }
  else if (dquote)
    {
      if (*input == '"')
    {
      dquote = 0;
    }
      else
    {
      *arg++ = *input;
    }
    }
  else
    {
      if (*input == '\'')
    {
      squote = 1;
    }
      else if (*input == '"')
    {
      dquote = 1;
    }
      else
    {
      *arg++ = *input;
    }
    }
  input++;
}
}
```

Turns out, it was a `while` loop over each and every character with a ton
of `if` and `else if`! Essentially: read the character, make the appropriate
decision. After reading through the code, I came up with the following solution
in PHP:

```php
protected function _commandStringToArgs($command)
{
    $charCount = strlen($command);
    $argv = [];
    $arg = '';
    $inDQuote = false;
    $inSQuote = false;
    for ($i = 0; $i < $charCount; $i++) {
        $char = substr($command, $i, 1);
        if ($char === ' ' && !$inDQuote && !$inSQuote) {
            if (strlen($arg)) {
                $argv[] = $arg;
            }
            $arg = '';
            continue;
        }
        if ($inSQuote && $char === "'") {
            $inSQuote = false;
            continue;
        }
        if ($inDQuote && $char === '"') {
            $inDQuote = false;
            continue;
        }
        if ($char === '"' && !$inSQuote) {
            $inDQuote = true;
            continue;
        }
        if ($char === "'" && !$inDQuote) {
            $inSQuote = true;
            continue;
        }
        $arg .= $char;
    }
    $argv[] = $arg;
    return $argv;
}
```

In the end, this solution, inspired by the C implementation, worked the best. It
also took entirely less time to write than fiddling with what I thought were
better ideas. After all, someone else did the work when they originally
contributed this to C.

<sup>1:</sup> <small>I'm not great at reading PHP source.</small>

[1]: https://github.com/cakephp/cakephp/pull/10816
[2]: https://regex101.com/
[3]: https://regex101.com/library?orderBy=RELEVANCE&search=command%20line