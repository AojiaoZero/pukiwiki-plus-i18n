/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: default.js,v 2.0.7 2004/12/03 15:56:08 miko Exp $
// Original is nao-pon
//

// Set masseges.
var pukiwiki_msg_copyed = "����åץܡ��ɤ˥��ԡ����ޤ�����";
var pukiwiki_msg_select = "�о��ϰϤ����򤷤Ƥ���������";
var pukiwiki_msg_fontsize = "ʸ�����礭�� ( % �ޤ��� pt[��ά��] �ǻ���): ";
var pukiwiki_msg_to_ncr = "����ʸ�����Ȥ��Ѵ�";
var pukiwiki_msg_hint = '<img src="image/plus/hint.png" width="18" height="16" border="0" title="hint" alt="hint" />';
var pukiwiki_msg_winie_hint_text = "\n\n������ϡ��ǽ�����򤷤�����ʸ�������������򤷤������طʿ��ˤʤ�ޤ���\n\n�����ϰϤ������ϡ������ϰϤ����򤷤��ޤޤˤʤäƤ��ޤ���\n³����ʸ�������Ϥ�����ϡ�[ �� ]�����ǥ���������ư���Ƥ������Ϥ��Ƥ���������\n\n\n-- +��(���ɥХ󥹥⡼��) --\n\n[ &# ] �ܥ���ϡ�����ʸ��������ʸ�����Ȥ��Ѵ����ޤ���";
var pukiwiki_msg_gecko_hint_text = pukiwiki_msg_winie_hint_text + "\n\n" + "ɽ���ϰϤ���Ƭ����äƤ��ޤ������������ϰϤ������ʤ��ʤä����ϡ�[ ESC ]�����򲡤��ƤߤƤ���������";
var pukiwiki_msg_to_easy_t = '<img src="image/plus/easy.png" width="18" height="16" border="0" title="easy" alt="easy" />';
var pukiwiki_msg_to_adv_t = '<img src="image/plus/adv.png" width="18" height="16" border="0" title="adv" alt="adv" />';
var pukiwiki_msg_to_easy = "���������⡼�ɤ��ѹ����ޤ�����\n������ɸ��ͭ���ˤʤ�ޤ���\n\n������������ɤ��ޤ�����";
var pukiwiki_msg_to_adv = "���ɥХ󥹥⡼�ɤ��ѹ����ޤ�����\n������ɸ��ͭ���ˤʤ�ޤ���\n\n������������ɤ��ޤ�����";
var pukiwiki_msg_inline1 = "�ץ饰����̾�����Ϥ��Ƥ���������[ �� �Ͼʤ� ]";
var pukiwiki_msg_inline2 = "�ѥ�᡼���������Ϥ��Ƥ���������[ ( )�� ]";
var pukiwiki_msg_inline3 = "��ʸ�����Ϥ��Ƥ���������[ { }�� ]";
var pukiwiki_msg_link = "��󥯤����ꤹ��ʸ�������Ϥ��Ƥ���������";
var pukiwiki_msg_url = "������URL�����Ϥ��Ƥ���������";
var pukiwiki_msg_elem = "�����򤹤��оݤ����򤷤Ƥ���������";

// Init.
var pukiwiki_WinIE=(document.all&&!window.opera&&navigator.platform=="Win32");
var pukiwiki_Gecko=(navigator && navigator.userAgent && navigator.userAgent.indexOf("Gecko/") != -1);

// Common function.
function open_mini(URL,width,height){
	aWindow = window.open(URL, "mini", "toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=yes,resizable=no,width="+width+",height="+height);
}

// Common Plus function.
function open_uri(href, frame)
{
	if (!frame) {
		return false;
	}
	window.open(href, frame);
	return false;
}

// cookie
var pukiwiki_adv = pukiwiki_load_cookie("pwplus");

// Helper image tag set
var pukiwiki_adv_tag = '';
if (pukiwiki_adv == "on") pukiwiki_adv_tag = '<span style="cursor:hand;">'+
'<img src="image/plus/ncr.gif" width="22" height="16" border="0" title="'+pukiwiki_msg_to_ncr+'" alt="'+pukiwiki_msg_to_ncr+'" onClick="javascript:pukiwiki_charcode(); return false;" />'+
'<img src="image/plus/br.gif" width="18" height="16" border="0" title="&amp;br;" alt="&amp;br;" onClick="javascript:pukiwiki_ins(\'&br;\'); return false;" />'+
'<'+'/'+'span>&nbsp;';

//'<img src="image/plus/iplugin.gif" width="18" height="16" border="0" title="Inline Plugin" alt="Inline Plugin" onClick="javascript:pukiwiki_ins(\'&(){};\'); return false;" />'+

var pukiwiki_helper_img = 
'<img src="image/plus/buttons.gif" width="103" height="16" border="0" usemap="#map_button" tabindex="-1" />&nbsp;'+
pukiwiki_adv_tag +
'<img src="image/plus/colors.gif" width="64" height="16" border="0" usemap="#map_color" tabindex="-1" />&nbsp;'+
'<span style="cursor:hand;">'+
'<img src="image/face/smile.png" width="15" height="15" border="0" title="(^^)" alt="(^^)" onClick="javascript:pukiwiki_face(\'(^^)\'); return false;" />'+
'<img src="image/face/bigsmile.png" width="15" height="15" border="0" title="(^-^" alt="(^-^" onClick="javascript:pukiwiki_face(\'(^-^\'); return false;" />'+
'<img src="image/face/huh.png" width="15" height="15" border="0" title="(^Q^" alt="(^Q^" onClick="javascript:pukiwiki_face(\'(^Q^\'); return false;" />'+
'<img src="image/face/oh.png" width="15" height="15" border="0" title="(..;" alt="(..;" onClick="javascript:pukiwiki_face(\'(..;\'); return false;" />'+
'<img src="image/face/wink.png" width="15" height="15" border="0" title="(^_-" alt="(^_-" onClick="javascript:pukiwiki_face(\'(^_-\'); return false;" />'+
'<img src="image/face/sad.png" width="15" height="15" border="0" title="(--;" alt="(--;" onClick="javascript:pukiwiki_face(\'(--;\'); return false;" />'+
'<img src="image/face/worried.png" width="15" height="15" border="0" title="(^^;" alt="(^^;" onclick="javascript:pukiwiki_face(\'(^^\;\'); return false;" />'+
'<img src="image/face/tear.png" width="15" height="15" border="0" title="(T-T" alt="(T-T" onclick="javascript:pukiwiki_face(\'(T-T\'); return false;" />'+
'<img src="image/face/heart.png" width="15" height="15" border="0" title="&amp;heart;" alt="&amp;heart;" onClick="javascript:pukiwiki_face(\'&amp;heart;\'); return false;" />'+
'<'+'/'+'span>';

//'<img src="image/face/star.gif" width="15" height="15" border="0" title="&amp;star;" alt="&amp;star;" onClick="javascript:pukiwiki_face(\'&amp;star;\'); return false;" />'+

// Helper function.
function pukiwiki_show_fontset_img()
{
	var str =  pukiwiki_helper_img + '&nbsp;<a href="#" onClick="javascript:pukiwiki_show_hint(); return false;">' + pukiwiki_msg_hint + '<'+'/'+'a>';
	
	if (pukiwiki_adv == "on")
	{
		str = str + '<a href="#" onClick="javascript:pukiwiki_adv_swich(); return false;">' + pukiwiki_msg_to_easy_t + '<'+'/'+'a>';
	}
	else
	{
		str = str + '<a href="#" onClick="javascript:pukiwiki_adv_swich(); return false;">' + pukiwiki_msg_to_adv_t + '<'+'/'+'a>';
	}
	
	document.write(str);
}

function pukiwiki_adv_swich()
{
	if (pukiwiki_adv == "on")
	{
		pukiwiki_adv = "off";
		pukiwiki_ans = confirm(pukiwiki_msg_to_easy);
	}
	else
	{
		pukiwiki_adv = "on";
		pukiwiki_ans = confirm(pukiwiki_msg_to_adv);
	}
	pukiwiki_save_cookie("pwplus",pukiwiki_adv,1,"/");
	if (pukiwiki_ans) window.location.reload();
}
function pukiwiki_save_cookie(arg1,arg2,arg3,arg4){ //arg1=dataname arg2=data arg3=expiration days
	if(arg1&&arg2)
	{
		if(arg3)
		{
			xDay = new Date;
			xDay.setDate(xDay.getDate() + eval(arg3));
			xDay = xDay.toGMTString();
			_exp = ";expires=" + xDay;
		}
		else
		{
			_exp ="";
		}
		if(arg4)
		{
			_path = ";path=" + arg4;
		}
		else
		{
			_path= "";
		}
		document.cookie = escape(arg1) + "=" + escape(arg2) + _exp + _path +";";
	}
}

function pukiwiki_load_cookie(arg){ //arg=dataname
	if(arg)
	{
		cookieData = document.cookie + ";" ;
		arg = escape(arg);
		startPoint1 = cookieData.indexOf(arg);
		startPoint2 = cookieData.indexOf("=",startPoint1) +1;
		endPoint = cookieData.indexOf(";",startPoint1);
		if(startPoint2 < endPoint && startPoint1 > -1 &&startPoint2-startPoint1 == arg.length+1)
		{
			cookieData = cookieData.substring(startPoint2,endPoint);
			cookieData = unescape(cookieData);
			return cookieData
		}
	}
	return false
}

function pukiwiki_area_highlite(id,mode)
{
	if (mode)
	{
		document.getElementById(id).className = "area_on";
	}
	else
	{
		document.getElementById(id).className = "area_off";
	}
	
}
// Branch.
if (pukiwiki_WinIE)
{
	document.write ('<scr'+'ipt type="text/javascr'+'ipt" src="skin/winie.js"></scr'+'ipt>');
}
else if (pukiwiki_Gecko)
{
	document.write ('<scr'+'ipt type="text/javascr'+'ipt" src="skin/gecko.js"></scr'+'ipt>');
}
else
{
	document.write ('<scr'+'ipt type="text/javascr'+'ipt" src="skin/other.js"></scr'+'ipt>');
}