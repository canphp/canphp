<?php

/**
 * 邮件发送驱动
 */

namespace framework\ext\send;

class EmailDriver implements SendInterface{

	protected $config = array(
        'smtp_host'      => 'smtp.qq.com',  //smtp主机
        'smtp_port'      => '465',          //端口号
        'smtp_ssl'       => false,          //安全链接
        'smtp_username'  => '',             //邮箱帐号
        'smtp_password'  => '',             //邮箱密码
        'smtp_from_to'   => '',             //发件人邮箱
        'smtp_from_name' => 'canphp',       //发件人
        );
	protected $errorMsg = '';
    protected $email;

    public function __construct( $config = array() ) {
		$this->config = array_merge($this->config, $config);
        $this->mail = new \framework\ext\Email($this->config);
    }

    /**
     * 发送邮件
     * @param  string  $to      收信人
     * @param  string  $title   标题
     * @param  string  $content 内容
     * @param  array   $data    其他数据
     * @return array
     */
    public function push($to, $title, $content, $time = '', $data = array()) {
        return $this->mail->setMail($title, $content)->sendMail($to);
    }

    public function getError(){
        return $this->mail->getError();
    }
}