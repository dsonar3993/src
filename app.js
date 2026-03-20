const CANDIDATES_SHEET = "Candidates";
const CLIENTS_SHEET = "Clients";
const ACCOUNTS_SHEET = "Accounts";
const SHEET_ID = "1N_nBLEkX80rkK79thJryfag8UE_m50mMsM714HfdqPw";

/* =========================
   ROUTING
========================= */
function doGet(e) {
  try {
    const page = e && e.parameter ? e.parameter.page : null;

    switch (page) {
      case 'clients_view': return render('clients_view');
      case 'clients_add': return render('clients_add');
      case 'candidates_view': return render('candidates_view');
      case 'candidates_add': return render('candidates_add');
      case 'accounts_view': return render('esf_view');
      case 'accounts_add': return render('esf_add');
      default: return render('index');
    }
  } catch (err) {
    Logger.log("doGet ERROR: " + err);
    return HtmlService.createHtmlOutput(
      "<h2>Page Render Error</h2><pre>" + err + "</pre>"
    );
  }
}

function render(file) {
  return HtmlService.createTemplateFromFile(file).evaluate();
}

function getPageUrl(pageName) {
  return ScriptApp.getService().getUrl() + '?page=' + pageName;
}

function include(filename) {
  return HtmlService.createTemplateFromFile(filename).evaluate().getContent();
}

function formatDateValue(value) {
  if (!value) return "";

  const date = value instanceof Date ? value : new Date(value);
  if (Number.isNaN(date.getTime())) return "";

  return date.toISOString().split("T")[0];
}

/* =========================
   GET DATA
========================= */
function getCandidates() {
  try {
    const sheet = SpreadsheetApp.openById(SHEET_ID).getSheetByName(CANDIDATES_SHEET);
    if (!sheet) throw new Error("Candidates sheet not found");
    Logger.log("Candidates sheet: " + sheet.getName());
    const lastRow = sheet.getLastRow();
    const lastCol = sheet.getLastColumn();

    if (lastRow < 2) return [];

    const data = sheet.getRange(2, 1, lastRow - 1, lastCol).getValues();

    const mapped = data.map(row => ({
      cid: row[0] || "",
      name: row[1] || "",
      gender: row[2] || "",
      email: row[3] || "",
      mobile: row[4] || "",
      dob: formatDateValue(row[5]),
      altm: row[6] || "",
      emgNum: row[7] || "",
      emgName: row[8] || "",
      skillsp: row[9] || "",
      skillss: row[10] || "",
      marital: row[11] || "",
      panno: row[12] || "",
      expt: row[13] || "",
      expr: row[14] || "",
      caddr: row[15] || "",
      paddr: row[16] || "",
      insures: row[17] || "",
      insured: row[18] || ""
    }));

    Logger.log("Candidates fetched: " + mapped.length);
    return mapped;

  } catch (err) {
    Logger.log("getCandidates ERROR: " + err);
    throw err;
  }
}

function getClients() {
  try {
    const sheet = SpreadsheetApp.openById(SHEET_ID).getSheetByName(CLIENTS_SHEET);
    if (!sheet) throw new Error("Clients sheet not found");

    const lastRow = sheet.getLastRow();
    const lastCol = sheet.getLastColumn();

    if (lastRow < 2) return [];

    const data = sheet.getRange(2, 1, lastRow - 1, lastCol).getValues();

    const mapped = data.map(row => ({
      cid: row[0] || "",
      name: row[1] || "",
      email: row[2] || "",
      mobile: row[3] || "",
      city: row[4] || "",
      country: row[5] || "",
      industry: row[6] || "",
      requirebgv: row[7] || "",
      billingtype: row[8] || "",
      burden: row[9] || "",
      workingdays: row[10] || ""
    }));

    Logger.log("Clients fetched: " + mapped.length);
    return mapped;

  } catch (err) {
    Logger.log("getClients ERROR: " + err);
    throw err;
  }
}

/* =========================
   ID GENERATORS
========================= */
function generateCandidateId(sheet) {
  if (!sheet) throw new Error("Candidates sheet not found");
  const lastRow = sheet.getLastRow();
  if (lastRow < 2) return "CAND-001";

  const lastId = String(sheet.getRange(lastRow, 1).getValue() || "");
  const parts = lastId.split("-");
  const num = parts.length > 1 ? parseInt(parts[1], 10) : 0;

  return "CAND-" + String((num || 0) + 1).padStart(3, "0");
}

function generateClientId(sheet) {
  if (!sheet) throw new Error("Clients sheet not found");
  const lastRow = sheet.getLastRow();
  if (lastRow < 2) return "CLI-001";

  const lastId = String(sheet.getRange(lastRow, 1).getValue() || "");
  const parts = lastId.split("-");
  const num = parts.length > 1 ? parseInt(parts[1], 10) : 0;

  return "CLI-" + String((num || 0) + 1).padStart(3, "0");
}

function generateAccountId(sheet) {
  if (!sheet) throw new Error("Accounts sheet not found");
  const values = sheet.getRange("A2:A").getValues().flat().filter(String);

  if (values.length === 0) return "ACC-001";

  const lastId = String(values[values.length - 1] || "");
  const parts = lastId.split("-");
  const num = parts.length > 1 ? parseInt(parts[1], 10) : 0;

  return "ACC-" + String((num || 0) + 1).padStart(3, "0");
}

/* =========================
   SUBMIT FUNCTIONS
========================= */
function submitCandidate(data) {
  const sheet = SpreadsheetApp.openById(SHEET_ID).getSheetByName(CANDIDATES_SHEET);
  if (!sheet) throw new Error("Candidates sheet not found");
  const id = generateCandidateId(sheet);

  sheet.appendRow([
    id,
    data.candidateName,
    data.gender,
    data.email,
    data.mobile,
    data.dob,
    data.altMobile,
    data.emergencyNumber,
    data.emergencyPerson,
    data.primarySkills,
    data.secondarySkills,
    data.maritalStatus,
    data.pan,
    data.totalExp,
    data.relevantExp,
    data.presentAddress,
    data.permanentAddress,
    data.selfInsurance,
    data.dependantsInsurance
  ]);

  return { success: true };
}

function submitClient(data) {
  const sheet = SpreadsheetApp.openById(SHEET_ID).getSheetByName(CLIENTS_SHEET);
  if (!sheet) throw new Error("Clients sheet not found");
  const id = generateClientId(sheet);

  sheet.appendRow([
    id,
    data.clientName,
    data.email,
    data.mobile,
    data.city,
    data.country,
    data.industry,
    data.requirebgv,
    data.billingType,
    data.burden,
    data.workingDays
  ]);

  return { success: true };
}

function submitAccount(data) {
  const sheet = SpreadsheetApp.openById(SHEET_ID).getSheetByName(ACCOUNTS_SHEET);
  if (!sheet) throw new Error("Accounts sheet not found");
  const id = generateAccountId(sheet);

  sheet.appendRow([
    id,
    data.candidateName,
    data.recruiterName,
    data.clientName,
    data.dateOfJoining,
    data.employmentMode,
    data.designation,
    data.billingType,
    data.leavesPerAnnum,
    data.billRate,
    data.workingPerAnnum,
    data.workLocation,
    data.monthlyBillRate,
    data.contractPeriod,
    data.bgvRequired,
    data.subconPer,
    data.subconAmount,
    data.prevCTC,
    data.bonus,
    data.ctcOffered,
    data.bonusAmt,
    data.ctcPerMonth,
    data.bonusPayTenure,
    data.hikeGiven,
    data.marginPer,
    data.burdenPer,
    data.burdenAmt,
    data.approval,
    data.grossMargin
  ]);

  return { success: true };
}