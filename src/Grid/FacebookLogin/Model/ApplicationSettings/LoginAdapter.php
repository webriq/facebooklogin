<?php

namespace Grid\FacebookLogin\Model\ApplicationSettings;

use Grid\Facebook\Model\ApplicationSettings\AbstractAdapter;

/**
 * LoginAdapter
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class LoginAdapter extends AbstractAdapter
{

    /**
     * @const string
     */
    const APPLICATION = 'login';

    /**
     * Facebook login need an `appId` & an `appSecret`
     *
     * @return  array
     */
    public static function getDefaultSettingsKeys()
    {
        return array(
            'appId',
            'appSecret',
        );
    }

}
