# Bye
Bye is a simple set of PHP scripts designed to assist individuals and organizations
in operating a livestreamed tournament.

## About
Bye creates a set of HTML overlays that can be added to popular streaming
software such as [XSplit](https://www.xsplit.com/). Any streaming system that supports HTML web page overlays
should be able to render Bye pages properly.

![example output](https://c312441.ssl.cf1.rackcdn.com/files.calref/16/2d502e1b_bye_example-standings.jpg)

## Set up
### Dependencies
Bye requires a somewhat-modern version of PHP (tested on v5.5.6). In addition, for use of the bracket overlay,
[QueryPath](https://github.com/technosophos/querypath) (`qp.php`) must be in PHP's PATH.

### Usage
Simply copy `config.sample.php` to `sample.php` and fill in your tournament info and API keys as needed.
The config file is self-explanatory.

The scripts do not require any server-specific settings to work, and in fact
run perfectly well using php's
[built-in webserver](http://php.net/manual/en/features.commandline.webserver.php) (i.e. `-S localhost:8080`).

Please note that while Bye was designed with basic security in mind,
it will most likely not hold up to a serious attack. The author highly recommends
common-sense firewalling and port binding as needed.

#### Example Setup: XSPlit
The author recommends a scene setup similar to the following:

* A 'game' scene, featuring the scorebug and a game capture/capture card capture. It is recommended to leave off the
  "keep source in memory" option for the scorebug.
* A 'standings' scene, featuring the standings overlay on top of a relevent background. It is recommended to leave off
  the "keep source in memory" option for the overlay, but on for the background (especially for a video background)
* A 'bracket' scene, set up to be similar as above. However, note that brackets with particularly many rounds may
  cause an unsightly scrollbar to appear. If so, you can use the `HTML->Display->Resolution` options to force it to be
  wide enough.

The overlays will by default use all available width, but only the height they need (and attach to the bottom of the
"page" defined by the XSplit source. It is highly recommended that the user set up their scenes and rehearse
thouroughly before going live.

![example config](https://c312441.ssl.cf1.rackcdn.com/files.calref/16/22a7f04e_bye_example-standings-config.png)

## Contact and Contributions
Please feel free to contact me via email or XMPP at sylae@calref.net if you have any questions or are interested
in helping out.
