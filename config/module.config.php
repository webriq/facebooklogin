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
        'Grid\User\Model\Authentication\AdapterFactory' => array(
            'adapter'   => array(
                'facebook'  => 'Grid\FacebookLogin\Authentication\FacebookAdapter',
            ),
        ),
    ),
    'modules' => array(
        'Grid\Core' => array(
            'settings' => array(
                'facebook' => array(
                    'textDomain'    => 'facebook',
                    'fieldsets'     => array(
                        'login'     => 'facebook-login',
                    ),
                ),
                'facebook-login'    => array(
                    'textDomain'    => 'facebook',
                    'elements'      => array(
                        'enabled'   => array(
                            'key'   => 'modules.User.features.loginWith.Facebook.enabled',
                            'type'  => 'ini',
                        ),
                        'appId'     => array(
                            'key'   => 'modules.FacebookLogin.appId',
                            'type'  => 'ini',
                        ),
                        'appSecret' => array(
                            'key'   => 'modules.FacebookLogin.appSecret',
                            'type'  => 'ini',
                        ),
                    ),
                ),
            ),
            'navigation'    => array(
                'settings'  => array(
                    'pages' => array(
                        'service'   => array(
                            'label'         => 'admin.navTop.service',
                            'textDomain'    => 'admin',
                            'order'         => 7,
                            'uri'           => '#',
                            'parentOnly'    => true,
                            'pages'         => array(
                                'facebook'  => array(
                                    'label'         => 'admin.navTop.settings.facebook',
                                    'textDomain'    => 'admin',
                                    'order'         => 2,
                                    'route'         => 'Grid\Core\Settings\Index',
                                    'resource'      => 'settings.facebook',
                                    'privilege'     => 'edit',
                                    'params'        => array(
                                        'section'   => 'facebook',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'Grid\User'  => array(
            'features'  => array(
                'loginWith' => array(
                    'Facebook'  => array(
                        'enabled'   => false,
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
        'Grid\FacebookLogin' => array(
            'appId'     => '',
            'appSecret' => '',
        ),
    ),
    'form' => array(
        'Grid\Core\Settings\Facebook' => array(
            'type'          => 'Grid\Core\Form\Settings',
            'attributes'    => array(
                'data-js-type' => 'js.form.fieldsetTabs',
            ),
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
    'view_manager' => array(
        'head_defaults' => array(
            'headMeta'  => array(
                'googleSiteVerification' => array(
                    'name'      => 'google-site-verification',
                    'content'   => '',
                ),
            ),
        ),
    ),
);