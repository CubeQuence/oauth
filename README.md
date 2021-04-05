<p align="center"><a href="https://github.com/CubeQuence/oauth" target="_blank" rel="noopener"><img src="https://raw.githubusercontent.com/CubeQuence/CubeQuence/master/public/assets/images/banner.png"></a></p>

<p align="center">
<a href="https://packagist.org/packages/cubequence/oauth"><img src="https://poser.pugx.org/cubequence/oauth/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/cubequence/oauth"><img src="https://poser.pugx.org/cubequence/oauth/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/cubequence/oauth"><img src="https://poser.pugx.org/cubequence/oauth/license.svg" alt="License"></a>
</p>

# OAuth

PHP FusionAuth client made for CubeQuence

## Installation

1. `composer require cubequence/oauth`

## Example

Look at the `examples` folder

## Client Methods

- constuct
    - flowProvider: An flowProvider instance for the required flow
    - authorizationServer: FusionAuth instance url
    - clientId: Client ID
    - clientSecret: Client Secret

- start
    - No Variables, Returns data based on the flowProvider

- callback
    - queryParams: The $_GET object
    - storedVar: The stored state or device_code

- refresh
    - refreshToken: Refresh Token returnd by callback or refresh

- getUser
    - accessToken: accessToken returned by callback or refresh

- logout
    - No Vairables, returns logout url

## Security Vulnerabilities

Please review [our security policy](https://github.com/CubeQuence/oauth/security/policy) on how to report security vulnerabilities.

## License

The CubeQuence framework is open-sourced software licensed under the [MIT license](LICENSE.md).
