# Summary

This example demonstrates how to load the IPQS device tracker on page load but delay complete initialization until a contact form is submitted. The device tracker waits for the contact form to be submitted, attaches the phone and email address to the fingerprint, and fully loads the device tracker. 


## Step By Step
1. The page loads and starts the device tracker.

2. A user fills out the contact form and clicks the 'Send' button. 

3. The device tracker attaches the email address and phone number to the device tracker.

4. The device tracker is fully loaded. 

Note that the email address is saved to a custom variable in your IPQS account. This custom variable can be called whatever you prefer, but it must be configured in your IPQS account before the information associated with it from the device fingerprint will be saved. 

Custom variables can be configured [here](https://www.ipqualityscore.com/user/settings#variables)

5. The contact form submits the message.


