<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: html.php,v 1.18.12 2004/12/02 13:05:02 miko Exp $
//

// ��ʸ�����
function catbody($title,$page,$body)
{
	global $script, $vars, $arg, $defaultpage, $whatsnew, $help_page, $hr;
	global $related_link, $cantedit, $function_freeze, $search_word_color, $_msg_word;
	global $foot_explain, $note_hr, $head_tags;
	global $trackback, $trackback_javascript, $referer, $javascript;
	global $skin_file;
	global $_LANG, $_LINK, $_IMAGE;

	global $html_transitional; // FALSE:XHTML1.1 TRUE:XHTML1.0 Transitional
	global $page_title;        // �ۡ���ڡ����Υ����ȥ�
	global $do_backup;         // �Хå����åפ�Ԥ����ɤ���
	global $modifier;          // �Խ��ԤΥۡ���ڡ���
	global $modifierlink;      // �Խ��Ԥ�̾��

	if (! file_exists(SKIN_FILE) || ! is_readable(SKIN_FILE)) {
		if (! file_exists($skin_file) || ! is_readable($skin_file)) {
			die_message(SKIN_FILE.'(skin file) is not found.');
		} else {
			define(SKIN_FILE,$skin_file);
		}
	}

	$_LINK = $_IMAGE = array();

	// Add JavaScript header when ...
	if ($trackback && $trackback_javascript) $javascript = 1; // Set something If you want
	if (! PKWK_ALLOW_JAVASCRIPT) unset($javascript);

	$_page  = isset($vars['page']) ? $vars['page'] : '';
	$r_page = rawurlencode($_page);

	// Set $_LINK for skin
	$_LINK['add']      = "$script?cmd=add&amp;page=$r_page";
	$_LINK['backup']   = "$script?cmd=backup&amp;page=$r_page";
	$_LINK['copy']     = "$script?plugin=template&amp;refer=$r_page";
	$_LINK['diff']     = "$script?cmd=diff&amp;page=$r_page";
	$_LINK['edit']     = "$script?cmd=edit&amp;page=$r_page";
	$_LINK['filelist'] = "$script?cmd=filelist";
	$_LINK['freeze']   = "$script?cmd=freeze&amp;page=$r_page";
	$_LINK['help']     = "$script?cmd=help";
	$_LINK['list']     = "$script?cmd=list";
	$_LINK['menu']     = "$script?$menubar";
	$_LINK['new']      = "$script?plugin=newpage&amp;refer=$r_page";
	$_LINK['read']     = "$script?plugin=read&amp;page=$r_page";
	$_LINK['rdf']      = "$script?cmd=rss&amp;ver=1.0";
	$_LINK['recent']   = "$script?" . rawurlencode($whatsnew);
	$_LINK['refer']    = "$script?plugin=referer&amp;page=$r_page";
	$_LINK['reload']   = "$script?$r_page";
	$_LINK['rename']   = "$script?plugin=rename&amp;refer=$r_page";
	$_LINK['rss']      = "$script?cmd=rss";
	$_LINK['rss10']    = "$script?cmd=rss&amp;ver=1.0"; // Same as 'rdf'
	$_LINK['rss20']    = "$script?cmd=rss&amp;ver=2.0";
	$_LINK['rssplus']  = "$script?cmd=rss10plus";
	$_LINK['search']   = "$script?cmd=search";
	$_LINK['side']     = "$script?$sidebar";
	$_LINK['source']   = "$script?plugin=source&amp;refer=$r_page";
	$_LINK['top']      = "$script?" . rawurlencode($defaultpage);
	if ($trackback) {
		$tb_id = tb_get_id($_page);
		$_LINK['trackback'] = "$script?plugin=tb&amp;__mode=view&amp;tb_id=$tb_id";
	}
	$_LINK['unfreeze'] = "$script?cmd=unfreeze&amp;page=$r_page";
	$_LINK['upload']   = "$script?plugin=attach&amp;pcmd=upload&amp;page=$r_page";

	// Compat: Skins for 1.4.4 and before
	$link_add       = & $_LINK['add'];
	$link_new       = & $_LINK['new'];	// New!
	$link_edit      = & $_LINK['edit'];
	$link_diff      = & $_LINK['diff'];
	$link_top       = & $_LINK['top'];
	$link_list      = & $_LINK['list'];
	$link_filelist  = & $_LINK['filelist'];
	$link_search    = & $_LINK['search'];
	$link_whatsnew  = & $_LINK['recent'];
	$link_backup    = & $_LINK['backup'];
	$link_help      = & $_LINK['help'];
	$link_trackback = & $_LINK['trackback'];	// New!
	$link_rdf       = & $_LINK['rdf'];		// New!
	$link_rss       = & $_LINK['rss'];
	$link_rss10     = & $_LINK['rss10'];		// New!
	$link_rss20     = & $_LINK['rss20'];		// New!
	$link_freeze    = & $_LINK['freeze'];
	$link_unfreeze  = & $_LINK['unfreeze'];
	$link_upload    = & $_LINK['upload'];
	$link_template  = & $_LINK['copy'];
	$link_refer     = & $_LINK['refer'];	// New!
	$link_rename    = & $_LINK['rename'];
	$link_read      = & $_LINK['read'];     // Plus!
	$link_reload    = & $_LINK['reload'];   // Plus!
	$link_menu      = & $_LINK['menu'];     // Plus!
	$link_side      = & $_LINK['side'];     // Plus!
	$link_source    = & $_LINK['source'];   // Plus!

	// �ڡ�����ɽ����TRUE(�Хå����åפ�ɽ����RecentChanges��ɽ�������)
	$is_page = (is_pagename($_page) && ! arg_check('backup') && $_page != $whatsnew);

	// �ڡ������ɤ߽Ф���TRUE
	$is_read = (arg_check('read') && is_page($_page));

	// �ڡ�������뤵��Ƥ���Ȥ�TRUE
	$is_freeze = is_freeze($_page);

	// �ڡ����κǽ���������(ʸ����)
	$lastmodified = $is_read ?  get_date('D, d M Y H:i:s T', get_filetime($_page)) .
		' ' . get_pg_passage($_page, FALSE) : '';

	// ��Ϣ����ڡ����Υꥹ��
	$related = ($is_read && $related_link) ? make_related($_page) : '';

	// ź�եե�����Υꥹ��
	$attaches = ($is_read && exist_plugin_action('attach')) ? attach_filelist() : '';

	// ���Υꥹ��
	ksort($foot_explain, SORT_NUMERIC);
	$notes = ! empty($foot_explain) ? $note_hr . join("\n", $foot_explain) : '';

	// <head>����ɲä��륿��
	$head_tag = ! empty($head_tags) ? join("\n", $head_tags) ."\n" : '';

	// 1.3.x compat
	// �ڡ����κǽ���������(UNIX timestamp)
	$fmt = $is_read ? get_filetime($_page) + LOCALZONE : 0;

	//ñ�측��
	if ($search_word_color && isset($vars['word'])) {
		$body = '<div class="small">' . $_msg_word . htmlspecialchars($vars['word']) .
			"</div>$hr\n$body";
		$words = array_flip(array_splice(
			preg_split('/\s+/', $vars['word'], -1, PREG_SPLIT_NO_EMPTY),
			0, 10));
		$keys = array();
		foreach ($words as $word=>$id) {
			$keys[$word] = strlen($word);
		}
		arsort($keys, SORT_NUMERIC);
		$keys = get_search_words(array_keys($keys), TRUE);
		$id = 0;
		foreach ($keys as $key=>$pattern)
		{
			$s_key    = htmlspecialchars($key);
			$pattern  = "/<[^>]*>|($pattern)|&[^;]+;/";
			$callback = create_function(
				'$arr',
				'return (count($arr) > 1) ? "<strong class=\"word' . $id++ . '\">{$arr[1]}</strong>" : $arr[0];'
			);
			$body  = preg_replace_callback($pattern, $callback, $body);
			$notes = preg_replace_callback($pattern, $callback, $notes);
		}
	}

	$longtaketime = getmicrotime() - MUTIME;
	$taketime     = sprintf('%01.03f', $longtaketime);

	require(SKIN_FILE);
}

// ����饤�����ǤΥѡ��� (obsolete)
function inline($line, $remove = FALSE)
{
	global $NotePattern;

	$line = htmlspecialchars($line);
	if ($remove) 
		$line = preg_replace($NotePattern, '', $line);

	return $line;
}

// ����饤�����ǤΥѡ��� (��󥯡����Ф�����) (obsolete)
function inline2($str)
{
	return make_link($str);
}

// �Խ��ե������ɽ��
function edit_form($page, $postdata, $digest = 0, $b_template = TRUE)
{
	global $script, $vars, $rows, $cols, $hr, $function_freeze;
	global $_btn_addtop, $_btn_preview, $_btn_repreview, $_btn_update, $_btn_cancel,
		$_btn_freeze, $_msg_help, $_btn_notchangetimestamp;
	global $whatsnew, $_btn_template, $_btn_load, $non_list, $load_template_func;

	$refer = $template = $addtag = $add_top = '';

	if ($digest == 0) $digest = md5(join('', get_source($page)));

	$checked_top  = isset($vars['add_top'])     ? ' checked="checked"' : '';
	$checked_time = isset($vars['notimestamp']) ? ' checked="checked"' : '';

	if(isset($vars['add'])) {
		$addtag  = '<input type="hidden" name="add" value="true" />';
		$add_top = "<input type=\"checkbox\" name=\"add_top\" value=\"true\"$checked_top /><span class=\"small\">$_btn_addtop</span>";
	}

	if($load_template_func && $b_template) {
		$_pages = get_existpages();
		$pages  = array();
		foreach($_pages as $_page) {
			if ($_page == $whatsnew || preg_match("/$non_list/", $_page))
				continue;
			$s_page = htmlspecialchars($_page);
			$pages[$_page] = "   <option value=\"$s_page\">$s_page</option>";
		}
		ksort($pages);
		$s_pages  = join("\n", $pages);
		$template = <<<EOD
  <select name="template_page">
   <option value="">-- $_btn_template --</option>
$s_pages
  </select>
  <input type="submit" name="template" value="$_btn_load" accesskey="r" />
  <br />
EOD;

		if (isset($vars['refer']) && $vars['refer'] != '')
			$refer = '[[' . strip_bracket($vars['refer']) ."]]\n\n";
	}

	$r_page      = rawurlencode($page);
	$s_page      = htmlspecialchars($page);
	$s_digest    = htmlspecialchars($digest);
	$s_postdata  = htmlspecialchars($refer . $postdata);
	$s_original  = isset($vars['original']) ? htmlspecialchars($vars['original']) : $s_postdata;
	$s_id        = isset($vars['id']) ? htmlspecialchars($vars['id']) : '';
	$b_preview   = isset($vars['preview']); // �ץ�ӥ塼��TRUE
	$btn_preview = $b_preview ? $_btn_repreview : $_btn_preview;
	$refpage = htmlspecialchars($vars['refpage']);

	$add_assistant = edit_form_assistant();

	$body = <<<EOD
<form action="$script" method="post">
 <div class="edit_form" onmouseup="pukiwiki_pos()" onkeyup="pukiwiki_pos()">
$template
  $addtag
  <input type="hidden" name="cmd"    value="edit" />
  <input type="hidden" name="page"   value="$s_page" />
  <input type="hidden" name="digest" value="$s_digest" />
  <input type="hidden" name="id"     value="$s_id" />
  <textarea name="msg" rows="$rows" cols="$cols">$s_postdata</textarea>
  <br />
  $add_assistant
  <br />
  <input type="submit" name="preview" value="$btn_preview" accesskey="p" />
  <input type="submit" name="write"   value="$_btn_update" accesskey="s" />
  $add_top
  <input type="checkbox" name="notimestamp" value="true"$checked_time />
  <span style="small">$_btn_notchangetimestamp</span> &nbsp;
  <input type="submit" name="cancel"  value="$_btn_cancel" accesskey="c" />
  <textarea name="original" rows="1" cols="1" style="display:none">$s_original</textarea>
 </div>
</form>
EOD;

	if (isset($vars['help'])) {
		$body .= $hr . catrule();
	} else {
		$body .=
		"<ul><li><a href=\"$script?cmd=edit&amp;help=true&amp;page=$r_page\">$_msg_help</a></li></ul>";
	}

	return $body;
}

// ���ϥ����������
function edit_form_assistant()
{
	global $html_transitional;
	$html_transitional = TRUE;

	static $assist_loaded = 0;
	if (!$assist_loaded) {
		$map = <<<EOD
<map id="map_button" name="map_button">
<area shape="rect" coords="0,0,22,16" title="URL" alt="URL" href="#" onclick="javascript:pukiwiki_linkPrompt('url'); return false;" />
<area shape="rect" coords="24,0,40,16" title="B" alt="B" href="#" onclick="javascript:pukiwiki_tag('b'); return false;" />
<area shape="rect" coords="43,0,59,16" title="I" alt="I" href="#" onclick="javascript:pukiwiki_tag('i'); return false;" />
<area shape="rect" coords="62,0,79,16" title="U" alt="U" href="#" onclick="javascript:pukiwiki_tag('u'); return false;" />
<area shape="rect" coords="81,0,103,16" title="SIZE" alt="SIZE" href="#" onclick="javascript:pukiwiki_tag('size'); return false;" />
</map>
<map id="map_color" name="map_color">
<area shape="rect" coords="0,0,8,8" title="Black" alt="Black" href="#" onclick="javascript:pukiwiki_tag('Black'); return false;" />
<area shape="rect" coords="8,0,16,8" title="Maroon" alt="Maroon" href="#" onclick="javascript:pukiwiki_tag('Maroon'); return false;" />
<area shape="rect" coords="16,0,24,8" title="Green" alt="Green" href="#" onclick="javascript:pukiwiki_tag('Green'); return false;" />
<area shape="rect" coords="24,0,32,8" title="Olive" alt="Olive" href="#" onclick="javascript:pukiwiki_tag('Olive'); return false;" />
<area shape="rect" coords="32,0,40,8" title="Navy" alt="Navy" href="#" onclick="javascript:pukiwiki_tag('Navy'); return false;" />
<area shape="rect" coords="40,0,48,8" title="Purple" alt="Purple" href="#" onclick="javascript:pukiwiki_tag('Purple'); return false;" />
<area shape="rect" coords="48,0,55,8" title="Teal" alt="Teal" href="#" onclick="javascript:pukiwiki_tag('Teal'); return false;" />
<area shape="rect" coords="56,0,64,8" title="Gray" alt="Gray" href="#" onclick="javascript:pukiwiki_tag('Gray'); return false;" />
<area shape="rect" coords="0,8,8,16" title="Silver" alt="Silver" href="#" onclick="javascript:pukiwiki_tag('Silver'); return false;" />
<area shape="rect" coords="8,8,16,16" title="Red" alt="Red" href="#" onclick="javascript:pukiwiki_tag('Red'); return false;" />
<area shape="rect" coords="16,8,24,16" title="Lime" alt="Lime" href="#" onclick="javascript:pukiwiki_tag('Lime'); return false;" />
<area shape="rect" coords="24,8,32,16" title="Yellow" alt="Yellow" href="#" onclick="javascript:pukiwiki_tag('Yellow'); return false;" />
<area shape="rect" coords="32,8,40,16" title="Blue" alt="Blue" href="#" onclick="javascript:pukiwiki_tag('Blue'); return false;" />
<area shape="rect" coords="40,8,48,16" title="Fuchsia" alt="Fuchsia" href="#" onclick="javascript:pukiwiki_tag('Fuchsia'); return false;" />
<area shape="rect" coords="48,8,56,16" title="Aqua" alt="Aqua" href="#" onclick="javascript:pukiwiki_tag('Aqua'); return false;" />
<area shape="rect" coords="56,8,64,16" title="White" alt="White" href="#" onclick="javascript:pukiwiki_tag('White'); return false;" />
</map>
EOD;
		$assist_loaded++;
	}
	return <<<EOD
$map
<script type="text/javascript" src="skin/assistant.js"></script>
EOD;
}

// ��Ϣ����ڡ���
function make_related($page, $tag = '')
{
	global $script, $vars, $related, $rule_related_str, $related_str, $non_list;
	global $_ul_left_margin, $_ul_margin, $_list_pad_str;

	$links = links_get_related($page);

	if ($tag) {
		ksort($links);
	} else {
		arsort($links);
	}

	$_links = array();
	foreach ($links as $page=>$lastmod) {
		if (preg_match("/$non_list/", $page)) continue;

		$r_page   = rawurlencode($page);
		$s_page   = htmlspecialchars($page);
		$passage  = get_passage($lastmod);
		$_links[] = $tag ?
			"<a href=\"$script?$r_page\" title=\"$s_page $passage\">$s_page</a>" :
			"<a href=\"$script?$r_page\">$s_page</a>$passage";
	}

	if (empty($_links)) return '';

	if ($tag == 'p') { // ��Ƭ����
		$margin = $_ul_left_margin + $_ul_margin;
		$style  = sprintf($_list_pad_str, 1, $margin, $margin);
		$retval =  "\n<ul$style>\n<li>" . join($rule_related_str, $_links) . "</li>\n</ul>\n";
	} else if ($tag) {
		$retval = join($rule_related_str, $_links);
	} else {
		$retval = join($related_str, $_links);
	}

	return $retval;
}

// �桼������롼��(���������ִ���������С���)
function make_line_rules($str)
{
	global $line_rules;
	static $pattern, $replace;

	if (! isset($pattern)) {
		$pattern = array_map(create_function('$a', 'return "/$a/";'), array_keys($line_rules));
		$replace = array_values($line_rules);
		unset($line_rules);
	}

	return preg_replace($pattern, $replace, $str);
}

// HTML�����������
function strip_htmltag($str)
{
	global $_symbol_noexists;

	$noexists_pattern = '#<span class="noexists">([^<]*)<a[^>]+>' .
		preg_quote($_symbol_noexists, '#') . '</a></span>#';

	$str = preg_replace($noexists_pattern, '$1', $str);
	//$str = preg_replace('/<a[^>]+>\?<\/a>/', '', $str);
	return preg_replace('/<[^>]+>/', '', $str);
}

// �ڡ���̾����ڡ���̾�򸡺������󥯤����
function make_search($page)
{
	global $script, $WikiName;

	$s_page = htmlspecialchars($page);
	$r_page = rawurlencode($page);

	//WikiWikiWeb like...
	//if(preg_match("/^$WikiName$/", $page))
	//	$name = preg_replace("/([A-Z][a-z]+)/", "$1 ", $name);

	return "<a href=\"$script?cmd=search&amp;word=$r_page\">$s_page</a> ";
}

// ���Ф������� (����HTML���������)
function make_heading(& $str, $strip = TRUE)
{
	global $NotePattern;

	// ���Ф��θ�ͭID������
	$id = '';
	$matches = array();
	if (preg_match('/^(\*{0,3})(.*?)\[#([A-Za-z][\w-]+)\](.*?)$/m', $str, $matches)) {
		$str = $matches[2] . $matches[4];
		$id  = $matches[3];
	} else {
		$str = preg_replace('/^\*{0,3}/', '', $str);
	}

	if ($strip === TRUE)
		$str = strip_htmltag(make_link(preg_replace($NotePattern, '', $str)));

	return $id;
}

// Separate a page-name(or URL or null string) and an anchor
// (last one standing) without sharp
function anchor_explode($page, $strict_editable = FALSE)
{
	$pos = strrpos($page, '#');
	if ($pos === FALSE) return array($page, '', FALSE);

	// Ignore the last sharp letter
	if ($pos + 1 == strlen($page)) {
		$pos = strpos(substr($page, $pos + 1), '#');
		if ($pos === FALSE) return array($page, '', FALSE);
	}

	$s_page = substr($page, 0, $pos);
	$anchor = substr($page, $pos + 1);

	if($strict_editable === TRUE &&  preg_match('/^[a-z][a-f0-9]{7}$/', $anchor)) {
		return array ($s_page, $anchor, TRUE); // Seems fixed-anchor
	} else {
		return array ($s_page, $anchor, FALSE);
	}
}

// Check header()s were sent already, or
// there're blank lines or something out of php blocks
function pkwk_headers_sent()
{
	if (PKWK_OPTIMISE) return;

	if (version_compare(PHP_VERSION, '4.3.0', '>=')) {
		if (headers_sent($file, $line)) {
			print('Headers already sent at ' .
				htmlspecialchars($file) .
				' line ' . $line . '.');
			exit;
		}
	} else {
		if (headers_sent()) {
			print('Headers already sent.');
			exit;
		}
	}
}
?>
