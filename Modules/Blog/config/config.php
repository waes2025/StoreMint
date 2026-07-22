<?php

return [
    'name' => 'Blog',
    'menus' => [
        [
            'title' => 'Blog',
            'icon' => 'BookOpen',
            'route' => 'blog.adminIndex',
            'href' => '/admin/blogs',
            'type' => 'sidebar',
            'order' => 10,
        ]
    ]
];
