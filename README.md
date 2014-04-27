# Reporter
### A simple intgeration test framework aimed at develeopers and network administrators, written in PHP.

### Setup

1\. Copy config.ini.sample to config.ini

#### Setup Email

Reporter can be easily configured to email test results on test completion using PHPMailer. If you don't want test results emailed, you can skip these steps.

2\. Within the base folder of Reporter, git clone the [PHPMailer](https://github.com/PHPMailer/PHPMailer) repository
```
git clone https://github.com/PHPMailer/PHPMailer.git
```
3\. Uncomment the php_mailer_location variable in config.ini and point to class.phpmailer.php
```
; Set location of PHPMailer. If empty, email notifications won't be available
php_mailer_location = 'PHPMailer/class.phpmailer.php';
```
4\. Add your SMTP mail server setup to config.ini

If you don't have your own STMP mail server, you can use a third party service to send email:
- Gmail - send email esily using your existing Gmail account: https://www.digitalocean.com/community/articles/how-to-use-google-s-smtp-server. Note you may have to unlock your account by going here first: https://accounts.google.com/displayunlockcaptcha.
- Mandrill - from the people who make Mailchimp: http://help.mandrill.com/categories/20090941-SMTP-Integration

### Usage
Reporter comes with an example json test script for testing github com. Call reporter.php with the --test argument to test against test scripts in the /tests folder. You can also call reporter.php with an absolute path to the test script or call without the --test argument to test all scripts in the /tests folder.

```
// Test all valid json test files in the /tests directory
php reporter.php

// Test github.json in the /tests directory
php reporter.php --test=github.json

// Or use an absolute path to the test file
php reporter.php --test=/opt/tests/github.json
```



