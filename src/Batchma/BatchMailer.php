<?php

namespace hdvianna\Batchma;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Twig_Environment;

class BatchMailer implements \hdvianna\Core\CommandInterface {

    private $argumentManager;

    /**
     *
     * @var Twig_Environment 
     */
    private $twig;
    private $templatePathInfo;

    public function __construct() {
        $this->argumentManager = new \hdvianna\Core\ArgManager("Batchma, the PHP batch mailer");
        $this->argumentManager->addArgumentDefinition("h", "SMTP host")
                ->addArgumentDefinition("u", "SMTP user")
                ->addArgumentDefinition("w", "SMTP password")
                ->addArgumentDefinition("p", "SMTP port")
                ->addArgumentDefinition("s", "Subject")
                ->addArgumentDefinition("a", "From address")
                ->addArgumentDefinition("n", "From name")
                ->addArgumentDefinition("d", "Data definition path")
                ->addArgumentDefinition("t", "Mail template path")
                ->addArgumentDefinition("f", "Comma-separated attachments path", true);
    }

    /**
     * 
     * @param PHPMailer $mail
     */
    private function addAttachments($mail) {
        $arguments = $this->argumentManager->getArguments();
        if (array_key_exists("f", $arguments)) {
            $attachments = explode(",", $arguments['f']);
            foreach ($attachments as $attachment) {
                $mail->addAttachment($attachment);
            }
        }
    }

    /**
     * 
     * @param string $email
     * @param array $definition
     * @return PHPMailer
     */
    private function createMail($email, $definition) {
        $mail = new PHPMailer(true);
        //Server settings
        $mail->isSMTP();
        $mail->Host = $this->argumentManager->getArguments()['h'];
        $mail->SMTPAuth = true;
        $mail->Username = $this->argumentManager->getArguments()['u'];
        $mail->Password = $this->argumentManager->getArguments()['w'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = $this->argumentManager->getArguments()['p'];
        //Recipients
        $mail->setFrom($this->argumentManager->getArguments()['a'], $this->argumentManager->getArguments()['n']);
        $mail->addAddress($email, array_key_exists('name', $definition) ? $definition['name'] : '');
        //Content
        $mail->isHTML(false);
        $mail->Subject = $this->argumentManager->getArguments()['s'];
        $mail->Body = $this->twig->render($this->templatePathInfo['basename'], $definition);
        $this->addAttachments($mail);

        return $mail;
    }

    private function sendMails() {
        echo "Sending mails ..." . PHP_EOL;
        $definition = json_decode(file_get_contents($this->argumentManager->getArguments()['d']), true);
        foreach ($definition as $email => $data) {
            $mail = $this->createMail($email, $data);
            try {
                echo "Sending mail to $email" . PHP_EOL;
                $mail->send();
                echo "Mail sent." . PHP_EOL;
            } catch (Exception $e) {
                echo 'Mail could not be sent. Mailer Error: ', $mail->ErrorInfo . PHP_EOL;
            }
        }
    }

    public function execute() {
        if ($this->argumentManager->check()) {
            $this->templatePathInfo = pathinfo($this->argumentManager->getArguments()['t']);
            $loader = new \Twig_Loader_Filesystem($this->templatePathInfo['dirname']);
            $this->twig = new \Twig_Environment($loader, []);
            $this->sendMails();
        } else {
            $this->argumentManager->showDescription();
        }
    }

}
