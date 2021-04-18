/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL,
	as published by the Free Software Foundation, either version 3
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
var _focus;
var _hotkeys = {
	'alt': false,	// whether is the Alt key pressed
	'list': false, // list of all elements with hotkey used recently
	'focus': -1		// currently selected list element
};

/**
 * ADDED AUTO-COMPLETE SELECT BOX BY ANOOP MB.
 * http://frontaccounting.com/punbb/viewtopic.php?id=6198
 */

/*<----------MBCODE START------------>*/

function mbselect(elm)
{
	var mbc = '';
	var selectedvalue = null;
	var selectedoption = null;
	var selectedoptionkey = null;
	var dispayitems = 0;
	var optgropc = 0;
	var selectedoptionold;
	var selectedvalueold;
	var selectedoptionkeyold;

	var options = elm.getElementsByTagName('option');
	if (options.length != 0) {
		var rep="";
		var tit="";
		for (var j = 0; j < options.length; j++) {

			if (options[j].selected) { // check if options is selected

				selectedvalue = options[j].getAttribute('value');
				selectedoption = document.createElement('LI');
				selectedoption.className = "aselected";
				selectedoption.id = options[j].getAttribute('value');
				selectedoption.innerHTML = options[j].text;
				selectedoptionkey = j;

			}
			if(options[j].parentNode.nodeName.toLowerCase() === 'optgroup' && rep!=options[j].parentNode.getAttribute('label')){
				var optgroup=options[j].parentNode;
				var l =optgroup.getAttribute('label');
				mbc += '<li id="' + l.toUpperCase() + '" class="abbrs">' + l.toUpperCase() + '</li>';
				rep=l;
				tit=l;
				dispayitems++;
			}
			mbc += '<li title="'+tit+'" class="anoselect" id="' + options[j].getAttribute('value') + '">' + options[j].text + '</li>';
			dispayitems++;
		}
	}
	else {
		mbc +='<li class="abbrs">No Options</li>';
		selectedoption = document.createElement('LI');
		selectedoption.innerHTML ='';
		dispayitems++;
	}

	var ih=elm.offsetHeight;
	var iw=elm.offsetWidth;
	elm.style.display = 'none';
	elm.setAttribute("style","display:none");
	var wrapper = document.createElement('div');
	wrapper.setAttribute("style","display:inline-block;height:"+ih+"px;");
	wrapper.style.display = 'inline-block';
	wrapper.style.height =ih+"px";
	var typer = document.createElement('INPUT');
	typer.setAttribute("type","text");
	typer.type = 'text';
	typer.setAttribute("class","typer");
	typer.setAttribute("autocomplete","off");
	typer.className = "typer";
	typer.setAttribute("name",elm.getAttribute("name")+"mselect");
	var box_name =elm.getAttribute("rel");
    if (box_name != null) {
        typer.setAttribute("rel",box_name);
        var box = document.getElementsByName(elm.getAttribute('rel'))[0];
        box.setAttribute("rel",elm.getAttribute("name")+"mselect");
        box.value='';
    }
	typer.setAttribute("style","display:inline-block;width:"+iw+"px");
	typer.style.display = 'inline-block';
	typer.setAttribute("style","width:"+iw+"px");
	typer.style.width =iw+"px";
	typer.value = decodeHtml(selectedoption.innerHTML);
	typer.setAttribute("value",typer.value);
	var hidden = document.createElement('INPUT');
	hidden.setAttribute("type","hidden");
	hidden.type = 'hidden';
	hidden.setAttribute("name",elm.getAttribute("name"));
	hidden.value = selectedvalue;

	var totitems=dispayitems;
	elm.parentNode.insertBefore(wrapper, elm);
	var arrow = document.createElement('SPAN');
	arrow.innerHTML = '\u25BC';
	arrow.setAttribute("class","mb");
	arrow.className = "mb";
	wrapper.appendChild(typer);
	wrapper.appendChild(arrow);
	wrapper.appendChild(hidden);
	elm.parentNode.removeChild(elm);

	var popup = document.createElement('div');
	var list = popup.getElementsByTagName('LI');

       typer.select();

	function pop(disp, cont) {
		disp = disp || 'none';
		cont = cont || null;
		var rect = typer.getBoundingClientRect();

		var left = rect.left;
		var top = rect.bottom;
		var width = typer.offsetWidth-2;

		popup.className += " popups";
		var contentmb = '<div class="lister"><ul class="mselect-rset">';

		contentmb += mbc;
		contentmb += '</ul></div>';
		popup.innerHTML = contentmb;
		if (cont != null) {

			var opts = [];
			var ullist = popup.getElementsByTagName('UL')[0];
			var hlino = ullist.querySelectorAll('li.abbrs').length;
			var gid = '#$%#';
			var cu=0;
			var co=0;
			var ck=0;
			var vk=0;
			for (var nn = 0; nn < list.length; nn++) {

				if (cont.test(list[nn].innerHTML)) {

					if (list[nn].className == 'abbrs') {
						opts.push('<li class="abbrs">' + list[nn].innerHTML.replace(cont, "<u>$1</u>") + '</li>');
						gid = list[nn].id.toLowerCase();

						cu=1;
					}else{
						if(cu!=1 &&ck!=list[nn].title &&hlino!=0){
							opts.push('<li class="abbrs">'+list[nn].title.toUpperCase()+'</li>');
							ck=list[nn].title;

						}
						if(vk!=1) {
							opts.push('<li class="aselected"  id="' + list[nn].id+'">' + list[nn].innerHTML.replace(cont, "<u>$1</u>") + '</li>');
							vk=1;
							selectedoption=list[nn];
							selectedoptionkey=nn;
							selectedvalue=list[nn].id;
						}
						else
						{
							opts.push('<li class="anoselect "  id="' + list[nn].id+'">' + list[nn].innerHTML.replace(cont, "<u>$1</u>") + '</li>');

						}
						co=1;

					}

				}

				if(gid==list[nn].title.toLowerCase() && co!=1)
				{
					if(vk!=1) {
						opts.push('<li class="aselected"  id="' + list[nn].id+'">'+list[nn].innerHTML+'</li>');
						vk=1;
						selectedoption=list[nn];
						selectedoptionkey=nn;
						selectedvalue=list[nn].id;
					}
					else
					{
						opts.push('<li class="anoselect"  id="' + list[nn].id+'">'+list[nn].innerHTML+'</li>');
					}
				}
			}
			totitems =opts.length;
			if(opts.length==0){
				opts[0]='<li class="abbrs">Nothing Found</li>';
				selectedoption=selectedoptionold;
				selectedoptionkey=selectedoptionkeyold;
			}

			//alert(opts.join(""));
			ullist.innerHTML = ullist.innerHTML.replace(ullist.innerHTML,opts.join(""));
		}
		popup.style.position = "fixed";
		popup.style.left = left + "px";
		popup.style.top = top + "px";
		popup.style.width = width + "px";
		typer.parentNode.appendChild(popup);
		popup.style.display = disp;
		optgropc = 0;
		list = popup.getElementsByTagName('LI');

		function clear() {

			for (var mm = 0; mm < list.length; mm++) {
				if (list[mm].className != 'abbrs') {
					list[mm].className = ' anoselect';
				}
			}

		}

		function setlistindex() {
			clear();
			for (var mm = 0; mm < list.length; mm++) {

				if (list[mm].className != 'abbrs') {

					if (selectedoption.id == list[mm].id) {
						list[mm].className = ' aselected';
						list[mm].scrollIntoView(false);
					}
				}
			}

		}
		selectedoptionold =selectedoption;
		selectedvalueold =selectedvalue;
		selectedoptionkeyold =selectedoptionkey;
		if(list.length>0) {
			setlistindex();
			for (var mm = 0; mm < list.length; mm++) {
				(function (mm) {
					if (list[mm].className != 'abbrs') {
						list[mm].onmousedown = function (event) {
							typer.value = decodeHtml(this.innerHTML);
							hidden.value = this.id;
							selectedoption = this;
							selectedvalue = this.id;
							selectedoptionkey = mm;
							selectedoptionold = selectedoption;
							selectedvalueold = selectedvalue;
							selectedoptionkeyold = selectedoptionkey;
							typer.blur();

							popup.style.display = 'none';

							//JsHttpRequest.request(hidden);
							//_update_box(hidden);

                            if (validate(hidden)) {
                                var sname = '_' + hidden.name + '_update';
                                var update = document.getElementsByName(sname)[0];
                                if (update) {
                                    JsHttpRequest.request(update);
                                }
                            }
							return false;

						};
						list[mm].onmouseover = function (event) {
							clear();
							optgropc = 0;
							selectedoption = this;
							selectedvalue = this.id;
							selectedoptionkey = mm;
							this.className = ' aselected';

						};
						list[mm].onmouseout = function (event) {

							selectedoption = selectedoptionold;
							selectedvalue = selectedvalueold;
							selectedoptionkey = selectedoptionkeyold;
							optgropc = 0;
							this.className = ' anoselect';
						};

					}
					else {
						list[mm].onmousedown = function (event) {
							typer.focus();
							optgropc = 1;

						}
					}

				})(mm);
			}
		}
	}
    typer.setvalue = function(id) {
        for (var mm = 0; mm < list.length; mm++) {
                if (id == list[mm].id) {
                    selectedoption = list[mm];
                    selectedvalue = id;
                    list[mm].className = ' aselected';
                    list[mm].scrollIntoView(false);
                }
        }
        this.value = decodeHtml(selectedoption.innerHTML);
		this.select();
		this.focus();
    }

	typer.onmousedown = function (event) {
		pop("block");
	};
	typer.onfocus = function (event) {
		save_focus(typer);
		typer.select();
	};
	window.onresize = function (event) {
		popcl();
	};
	typer.onblur = function (event) {

		if (optgropc != 1) {
			pop("none");
			optgropc = 0;
			if (typer.value == null || typer.value == '' ||typer.value!=selectedoption.innerHTML) {
				typer.value = decodeHtml(selectedoption.innerHTML);
				hidden.value = selectedvalue;

			}
		} else {
			optgropc = 0;
			this.focus();
		}
	};
	typer.onmouseout = function (event) {
		optgropc = 0;
	};
	arrow.onclick = function(event) {
		typer.select();
		typer.focus();
		pop("block");

	};
	typer.onkeydown = function (event) {
		event = event || window.event;

		if (event.which == 40) {

			if(totitems>0){

				var listsel = popup.querySelectorAll('li.aselected');
				if(selectedoptionkey>list.length)selectedoptionkey=0;
				if(list[selectedoptionkey].className=='abbrs')selectedoptionkey++;

				selectedoptionkey++;

				if(selectedoptionkey==list.length)selectedoptionkey=0;
				if(list[selectedoptionkey].className=='abbrs')selectedoptionkey++;
				listsel[0].className=listsel[0].className.replace('aselected','anoselect');

				list[selectedoptionkey].className='aselected';
				selectedoption = list[selectedoptionkey];
				selectedvalue = list[selectedoptionkey].id;
				selectedoption.scrollIntoView(false);

			}

			event.cancelBubble = true;
			if (navigator.appName != 'Microsoft Internet Explorer')	{
				event.preventDefault();
				event.stopPropagation();
			}
			return false;
		}
		if (event.which == 38) {

			if(totitems>0){
				var listsel = popup.querySelectorAll('li.aselected');
				if(selectedoptionkey>list.length)selectedoptionkey=0;
				if(list[selectedoptionkey].className=='abbrs')selectedoptionkey--;
				selectedoptionkey--;
				if(list[0].className=='abbrs')
				{
					if(selectedoptionkey<1)selectedoptionkey=list.length-1;
				}else
				{
					if(selectedoptionkey<0)selectedoptionkey=list.length-1;
				}

				if(list[selectedoptionkey].className=='abbrs')selectedoptionkey--;
				listsel[0].className=listsel[0].className.replace('aselected','anoselect');
				list[selectedoptionkey].className='aselected';
				selectedoption = list[selectedoptionkey];
				selectedvalue = list[selectedoptionkey].id;
				selectedoption.scrollIntoView(false);

			}

			event.cancelBubble = true;
			if (navigator.appName != 'Microsoft Internet Explorer')	{
				event.preventDefault();
				event.stopPropagation();
			}
			return false;
		}

		    key = event.keyCode||event.which;
		    var box = document.getElementsByName(this.getAttribute('rel'))[0];
//alert(box.className + ":" + box.name);
            // F1 or ctrl-B (used for barcode reader) activates search
		    if (box && (key == 112 || (key == 66 && event.ctrlKey)) ) {
                this.style.display = 'none';
                box.style.display = 'inline';
                box.value='';
                setFocus(box);
			    return false;
            }
	}
	typer.onkeypress = function (event) {
		event = event || window.event;
		if (event.which == 13) {
			selectedoptionold =selectedoption;
			selectedvalueold =selectedvalue;
			selectedoptionkeyold =selectedoptionkey;
			typer.value = decodeHtml(selectedoption.innerHTML);
			hidden.value = selectedvalue;
			typer.blur();
			popcl();
			//JsHttpRequest.request(hidden );
			//_update_box(hidden);
            if (validate(hidden)) {
                var sname = '_'+hidden.name+'_update';
                var update = document.getElementsByName(sname)[0];
                if(update) {
                    JsHttpRequest.request(update);
                }
            }
			return false;
		}
	};
	typer.onkeyup=function(event){
		if(event.which!=13&&event.which!=16&&event.which!=17&&event.which!=18&&event.which!=19&&event.which!=20
				&&event.which!=27&&event.which!=35&&event.which!=36&&event.which!=37&&event.which!=38
				&&event.which!=39&&event.which!=40&&event.which!=91&&event.which!=93&&event.which!=224
				&&event.which!=16&&event.which!=9){
			if (typer.value != '' && typer.value != null) {
				var tes = new RegExp('(' + escapeRegExp(typer.value) + ')', "i");
				pop('block', tes);

			}
			else {
				pop('block');
			}

		}else{

			return false;
		}
	}
	var myEfficientFn = debounce(function () {
		var rect = typer.getBoundingClientRect();
		var left = rect.left;
		var top = rect.bottom;
		popup.style.left = left + "px";
		popup.style.top = top + "px";
	}, 5);

	window.addEventListener('scroll', myEfficientFn);

}

function debounce(func, wait, immediate) {
	var timeout;
	return function() {
		var context = this, args = arguments;
		var later = function() {
			timeout = null;
			if (!immediate) func.apply(context, args);
		};
		var callNow = immediate && !timeout;
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
		if (callNow) func.apply(context, args);
	};
};

function popcl() {
	// All the elements with class close or fade.
	var els = document.querySelectorAll('.popups');
	var ins = document.querySelectorAll('input.typer');
	// Loop through the list.
	for (var i = 0; i < els.length; i++) {
		// Hide them.
		els[i].style.display = 'none';
	}
	for (var i = 0; i < ins.length; i++) {
		// Blur them.
		ins[i].blur();
	}

}

function escapeRegExp(str) {
    return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
}

function decodeHtml(html) {
    var txt = document.createElement("textarea");
    html = html.replace(/\<u\>/ig, '');
    html = html.replace(/\<\/u\>/ig, '');
    txt.innerHTML = html;
    return txt.value;
}

/*<----------MBCODE END------------>*/

function validate(e) {
	if (e.name && (typeof _validate[e.name] == 'function'))
		return _validate[e.name](e);
	else {
		var n = e.name.indexOf('[');
		if(n!=-1) {
			var key = e.name.substring(n+1, e.name.length-1);
			if (key.length>1 && _validate[e.name.substring(0,n)])
				return _validate[e.name.substring(0,n)][key](e);
		}
	}
	return true;
}

function set_fullmode() {
	document.getElementById('ui_mode').value = 1;
	document.loginform.submit();
	return true;
}

function save_focus(e) {
  _focus = e.name||e.id;
  var h = document.getElementById('hints');
  if (h) {
	h.style.display = e.title && e.title.length ? 'inline' : 'none';
	h.innerHTML = e.title ? e.title : '';
  }
}

function _expand(tabobj) {

  var ul = tabobj.parentNode.parentNode;
  var alltabs=ul.getElementsByTagName("button");
  var frm = tabobj.form;

  if (ul.getAttribute("rel")){
	for (var i=0; i<alltabs.length; i++){
	  alltabs[i].className = "ajaxbutton"  //deselect all tabs // Review CP 2014-11 This will remove all other classes from the element.
	}
	tabobj.className = "current";
	JsHttpRequest.request(tabobj)
  }
}

//interface for selecting a tab (plus expand corresponding content)
function expandtab(tabcontentid, tabnumber) {
  var tabs = document.getElementById(tabcontentid);
 _expand(tabs.getElementsByTagName("input")[tabnumber]);
}

function _set_combo_input(e) {
		e.setAttribute('_last', e.value);
		e.onblur=function() {
		  var but_name = this.name.substring(0, this.name.length-4)+'button';
		  var button = document.getElementsByName(but_name)[0];
		  var select = document.getElementsByName(this.getAttribute('rel'))[0];
		  save_focus(select);
// submit request if there is submit_on_change option set and
// search field has changed.

          if (button != null && (this.value != this.getAttribute('_last'))) {
            JsHttpRequest.request(button);
          } else if(string_contains(this.className, 'combo2')) {
                this.style.display = 'none';
                select.style.display = 'inline';
                setFocus(select);
		  }
		  return false;
		};
		e.onkeyup = function(ev) {
			var select = document.getElementsByName(this.getAttribute('rel'))[0];
			if(select && select.selectedIndex>=0) {
			  var len = select.length;
			  var byid = string_contains(this.className, 'combo') || string_contains(this.className, 'combo3');
			  var ac = this.value.toUpperCase();
			  select.options[select.selectedIndex].selected = false;
			  var selIndex = -1;
			  for (i = 0; i < len; i++) {
				var txt = byid ? select.options[i].value : select.options[i].text;
				if (string_contains(this.className, 'combo3')) {
				  if(txt.toUpperCase().indexOf(ac) == 0) {
					selIndex=i;
				  	break;
				  }
				} else {
				  if(txt.toUpperCase().indexOf(ac) >= 0) {
					selIndex=i;
					select.options[i].removeAttribute("hidden");
				  } else
					select.options[i].setAttribute("hidden", true);
				}
			  }
			  if (selIndex != -1)
				select.options[selIndex].selected = true;
			}
		};
    	e.onkeydown = function(ev) {
	  		ev = ev||window.event;
	  		key = ev.keyCode||ev.which;
	  		if(key == 13) {
              this.blur();
	  		  return false;
	  		}
		};
}

function _update_box(s) {
	var byid = string_contains(s.className, 'combo') || string_contains(s.className, 'combo3');
	var rel = s.getAttribute('rel');
	var box = document.getElementsByName(rel)[0];
		if(box && s.selectedIndex>=0) {
			  var opt = s.options[s.selectedIndex];
				if(box) {
				  var old = box.value;
				  box.value = byid ? opt.value : '';
				  box.setAttribute('_last', box.value);
				  return old != box.value
				}
		}
}

function _set_combo_select(e) {
		// When combo position is changed via js (eg from searchbox)
		// no onchange event is generated. To ensure proper change
		// signaling we must track selectedIndex in onblur handler.
		e.setAttribute('_last', e.selectedIndex);
		e.onblur = function() {
		    var box = document.getElementsByName(this.getAttribute('rel'))[0];
			if ((this.selectedIndex != this.getAttribute('_last'))
				||((string_contains(this.className, 'combo') || string_contains(this.className, 'combo3')) && _update_box(this))
				)
					this.onchange();
		}
		e.onchange = function() {
			var s = this;
			this.setAttribute('_last', this.selectedIndex);
			_update_box(s);
			if (validate(e) && s.selectedIndex>=0) {
				 var sname = '_'+s.name+'_update';
				 var update = document.getElementsByName(sname)[0];
				 if(update)
					    JsHttpRequest.request(update);
			}
			return true;
		}
		e.onkeydown = function(event) {
		    event = event||window.event;
		    key = event.keyCode||event.which;
		    var box = document.getElementsByName(this.getAttribute('rel'))[0];
	  		if(key == 8 || (key==37 && event.altKey)) {
				event.returnValue = false;
  			  	return false;
  			}
            // F1 or ctrl-B (used for barcode reader) activates search
		    if (box && (key == 112 || (key == 66 && event.ctrlKey)) ) {
                this.style.display = 'none';
                box.style.display = 'inline';
                box.value='';
                setFocus(box);
			    return false;
			 } else {
			 	if (key == 13 && !e.length) // prevent chrome issue (blocked cursor after CR on empty selector)
			 		return false;
			 }
		}
}

var _w;

function callEditor(key) {
  var el = document.getElementsByName(editors[key][1])[0];
  if(_w) _w.close(); // this is really necessary to have window on top in FF2 :/
  var left = (screen.width - editors[key][2]) / 2;
  var top = (screen.height - editors[key][3]) / 2;
  _w = open(editors[key][0]+el.value+'&popup=1',
	  "edit","scrollbars=yes,resizable=0,width="+editors[key][2]+",height="+editors[key][3]+",left="+left+",top="+top+",screenX="+left+",screenY="+top);
  if (_w.opener == null)
	  _w.opener = self;
  editors._call = key; // store call point for passBack
  _w.focus();
}

function passBack(value) {
	var o = opener;
	if(value != false) {
		var back = o.editors[o.editors._call]; // form input bindings
		var to = o.document.getElementsByName(back[1])[0];
		if (to) {
			if (to[0] != undefined)
				to[0].value = value; // ugly hack to set selector to any value
			to.value = value;
			// update page after item selection
			o.JsHttpRequest.request('_'+to.name+'_update', to.form);
			o.setFocus(to.name);
		}
	}
	close();
}

/*
	Normalize date format using previous input value to guess missing date elements.
	Helps fast date input field change with only single or double numbers (for day or day-and-month fragments)
*/
function fix_date(date, last)
{
	var dat = last.split(user.datesep);
	var cur = date.split(user.datesep);
	var day, month, year;

// TODO: user.date as default?
// TODO: user.datesys
	if (date == "" || date == last) // should we return an empty date or should we return last?
		return date;
	if (user.datefmt == 0 || user.datefmt == 3) // set defaults
	{
		day = dat[1]; month = dat[0]; year = dat[2];
	} else if (user.datefmt == 1 || user.datefmt == 4){
		day = dat[0]; month = dat[1]; year = dat[2];
	} else {
		day = dat[2]; month = dat[1]; year = dat[0];
	}
	if (cur[1] != undefined && cur[1] != "") // day or month entered, could be string 3
	{
		if (user.datefmt == 0 || user.datefmt == 3 || ((user.datefmt == 2 || user.datefmt == 5) && (cur[2] == undefined || cur[2] == "")))
			day = cur[1];
		else
			month = cur[1];
	}
	if (cur[0] != undefined && cur[0] != "") // day or month entered. could be string 3
	{
		if (cur[1] == undefined || cur[1] == "")
			day = cur[0];
		else if (user.datefmt == 0 || user.datefmt == 3 || ((user.datefmt == 2 || user.datefmt == 5) && (cur[2] == undefined || cur[2] == "")))
			month = cur[0];
		else if (user.datefmt == 2 || user.datefmt == 5)
			year = cur[0];
		else
			day = cur[0];
	}
	if (cur[2] != undefined && cur[2] != "") // year,
	{
		if (user.datefmt == 2 || user.datefmt == 5)
			day = cur[2];
		else
			year = cur[2];
	}
	if (user.datefmt<3) {
		if (day<10) day = '0'+parseInt(day, 10);
		if (month<10) month = '0'+parseInt(month, 10);
	}
	if (year<100) year = year<60 ? (2000+parseInt(year,10)) : (1900+parseInt(year,10));

//	console.info(day,month,year)
	if (user.datefmt == 0 || user.datefmt==3)
		return month+user.datesep+day+user.datesep+year;
	if (user.datefmt == 1 || user.datefmt==4)
		return day+user.datesep+month+user.datesep+year;
	return year+user.datesep+month+user.datesep+day;
}

/*
 Behaviour definitions
*/
var inserts = {
	'input': function(e) {
		if(e.onfocus==undefined) {
			e.onfocus = function() {
				save_focus(this);
				if (string_contains(this.className, 'combo') || string_contains(this.className, 'combo3'))
					this.select();
			};
		}
		if (string_contains(e.className, 'combo') || string_contains(e.className, 'combo2') || string_contains(e.className, 'combo3')) {
				_set_combo_input(e);
		}
		else
    		if(e.type == 'text' ) {
   	  			e.onkeydown = function(ev) {
  					ev = ev||window.event;
  					key = ev.keyCode||ev.which;
 	  				if(key == 13 && e.className != 'typer') {
						if(e.className == 'searchbox')
                            e.onblur();
                        return false;
					}
                    return true;
	  			}
			}
	},
	'input.combo2,input[aspect="fallback"]':
	function(e) {
  	    // this hides search button for js enabled browsers
	    e.style.display = 'none';
	},
	'div.js_only':
	function(e) {
  	    // this shows divs for js enabled browsers only
	    e.style.display = 'block';
	},
	'button': function(e) {
            e.onclick = function(){
			if (validate(e)) {
				setTimeout(function() {	var asp = e.getAttribute('aspect');
					if (asp && asp.indexOf('download') === -1 && asp.indexOf('popup') === -1)
						set_mark((asp && ((asp.indexOf('process') !== -1) || (asp.indexOf('nonajax') !== -1))) ? 'progressbar.gif' : 'ajax-loader.gif');
				}, 100);
				return true;
			}
			return false;
		},
		e.onkeydown = function(ev) {	// block unintentional page escape with 'history back' key pressed on buttons
			ev = ev||window.event;
 			key = ev.keyCode||ev.which;
	  		if(key == 8 || (key==37 && ev.altKey)) {
				ev.returnValue = false;
  			  	return false;
  			}
		}

	},
//	'.ajaxsubmit,.editbutton,.navibutton': // much slower on IE7
	'button.ajaxsubmit,input.ajaxsubmit,input.editbutton,button.editbutton,button.navibutton':
	function(e) {
			e.onclick = function() {
				if (validate(e)) {
					save_focus(e);
					var asp = e.getAttribute('aspect')
					if (asp && (asp.indexOf('process') !== -1))
						JsHttpRequest.request(this, null, 600000); // ten minutes for backup
					else
						JsHttpRequest.request(this);
				}
				return false;
			}
	},
	'.amount': function(e) {
		if (e.onblur == undefined) {
		  e.setAttribute('_last_val', e.value);
  		  e.onblur = function() {
			var dec = this.getAttribute("dec");
			var val = this.getAttribute('_last_val');
			if (val != get_amount(this.name)) {
				this.setAttribute('_last_val', get_amount(this.name));
				price_format(this.name, get_amount(this.name), dec);
				if (e.className.match(/\bactive\b/))
					JsHttpRequest.request('_'+this.name+'_changed', this.form);
			}
		  };
		}
	},
	'.searchbox': // emulated onchange event handling for text inputs
		function(e) {
			e.setAttribute('_last_val', e.value);
			e.setAttribute('autocomplete', 'off'); //must be off when calling onblur
  		  	e.onblur = function() {
				var val = this.getAttribute('_last_val');
				if (val != this.value) {
					this.setAttribute('_last_val', this.value);
					JsHttpRequest.request('_'+this.name+'_changed', this.form);
				}
			}
	},
	'.date':
		function(e) {
			e.setAttribute('_last_val', e.value);
			e.setAttribute('autocomplete', 'off');
  		  	e.onblur = function() {
				var val = this.getAttribute('_last_val');
				if (val != this.value) {
					this.value = fix_date(this.value, val);
					this.setAttribute('_last_val', this.value);
					if (e.className.match(/\bactive\b/))
						JsHttpRequest.request('_'+this.name+'_changed', this.form);
				}
			}
	},
	'button[aspect*selector], button[aspect*abort], input[aspect*selector]': function(e) {
		e.onclick = function() {
			passBack(this.getAttribute('rel'));
			return false;
		}
	},
	'button[aspect=popup]': function(e) {
		e.onclick = function() {
			if(_w) _w.close(); // this is really necessary to have window on top in FF2 :/
			 var left = (screen.width - 800)/2;
			 var top = (screen.height - 600)/2;
			  _w = open(document.location+'popup=1',
				  "edit","Scrollbars=0,resizable=0,width=800,height=600, top="+top+",left="+left+",screenX="+left+",screenY="+top);
			  if (_w.opener == null)
				  _w.opener = self;
			//  editors._call = key; // store call point for passBack
//			  _w.moveTo(50, 50);
			  _w.focus();
			return false;
		}
	},
	'select': function(e) {
		var c = e.className;
		if (string_contains(c, 'mbselect'))
		    mbselect(e);
		else {
            if(e.onfocus==undefined) {
                e.onfocus = function() {
                    save_focus(this);
                };
            }
            var c = e.className;
            if (string_contains(c, 'combo') || string_contains(c, 'combo2') || string_contains(c, 'combo3'))
                _set_combo_select(e);
            else {
                e.onkeydown = function(ev) {	// block unintentional page escape with 'history back' key pressed on buttons
                    ev = ev||window.event;
                    key = ev.keyCode||ev.which;
                    if(key == 8 || (key=37 && ev.altKey)) {
                        ev.returnValue = false;
                        return false;
                    }
                }
            }
		}
	},
	'a.printlink': 	function(l) {
		l.onclick = function() {
		    save_focus(this);
			JsHttpRequest.request(this, null, 60000);
			return false;
		}
	},
	'a.repopts_link': 	function(l) {
		l.onclick = function() {
		    save_focus(this);
		    var replinks = document.getElementsBySelector('a.repopts_link');
				for(var i in replinks)
					replinks[i].style.fontWeight = replinks[i]==this ? 'bold' : 'normal';
			JsHttpRequest.request(this, null);
			return false;
		}
	},
	'a': function(e) { // traverse menu
  		e.onkeydown = function(ev) {
			ev = ev||window.event;
			key = ev.keyCode||ev.which;
			if(key==37 || key==38 || key==39 || key==40) {
					move_focus(key, e, document.links);
					ev.returnValue = false;
					return false;
			}
		}
		// prevent unneeded transaction entry abortion
		if (e.className == 'shortcut'
		 || e.className == 'menu_option'
		 || e.className == 'menu_tab'
 		 || e.className == 'selected')
			e.onclick = function(ev) {
				if (_validate._processing
				 && _validate._modified
				 && !confirm(_validate._processing)) {
					ev.returnValue = false;
					return false;
                }
				if (_hotkeys.alt)	// ommit Chrome accesskeys
					return false;
                window.onbeforeunload = null;
 				window.location = e.href;
			}
	},
	'ul.ajaxtabs':	function(ul) {
	    var ulist=ul.getElementsByTagName("li");
	    for (var x=0; x<ulist.length; x++){ //loop through each LI e
		var tab=ulist[x].getElementsByTagName("button")[0];
		    var url = tab.form.action
		    tab.onclick=function(){
		    if (!_hotkeys.alt && !this.disabled)
				_expand(this);
			return false;
		    }
		}
//	    }
	},
	'textarea': function(e) {
		if(e.onfocus==undefined) {
			e.onfocus = function() {
			    save_focus(this);
			};
		}
	}
/* TODO
	'a.date_picker':  function(e) {
	    // this un-hides data picker for js enabled browsers
	    e.href = date_picker(this.getAttribute('rel'));
	    e.style.display = '';
	    e.tabindex = -1; // skip in tabbing order
	}
*/
};

function stopEv(ev) {
			if(ev.preventDefault) {
				ev.preventDefault();
				ev.stopPropagation();
			} else {
				ev.returnValue = false;
				ev.cancelBubble = true;
				window.keycode = 0;
			}
			return false;
}
/*
	Modified accesskey system. While Alt key is pressed letter keys moves
	focus to next marked link. Alt key release activates focused link.
*/
function setHotKeys() {
	document.onkeydown = function(ev) {
		ev = ev||window.event;
		key = ev.keyCode||ev.which;
		if (key == 18 && !ev.ctrlKey) {	// start selection, skip Win AltGr
			_hotkeys.alt = true;
			_hotkeys.focus = -1;
			return stopEv(ev);
		}
		else if (ev.altKey && !ev.ctrlKey && ((key>47 && key<58) || (key>64 && key<91))) {
			key = String.fromCharCode(key);
			var n = _hotkeys.focus;
			var l = document.getElementsBySelector('[accesskey='+key+']');
			var cnt = l.length;
			_hotkeys.list = l;
			for (var i=0; i<cnt; i++) {
				n = (n+1)%cnt;
				// check also if the link is visible
				if (l[n].accessKey==key && (l[n].offsetWidth || l[n].offsetHeight)) {
					_hotkeys.focus = n;
	    // The timeout is needed to prevent unpredictable behaviour on IE.
					var tmp = function() {l[_hotkeys.focus].focus();};
					setTimeout(tmp, 0);
					break;
				}
			}
			return stopEv(ev);
		}
		if((ev.ctrlKey && key == 13) || key == 27) {
			_hotkeys.alt = false; // cancel link selection
			_hotkeys.focus = -1;
			ev.cancelBubble = true;
   			if(ev.stopPropagation) ev.stopPropagation();
			// activate submit/escape form
			for(var j=0; j<this.forms.length; j++) {
				var form = this.forms[j];
				for (var i=0; i<form.elements.length; i++){
					var el = form.elements[i];
					var asp = el.getAttribute('aspect');

					if (!string_contains(el.className, 'editbutton') && (asp && asp.indexOf('selector') !== -1) && (key==13 || key==27)) {
						passBack(key==13 ? el.getAttribute('rel') : false);
						ev.returnValue = false;
						return false;
					}
					if (((asp && asp.indexOf('default') !== -1) && key==13)||((asp && asp.indexOf('cancel') !== -1) && key==27)) {
						if (validate(el)) {
							if (asp.indexOf('nonajax') !== -1)
								el.click();
							else
							if (asp.indexOf('process') !== -1)
								JsHttpRequest.request(el, null, 600000);
							else
								JsHttpRequest.request(el);
						}
						ev.returnValue = false;
						return false;
					}
				}
			}
			ev.returnValue = false;
			return false;
		}
		if (editors!=='undefined' && editors[key]) {
			callEditor(key);
			return stopEv(ev); // prevent default binding
		}
		return true;
	};
	document.onkeyup = function(ev) {
		ev = ev||window.event;
		key = ev.keyCode||ev.which;

		if (_hotkeys.alt==true) {
			if (key == 18) {
				_hotkeys.alt = false;
				if (_hotkeys.focus >= 0) {
					var link = _hotkeys.list[_hotkeys.focus];
					if(link.onclick)
						link.onclick();
					else
						if (link.target=='_blank') {
							window.open(link.href,'','toolbar=no,scrollbar=no,resizable=yes,menubar=no,width=900,height=500');
							openWindow(link.href,'_blank');
						} else
							window.location = link.href;
				}
			return stopEv(ev);
			}
		}
		return true;
	}
}

Behaviour.register(inserts);

Behaviour.addLoadEvent(setFocus);
Behaviour.addLoadEvent(setHotKeys);
