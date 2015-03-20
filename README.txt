=== Emma for WordPress ===
Contributors: ahsodesigns, brettshumaker
Tags: Plugin, Emma, MyEmma, emarketing, form, custom, api, widget, shortcode, subscription
Author URI: http://ahsodesigns.com
Plugin URI: http://www.ahsodesigns.com/products/emma-emarketing-plugin/
Requires at least: 3.0
Tested up to: 4.1.1
Stable tag: 1.1
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

The Emma for Wordpress plugin allows you to quickly and easily add a signup form for your Emma list as a widget or a shortcode on your WordPress 3.0 or higher site.

After the plugin is installed, the setup page will guide you through entering your Emma API login information, selecting your group, setting up the fields for your form, customizing the form, and then adding the Widget to your site. The great news is that this whole process will take less than five minutes and everything can be done via the WordPress Dashboard Setting GUI – no file editing at all!


== Installation ==

**About the plugin:**

The Emma for Wordpress plugin allows you to quickly and easily add a signup form for your Emma list as a widget on your WordPress 3.0 or higher site.

After the plugin is installed, the setup page will guide you through entering your Emma API login information, selecting your group, setting up the fields for your form, customizing the form, and then adding the Widget to your site. The great news is that this whole process will take less than five minutes and everything can be done via the WordPress Dashboard Setting GUI – no file editing at all!

&nbsp;

== Usage Documentation ==

**ACCOUNT INFORMATION TAB**

**Account Login Information:**

Log into your [Emma account](http://myemma.com/login/ "Email Marketing Services - Email Marketing Software - Email Marketing | Emma, Inc.") and click on 'Account & billing' in the upper right hand of your Emma dashboard. This will take you to your “Manage your account settings” page. In the Account settings section, the fourth tab is API key. Click on Generate new key to create your API key.

Once you create the key, you will need to copy your **Account ID**, **Public API Key**, and **Private API** into the corresponding fields in the plugin.

The plugin will now be able to connect your WordPress site to your Emma account.

**Add New Members to Group (optional) –** assign all submissions to a specific group within your Emma account.

&nbsp;

**FORM SETUP TAB**

**Signup ID -** Add this ID to target messages based on the signup form members used to join your audience. Click [here](https://www.google.com/url?q=https%3A%2F%2Fsupport.e2ma.net%2FResource_Center%2FAccount_how-to%2Fcustomizing-your-signup-form%23publish&sa=D&sntz=1&usg=AFQjCNF5iYZYQ9csCvvCibwt6L_zyQr0Ig) for more information.

**Form Fields -** select which fields you would like to display.

**Set Form Width -** set the width of your form.

**Form Placeholders -** customize the placeholder messages within each field.

**Confirmation Messages –** customize the messages that displays under the form after it has been submitted.

**Confirmation Email –** select whether or not to send a confirmation email, then specify the subject and message of the confirmation email.

&nbsp;

**FORM CUSTOMIZATION TAB**

**Form Layout –** select how you would like the form to be displayed on your site.

**Form Fields Customization –** customize the styles of your form fields including border width, border color, border type, text color and background color.

**Submit Button –** customize the styles of your form’s submit button including width, text color, background color, border width, border color and border type.

**Submit Button Hover State Customization –** customize the styles of your form’s submit button when users hover on it.

&nbsp;

**DISPLAYING THE FORM ON YOUR SITE**

To insert the form as a **widget** on your sidebar, go to Appearance -> Widgets and then move the “Emma for Wordpress Subscription Form” to the widget area where you want the form to appear.

To insert the form as a **shortcode** within your site, insert [emma_form] within your text editor where you want the form to appear.

== Screenshots ==

1. This is the Account Information tab of the plugin settings, here you enter your account keys and account number, then select the group you wish to add members to.
2. This is the Form Setup tab of the plugin settings, here you configure the form's output on your site, you can also choose to add a stylish emma logo to your form, and share some love.
3. This is the Form Customization tab of the plugin settings, here you can style your form, choose colors, border types, and so on.
4. This is the Help tab of the plugin settings, it contains instructions on how to get up and running with your new Emma for WordPress Plugin.

== FAQ ==

1. How do I put the form on my website?
   - Once you have activated the plugin, setup your Emma account to work with their new API, and configured your Emma for Wordpress Settings, you have two options for adding the form to your site. 

   -**Widget**
   If you would like to add the form to a widget area, navigate to Appearance->Widgets. This plugin comes with a widget called 'Emma for Wordpress Subscription Form'. The Widgets are listed in alphabetical order. 

   -**Shortcode**
   If you would like to add the form to another area on your site, you can use the Emma shortcode. Simply type [emma_form] in the HTML view in the post editor. For more information on shortcodes, check the almighty codex: [Shortcodes](http://codex.wordpress.org/Shortcode" WordPress Codex - Shortcodes").

2. How do I add members to a specific group?
   - Once you've configured the plugin, and entered your account ID, Private and Public API keys, navigate from the Dashboard to Settings -> Emma for Wordpress. Under the “Account information” tab there is a dropdown menu under “Add New Members to Group.” From here, you can select the group to which new members should be added.

== Upgrade Notice ==

2. This Plugin requires Wordpress version 3.0 and above
3. This Plugin requires PHP version 5.2.6, as it uses json_encode with integers in the Emma_API class

== Changelog ==

- v 1.1 - Updated plugin to work with Emma's newest API. Lots of minor tweaks/updates.

- v 1.0.5 - added confirmation email message, nomenclature updates, Emma_API class fixes, relegated error handling to object making the call, Emma_API fits the adapter pattern better now. more bugfixes.

- v 1.0.4 - bugfixes, updated readme.txt

- v 1.0.3 - fixed accidental php short tag, ( tyty @avioli ), updated readme.txt, spelling errors, and nomenclature updates.

- v 1.0.2 - cleaned up OOP structure. switched to WP naming conventions, fixed bug where users weren't being assigned to groups,

- v 1.0.1 - typed active group_id as integer for uptake to Emma. Emma required group_ids submitted as an array of integers. in older versions of PHP json_encode types integers as strings.

- v 1.0 - it's stable. It needs some cleaning, but it flies, and flies well.
