---
layout: post
title: Building a Simple Typechecker
---

Type-checking is pretty sweet and helps prevent lots of hard-to-find bugs. If you're using [default named params]({% post_url 2016-10-11-default-named-parameter-functions-for-everything %}) for all your functions, it's fairly easy to integrate some simple type-checking.

A real-world example might make this more clear!

Frankenstein (the doctor -- not the monster) has some cats. Some of them have less legs than they are supposed to have. He can add more legs, but he developed some compulsive disorder and can only add legs if he's allowed to double them. As @mpjme would say, this is the kind of problem you encounter every day in Enterprise-level Coding™.

```js
const typeCheck = (interface, argsObj) => {
  for (const param in interface) {
    if (typeof argsObj[param] !== interface[param]) // Comparing the arguments to the type interface.
      throw new TypeError(`${argsObj[param]} should be a ${interface[param]} type!`)
  }
}

// Type interface
const kittenTyping = {
  nickname: 'string',
  legs: 'number'
}

const frankenKitten = ({nickname = 'Hops', legs = 3}) => {
  typeCheck(kittenTyping, {nickname, legs}) // interface, argsObj
  console.log(`*sewing sounds* 🙀  Now ${nickname} has ${legs * 2} legs! 😻`)
}

// Lets saw a leg off so Hops ends up with a normal amount of legs (4), then let's rename him Skip.
frankenKitten({nickname: 'Skip', legs: '2'}) // TypeError: 2 should be a number type!
frankenKitten({legs: 2}) // Ah, that's better!
```

Now we're catching type bugs that otherwise would've leaked through since clever JavaScript will "intelligently" (aka: magically) convert that `'2'` string into a number during the multiplication step.

> **Protip:** Place `typeCheck()` right above `return` statements to help prevent bugs. If you encounter a bug, just add `typeCheck()`s higher and higher in your code until you find the culprit (then clean up after yourself except for that one right above your `return`). Psst, the culprit usually looks like `Object.<anonymous>` in the terminal stack trace.

This isn't great because it requires us to pass all the interface params to `argsObj` whether we're using them or not.

```js
// Hops is still a good name!
frankenKitten({legs: 2})

// ...

typeCheck(kittenTyping, {legs}) // Error! We always need to pass whatever params match in the function defaults and the interface.
typeCheck(kittenTyping, {nickname, legs}) // Works, but smells a bit.
```

I'm sure there's some clever way to capture deconstructed variables without having to explicitly pass _all_ of them within `typeCheck()`, but tbh, this solution isn't as nice as existing tools and I'm getting hungry.

It **is**, however, a pretty good way to start sneaking basic type-checking into stuff without introducing any dependencies.

It also educates and promotes the discussion of integrating one of those more feature-rich solutions. Coworkers should begin to see the value in type-checking, but probably feel kind of gross about all the `typeCheck()` functions littering your code, or they'll want to typecheck arrays and such...

That's the _purrfect_ time to sell something like Flow/TypeScript/Elm. 😽
