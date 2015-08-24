<?php

require 'phpmailer/class.phpmailer.php';

class Base_Mail {

    public static $instance;
    private $dbwrite;

    public function __construct($dbwrite) {
        $this->dbwrite = $dbwrite;
    }

    public static function getInstance($dbwrite) {
        if (!self::$instance) {
            self::$instance = new self($dbwrite);
        }
        return self::$instance;
    }

    /**
     * @todo 带配置的邮件发送类
     *
     * @param array $tomails
     * @param array $ccmails
     * @param string $title
     * @param string $body
     * @param array $attachments
     * @param string $signkey
     * @param string $smtp
     * @return boolean
     */
    function sendto($tomails, $ccmails = '', $title, $body, $attachments = '', $smtp) {
        /* if($this->signkey!==$signkey)
          {
          $this->mailservices_logs('ERROR',"signkey 认证失败",$tomails,$ccmails,$title,$body,$attachments);
          return false;
          } */
        if (empty($tomails)) {
            $this->mailservices_logs('ERROR', "发送邮件列表为空", $tomails, $ccmails, $title, $body, $attachments);
            return false;
        }
        if (empty($title)) {
            $this->mailservices_logs('ERROR', "邮件标题为空", $tomails, $ccmails, $title, $body, $attachments);
            return false;
        }
        if (empty($body)) {
            $this->mailservices_logs('ERROR', "邮件内容为空", $tomails, $ccmails, $title, $body, $attachments);
            return false;
        }

        /**
         * 获取SMTP配置
         */
        /* 	
          $db=$this->dbread;
          $sql = "select `ConfigValue` from `game_center`.`sys_config` where `ConfigName`='$smtp' limit 1";
          $query = $db->query($sql);
          if(!$query)
          {
          $this->mailservices_logs('ERROR',"获取SMTP信息错误; SQL: {$sql}; ERROR: ".$db->error(),$tomails,$ccmails,$title,$body,$attachments);
          return false;
          }
          $info = $db->result($query); */
        $info = $smtp;
        $info = str_replace("\r", '', $info);
        $info = explode("\n", $info);
        $smtpinfo = array();
        foreach ($info as $t) {
            $t = explode('=', $t);
            $smtpinfo[trim($t[0])] = $t[1];
        }
        if (!isset($smtpinfo['server']) || !isset($smtpinfo['port']) || !isset($smtpinfo['user']) || !isset($smtpinfo['pass']) || !isset($smtpinfo['mail'])) {
            $this->mailservices_logs('ERROR', "SMTP信息不完整; server: {$smtpinfo['server']}; port: {$smtpinfo['port']}; user: {$smtpinfo['user']}; pass: {$smtpinfo['pass']}; mail: {$smtpinfo['mail']}", $tomails, $ccmails, $title, $body, $attachments);
            return false;
        }
        //print_r($smtpinfo);
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->CharSet = 'UTF-8';
        try {
            $mail->Host = $smtpinfo['server']; // SMTP server
            $mail->SMTPAuth = true;                  // enable SMTP authentication
            $mail->Port = $smtpinfo['port'];                    // set the SMTP port for the GMAIL server
            $mail->Username = $smtpinfo['user']; // SMTP account username
            $mail->Password = $smtpinfo['pass'];        // SMTP account password
            $mail->AddReplyTo($smtpinfo['mail']);

            /**
             * 处理发送地址
             */
            if (is_string($tomails)) {
                /* substr($tomails,-1)!=';' && $tomails.=';';
                  eval('$tomails = ' . $tomails); */
                if (strpos($tomails, ',')) {
                    $tomails = explode(',', $tomails);
                } else {
                    $tomails = array($tomails);
                }
            }

            if (is_array($tomails)) {
                foreach ($tomails as $m) {
                    $mail->AddAddress($m);
                }
            } else {
                $this->mailservices_logs('ERROR', "发送邮件列表格式错误", $tomails, $ccmails, $title, $body, $attachments);
                return false;
            }
            /**
             * 处理抄送地址
             */
            if (!empty($ccmails)) {
                if (is_string($ccmails)) {
                    /* substr($ccmails,-1)!=';' && $ccmails.=';';
                      eval('$ccmails = ' . $ccmails); */
                    if (strpos($ccmails, ',')) {
                        $ccmails = explode(',', $ccmails);
                    } else {
                        $ccmails = array($ccmails);
                    }
                }

                if (is_array($ccmails)) {
                    foreach ($ccmails as $m) {
                        $mail->AddCC($m);
                    }
                } else {
                    $this->mailservices_logs('ERROR', "抄送邮件列表格式错误", $tomails, $ccmails, $title, $body, $attachments);
                    return false;
                }
            }

            /**
             * 处理附件
             */
            $attlist = array();
            if (!empty($attachments)) {
                if (is_string($attachments)) {
                    /* substr($attachments,-1)!=';' && $attachments.=';';
                      eval('$attachments = ' . $attachments); */
                    if (strpos($attachments, ',')) {
                        $attachments = explode(',', $attachments);
                    } else {
                        $attachments = array($attachments);
                    }
                }

                if (is_array($attachments)) {
                    foreach ($attachments as $attachment) {
                        $attpath = $attachment;
                        if (substr($attachment, 0, 7) == 'http://') {
                            $attpath = '../tmpatt/' . basename($attachment);
                            file_put_contents($attpath, file_get_contents($attachment));
                        }
                        $mail->AddAttachment($attpath);
                        $attlist[] = $attpath;
                    }
                } else {
                    $this->mailservices_logs('ERROR', "附件邮件列表格式错误", $tomails, $ccmails, $title, $body, $attachments);
                    return false;
                }
            }
            $mail->SetFrom($smtpinfo['mail']);
            $mail->Subject = stripslashes($title);
            $mail->MsgHTML(stripslashes($body));
            if (!$mail->Send()) {
                $this->mailservices_logs('ERROR', '发送失败; ERROR: ' . $mail->ErrorInfo, $tomails, $ccmails, $title, $body, $attachments);
                $this->delattlist($attlist);
                return false;
            } else {
                $this->mailservices_logs('SUCCESS', '发送成功', $tomails, $ccmails, $title, $body, $attachments);
                $this->delattlist($attlist);
                return true;
            }
        } catch (phpmailerException $e) {
            $this->mailservices_logs('ERROR', '发送失败; ERROR: ' . $e->errorMessage(), $tomails, $ccmails, $title, $body, $attachments);
            return false;
        } catch (Exception $e) {
            $this->mailservices_logs('ERROR', '发送失败; ERROR: ' . $e->getMessage(), $tomails, $ccmails, $title, $body, $attachments);
            return false;
        }
    }

    /**
     * 记录发送日志
     *
     * @param string $type
     * @param string $message
     * @param array $tomails
     * @param array $ccmails
     * @param string $title
     * @param string $body
     * @param array $attachments
     */
    function mailservices_logs($type, $message, $tomails, $ccmails, $title, $body, $attachments) {
        $db = $this->dbwrite;
        $tomails = is_array($tomails) ? implode(',', $tomails) : $tomails;
        $ccmails = is_array($ccmails) ? implode(',', $ccmails) : $ccmails;
        $attachments = is_array($attachments) ? implode(',', $attachments) : $attachments;
        $nowtime = date('Y-m-d H:i:s');
        $ip = $this->getClientIp();
        $sql = "insert into `game_center`.`sys_maillog`(`type`,`message`,`tomails`,`ccmails`,`title`,`body`,`attachments`,`time`,`ip`) values('{$type}','{$message}','{$tomails}','{$ccmails}','{$title}','{$body}','{$attachments}','{$nowtime}','$ip')";
        $db->query($sql);
    }

    /**
     * @todo 获取客户端ip
     * @return string
     */
    function getClientIp() {
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $CLIENT_IP = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $CLIENT_IP = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $CLIENT_IP = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $CLIENT_IP = $_SERVER['REMOTE_ADDR'];
        }
        return $CLIENT_IP;
    }

    /**
     * 删除临时附件
     *
     * @param array $list
     */
    function delattlist($list) {
        foreach ($list as $f)
            @unlink($f);
    }

}

?>