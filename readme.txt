=== Plugin Name ===
Contributors: jperelli
Tags: newsletter, wp-cron, user_meta
Requires at least: 3.0.1
Tested up to: 3.4
Stable tag: 0.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Customizable autosended (wp-cron) newsletters for each user, using PHP directly on the newsletter (thought to use user_meta).

== Description ==

**Features:**

1. Setup multiple newsletters.
2. Different send modes include manual, diary, weekly and monthly.
3. Custom newsletter content, template using php directly and all wp functions.
4. $user (newsletter receiver) object available in template.
5. User can configure to receive newsletter in it's profile edit page.
6. Uses wp_cron and wp_mail, no extra configuration on the server is needed.

== Installation ==

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==

1. Campaigns List page
2. Add new Campaign page
3. Profile edit page newsletter options
The first two options are from the newsletter plugin.
The other two were added using the hook 'personal_newsletter_edit_user_profile'

== Changelog ==

= 0.0.2 =
* Check the user selected frequency

= 0.0.1 =
* First release

== Repository access ==

The repo is in github, thanx to incodex.com
[Repository link](http://github.com/incodex/personal-newsletter "wp-personal-newsletter repository")
