Changelog
=========

+ 1.0.10
  + Fixed bugs caused by non-universal time references

+ 1.0.9
  + Subscriptions and transactions now load via ajax in blocks. There is virtually no limit to how large these tables can now be, they will load into the jQuery data tables.

+ 1.0.8
  + Added core code a developer could use to do some basic tracking of referral ids. You encrypt a user ID and pass it in as a url parameter to your order form. The order form MUST submit this id under the name of "referrer" and the WPAT plugin will simply record the new subscription ID to the RefID users meta data.
  + New function names for RefID wpat_encrypt_user_id($userID), wpat_decrypt_user_id($userID), wpat_get_referrals($userID)

+ 1.0.7
  + Adjusted auto cancel routine to ensure all suspended subscriptions regardless of their authorize.net status are canceled.
  + Adjusted "Next Billing Date" calculations to fix an error that occurred when a subscription was on its initial billing cycle.

+ 1.0.6
  + Show subscriptionCancelledBy and subscriptionCanceledDate date fields on Subscriptions page

+ 1.0.5
  + Undocumented

+ 1.0.4.2
  + RB: Change subscriptions page default active columns
  + JG: Added addonData handling to billingUpdate class

+ 1.0.4.1
  + 1.0.4 patch for transaction status did not work as intended. Re-worked and patched again
  + Adjusted billing update functions to ensure transactions are associated with the same invoice number as original order

+ 1.0.4
  + Fixed bugs related to identifying transcation status on auth.net returns
  + Rearranged so that invoice numbers can be matched between transactions and subscriptions to establish relationship between the two
  + MD5 hashing of auth.net returns can be be enabled or disabled
  + Transactions are now analyzed to determine which are handled and which are orphans, admin emails are no longer sent for orphans

+ 1.0.3.1
  + Fixed a styling bug in data tables

+ 1.0.3
  + Users no longer see a FOBUC on data table loading, just a "Loading..." message
  + Added Subscriptions page

+ 1.0.2
  + Minor documentation updates
  + Set up CK project
  + Added style/script mgmt
  + Cleaned up transactions page
  + Integrated jQuery DataTables
  + Added conditional for update routine

+ 1.0.1
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

+ 1.0.0
  + Initial release
   post type always displays
  + PEM file now resides outside plugin
  + Added md5 hash enable option, not integrated
  + MD5 hashing temporarily always disabled
  + Settings form no longer drawn from billing class
  + Styled settings page

+ 1.0.0
  + Initial release
