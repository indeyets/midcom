#Delete all fi.protiie.gargabetruck records
delete from fi_protie_garbagetruck_area where id>0;
delete from repligard where realm="fi_protie_garbagetruck_area";
delete from fi_protie_garbagetruck_log where id>0;
delete from repligard where realm="fi_protie_garbagetruck_log";
delete from fi_protie_garbagetruck_route where id>0;
delete from repligard where realm="fi_protie_garbagetruck_route";
delete from fi_protie_garbagetruck_vehicle where id>0;
delete from repligard where realm="fi_protie_garbagetruck_vehicle";
