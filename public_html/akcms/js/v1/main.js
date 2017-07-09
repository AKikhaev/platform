//menu extender
var mnuitems=$('#mnu');mnuitems.html(mnuitems.html().split('</li><li').join('</li> <li class="line"></li> <li'));
//fuel
var trans_costcalc = function(){
	var f1=$('#s_trns_calc_f1'),f2=$('#s_trns_calc_f2'),f3=$('#s_trns_calc_f3');
    var distance = f1.prop('value'),size = f2.prop('value'),koef = 1,res = 1;
    if (size<20){
        koef = 0.006;
        res = (distance*koef).toFixed(1);
        if (res < 0.6){ res = 0.6}
    } else if (size<30) {
        koef = 0.005;
        res = (distance*koef).toFixed(1);
        if (res < 0.5){ res = 0.5}
    } else {
        koef =  0.004;
        res = (distance*koef).toFixed(1);
        if (res < 0.4){ res = 0.4}
    }
	f3.prop('value',res);
};
$('#s_trns_calc_f1').keydown(function (e) {
	// Allow: backspace, delete, tab, escape, enter and .
	if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
		 // Allow: Ctrl+A
		(e.keyCode == 65 && e.ctrlKey === true) || 
		 // Allow: home, end, left, right
		(e.keyCode >= 35 && e.keyCode <= 39)) {
			 // let it happen, don't do anything
			 return;
	}
	// Ensure that it is a number and stop the keypress
	if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
		e.preventDefault();
	}
}).keyup(trans_costcalc);
$('#s_trns_calc_f2').change(trans_costcalc);
//Плавная прокрутка
$("a.scrollto").click(function () {
	elementClick = $(this).attr("href");
	destination = $(elementClick).offset().top-(15);
	$("html:not(:animated),body:not(:animated)").animate({scrollTop: destination}, 1100);
	return false;
});
