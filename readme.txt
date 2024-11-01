WP e-Commerce Swipe plugin

Version:	3.1.0 / 24 Sep 2013
Copyright:	(c) 2012-2013, Optimizer Ltd.
Link:		http://www.swipehq.com/checkout/

 

REQUIREMENTS
---

* Swipe account
* Wordpress
* Wordpress WP e-Commerce plugin


INSTALLATION
---

1. Please install this plugin through the normal Wordpress installation process (Plugins -> Add New, then Search or Upload)
2. After successful installation it will appear in the list of Plugins as "Swipe Checkout for WP e-Commerce", make sure to Activate the plugin
3. Then configure Swipe, in the Plugins list, for Swipe Checkout, click on the Settings link, then add the following details 
	from your Swipe Merchant login under Settings -> API Credentials:
		Swipe Merchant ID
		Swipe API Key
		Swipe API Url
		Swipe Payment Page Url
4. And finally configure your Swipe account to send customers back to your shop after they pay. 
	In your Merchant login under Settings -> Payment Notifiers, set:
   		Callback Url:  					%YOUR_WEBSITE%/index.php?swipehq=redirect
   		Callback pass back user data: 	on
   		LPN Url: 						%YOUR_WEBSITE%/index.php?swipehq=callback
	making sure to replace %YOUR_WEBSITE% with your website url, e.g. http://www.example.com/my-shop?swipehq_wpec=redirect
5. All done, test it out, add some products to your cart and you will get the option to pay with Swipe.


NOTES
---
* WP e-Commerce must be configured to use a currency that your Swipe Merchant Account supports for customers to be able to use Swipe as a payment option.
	To see a list of currencies supported by Swipe see Settings -> API Credentials in your merchant account.
	To see which currency your WP e-Commerce store is using see Settings -> Store.


CHANGE LOG
---

1.0
- First Public Release.

2.0
- Test Mode Compatibility.
- Added Multi-currency Support
- Canadian merchant support
- Minor plugin enhancements

3.0.0
- Cleanup
- Fixing conflicts with other Swipe plugins
- Adding Settings link in the Plugins page
- Adding currency check in admin

3.1.0
- Added test configuration test button