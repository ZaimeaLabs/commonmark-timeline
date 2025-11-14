---
title: How to use package
description: How to use package
github: https://github.com/zaimealabs/commonmark-timeline/edit/main/docs/
---

# CommonMark Timeline Usage

[[TOC]]

## Usage

Register the extension:

```php
use League\CommonMark\Environment\Environment;
use League\CommonMark\CommonMarkConverter;
use Zaimea\CommonMark\Timeline\TimelineExtension;

$environment = new Environment([]);
$environment->addExtension(new TimelineExtension());

$converter = new CommonMarkConverter([], $environment);
echo $converter->convert($markdown);

```

## Syntax

```md
::: timeline
### Step One
This is some text.

### Step Two
More content here.
:::
```
Output Example (simplified)
```html
<div class="step" id="steps-xxxx">
    <ol class="relative border-s border-gray-200">
        <li class="mb-10 ms-4">
            <h3 id="steps-xxxx-item-1-title">Step One</h3>
            <div id="steps-xxxx-item-1" role="region" aria-labelledby="steps-xxxx-item-1-title">
                <p>This is some text.</p>
            </div>
        </li>
        ...
    </ol>
</div>
```

## Header Metadata

The first line after `::: steps` is treated as an info string.
```md
::: steps warning Installation Guide
### Step A
...
:::
```
This produces:
 - type = "warning" → CSS class warning
 - Title = Installation Guide → rendered as <h2>

Resulting HTML:
```html
<div class="step warning" id="steps-xxxx">
    <h2 class="step-title mb-4 text-xl font-bold">Installation Guide</h2>
    ...
</div>
```

## Styling (optional)

```css
.step {
    @apply mt-6 mb-6;
}
.step ol {
    @apply relative border-s border-gray-200 dark:border-gray-700;
}
.step li {
    @apply mb-10 ms-4;
}
.step h3 {
    @apply text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2;
}
```

## Example: Full Steps Block

```md
::: steps info Installation
### Download
Get the archive from the website.

### Extract
Unzip the downloaded file.

### Install
Run the installer and follow the instructions.
:::
```
------------------------------------------------------------------------

## Support

For issues or suggestions: [GitHub Issues](https://github.com/zaimealabs/commonmark-timeline/issues)
