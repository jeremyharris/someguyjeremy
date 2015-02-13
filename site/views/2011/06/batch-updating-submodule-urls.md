# Batch Updating Submodule URLs

Since I have moved all of my plugins to Github, there's obviously some updating that needs to be done on my CakePHP apps that use those plugins as git submodules. Namely, I needed to replace the old repo urls with the new ones. To do this, I first opened up my `.gitmodules` file using vim.

```
$ vim .gitmodules
```

Then just some regexp happiness from command mode.

```
:%s/git\(:\/\/\|@\)codaset\.com\(:\|\/\)jeremyharris\//git:\/\/github\.com\/jeremyharris\//g
```

It looks like a lot, but it's really nothing special (just a lot of character escaping). It looks for instances of my old Codaset repo urls and replaces them with the read-only Github ones. Just to note, it looks for my Codaset read+write ssh urls as well as the regular read-only http ones. Technically this isn't the cleanest regexp (no word boundaries, matches all combinations of `:|@` and `:|/`), but because of the nature of the contents of the file there's really no need.

Finally, I had named some of my plugins using `-` as a space delimeter rather than `_`. To make it easier to clone modules into Cake apps, I've renamed them using `_` in Github. To fix this in my `.gitmodules` file, I ran

```
:g/jeremyharris/s/-/_/
```

This command looks for lines containing my name and replaces `-` with `_`. Then just save using `:wq`.

After that, simply sync the new repo urls with git.

```
$ git submodule sync
```