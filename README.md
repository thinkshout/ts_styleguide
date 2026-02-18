# ThinkShout Styleguide
Extremely simple module to load a styleguide page.

All opinions about the styleguide are part of the twig template in 
the theme, which distinguishes it from other styleguide drupal modules.

## Adding custom CSS/JS to the styleguide
If you create a library in your theme (THEMENAME.library.yml) called
`ts_styleguide`, it will be automatically loaded on your styleguide page.

Example:
```yaml
ts_styleguide:
  css:
    theme:
      assets/css/styleguide.css: {}
```