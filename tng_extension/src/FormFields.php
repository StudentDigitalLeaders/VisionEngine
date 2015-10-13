<?php

namespace Bolt\Extension\Bolt\ClientLogin;

/**
 * Form data
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class FormFields
{
    /**
     * Symfony/Bolt Forms fields for the password auth
     *
     * @return array
     */
    public static function Password()
    {
        return [
            'parent'       => '',
            'notification' => ['enabled' => false],
            'feedback'     => [
                'success' => '',
                'error'   => ''
            ],
            'fields'       => [
                'username' => [
                    'type'    => 'text',
                    'options' => [
                        'required'    => true,
                        'label'       => 'Username',
                        'constraints' => [
                            'NotBlank',
                            [
                                'Length' => [
                                    'min' => 5,
                                    'max' => 64
                                ]
                            ]
                        ],
                        'attr'        => [
                            'placeholder' => 'Enter your username…'
                        ]
                    ]
                ],
                'password' => [
                    'type'    => 'password',
                    'options' => [
                        'required'    => true,
                        'label'       => 'Password',
                        'constraints' => [
                            'NotBlank',
                            [
                                'Length' => [
                                    'min' => 5,
                                    'max' => 64
                                ]
                            ]
                        ],
                        'attr'        => [
                            'placeholder' => 'Enter your password…'
                        ]
                    ]
                ],
                'submit'   => [
                    'type' => 'submit'
                ]
            ]
        ];
    }
}
