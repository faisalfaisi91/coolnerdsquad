=== WP Time Slots Booking Form ===
Contributors: codepeople
Donate link: https://wptimeslot.dwbooster.com/download
Tags: booking,calendar,time,slot,form,meeting,appointment,schedule,scheduling,event,reservation
Requires at least: 3.0.5
Tested up to: 5.4
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WP Time Slots Booking Form creates booking forms for booking time slots into the calendar dates.

== Description ==

WP Time Slots Booking Form creates booking forms for booking time slots into the calendar dates.  Basically you can create a form with a calendar where the end-user can pick times into the calendar dates and book them. Notifications emails are sent to let the admin know that a booking was completed and confirmation email can be sent also to the user making the booking.

This type of booking is useful for reserving classrooms, purchasing event tickets, medical / doctors appointments, booking times in escape rooms, booking personal coaching or other professional assistance, booking cleaning services and any other type of service where the customer needs to select a date-time or a group of date-times (time slots) from a set of available times.

In the calendar you can setup:

* The available time slots for each weekday
* The available time slots on specific dates
* The capacity (# of persons that can book) of each time slot 
* The number of different time slots that can be selected in a single booking
* Min and maximum available dates
* Holiday / closed dates
* Price for each time slot
* Prices for different number of selected slots
* Prices for (example) the number of adults and number of children (optional)
* Number of months to display
* ... and other calendar features

In addition to the calendar-related features, other important features of the plugin are:
 
* Modern look / mobile friendly
* Responsive calendar and form 
* Visual form builder 
* Multi language support
* Notification emails
* Antispam features
* Email reports
* CSV reports
* Usage / Stats area
* Bookings list
* Printable schedule list 
* Multi-View calendar for displaying schedule
* Integration with Elementor, Gutemberg and other page builders
* Add-ons support with iCal add-on included
 
The plugin properly manages the availability of each time slot, allowing to define a maximum capacity for each time slot and to define also the maximum number of time slots that the customer can select for the booking. 

You can optionally allow multiple persons to book the same time-slots until its capacity become fully filled. The plugin allows to setup different prices for two groups (example: adults and children) and features other options that make it appropriate for purchasing even tickets or other activities / items with multiple capacity.

= Features in commercial versions =

While the free version of the plugin is fully functional, there are also commercial versions that adds premium features like the following:

* Payment integration: PayPal, Stripe, Skrill, Authorize.net, iDEAL, SagePay, Redsys
* Payments are SCA ready (Strong Customer Authentication), compatible with the new Payment services (PSD 2) - Directive (EU).
* iCal synchronization (<a href="https://wptimeslot.dwbooster.com/blog/2018/12/20/ical-import/" rel="friend" title="iCal import">iCal import</a> / <a href="https://wptimeslot.dwbooster.com/blog/2018/12/19/adding-google-iphone-outlook/" rel="friend" title="iCal import">iCal export</a>)
* Integration with external services: reCaptcha, MailChimp, SalesForce, WooCommerce and others
* Integration with phone SMS messages via Twilio or Clickatell
* Booking reminders
* Rich form builder (conditional fields, multi-page forms, uploads, ...)
* <a href="https://wptimeslot.dwbooster.com/blog/2018/12/15/additional-services/" rel="friend" title="WP Time Slots Booking Form additional items">Additional items fields</a>
* <a href="https://wptimeslot.dwbooster.com/blog/2018/11/28/status-update-emails/" rel="friend" title="WP Time Slots Booking Form email notifications">Email notifications on booking status updates</a> 

For a full list of commercial features check the <a href="https://wptimeslot.dwbooster.com/download" rel="friend" title="WP Time Slots Booking Form Download Page">plugin download page</a>.



== Installation ==

To install **WP Time Slots Booking Form**, follow these steps:

1.	Download and unzip the WP Time Slots Booking Form calendar plugin
2.	Upload the entire appointment-hour-booking/ directory to the /wp-content/plugins/ directory
3.	Activate the WP Time Slots Booking Form plugin through the Plugins menu in WordPress
4.	Configure the settings at the administration menu >> Settings >> WP Time Slots Booking Form. 
5.	To insert the WP Time Slots Booking Form calendar form into some content or post use the icon that will appear when editing contents

== Frequently Asked Questions ==

= Q: Where can I find the complete WP Time Slots Booking Form plugin documentation? =

A: The product's page contains detailed documentation and support:

<a href="https://wptimeslot.dwbooster.com/support">https://wptimeslot.dwbooster.com/support</a>

= Q: Where can I find the complete WP Time Slots Booking Form plugin documentation? =

A: The product's page contains detailed documentation and support:

<a href="https://wptimeslot.dwbooster.com/support">https://wptimeslot.dwbooster.com/support</a>

= Q: How can I customize the styles? =

A: Please check complete instructions in the following page: <a href="https://wptimeslot.dwbooster.com/blog/2018/11/02/customizing-styles/">https://wptimeslot.dwbooster.com/blog/2018/11/02/customizing-styles/</a>

= Q: Can I display a list with the appointments? =

A: A list with the appointments set on the calendar can be displayed by using this shortcode in the page where you want to display the list:

[CP_TIME_SLOTS_BOOKING_LIST]

Additional details available in the following FAQ entry: <a href="https://wptimeslot.dwbooster.com/faq#q511">https://wptimeslot.dwbooster.com/faq#q511</a>   ... and additional details at the following page: <a href="https://wptimeslot.dwbooster.com/blog/2018/11/21/grouped-frontend-lists/">https://wptimeslot.dwbooster.com/blog/2018/11/21/grouped-frontend-lists/</a>

= Q: I'm not receiving the emails with the appointment data. =

A: Try first using a "from" email address that belongs to your website domain, this is the most common restriction applied in most hosting services.

If that doesn't work please check if your hosting service requires some specific configuration to send emails from PHP/WordPress websites. The plugin uses the settings specified into the WordPress website to deliver the emails, if your hosting has some specific requirements like a fixed "from" address or a custom "SMTP" server those settings must be configured into the WordPress website.

= Q: About changing styles of the dates depending of the amount of booked/available bookings =

A: You can set a different color/style of the dates depending of the number of booked/available slots for that date so the user can get an idea of the amount of spaces available without clicking the date. This feature is useful for example to indicate to the customers the dates where there are few slots available so they know to hurry up.

The instructions are described in detail at the following page: <a href="https://wptimeslot.dwbooster.com/blog/2019/05/10/booked-date-colors/">https://wptimeslot.dwbooster.com/blog/2019/05/10/booked-date-colors/</a>

= Q: Can I export the bookings to external calendars? =

Yes, that can be done using the iCal export add-on included in all versions of the plugin. The process is described in detail at the following page: 
<a href="https://wptimeslot.dwbooster.com/blog/2018/12/19/adding-google-iphone-outlook/">https://wptimeslot.dwbooster.com/blog/2018/12/19/adding-google-iphone-outlook/</a>

= Q: I'm getting API errors while using the the Zoom integration? =

A: If you are getting API errors while creating the Zoom meeting via API please refer to the Zoom developer impacting changes during COVID-19:  <a href="https://devforum.zoom.us/t/developer-impacting-changes-during-covid-19/8930">https://devforum.zoom.us/t/developer-impacting-changes-during-covid-19/8930</a>

= Q: How can I align the form using various columns? =

A: The solution is described at the following FAQ entry: <a href="https://wptimeslot.dwbooster.com/faq#q66">https://wptimeslot.dwbooster.com/faq#q66</a>

= Q: How can I add specific fields into the email message? =

A: Please refer to the following FAQ entry about the available tags for adding info and customizing the emails: <a href="https://wptimeslot.dwbooster.com/faq#q81">https://wptimeslot.dwbooster.com/faq#q81</a>

= Q: How to make the calendar 100% width / responsive? =

A: Use the following CSS style to make the WP Time Slots Booking Form 100% width / responsive on the page:

    #fbuilder .ui-datepicker-inline{max-width:none !important}

Add the styles into the "WP Time Slots Booking Form >> General Settings >> Edit Styles" area.


== Other Notes ==

= The Troubleshoot Area =

Use the troubleshot if you are having problems with special or non-latin characters. In most cases changing the charset to UTF-8 through the option available for that in the troubleshot area will solve the problem.

You can also use this area to change the script load method if the booking calendar isn't appearing in the public website.
 
== Screenshots ==

1. Booking form with quantity fields.
2. Simple booking form.
3. Publish form location in the new Gutemberg editor.
4. Calendar configuration.
5. Usage / Stats area
6. Bookings list
7. Email reports
8. Managing forms
9. Publishing the form with the new editor Gutemberg block

== Changelog ==

= 1.0.03 =
* First version published

= 1.0.04 =
* Improved CSV exports character encoding

= 1.0.05 =
* Fixed issue in quantity management

= 1.0.06 =
* Fixed special dates edition bug
* Improved bookings schedule
* Redirect / confirmation page now supports booking parameters

= 1.0.07 =
* Fixed bug in availability edition

= 1.0.08 =
* Fixed bug in special dates edition

= 1.0.09 =
* Improved translations

= 1.0.10 =
* Fixed bug in form edition

= 1.0.11 =
* Support to booking status

= 1.0.12 =
* Better CSS customization options

= 1.0.14 =
* Clone calendar feature

= 1.0.15 =
* Removed use of CURL

= 1.0.16 =
* Integration with Elementor
* New visual calendar for the schedule view
* Feature for adding bookings from dashboard

= 1.0.17 =
* Removed min/max date restriction for admin bookings
* Fixed available dates definition bug

= 1.0.18 =
* Increased limit of max slots
* Improved language translations support

= 1.0.19 =
* New feature for min available date in hours
* Improved form builder styles
* Added SSL detection

= 1.0.20 =
* Fixed conflict with Visual Composer

= 1.0.21 =
* Fixed bug in special dates

= 1.0.22 =
* Improvements to user permissions section

= 1.0.23 =
* Date format fix

= 1.0.24 =
* Fixed compatibility issue with PHP 7.2+

= 1.0.25 =
* Fixed conflict with lazy loading feature of Jetpack

= 1.0.26 =
* Fixed conflict with Yoast SEO

= 1.0.27 =
* Fixed captcha bug

= 1.0.28 =
* Compatible with WordPress 5.2

= 1.0.29 =
* Added features for adding custom colors to slots depending of booked spaces

= 1.0.30 =
* Language support improvements

= 1.0.31 =
* Date format improvements

= 1.0.32 =
* Fixed bug in iconv function

= 1.0.33 =
* Compatible with Google Translate

= 1.0.34 =
* Update for compatibility with WordPress 5.2

= 1.0.35 =
* iCal end time correction

= 1.0.36 =
* Code improvements

= 1.0.37 =
* Added nonce validation to settings options

= 1.0.38 =
* iCal link improvement

= 1.0.39 =
* Multiple code improvements

= 1.0.40 =
* Fix to captcha image and table encoding

= 1.0.41 =
* Update to reports

= 1.0.42 =
* Fixed bug in date filters

= 1.0.43 =
* Fixed bug max date filter

= 1.0.44 =
* Fixed conflict with autoptimize

= 1.0.45 =
* Fixed conflict with Elementor add-ons

= 1.0.46 =
* New dashboard list add-on

= 1.0.47 =
* New feature for using 12/24 hour format (military / non-military time)

= 1.0.48 =
* Fix to 12 hours time format

= 1.0.49 =
* Feature for highligthing specific dates

= 1.0.50 =
* Compatible with WordPress 5.3

= 1.0.53 =
* Fixed conflict with javascript minify plugins

= 1.0.54 =
* Fixed bug in exported CSV filenames

= 1.0.55 =
* New feature for dealing capacity in booking form

= 1.0.56 =
* Fixed bug in reply-to email header

= 1.0.57 =
* Fixed bug in times pre-fill

= 1.0.58 =
* Fixed bugs in date formatting

= 1.0.59 =
* New tags for emails

= 1.0.60 =
* Fixed conflict with bootstrap datepicker

= 1.0.61 =
* Improved translations

= 1.0.62 =
* Improved iCal add-on
* Better price number formatting for selected times

= 1.0.63 =
* Support for multiple list in same page

= 1.0.64 =
* Multiple improvements and bug fixes

= 1.0.65 =
* Interface improvements

= 1.0.66 =
* iCal export and ics files improvements

= 1.0.67 =
* Fixed bug in invalid dates

= 1.0.68 =
* Fixed bug in working dates

= 1.0.69 =
* New hooks for conversion tracking and improved CSV

= 1.0.70 =
* Added new time intervals

= 1.0.71 =
* PHP 7.x compatibility update

= 1.0.72 =
* Fixed bug in price calculation

= 1.0.73 =
* Compatible with WordPress 5.4

= 1.0.74 =
* Improved translations
* Fixed optimization / cache conflicts

= 1.0.75 =
* Improvement to avoid conflicts with third party themes

= 1.0.76 =
* Update for Gutemberg integration

= 1.0.77 =
* Fixed bug in max-date restriction

= 1.0.78 =
* Improved load speed
* Automatic compatibility with most script optimizers

= 1.0.79 =
* Better visualization speed

= 1.0.80 =
* New translations and language improvements

= 1.0.81 =
* Automatic translation of date format

= 1.0.82 =
* Fixed bug in min-date settings

= 1.0.83 =
* Added support for up to 4 different quantity fields, example, for selecting different number of "Adults", "Children" and "Infants" for the booking

= 1.0.84 =
* Fixed bug in special dates

= 1.0.85 =
* Interface improvements, translations and new quantity feature

= 1.0.86 =
* Multiple interface improvements

= 1.0.87 =
* Optimizations

= 1.0.88 =
* Fixed bug in slots selection

= 1.0.89 =
* New feature for supporting quantity 0 in first quantity fields
* Improved multi-language support

= 1.0.90 =
* Fixed bug when no quantity is used

= 1.0.91 =
* Fixed calendar initialization bug

= 1.0.92 =
* Optional 0 quantity for first qty fields

= 1.0.93 =
* Fixed to the schedule CSV export

= 1.0.94 =
* Add multiple appointment times w/ price structure

= 1.0.95 =
* Removed console log debug line

= 1.0.96 =
* Fixed backward compatibility bug

== Upgrade Notice ==

= 1.0.96 =
* Fixed backward compatibility bug