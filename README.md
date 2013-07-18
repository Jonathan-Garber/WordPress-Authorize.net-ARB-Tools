WordPress Authorize.net ARB Tools
=================================

WordPress Authorize.net ARB Tools is a WordPress plugin designed to allow developers to build subscription based billing management sites using WordPress, a custom theme and this plugin.

The plugin also allows for processing of single payments using Authorize.net.

Recurring payments are handled through Authorize.net ARB. This plugin only operates with Authorize.net and will never be adapted to other payment processing gateways.

[Read the plugin Wiki](https://github.com/ryanburnette/WordPress-Authorize.net-ARB-Tools/wiki) for more details.

Version History
---------------

### 1.0.5
+ Undocumented

### 1.0.4.2
+ RB: Change subscriptions page default active columns
+ JG: Added addonData handling to billingUpdate class

### 1.0.4.1
+ 1.0.4 patch for transaction status did not work as intended. Re-worked and patched again
+ Adjusted billing update functions to ensure transactions are associated with the same invoice number as original order

### 1.0.4
+ Fixed bugs related to identifying transcation status on auth.net returns
+ Rearranged so that invoice numbers can be matched between transactions and subscriptions to establish relationship between the two
+ MD5 hashing of auth.net returns can be be enabled or disabled
+ Transactions are now analyzed to determine which are handled and which are orphans, admin emails are no longer sent for orphans

### 1.0.3.1
+ Fixed a styling bug in data tables

### 1.0.3
+ Users no longer see a FOBUC on data table loading, just a "Loading..." message
+ Added Subscriptions page

### 1.0.2
+ Minor documentation updates
+ Set up CK project
+ Added style/script mgmt
+ Cleaned up transactions page
+ Integrated jQuery DataTables
+ Added conditional for update routine

### 1.0.1
+ Added base of internal version tracking
+ Began adhering to style guide
+ Removed subscription management functions (unused)
+ Reorganized menus
+ Added Transactions page
+ Auth-processors post type always displays
+ PEM file now resides outside plugin
+ Added md5 hash enable option, not integrated
+ MD5 hashing temporarily always disabled
+ Settings form no longer drawn from billing class
+ Styled settings page

### 1.0.0
+ Initial release
