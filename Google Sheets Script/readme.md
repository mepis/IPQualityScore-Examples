# How to validate IPs in Google Sheets

This Google Apps script can help make validating data inside of Google Sheets with IPQS easier. 

## Pre-requisites
- A valid IPQS account
- A valid IPQS API key
- This script is subject to the restrictions of your IPQS subscription level

## Additional Information
This script supports data validation for:
- IPs
- Phone Numbers
- Email Address
- URLs

Data can be in any column inside Google Sheets, though the entire column must contain the same data. 

Data can live in multiple columns. For example, customers may have multiple columns for phone numbers (Phone_1, Phone_2, etc…). The script will prompt you for each column letter before running. So, if Phone_1 is in column ‘D’, and Phone_2 is in Column ‘F’, enter columns D and F when asked to validate both columns. 

Each column letter must be separated by a comma. Eg: f,d

Only one data validation type can be run at a time. If a customer needs to validate phone numbers, email addresses, and IPs, the phone report must be run first, then email addresses, and finally IPs. 

A new sheet is created for each data type. The original data is not changed.

If a report was already run (Eg. validating phone numbers), and the same report is run again, the sheet containing the validation information for the original report will be overwritten. 

For ease, the example file below can be copied into your Google Drive as a template instead of copying the App Script into a new Sheet. Customer data can then be copied and pasted into the copy of that file. 

## Where can I test this script?
IPQS staff with a valid IPQS Google Workspace email address can access the test sheet here: IPQS IP Report Example

## Installation Instructions
1. Download the App Script from the link below. 
2. Open the App Script in a text editor or code editor like Visual Studio Code. Make sure to use a plain text editor such as Notepad (Windows), Text Editor (Mac OS), GEdit (Linux), NotePad++, or Visual Studio Code. Other text editors may add additional characters or change the formatting. 
3. Click Extensions in the menu bar in Google Sheets
4. Choose App Scripts
5. Copy and paste the code below into the Google App Scripts Editor screen
6. Save the App Script
7. Close and re-open the Google Sheet
8. Click Extensions and choose App Scripts again
9. A new menu bar item called ‘Run IPQS Report’ will appear
10. Click ‘Run IPQS Report’
11. Choose which report type to run
12. A dialogue box will appear. Enter your IPQS API key into the dialogue box.
13. Another dialogue box will appear asking which columns the data is in. Enter the column letter only. Multiple column letters can be entered. Separate each column letter by a comma. 
14. Press the ‘OK’ button to run the report. 

