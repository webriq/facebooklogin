<?php

return array(
    'translator' => array(
        'translation_file_patterns' => array(
            'facebookLogin'     => array(
                'type'          => 'phpArray',
                'base_dir'      => __DIR__ . '/../languages/facebookLogin',
                'pattern'       => '%s.php',
                'text_domain'   => 'facebookLogin',
            ),
        ),
    ),
    'factory' => array(
        'Grid\Facebook\Model\ApplicationSettings\AdapterFactory' => array(
            'adapter'       => array(
                'login'     => 'Grid\FacebookLogin\Model\ApplicationSettings\LoginAdapter',
            ),
        ),
        'Grid\User\Model\Authentication\AdapterFactory' => array(
            'adapter'       => array(
                'facebook'  => 'Grid\FacebookLogin\Authentication\FacebookAdapter',
            ),
        ),
    ),
    'modules' => array(
        'Grid\User'  => array(
            'features'  => array(
                'loginWith' => array(
                    'Facebook'  => array(
                        'enabled'   => array(
                            'service'   => 'Grid\FacebookLogin\Model\ApplicationSettings\LoginEnabled',
                        ),
                        'route'     => 'Grid\User\Authentication\LoginWidth',
                        'options'   => array(
                            'query' => array(
                                'facebook' => true,
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'form' => array(
        'Grid\Facebook\ApplicationSettings' => array(
            'fieldsets'     => array(
                'login'     => array(
                    'spec'  => array(
                        'name'      => 'login',
                        'options'   => array(
                            'label'       => 'facebookLogin.form.settings.legend',
                            'description' => 'facebookLogin.form.settings.description',
                        ),
                        'elements'  => array(
                            'enabled'   => array(
                                'spec'  => array(
                                    'type'  => 'Zork\Form\Element\Checkbox',
                                    'name'  => 'enabled',
                                    'options'   => array(
                                        'label' => 'facebookLogin.form.settings.enabled',
                                    ),
                                ),
                            ),
                            'mode'  => array(
                                'spec'  => array(
                                    'type'  => 'Zork\Form\Element\Radio',
                                    'name'  => 'mode',
                                    'options'   => array(
                                        'label'     => 'facebookLogin.form.settings.mode',
                                        'options'   => array(
                                            'default'   => 'facebookLogin.form.settings.mode.default',
                                            'specific'  => 'facebookLogin.form.settings.mode.specific',
                                        ),
                                    ),
                                ),
                            ),
                            'appId' => array(
                                'spec'  => array(
                                    'type'  => 'Zork\Form\Element\Text',
                                    'name'  => 'appId',
                                    'options'   => array(
                                        'label' => 'facebookLogin.form.settings.appId',
                                    ),
                                ),
                            ),
                            'appSecret' => array(
                                'spec'  => array(
                                    'type'  => 'Zork\Form\Element\Text',
                                    'name'  => 'appSecret',
                                    'options'   => array(
                                        'label' => 'facebookLogin.form.settings.appSecret',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);
