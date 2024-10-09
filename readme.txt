=== Add-On for Zoom Registration and Gravity Forms ===
Contributors: apos37, michaelbourne
Tags: gravity forms, zoom, webinar, meeting, registration
Requires at least: 5.0.0
Requires PHP: 8.0
Tested up to: 6.6.2
Stable tag: 1.3.1
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

Register attendees in your Zoom Webinar or Zoom Meeting through a Gravity Form.

== Description ==

This plugin adds a "Zoom Registration" feed to your Gravity Forms. Although it was created specifically for the Webinars feature on Zoom, it will also work with regular Meetings.

Updated to work with Server-to-server OAuth.

Originally developed by Michael Bourne as "Gravity Forms Zoom Webinar Registration." This is the same plugin modified for release on the WP Plugin Repository. We had to change the name per guidelines.

== Requirements ==

1. A WordPress.org based website
2. The [Gravity Forms](https://www.gravityforms.com/) plugin
3. A [Zoom](https://zoom.us/) account, Pro plan or higher
4. Recommended: the [Webinar add-on](https://zoom.us/webinar) for your Zoom account
5. A [Server-to-Server OAuth Application](https://marketplace.zoom.us/docs/guides/build/server-to-server-oauth-app/) created for your own Zoom account
6. WordPress version 5+
7. PHP version 8.0+

== Installation ==

1. Install the plugin from your website's plugin directory, or upload the plugin to your plugins folder. 
2. Activate it.
3. Go to **Gravity Forms > Settings > Zoom Webinar**.
3. Optional: save and import the `gravity-forms-zoom-registration-sample-form.json` file as a starter form. All required and optional registration fields are included.

== Usage ==

1. After installation, go to **Gravity Forms > Settings > Zoom Webinar**. Enter your [Server OAuth App](https://marketplace.zoom.us/docs/guides/build/server-to-server-oauth-app/) Account ID, Client ID, and Client Secret. These apps are free to create, take only 5 minutes, and don't need to be published. Fill in all three fields and hit Save.
2. Follow the directions on the Zoom API docs carefully. You’ll need to edit roles in Zoom settings and create the app. Your user role and app must have the `meeting:write:admin` and `webinar:write:admin` scopes.
3. Ensure the Server-to-Server OAuth App in Zoom is *active* before using this addon.
4. For the form you'd like to use for registrations, go to **Settings > Zoom Webinar**. Add a new feed, give it a name, choose the meeting type, enter your Meeting ID, and match registration fields accordingly. First name, last name, and email are required fields.
5. Enable registrations on your meeting if using that instead of webinars.

*We strongly encourage enabling logging in Gravity Forms settings when testing this add-on.*

== Constants ==

By default, this plugin will ask for your Account ID, Client ID & Secret in the Gravity Forms settings. Users wanting more control can specify these as constants: `GRAVITYZWR_ACCOUNT_ID`, `GRAVITYZWR_CLIENT_ID`, and `GRAVITYZWR_CLIENT_SECRET`.

== Payments ==

This add-on supports delayed payment through the Gravity Forms PayPal add-on. Charge for registrations via PayPal, processing the Zoom Registration feed only upon successful payment. [Read more here.](https://docs.gravityforms.com/setting-up-paypal-payments-standard/)

== Support ==

= Where can I request features and get further support? =
Join my [Discord support server](https://discord.gg/3HnzNEJVnR)

== Screenshots ==
1. Plugin settings
2. Form feed settings
3. Entry note on successful registration
4. Entry note on failed registration

== Changelog ==
= 1.3.1 =
* Initial release to WP Plugin Repository
* Update: Added a note for when it is successful as well
* Update: Updated error note to include only the body code and message instead of the whole array
* Update: Added optional form json file to settings page for easy download
* Tweak: Change form settings icon
* Tweak: Changed name due to WP repo and GF guidelines
* Fix: IDs from settings not caching causing a bad request