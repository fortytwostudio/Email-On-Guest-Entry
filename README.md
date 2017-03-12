# Email-On-Guest-Entry
CraftCMS plugin that emails the "author" of the guestentry when a new guestentry is saved.

## Requirements
First, grab the [GuestEntries plugin](https://github.com/pixelandtonic/GuestEntries) and install as per it's instructions.

## How to use
Place the emailonguestentry folder in your craft/plugins/ folder. Activate the plugin in your Craft plugin settings page. That's it. It listens for the GuestEntry plugin firing an event saying that an entry's been saved and automatically send the emails.

## Optional additional fields
This plugin will handle the following optional field handles in the submitted form:

- `contactFormName` -- This field represents the name of the person filling out the form. It'll put that in the fromName field in the email. Defaults to `siteName`.
- `contactFormEmail` -- This field represents the email address of the person filling in the form. It'll use this in the replyTo field in the email. Defaults to the `emailAddress` from the Craft email settings.
- `sendCopyToUser` -- This field is normally represented on the front end by a checkbox. If supplied it'll CC the person filling in the form with their submission. You'll need to ensure it asks for `contactFormEmail` for this to work.
- `toEmail` -- If you don't want to use the user set as the author of the guestEntry type, you can pass an email address in this field. Helpful if you perhaps have a craft personal licence. Defaults to the author's email address set for the guest entry type.
