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


## Setup

1\. Copy config.ini.sample to config.ini

##### Email Setup (optional)

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

## Usage
Reporter comes with an example json test script for testing github com. Call reporter.php with the --test argument to test against test scripts in the /tests folder. You can also call reporter.php with an absolute path to the test script or call without the --test argument to test all scripts in the /tests folder.

```
// Test all valid json test files in the /tests directory
php reporter.php

// Test github.json in the /tests directory
php reporter.php --test=github.json

// Or use an absolute path to the test file
php reporter.php --test=/opt/tests/github.json
```

### Test Configuration
Reporter comes with a sample testfile [github.json](tests/github.json). You can name a test whatever you want as long as you end with the file ending specified by the test_file_extension var in your [config.ini](config.ini.sample) file. Test files must be valid json. For help debugging invalid json, try using http://json.parser.online.fr.  

__Test File Contents - top level properties__

Variable | Description
------------- | -------------
name  | _(Required)_ The name of the test file.
description  | _(Optional)_ A description of the test file, added to notification emails when present.
emails | _(Optional)_ An array of email addresses to send the test results to.
options | _(Optional)_ Additional test options. See breakout table below.
tests | _(Required)_ An array of test objects. See breakout table below.

__Options Contents - a top level property (see above)__

Variable | Description
------------- | -------------
email_level  | _(Optional)_ When to send results via email after running a test. Valid values: `skip`, `fail`, `all`. Skip will only send results when the test results contain a skip or fail result. Fail (default value when unspecified) will only send results when test results contain a fail value. All always sends test results.

__Test Contents - properties of the test object, which is an array element of tests (see above)__

Variable | Description
------------- | -------------
name  | _(Required)_ The name of the test.
uri  | _(Required)_ The uri to retrieve for use in the test.
content | _(Required)_ The content of the uri used when running the test. Name corresponds to the class name containing the comparison methods. (see below Content Classes and Methods section)
operator | _(Required)_ The comparison to be used when running the test. Name corresponds to the method of the content class used. (see below Content Classes and Methods section)
args | _(Optional)_ An additional value passed to the operator method of the content class. (see below Content Classes and Methods section)

### Content Classes and Comparison Methods
Reporter ships with two content classes that allow you to execute a variety of tests against retrieved content. Additional custom classes can be plugged into Reporter to give richer test functionality.

__html Content Class - [/lib/content/Html.php](/lib/content/Html.php)__

Operator | Description
------------- | -------------
contains  | Test whether the html contents contains the value specified by the args value.
!contains  | Test whether the html contents does not contain the value specified by the args value.

__status Content Class - [/lib/content/Status.php](/lib/content/Status.php)__

Operator | Description
------------- | -------------
=  | Test whether the html response status code is equal to the value specified by the args value.
!=  | Test whether the html response status code is not equal to the value specified by the args value.
<  | Test whether the html response status code is less than the value specified by the args value.
<=  | Test whether the html response status code is less than or equal to the value specified by the args value.
>  | Test whether the html response status code is greater than the value specified by the args value.
>=  | Test whether the html response status code is greater than or equal to the value specified by the args value.

#### Custom Content Classes

_Coming soon_

