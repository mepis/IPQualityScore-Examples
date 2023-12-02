const ui = SpreadsheetApp.getUi();
const sheet = SpreadsheetApp.getActiveSpreadsheet();
let apiKey = '';
let rawColumnSelection = '';
let isIPCheck = false;
let isPhoneCheck = false;
let isURLCheck = false;
let isEmailCheck = false;

const infoTitle = "How to use the IPQS Google Sheets App Script";
const infoString = "\n\n Select the appropriate option from the 'Run IPQS Report' menu." +
  "\n\n Enter your IPQS API key when prompted. API keys are not stored for security reasons." +
  "\n\n Data can be validated from multiple columns. When prompted, enter each column letter only. Each column letter must be seperated by a comma. " +
  "\n\n Different types of data cannot be validated at the same time (EG. IPs + phones, etc...)." +
  "\n\n Each data type must be run seperately (Eg. Ips THEN phones, etc.)." +
  "\n\n A new sheet will be created for each data type.";

// #############################
// # User Prompts
// #############################

function onOpen() {
  ui.createMenu('Run IPQS Report')
    .addItem('Validate IPs', 'startIPValidationProcess')
    .addItem('Validate Emails', 'startEmailValidationProcess')
    .addItem('Validate Phone Numbers', 'startPhoneValidationProcess')
    .addItem('Validate URLs', 'startURLValidationProcess')
    .addSeparator()
    .addItem('Info', 'info')
    .addToUi();
}

function promptForAPIKey() {
  let result = ui.prompt(
    'For safety, you will be prompted to enter your IPQS API key each time. API keys are not stored permanently.',
    'Enter your IPQS API key here:',
    ui.ButtonSet.OK_CANCEL);
  let temp = result.getResponseText();
  apiKey = temp.replaceAll(/\s/g, '');
  if (result.getSelectedButton() == ui.Button.OK) {
    promptForDataColumns();
  }
  if (result.getSelectedButton() == ui.Button.CANCEL) {
    flushAPIKey();
  }
}

function promptForDataColumns() {
  let result = ui.prompt(
    'Which column is your data in?',
    'Enter column letters ONLY, each column letter must be seperated by a comma ( , ):',
    ui.ButtonSet.OK_CANCEL);
  let temp = result.getResponseText();
  rawColumnSelection = temp.replaceAll(/\s/g, '');
  if (result.getSelectedButton() == ui.Button.OK) {
    if (isIPCheck) createIPResultsSheet();
    if (isPhoneCheck) createPhoneResultsSheet();
    if (isURLCheck) createURLResultsSheet();
    if (isEmailCheck) createEmailResultsSheet();
  }
  if (result.getSelectedButton() == ui.Button.CANCEL) {
    flushAPIKey();
  }
}

function info() {
  ui.alert(infoTitle, infoString, ui.ButtonSet.OK);
}

// #############################
// # Helpers
// #############################

function flushAPIKey() {
  apiKey = "";
  isIPCheck = false;
  isPhoneCheck = false;
  isURLCheck = false;
  isEmailCheck = false;
}

function startIPValidationProcess() {
  isIPCheck = true;
  promptForAPIKey();
}

function startEmailValidationProcess() {
  isEmailCheck = true;
  promptForAPIKey();
}

function startPhoneValidationProcess() {
  isPhoneCheck = true;
  promptForAPIKey();
}

function startURLValidationProcess() {
  isURLCheck = true;
  promptForAPIKey();
}

function deleteSheet() {
  return new Promise(async (resolve) => {
    let doesSheetExist;
    if (isIPCheck) doesSheetExist = sheet.getSheetByName('IPValidationResults');
    if (isPhoneCheck) doesSheetExist = sheet.getSheetByName('PhoneValidationResults');
    if (isURLCheck) doesSheetExist = sheet.getSheetByName('URLValidationResults');
    if (isEmailCheck) doesSheetExist = sheet.getSheetByName('EmailValidationResults');

    if (doesSheetExist) {
      sheet.deleteSheet(doesSheetExist);
      resolve(true);
    }
  });
}

async function prepData() {
  const columns = rawColumnSelection.split(",");
  const cycle = 0;
  let data = await getData(columns, cycle);
  let filteredData = data.reduce(function (cells, row) {
    return cells.concat(row.filter(function (cell) {
      return cell != "";
    }));
  }, []);
  if (isIPCheck) validateIPs(filteredData, 0);
  if (isPhoneCheck) validatePhones(filteredData, 0);
  if (isURLCheck) validateURLs(filteredData, 0);
  if (isEmailCheck) validateEmails(filteredData, 0);
}

function getData(columns, cycle) {
  return new Promise(async (resolve) => {
    if (columns.length > cycle) {
      let data = sheet.getSheets()[0].getRange(columns[cycle] + "1:" + columns[cycle]).getValues();
      cycle++;
      let combinedData = data.concat(await getData(columns, cycle));
      resolve(combinedData);
    } else {
      const done = [];
      resolve(done);
    }
  });
}

// #############################
// # IP Validation functions
// #############################

async function createIPResultsSheet() {
  let doesSheetExist = sheet.getSheetByName('IPValidationResults');
  if (!doesSheetExist) {
    sheet.insertSheet('IPValidationResults');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 1).setValue('IP Address');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 2).setValue('Response Message');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 3).setValue('Fraud Score');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 4).setValue('Country Code');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 5).setValue('Region');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 6).setValue('City');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 7).setValue('ISP');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 8).setValue('ASN');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 9).setValue('Orginization');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 10).setValue('Is Crawler');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 11).setValue('Timezone');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 12).setValue('Is Mobile');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 13).setValue('Host');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 14).setValue('Proxy');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 15).setValue('Is VPN');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 16).setValue('Is Tor');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 17).setValue('Is Active VPN');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 18).setValue('Is Acive Tor');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 19).setValue('Has Resent Abuse');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 20).setValue('Bot Status');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 21).setValue('Connection Type');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 22).setValue('Abuse Velocity');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 23).setValue('Zip Code');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 24).setValue('Latitude');
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 25).setValue("longitude");
    SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(1, 26).setValue('IPQS Request ID');
    prepData();
  }
  if (doesSheetExist) {
    let didSheetDelete = await deleteSheet();
    if (didSheetDelete) {
      createIPResultsSheet();
    }
  }
}

function validateIPs(filteredData, cycleCount) {
  if (filteredData.length > cycleCount) {
    try {
      const targetCell = cycleCount + 2;
      const apiString = "https://ipqualityscore.com/api/json/ip/" + apiKey + "/" + filteredData[cycleCount];
      let response = UrlFetchApp.fetch(apiString);
      let responseData = JSON.parse(response.getContentText());
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 1).setValue(filteredData[cycleCount]);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 2).setValue(responseData.message);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 3).setValue(responseData.fraud_score);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 4).setValue(responseData.country_code);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 5).setValue(responseData.region);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 6).setValue(responseData.city);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 7).setValue(responseData.ISP);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 8).setValue(responseData.ASN);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 9).setValue(responseData.organization);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 10).setValue(responseData.is_crawler);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 11).setValue(responseData.timezone);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 12).setValue(responseData.mobile);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 13).setValue(responseData.host);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 14).setValue(responseData.proxy);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 15).setValue(responseData.vpn);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 16).setValue(responseData.tor);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 17).setValue(responseData.active_vpn);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 18).setValue(responseData.active_tor);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 19).setValue(responseData.recent_abuse);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 20).setValue(responseData.bot_status);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 21).setValue(responseData.connection_type);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 22).setValue(responseData.abuse_velocity);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 23).setValue(responseData.zip_code);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 24).setValue(responseData.latitude);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 25).setValue(responseData.longitude);
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 26).setValue(responseData.request_id);
    } catch (error) {
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 1).setValue('Error Validating');
      SpreadsheetApp.getActive().getSheetByName('IPValidationResults').getRange(targetCell, 2).setValue(error);
      Logger.log(error);
    } finally {
      cycleCount++;
      validateIPs(filteredData, cycleCount);
    }
  } else {
    flushAPIKey();
    ui.alert("Complete");
  }
}

// #############################
// # Email Validation functions
// #############################

async function createEmailResultsSheet() {
  let doesSheetExist = sheet.getSheetByName('EmailValidationResults');
  if (!doesSheetExist) {
    sheet.insertSheet('EmailValidationResults');
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 1).setValue("Email");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 2).setValue("Is Valid");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 3).setValue("Timed Out");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 4).setValue("Is Disposable");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 5).setValue("First Name");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 6).setValue("Deliverability");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 7).setValue("SMTP Score");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 8).setValue("Overall Score");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 9).setValue("Catch All");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 10).setValue("Is Generic");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 11).setValue("Is Common");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 12).setValue("Is DNS Valid");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 13).setValue("Is Honeypot");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 14).setValue("Is Frequent Complainers");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 15).setValue("Is Suspect");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 16).setValue("Has Recent Abuse");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 17).setValue("Fraud Score");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 18).setValue("Is Leaked");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 19).setValue("Suggested Domain");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 20).setValue("Domain Velocity");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 21).setValue("User Activity");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 22).setValue("First Seen");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 23).setValue("Domain Age");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 24).setValue("Success");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 25).setValue("Spam Trap Score");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 26).setValue("Sanitized Email");
    SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(1, 27).setValue("IPQS Request ID");
    prepData();
  }
  if (doesSheetExist) {
    let didSheetDelete = await deleteSheet();
    if (didSheetDelete) {
      createEmailResultsSheet();
    }
  }
}

function validateEmails(filteredData, cycleCount) {
  if (filteredData.length > cycleCount) {
    try {
      const targetCell = cycleCount + 2;
      const apiString = "https://ipqualityscore.com/api/json/email/" + apiKey + "/" + filteredData[cycleCount];
      let response = UrlFetchApp.fetch(apiString);
      let responseData = JSON.parse(response.getContentText());
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 1).setValue(filteredData[cycleCount]);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 2).setValue(responseData.valid);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 3).setValue(responseData.timed_out);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 4).setValue(responseData.disposable);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 5).setValue(responseData.first_name);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 6).setValue(responseData.deliverability);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 7).setValue(responseData.smtp_score);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 8).setValue(responseData.overall_score);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 9).setValue(responseData.catch_all);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 10).setValue(responseData.generic);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 11).setValue(responseData.common);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 12).setValue(responseData.dns_valid);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 13).setValue(responseData.honeypot);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 14).setValue(responseData.frequent_complainer);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 15).setValue(responseData.suspect);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 16).setValue(responseData.recent_abuse);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 17).setValue(responseData.fraud_score);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 18).setValue(responseData.leaked);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 19).setValue(responseData.suggested_domain);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 20).setValue(responseData.domain_velocity);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 21).setValue(responseData.user_activity);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 22).setValue(responseData.first_seen.human);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 23).setValue(responseData.domain_age.human);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 24).setValue(responseData.success);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 25).setValue(responseData.spam_trap_score);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 26).setValue(responseData.sanitized_email);
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 27).setValue(responseData.request_id);
    } catch (error) {
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 1).setValue('Error Validating');
      SpreadsheetApp.getActive().getSheetByName('EmailValidationResults').getRange(targetCell, 2).setValue(error);
      Logger.log(error);
    } finally {
      cycleCount++;
      validateEmails(filteredData, cycleCount);
    }
  } else {
    flushAPIKey();
    ui.alert("Complete");
  }
}


// #############################
// # Phone Validation functions
// #############################

async function createPhoneResultsSheet() {
  let doesSheetExist = sheet.getSheetByName('PhoneValidationResults');
  if (!doesSheetExist) {
    sheet.insertSheet('PhoneValidationResults');
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 1).setValue("Phone");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 2).setValue("Response Message");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 3).setValue("Success");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 4).setValue("Formatted Number");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 5).setValue("Local Format");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 6).setValue("Is Valid");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 7).setValue("Fraud Score");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 8).setValue("Has Recent Abuse");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 9).setValue("Is Voip");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 10).setValue("Is Prepaid");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 11).setValue("Is Risky");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 12).setValue("Is Active");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 13).setValue("Name");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 14).setValue("Carrier");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 15).setValue("Line Type");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 16).setValue("Country");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 17).setValue("Region");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 18).setValue("City");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 19).setValue("Time Zone");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 20).setValue("Zip Code");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 21).setValue("Dialing Code");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 22).setValue("Do Not Call");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 23).setValue("Leaked");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 24).setValue("Spammer");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 25).setValue("Active Status");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 26).setValue("User Activity");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 27).setValue("Has Associated Emails");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 28).setValue("Associated Emails");
    SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(1, 29).setValue("IPQS Request ID");
    prepData();
  }
  if (doesSheetExist) {
    let didSheetDelete = await deleteSheet();
    if (didSheetDelete) {
      createPhoneResultsSheet();
    }
  }
}

function validatePhones(filteredData, cycleCount) {
  if (filteredData.length > cycleCount) {
    try {
      const targetCell = cycleCount + 2;
      let temp = String(filteredData[cycleCount]);
      let tempNumber = temp.replace(/\D/g, '');
      const apiString = "https://ipqualityscore.com/api/json/phone/" + apiKey + "/" + tempNumber;
      let response = UrlFetchApp.fetch(apiString);
      let responseData = JSON.parse(response.getContentText());
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 1).setValue(filteredData[cycleCount]);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 2).setValue(responseData.message);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 3).setValue(responseData.success);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 4).setValue(responseData.formatted);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 5).setValue(responseData.local_format);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 6).setValue(responseData.valid);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 7).setValue(responseData.fraud_score);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 8).setValue(responseData.recent_abuse);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 9).setValue(responseData.VOIP);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 10).setValue(responseData.prepaid);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 11).setValue(responseData.risky);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 12).setValue(responseData.active);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 13).setValue(responseData.name);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 14).setValue(responseData.carrier);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 15).setValue(responseData.line_type);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 16).setValue(responseData.country);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 17).setValue(responseData.region);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 18).setValue(responseData.city);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 19).setValue(responseData.timezone);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 20).setValue(responseData.zip_code);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 21).setValue(responseData.dialing_code);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 22).setValue(responseData.do_not_call);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 23).setValue(responseData.leaked);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 24).setValue(responseData.spammer);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 25).setValue(responseData.active_status);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 26).setValue(responseData.user_activity);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 27).setValue(responseData.associated_email_addresses.status);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 28).setValue(responseData.associated_email_addresses.emails);
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 29).setValue(responseData.request_id);
    } catch (error) {
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 1).setValue('Error Validating');
      SpreadsheetApp.getActive().getSheetByName('PhoneValidationResults').getRange(targetCell, 2).setValue(error);
      Logger.log(error);
    } finally {
      cycleCount++;
      validatePhones(filteredData, cycleCount);
    }
  } else {
    flushAPIKey();
    ui.alert("Complete");
  }
}


// #############################
// # URL Validation functions
// #############################

async function createURLResultsSheet() {
  let doesSheetExist = sheet.getSheetByName('URLValidationResults');
  if (!doesSheetExist) {
    sheet.insertSheet('URLValidationResults');
    SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(1, 1).setValue("URL");
    SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(1, 2).setValue("Respone Message");
    SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(1, 3).setValue("Success");
    SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(1, 4).setValue("Is Unsafe");
    SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(1, 5).setValue("Domain");
    SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(1, 6).setValue("IP Address");
    SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(1, 7).setValue("Server");
    SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(1, 8).setValue("Content Type");
    SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(1, 9).setValue("Status Code");
    SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(1, 10).setValue("Page Size");
    SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(1, 11).setValue("Domain Rank");
    SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(1, 12).setValue("Is DNS Valid");
    SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(1, 13).setValue("Is Parked");
    SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(1, 14).setValue("Is Spamming");
    SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(1, 15).setValue("Is Malware");
    SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(1, 16).setValue("Is Phishing");
    SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(1, 17).setValue("Is Suspicious");
    SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(1, 18).setValue("Is Adult");
    SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(1, 19).setValue("Risk Score");
    SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(1, 20).setValue("Domain Age");
    SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(1, 21).setValue("Category");
    SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(1, 22).setValue("IPQS Request ID");
    prepData();
  }
  if (doesSheetExist) {
    let didSheetDelete = await deleteSheet();
    if (didSheetDelete) {
      createURLResultsSheet();
    }
  }
}

function validateURLs(filteredData, cycleCount) {
  if (filteredData.length > cycleCount) {
    try {
      const targetCell = cycleCount + 2;
      const apiString = "https://ipqualityscore.com/api/json/url/" + apiKey + "/" + encodeURIComponent(filteredData[cycleCount]);
      let response = UrlFetchApp.fetch(apiString);
      let responseData = JSON.parse(response.getContentText());
      SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(targetCell, 1).setValue(filteredData[cycleCount]);
      SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(targetCell, 2).setValue(responseData.message);
      SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(targetCell, 3).setValue(responseData.success);
      SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(targetCell, 4).setValue(responseData.unsafe);
      SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(targetCell, 5).setValue(responseData.domain);
      SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(targetCell, 6).setValue(responseData.ip_address);
      SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(targetCell, 7).setValue(responseData.server);
      SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(targetCell, 8).setValue(responseData.content_type);
      SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(targetCell, 9).setValue(responseData.status_code);
      SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(targetCell, 10).setValue(responseData.page_size);
      SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(targetCell, 11).setValue(responseData.domain_rank);
      SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(targetCell, 12).setValue(responseData.dns_valid);
      SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(targetCell, 13).setValue(responseData.parking);
      SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(targetCell, 14).setValue(responseData.spamming);
      SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(targetCell, 15).setValue(responseData.malware);
      SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(targetCell, 16).setValue(responseData.phishing);
      SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(targetCell, 17).setValue(responseData.suspicious);
      SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(targetCell, 18).setValue(responseData.adult);
      SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(targetCell, 19).setValue(responseData.risk_score);
      SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(targetCell, 20).setValue(responseData.domain_age.human);
      SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(targetCell, 21).setValue(responseData.category);
      SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(targetCell, 22).setValue(responseData.request_id);
    } catch (error) {
      SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(targetCell, 1).setValue('Error Validating');
      SpreadsheetApp.getActive().getSheetByName('URLValidationResults').getRange(targetCell, 2).setValue(error);
      Logger.log(error);
    } finally {
      cycleCount++;
      validateURLs(filteredData, cycleCount);
    }
  } else {
    flushAPIKey();
    ui.alert("Complete");
  }
}

