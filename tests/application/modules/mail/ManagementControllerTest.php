<?php

class Mail_ManagementControllerTest extends ControllerTestCase
{

    /**
     * Set up
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->_doLogin(
            Users_Model_User::ROLE_ADMIN,
            Users_Model_User::STATUS_ACTIVE
        );

        $this->_fixture = array(
            'alias'       => 'registration',
            'subject'     => 'hello message'.time(),
            'description' => 'hello message desc'.date('Y-m-d H:i:s'),
            'bodyHtml'    => 'hello test' . time(),
            'bodyText'    => 'hello test' . time(),
            'fromName'    => 'test',
            'fromEmail'   => 'test@nixsolutions.com'
        );

        $this->_layout = 'Custom Layout '.time();

        $this->_table = new Mail_Model_Templates_Table();
    }

    /**
     * Test /admin/mail/
     *
     */
    public function testIndexAction()
    {
        $this->dispatch('/mail/management');
        $this->assertModule('mail');
        $this->assertController('management');
        $this->assertAction('index');
        $this->assertQuery('div#grid');
    }

    /**
     * Test /admin/mail/delete
     *
     */
    public function testDeleteAction()
    {
        $this->dispatch('/mail/management/delete');
        $this->assertModule('index');
        $this->assertController('error');
        $this->assertAction('notfound');
    }

    /**
     * Test /admin/mail/send
     *
     */
    public function testSendAction()
    {
        $this->dispatch('/mail/management/send');
        $this->assertModule('mail');
        $this->assertController('management');
        $this->assertAction('send');
        //$this->assertQuery('form#mailSendForm');

//        $this->dispatch('/mail/management/send/alias/35sasfd2');
//        $this->assertModule('mail');
//        $this->assertController('error');
//        $this->assertAction('internal');
//
//        $this->dispatch('/mail/management/send/alias/'.$this->_fixture['alias']);
//        $this->assertModule('mail');
//        $this->assertController('management');
//        $this->assertAction('send');
//        $this->assertQuery('form#mailSendForm');
    }

    /**
     * Test /admin/mail/edit
     *
     */
    public function testEditAction()
    {
        $mail = $this->_table->getModel($this->_fixture['alias']);
        $registration = array_merge($mail->toArray(), $this->_fixture);

        $this->request
             ->setMethod('POST')
             ->setPost(array_filter($registration));

        $this->dispatch('/mail/management/edit/id/' . $mail->id);

        //$this->assertQuery('form#mailEditForm');
        $this->assertRedirect();

        $mail = $this->_table->getModel($this->_fixture['alias']);

        $this->assertEquals($registration, $mail->toArray());
    }

}