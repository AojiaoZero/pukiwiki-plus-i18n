<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: keitai.skin.php,v 1.3.9 2004/10/17 13:29:52 miko Exp $
//

// Prohibit direct access
if (! defined('UI_LANG')) exit;

global $script, $vars, $page_title, $max_size, $accesskey, $menubar;

// Force Shift JIS encode for Japanese embedded browsers and devices
header('Content-Type: text/html; charset=Shift_JIS');
$title = mb_convert_encoding($title, 'SJIS', SOURCE_ENCODING);
$body  = mb_convert_encoding($body,  'SJIS', SOURCE_ENCODING);

// Make 1KByte spare (for header, etc)
$max_size = --$max_size * 1024;

// �����ȹԤ�̵����
$body = preg_replace('#<!(?:--[^-]*-(?:[^-]+-)*?-(?:[^>-]*(?:-[^>-]+)*?)??)*(?:>|$(?!\n)|--.*$)#', '', $body);

// <del>��</del>����
$body = preg_replace('#(<del>)([\w\W]*)(</del>)#i', '', $body);

// �Խ���ǽ���� IMG ����(����)��ʸ�����ִ�
$body = preg_replace('#(<div[^>]+>)?(<a[^>]+>)?<img[^>]*alt="Edit"[^>]*>(?(2)</a>)(?(1)</div>)#i', '&#63826;', $body);

// ALT="keitai" ����� IMG ����(����)���ִ�(*1)
$body = preg_replace('#<img([^>]*)title="keitai"[^>]*>#i', '<PWimg $1>', $body);

// ALT option ����� IMG ����(����)��ʸ������ִ�
$body = preg_replace('#(<div[^>]+>)?(<a[^>]+>)?<img[^>]*alt="([^"]+)"[^>]*>(?(2)</a>)(?(1)</div>)#i', '[$3]', $body);

// ALT option ��̵�� IMG ����(����)��ʸ������ִ�
$body = preg_replace('#(<div[^>]+>)?(<a[^>]+>)?<img[^>]+>(?(2)</a>)(?(1)</div>)#i', '[img]', $body);

// ALT="keitai" ����� IMG ����(����)���ִ�(*2)
$body = preg_replace('#<PWimg#', '<img', $body);

// �ڡ����ֹ�
$r_page = isset($vars['page']) ? $vars['page'] : '';
$r_page = rawurlencode($r_page);
$pageno = (isset($vars['p']) and is_numeric($vars['p'])) ? $vars['p'] : 0;
$pagecount = ceil(strlen($body) / $max_size);
$lastpage = $pagecount - 1;

// �ʥӥ��������
$navi = array();
$navi[] = '<a href="' . $_LINK['new'] . '"' . $accesskey . '="1">1.New</a>';
$navi[] = '<a href="' . $_LINK['edit'] . '"' . $accesskey . '="2">2.Edit</a>';
if ($is_read and $function_freeze) {
	if (! $is_freeze) {
		$navi[] = '<a href="' . $_LINK['freeze']   . '" ' . $accesskey . '="3">3.Freeze</a>';
	} else {
		$navi[] = '<a href="' . $_LINK['unfreeze'] . '" ' . $accesskey . '="3">3.Unfreeze</a>';
	}
}
$navi[] = '<a href="' . $_LINK['menu'] . '"' . $accesskey . '="4">4.Menu</a>';
$navi[] = '<a href="' . $_LINK['recent'] . '"' . $accesskey . '="5">5.Recent</a>';

// ��/���Υ֥�å�
if ($pagecount > 1) {
	$prev = $pageno - 1;
	$next = $pageno + 1;
	if ($pageno > 0) {
		$navi[] = '<a href="' . $_LINK['read'] . '&amp;p=' . $prev . '"' . $accesskey . '="7">7.Prev</a>';
	}
	$navi[] = "$next/$pagecount ";
	if ($pageno < $lastpage) {
		$navi[] = '<a href="' . $_LINK['read'] . '&amp;p=' . $next . '"' . $accesskey . '="8">8.Next</a>';
	}
}
$navi[] = '<a href="' . $_LINK['reload'] . '"' . $accesskey . '="9">9.Reload</a>';
$navi[] = '<a href="' . $_LINK['top'] . '"' . $accesskey . '="0">0.Top</a>';

$navi = join(' | ', $navi);
$body = substr($body, $pageno * $max_size, $max_size);

// Output
?><html><head><title><?php
	echo $title
?></title></head><body><?php
	echo $body
?><hr><?php
	echo $navi
?></body></html>
