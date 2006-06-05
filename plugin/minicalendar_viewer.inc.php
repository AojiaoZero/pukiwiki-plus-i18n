<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: minicalendar_viewer.inc.php,v 1.34.21 2006/06/05 12:46:59 miko Exp $
//
// Calendar viewer plugin - List pages that calendar/calnedar2 plugin created
// (Based on calendar and recent plugin)

// Page title's date format
//  * See PHP date() manual for detail
//  * '$\w' = weeklabel defined in $_msg_week
define('PLUGIN_MINICALENDAR_VIEWER_DATE_FORMAT',
		FALSE         // 'pagename/2004-02-09' -- As is
	//	'D, d M, Y'   // 'Mon, 09 Feb, 2004'
	//	'F d, Y'      // 'February 09, 2004'
	//	'[Y-m-d]'     // '[2004-02-09]'
	//	'Y/n/j ($\w)' // '2004/2/9 (Mon)'
	);

// ----

define('PLUGIN_MINICALENDAR_VIEWER_USAGE',
	'#calendar_viewer(pagename,this|yyyy-mm|n|x*y[,mode[,separater]])');

define('PLUGIN_MINICALENDAR_MAX_VIEWS', 3);

define('PLUGIN_MINICALENDAR_VIEWER_HOLIDAYVIEW',TRUE);
define('PLUGIN_MINICALENDAR_VIEWER_COMMENT',FALSE);
define('PLUGIN_MINICALENDAR_VIEWER_TRACKBACK',TRUE);

/*
 ** pagename
 * - calendar or calendar2�ץ饰����򵭽Ҥ��Ƥ�ڡ���̾
 *   pagename/2004-12-30
 *   pagename/2004-12-31
 *   ...
 *
 ** (yyyy-mm|n|this)
 * this    - ����Υڡ��������ɽ��
 * yyyy-mm - yyyy-mm�ǻ��ꤷ��ǯ��Υڡ��������ɽ��
 * n       - n��ΰ���ɽ��
 * x*n     - x���ܤ���n��ΰ���ɽ��(0������)
 ** [mode]
 * past   - ���������Υڡ����ΰ���ɽ���⡼�ɡ������������������ (��ά���ǥե����)
 * future - �����ʹߤΥڡ����ΰ���ɽ���⡼�ɡ����٥��ͽ��䥹�����塼�����
 * view   - ����̤��ؤΰ���ɽ���⡼�ɡ�ɽ���޻ߤ���ڡ����Ϥ���ޤ���
 *
 ** [separater]
 * - ǯ��������ڤ륻�ѥ졼������ꡣ
 * - �ǥե���Ȥ� '-' (yyyy-mm-dd)
 **
 * TODO
 *  past or future �Ƿ�ñ��ɽ������Ȥ��ˡ����줾�������ΰ����ؤΥ�󥯤�
 *  ɽ�����ʤ��褦�ˤ���
 *
 */
function plugin_minicalendar_viewer_convert()
{
	global $vars, $get, $post, $script, $weeklabels;
	global $_err_calendar_viewer_param, $_err_calendar_viewer_param2;
	global $_msg_calendar_viewer_right, $_msg_calendar_viewer_left;
	global $_msg_calendar_viewer_restrict;
	global $_symbol_paraedit, $trackback;

	static $viewed = array();

	if (func_num_args() < 2)
		return PLUGIN_MINICALENDAR_VIEWER_USAGE . '<br />' . "\n";

	$func_vars_array = func_get_args();

	// �ǥե�����ͤ򥻥å�
	$pagename   = $func_vars_array[0]; // ���Ȥʤ�ڡ���̾
	$limit_page = 7;                   // ɽ������������
	$date_YM    = "";                  // ����ɽ������ǯ��
	$mode       = "past";              // ư��⡼��
	$date_sep   = "-";                 // ���դΥ��ѥ졼�� calendar2�ʤ�"-" calendar�ʤ�""

	// Check $func_args[1]
	if (preg_match("/[0-9]{4}".$date_sep."[0-9]{2}/",$func_vars_array[1])) {
		//����ǯ��ΰ���ɽ��
		$page_YM = $func_vars_array[1];
		$limit_base = 0;
		$limit_page = 31;	//��ȴ����31��ʬ���ߥåȤȤ��롣
	}else if (preg_match("/this/si",$func_vars_array[1])) {
		//����ΰ���ɽ��
		$page_YM = get_date("Y".$date_sep."m");
		$limit_base = 0;
		$limit_page = 31;
	}else if (preg_match("/^[0-9]+$/",$func_vars_array[1])) {
		//n��ʬɽ��
		$limit_pitch = $func_vars_array[1];
		$limit_page = $limit_pitch;
		$limit_base = 0;
		$page_YM = "";
	}else if (preg_match("/([0-9]+)\*([0-9]+)/",$func_vars_array[1],$reg_array)) {
		$limit_pitch = $reg_array[2];
		$limit_page = $reg_array[1] + $limit_pitch;
		$limit_base = $reg_array[1];
		$page_YM = "";
	} else {
		return '#calendar_viewer(): ' . $_err_calendar_viewer_param2 . '<br />' . "\n";
	}

	// $func_args[2]: Change default delimiter
	if (isset($func_vars_array[2]) && preg_match("/^(past|pastex|view|viewex|future|futureex)$/si",$func_vars_array[2])) {
		$mode = $func_vars_array[2];
	}

	// $func_args[3]: Change default delimiter
    if (isset($func_vars_array[3])) {
      $date_sep = $func_vars_array[3];
    }

	// Avoid Loop etc.
	if (isset($viewed[$pagename])) {
		if ($viewed[$pagename] > PLUGIN_MINICALENDAR_MAX_VIEWS) {
			$s_page = htmlspecialchars($pagename);
			return "#calendar_viewer(): You already view: $s_page<br />";
		}
		$viewed[$pagename]++; // Valid
	} else {
		$viewed[$pagename]=1; // Valid
	}

	// ����ɽ������ڡ���̾�ȥե�����̾�Υѥ����󡡥ե�����̾�ˤ�ǯ���ޤ�
	if ($pagename == '') {
		// pagename̵����yyyy-mm-dd���б����뤿��ν���
		$pagepattern     = '';
		$pagepattern_len = 0;
		$filepattern     = encode($page_YM);
		$filepattern_len = strlen($filepattern);
	} else {
		$pagepattern     = strip_bracket($pagename) . '/';
		$pagepattern_len = strlen($pagepattern);
		$filepattern     = encode($pagepattern . $page_YM);
		$filepattern_len = strlen($filepattern);
	}

	// �ڡ����ꥹ�Ȥμ���
	$pagelist = array();
	if ($dir = @opendir(DATA_DIR)) {
		$_date = get_date('Y' . $date_sep . 'm' . $date_sep . 'd');
		$page_date  = '';
		while($file = readdir($dir)) {
			if ($file == '..' || $file == '.') continue;
			if (substr($file, 0, $filepattern_len) != $filepattern) continue;

			$page = decode(trim(preg_replace("/\.txt$/"," ",$file)));
			$page_date = substr($page, $pagepattern_len);

			// $page���������������ʤΤ������å� �ǥե���ȤǤ� yyyy-mm-dd
			if (plugin_minicalendar_viewer_isValidDate($page_date,$date_sep) == FALSE) continue;

			// mode����̾��ǤϤ���
			// pastex mode�ǤϺ�����ޤ�̤��Υڡ�����NG
			// futureex mode�ǤϺ�����ޤ���Υڡ�����NG
			// past mode�Ǥ�̤��Υڡ�����NG
			// future mode�Ǥϲ��Υڡ�����NG
			if (($page_date >= $_date) && ($mode=="pastex")) continue;
			if (($page_date <= $_date) && ($mode=="futureex")) continue;
			if (($page_date > $_date) && ($mode=="past")) continue;
			if (($page_date < $_date) && ($mode=="future")) continue;

			$pagelist[] = $page;
		}
	}
	closedir($dir);

	// �ޤ�������
	if ($mode == 'past' || $mode == 'pastex' || $mode =='viewex') {
		rsort($pagelist);
	} else {
		sort($pagelist);
	}

	// �������饤�󥯥롼��
	$tmppage     = $vars["page"];
	$return_body = "";

	// $limit_page�η���ޤǥ��󥯥롼��
	$tmp = max($limit_base, 0); // Skip minus

	while ($tmp < $limit_page){
		if (! isset($pagelist[$tmp])) break;

		$page = $pagelist[$tmp];
		$get['page'] = $post['page'] = $vars['page'] = $page;

		// �����Ǳ������Ĥ����������ɽ������
		if (check_readable($page, FALSE, FALSE)) {
			if (function_exists('convert_filter')) {
				$body = convert_html(convert_filter(get_source($page)));
			} else {
				$body = convert_html(get_source($page));
			}
		} else {
			$body = str_replace('$1', $page, $_msg_calendar_viewer_restrict);
		}

		$r_page = rawurlencode($page);
		$s_page = htmlspecialchars($page);
		if (PLUGIN_MINICALENDAR_VIEWER_DATE_FORMAT !== FALSE) {
			$time = strtotime(basepagename($page)); // $date_sep must be assumed '-' or ''!
			if ($time == -1) {
				$s_page = htmlspecialchars($page); // Failed. Why?
			} else {
				$week   = $weeklabels[date('w', $time)];
				$s_page = htmlspecialchars(str_replace(
						array('$w'),
						array($week),
						date(PLUGIN_CALENDAR_VIEWER_DATE_FORMAT, $time)
					));
			}
		}

		$refpage = rawurlencode($tmppage);
		$page_title = basepagename($page);
		$s_page_ttile = htmlspecialchars($page_title);

		if (PKWK_READONLY) {
			$link = $script . '?' . $r_page;
		} else {
			$link = $script . '?cmd=edit&amp;page=' . $r_page . '&amp;refpage=' . $refpage;
		}
		$link = '<a class="anchor_super" href="' . $link . '">' . $_symbol_paraedit . '</a>';
		$head = '<h3 class="minicalendar">' . $s_page_title . ' ' . $link . '</h3>' . "\n";
		$tail = '';

		if (PLUGIN_MINICALENDAR_VIEWER_HOLIDAYVIEW === TRUE) {
			$time = strtotime($page_title);
			if ($time != -1) {
				$yy = intval(date('Y', $time));
				$mm = intval(date('n', $time));
				$dd = intval(date('d', $time));
				$monthlabel = array(1 => 
					'January','Feburary','March',    'April',  'May',     'June',
					'July',   'August',  'September','October','November','December'
				);
				$mmstr = $monthlabel[$mm];
				$h_today = public_holiday($yy, $mm, $dd); 
				$hday = $h_today['rc'];
				$f_today = getdate(mktime(0,0,0,$mm,$dd,$yy) - LOCALZONE + ZONETIME);
				$wday = $f_today['wday'];
				if ($hday != 0)      { $classname = 'date_holiday'; }
				else if ($wday == 0) { $classname = 'date_holiday'; }
				else if ($wday == 6) { $classname = 'date_weekend'; }
				else { $classname = 'date_weekday'; }
				$head = '<h3 class="'. $classname . '"><span class="day">' . sprintf('%02d', $dd) . '</span> <br />'
				      . '<b>' . $mmstr . '</b>, <b>' . $yy . '</b>' . $link . '</h3>' . "\n";
			}
		}
		if (PLUGIN_MINICALENDAR_VIEWER_COMMENT === TRUE) {
			if (is_page(':config/plugin/addline/comment') && exist_plugin_inline('addline')) {
				$comm = convert_html(array('&addline(comment,above){comment};'));
				$comm = preg_replace(array("'<p>'si","'</p>'si"), array("",""), $comm );
				$tail .= str_replace('>comment','><img src="'.IMAGE_URI.'plus/comment.png" width="15" height="15" alt="Comment" title="Comment" />Comment',$comm);
			}
		}
		if (MINICALENDAR_VIEWER_TRACKBACK === TRUE) {
			if ($trackback) {
		        $tb_id = tb_get_id($page);
		        $tail .= '<a href="' . $script . '?plugin=tb&amp;__mode=view&amp;tb_id=' . $tb_id . '">'
		               . '<img src="' . IMAGE_URI . 'plus/trackback.png" width="15" height="15" alt="" title="" />Trackback("' . tb_count($page) . '")'
		               . '</a>' . "\n";
			}
		}

	    if ($tail != '') { $tail = '<div class="trackback">'. $tail . '</div>'; };
	    $return_body .= $head . '<div class="minicalendar_viewer">' . $body . '</div>' . $tail;

		++$tmp;
	}

	//�����ǡ�����Υ�󥯤�ɽ��
	//?plugin=minicalendar_viewer&file=�ڡ���̾&date=yyyy-mm
	$enc_pagename = rawurlencode(substr($pagepattern,0,$pagepattern_len -1));

	if ($page_YM != "") {
		// ǯ��ɽ����
		$date_sep_len = strlen($date_sep);
		$this_year    = substr($page_YM, 0, 4);
		$this_month   = substr($page_YM, 4+$date_sep_len, 2);

		// ����
		$next_year  = $this_year;
		$next_month = $this_month + 1;
		if ($next_month > 12) {
			$next_year ++;
			$next_month = 1;
		}
		$next_YM = sprintf("%04d%s%02d",$next_year,$date_sep,$next_month);
		$next_YMX = sprintf("%04d%02d",$next_year,$next_month);

		// ����
		$prev_year  = $this_year;
		$prev_month = $this_month -1;
		if ($prev_month < 1) {
			$prev_year --;
			$prev_month = 12;
		}
		$prev_YM = sprintf("%04d%s%02d",$prev_year,$date_sep,$prev_month);
		$prev_YMX = sprintf("%04d%02d",$prev_year,$prev_month);

//		if ($mode == "past" || $mode == "pastex") {
//			$right_YM   = $prev_YM;
//			$right_YMX  = $prev_YMX;
//			$right_text = $prev_YM."&gt;&gt;";
//			$left_YM    = $next_YM;
//			$left_YMX   = $next_YMX;
//			$left_text  = "&lt;&lt;".$next_YM;
//		} else {
			$left_YM    = $prev_YM;
			$left_YMX   = $prev_YMX;
			$left_text  = '&lt;&lt;' . $prev_YM;
			$right_YM   = $next_YM;
			$right_YMX  = $next_YMX;
			$right_text = $next_YM . '&gt;&gt;';
//		}
	} else {
		// n��ɽ����
		if ($limit_base >= count($pagelist)) {
			$right_YM = '';
		}else{
			$right_base = $limit_base + $limit_pitch;
			$right_YM = $right_base . '*' . $limit_pitch;
			$right_text = sprintf($_msg_calendar_viewer_right, $limit_pitch);
		}
		$left_base  = $limit_base - $limit_pitch;
		if ($left_base >= 0) {
			$left_YM = $left_base . '*' . $limit_pitch;
			$left_text = sprintf($_msg_calendar_viewer_left, $limit_pitch);
		}else{
			$left_YM = '';
		}
		$prev_YMX = '';
		$next_YMX = '';
	}

	// �ʥӥ������ѤΥ�󥯤��������ɲ�
	$s_date_sep = htmlspecialchars($date_sep);
	if ($left_YM != '') {
		if ($left_YMX != '') {
			$left_link = "<a href=\"$script?plugin=minicalendar&amp;file=$enc_pagename&amp;date=$left_YMX\">$left_text</a>";
		} else {
			$left_link = "<a href=\"$script?plugin=minicalendar_viewer&amp;file=$enc_pagename&amp;date=$left_YM&amp;date_sep=$s_date_sep&amp;mode=$mode\">$left_text</a>";
		}
	} else {
	    $left_link = '';
	}
	if ($right_YM != '') {
		if ($right_YMX != '') {
			$right_link = "<a href=\"$script?plugin=minicalendar&amp;file=$enc_pagename&amp;date=$right_YMX\">$right_text</a>";
		} else {
			$right_link = "<a href=\"$script?plugin=minicalendar_viewer&amp;file=$enc_pagename&amp;date=$right_YM&amp;date_sep=$s_date_sep&amp;mode=$mode\">$right_text</a>";
		}
	} else {
	    $right_link = '';
	}

	//past mode��<<�� ��>> ¾��<<�� ��>>
	$return_body .= '<div class="prevnext">';
	$return_body .= '<div class="prevnext_r">' . $right_link . '</div>';
	$return_body .= '<div class="prevnext_l">' . $left_link  . '</div>';
	$return_body .= '</div><br style="display:block;clear:both" />';

	$get['page'] = $post['page'] = $vars['page'] = $tmppage;

	return $return_body;
}

function plugin_minicalendar_viewer_action()
{
	global $vars, $get, $post, $script;

	$date_sep = "-";
	$return_vars_array = array();

	$page = strip_bracket($vars['page']);
	$vars['page'] = '*';
	if (isset($vars['file'])) $vars['page'] = $vars['file'];

	$date_sep = $vars['date_sep'];

	$page_YM = $vars['date'];
	if ($page_YM == '') $page_YM = get_date('Y' . $date_sep . 'm');
	$mode = $vars['mode'];

	$args_array = array($vars['page'], $page_YM, $mode, $date_sep);
	$return_vars_array["body"] = call_user_func_array('plugin_minicalendar_viewer_convert',$args_array);

	//$return_vars_array["msg"] = "minicalendar_viewer ".$vars["page"]."/".$page_YM;
	$return_vars_array["msg"] = "minicalendar_viewer ".htmlspecialchars($vars["page"]);
	if ($vars["page"] != '') $return_vars_array['msg'] .= '/';

	if (preg_match("/\*/",$page_YM)) {
		//������n��ɽ���λ��Ϥʤ�ƥڡ���̾�ˤ����餤����
	} else {
		$return_vars_array["msg"] .= htmlspecialchars($page_YM);
	}

	// Patched By miko - �ɤ߹��ߥ⡼�ɤˤ���.
	$vars['cmd'] = 'read';
	$vars['page'] = $page;
	return $return_vars_array;
}

function plugin_minicalendar_viewer_isValidDate($aStr, $aSepList="-/ .")
{
	if ($aSepList == '') {
		// yyyymmdd�Ȥ��ƥ����å��ʼ�ȴ��(^^;��
		return checkdate(substr($aStr,4,2),substr($aStr,6,2),substr($aStr,0,4));
	}

	$matches = array();
	if ( ereg("^([0-9]{2,4})[$aSepList]([0-9]{1,2})[$aSepList]([0-9]{1,2})$", $aStr, $matches) ) {
		return checkdate($matches[2], $matches[3], $matches[1]);
	}

	return FALSE;
}
?>