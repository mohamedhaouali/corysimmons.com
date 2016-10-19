---
layout: post
title: Introducing Package Script Manager
categories: node
---

[https://github.com/corysimmons/package-script-manager](https://github.com/corysimmons/package-script-manager)

I had been using npm scripts for a lot of little things, but never really saw it as a build tool until I read [Keith Cirkel's article](https://www.keithcirkel.co.uk/how-to-use-npm-as-a-build-tool/) on it.

There are some huge underlying points to that approach that never got the attention they deserved:

1. This isn't about npm or Make or XYZ. It's about CLIs. CLIs are simply the most portable interface for a library. You can *easily* wrap CLIs for use with _any_ task runner. You can even use CLIs without any sort of task runner.
2. You can run npm tasks in a particular order, and/or in parallel with [npm-run-all](https://www.npmjs.com/package/npm-run-all).
3. You can watch any glob pattern for a variety of events using [Chokidar CLI](https://github.com/kimmobrunfeldt/chokidar-cli). It also makes a few bash variables available `{event}` and `{path}`. You can use bash functions with that event, and operate on that path using tools like [cut](https://linux.die.net/man/1/cut).

Those last two points were very common/invalid complaints about using npm scripts, but there are still a lot of valid complaints revolving around the use of JSON.

> Let me make this perfectly clear for any aspiring library authors: **STOP USING THINGS LIKE YAML AND JSON FOR CONFIG!** The only reason static content should ever exist is when working with databases or state. Otherwise, assume your userbase isn't full of toddlers and give them a power tool to explore interesting ways to config their projects.

Since npm scripts are tied up in JSON, there are lots of things you can't do with them: comments, variables, etc. In fact, Keith's article's comment section is literally full of reasons why JSON configs suck.

So I made a thing that will import all the key/val pairs from a JS object to package.json `"scripts"`. It does this destructively (any scripts you currently have in package.json will be overwritten) to force a single source of truth for your scripts.

Enter [Package Script Manager](https://github.com/corysimmons/package-script-manager). It's a very simple CLI that destructively migrates a JS object from one file to `package.json`'s `"scripts"` object.

It works like this:

`npm i -D package-script-manager`

```js
// psm.js
const planet = 'Earth'

module.exports = {
  // This task should just echo 'Hello Earth!' to the terminal
  "start": `echo 'Hello ${planet}!'`
}
```

> ❗️ Next part is destructive and will discard whatever scripts you have in package.json. Make sure you migrate your package.json scripts to psm.js, and/or back them up somewhere until you're comfortable with this workflow.

`$ node_modules/.bin/psm` (need to run this from `node_modules/.bin` once to config some new tasks for it)

```js
// package.json
{
  "scripts": {
    "start": "echo 'Hello Earth!'",
    "psm": "psm psm.js package.json",
    "psm:watch": "chokidar psm.js -c 'npm run psm'"
  }
}
```

Now you can use it with `npm run psm` (or `psm:watch`).

Between `npm-run-all`, `chokidar-cli`, and `package-script-manager`, you have all the power of any task runner out there, but the most important part is, now you are supporting CLI development as opposed to Gulp plugin development. Again, one of those things is insanely portable, and the other is specific to a single task runner. And if you're going to write some sort of function for a build task, would you rather do it in a task runner's plugin API, or just in plain Node?

Stop reading here if you like Webpack.

Where do tools like Webpack fit into this? They perform a separate function than task running. They mostly focus on something called "bundling". In very plain/overly-simple terms, bundling just concatenates files (this triggers the React developer), and Webpack isn't particularly good at it when compared to a forward-thinking bundler like Rollup.

Webpack is bad for the internet because it contributes to the JS-everything mindset -- going as far as to import/insert CSS and imagery through JS (people without JS get absolutely nothing) in the name of HTTP request reduction. The minimize requests thing is now an anti-pattern carryover from HTTP1, but I'm already escaping the scope of this post so I'll cover that more in a future post.

I'd suggest dumping Webpack for Rollup; just using it to eliminate useless code (unless you also need Node in the browser); and throwing that task into your npm scripts along with every other optimization task.

This approach, combined with comments, keeps a very clear separation of concerns. And with those few tools mentioned above, you can easily slap together a very easy-to-reason-about/powerful/extensible/portable task config.

```js
// psm.js

// Easily alter your project structure
const distDir = 'dist'
const jsDist = `${distDir}/js`
const bundleName = 'bundle'

module.exports = {
  // Compresses imagery once on startup, then runs other optimizations and the development server in parallel.
  // https://github.com/mysticatea/npm-run-all
  "start": `npm-run-all imagemin -p rollup chokidar:uglify brower-sync`,

  // Losslessly compress imagery https://github.com/imagemin/imagemin-cli
  // We could watch this with Chokidar, but I wanted to demonstrate you can perform tasks sequentially, as well as in parallel, very easily.
  "imagemin": `imagemin src/img/**/* --out-dir=${distDir}`,

  // Tree-shake JS https://medium.com/@Rich_Harris/tree-shaking-versus-dead-code-elimination-d3765df85c80
  // It will default to use the config in rollup.config.js (see? some people know JSON configs suck)
  "rollup": `rollup -c --watch -o ${jsDist}/${bundleName}.js`,

  // Uglify/minify JS https://github.com/mishoo/UglifyJS
  "uglify": `uglifyjs ${jsDist}/${bundleName}.js -o ${jsDist}/${bundleName}.js`,

  // Let's use Chokidar and run uglify everytime our JS bundle changes https://github.com/kimmobrunfeldt/chokidar-cli
  "chokidar:uglify": `chokidar ${jsDist}/${bundleName}.js -c 'npm run uglifyjs'`,

  // Fire up a really powerful dev server https://www.browsersync.io/docs/command-line
  // Trigger more React devs: I hear Webpack's HMR is overrated from people who write books about Webpack.
  "browser-sync": `browser-sync start --server --no-open --no-notify --files=${distDir}/**/*`
}
```

This is untested pseudocode, but I'm pretty sure the biggest hiccup would be a typo or something.

Think about that for a second. I'm compressing all types of imagery; tree-shaking my JS; watching tree-shook code and uglifying it on change; and spinning up a dev server; with 6 lines of very readable, documented, code.

Gulp & Co. are dead. Long live CLIs.
