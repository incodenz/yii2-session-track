# Session Track for Yii2 Application

Simple component that allows for tracking of sessions / logins.
  
Provides a configurable list of exceptions.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Add the following to your `composer.json` file.

~~~
    "require" : {
        "incodenz/yii2-session-track": "*"
    },
~~~

## Configuration Examples

### Basic Configuration
~~~
...
'bootstrap' => [
    'sessionTrack',
]
'components' => [
    'sessionTrack' => [
        'class' => 'incodenz\SessionTrack\Component',
    ]
],
...
~~~

Run the migrations
~~~bash
cp vendor/incodenz/yii2-session-track/src/migrations/* migrations/
php yii migrate
~~~

