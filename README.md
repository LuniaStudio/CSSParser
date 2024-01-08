# CSSParser

This PHP class reduces download overhead by excluding unused CSS classes.

## Introduction

`CSSParser` analyses an HTML string and identifies the CSS styles being used. It then injects the corresponding classes from your own custom library directly back into the HTML `<styles>` tag. If there is no `<styles>` tag it will create one.

`CSSParser` includes an optional, responsive utility library that provides all the reusable classes you need to build a site using a simple and intuitive syntax. This utility library uses the `data-util` attribute, instead of the standard `class` attribute, in order to keep utilities separate from your own custom classes. You can change the attribute by editing `config.ini`.

## Usage

1. **Instantiate CSSParser:**
    ```php
    $cssParser = new CSSParser();
    ```

2. **Parse HTML:**
    ```php
    $html = '<html>...</html>';
    $modifiedHtml = $cssParser->parse($html);
    ```

3. **Customisation:**
    - Modify configuration files to tailor the styling behaviour.

## Configuration Files

- `config.ini` for general settings.
- `root.ini` for root styles whiach are applied globally.
- `elements.ini` for standard CSS elements (e.g. `div`).
- `custom.ini` for custom classes (e.g. `header-hero`).
- `utilities.ini` for built-in utility attributes (e.g. `row(lc)s+`).
- `resolutions.ini` for media query resolutions.

## Dependencies

- **PHP:** Requires PHP 7.0 or later.
- **DOMDocument:** Uses the `DOMDocument` class for HTML parsing.

## Error Handling

The class throws a `RuntimeException` if parsing any configuration file fails. It is essential to handle this exception appropriately in your application.

## License

This project is licensed under the MIT License.

## Acknowledgments

Feel free to contribute, report issues, or suggest improvements!
