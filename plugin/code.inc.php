<?php
/**
 * �����ɥϥ��饤�ȵ�ǽ��PukiWiki���ɲä���
 * @author sky
 * Time-stamp: <05/07/30 20:00:55 sasaki>
 * 
 * GPL
 *
 * Ver. 0.5.0
 */

define('PLUGIN_CODE_LANGUAGE', 'pre');  // ɸ����� ���ƾ�ʸ���ǻ���
// ɸ������
define('PLUGIN_CODE_NUMBER',    true);  // ���ֹ�
define('PLUGIN_CODE_OUTLINE',   true);  // �����ȥ饤��;
define('PLUGIN_CODE_COMMENT',   false); // ������ɽ��/��ɽ�� // 0.5.0 �Ǥ���侩
define('PLUGIN_CODE_MENU',      true);  // ��˥塼��ɽ��/��ɽ��;
define('PLUGIN_CODE_FILE_ICON', true);  // ź�եե�����˥�������ɥ���������դ���
define('PLUGIN_CODE_LINK',      true);  // �����ȥ��
define('PLUGIN_CODE_CACHE',    false);  // ����å����Ȥ�


// URL�ǻ��ꤷ���ե�������ɤ߹��फ�ݤ�
define('PLUGIN_CODE_READ_URL',  false);

// �ơ��֥��Ȥ����ݤ�(false��CSS��div�ˤ��ʬ��)
define('PLUGIN_CODE_TABLE',     true);

// TAB��
define('PLUGIN_CODE_WIDTHOFTAB', '    ');
// �����ե����������
define('PLUGIN_CODE_IMAGE_FILE', IMAGE_DIR.'code_dot.png');

define('PLUGIN_CODE_OUTLINE_OPEN_FILE',  IMAGE_DIR.'code_outline_open.png');
define('PLUGIN_CODE_OUTLINE_CLOSE_FILE', IMAGE_DIR.'code_outline_close.png');

if (! defined('FILE_ICON')) {
	define('FILE_ICON',
	'<img src="' . IMAGE_DIR . 'file.png" width="20" height="20"' .
	' alt="file" style="border-width:0px" />');
}


define('PLUGIN_CODE_USAGE', 
	   '<p class="error">Plugin code: Usage:<br />#code[(Lang)]{{<br />src<br />}}</p>');


function pluing_code_init()
{
	global $javascript; $javascript = true;
}
function plugin_code_action()
{
	global $vars;
	global $_source_messages;
	
	if (PKWK_SAFE_MODE) die_message('PKWK_SAFE_MODE prohibits this');

	$vars['refer'] = $vars['page'];

	if (! is_page($vars['page']) || ! check_readable($vars['page'],false,false)) {
		return array( 'msg' => $_source_messages['msg_notfound'],
					  'body' => $_source_messages['err_notfound'] );
	}
	return array( 'msg' => $_source_messages['msg_title'],
				  'body' => plugin_code_convert('pukiwiki',
												join('',get_source($vars['page']))."\n"));
}

function plugin_code_convert()
{
	if (file_exists(PLUGIN_DIR.'code/codehighlight.php'))
		require_once(PLUGIN_DIR.'code/codehighlight.php');
	else
		die_message('file '.PLUGIN_DIR.'code/codehighlight.php not exist or not readable.');

	static $plugin_code_jscript_flag = true;
	
	$title = '';
	$lang = null;
	$option = array(
					'number'      => false,  // ���ֹ��ɽ������
					'nonumber'    => false,  // ���ֹ��ɽ�����ʤ�
					'outline'     => false,  // �����ȥ饤�� �⡼��
					'nooutline'   => false,  // �����ȥ饤�� ̵��
					'comment'     => false,  // �����ȳ��Ĥ���
					'nocomment'   => false,  // �����ȳ��Ĥ��ʤ�
					'menu'        => false,  // ��˥塼��ɽ������
					'nomenu'      => false,  // ��˥塼��ɽ�����ʤ�
					'icon'        => false,  // ���������ɽ������
					'noicon'      => false,  // ���������ɽ�����ʤ�
					'link'        => false,  // �����ȥ�� ͭ��
					'nolink'      => false,  // �����ȥ�� ̵��
					);
	
    $num_of_arg = func_num_args();
    $args = func_get_args();
    if ($num_of_arg < 1) {
        return PLUGIN_CODE_USAGE;
    }

	$arg = $args[$num_of_arg-1];
    if (strlen($arg) == 0) {
        return PLUGIN_CODE_USAGE;
    }

	if ($num_of_arg != 1 && ! _plugin_code_check_argment($args[0], $option)) {
		$is_setlang = true;
        $lang = htmlspecialchars(strtolower($args[0])); // ����̾�����ץ�����Ƚ��
	} else
		$lang = PLUGIN_CODE_LANGUAGE; // default

	$begin = 0;
	$end = null;
	// ���ץ�����Ĵ�٤�
	for ($i = 1;$i < $num_of_arg-1; ++$i) {
		if (! _plugin_code_check_argment($args[$i], $option))
			_plugin_code_get_region($args[$i], $begin, $end);
	}
	$multiline = _plugin_code_multiline_argment($arg, $data, $option, $begin, $end);
	
	if (PLUGIN_CODE_CACHE && ! $multiline) { 
		$html = _plugin_code_read_cache($arg);
		if ($html != '' or $html != null)
			return $html;
	}		
	
	if (isset($data['_error']) && $data['_error'] != '') {
		return $data['_error'];
	}
	$lines = $data['data'];
	$title = $data['title'];
	
	$highlight = new CodeHighlight;
	$lines = $highlight->highlight($lang, $lines, $option);
	$lines = '<div class="'.$lang.'">'.$lines.'</div>';
	
	if ($plugin_code_jscript_flag && ($option['outline'] || $option['comment'])) {
		$plugin_code_jscript_flag = false;
		$title .= '<script type="text/javascript" src="'.SKIN_DIR.'code.js"></script>'."\n";
	}
	$html = $title.$lines;
	if (PLUGIN_CODE_CACHE && ! $multiline) {
		_plugin_code_write_cache($arg, $html);
	}
    return $html;
}

/**
 *  ����å���˽񤭹���
 * ������ź�եե�����̾, HTML�Ѵ���Υե�����
 */
function _plugin_code_write_cache($fname, $html)
{
	global $vars;
	// ź�եե�����Τ���ڡ���: default�ϸ��ߤΥڡ���̾
	$page = isset($vars['page']) ? $vars['page'] : '';
	
	// �ե�����̾�˥ڡ���̾(�ڡ������ȥѥ�)����������Ƥ��뤫
	//   (Page_name/maybe-separated-with/slashes/ATTACHED_FILENAME)
	if (preg_match('#^(.+)/([^/]+)$#', $fname, $matches)) {
		if ($matches[1] == '.' || $matches[1] == '..')
			$matches[1] .= '/'; // Restore relative paths
			$fname = $matches[2];
			$page = get_fullname(strip_bracket($matches[1]), $page); // strip is a compat
			$file = encode($page) . '_' . encode($fname);
	} else {
		// Simple single argument
		$file =  encode($page) . '_' . encode($fname);
	}
	$fp = fopen(CACHE_DIR.'code/'.$file.'.html', 'w') or
		die_message('Cannot write cache file ' .
					CACHE_DIR.'code/'. $file .'.html'.
					'<br />Maybe permission is not writable or filename is too long');
	
	set_file_buffer($fp, 0);
	flock($fp, LOCK_EX);
	rewind($fp);
	fputs($fp, $html);
	flock($fp, LOCK_UN);
	fclose($fp);
}

/**
 * ����å�����ɤ߽Ф�
 * ������ź�եե�����̾
 * �Ѵ����줿�ե�����ǡ������֤�
 */
function _plugin_code_read_cache($fname)
{
	global $vars;
	// ź�եե�����Τ���ڡ���: default�ϸ��ߤΥڡ���̾
	$page = isset($vars['page']) ? $vars['page'] : '';
	
	// �ե�����̾�˥ڡ���̾(�ڡ������ȥѥ�)����������Ƥ��뤫
	//   (Page_name/maybe-separated-with/slashes/ATTACHED_FILENAME)
	if (preg_match('#^(.+)/([^/]+)$#', $fname, $matches)) {
		if ($matches[1] == '.' || $matches[1] == '..')
			$matches[1] .= '/'; // Restore relative paths
		$fname = $matches[2];
		$page = get_fullname(strip_bracket($matches[1]), $page); // strip is a compat
		$file = encode($page) . '_' . encode($fname);
	} else {
		// Simple single argument
		$file =  encode($page) . '_' . encode($fname);
	}
	
	/* Read file data */
	$fdata = '';
	$filelines = file(CACHE_DIR.'code/'.$file.'.html');
	
	foreach ($filelines as $line)
		$fdata .= $line;
	
	return $fdata;
}
?>
