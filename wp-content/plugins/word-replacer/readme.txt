=== Word Replacer ===
Contributors: takien
Donate link: http://takien.com/donate
Tags: replacer, post, comment, page, replace, censor, bbcode, filter, bbpress
Requires at least: 3.1
Tested up to: 3.9.2
Stable tag: 0.4
License: GPLv2 or later

Replace word by another word in post, page, or comment. And... bbPress

== Description ==

Word Replacer is a Wordpress plugin to replace any texts or words with another. You can filter which content to be replaced, eg. only in page, comment, or post. With very userfriendly administration page you can manage list of word easily. It's also can be used to censor any bad words in your comments. And... bbPress :)

= Features =
1. With regex support.
2. Userfriendly administration page.
3. Define yourself what word to replace in where. (e.g. a word shoud be replaced in comment but not in post etc)

== Installation ==

The installation process.

1. Upload  to the `/wp-content/plugins/` directory. Or Directly upload from your Plugin management page.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings menu and you will see Word Replacer sub menu. 

== Frequently Asked Questions ==
-

== Screenshots ==

1. Settings page where you can add/remove your words.

== Changelog ==

= 0.4 =
* Fixed: Suppress error `PHP Warning:  Missing argument 2 for WordReplacer::word_replacer_comment()` if comment disabled.

= 0.3.3 =
* Fixed: If original word contains `[` or `]` and regex not checked cause loop replacement

= 0.3.2 =
* Fixed: error on WordPress 3.6 caused by deprecated function `$wpdb->escape`

= 0.3.1 =
* Fix typo

= 0.3 =
* Added: Now supported bbPress
* Added: Option to strip backslash during update words ( if necessary )
* Improved code

= 0.2.3 =
* Changed: now use Class.
* Changed: replacement field is now textarea with autoresize.
* Fixed: form submitted twice (bug in version 0.2.2)
* Fixed: Initial field is back (was lost in version 0.2.2).
* Added: filter priority, so it can override many other filter plugins.
* Added: checkbox to easy delete multiple rows.
* Improve: few code improvement.

= 0.2.2 =
* Fixed: array_diff error.
* Changed: submit button name.
* Added: nonce check.

= 0.2.1 =
* Changed: original and replacement field in database is now TEXT type instead of VARCHAR
* Fixed: plugins will analyze first whether original value in databse is base64 encoded (bug in version 0.2, when upgrade from version 0.1)


= 0.2 = 
* Regex support
* Some bug fixes
* Changed: Now using preg_replace PHP functions, instead of str_replace
* Added: Replace title and page title
* Added: Search whole word only
* Added: Search case insensitive
* Added: Contextual help
* Changed: Original words saved to the database is now base64 encoded, to keep character consistency. 
* Added: Expand/collapse options page
* Removed: initial word value (badword/good word)

= 0.1 =
* First release

== Upgrade Notice ==
* None