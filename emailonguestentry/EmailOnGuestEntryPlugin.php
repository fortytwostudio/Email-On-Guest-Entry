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
        return '1.0.4';
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
     * This is why comments are a bad idea in code, they're always out of date. Let's try again anyway.
     * This builds and sends a notification email to the email address given as the field in the entry with
     * the handle of 'toEmail' and if that field doesn't exist it sends it to the person set as the author
     * for GuestEntries in that section. It uses null coalescence so needs PHP7 folks. Yes I could make it
     * support lower PHP versions but if I did that what would you learn?
     */
    public function init()
    {
        craft()->on('guestEntries.success', function (GuestEntriesEvent $event)
        {
            /** @var EntryModel $entryModel */
            $entryModel = $event->params['entry'];

            $emailSettings = craft()->email->getSettings();
            $email         = new EmailModel;

            // send copy of email to the user
            if (!empty($entryModel->sendCopyToUser))
            {
                $email->cc = array(
                    array(
                        'name'  => '',
                        'email' => $entryModel->contactFormEmail,
                    ),
                );
            }
            $email->toFirstName = $entryModel->getAuthor()->firstName;
            $email->toLastName  = $entryModel->getAuthor()->lastName;
            $email->toEmail     = !empty($entryModel->toEmail) ? $entryModel->toEmail : $entryModel->getAuthor()->email;
            $email->fromEmail   = $emailSettings['emailAddress'];
            $email->replyTo     = !empty($entryModel->contactFormEmail) ? $entryModel->contactFormEmail : $emailSettings['emailAddress'];
            $email->sender      = $emailSettings['emailAddress'];
            $email->fromName    = !empty($entryModel->contactFormName) ? $entryModel->contactFormName : craft()->config->get('siteName');
            $email->subject     = craft()->config->get('siteName') . " - New {$entryModel->getType()->name} Received";
            $email->body        = "";
            /** @var FieldLayoutFieldModel $fieldLayout */
            foreach ($entryModel->getFieldLayout()->getFields() as $fieldLayout)
            {
                $field = $fieldLayout->getField();
                if ($field->getFieldType()->getName() == Craft::t('Lightswitch'))
                {
                    $value = ($entryModel->{$field->handle}) ? "Yes" : "No";
                } elseif ($field->getFieldType()->getName() == Craft::t('Checkboxes'))
                {
                    $values = array();
                    foreach ($entryModel->{$field->handle}->getOptions() as $option)
                    {
                        if ($option->selected)
                        {
                            $values[] = $option->label;
                        }
                    }
                    $value = implode(", ", $values);
                } else
                {
                    $value = $entryModel->{$field->handle};
                }
                $email->body .= $field->__toString() . ": \n" . $value . "\n \n";
                $email->htmlBody .= "<strong>{$field->__toString()}</strong><br><pre>{$value}</pre><br>\n";
            }

            craft()->email->sendEmail($email);
        });
    }
}
