=== Community Watch ===
Contributors: jjeaton
Donate link: http://www.josheaton.org/
Tags: community, content
Requires at least: 3.5.1
Tested up to: 3.6
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows users to report inappropriate content across a site's content types.

== Description ==

Allows users to report inappropriate content across a site's content types. An email is sent to the admin user and the report is logged in the WP Admin under Dashboard > Content Reports.

Administrators can choose which post types to enable the report link and whether the link should appear before or after the content.

== Installation ==

1. Upload the `community-watch` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Enable post types and display options at Settings > Community Watch.
1. View content reports at Dashboard > Content Reports.

== Frequently Asked Questions ==

= Why is there a user with a weird name like 'cwbot@example.com'? =

Don't panic. Your site is fine. The plugin automatically creates a user with Editor privileges to be the author of the content reports. When the plugin is uninstalled, it will remove this user.

= What happens when I uninstall the plugin? =

All options, content reports, and the content report user 'CommunityWatchBot' will be removed from your site.

== Changelog ==

= 1.0.0 =
* Initial Version
