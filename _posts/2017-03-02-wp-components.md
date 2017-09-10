---
layout: post
title: WP Components
categories: php
---

> **Update:** Updated codebase for this approach [https://github.com/corysimmons/wp-comp](https://github.com/corysimmons/wp-comp)

[Jump to the code](#introducing-wp-components)

I've recently been working on a WordPress project where the client wanted to reuse existing content in different contexts. For instance, a blog feed listing that might appear on its own page, but would also appear on the homepage.

It seemed like I could just duplicate code and that would work fine. Then I realized they wanted to reuse content everywhere.

Wordpress' Loop is pretty nasty. From the Codex:

```php
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
  <?php the_title(); ?>
<?php endwhile; else : ?>
  <p><?php _e( 'Sorry, no posts matched your criteria.' ); ?></p>
<?php endif; ?>
```

This got duplicated all over the place.

Then I realized I was reusing a lot of markup and styles as well. Perhaps a post listing was identical in two places: same markup, same styles, but maybe we just want to return 5 posts instead of 10.

Does this stuff really require duplicating code?

## Component-based Architecture

I set out to solve this problem, and I've really been itching to implement a [component-based architecture](https://www.youtube.com/watch?v=m0oMHG6ZXvo). I really like the idea of keeping all aspects of a component isolated to a single folder rather than organizing by something arbitrary like file extension.

So this:

```
- index.php
- page.php
- includes
  - slider.php
  - listing.php
- css
  - slider.css
  - listing.css
- js
  - slider.js
  - listing.js
```

Becomes this:

```
- index.php
- page.php
- components
  - slider
    - slider.php
    - slider.css
    - slider.js
  - listing
    - listing.php
    - listing.css
    - listing.js
```

This architecture's benefits are many. All code related to a component is easily found, and isolated, in the same directory. If you've ever had to migrate from a theme built using a framework (like Bootstrap) to a framework (like Foundation) then you're probably very familiar with the problem of not just being able to delete parts of the old framework as you implement those same parts in the new framework. Component architecture solves this problem.

Say you need to upgrade the slider component. First you could create:

```
- components
  - slider-dev
    - slider-dev.php
    - slider-dev.css
    - slider-dev.js
```

When `slider-dev` looks good, just delete the entire `components/slider` directory then rename `slider-dev` to `slider`.

You can read more about this architecture in "[Enduring CSS](http://ecss.io/chapter5.html)".

## Jake Archibald's Performance Trick

Jake is on Google's fancy web performance team. His job consists of sitting around figuring out neat ways to make sites load faster.

Last year [he wrote](https://jakearchibald.com/2016/link-in-body/) about putting CSS `<link>` tags immediately above their components rather than in `<head>` for a quick perf win.

This technique goes perfectly with component-based architecture.

It looks something like this:

```html
<html>
  <head></head>
  <body>

    <link rel="stylesheet" href="components/slider/slider.css">
    <div class="slider">
      <img src="img/slide-1.jpg">
      <img src="img/slide-2.jpg">
      <img src="img/slide-3.jpg">
    </div>
    <script src="components/slider/slider.js"></script>

    <link rel="stylesheet" href="components/listing/listing.css">
    <div class="listing">
      <a href="#1">Post 1</a>
      <a href="#2">Post 2</a>
      <a href="#3">Post 3</a>
    </div>
    <script src="components/listing/listing.js"></script>

  </body>
</html>
```

People rightfully worried the assets might be loaded multiple times on the same page if the component was loaded multiple times on the same page.

With my approach, the assets are only loaded the first time they are requested on a page so this is a non-issue.

## Obfuscating to WP_Query

The entire concept rests on the fact you can query almost anything in WordPress via the aptly named [WP_Query](https://codex.wordpress.org/Class_Reference/WP_Query) class.

But, like The Loop, WP_Query looks bloated and terrible. From the Codex:

```php
<?php

// The Query
$the_query = new WP_Query( $args );

// The Loop
if ( $the_query->have_posts() ) {
  echo '<ul>';
  while ( $the_query->have_posts() ) {
    $the_query->the_post();
    echo '<li>' . get_the_title() . '</li>';
  }
  echo '</ul>';
  /* Restore original Post Data */
  wp_reset_postdata();
} else {
  // no posts found
}
```

Even cleaned-up, with the ability to mix markup and real args, it looks like this:

```php
<?php
  $query = new WP_Query([
    'author' => 123
  ]);
?>

<?php if ($query->have_posts() ) : ?>
  <ul>
    <?php while ($query->have_posts()) : ?>
      <?php $query->the_post(); ?>
      <li><?= get_the_title(); ?></li>
    <?php endwhile; ?>
  </ul>

  <?php wp_reset_postdata(); ?>
<?php else : ?>
  <p>No posts found.</p>
<?php endif; ?>
```

This is why people hate PHP.

## Introducing WP Components

**Step 1:** Put the following in your `functions.php` file (at some point in the future I'll probably turn this into a real WP "plugin").

```php
$loaded_components = [];

function component($component, $args, $e = null) {
  global $loaded_components;

  ob_start();

  if (!in_array($component, $loaded_components)) {
    // TODO: Test to see if these files exist. Users might not need CSS and/or JS.
    echo '<link rel="stylesheet" href="' . get_template_directory_uri() . '/components/' . $component . '/' . $component . '.css">';
  }

  $c = new WP_Query($args); if ($c->have_posts()) {
    require 'components/' . $component . '/' . $component . '.php';
    wp_reset_postdata();
  } else {
    require 'components/error.php';
  }

  if (!in_array($component, $loaded_components)) {
    echo '<script src="' . get_template_directory_uri() . '/components/' . $component . '/' . $component . '.js"></script>';
  }

  echo ob_get_clean();

  $loaded_components[] = $component;
}
```

**Step 2:** Create a `theme/components/slider` directory with `slider.php`, `slider.css`, and `slider.js` inside of it.

**Step 3:** Insert the following anywhere in your views (use a valid $WP_Query array):

```php
<?= component('slider', ['category_name' => 'slide']); ?>
```

You can reuse this anywhere with slightly different context:

```php
<?= component('slider', ['category_name' => 'slide']); ?>
<br>
<?=
  component('slider', [
    'category_name' => 'slide',
    'post_count' => 3
  ]);
?>
```

## Extra Context

*"What if I want the same $WP_Query but slightly different markup, styles, or scripts?"*

The third parameter to the `component()` function is an array of custom extra context.

It is passed to the components `.php` file only. But with it, you can modify markup directly, CSS via classes, and JS via classes or `data` attributes.

```php
<?=
  component('slider', [
    'category_name' => 'slide',
    'post_count' => 3
  ], [
    'title' => 'Slider with only 3 slides'
    'classes' => [
      'slider--short',
      'slide'
    ]
  ]);
?>
```

Then in `theme/components/slider/slider.php` you might do something like this:

`$e` is short for "extra" (as in extra context). `$c` is short for "component".

You'll notice we're using The Loop here. It's preferable to use core/pure/PHP WordPress functions (vs something like Timber) as long as you can obfuscate it a bit (less than Timber, but more than duplicating "The Loop" everywhere).

```php
<?php if ($e['title']) : ?>
  <h2><?= $e['title']; ?></h2>
<?php endif; ?>

<ul class="slider <?= $e['classes'][0]; ?>">

  <?php while ($c->have_posts()) : $c->the_post(); ?>

    <li class="<?= $e['classes'][1]; ?>">
      <?= get_the_title(); ?>
    </li>

  <?php endwhile; ?>

</ul>
```

So the output would be:

```html
<link rel="stylesheet" href="components/slider/slider.css"> <!-- asset paths resolve via get_template_directory_uri() -->

<h2>Slider with only 3 slides</h2>

<ul class="slider slider--short">
  <li class="slide">
    Slide Title 1
  </li>
  <li class="slide">
    Slide Title 2
  </li>
  <li class="slide">
    Slide Title 3
  </li>
</ul>

<script src="components/slider/slider.js"></script>
```

## Conclusion

Benefits:

- You can substantially clean up your views.
- You can reuse content to provide multiple paths to the same endpoints throughout your design -- in a very clean/easy/intuitive way.
- Components are organized and easy to modify/upgrade, debug, and remove.
- Obfuscates The Loop without throwing away the WP core API or forcing you to rely on a templating language (e.g. Twig).

Tips:

- Isolate the usage of this technique to **components only**.
- Work **with** WP's core templating system, not against it. For instance, create `page.php` as a generic template for all pages and then sprinkle the WP Components technique on top of it (e.g. a slider component that should appear on every page).
- Ensure your JS waits until everything is ready before firing. If you have 2 sliders on the same page, the first slider will load JS immediately (before the 2nd slider's markup exists in the DOM).

Todo:

- Test to see if these files exist. Users might not need CSS and/or JS.
- Allow users to pass PHP vars to JS and CSS (probably via .js.php and .css.php extensions).
- Convert to a WP plugin.
