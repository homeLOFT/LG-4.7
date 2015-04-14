/*
71ae8da1574e2d17ed36dafb837750799af54781, v1 (xcart_4_6_0), 2013-04-05 15:51:07, klarna_popup_address.js, random
vim: set ts=2 sw=2 sts=2 et:
*/
function klarna_popup_address() {
	var ssn = document.getElementById("place_user_ssn").value;
	popupOpen("klarna_popup_address.php?ssn=" + ssn);
}
