=== Token Manager ===
Contributors: Codevendor
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=DJ3FZSYFT9AMW
Author URI: http://codevendor.com/
Plugin URI: http://www.codevendor.com/product/tokenmanager/
Tags: token, tokens, manager, code, PHP, CSS, javascript, HTML, js, develop, injection, inline
Requires at least: 3.0
Tested up to: 3.4.2
Stable tag: 1.0.4

The Token Manager allows web developers to program PHP, HTML, CSS and JavaScript into tokens that can be used throughout WordPress.

== Description ==

The Token Manager allows web developers to program PHP, HTML, CSS and JavaScript into tokens that can be used throughout WordPress. Tokens can be placed into themes, templates, pages, widgets, etc. The code in the token is processed and then injected into the token name. It allows web developers to package their code into components for easy reuse and maintenance while speeding up development time and distribution. The tokens are also aware of WordPress and can utilize all the common libraries and database from within the token. This allows for unlimited possibilities with web page design, third party api, WordPress api, PHP frameworks, web 2.0, other WordPress plugins, etc. The tokens can even be used within other tokens for replacement of text, making HTML, CSS, JS, fully dynamic. 

= MultiSite =
The Token Manager has been built to work with WordPress MultiSite as well as single blog installations. By activating it on the network, it will add the capabilities to control tokens across WordPress sites and blogs. It will also store all tokens in custom tables that are not attached to WordPress, allowing for easy upgrades, backup and version changes.

= Token Management =
The management of tokens is very simple, with built in ajax controls to allow assignment of tokens to individual and all pages. It also separates out frontpage assignment, giving you the freedom to control exactly what is seen on the frontpage. The manager also keeps track of who created and last updated the tokens in WordPress. 

= Token Parameters =
The Token Manager now supports token parameters. You can specify a string or integer parameter by adding them behind the token name. You can then access the parameter from within the token code, allowing for unlimited code possibilities.

= Error Handling =
PHP and token errors are handled by the Token Manager and can be displayed by turning on custom settings in the manager. This gives developers the full control to know where errors are occurring within their token code. The error setting can also be turned off for live sites. This protects you from displaying error information on public sites. All errors that occur in the tokens, unless fatal, will not stop the webpage from being displayed. So feel free to code your ideas without worring about errors halting WordPress.

= Token Manager (Standard) Features =
* Dynamic Processes PHP, HTML, CSS, JS
* Token Management, Add, Edit, Delete
* Token Parameters
* Token Process Ordering
* Token Page Assignment
* Token Statistics
* Token Descriptions
* Custom Token Types
* Custom Searching and Recordsets
* Supports WordPress MultiSite
* WordPress aware Tokens
* Tokens within Tokens
* Error Handling on Webpage, PHP and Tokens
* Quick Help Question Marks

= Token Manager (Professional) Features =
* All Standard Features
* Global Tokens
* External Scripts
* Code Versioning
* Token History
* Backup and Restore Systems
* Error Management Systems
* Online Technical Support

= Future Enhancements For Both =
* WordPress Token Injection Menus
* Code Optimization
* Sorting Systems
* Better Searching

If you have any enhancement suggestions or want to report a bug, please visit http://www.codevendor.com/support/

== Installation ==

= WordPress Standard Installation =
1. Upload the contents of 'token-manager' to '/wp-content/plugins/token-manager' within your WordPress.
2. Activate the plugin through the 'Plugins' menu in the WordPress Admin
3. Visit the Token Manager Settings Page and turn on the features you desire.
4. Add some token types in the manager.
5. Add some tokens in the manager.
6. Assign the token to a WordPress page.
7. Put {your_token_name} in the assigned page, template, widget, theme, etc. 
8. Visit the page to see the token replacement.

= WordPress MultiSite Installation =
1. Upload the contents of 'token-manager' to '/wp-content/plugins/token-manager' within your WordPress.
2. Network activate the plugin through the 'Plugins' menu in the WordPress Network Admin.
3. Visit a desired network site or blog dashboard.
4. Visit the Token Manager Settings Page and turn on the features you desire.
5. Add some token types in the manager.
6. Add some tokens in the manager.
7. Assign the token to a WordPress page.
8. Put {your_token_name} in the assigned page, template, widget, theme, etc. 
9. Visit the page to see the token replacement.


== Frequently Asked Questions ==

**My token is not appearing on my page?**
You must assign your token from the Token Manager to page. Visit the tokens link. Click on the icon that look like pages. Search for a page. Assign that page or multiple pages to the token by clicking on the arrow buttons.

**What are token types?**
Token types are created by you. They allow you to organize your code into categories of type. i.e text, control, template, thirdparty

**How do I assign the front page or all pages to my token?**
Visit the tokens link. Click on the icon that looks like pages. In the search box type 'frontpage' for frontpage or 'all pages' for all pages. Then click on the arrow button to assign it.

**Where does my token go if I delete it from the Token Manager?**
No worries! It does not delete the token, it just deactivates it from the Token Manager. You would still be able to find it in the database tables. Future enhancements will probably include the functionality to retrieve it.

**How does this plugin support WordPress MultiSite?**
When activated as a network plugin, you can assign unique tokens in each site and blog from their corresponding dashboards.

**Is there a history of action on the tokens?**
Yes, The Token Manager records an xml file in the table recording actions taken. Future versions of Token Manager will include history managing tools.

**Do you support token code versioning systems?**
Yes!, but only in the professional versions. The standard free version uses versioning in the background, but doesnt give you the tools to manage it. If you would like to find out more about the professional version please visit http://www.codevendor.com/product/tokenmanager/

**If I write a PHP code incorrectly, will it halt my WordPress?**
No, not unless it is a fatal error. Common errors in syntax will be displayed with an error message, if the Token Manager has the settings enabled. If the settings are not turned on, it will display a blank token. 

**How do I write dynamic HTML, CSS and JS?**
PHP code is supported in all coding tabs. Also token codes are also supported in other tokens starting from top to bottom ordering of types.

**Do you support token backup?**
Yes!, but it is a manual process in the standard version. You must edit each token and copy the contents from each code tab. Professional versions include a simple detailed backup system with versioning and restore tool. If you would like to find out more about the professional version please visit http://www.codevendor.com/product/tokenmanager/

**How do I make suggestions or bug reports?**
Please visit the WordPress forums for this plugin or visit the main website at http://www.codevendor.com/support/

**How do I optimize my tokens to load faster?**
By directly assigning each token to a specific page and only using 'all pages' sparingly.

**How do I keep my token PHP code from having naming convention and collision errors?**
By using classes and unique naming, just like you would do writing any PHP code. Please refer to http://PHP.net/manual/en/language.oop5.PHP

**Do I have to write the beginning and ending script tags for PHP, CSS and JS?**
No, they are added for you. If you are injecting PHP code into another language, then yes, `<?PHP ?>` would be required.

**Are PHP shortcodes supported `<? ?>`?**
No, not at this time. You must use `<?PHP ?>` for your PHP.

**Do all codes inline on the page?**
Yes, for now each code inlines into the page, but future enhancements will allow you to make things external.

**How do I add token parameters?**
If you have a token named `example`, you could add parameters to extend it like so: {example, `Param1`, `Param2`, ParamInt3}.
This will pass param1, param2 and param3 to example token. Token parameters accept strings and integers. You can use either double or single quotes around parameters. If you have quotes inside quotes you need to escape them. If you use `{` or `}` in your strings, make sure you encode them. To access the parameters within your token code use the following: $GLOBALS["ARGS"][0]

== Screenshots ==

1. Here is a screen shot of adding a token that creates a copyright.
2. Here is what the Token Manager looks like.
3. This screen shot shows how to assign tokens to pages.
4. This screen shot shows adding the token text to the page we assigned.
5. Shows the actual copyright token being rendered in a view.
6. Example helloworld, showing how to use the HTML tab.
7. Example helloworld, showing how to use the PHP tab.
8. Example helloworld, showing how to use the CSS tab.
9. Example helloworld, showing how to use the JS tab.
10. Shows the actual helloworld token being rendered in a view.
11. Shows the actual helloworld token running the javascript onclick.
12. The error handler showing a PHP error on the page.
13. Show all key value pairs for the page with settings turned on.
14. Shows the token ordering for processing tokens in a specific custom order.
15. Shows the code editor for the tokens.

== Changelog ==
= 1.0.4 =
* Updated database and changed history data type from TEXT to MEDIUMTEXT to allow more token updates than 400+. Added in mysql return errors on update statements.

= 1.0.3 =
* Security update to remove XSS with 'tid' in tokenedit.php and typeedit.php. No other changes, safe to update.

= 1.0.2 =
* Added in search for page by id. Added in process id into status hover. Fixed link locations. Fixed support menus to display on top of other controls with proper z-index. Added in sorting links for id, name and type. Moved delete and version button to right side. Changed some status menus to correct textual information. No database changes safe to update.

= 1.0.1 =
* No database changes. Added in CodeMirror version 2.3 source code editor. Allows for code highlighting PHP, JS, CSS and HTML. Also has find and replace features built in. Fixed a couple css size issues with form elements.

= 1.0.0 =
* Token Manager has been revamped to include parameters for tokens. No database changes, only large optimizations of code and processing. Added in hover help icons and hover status messages. Fixed delete process for token types. Fixed token ordering issues. Added in searching by ids and names. Changed layout a little. Hopefully, I didnt break much! You should upgrade and tell me what you think of the new changes.

= 0.2.5 =
* New feature added that allows you to process the tokens in a specific custom order. Small database table (tokenmanager) change. Added column (processorder). This change will require previous versions to add in the new column and assign a process order id to existing tokens.

= 0.2.4 =
* Added in a setting to remove donation button. Added in a coffee message. Working on token ordering for next release. No changes to database.

= 0.2.3 =
* Reversed token replace order to make sure extended tokens are replaced first. Added in REQUEST_TEMPLATESURL to the extended tokens. No changes to database.

= 0.2.2 =
* Added in setting option to remove automatic WordPress p tags. Added in REQUEST_TEMPLATESPATH to the extended tokens. No changes to database.

= 0.2.1 =
* Cleaned Up PHP Error Notices and Changed plugin name from 'token manager' to 'token-manager' because wordpress zip program packs it that way causing my code to break down. Cleaned up readme file also. Submitted icons folder into svn.

= 0.2 =
* Added changes to tokenmanager.php for role capabilities from 'Admin' to 'activate_plugins'. This allows plugin to work for single installs as well as multi-site.

= 0.1 =
* Initial release

== Upgrade Notice ==
* None as of Yet

