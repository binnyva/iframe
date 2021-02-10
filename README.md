# iFrame
## 2.0.6

iFrame is a lightweight PHP framework.

```
<?php
require __DIR__.'/vendor/autoload.php';
$app = new iframe\App;

$message = "Hello World!";

$app->render();
```

## The Basics

iFrame is an MVC framework - with a twist. The controller is the file that is called. To understand this better, lets go thru the above code. Its called from this URL - `example.com/hello.php`

In this case, `hello.php` is the controller. The `$app->render()` will do these things...

1. Include the `<app folder>templates\layout\page.php` file(which provides the general layout)
2. Include the view for this specific login file - which will be `<app folder>templates\hello.php`
3. Include `<app folder>\assets\css\hello.css`
4. Include `<app folder>\assets\js\hello.js` 
5. Place the view output within the general layout.

The system will guess the file path of the template file based on controller file path. You can change the template file path if you wish.

## Installation

Use [composer](https://getcomposer.org/doc/00-intro.md) to install iFrame

```
$ composer require binnyva/iframe
```

If you are starting a new project with iframe, its recommened to install the skeleton project...

```
$ composer create-project binnyva/iframe-skeleton [my-app-name]
```

This will automatically install iframe and setup the required folder structure. 

## Warning!

Don't use this framework unless you know what you are doing. It has been developed with a few specific use cases in mind. There are better frameworks around - please use one of them...

- [Laravel](https://laravel.com/)
- [Symphony](https://symfony.com/)
- [Slim](https://slimframework.com)