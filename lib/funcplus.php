<?php
// PukiWiki Plus! - Yet another WikiWikiWeb clone.
// $Id: funcplus.php,v 0.1.8 2006/05/25 00:52:00 miko Exp $
// Copyright (C)
//   2005-2006 PukiWiki Plus! Team
// License: GPL v2 or (at your option) any later version
//
// Plus! extension function(s)

defined('FUNC_SPAMLOG')   or define('FUNC_SPAMLOG', FALSE);
defined('FUNC_SPAMREGEX') or define('FUNC_SPAMREGEX', '/a\s+href=/i');
defined('FUNC_SPAMCOUNT') or define('FUNC_SPAMCOUNT', 3);

// same as 'basename' for page
function basepagename($str)
{
	return mb_basename($str);
}

// multibyte supported 'basename' function
function mb_basename($str)
{
	return preg_replace('#^.*/#', '', $str);
}

// SPAM check
function is_spampost($array)
{
	global $vars;

	$matches = array();
	foreach($array as $idx) {
		if (preg_match_all(FUNC_SPAMREGEX, $vars[$idx], $matches) >= FUNC_SPAMCOUNT)
			return TRUE;
	}
	return FALSE;
}

// SPAM logging
function honeypot_write()
{
	global $get, $post, $vars;

	// Logging for SPAM Report
	// NOTE: Not recommended use Rental Server
	if (FUNC_SPAMLOG === TRUE && version_compare(PHP_VERSION, '4.2.0', '>=')) {
		error_log("----" . date('Y-m-d H:i:s', time()) . "\n", 3, CACHE_DIR . 'honeypot.log');
		error_log("[GET]\n"  . var_export($get,  TRUE) . "\n", 3, CACHE_DIR . 'honeypot.log');
		error_log("[POST]\n" . var_export($post, TRUE) . "\n", 3, CACHE_DIR . 'honeypot.log');
		error_log("[VARS]\n" . var_export($vars, TRUE) . "\n", 3, CACHE_DIR . 'honeypot.log');
	}
}

// ���󥯥롼�ɤ�;�פʤ�Τϥ���������������
function convert_filter($str)
{
	global $filter_rules;
	static $patternf, $replacef;

	if (!isset($patternf))
	{
		$patternf = array_map(create_function('$a','return "/$a/";'),array_keys($filter_rules));
		$replacef = array_values($filter_rules);
		unset($filter_rules);
	}
	return preg_replace($patternf,$replacef,$str);
}

function get_fancy_uri()
{
	$script  = (SERVER_PORT == 443 ? 'https://' : 'http://');       // scheme
	$script .= SERVER_NAME; // host
	$script .= (SERVER_PORT == 80 ? '' : ':' . SERVER_PORT); // port

	// SCRIPT_NAME ��'/'�ǻϤޤäƤ��ʤ����(cgi�ʤ�) REQUEST_URI��ȤäƤߤ�
	$path    = SCRIPT_NAME;
	$script .= $path;       // path

	return $script;
}

function mb_ereg_quote($str)
{
	return mb_ereg_replace('([.\\+*?\[^\]\$(){}=!<>|:])', '\\\1', $str);
}

// �������ɲ�
function open_uri_in_new_window($anchor, $which)
{
	global $use_open_uri_in_new_window,		// ���δؿ���Ȥ����ݤ�
	       $open_uri_in_new_window_opis,		// Ʊ�쥵���С�(Farm?)
	       $open_uri_in_new_window_opisi,		// Ʊ�쥵���С�(Farm?)��InterWiki
	       $open_uri_in_new_window_opos,		// ���������С�
	       $open_uri_in_new_window_oposi;		// ���������С���InterWiki
	global $_symbol_extanchor, $_symbol_innanchor;	// ����������ɥ��򳫤���������
	
	// ���δؿ���Ȥ�ʤ� OR �ƤӽФ����������ʾ��ϥ��롼����
	if (!$use_open_uri_in_new_window || !$which || !$_symbol_extanchor || !$_symbol_innanchor) {
		return $anchor;
	}

	// ���������Υ�󥯤�ɤ����뤫
	$frame = '';
	if ($which == 'link_interwikiname') {
		$frame = (is_inside_uri($anchor) ? $open_uri_in_new_window_opisi:$open_uri_in_new_window_oposi);
		$symbol = (is_inside_uri($anchor) ? $_symbol_innanchor:$_symbol_extanchor);
		$aclass = (is_inside_uri($anchor) ? 'class="inn" ':'class="ext" ');
	} elseif ($which == 'link_url_interwiki') {
		$frame = (is_inside_uri($anchor) ? $open_uri_in_new_window_opisi:$open_uri_in_new_window_oposi);
		$symbol = (is_inside_uri($anchor) ? $_symbol_innanchor:$_symbol_extanchor);
		$aclass = (is_inside_uri($anchor) ? 'class="inn" ':'class="ext" ');
	} elseif ($which == 'link_url') {
		$frame = (is_inside_uri($anchor) ? $open_uri_in_new_window_opis:$open_uri_in_new_window_opos);
		$symbol = (is_inside_uri($anchor) ? $_symbol_innanchor:$_symbol_extanchor);
		$aclass = (is_inside_uri($anchor) ? 'class="inn" ':'class="ext" ');
	}

	if ($frame == '')
		return $anchor;

	// ���� $anchor �� a ��������˥��饹�Ϥʤ�
	$aclasspos = mb_strpos($anchor, '<a ', mb_detect_encoding($anchor)) + 3; // 3 is strlen('<a ')
	$insertpos = mb_strpos($anchor, '</a>', mb_detect_encoding($anchor));
	preg_match('#href="([^"]+)"#', $anchor, $href);

	return (mb_substr($anchor, 0, $aclasspos) . $aclass .
		mb_substr($anchor, $aclasspos, $insertpos-$aclasspos)
	        . str_replace('$1', $href[1], str_replace('$2', $frame, $symbol)) . mb_substr($anchor, $insertpos));
}

function is_inside_uri($anchor)
{
	global $open_uri_in_new_window_servername;

	foreach ($open_uri_in_new_window_servername as $servername) {
		if (stristr($anchor, $servername)) {
			return true;
		}
	}
	return false;
}

if (version_compare(PHP_VERSION, '5.0.0', '<')) {
	function htmlspecialchars_decode($str, $quote_style = ENT_COMPAT) {
	   return strtr($str, array_flip(get_html_translation_table(HTML_SPECIALCHARS, $quote_style)));
	}
}
?>