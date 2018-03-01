# BMORE911API

TRAVIS-CI: [![Build Status](https://travis-ci.org/arn95/Bmore911API.svg?branch=master)](https://travis-ci.org/arn95/Bmore911API)

API is live: https://api.bmore911.com/api/hello

# Table of Contents

- [Project Summary](#project-summary)
    - [Solving a Problem](#solving-a-problem)
    - [Features](#features)
    - [Upcoming Features](#upcoming-features)
    - [Issues](#issues)
- [Continuous Integration](#continuous-integration)
    - [Server Requirements](#server-requirements)
    - [Project Structure](#project-structure)
    - [Building](#building)
    - [Testing](#testing)
    - [Deployment](#deployment)
        - [Heroku](#heroku)
        - [Other Server](#other-server)
- [Endpoints](#endpoints)
    - [Authentication](#authentication)
    - [Call Record](#call-record)
        - [Count](#count)
        - [Search](#search)
- [Contact](#contact)




# Project Summary

Bmore911API serves [Bmore911](https://bmore911.com) ([Github](https://github.com/arn95/Bmore911)) by downloading the latest 911 call record file from https://data.baltimorecity.gov, cleaning and processing the call records for uniformity and storing them in a MySQL database. Records are stored in the database without discriminating against missing data. Missing or corrupted data is simply transformed to a more appropriate format. This is because the API could potentially serve other applications in the future and having the record itself is important, whereas having it without coordinates or description might not be. Client applications can send requests to the [endpoints](#endpoints) documented in this readme and fetch the relevant data. There is no client authentication authentication layer as of now but that is subject to change. The API is hosted by Heroku on a free plan with Heroku Cron and ClearDB addon. The ClearDB addon is upgraded to 1GB due to the amount of records stored surpassing the size allowance for a free plan. This app is built using the Laravel 5.6 framework which abstracts away a lot of complexity when it comes to making your very own backend. Lumen, the minified Laravel for even faster performance was a good candidate. However, Laravel provides its Artisan CLI and many more niceties, which give a very nice jumpstart on the project and general peace of mind.

## Solving a Problem
This API in addition to supporting the main application [Bmore911](https://bmore911.com) ([Github](https://github.com/arn95/Bmore911)) can potentially be used for other purposes. As far as I know https://data.baltimorecity.gov does not have an API that is truly Restful and you have to create an account to get service. They allow only downloads of the full dataset without filters such as those found in the [search endpoint](#search). Also, due to the fact that there is a lack of exposed endpoints for call record stats, the importance of this API increases for those who need them.

## Features
* Downloads call record file daily using cron and Laravel's Scheduler.
* Processes only the call records of the current year and those that aren't in the database by keeping track of where it is in the call record file.
* Provides authentication endpoints for clients and users.
* Endpoints can be called by everyone.
* Call record endpoints are Restful and provide different sorts of data from the dataset

## Upcoming Features
* Add client authentication layer.
* Store call record files in Amazon AWS instead of Heroku's ephemeral filesystem.
* Add more endpoints that show different and interesting stats about the data

## Issues
* Responses sometimes are slow and that is due to the fact that the API is running on Heroku's free plan with a single worker thread.

# Continuous Integration

This project is using the community version of Travis CI for its building, testing and deployment phase. Please have a look at .travis.yml and .env.travis for the config.

Below you will find each phase that Travis executes in detail, so that if you're not using Travis you can still run the app.

## Project Structure

All Laravel apps generated from either composer or laravel cli (like this one) have this project structure described [here](https://laravel.com/docs/5.6/structure)


Please refer to it to understand the basic files that make this project work.

## Server Requirements

Taken from Laravel's [website](https://laravel.com/docs/5.6/installation) make sure you have the following in your deployment server:

* PHP >= 7.1.3
* OpenSSL PHP Extension
* PDO PHP Extension
* Mbstring PHP Extension
* Tokenizer PHP Extension
* XML PHP Extension
* Ctype PHP Extension
* JSON PHP Extension

## Building

This guide assumes you are using either MacOS or Linux. Windows sux.

1. Install Composer (Laravel manages its dependencies with it)

```sh
php composer-setup.php --install-dir=bin --filename=composer
```

[Composer installation on Windows](https://getcomposer.org/doc/00-intro.md#installation-windows)

2. (Optional) Make composer available in entire system

```sh
mv composer.phar /usr/local/bin/composer
```

3. Install Laravel with Composer

```sh
composer global require "laravel/installer"
```

For the sake of completeness, if you want to know the command to make a Laravel project it is:

```sh
laravel new [project name]
```

Or

```sh
composer create-project --prefer-dist laravel/laravel [project name]
```

3. Run it! (make sure you are in this projects folder)

```sh
php artisan serve
```

## Testing

As is customary for Laravel project this app is using PHPUnit for testing.

To run the tests (make sure you are in project root dir):

1. Make sure MySQL daemon is running and has the following
    * MySQL database or schema bmore911_testing exists
    * MySQL host is localhost
    * MySQL username is root
    * MySQL password is empty or nothing

If you have your own setup then you can:
    * Change the default values above at config/database.php
    * Add or change the default values above at .env

Please note that the environment variables set at .env take precedence over any environment value set anywhere in the app.

2. Run the tests

```sh
vendor/bin/phpunit
```

In addition to unit tests I personally wrote a pre-commit hook that would:
1. Check for syntax errors
2. Run the unit tests
3. Check whether .env.prod exists and copy it to .env (.env.prod are the environment variables used when the app is in production/deployed)

```sh
#!/bin/bash

echo "Pre-commit starting..."

git diff --cached --name-only | while read FILE; do
if [[ "$FILE" =~ ^.+(php|inc|module|install|test)$ ]]; then
    if [[ -f $FILE ]]; then
        php -l "$FILE" 1> /dev/null
        if [ $? -ne 0 ]; then
            echo -e "\e[1;31m\tAborting commit due to files with syntax errors.\e[0m" >&2
            exit 1
        fi
    fi
fi
done || exit $?

if [ $? -eq 0 ]; then
    vendor/bin/phpunit 1> /dev/null
    if [ $? -ne 0 ]; then
        echo -e "\e[1;31m\tUnit tests failed ! Aborting commit.\e[0m" >&2
        exit 1;
    else
        RESULT=$(grep "dpm(" "$FILE")
        if [ ! -z $RESULT ]; then
            echo -e "\e[1;33m\tWarning, the commit contains a call to dpm(). Commit was not aborted, however.\e[0m" >&2
        fi
    fi
fi

cp .env.prod .env
if [ $? -ne 0 ]; then
    echo -e "\e[1;31m\tCopying .env.prod to .env failed! Aborting commit.\e[0m" >&2
    exit 1;
fi

echo "Pre-commit ended."
```

### A note about .env

Please note that every Laravel app contains a special base64 key used by Laravel to perform encryption and hashing that should be in .env during production. It should be secret so .env.prod will not be on git. It is defined in APP_KEY environment variable.

Laravel sets it during project structure setup when this runs:

```sh
laravel new [project name]
```

In case APP_KEY is not there then run:

```sh
php artisan key:generate
```

## Deployment

This is the fun phase. This project is hosted by Heroku and Travis takes care of deployment to the current Heroku instance. But if you have your own free Heroku or have another setup, and you probably do, you can certainly run this app if you meet the [requirements](#requirements) and have a valid .env file.

### Heroku

This is great tutorial on how to set up Laravel on Heroku:
https://blog.devcenter.co/the-ultimate-cheatsheet-for-deploying-laravel-apps-to-heroku-f0c4f281e0f3

Pay close attention to the Queues section. That is one way to start the queue listener to run the daily fetch of call records however you can also get the Heroku Cron addon.

If you get the Heroku Cron addon then set this in your cron job:

```sh
php artisan schedule:run
```

Then configure the cron job to run at whatever interval. I have set it to hourly. Daily would also work but could potentially cause disruption in schedule of fetching if timezones of Laravel Schedulers and Heroku instances are not the same. Shorter intervals are definitely not necessary for this project to run.

I chose to add the Heroku Cron addon because I wanted to control the inverval and set it to hourly.

### Other Server

If you are not using Heroku, then you can run this app by making sure that your Nginx or Apache are configured to look at the ```public/``` folder. When that is done Laravel does the rest.

I recommend using something like Dokku to manage your apps in your server of choice. Some Digital Ocean droplets have it by default and its ready to go. Dokku is something like Heroku + Docker. Great piece of software. And the great thing about it is that you would not have to change a line of code to get this project running on Dokku.


## Endpoints

The base API url is ```api.bmore911.com/api```

#### HTTP Response Codes

This project has standardized HTTP response codes that mean what they are supposed to mean.

They are as follows:
* 200 or 201 - Success => Request is understood and what was requested is returned


* 400 - Bad Request => Params or json body properties are wrong)


* 401 - Unauthorized => Client or User authentication failed because their token is expired or invalid


* 403 - Forbidden => User does not have the required privileges to view this resource


* 404 - Not Found => The requested resource does not exist. Your url is probably wrong.


* 405 - Method Not Allowed => Change method. If you had POST then try GET.


* 409 - Conflict => Too many requests are being sent that modify the same resource.


* 500 - Internal Error => There was an error server-side. Its not you, its the app.

### Authentication

User authentication in this app uses the JWT standard. 
Authentication routes are as follows:

**POST**: ```https://api.bmore911.com/api/auth/signup```


**POST**: ```https://api.bmore911.com/api/auth/login```


**POST**: ```https://api.bmore911.com/api/auth/recovery```


**POST**: ```https://api.bmore911.com/api/auth/reset```


**POST**: ```https://api.bmore911.com/api/auth/logout```


**POST**: ```https://api.bmore911.com/api/auth/refresh```


**GET**: ```https://api.bmore911.com/api/auth/me```

I will not go in detail about them since they are not currently used at all.

### Call Record

Call record routes so far have 2 types of endpoints which are Count and [Search](#search).

#### Count

To get the count of all call records made **today** send a request at: 

**GET**: ```https://api.bmore911.com/api/records/count/today```

To get the count of all call records made **this week** send at request at:

**GET**: ```https://api.bmore911.com/api/records/count/week```

To get the count of all call records made **this month** send a request at:

**GET**: ```https://api.bmore911.com/api/records/count/month```

To get the count of all call records made **this year** send a request at:

**GET**: ```https://api.bmore911.com/api/records/count/year```

An example response from all the endpoints above would be:

```json
{
	"status": "success",
	"data": 118539
}
```

or 

```json
{
	"status": "failed",
	"message": "An unknown error occurred."
}
```

or simply a response containing the 500 statusin case of failure.

To get all the data from the above endpoints then send a request at:

**GET**: ```https://api.bmore911.com/api/records/count/all```

An example response from that endpoint would be:

```json
{
	"status": "success",
	"data": {
		"today": 0,
		"week": 0,
		"month": 0,
		"year": 118539
	}
}
```

or 

```json
{
	"status": "failed",
	"message": "An unknown error occurred."
}
```

or simply a response containing the 500 status in case of failure.

#### Search

**POST**: ```https://api.bmore911.com/api/auth/refresh```

Example Request Body:

```json
{
    "start_date": "2018-02-16",
    "end_date":"2018-02-17",
	"priorities": [2,3],
	"districts": ["NE", "ND"]
}
```

Corresponding Response:

```json
{
	"status": "success",
	"data": [
		{
			"bpd_call_id": "P180470013",
			"call_time": "2018-02-16 00:05:00",
			"priority": 3,
			"district": "NE",
			"description": "Traffic Stop",
			"address": "6200 HARFORD RD",
			"latitude": 39.359447000000003,
			"longitude": -76.555283000000003,
			"created_at": "2018-02-24 15:22:24",
			"updated_at": "2018-02-24 15:22:24"
		},
		{
			"bpd_call_id": "P180470024",
			"call_time": "2018-02-16 00:09:00",
			"priority": 2,
			"district": "NE",
			"description": "FAMILY DISTURB",
			"address": "5000 GOODNOW RD",
			"latitude": 39.326146999999999,
			"longitude": -76.547083000000001,
			"created_at": "2018-02-24 15:22:25",
			"updated_at": "2018-02-24 15:22:25"
		},
		{
			"bpd_call_id": "P180470025",
			"call_time": "2018-02-16 00:10:00",
			"priority": 2,
			"district": "NE",
			"description": "DISORDERLY",
			"address": "1900 E 30TH ST",
			"latitude": 39.325384,
			"longitude": -76.58896,
			"created_at": "2018-02-24 15:21:18",
			"updated_at": "2018-02-24 15:21:18"
		},
		{
			"bpd_call_id": "P180470031",
			"call_time": "2018-02-16 00:15:00",
			"priority": 3,
			"district": "ND",
			"description": "Traffic Stop",
			"address": "500 E 35TH ST",
			"latitude": 39.330942,
			"longitude": -76.609012000000007,
			"created_at": "2018-02-24 15:22:25",
			"updated_at": "2018-02-24 15:22:25"
		},
		{
			"bpd_call_id": "P180470037",
			"call_time": "2018-02-16 00:17:00",
			"priority": 2,
			"district": "ND",
			"description": "UNFOUNDED",
			"address": "300 WINSTON AV",
			"latitude": 39.349679000000002,
			"longitude": -76.615301000000002,
			"created_at": "2018-02-24 15:22:25",
			"updated_at": "2018-02-24 15:22:25"
		},
		{
			"bpd_call_id": "P180470038",
			"call_time": "2018-02-16 00:18:00",
			"priority": 2,
			"district": "ND",
			"description": "UNFOUNDED",
			"address": "3400 BLK ROLAND AV",
			"latitude": 39.326937000000001,
			"longitude": -76.632672999999997,
			"created_at": "2018-02-24 15:22:25",
			"updated_at": "2018-02-24 15:22:25"
		},
		{
			"bpd_call_id": "P180470040",
			"call_time": "2018-02-16 00:18:00",
			"priority": 3,
			"district": "ND",
			"description": "DISCHRG FIREARM",
			"address": "3500 BLK ROLAND AV",
			"latitude": 39.329686000000002,
			"longitude": -76.632571999999996,
			"created_at": "2018-02-24 15:22:25",
			"updated_at": "2018-02-24 15:22:25"
		},
		{
			"bpd_call_id": "P180470043",
			"call_time": "2018-02-16 00:20:00",
			"priority": 2,
			"district": "ND",
			"description": "DISORDERLY",
			"address": "800 UNION AV",
			"latitude": 39.333536000000002,
			"longitude": -76.630097000000006,
			"created_at": "2018-02-24 15:22:25",
			"updated_at": "2018-02-24 15:22:25"
		},
		{
			"bpd_call_id": "P180470048",
			"call_time": "2018-02-16 00:20:00",
			"priority": 2,
			"district": "NE",
			"description": "AUTO ACCIDENT",
			"address": "5500 CEDONIA AV",
			"latitude": 39.333725999999999,
			"longitude": -76.532674999999998,
			"created_at": "2018-02-24 15:22:25",
			"updated_at": "2018-02-24 15:22:25"
		}

        ...
```

# Contact

If you have any questions or simply want to say hi send me an email at ```arnold.balliu@gmail.com```


