=== Add-On for Zoom Registration and Gravity Forms ===
Contributors: apos37, michaelbourne
Tags: gravity forms, zoom, webinar, meeting, registration
Requires at least: 5.0
Requires PHP: 8.0
Tested up to: 6.8
Stable tag: 1.5.2
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

Register attendees in your Zoom Webinar or Zoom Meeting through a Gravity Form.

== Description ==
This plugin adds a "Zoom Registration" feed to your Gravity Forms. Although it was created specifically for the Webinars feature on Zoom, it will also work with regular Meetings.

Updated to work with Server-to-server OAuth.

Originally developed by Michael Bourne as "[Gravity Forms Zoom Webinar Registration](https://github.com/michaelbourne/gravity-forms-zoom-webinar-registration)." This is the same plugin modified for release on the WP Plugin Repository. We had to change the name per guidelines.

== Requirements ==
1. A WordPress.org based website
2. The [Gravity Forms](https://www.gravityforms.com/) plugin
3. A [Zoom](https://zoom.us/) account, Pro plan or higher
4. Recommended: the [Webinar add-on](https://zoom.us/webinar) for your Zoom account
5. A [Server-to-Server OAuth Application](https://marketplace.zoom.us/docs/guides/build/server-to-server-oauth-app/) created for your own Zoom account
6. WordPress version 5+
7. PHP version 8.0+

== Third-Party Services ==
This plugin integrates with the Zoom API to facilitate registration for webinars and meetings. By using this plugin, you acknowledge that certain data will be transmitted to Zoom in order to create and manage webinar registrants.

**Circumstances Under Which Data is Sent:**
When a user submits a registration form, the plugin sends the registrant's information (e.g., name, email) to Zoom to create a new registrant for the specified webinar or meeting.

**Links:**
 • **Zoom API Documentation**: [Zoom API Documentation](https://developers.zoom.us/docs/api/)
 • **Zoom Privacy Policy**: [Zoom Privacy Policy](https://www.zoom.com/en/trust/privacy/)
 • **Zoom Terms of Service**: [Zoom Terms of Service](https://www.zoom.com/en/trust/terms/)

For any concerns regarding data transmission and processing, please refer to the links above to understand how your information is handled by Zoom.

== Installation ==
1. Install the plugin from your website's plugin directory, or upload the plugin to your plugins folder. 
2. Activate it.
3. Go to **Gravity Forms > Settings > Zoom Webinar**.
3. Optional: save and import the `gravity-forms-zoom-registration-sample-form.json` file as a starter form. All required and optional registration fields are included.

== Usage ==
1. After installation, go to **Gravity Forms > Settings > Zoom Webinar**. Enter your [Server OAuth App](https://marketplace.zoom.us/docs/guides/build/server-to-server-oauth-app/) Account ID, Client ID, and Client Secret. These apps are free to create, take only 5 minutes, and don't need to be published. Fill in all three fields and hit Save.
2. Follow the directions on the Zoom API docs carefully. You’ll need to edit roles in Zoom settings and create the app. Your user role and app must have the `meeting:write:admin` and `webinar:write:admin` scopes. If you are having issues, you may need to add the following scopes: `meeting:write:registrant:admin` and `meeting:read:list_meetings:admin`.
3. Ensure the Server-to-Server OAuth App in Zoom is *active* before using this addon.
4. For the form you'd like to use for registrations, go to **Settings > Zoom Webinar**. Add a new feed, give it a name, choose the meeting type, enter your Meeting ID, and match registration fields accordingly. First name, last name, and email are required fields.
5. Enable registrations on your meeting if using that instead of webinars.

*We strongly encourage enabling logging in Gravity Forms settings when testing this add-on.*

== Constants ==
By default, this plugin will ask for your Account ID, Client ID & Secret in the Gravity Forms settings. Users wanting more control can specify these as constants: `GRAVITYZWR_ACCOUNT_ID`, `GRAVITYZWR_CLIENT_ID`, and `GRAVITYZWR_CLIENT_SECRET`.

== Payments ==
This add-on supports delayed payment through the Gravity Forms PayPal add-on. Charge for registrations via PayPal, processing the Zoom Registration feed only upon successful payment. [Read more here.](https://docs.gravityforms.com/setting-up-paypal-payments-standard/)

== Migrating from Old Plugin ==
This plugin uses the same text domain as the other one by Michael Bourne, so all of the settings and webinar feeds that you previously set up will remain. All you need to do is install and activate this plugin while the other one is activated. No need to set everything up again. :)

== Frequently Asked Questions ==
= Does this work with Events or Sessions? =
At this time, compatibility with Zoom Events or Zoom Sessions has not been confirmed. As this is a free plugin with no revenue model, investing in an additional Zoom plan solely for testing isn’t feasible at the moment.

= Can I use one form for multiple Zoom webinars? =
Yes, you can use a single Gravity Form to handle registrations for multiple Zoom webinars by creating a separate feed for each webinar. Each feed can be triggered conditionally based on a form field value. There are two common ways to manage this:
 • **Dropdown Field**: Add a visible dropdown field to the form listing available webinars. Each feed is configured to run only when a specific option is selected.
 • **Hidden Field**: Use a hidden field (e.g., webinar_id) that is dynamically populated—such as via a URL parameter or post meta—based on the page the form is displayed on. Each feed uses this value to determine which webinar to register for.

This allows you to reuse the same form without needing to create a new one for each webinar.

= The registration did not go through, what happened? =
There could be several reasons. You can check for an error in the entry details notes, which should give you a good indication of what happened. Some common reasons include:
 • You didn't actually set up the plugin or a webinar/meeting feed
 • The Account ID, Client ID, and/or Client Secret are incorrect
 • Incorrect scopes set
 • A field is required on your Zoom registration form but not mapped on the plugin feed
 • The webinar or meeting with the ID you entered does not exist
 • The feed is set to a webinar when it should be a meeting or vice versa
 • The webinar/meeting host can not register
 • User's email cannot register for the same webinar/meeting more than 3 times in 24 hours

= How do I retrieve the join link URL after registration? =
A Join Link field has been added to the Zoom Webinar feed registation fields section. If you want the join link to populate into a hidden field on the entry, then just choose the field you want from your form.

= How do I map custom questions? =
You can map custom questions to your Zoom registration by using a simple code snippet in your website’s theme. This allows you to include additional fields beyond the default ones. To do this, you’ll need to add a small piece of code to your theme’s `functions.php` file. In the example below, we’re adding a custom question called “Referral Source.” The **name** in the code must exactly match the field name used in your Zoom registration. Once this is added, the “Referral Source” field will be included in your Gravity Forms Zoom Webinar feed settings. Here’s the code you need to add:

`add_filter( 'gravityzwr_registration_fields', function( $fields ) {  
    $fields[ 'referral_source' ] = [  
        'type'     => 'string',  
        'name'     => 'Referral Source',  
        'required' => false,  
    ];  
    return $fields;  
} );`

= Where can I request features and get further support? =
We recommend using our [website support forum](https://pluginrx.com/support/plugin/gravity-zwr/) as the primary method for requesting features and getting help. You can also reach out via our [Discord support server](https://discord.gg/3HnzNEJVnR) or the [WordPress.org support forum](https://wordpress.org/support/plugin/gravity-zwr/), but please note that WordPress.org doesn’t always notify us of new posts, so it’s not ideal for time-sensitive issues.

== Screenshots ==
1. Plugin settings
2. Form feed settings
3. Entry note on successful registration
4. Entry note on failed registration

== Changelog ==
= 1.5.2 =
* Update: Added optional occurence ID (props @sflwa)
* Fix: Undefined variables when missing OAuth credentials

= 1.5.1 =
* Update: Added an action hook after successful registration (props @codegeekatx)

= 1.5.0 =
* Update: New support links

= 1.4.1 =
* Update: Updated author name and website again per WordPress trademark policy

= 1.4.0 =
* Update: Added support for join link to be populated into an entry field (feature request by venzee)
* Update: Added support for custom questions with `gravityzwr_registration_fields` hook (feature request by lsterling03)

= 1.3.5 =
* Update: Changed author name from Apos37 to WordPress Enhanced, new Author URI
* Tweak: Optimization

= 1.3.4.1 =
* Update: Add additional scopes to readme
* Fix: Fatal error on get_body(); update error when there is no response body (reported by nathwl)

= 1.3.4 =
* Update: Added default meeting type field to plugin settings (props @sflwa for suggestion)

= 1.3.3.1 =
* Fix: Address country not working on webinars (reported by peter_04347)

= 1.3.2 =
* Initial release to WP Plugin Repository
* Update: Added a note for when it is successful as well
* Update: Updated error note to include only the body code and message instead of the whole array
* Update: Added optional form json file to settings page for easy download
* Tweak: Change form settings icon
* Tweak: Changed name due to WP repo and GF guidelines
* Fix: IDs from settings not caching causing a bad request