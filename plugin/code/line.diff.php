<?php
/**
 * diff �����������ե�����
 * �Իظ��⡼����
 */

$switchHash['!'] = IDENTIFIRE_CHAR;   // changed
$switchHash['|'] = IDENTIFIRE_CHAR;   // changed
$switchHash['+'] = IDENTIFIRE_WORD;   // added
$switchHash['>'] = IDENTIFIRE_CHAR;   // added
$switchHash[')'] = IDENTIFIRE_CHAR;   // added
$switchHash['-'] = IDENTIFIRE_WORD;   // removed
$switchHash['<'] = IDENTIFIRE_CHAR;   // removed
$switchHash['('] = IDENTIFIRE_CHAR;   // removed
$switchHash['*'] = IDENTIFIRE_CHAR;   // control
$switchHash['\\']= IDENTIFIRE_CHAR;   // control
$switchHash['@'] = IDENTIFIRE_CHAR;   // control

$mkoutline = $option["outline"] = false; // �����ȥ饤��⡼���Բ� 
$mkcomment = $option["comment"] = false; // ������̵�� 
$linemode = true; // �������Ϥ��ʤ�

// 
$code_identifire = array(
	 '-' => Array(
		  '---',
		 ),
	 '+' => Array(
		  '+++',
		 ),
	 );


// ���������
$switchHash["#"] = COMMENT;	// �����Ȥ� # ������Ԥޤ�

$code_css = Array(
					   'changed', //
					   'added',   //
					   'removed', //

					   'system', //
);

$code_keyword = Array(
						   '!' => 1,
						   '|' => 1,

						   '+' => 2,
						   '>' => 2,
						   ')' => 2,
						   '/' => 2,

						   '-' => 3,
						   '<' => 3,
						   '(' => 3,
						   '\\' => 3,

						   '*' => 4,
						   '\\' => 4,
						   '@' => 4,
						   '---' => 4,
						   '+++' => 4,
);
?>