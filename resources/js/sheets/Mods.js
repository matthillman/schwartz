"use strict";

var MODS_SHEET_NAME = 'Mods';

// Read and parse swgoh.gg mod pages
function scrapeMods_(base_url, uiAlert) {
  var p = new ModParser();
  var page = 1;
  var mods = [];
  while (true) {
    var url = Utilities.formatString('%smods/?page=%s', base_url, page);
    var html = getURL_(url, uiAlert);
    if (html) {
      // Parse the page
      mods = mods.concat(p.parse(html));
      page = p.getNextPage();
      // Last page
      if (page === null) {
        break;
      }
    }
  }
//  return mods.length === 0 ? null : mods;
  return mods;
}

// Returns an array of all properties of an object
function getProperties_(obj) {
  var properties = [];
  for (var property in obj) {
    if (obj.hasOwnProperty(property)) {
      properties.push(property);
    }
  }
  return properties;
}

// Read mods from sheet
function readMods_() {
  var Spreadsheet = SpreadsheetApp.getActive();
  var Sheet = Spreadsheet.getSheetByName(MODS_SHEET_NAME);
  if (Sheet === null) {
    // TODO: NamedRanges check
    return;
  }
  var Range = Sheet.getDataRange();
  var Values = Range.getValues();
  var mods = [];
  var properties = Values.shift();
  mods = Values.map(function(row) {
    var obj = {};
    for (idx in properties) {
      var property = properties[idx];
      obj[property] = row[idx];
    }
    return obj;
  })
  return mods;
}

// Write mods to sheet
function writeMods_(mods) {
  var Spreadsheet = SpreadsheetApp.getActive();
  var Sheet = Spreadsheet.getSheetByName(MODS_SHEET_NAME);
  if (Sheet === null) {
    Sheet = Spreadsheet.insertSheet(MODS_SHEET_NAME);
  }
  Sheet.getDataRange().clear();

  if (mods && mods.length > 0) {
    var properties = getProperties_(mods[0])
    // add headers
    var values = [properties];
    // add mods
    mods.forEach(function(mod) {
      // read each property
      var row = properties.map(function(property) {
        return mod[property];
      });
      values.push(row);
    });
    var Range = Sheet.getRange(1, 1, mods.length + 1, properties.length);
    Range.setValues(values);
  }
}

/**
 * Imports mods from swgoh.gg if profile's Last Updated has changed.
 *
 * @customfunction
 */
function UI_ImportMods() {
  if (readProfile_(true)) {
    ImportMods();
  }
}

/**
 * Imports mods from swgoh.gg (bypass Last Updated test).
 *
 * @customfunction
 */
function ImportMods() {
  var uiAlert = true;
  var base_url = GetProfileURL(uiAlert);
  var mods = scrapeMods_(base_url, uiAlert);
  writeMods_(mods)
}

/**
 * Export mods to Crouching Rancor, after checking if profile's Last Updated has changed.
 *
 * @customfunction
 */
function UI_ExportMods() {
  if (readProfile_(false)) {
    var ui = SpreadsheetApp.getUi();
    var response = ui.alert('SWGoH.gg may have fresher data. Do you want to import them?', ui.ButtonSet.YES_NO);
    if (response === ui.Button.YES) {
      ImportMods();
    }
  }
  ExportMods()
}

/**
 * Export mods to Crouching Rancor.
 *
 * @customfunction
 */
function ExportMods() {
  var mods = readMods_();
  var indices = ['1', '2', '3', '4'];
  var specialTypes = ['Defense', 'Health', 'Offense', 'Protection'];
  // Drop the 'name' property and handle percentage vs whole values
  mods = mods.map(function(mod) {
    delete mod.name;
    indices.forEach(function(index) {
      var type = 'secondaryType_' + index;
      var value = 'secondaryValue_' + index;
      if (mod[type] && specialTypes.indexOf(mod[type]) >= 0) {
        if (mod[value].indexOf('%') >= 0) {
          mod[value] = mod[value].replace('%', '');
          mod[type] += ' %';
        }
      }
    })
    return mod;
  });
  // Create and display the export dialog
  var template = HtmlService.createTemplateFromFile('DownloadJson');
  template.json = JSON.stringify(mods);
  var lastUpdated = GetLastUpdated().split('T')[0];
  var playerName = GetPlayerName();
  var filename = Utilities.formatString('%s-%s-ModsManager', lastUpdated, playerName);
  template.filename = filename;
  SpreadsheetApp.getActive().show(template.evaluate());
}

