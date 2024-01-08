# CSSParser

A PHP class for parsing and transforming CSS within HTML markup.

## Introduction

The `CSSParser` class is designed to parse HTML markup and dynamically apply CSS styles based on various configuration files. It allows for the creation and customization of styles for different elements, classes, and utilities, providing a flexible and efficient way to manage styles in HTML documents.

## Features

- **Modular Configuration:** The class utilizes multiple configuration files for different aspects of CSS, allowing easy customization and extension.
- **Dynamic Styling:** Parses HTML markup and dynamically adds CSS styles based on specified configurations.
- **Media Queries:** Generates media queries for different resolutions, enabling responsive design.

## Usage

1. **Instantiate the CSSParser:**
    ```php
    $cssParser = new CSSParser();
    ```

2. **Parse HTML Markup:**
    ```php
    $htmlMarkup = '<html>...</html>';
    $modifiedMarkup = $cssParser->parse($htmlMarkup);
    ```

3. **Customization:**
    - Modify configuration files (`config.ini`, `root.ini`, `elements.ini`, `custom.ini`, `utilities.ini`, `resolutions.ini`) to tailor the styling behavior.

## Configuration Files

- `config.ini`: General configuration settings.
- `root.ini`: :root styles, applied globally.
- `elements.ini`: Element-specific styles.
- `custom.ini`: Custom class styles.
- `utilities.ini`: Styles applied based on utility attributes.
- `resolutions.ini`: Resolutions and corresponding media query ranges.

## Dependencies

- **PHP:** Requires PHP 7.0 or later.
- **DOMDocument:** Utilizes the DOMDocument class for HTML parsing.

## Error Handling

The class throws a `\RuntimeException` if parsing any configuration file fails. It is essential to handle this exception appropriately in your application.

## Example

```php
$cssParser = new CSSParser();
$htmlMarkup = '<html>...</html>';
$modifiedMarkup = $cssParser->parse($htmlMarkup);
echo $modifiedMarkup;

#License

This project is licensed under the MIT License.

#Acknowledgments

Feel free to contribute, report issues, or suggest improvements!
