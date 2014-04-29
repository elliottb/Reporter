# Reporter
##### A developer and network admin focused content monitoring, testing, and notification suite written in PHP.

### What Reporter Is
- Simple to setup and run by network admins and developers
- Executes integration tests by examining site or service content for expected/unexpected content
- Outputs test results in shell or sent via email
- Supplemental to existing network health monitoring systems

### What Reporter Isn't
- A comprehensive network or system health monitoring system
- Simple to setup for **non** network admins and developers


### Use Cases

**A. Troubleshooting & Informational Purposes**

Reporter can be used to find intermittent site problems or conditions that are hard to track down. Often, knowledge of a problem's occurrence helps troubleshoot it. Without polluting the existing network monitoring infrastructure, a developer can setup a Reporter test to check for a problem and be alerted when it occurs.

**B. Backup to Existing Network Health & Monitor**

Reporter can be used as a self-hosted backup to paid uptime and health monitoring services.


### Setup

1\. Copy config.ini.sample to config.ini

##### Email Setup

*Reporter can be easily configured to email test results on test completion using PHPMailer. If you don't want test results emailed, you can skip these steps.*

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



