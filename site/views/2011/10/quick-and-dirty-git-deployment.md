# Quick and Dirty Git Deployment

I decided I hated the way were were "deploying" at work. The guy before me never really had a deployment strategy (at least none that he left behind), let alone version controlled or tested software. I decided to take a crack at creating a simple Git-only deployment system. Keep in mind that this doesn't provide any quality control, such as running tests or setting permissions. It's just something that skips the FTP business and allows us to roll back changes if something goes awry.

For this example, I'll be using Git as our VCS and will be setting it up on an Apache server via SSH and assuming you have some knowledge in them. The initial 3 steps are one-time only setups.

### Step 0: Git user

We'll want to create a user to handle all Gitish things on the server. If you have an existing web group, we'll want to add the user to that group as well. SSH into your server and create the user.

```
$ sudo adduser -g web -p mypassword git
```

Where "web" is your web group and "git" is the name of the user. Please for all that is good in this world, choose a strong password and don't use the example one. Now that we have a git user, close the current SSH session and start a new one as git.

### Step 1: Password-less login

The next step is to get us SSHing in without having to enter a password. If you're using Git and a remote host, then you already have a private SSH key (found in your `~/.ssh` folder). If you don't, generate one thusly:

```
$ cd ~/.ssh
$ ssh-keygen -t rsa -C "email@example.com"
```

This will generate a private (`id_rsa`) and public (`id_rsa.pub`) key.
*Note: If you're on a Windows box using PuTTY as your SSH client, make sure to download [PuTTYgen](http://www.chiark.greenend.org.uk/~sgtatham/putty/download.html). PuTTY doesn't like keys unless they're generated with its generator, so open it up, load up your key and generate a PuTTY-fied one to use instead. Since PuTTY uses a different extension for its keys, there's no duplicate filename issue to worry about.*

Now copy the private key to the git user's ssh folder using secure copy (or copy and paste in your SSH client, whatever floats your boat). Remember, if you're using PuTTY use the `.ppk` file instead of the extensionless one.

```
$ mkdir ~/.ssh
$ scp ~/.ssh/id_rsa ~/.ssh/authorized_keys
```

Now that you've authorized your current local computer to have access to the server using the private key, try closing the session and restarting it (as the git user still). You should just automatically log in.

### Step 2: Install git

Using whatever package manager you have on the server (we have `yum`), install git.

```
$ yum install git
```

### Step 3: Make a git repo

Now onto the good stuff. What we'll do here is create a bare Git repository in our user folder. We're going to point it's working directory to the webroot. This is important because we don't want to have the `.git` folder in our webroot, otherwise someone could enumerate the files and have our entire sourcecode. Not good. So let's start by creating a bare repo. For example purposes, we're creating a site called "example" and its webroot will live in `/var/www/html/example.com`. Since you need to make the folder beforehand (Git won't do it for you), it's included here.

```
$ mkdir /var/www/html/example.com
$ mkdir ~/example.git
$ cd ~/example.git
$ git init --bare
```

Cool, a bare Git repo. Now, some Git configuration. First, let's tell Git that it's not indeed a bare repo and we have a working tree somewhere. We init'd it as one so all the Git information would be stored in that folder, rather than `.git`. We'll also set a config setting to allow us to have an out of sync working tree and HEAD (which we fix later).

```
$ git config core.bare false
$ git config core.worktree /var/www/html/example.com
$ git config receive.denycurrentbranch ignore
```

Now the post-receive hook. This is a little Git hook that automatically checks out the latest commit. I'm going to use vim as my editor. Use whatever you like:

```
$ vim -c start hooks/post-receive
 #!/bin/sh
read OLDREV NEWREV REFNAME
WORKTREE=`git config core.worktree`
GITDIR=`pwd` 

# checkout current commit
umask 002 && git reset --hard

# checkout submodules (you must be in the worktree to do this)
cd $WORKTREE
git --git-dir="$GITDIR" submodule update --init
git --git-dir="$GITDIR" submodule foreach git reset --hard

# fix permissions
FILES=`git --git-dir=${GITDIR} diff ${OLDREV}..${NEWREV} --name-only --diff-filter=ACMRTUXB`
for F in $FILES; do
    chown git:www-data "$F"
done
```

Let me break it down. In the first section we read some variables in from STDIN, as documented on the [git website](http://git-scm.com/book/ch7-3.html). We then set the worktree to whatever we defined it as earlier in the git config file, and set a variable for the git directory. Now that we've got all our paths and revisions ready to go, we checkout the current commit by resetting the worktree to HEAD.

To update submodules, we need to be in the top level of the working tree. Since we're storing the git database elsewhere, all we need to do is tell git where the git directory is after cd'ing into the work tree.
The next part is an awesome [answer](http://stackoverflow.com/a/9621213/724063) to a question I found on StackOverflow. When git checks out code, it does it as that user. This is normally fine, but you're not generally pushing as the web server's user, so the web server will lose permissions to those files. In previous iterations of this hook, I've reset permissions to the entire worktree as a "fix," but this is a much more elegant solution. It grabs all files that have changed between the revision we were on and where we're at, and changes the permissions for just them. The `--diff-filter` value is everything except D, that is, deleted files. No use trying to change permissions on files that no longer exist.

After that, all you need to do is enable it by making it executable.

```
$ chmod +x hooks/post-receive
```

You can copy this hook when creating new repos on the server, since it's not specific to any one repo.

### Step 4: Push to production!

Finally, let's set up our remote on our local machine!

```
$ git remote add production ssh://git@example.com/~/example.git
$ git push production master
```

There you have it. A pretty simple git deployment setup. For security, I suggest restricting your SSH port if you haven't already and making sure your site has proper web permissions. Now when things go wrong, your production server is all ready to be rolled back.