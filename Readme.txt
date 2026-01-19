=== DW Directory Service ===
Contributors: dhlee7
Tags: directory, business listing, yellow pages, church directory, local business
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 2.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A directory service plugin for WordPress - perfect for local business listings, church member directories, and community resources.

== Description ==

DW Directory Service enables you to create and manage directory listings in WordPress. It was designed for use cases such as:

* Local community business directories
* Church member business listings
* Restaurant guides
* Service provider directories
* Any categorized listing needs

**Features:**

* Hierarchical category system (up to 3 levels)
* User submission with admin approval workflow
* Search functionality
* Responsive design
* Easy shortcode integration
* AJAX-powered interface

**Usage:**

Simply add the shortcode `[dw-directory]` to any page or post where you want the directory to appear.

**Links:**

* [GitHub Repository](https://github.com/Daworks/dw-directory)
* [Documentation](https://daworks.github.io/dw-directory/)

== Installation ==

1. Upload the `dw-directory` folder to the `/wp-content/plugins/` directory, or install directly through the WordPress plugins screen.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to 'Directory Service' in the admin menu to configure categories and manage items.
4. Add the shortcode `[dw-directory]` to any page or post where you want the directory to appear.

== Frequently Asked Questions ==

= How do I use the plugin after installation? =

After activating the plugin, add the shortcode `[dw-directory]` to any page or post where you want to display the directory.

= How do I manage the directory? =

Log in to your WordPress dashboard. You'll find a 'Directory Service' menu with options for:
- Pending Items: Approve or reject user submissions
- Category Management: Add, edit, or delete categories
- Item Management: Manage individual directory entries

= How do I set up categories? =

Go to 'Directory Service > Category Management' in the admin menu. You can create a hierarchical structure with up to 3 levels of categories.

= Can users submit their own listings? =

Yes! Logged-in users can submit listings through the front-end form. Submissions go to a pending queue for admin approval.

= Where can I get support? =

* [GitHub Issues](https://github.com/Daworks/dw-directory/issues)
* [Documentation](https://daworks.github.io/dw-directory/)

== Screenshots ==

1. Directory service front-end display
2. Pending items management (admin)
3. Category management (admin)
4. Item management (admin)

== Changelog ==

= 2.0.0 =
* **Security**: Added nonce verification to all AJAX requests
* **Security**: Implemented prepared statements for all database queries
* **Security**: Added proper input sanitization and output escaping
* **Compatibility**: Updated for WordPress 6.7
* **Compatibility**: Requires PHP 7.4 or higher
* **Fix**: Removed deprecated `get_currentuserinfo()` function
* **Improvement**: Modernized codebase following WordPress Coding Standards
* **Improvement**: Better error handling and user feedback
* **Improvement**: Improved accessibility with proper link attributes

= 1.2 =
* Updated source code for WordPress compatibility
* CSS fixes

= 1.1 =
* Fixed admin page errors

= 1.0 =
* Initial release

== Upgrade Notice ==

= 2.0.0 =
Major security update. All users are strongly recommended to upgrade. This version includes nonce verification, prepared SQL statements, and proper data sanitization. Requires WordPress 5.0+ and PHP 7.4+.

= 1.2 =
Compatibility update for newer WordPress versions.

= 1.0 =
Initial release. Requires WordPress 4.5 or later.
