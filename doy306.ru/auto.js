function getElementById (id) {
	if (navigator.appName == 'Microsoft Internet Explorer') {
		return document.all[id];
	} else {
		return document.getElementById(id);
	}
}


function rowswitch (index)
{
	var state = getElementById('row' + index + 'a').style.display != 'none';
	if (state)
	{
		getElementById('row' + index + 'a').style.display = 'none';
		getElementById('row' + index + 'b').style.display = 'none';
		getElementById('row' + index + 'c').style.display = 'none';
		getElementById('row' + index + 'z').innerText = '+';
	} else
	{
		getElementById('row' + index + 'a').style.display = '';
		getElementById('row' + index + 'b').style.display = '';
		getElementById('row' + index + 'c').style.display = '';
		getElementById('row' + index + 'z').innerText = '-';
	}
}
