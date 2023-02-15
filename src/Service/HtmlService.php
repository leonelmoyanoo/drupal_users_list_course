<?php

namespace Drupal\drupal_users_list\Service;

use Drupal\Core\Url;
use Drupal\Core\Link;

class HtmlService
{

    public function __construct()
    {
    }

    public function createLink($url, $title, $class = NULL, $params = NULL, $render = TRUE)
    {
        if (is_null($class)) {
            $class = ['button', 'button--primary', 'button--small'];
        }
        $class = ['class' => $class];
        $url = $this->createUrl($url);
        if($params) {
            $url->setRouteParameters($params);
        }
        $link = new Link(t('@title', [
            '@title' => $title
        ]), $url);
        if ($render) {
            $link = $link->toRenderable();
            $link['#attributes'] = $class;
        }
        return $link;
    }

    public function createUrl($url, $uri = FALSE)
    {
        return $uri ? Url::fromUri($url) : new Url($url);
    }

    public function createTable(array $headers, array $rows, $empty)
    {
        $headers = array_map(function ($header) {
            return t($header);
        }, $headers);
        $table = [
            '#rows' => $rows,
            '#header' => $headers,
            '#type' => 'table',
            '#empty' => t('@empty', [
                '@empty' => $empty
            ]),
            '#attributes' => [
                'class' => ['table', 'views-table']
            ]
        ];
        
        return $table;
    }

    public function createPager($weight)
    {
        return [
            '#type' => 'pager',
            '#weight' => $weight
        ];
    }
}
