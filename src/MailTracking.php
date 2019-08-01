<?php

namespace Forrestedw\Mailtracker;

use Illuminate\Support\Facades\Mail;

trait MailTracking
{
    protected $emails = [];

    /**
     * @before
     */
    protected function setUpMailTracking(): void
    {
        parent::setUp();

        Mail::getSwiftMailer()
            ->registerPlugin(new class($this) implements \Swift_Events_EventListener {
                                protected $test;

                                public function __construct($test)
                                {
                                    $this->test = $test;
                                }
                                public function beforeSendPerformed($event)
                                {
                                    $this->test->addEmail($event->getMessage());
                                }
                            });
    }

    protected function assertEmailWasSent()
    {
        $this->assertNotEmpty(
            $this->emails, 'No emails have been sent.'
        );

        return $this;
    }

    protected function assertEmailWasNotSent()
    {
        $x = \count($this->emails);

        $wh = ($x === 1) ? 'was' : 'were';

        $this->assertEmpty(
            $this->emails, "Expected no emails to have been sent, but {$x} {$wh}."
        );

        return $this;
    }

    protected function assertEmailsSent($expected)
    {
        $actual = \count($this->emails);

        $this->assertEquals(
            $actual, $expected, "Expected {$expected} emails to be sent, only {$actual} sent."
        );

        return $this;
    }

    protected function assertEmailTo($recipient, Swift_Message $message = null)
    {
        $this->assertArrayHasKey(
            $recipient, $this->getEmail($message)->getTo(),
            "No email was sent to {$recipient}"
        );

        return $this;
    }

    protected function assertEmailNotTo($recipient, Swift_Message $message = null)
    {
        $this->assertArrayNotHasKey(
            $recipient, $this->getEmail($message)->getTo(),
            "An email was sent to {$recipient}"
        );

        return $this;
    }


    protected function assertEmailFrom($sender, Swift_Message $message = null)
    {
        $this->assertArrayHasKey(
            $sender, $this->getEmail($message)->getFrom(),
            "No email was sent from {$sender}"
        );

        return $this;
    }

    protected function assertEmailNotFrom($sender, Swift_Message $message = null)
    {
        $this->assertArrayNotHasKey(
            $sender, $this->getEmail($message)->getFrom(),
            "An email was sent from {$sender}"
        );

        return $this;
    }

    protected function assertEmailEquals($body, Swift_Message $message = null)
    {
        $this->assertEquals(
            $body, $this->getEmail($message)->getBody(),
            "No email with the provided body was sent.");

        return $this;
    }

    protected function assertEmailDoesNotEqual($body, Swift_Message $message = null)
    {
        $this->assertNotEquals(
            $body, $this->getEmail($message)->getBody(),
            "An email with the provided body was found.");

        return $this;
    }

    protected function assertEmailContains($excerpt, Swift_Message $message = null)
    {
        $this->assertContains(
            $excerpt, $this->getEmail($message)->getBody(),
            "No email containing '{$excerpt}' was found.");

        return $this;
    }

    protected function assertEmailDoesNotContain($excerpt, Swift_Message $message = null)
    {
        $this->assertNotContains(
            $excerpt, $this->getEmail($message)->getBody(),
            "An email contains the string '{$excerpt}' was found.");

        return $this;
    }

    protected function assertEmailSubjectIs($subject, Swift_Message $message = null)
    {
        $this->assertEquals(
            $subject, $this->getEmail($message)->getSubject(),
            "The email subject isn't '{$subject}'."
        );

        return $this;
    }

    protected function assertEmailSubjectIsNot($subject, Swift_Message $message = null)
    {
        $this->assertNotEquals(
            $subject, $this->getEmail($message)->getSubject(),
            "The email subject is '{$subject}'."
        );

        return $this;
    }

    public function addEmail(\Swift_Message $email)
    {
        $this->emails[] = $email;
    }

    protected function getEmail(Swift_Message $message = null)
    {
        $this->assertEmailWasSent();

        return $message ?: $this->lastEmail();
    }

    protected function lastEmail()
    {
        return \end($this->emails);
    }
}

//class TestingMailEventListener implements \Swift_Events_EventListener
//{
//    protected $test;
//
//    public function __construct($test)
//    {
//        $this->test = $test;
//    }
//    public function beforeSendPerformed($event)
//    {
//        $this->test->addEmail($event->getMessage());
//    }
//}