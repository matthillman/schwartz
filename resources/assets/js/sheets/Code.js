/**
 * @OnlyCurrentDoc
 */

"use strict";

function onOpen(e) {
  buildMenu();
  readProfile_(false);
}

function onEdit(e) {
  var urlRange = SpreadsheetApp.getActive().getRangeByName('PlayerURL');
  var range = e.range;  // Range
  if (range.getSheet().getSheetId() === urlRange.getSheet().getSheetId() && range.getA1Notation() === urlRange.getA1Notation()) {
    var value = e.value;
    var url = formatProfileURL_(value, false);
    if (url) {
      if (value !== url) {
        range.setValue(url);
      }
    } else {
      if (value) {
        range.setValue('<<< enter an swgoh.gg profile url >>>');
      }
    }
  }
}

/**
 * Builds the UI custom menu.
 *
 * @customfunction
 */
function buildMenu() {
  var ui = SpreadsheetApp.getUi();
  ui.createMenu('ModsManager')
    .addItem('Import mods from SWGoH.gg', 'UI_ImportMods')
    .addItem('Export mods for Crouching Rancor', 'UI_ExportMods')
    .addToUi();
}

function formatProfileURL_(value, uiAlert) {
  var url = null;
  var match = /swgoh\.gg\/u\/([^\/]+)/i.exec(value);
  if (match) {
    url = Utilities.formatString('https://swgoh.gg/u/%s/', match[1]);
  }
  if (uiAlert && url === null) {
    SpreadsheetApp.getUi().alert('Your SWGoH.gg profile URL does not look correct');
    return false;
  }
  return url;
}

function getLastUpdated_(html) {
  var re = /data-datetime="([^"]+)"/m;
  var value = re.exec(html);
  if (value !== null) {
    return value[1].trim();
  }
}

function getURL_(url, uiAlert) {
  try {
    return UrlFetchApp.fetch(url).getContentText();
  } catch (e) {
    if (uiAlert) {
      // 404 Page not found
      if (e.message.indexOf('code 404') >= 0) {
        SpreadsheetApp.getUi().alert(Utilities.formatString('Page not found: %s', url));
      } else {
        SpreadsheetApp.getUi().alert(Utilities.formatString('Unkown error: %s', e));
      }
    }
    Logger.log(e);
  }
}

