=== Contact Form 7 - Show Page ===
Contributors: ugene, sachyya-sachet
Donate link: #
Tags: Contact Form 7, form, forms, contactform7, contact form, find contact form, show page contact form 7, where contact form
Requires at least: 4.5
Tested up to: 4.9
Stable tag: 1.0.3
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

A simple WordPress plugin that helps you to know which contact forms are used in the site.


== Description ==

You don't need this plugin but hey, you might need this to quickly check where the contact forms are used. If you have good memory then you will good without it. But there is chance to forget it right?

So here below are the scenarios where you will find this plugin helpful:
1. You have multiple contact forms used in various pages and posts or may be used in widgets. To edit the one you like meaning find it first. This plugin will help you showing the post/page name with a link to view or edit that post or page. For the widget case, it will tell you whether the current form is used in the widget or not.
2. This is quite useful in this condition. Lets say you are a developer and you've come across the site where there are multiple contact forms. You might have to clean it up. This plugin will help you find and know if the contact form is used or not, meaning you are on your own far from the fear of mistakenly deleting the used one forms.

By default this plugin only supports the search in default post types (post/page). But you can extend the search in custom post types of your own further by using a quick filter hook like below:

`function extend_posttype_support ( $support ) {
	return array( 'book', 'movie' );
}
add_filter( 'wpcf7sp_support_posttypes', 'extend_posttype_support' );`


== Usages ==

Backend

	1. After installing the plugin, you will find a CF7 Show pages menu under each contact form edit page.
	2. No settings, just view where the form is used. It wil list the name of post, pages in the table with a link to view and edit.
	3. For other custom post types, filter is provided.


== Notes ==

1. [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) must be activated.


== Installation ==

1. In your admin panel, go to Appearance > Plugins and click the Add New button.
2. Click Upload Theme and Choose File, then select the theme's .zip file. Click Install Now.
3. Click Activate to use your new theme right away.


== Frequently Asked Questions ==

= Do I have to activate contact form 7 plugin? =

Yes, this plugin is the extension to the famous Contact Form 7 plugin and thus Contact Form 7 is must.


== Screenshots ==

1. Overview


== Changelog ==

= 1.0.3 Dec 4 2018 =
* Upgrade: Search on **Custom HTML**  widget too.
* Refactor code
* Add TGM Plugin Activation PHP library 

= 1.0.2 =
* Add message when post types or widgets do not use the Contact Form.

= 1.0.1 =
* Extend plugin to search in widgets too but works only on **Text Widget** for now.
* Remove default private post types.
* Improve filter by checking for string and array only.

= 1.0 =
* Initial Release