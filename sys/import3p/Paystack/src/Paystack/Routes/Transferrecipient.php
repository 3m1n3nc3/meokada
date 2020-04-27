<?php

namespace Yabacon\Paystack\Routes;

use Yabacon\Paystack\Contracts\RouteInterface;

class Transferrecipient implements RouteInterface
{

    public static function root()
    {
        return '/transferrecipient';
    }

    public static function create()
    {
        return [
            RouteInterface::METHOD_KEY => RouteInterface::POST_METHOD,
            RouteInterface::ENDPOINT_KEY => Transferrecipient::root(),
            RouteInterface::PARAMS_KEY => [
                'type',
                'name',
                'description',
                'metadata',
                'bank_code',
                'currency',
                'account_number',
            ],
            RouteInterface::REQUIRED_KEY => [
                RouteInterface::PARAMS_KEY => [
                    'type',
                    'name',
                    'bank_code',
                    'account_number',
                ],
            ],
        ];
    }

    public static function getList()
    {
        return [
            RouteInterface::METHOD_KEY => RouteInterface::GET_METHOD,
            RouteInterface::ENDPOINT_KEY => Transferrecipient::root(),
            RouteInterface::PARAMS_KEY => [
                'perPage',
                'page',
            ],
        ];
    }

    public static function update()
    {
        return [
            RouteInterface::METHOD_KEY => RouteInterface::PUT_METHOD,
            RouteInterface::ENDPOINT_KEY => Transferrecipient::root() . '/{recipient_code}',
            RouteInterface::PARAMS_KEY => [ 
                'name',
                'email' 
            ],
            RouteInterface::ARGS_KEY => ['recipient_code'],
        ];
    }

    public static function delete()
    {
        return [
            RouteInterface::METHOD_KEY => RouteInterface::GET_METHOD,
            RouteInterface::ENDPOINT_KEY => Transferrecipient::root() . '/{recipient_code}',
            RouteInterface::ARGS_KEY => ['recipient_code'], 
        ];
    }
}
