# Changelog

## Version 2.0.3

- Add field "include_email_tokens"

## Version 2.0.2

- Add field "apns_push_type_override"

## Version 2.0.1

- Add missed chrome_web_badge option to NotificationResolver
- Add create/update segments and notification history examples to readme

## Version 2.0.0

- At least PHP 7.3 version is now required.
- `OneSignal\OneSignal` client now requires always to provide `OneSignal\Config`.
- `OneSignal\OneSignal` client now expects `Psr\Http\Client\ClientInterface` as a second arguments instead of `Http\Client\Common\HttpMethodsClient` and is mandatory.
- `OneSignal\OneSignal` client now requires always to provide `Psr\Http\Message\RequestFactoryInterface` as a third argument and `Psr\Http\Message\StreamFactoryInterface` as a fourth argument.
- Replaced magic __get method with __call in `OneSignal\OneSignal`, so from now calls like
`$oneSignal->apps` must be used as `$oneSignal->apps()`. It is better to use Dependency injection, because these calls will construct new instances.
- Removed `OneSignal\Exception\OneSignalException` and added `OneSignal\Exception\OneSignalExceptionInterface`.
- Removed `setConfig` and `setClient` methods in `OneSignal\OneSignal`. You can build new instances with different configs or http clients.
