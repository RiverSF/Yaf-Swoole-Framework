<?php
/**
 * Created by PhpStorm.
 * User: River
 * Date: 2019/2/26
 * Time: 18:46
 */

class Email
{
    const CHARSET = 'UTF-8';
    const HOST  = 'smtp.exmail.qq.com';
    const SMTP_AUTH = true;
    const USER_NAME = 'xxx@xxx.com';
    const PASSWORD  = 'password';
    const SMTP_SECURE = 'ssl';
    const PORT = 465;

    private $body;
    private $address;
    private $subject;
    private $attachment = [];

    /**
     * Email constructor.
     * @param array $address
     * @param string $body
     * @param string $subject
     * @param array $attachment
     */
    public function __construct(array $address, $body = '', $subject = '邮件通知', array $attachment = [])
    {
        $this->address  =   $address;
        $this->body     =   $body;
        $this->subject  =   $subject;
        $this->attachment   =   $attachment;
    }

    /**
     * @return bool
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function sendMail()
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer();

        $mail->isSMTP();
        $mail->CharSet = self::CHARSET;
        $mail->Host = self::HOST;
        $mail->SMTPAuth = self::SMTP_AUTH;
        $mail->Username = self::USER_NAME;
        $mail->Password = self::PASSWORD;
        $mail->SMTPSecure = self::SMTP_SECURE;
        $mail->Port = self::PORT;

        //发件人
        $mail->setFrom('xxx@xxx.com', '邮件通知');

        //收件人
        foreach ($this->address as $add) {
            $mail->addAddress($add, '接收人名称');
        }

        //附件
        foreach ($this->attachment as $file) {
            $mail->addAttachment($file);
        }

        //内容
        $mail->isHTML(true);
        $mail->Subject = $this->subject;
        $mail->Body = empty($body) ? '系统通知' : $body;

        $result = $mail->send();
        if (!$result) {
            Log::error('Mailer Error: ' . $mail->ErrorInfo);
            return false;
        }
        return true;
    }
}