<?php
/**
 * Abstract.php
 * Created by Igor Malinovskiy <u.glide@gmail.com>.
 * Date: 19.07.12
 */
abstract class Core_Mail_Templates_Abstract
{
    /**
     * Default values for mail template
     *
     * @var array
     */
    protected $_data = array(
        'toEmail'      => null,
        'toName'       => null,
        'fromEmail'    => null,
        'fromName'     => null,
        'bcc'          => null,
        'subject'      => null,
        'bodyHtml'     => null,
        'bodyText'     => null,
        'attachments'  => null,
        'sendExternal' => null,
    );

    /**
     * Constructor;
     * Sets a values to default properties
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->setFromArray($data);
    }

    /**
     * Set from array
     *
     * @param  array $data
     * @return self
     */
    public function setFromArray(array $data)
    {
        $this->_data = array_merge($this->_data, $data);
        return $this;
    }

    /**
     * Get array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_data;
    }

    /**
     * Assign data to template
     *
     * @param string $name
     * @param string $value
     * @return self
     */
    public abstract function assign($name, $value);

    /**
     * Get property
     *
     * @param string $key
     * @return string|null
     */
    public function __get($key)
    {
        if (isset($this->_data[$key])) {
            return $this->_data[$key];
        }
        return null;
    }

    /**
     * Set property
     *
     * @param string $key
     * @param string $value
     */
    public function __set($key, $value)
    {
        if (array_key_exists($key, $this->_data)) {
            $this->_data[$key] = $value;
        }
    }

    /**
     * Send email
     *
     * @param Zend_Mail $mail
     * @return Zend_Mail
     */
    public function send(Zend_Mail $mail = null)
    {
        if ($mail) {
            $mail = clone $mail;
        } else {
            $mail = new Zend_Mail();
        }
        $mail = $this->populate($mail);

        return $mail->send();
    }

    /**
     * Populate mail instance
     *
     * @param Zend_Mail $mail
     * @return Zend_Mail
     */
    public function populate(Zend_Mail $mail)
    {
        if ($this->fromEmail || $this->fromName) {
            $mail->setFrom($this->fromEmail, $this->fromName);
        }
        if ($this->toEmail || $this->toName) {
            $mail->addTo($this->toEmail, $this->toName);
        }
        if ($this->bcc) {
            if (!is_array($this->bcc)) {
                $mail->addBcc($this->bcc);
            } else {
                if (isset($this->bcc['email']) && !empty($this->bcc['email'])) {
                    if (isset($this->bcc['name']) && !empty($this->bcc['name'])) {
                        $mail->addBcc($this->bcc['email'], $this->bcc['name']);
                    } else {
                        $mail->addBcc($this->bcc['email']);
                    }
                } elseif (count($this->bcc)) {
                    foreach($this->bcc as $bcEmail) {
                        $mail->addBcc($bcEmail);
                    }
                }
            }
        }
        if ($this->subject) {
            $mail->setSubject($this->subject);
        }
        if ($this->bodyHtml) {
            $mail->setBodyHtml($this->bodyHtml);
        }
        if ($this->bodyText) {
            $mail->setBodyText($this->bodyText);
        }
        if ($this->attachments && is_array($this->attachments)) {
            foreach ($this->attachments as $file) {
                if (file_exists($file['path'])) {
                    $fileContent = file_get_contents($file['path']);
                    if ($fileContent) {
                        $attachment = new Zend_Mime_Part($fileContent);
                        $attachment->type        = Zend_Mime::TYPE_OCTETSTREAM;
                        $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
                        $attachment->encoding    = Zend_Mime::ENCODING_BASE64;
                        $attachment->filename    = basename($file['path']);
                        $attachment->id = md5(time());
                        $attachment->description = $attachment->filename;

                        $mail->addAttachment($attachment);
                    }
                }
            }
        }
        if ($this->sendExternal) {
            $mail->addHeader('SEND_TO_EXTERNAL_SMTP', 'True');
        }
        return $mail;
    }

}
