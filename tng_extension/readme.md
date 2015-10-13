Client Login
============

An extension to remember authenticated visitors on your Bolt website. This extension uses 
<a href="http://hybridauth.sourceforge.net" target="_blank">HybridAuth</a> for the actual authentication process

Installation
============

To enable a provider set the value `enabled: true` in the configuration and 
replace the example provider keys with real values. (see below)


Adding Providers
================

Google
------
1. Login to https://console.developers.google.com/project
1. Click 'Create Project' (go to next step if using an existing one)
  1. Set a descriptive Project Name
  1. Agree to terms and services
  1. Click Create
1. Expand 'APIs & auth' menu and select 'Credentials'
  1. Under 'OAuth' click 'Create new Client ID'
  1. Click 'Configure consent screen'
  1. Set your desired email address from the selector
  1. Set a unique 'Product Name'
  1. Click 'Save'
1. In the 'Create Client ID' dialogue
  1. Set **Application Type**: Web Application
  1. Set **Authorized JavaScript Origins**: http://your-bolt-site.example.com  
    *(change the domain name to match yours)*
  1. Set **Authorized Redirect URI**: http://your-bolt-site.example.com/oauth2/callback?provider=Google  
    *(change the domain name to match yours)*
  1. Click 'Create Client ID'
1. Under the 'APIs & auth' menu select 'APIs'
  1. Click the 'Google+ API' link (middle of the page at time of writing)
  1. Click the 'Enable API' button
  1. Click the 'Explore this API' link
  1. Click the 'Authorize requests using OAuth 2.0' switch (top right)
  1. On the 'Authorize requests using OAuth 2.0' dialog enable:
    * Authorize requests using OAuth 2.0 (Authorize requests using OAuth 2.0)
    * Know who you are on Google (https://www.googleapis.com/auth/plus.me)
    * View your email address (https://www.googleapis.com/auth/userinfo.email)
    * View your basic profile info (https://www.googleapis.com/auth/userinfo.profile)
1. Add the 'Client ID' and 'Client Secret' to your config.yml

**NOTE** It may take as long as 10 minutes for the changes to propagate and the client ID and secret to work

Facebook
--------
1. Login to Facebook with the account you want to use for the site
1. Go to https://developers.facebook.com
1. Under the 'Apps' menu select 'Create a New App'
1. In the 'Create a new app' dialogue set:
  - **'Display Name'** set to something descriptive
  - **'Namespace'** just leave blank
  - **'Is this a test version of another app?'** leave as 'No'
  - **'Category'** just select a category that fits your site content
1. Click 'Create App' and enter displayed CAPTCHA
  - At this point your app is created in development mode and you will be redirected to the App Dashboard.
1. In the left menu, select 'Settings'
  1. In the 'App Domains' field enter your sites domain name:
    - your-bolt-site.example.com  
    *(change the domain name to match yours)*
  1. In the 'Contact Email' field enter your sites contact email address:
    - someone@your-bolt-site.example.com  
    *(change the address to match your site's)*
  1. Click 'Add Platform' button
  1. Choose "Website" in displayed dialogue
  1. Enter your site's relevant URLs:
    - **'Site URL'** - http://your-bolt-site.example.com
    - **'Mobile Site URL'** - http://mobile.your-bolt-site.example.com  
    *(change the domain name to match yours)*
  1. Click 'Save Changes'
1. In the left menu, select 'Status & Review'
  1. Set the toggle next to **"Do you want to make this app and all its live features available to the general public?"** to Yes
  1. Click 'Confim' in the displayed dialogue
1. In the left menu, select 'Dashboard'
1. Add the 'App ID' and 'App Secret' to your config.yml file


**Multiple URLs**
1. Go to https://developers.facebook.com
1. In the left menu, select 'Settings'
1. Select the 'Advanced' tab
1. Scroll down to the 'Security' section of the page
1. Add URLs to the 'Valid OAuth redirect URIs' field

GitHub
------
1. Log into GitHub
1. Go to: https://github.com/settings/applications/
1. Click 'Register new application'
1. Fill in the fields:
  1. **Application name**
  1. **Homepage URL**: http://your-bolt-site.example.com
  *(change the domain name to match yours)*
  1. **Application description**
  1. **Authorization callback URL**: http://your-bolt-site.example.com/oauth2/callback?provider=Github  
  *(change the domain name to match yours)*
1. Click 'Register application'
1. Add the 'Client ID' and 'Client Secret' to your config.yml

Password
--------
To use the password authentication you also need to install BoltForms.

Advanced Configuration
----------------------

See <a href="http://hybridauth.sourceforge.net/userguide.html" target="_blank">
the hybrid auth userguide</a> for advanced configuration options.

Template Usage
==============

You can use the following functions and snippets in your templates


Login Link(s)
-------------

There are two Twig function options for displaying the login links:

```
    {{ displaylogin() }}
```

``` 
    {{ displaylogin(true) }}
```
    
In the first instance, after authentication a user is redirected to the homepage.

By supplying the parameter `true` the user is redirected to the current page.

Logout Link
-----------

As with login, there are two options for the logout links:

```
    {{ displaylogout() }}
```

```
    {{ displaylogout(true) }}
```

In the first instance, after logging out a user is redirected to the homepage.

By supplying the parameter `true` the user is redirected back to the current page.

Dynamic Link
------------

If you want the login/logout to be automatically varied based on whether a user
is logged in or our, you can use:

```
    {{ displayauth() }}
```

Overriding Templates
====================

Twig templates used by ClientLogin can be overridden in your config file.

Assuming you're using a theme directory of `theme/my-site-theme/` you can:

1. Create the directory `theme/my-site-theme/extensions/clientlogin/`
2. Copy the contents of `extensions/vendor/bolt/clientlogin/assets/` to `theme/my-site-theme/extensions/clientlogin/`
3. In your `app/config/extensions/clientlogin.bolt.yml` file, set the `template`
  key to something similar too:
```yaml
template:
    login: extensions/clientlogin/_login.twig
    password: extensions/clientlogin/_password.twig
    password_parent: extensions/clientlogin/password.twig
    feedback: extensions/clientlogin/_feedback.twig
    button: extensions/clientlogin/_button.twig
```
At this point you can customise the files in `theme/my-site-theme/extensions/clientlogin/`
as you need for your site.

ClientLogin in Bolt Extensions
==============================

This extension is pretty bare-bones by design. Most likely, you will use this 
extension in combination with another extension that builds on its functionality. 

To get information about the current visitor:

```php
if ($this->app['clientlogin.session']->doCheckLogin()) {
    // User is logged in
    if ($this->app['clientlogin.db']->getUserProfileBySession($this->app['clientlogin.session']->token)) {
        $username = $this->app['clientlogin.db']->user['username'];
        $provider = $this->app['clientlogin.db']->user['provider'];
        $providerdata = json_decode($this->app['clientlogin.db']->user['providerdata']);
    }
}
```

Event Handlers
--------------

If you want to hook into login/logout events, ClientLogin dispatches events to 
listeners on `clientlogin.Login` and `clientlogin.Logout`.

You can add the hooks and specify callback functions like so: 

```php
$this->app['dispatcher']->addListener('clientlogin.Login',  array($this, 'myLoginCallback'));
$this->app['dispatcher']->addListener('clientlogin.Logout', array($this, 'myLogoutCallback'));
```

Inside your callback you can get the array of user profile data for the login/logout
with:

```php
public function myLoginCallback(\Bolt\Extension\Bolt\ClientLogin\ClientLogin\Event\ClientLoginEvent $event)
{ 
    $userdata = $event->getUser();
}
```
