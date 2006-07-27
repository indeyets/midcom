// ** I18N

// Calendar FI language
// Author: Henri Bergius, <henri.bergius@iki.fi>
// Encoding: any
// Distributed under the same terms as the calendar itself.

// For translators: please use UTF-8 if possible.  We strongly believe that
// Unicode is the answer to a real internationalized world.  Also please
// include your contact information in the header, as can be seen above.

// full day names
Calendar._DN = new Array
("Sunnuntai",
 "Maanantai",
 "Tiistai",
 "Keskiviikko",
 "Torstai",
 "Perjantai",
 "Lauantai",
 "Sunnuntai");

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
("Su",
 "Ma",
 "Ti",
 "Ke",
 "To",
 "Pe",
 "La",
 "Su");

// full month names
Calendar._MN = new Array
("Tammikuu",
 "Helmikuu",
 "Maaliskuu",
 "Huhtikuu",
 "Toukokuu",
 "Kesäkuu",
 "Heinäkuu",
 "Elokuu",
 "Syyskuu",
 "Lokakuu",
 "Marraskuu",
 "Joulukuu");

// short month names
Calendar._SMN = new Array
("Tammi",
 "Helmi",
 "Maalis",
 "Huhti",
 "Touko",
 "Kesä",
 "Heinä",
 "Elo",
 "Syys",
 "Loka",
 "Marras",
 "Joulu");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "Tietoa kalenterista";

Calendar._TT["ABOUT"] =
"DHTML Date/Time Selector\n" +
"(c) dynarch.com 2002-2003\n" + // don't translate this this ;-)
"Viimeisin versio: http://dynarch.com/mishoo/calendar.epl\n" +
"Saatavilla GNU LGPL-lisenssillä. Ks. http://gnu.org/licenses/lgpl.html." +
"\n\n" +
"Päivämäärän valinta:\n" +
"- Käytä \xab, \xbb nappeja valitaksesi vuoden\n" +
"- Köytä " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " nappeja valitaksesi kuukauden\n" +
"- Pidä hiirtä pohjassa valitaksesi nopeammin.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Ajan valinta:\n" +
"- Klikkaa mitä tahansa kellonajan osaa lisätäksesi sitä\n" +
"- tai Shift-klikkaa vähentääksesi sitä\n" +
"- tai paina ja vedä nopeuttaaksesi valintaa.";

Calendar._TT["PREV_YEAR"] = "Edellinen vuosi";
Calendar._TT["PREV_MONTH"] = "Edellinen kuukausi";
Calendar._TT["GO_TODAY"] = "Tänään";
Calendar._TT["NEXT_MONTH"] = "Seuraava kuukausi";
Calendar._TT["NEXT_YEAR"] = "Seuraava vuosi";
Calendar._TT["SEL_DATE"] = "Valitse päivämäärä";
Calendar._TT["DRAG_TO_MOVE"] = "Vedä liikuttaaksesi";
Calendar._TT["PART_TODAY"] = " (tämä päivä)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "Näytä %s ensin";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "1,7";

Calendar._TT["CLOSE"] = "Sulje";
Calendar._TT["TODAY"] = "Tänään";
Calendar._TT["TIME_PART"] = "Klikkaa tai vedä muuttaaksesi arvoa";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y-%m-%d";
Calendar._TT["TT_DATE_FORMAT"] = "%a, %b %e";

Calendar._TT["WK"] = "vko";
Calendar._TT["TIME"] = "Aika:";
