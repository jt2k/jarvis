Jarvis
======

Jarvis is a multi-modal chat bot written in PHP.  This started out as a simple test bot for [Slack](https://slack.com/), but is now a more generic framework that also responds via command line, web, SMS, and email.  There isn't much here that is novel (see [Hubot](http://hubot.github.com/)), but I have enjoyed having a single place to execute random bits of useful code I've written in the past.

Command-line usage
------------------

```sh
cd bin
jarvis  # interactive jarvis
jarvis weather forecast  # send the message "weather forecast" to jarvis
jarvis help  # list all responders (if help isn't disabled in config)
```

Configuration
-------------

Copy config.sample.php to config.php and edit as necessary.  The majority of configuration options are for storing API keys and other responder-specific settings.  This is also where you would set your Slack token for SlackBot or Mandrill email settings for MandrillBot.

**enabled_responders** can be set to an array of responders or set to 'all'.  Defaults to all.

For the web console (test.php) or web-hooks (used by Slack, Mandrill, and Twilio), you will want to point your web root to the web directory.

Add `bin/jarvis` to your path for access to Jarvis from anywhere on the command line.

Finally, if you want to make use of the file cache, make the cache directory writable by both your command line user and web server.

What's next
-----------

* Separate "default" (globally useful) responders from personal-use responders
* Twitter interface
* ~~Twilio interface~~
* IRC interface
* More modularized interface architecture
* Persistent storage for responders
* Stateful responders
* Per-user settings (e.g. allow users to override default lat/lon coordinates)
