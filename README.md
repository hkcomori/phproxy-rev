# ReversePHProxy

The reverse proxy which provided by nginx, squid, apache mod_proxy, etc, can't use by end users of web-hosting services which have not root privilledge.

In addition, service management features (such as Systemd) are not available, it is difficult to keep back-end services permanently available.

ReversePHProxy provides these missing features and expands these server potentials.

## Features

- Forward HTTP requests to the backend socket (eg. unix domain socket).
- If the backend socket is not listening, attempt to start it on demand (like [systemd.socket](https://www.freedesktop.org/software/systemd/man/latest/systemd.socket.html)).

These are just workarounds for small system. If common functions are available, you should use them (eg. nginx, Systemd, etc).

## Requirements

- Linux
- Web server: Apache 2.4+, LiteSpeed, etc
- PHP 8.0+
- Composer 2.2+

## Disclaimer

AS IS BASIS, NO WARRANTY AT ALL.

You MUST confirm that it does not conflict with the terms of use of your server yourself if you use this.

## Feedback and contributions

If you want to contribute to a project and make it better, your [issues](https://github.com/hkcomori/reverse-phproxy/issues) and [pull requests](https://github.com/hkcomori/reverse-phproxy/pulls) are very welcome.

## Installation

Copy files to web servers, create symbolic link to it under the public directory, run `composer install`, and create `.htaccess` with [this example](.htaccess_sample).

    cd ~/.local/opt/
    git clone https://github.com/hkcomori/reverse-phproxy.git
    ln -s ~/.local/opt/reverse-phproxy/p ~/public_html/p
    cd reverse-phproxy
    composer install --no-dev
    cp .htaccess_sample ~/public_html/.htaccess
    nano ~/public_html/.htaccess    # and edit some configurations

## Configuration

All configuration come from environment variables.
So a single installation provides features for multiple public directories.

Define it using `SetEnv` at `.htaccess` (See also [the example](.htaccess_sample).).

### `REVERSE_PHPROXY_BACKEND`

Backend socket URL  (eg. `unix:/path/to/app.sock`).

### `REVERSE_PHPROXY_START_BACKEND` (OPTIONAL)

Default: `""`

Command line string to start backend socket listening (eg. `/path/to/app/.venv/bin/gunicorn -c /path/to/app/gunicorn.py main:app`).
It must start the server process on background, not on foreground.
If set to blank (or no set), don't attempt to start backend socket listening.

### `REVERSE_PHPROXY_START_BACKEND_TIMEOUT` (OPTIONAL)

Default: `180`

Timeout seconds to wait for start backend socket listening.
