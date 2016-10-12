---
layout: post
title: Default Named Parameter Functions for Everything
categories: javascript
---

I'm starting to wonder if we should just use named params for everything. They provide a lot of flexibility with no side-effects that I know of.

Here's how you do it:

```js
const foo = ({a = 1, b = 2}) => console.log(`a: ${a}\nb: ${b}`)

foo({})
// a: 1
// b: 2

foo({b: 3})
// a: 1
// b: 3
```

- We don't have to pass any smelly stuff like `foo(undefined, 3)`.
- Our parameter is more descriptive.
- Our parameter is more flexible because it's not bound to any order-of-appearance.

Equal sign when setting defaults. Colon to assign when calling.

Yes, now you have to at least pass an empty object to your functions, and yes, we're forcing people to pass objects at all times, but there's probably an argument to be made about objects being more flexible anyway, and all-in-all, these are small prices to pay for how nice default arguments and named params are.

Named params have always been one of my favorite features of a language. JS doesn't seem to support a super-clean (still have to pass these as objects) way to do this, but at least now we can do it somewhat elegantly.

Now... if only there was a way to type-check those params...
