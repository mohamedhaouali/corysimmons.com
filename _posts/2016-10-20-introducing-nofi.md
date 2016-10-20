---
layout: post
title: Introducing npm Online-First Installer
categories: node
---

[https://github.com/corysimmons/nofi](https://github.com/corysimmons/nofi)

**TL;DR** Working with npm offline is terrible. nofi makes it slightly less terrible until Yarn has some time to grow.

For the past several months, I've been living in the woods -- when I say woods, I mean it's a 30-minute drive to the library with flaky wifi, and an hour drive to a college with consistent wifi.

As a web-developer, you would think this would be pretty terrible, but it's actually kind of nice. I drive into town in the morning, work throughout the day, download some packages & docs, then go home where there are no distractions to learn.

I've never learned as fast in my entire life.

The one big problem I've had is being able to download npm packages for offline use. Where some languages encourage replication (e.g. Python and [Bandersnatch](https://pypi.python.org/pypi/bandersnatch)), this seems to be npm's [entire business model](https://www.npmjs.com/package/npmo).

People have tried to figure out a solution to this. Addy has [a post](https://addyosmani.com/blog/using-npm-offline/) on the subject that made me hopeful, but after experimenting with all of these solutions, I was left frustrated.

Most of the solutions he presents are unmaintained, incomplete, and they are all riddled with bugs.

I would set them up according to their docs, download some packages, go offline, go to install them, and without fail, every so often there would be some dependency hell issue that prevented me from playing with a package I was studying... I'd troubleshoot and try alternate solutions. Nothing actually worked.

Since nothing works, our only option is to develop (see: wait for a team of JS gurus to develop) some amazing package that will dethrone npm. [Yarn](https://github.com/yarnpkg/yarn) is that package manager.

It's fast, auto shrinkwraps things (creates a lockfile so you're not fighting with merge conflicts every time a co-worker runs `npm install`), and purports to offer good offline support. If it doesn't, it will, the contributors behind it are really efficient. There are a plethora of other cool features to it, but those are the only three I'm particularly interested in.

The problem is, Yarn is huge and new. This means it's going to be full of bugs for a while. I tried installing a package that depended on a `.bin` CLI. The CLI wasn't there. Another night of not being able to tinker with toys. Another tool that doesn't help me.

At the recommendation of a few IRC'ers and an un-maintenance notice on [local-npm](https://github.com/nolanlawson/local-npm#unmaintained-notice), I just started using the simplest solution I could: npm itself.

`npm install --cache-min Infinity` will install from cache (located in `~/.npm` by default). It actually worked better than Addy's post would lead you to believe. It was at least very easy to reason about. As Addy also mentioned, it was pretty brutal to type that every time so I aliased it.

I wasn't using the offline tool enough to remember what it was doing, or when/why I needed to use it. I would catch myself using it in hopes of caching npm packages. Just stupid crap making me stupider because it's a stupid problem I didn't want to be dealing with.

So I made a tool to help me not have to think about it all the time.

Enter **nofi** (npm Online-First Installer).

nofi is an insanely simple wrapper around `npm install`. It works like this:

0. `nofi -D some-package`
0. Check if user is online.
  0. Online:  `npm install --force some-package`
  0. Offline: `npm install --cache-min Infinity --save-dev some-package`

It does this with a helpful message to ensure you know if you're install TO cache, or installing FROM cache (respective to the order above).

It works surprisingly well. It's _at least_ easy to reason about.

The only problem is it gets into that dependency hell thing where if you're offline and try to install XYZ package, it will try to install ABC package at a version you don't have.

Entering rambling territory...

There are a couple solutions to this I might implement after a few more "Dammit! I want to play with XYZ tonight!" moments, or if Yarn screws the pooch on their offline support.

_One_ is to have nofi compare all versions of the cached package's `package.json` dependencies to versions you have on your computer, then install that version of the the requested package. Pseudocode for offline installation might look like this:

0. `nofi PackageA`
0. Collect **all versions** of PackageA's `package.json`s.
0. Map over the **dependencies** in those `package.json`s, testing to see if they match cached packages at the versions specified.
  0. Add failures to an array to filter against.
    0. Continue to operate on until the PackageA packages left standing are all compatible with cached dependencies.
    0. Install the freshest out of that group.

The _other_ big "solution" is to just use nofi all the time. After a while, you'll have a pretty resilient collection of cached packages. Heck, we could even add a `--slllow` flag in there to download every version of every dependency, but the pseudocode solution is probably a much better way to go about it.
