"use strict";

function ReadProfile() {
  var p = new ProfileParser();
  var base_url = GetProfileURL(false);
  var html = getURL_(base_url, false);
  if(html) {
    if (true || GetLastUpdated() !== getLastUpdated_(html)) {
      var profile = p.parse(html);
      updateProfile_(profile);
      return true;
    } else {
      if (uiAlert) {
        SpreadsheetApp.getUi().alert('Mods data is up to date.');
      }
    }
  }
  return false;
}

function readProfile_(uiAlert) {
  var p = new ProfileParser();
  var base_url = GetProfileURL(uiAlert);
  var html = getURL_(base_url, uiAlert);
  if(html) {
    if (GetLastUpdated() !== getLastUpdated_(html)) {
      var profile = p.parse(html);
      updateProfile_(profile);
      return true;
    } else {
      if (uiAlert) {
        SpreadsheetApp.getUi().alert('Mods data is up to date.');
      }
    }
  }
  return false;
}

function updateProfile_(profile) {
  writeAccountName_(profile.account);
  writePlayerName_(profile.player);
  writeGuild_(profile.guild);
  writeCommunities_(profile.communities);
  writeStats_(profile.infos.concat(profile.stats));
  writeLastUpdated_(profile.last_updated);
}

/**
 * Get profile's swgoh.gg address.
 *
 * @return The swgoh.gg address.
 * @customfunction
 */
function GetProfileURL(uiAlert) {
  var Range = SpreadsheetApp.getActive().getRangeByName('PlayerURL');
  if (Range === null) {
    // TODO: NamedRanges check
    //Logger.log(Range.getNumColumns());
  }
  return formatProfileURL_(Range.getValue(), uiAlert);
}

/**
 * Get profile's Last Updated value.
 *
 * @return The Last Updated value.
 * @customfunction
 */
function GetLastUpdated() {
  var Range = SpreadsheetApp.getActive().getRangeByName('LastUpdated');
  var value = Range.getValue();
  return value;
}

function writeLastUpdated_(value) {
  var Range = SpreadsheetApp.getActive().getRangeByName('LastUpdated');
  Range.setValue(value);
}

function writeAccountName_(value) {
  var Range = SpreadsheetApp.getActive().getRangeByName('AccountName');
  Range.setValue(value);
}

/**
 * Get player name.
 *
 * @return The player name.
 * @customfunction
 */
function GetPlayerName() {
  var Range = SpreadsheetApp.getActive().getRangeByName('PlayerName');
  var value = Range.getValue();
  return value;
}

function writePlayerName_(value) {
  var Range = SpreadsheetApp.getActive().getRangeByName('PlayerName');
  var values = Range.getValues();
  var previous = values[0][0];
  if(previous === value){
    values[0][1] = "";
  } else {
    values[0][0] = value;
    values[0][1] = previous;
  }
  Range.setValues(values);
}

function writeGuild_(tuple) {
  // var value = Utilities.formatString('=HYPERLINK("https://swgoh.gg%s","%s")', tuple[0], tuple[1]);
  var value = '=HYPERLINK("https://swgoh.gg' + tuple[0] + '","' + tuple[1] + '")';
  var Range = SpreadsheetApp.getActive().getRangeByName('Guild');
  var values = Range.getFormulas();
  var previous = values[0][0];
  if(previous === value){
    values[0][1] = "";
  } else {
    values[0][0] = value;
    values[0][1] = previous;
  }
  Range.setFormulas(values);
}

function writeCommunities_(communities) {

  // Drop "Guild" and "Joined" fields
  communities = communities.filter(function(community) {
    return community[0] !== "Guild" && community[0] !== "Joined";
  });

  var Range = SpreadsheetApp.getActive().getRangeByName('Communities');
  var rows = Range.getValues();
  var formulas = Range.getFormulas();
  rows.map(function(row, rowIndex) {
    formulas[rowIndex].map(function(formula, colIndex) {
      if (formula !== '') {
        rows[rowIndex][colIndex] = formula;
      }
    });
  });

  // Handle ally code
  var allyCodeLabel = rows[0][0];
  communities.some(function(community, idx, communities) {
    if (community[0] === allyCodeLabel) {
      var value = community[1];
      var previous = rows[0][1];
      if (previous === value) {
        rows[0][2] = "";
      } else {
        rows[0][1] = value;
        rows[0][2] = previous;
      }
      communities.splice(idx, 1);
      return true;
    }
    return false;
  });

  // Handle communities
  var previousRows = rows;
  rows = [previousRows.shift()];
  rows = rows.concat(communities.map(function(community) {
    community.push("");
    return community;
  }));
  // Pad the community array
  while(rows.length < 5) {
    rows.push([[""], [""], [""]]);
  }
  rows.forEach(function(row, idx, rows) {
    var key = row[0];
    var value = row[1];
    // handle hyperlinks
    if(key != "" && value.match(/>[^<]+</)) {
      // <a href="https://forums.galaxy-of-heroes.starwars.ea.com/profile/discussions/PopGoesTheWza" target="_blank">PopGoesTheWza</a>
      var match = /href="([^"]+)[^>]*>([^<]+)</.exec(value);
      if (match !== null) {
        value = '=HYPERLINK("' + match[1] + '","' + match[2] + '")';
        row[1] = value;  // TODO: make it an hyperlink
      }
//      value = value === null ? "" : value[1];
    }
    previousRows.some(function(previousRow) {
      if (previousRow[0] === key) {
        var previous = previousRow[1];
        var r = rows[idx];
        if (previous === value) {
          rows[idx][2] = "";
        } else {
          rows[idx][1] = value;
          rows[idx][2] = previous;
        }
        return true;
      }
      return false;
    });
  });
  Range.setValues(rows);
}

function writeStats_(stats) {
  var Range = SpreadsheetApp.getActive().getRangeByName('Stats');
  var rows = Range.getValues();
  stats.forEach(function(stat) {
    var key = stat[0];
    var value = stat[1];
    rows.some(function(row, idx, rows) {
      if (row[0] === key) {
        var previous = row[1];
        if (previous === value) {
          rows[idx][2] = "";
        } else {
          rows[idx][1] = value;
          rows[idx][2] = previous;
        }
        return true;
      }
      return false;
    });
  });
  Range.setValues(rows);
}

