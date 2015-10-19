<?php

return array(
    'console' => array(
        'router' => array(
            'routes' => array(
                'sphinxconfig' => array(
                    'type'    => 'simple',
                    'options' => array(
                        'route'    => 'sphinxconfig build [<target>]',
                        'defaults' => array(
                            'controller'    => 'SphinxConfig\Config',
                            'action'        => 'build'
                        ),
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'sphinx_config' => __DIR__ . '/../view',
        ),
    ),
);
