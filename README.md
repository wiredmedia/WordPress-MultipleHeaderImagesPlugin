# WordPress Multiple Header Images Plugin

Allows user to select multiple header images to associate with a post, falling back to a set of default header images (Appearance -> Headers), failing that, reverting to the regular WordPress header image.

Add header_images() to your template (header.php?) to output a simple unordered list of images which you can then use with your favourite image slider, get_header_images() returns the URL's as an array if you wish to craft your own markup, get_header_images() accepts an optional post_id, header_images() accepts an array of options.

## Options

 - post_id: the id of the post, default is current post
 - class: the class name to apply to your unordered list
 - size: passed through to thumbnailer (untested)
 - width: html
 - height: html

## Disclaimer

This plugin is far from completion, use at your own risk, it's pretty basic, pull requests encouraged.
