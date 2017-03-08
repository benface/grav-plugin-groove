# Grav Groove Plugin

This [Grav](http://github.com/getgrav/grav) plugin creates a ticket in your [Groove](https://www.groovehq.com) mailbox when the user submits a form.

## Installing / Updating

You can install this plugin through the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm):

    bin/gpm install groove

This will install the plugin into your `/user/plugins` directory within Grav. To update to the latest version:

    bin/gpm update groove

## Configuration

User configuration should go in `/user/config/plugins/groove.yaml`. Here's a sample config file:

    enabled: true
    api_token: xxxxxxxxxxxxx
    to: contact@yourdomain.com
    subject: Contact Form

* `api_token` **(required)**: Your private token from Groove.
* `to` (optional): The email address of the mailbox to create tickets in. This can be overridden in the page that contains the form. If it is not set here, then it has to be set in the page.
* `subject` (optional): The default subject for new tickets. This setting can also be overridden/set in a page, or be omitted entirely (Groove uses the first characters of the body as the subject when it is not specified).

## Usage

Here's how you would use this plugin in a page that contains a form:

    form:
        name: contact
        fields:
            -
                name: name
                label: 'Name'
                type: text
            -
                name: email
                label: 'Email Address'
                type: email
            -
                name: subject
                label: 'Subject'
                type: text
            -
                name: message
                label: 'Message'
                type: textarea
        buttons:
            - type: submit
              value: Submit
        process:
            -
                groove:
                    from:
                        name: '{{ form.value.name }}'
                        email: '{{ form.value.email }}'
                    subject: '{{ form.value.subject }}'
                    body: '{{ form.value.message|nl2br }}'

Let's look at the parameters of the `groove` form action:

* `from` **(required)**: Can be a simple email address, or (as shown above) an array containing a name and an email.
* `to` (optional, or required if not set in the plugin's config): The email address of the Groove mailbox to create the ticket in.
* `subject` (optional): The subject line for the ticket. If omitted, the plugin's `subject` setting will be used.
* `body` **(required)**: The content of the ticket itself, as HTML.

## Credits / Thanks

Most of the `GrooveAPI` class is taken from [Groove-PHP-Wrapper](https://github.com/paamayim/Groove-PHP-Wrapper). It would be nice to add it as a proper dependency of this plugin, but it doesn't use Composer (and until the author accepts my pull request, it doesn't include the only method of the Groove API that this plugin uses).