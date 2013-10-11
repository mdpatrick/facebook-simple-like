=== Facebook Simple Like ===
Contributors: mdanielpatrick
Donate link: http://www.mdpatrick.com/donate/
Tags: facebook, like, social media, social, social bookmarking, fan, fan pages
Stable tag: 1.1.0
Requires at least: 3.1.2
Tested up to: 3.6.1

This allows you to create a fan page like button that has none of the fancy stuff normally that exists: stream, profile thumbnail, name. Button only. Enables widget & shortcodes to make the task easier.

== Description ==

Feature request?

**[Consider dropping me a few bucks (donate page)](http://www.mdpatrick.com/donate/)**

.. or ..

**[You can make your own feature and create a pull request on GitHub. This plug is open source.](https://github.com/mdpatrick/facebook-simple-like)**

Want to boost your Facebook fan page subscription rate? This plugin makes what should be an easy task, but isn't, an easy one. It enables you to use a shortcode to place a small like button where ever you like without the clutter: stream, faces, count, and all of the other junk that comes with the "fan page like box" ordinarily. Basically, it generates a fan page subscription button that looks *identical* to the one ordinarily only for *sharing* a page (as opposed to actually subscribing to the page).

= Features =

* Create a like button that resembles the one that normally *shares* the current page (*as opposed to subscribing to fan page*).
* Put the like button where ever you  want using the [facebooksimplelike] short code, or an easy-to-use sidebar or footer theme widget.
* Add the like button to the top or bottom (or both!) of all of your posts and pages with the click of a checkbox on the settings page.
* Create like buttons for as many different fan page destinations as you like. 

== Screenshots ==

1. The simplified Facebook fan page like button (contrast with the ordinary look of the Facebook "like box.")
2. The settings/configuration page for Facebook Simple Like.

== Changelog ==

= 1.0.0 =
* Release

= 1.0.1 =
* Removed the __DIR__ constant to increase reverse compatibility with older versions of PHP. (__DIR__ is PHP 5.3 only. A little too fresh. Oops.)

= 1.0.4 =
* Added a nag notice in the admin area to remind you guys to rate the plugin after you're done installing it.

= 1.1.0 =
* Added support for like buttons in the widgets area.
* You can now automatically add like buttons (for your fan page) to the top or bottom (or both) of all of your posts and pages.
* Added a demo area to the settings page.
* Fixed bug where multiple like buttons caused things to get weird.
* You no longer have to provide the profile id! This makes things MUCH easier.

= 1.1.1 =
* Swapped out file_get_contents() with wp_remote_get(). (Thanks, [nikolov](https://github.com/nikolov-tmw)!)
* CSS file is now more straightforward, defining border color unnecessary.
* Removed some code rendered unnecessary.
* Restructured code to make it somewhat more readable. [Read it and send a PR on GitHub!](https://github.com/mdpatrick/facebook-simple-like)

== Installation ==

1. Download the plugin

2. Extract the contents of facebook-simple-like.zip to wp-content/plugins/ folder. You should get a folder called facebook-simple-like.

3. Activate the Plugin in WP-Admin.

4. Go to your settings area and click the "Facebook Simple Like" settings page link, and enter the url for your fan page then you can use a widget in in your theme, or simply the shortcode: [facebooksimplelike]. It's that simple!

== Frequently Asked Questions ==

= What are the requirements for this plugin? =

A recent version of WordPress. The plugin doesn't do anything too fancy, so it probably runs on older versions too.

= Do you do WordPress consultation work? =

Absolutely! I'm a Zend Certified PHP 5.3 engineer, and can customize or create WordPress plugins and themes. Visit http://www.mdpatrick.com or email me (see support, below) for more details.

= Support =

A contact form is available inside the plugin as well as on my website. You may use it to report bugs, give feedback, or otherwise contact me. I'm also available on [Twitter](http://twitter.com/twitter/) and [Facebook](http://www.facebook.com/pages/mdpatrickcom/154842861208417).
