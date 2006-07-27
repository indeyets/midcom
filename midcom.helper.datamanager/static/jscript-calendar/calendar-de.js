// ** I18N

// Calendar EN language
// Author: Mihai Bazon, <mishoo@infoiasi.ro>
// Encoding: any
// Distributed under the same terms as the calendar itself.

// For translators: please use UTF-8 if possible.  We strongly believe that
// Unicode is the answer to a real internationalized world.  Also please
// include your contact information in the header, as can be seen above.

// full day names
Calendar._DN = new Array
("Sonntag",
 "Montag",
 "Dienstag",
 "Mittwoch",
 "Donnerstag",
 "Freitag",
 "Samstag",
 "Sonntag");

// Please note that the following array of short day names (and the same goes
// for short month names, _SMN) isn't absolutely necessary.  We give it here
// for exemplification on how one can customize the short day names, but if
// they are simply the first N letters of the full name you can simply say:
//
//   Calendar._SDN_len = N; // short day name length
//   Calendar._SMN_len = N; // short month name length
//
// If N = 3 then this is not needed either since we assume a value of 3 if not
// present, to be compatible with translation files that were written before
// this feature.

// short day names
Calendar._SDN = new Array
("Son",
 "Mon",
 "Die",
 "Mit",
 "Don",
 "Fre",
 "Sam",
 "Son");

// full month names
Calendar._MN = new Array
("Januar",
 "Februar",
 "Maerz",
 "April",
 "Mai",
 "Juni",
 "Juli",
 "August",
 "September",
 "Oktober",
 "November",
 "Dezember");

// short month names
Calendar._SMN = new Array
("Jan",
 "Feb",
 "Maer",
 "Apr",
 "Mai",
 "Jun",
 "Jul",
 "Aug",
 "Sep",
 "Okt",
 "Nov",
 "Dez");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "Ueber den Kalender";

Calendar._TT["ABOUT"] =
"DHTML Date/Time Selector\n" +
"(c) dynarch.com 2002-2003\n" + // don't translate this this ;-)
"For latest version visit: http://dynarch.com/mishoo/calendar.epl\n" +
"Distributed under GNU LGPL.  See http://gnu.org/licenses/lgpl.html for details." +
"\n\n" +
"Datum:\n" +
"- Mit den \xab, \xbb Schaltflaechen das Jahr auswaehlen\n" +
"- Mit den " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " den Montat auswaehlen\n" +
"- Die Maustaste gedrueckt halten, um schnell zu blaettern.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Zeit:\n" +
"- Auf die Zeitfelder klicken, um den Wert zu erhoehen\n" +
"- Mit Umschalten klicken verringert den Wret\n" +
"- Es kann auch bei gedrueckter Mausstaste gezogen werden.";

Calendar._TT["PREV_YEAR"] = "Letztes Jahr (festhalten fuer Menue)";
Calendar._TT["PREV_MONTH"] = "Vorheriges Jahr (festhalten fuer Menue)";
Calendar._TT["GO_TODAY"] = "Heute";
Calendar._TT["NEXT_MONTH"] = "Naechster Monat (festhalten fuer Menue)";
Calendar._TT["NEXT_YEAR"] = "Vorheriger Monat (festhalten fuer Menue)";
Calendar._TT["SEL_DATE"] = "Datum auswaehlen";
Calendar._TT["DRAG_TO_MOVE"] = "Ziehen um zu verschieben";
Calendar._TT["PART_TODAY"] = " (heute)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "%s als ersten Wochentag anzeigen";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "Schliessen";
Calendar._TT["TODAY"] = "Heute";
Calendar._TT["TIME_PART"] = "(Umschalten-)Klicken oder Ziehen um den Wert zu veraendern";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y-%m-%d";
Calendar._TT["TT_DATE_FORMAT"] = "%a, %e. %b";

Calendar._TT["WK"] = "Woche";
Calendar._TT["TIME"] = "Zeit:";
