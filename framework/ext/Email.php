<?php

/**
* 邮件发送类
*/

namespace framework\ext;

require_once __DIR__ . '/phpmailer/class.phpmailer.php';
require_once __DIR__ . '/phpmailer/class.smtp.php';

class Email {

    /**
     * 邮件类配置
     * @var array
     */
    protected $config = array(
        'smtp_host'      => 'smtp.qq.com',  //smtp主机
        'smtp_port'      => '465',          //端口号
        'smtp_ssl'       => false,          //安全链接
        'smtp_username'  => '',             //邮箱帐号
        'smtp_password'  => '',             //邮箱密码
        'smtp_from_to'   => '',             //发件人邮箱
        'smtp_from_name' => 'canphp',       //发件人
    );

    /**
    * @var objcet 邮件对象
    */
   protected $mail;

    /**
     * 构建函数
     * @param array $config 邮箱配置
     */
    public function __construct( $config = array() ) {
        $this->config = array_merge($this->config, $config);
        $this->mail = new \PHPMailer();
        $this->mail->isSMTP();
        $this->mail->Host = $this->config['smtp_host'];
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $this->config['smtp_username'];
        $this->mail->Password = $this->config['smtp_password'];
        if($this->config['smtp_ssl']){
            $this->mail->SMTPSecure = 'ssl';
        }else{
            $this->mail->SMTPSecure = 'tls';
        }
        $this->mail->Port = $this->config['port'];
        $this->mail->setFrom($this->config['smtp_from_to'], $this->config['smtp_from_name']);
        $this->mail->isHTML(true);
    }

    /**
    * 设置抄送，多个抄送，调用多次
    * @param string $cc 抄送地址
    * @return objcet
    */
    public function setCc($cc) {
        $ccs = explode(",",$cc);
        foreach($ccs as $cc) {
            if (preg_match("/^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/", $cc)) {
                $this->mail->addCC($cc);
            }
        }
        return $this;
    }
 
    /**
    * 设置秘密抄送，多个秘密抄送，调用多次
    * @param string $bcc 秘密抄送地址
    * @return objcet
    */
    public function setBcc($bcc) {
       $bccs = explode(",",$bcc);
        foreach($bccs as $bcc) {
            if (preg_match("/^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/", $bcc)) {
                $this->mail->addBCC($bcc);
            }
        }
        return $this;
    }
 
    /**
    * 设置邮件附件，多个附件，调用多次
    * @access public
    * @param string $file 文件地址
    * @return boolean
    */
    public function addAttachment($file) {
        foreach($file as $attachment) {
            if(empty($attachment['path'])) continue;
            $filename = isset($attachment['name']) ? $attachment['name'] : substr(strrchr($attachment['path'], "/"), 1);
            $this->mail->addAttachment($attachment['path'], $filename);
        }
    }

    /**
    * 设置邮件信息
    * @access public
    * @param string $title 邮件主题
    * @param string $body  邮件内容
    * @return boolean
    */
    public function setMail($title, $body) {
        $this->mail->Subject = $title;
        $this->mail->Body    = $body;
        return $this;
    }

    /**
     * 发送邮件
     * @param  string $to 收件人
     * @return boolean
     */
    public function sendMail($to) {
        $sendTo = explode(",", $to);
        foreach($sendTo as $add) {
            if (preg_match("/^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/", $add)) {
                $this->mail->addAddress($add);
            }
        }
        if(!$this->mail->Send()) {
            $return = false;
        } else {
            $return = true;
        }
        return $return;

    }

    /**
    * 返回错误信息
    * @return string
    */
    public function getError(){
        return $this->mail->ErrorInfo;
    }


    /**
     * 析构函数
     */
    public function __destruct(){
        $this->mail->SmtpClose();
        $this->mail = null;
    }
}