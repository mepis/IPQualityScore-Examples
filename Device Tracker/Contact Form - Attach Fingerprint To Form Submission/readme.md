# Summary

This example demonstrates how to attach the IPQS device fingerprinter to a HTML contact form. In this example, the HTML form action calls the submit() Javascript function on submission. When the submit() function is called, the IPQS fingerprinter collects the fingerprint data, adds the email address from the contact form to the fingerprint, and appends the fingerprint data to the contact form. 

## Step By Step

1. A user fills out the contact form and clicks the 'Send' button. 

2. The submit() function is called when clicking the 'Send' button as declared in the HTML form. 

3. The IPQS fingerprinter is initialized starting at line 21.

4. Line 23 intercepts the call to the submit() function, gets the email address from the contact form, and stores the email address to the device fingerprint. The email address can be referenced from the Advanced Reports page within the Device Fingerprint Tracking section in your IPQS account. 

Note that the email address is saved to a custom variable in your IPQS account. This custom variable can be called whatever you prefer, but it must be configured in your IPQS account before the information associated with it from the device fingerprint will be saved. 

Custom variables can be configured [here](https://www.ipqualityscore.com/user/settings#variables)

5. After the Startup.Trigger interception is finished, the submit() function is finally executed. 

In this example, the contact form data is logged to the console. The instructions in the submit() function can be replaced to handle the form data however is necessary. Likewise, the action in the HTML form can be changed to call any function or service as needed to handle the contact form submission as needed. 