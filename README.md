# Country Block Security Page
This repository contains a PHP script to block access from specific countries and manage request rate limits.

## Setup
- Make sure to have `cURL` enabled in your PHP environment.
- Update `$blockedCountries` with any additional countries you want to block.

## Features
- IP-based country blocking
- Rate limiting (max 10 requests per 60 seconds)
- Simple security verification page
