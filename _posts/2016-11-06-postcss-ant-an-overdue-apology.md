---
layout: post
title: postcss-ant - An Overdue Apology
categories: design
---

[https://github.com/corysimmons/postcss-ant](https://github.com/corysimmons/postcss-ant)

While maintaining Jeet/Lost, I ran into a lot of the same issues from the community:

1. "Is there any way to reduce bloat?"
2. "Is there any way to just return the size?"
3. "Can I combine varying sizes? What about varying sizes of different types?"
4. "Can I override settings on a local level?"

My answer was always something like, "Sorry, but that'd require a complete rewrite of Jeet/Lost."

postcss-ant is that rewrite.

Let me explain these one-by-one. While reading, try to keep in mind almost all grid systems suffer from at least a few of these problems even today.

## "Is there any way to reduce bloat?"

Grid authors attempt to fix common user missteps by providing a layer of fixes for multiple eventualities.

`clearfix` is a great example of this.

Jeet applied `clearfix` liberally so nested grids would always be cleared. But what if the user wasn't nesting grids? They still had mountains of `clearfix` to deal with. It helped users avoid a problem, but at a pretty high cost.

Nowadays we have flexbox, which gets rid of the need for `clearfix`, so postcss-ant's grid generator defaults to flexbox, and if you _do_ still prefer float-based grids, postcss-ant uses a much smaller `clearfix` and only applies it to the grid container element instead of elements with potentially no floated children.

On top of that, postcss-ant's grid generation can produce a lot of sizes depending on your needs, so a lot of measures were taken to combine selectors when possible. This feature isn't perfect, but it's a step in the right direction.

Places left for improvement:

- Cleaning up `calc` formulas. Right now they can get pretty gnarly, pretty fast. Although the formulas work well, it'd be best if they were a bit prettier. I know how to do this, but it'd just be a pretty large undertaking.
- Combining more selectors when possible.

## "Is there any way to just return the size?"

Sometimes people just wanted a size... Possibly so they could make their own grid classes... Possibly for some edge case... Possibly because they were building a space shuttle and just needed a size.

Cory of a few years ago thought this would be a lot harder than it was and never worked on it, but someone along the way submitted a nice PR to Jeet that added this functionality in a really simple mixin.

This functionality still needs added to Lost.

But postcss-ant's entire purpose was to **start** as a size-getting function. After that, I could use those sizes to create whatever helpers I wanted with ease. It worked out pretty nice. `generate-grid` is very powerful, and `ratios()` are unique to postcss-ant, but most importantly, you can always fallback to the size-getter to solve any layout need.

## "Can I combine varying sizes? What about varying sizes of different types?"

This came up so frequently...

I hate this issue because it's not possible (save yourself months and trust me) to have CSS know how many elements are on a row. It will be with some future CSS specs, but by then, Grid Spec will be mainstream and there will be no reason to try to make this kind of thing work.

You just have to use a markup-heavy, negative-margin grid. Simple as that.

I didn't want to face that truth so I never offered this option with Jeet and just kept plugging away at CSS hacks, trying to figure the answer out.

Eventually I gave in and when I made Lost I added negative-margin functionality (aka: "masonry grids"), so users can combine varying sizes all they'd like without having to worry about `nth-child` at all.

postcss-ant also has this negative-margin grid functionality, but where Lost only operated on fractions, postcss-ant can accept a slew of values on any row: valid CSS lengths (fixed units), fractions, and `auto` keyword(s) that span the available space after the first few size types have been subtracted.

## "Can I override settings on a local level?"

With Jeet and Lost, you can, but these settings were wrapped up into a single shorthand function. It was somewhat readable with Jeet (e.g. `column(1/2, $gutter: 10px)`), but I was too excited about the prospect of maintaining a single codebase (instead of 3: SCSS, Stylus, LESS) with PostCSS, that I didn't take the time needed to thoroughly learn PostCSS. I just piled all of these options on top of each other within a single, illegible, shorthand function.

The first part of `lost-column: 1/2` is fairly easy to understand, but what happens if we want `45px` gutters? Now we have to do something like `lost-column: 1/2 2 45px`. As if requiring someone to know what the `45px` was wasn't bad enough, there is some seemingly random cycle argument in there as well.

After learning a bit about the composable nature of functional languages, I really wanted to make postcss-ant's API very easy to read and even "chain".

Imagine you stumbled upon a project and saw this code:

```scss
section {
  generate-grid:
    columns(1/2 1/2)
    gutter(45px)
    technique(negative-margin)
  ;
}
```

Most of it is fairly easy to read, but what about that `technique()` thing? Well, if I picked bad words, or if it is just a concept you've never considered, you're not "up a creek". You can easily jump over to postcss-ant's docs. Click "technique()" and know exactly what it's doing.

I think the ability to start with a really simple, yet powerful, tool and then slowly adding building blocks as you become more comfortable with the concepts, is the zenith of good API design.

## Official Apology

I'm sorry to anyone who used Jeet/Lost and has experienced these problems. I should've been a better maintainer and worked harder to find solutions. I guess I was just poor/busy/burnt out/stupid, and the grids were serving my limited purposes pretty well, so I didn't take your issues as seriously as I should have.

I know these features may be overdue -- especially on the cusp of Grid Spec's reign (still several months from usable folks!) -- but I sincerely hope postcss-ant solves a lot of headaches you have with the current offering of grids -- in particular, mine.