Jarvis
======

Jarvis is a multi-modal chat bot written in PHP.  This started out as a simple test bot for [Slack](https://slack.com/), but is now a more generic framework that also responds via command line, web, SMS, and email.  There isn't much here that is novel (see [Hubot](http://hubot.github.com/)), but I have enjoyed having a single place to execute random bits of useful code I've written in the past.

Configuration
-------------

Copy config.sample.php to config.php and edit as necessary.  The majority of configuration options are for storing API keys and other responder-specific settings.  This is also where you would set your Slack token for SlackBot or Mandrill email settings for MandrillBot.

**enabled_responders** can be set to an array of responders or set to 'all'.  Defaults to all.

For the web console or web-hooks (used by Slack, Mandrill, and Twilio), you will want to point your web root to the **web** directory.

Add `bin/jarvis` to your path for access to Jarvis from anywhere on the command line.

Finally, if you want to make use of the file cache, make the **cache** directory writable by both your command line user and web server.

Command-line usage
------------------

```sh
cd bin
./jarvis  # interactive jarvis
./jarvis weather forecast  # send the message "weather forecast" to jarvis
./jarvis help  # list all responders
```

Web usage
---------

* **index.php** - web-based chat console; supports speech recognition and synthesis in Chrome
* **slack.php** - web-hook for Slack interface; **slackbot_token** must be defined in config.php
* **mandrill.php** - web-hook for Mandrill email interface; **mandrill_username**, **mandrill_password**, and **email_address** must be defined in config.php
* **twilio.php** - web-hook for Twilio SMS interface

What's next
-----------

* Separate "default" (globally useful) responders from personal-use responders
* Twitter interface
* Twilio interface :ballot_box_with_check:
* IRC interface
* More modularized interface architecture
* Persistent storage for responders
* Stateful responders
* Per-user settings (e.g. allow users to override default lat/lon coordinates)
