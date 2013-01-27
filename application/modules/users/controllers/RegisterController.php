<?php
/**
 * RegisterController for default module
 *
 * @category   Application
 * @package    Users
 * @subpackage Controller
 *
 * @version  $Id: RegisterController.php 170 2010-07-26 10:56:18Z AntonShevchuk $
 */
class Users_RegisterController extends Core_Controller_Action
{
    /**
     * Init controller plugins
     */
    public function init()
    {
        parent::init();
        /* Initialize action controller here */
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');

        /* Initialize user model */
        $this->_manager = new Users_Model_User_Manager();
    }

    /**
     * Forget password action
     *
     * 2-steps password restore:
     *
     * 1: confirm on password restoration sents to users' email
     * 2: if confirmed, new password generated and sends to user email
     * (forgetPwdConfirmAction)
     */
    public function forgetPasswordAction()
    {
        $form = new Users_Form_Auth_Forget();

        if ($this->_request->isPost()) {
            if ($form->isValid($this->_getAllParams())) {
                $user = $this->_manager
                             ->forgetPassword($form->getValue('email'));
                if ($user) {
                    // send email
                    Mail_Model_Mail::forgetPassword($user);

                    $message = 'The confirmation email to reset your '
                             . 'password is sent. Please check your email ';

                    $this->_flashMessenger->addMessage($message);

                    $this->_helper->redirector->gotoRoute(array(), 'login');
                }
            } else {
                // show errors
                $errors = $form->getErrors();
                foreach ($errors as $fn => $error) {
                    if (empty($error)) continue;
                    $el = $form->getElement($fn);
                    $dec = $el->getDecorator('HtmlTag');
                    $cls = $dec->getOption('class');
                    $dec->setOption('class', $cls .' error');
                }
            }
        }
        $this->view->form = $form;
    }

}