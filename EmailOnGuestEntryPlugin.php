<?php
namespace Craft;

class EmailOnGuestEntryPlugin extends BasePlugin
{
    /**
     * Returns the plugin’s version number.
     *
     * @return string The plugin’s version number.
     */
    public function getVersion()
    {
        return '1.0.0';
    }

    /**
     * Returns the plugin developer’s name.
     *
     * @return string The plugin developer’s name.
     */
    public function getDeveloper()
    {
        return "FortyTwo Studio";
    }

    /**
     * Returns the plugin developer’s URL.
     *
     * @return string The plugin developer’s URL.
     */
    public function getDeveloperUrl()
    {
        return "http://fortytwo.studio";
    }

    /**
     * Sends an email to admin when the contact form entry's been saved.
     * It'll send to whoever the guest entry user is set to in the CMS
     */
    public function init()
    {
        craft()->on('guestEntries.success', function (GuestEntriesEvent $event)
        {
            /** @var EntryModel $entryModel */
            $entryModel = $event->params['entry'];

            $emailSettings      = craft()->email->getSettings();
            $email              = new EmailModel;
            $email->toFirstName = $entryModel->getAuthor()->firstName;
            $email->toLastName  = $entryModel->getAuthor()->lastName;
            $email->toEmail     = $entryModel->toEmail ?? $entryModel->getAuthor()->email;
            $email->fromEmail   = $emailSettings['emailAddress'];
            $email->replyTo     = $entryModel->contactFormEmail ?? $emailSettings['emailAddress'];
            $email->sender      = $emailSettings['emailAddress'];
            $email->fromName    = $entryModel->contactFormName ?? 'Contact Form';
            $email->subject     = "New Contact Form Submitted";
            $email->body        = "";
            /** @var FieldLayoutFieldModel $fieldLayout */
            foreach ($entryModel->getFieldLayout()->getFields() as $fieldLayout)
            {
                $field = $fieldLayout->getField();
                $email->body .= $field->__toString() . ": " . $entryModel->{$field->handle} . "\n";
            }
            $email->htmlBody = nl2br($email->body);

            craft()->email->sendEmail($email);
        });
    }
}
